<?php

namespace EventEule\Domain;

/**
 * Generates eventeule_event posts from an eventeule_opening schedule.
 *
 * Schedule meta keys stored on the opening post:
 *   _oh_days_of_week   – serialized int[] e.g. [1,3,5]  (1 = Mon … 7 = Sun, ISO-8601)
 *   _oh_start_time     – "HH:MM"
 *   _oh_end_time       – "HH:MM"
 *   _oh_date_from      – "Y-m-d"  first date to generate (defaults to today)
 *   _oh_date_until     – "Y-m-d"  hard stop (optional – leave empty for open end)
 *   _oh_horizon_weeks  – int, weeks to generate ahead (default 8)
 *   _oh_excluded_dates – serialized string[]  dates ("Y-m-d") that must NOT be generated
 *
 * Copied onto every generated eventeule_event:
 *   _eventeule_start_date
 *   _eventeule_end_date     (same as start_date for single-day events)
 *   _eventeule_start_time
 *   _eventeule_end_time
 *   _eventeule_location
 *   _eventeule_registration_url
 *   _eventeule_short_description
 *   _eventeule_price
 *   _eventeule_note
 *   _eventeule_featured
 *   _eventeule_opening_id   (back-reference to the schedule post)
 */
class OpeningHoursGenerator
{
    /** Meta keys that are copied 1:1 from schedule to generated event. */
    private const COPIED_META = [
        '_eventeule_location',
        '_eventeule_registration_url',
        '_eventeule_short_description',
        '_eventeule_price',
        '_eventeule_note',
        '_eventeule_featured',
    ];

    /**
     * Run the generator for a single opening-hours schedule post.
     * Safe to call repeatedly – idempotent (will not duplicate events).
     */
    public function generate_for_schedule(int $scheduleId): void
    {
        $schedule = get_post($scheduleId);
        if (!$schedule || $schedule->post_type !== OpeningHoursPostType::POST_TYPE) {
            return;
        }

        // Read schedule meta
        $rawDays   = get_post_meta($scheduleId, '_oh_days_of_week', true);
        $daysOfWeek = is_array($rawDays) ? array_map('intval', $rawDays) : [];
        if (empty($daysOfWeek)) {
            return; // nothing configured yet
        }

        $startTime     = (string) get_post_meta($scheduleId, '_oh_start_time', true);
        $endTime       = (string) get_post_meta($scheduleId, '_oh_end_time', true);
        $dateFrom      = (string) get_post_meta($scheduleId, '_oh_date_from', true);
        $dateUntil     = (string) get_post_meta($scheduleId, '_oh_date_until', true);
        $horizonWeeks  = (int)   (get_post_meta($scheduleId, '_oh_horizon_weeks', true) ?: 8);
        $rawExcluded   = get_post_meta($scheduleId, '_oh_excluded_dates', true);
        $excludedDates = is_array($rawExcluded) ? $rawExcluded : [];

        // Determine window
        $windowStart = ($dateFrom !== '') ? max(current_time('Y-m-d'), $dateFrom) : current_time('Y-m-d');
        $horizonEnd  = date('Y-m-d', strtotime("+{$horizonWeeks} weeks", strtotime(current_time('Y-m-d'))));
        $windowEnd   = ($dateUntil !== '') ? min($dateUntil, $horizonEnd) : $horizonEnd;

        if ($windowStart > $windowEnd) {
            return;
        }

        // Collect already-generated event dates for this schedule
        $existing = $this->get_existing_dates($scheduleId);

        // Walk every date in the window
        $cursor = new \DateTime($windowStart);
        $end    = new \DateTime($windowEnd);

        while ($cursor <= $end) {
            $isoDay  = (int) $cursor->format('N'); // 1=Mon … 7=Sun
            $dateStr = $cursor->format('Y-m-d');

            if (in_array($isoDay, $daysOfWeek, true)
                && !in_array($dateStr, $excludedDates, true)
                && !in_array($dateStr, $existing, true)
            ) {
                $this->create_event($scheduleId, $schedule, $dateStr, $startTime, $endTime);
            }

            $cursor->modify('+1 day');
        }
    }

    /**
     * Generate for ALL active opening-hours schedules (called by cron).
     */
    public function generate_all(): void
    {
        $query = new \WP_Query([
            'post_type'      => OpeningHoursPostType::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        foreach ($query->posts as $id) {
            $this->generate_for_schedule((int) $id);
        }
    }

    /**
     * Add a date to the excluded list (= cancel that occurrence).
     */
    public function exclude_date(int $scheduleId, string $date): void
    {
        $rawExcluded   = get_post_meta($scheduleId, '_oh_excluded_dates', true);
        $excludedDates = is_array($rawExcluded) ? $rawExcluded : [];

        if (!in_array($date, $excludedDates, true)) {
            $excludedDates[] = $date;
            update_post_meta($scheduleId, '_oh_excluded_dates', $excludedDates);
        }

        // Also cancel the already-generated event for that date (if any)
        $eventId = $this->get_event_id_for_date($scheduleId, $date);
        if ($eventId) {
            update_post_meta($eventId, '_eventeule_cancelled', '1');
        }
    }

    /**
     * Remove a date from the excluded list (re-enable that occurrence).
     */
    public function include_date(int $scheduleId, string $date): void
    {
        $rawExcluded   = get_post_meta($scheduleId, '_oh_excluded_dates', true);
        $excludedDates = is_array($rawExcluded) ? $rawExcluded : [];

        $excludedDates = array_values(array_filter($excludedDates, fn($d) => $d !== $date));
        update_post_meta($scheduleId, '_oh_excluded_dates', $excludedDates);

        // Also un-cancel the generated event (if any)
        $eventId = $this->get_event_id_for_date($scheduleId, $date);
        if ($eventId) {
            delete_post_meta($eventId, '_eventeule_cancelled');
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * @return string[]  already-generated event dates (Y-m-d) for this schedule
     */
    private function get_existing_dates(int $scheduleId): array
    {
        $query = new \WP_Query([
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => ['publish', 'draft', 'trash'],
            'posts_per_page' => -1,
            'meta_query'     => [[
                'key'   => '_eventeule_opening_id',
                'value' => $scheduleId,
            ]],
            'fields'         => 'ids',
        ]);

        $dates = [];
        foreach ($query->posts as $postId) {
            $d = (string) get_post_meta((int) $postId, '_eventeule_start_date', true);
            if ($d !== '') {
                $dates[] = $d;
            }
        }
        return $dates;
    }

    private function get_event_id_for_date(int $scheduleId, string $date): ?int
    {
        $query = new \WP_Query([
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => ['publish', 'draft', 'trash'],
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    'key'   => '_eventeule_opening_id',
                    'value' => $scheduleId,
                ],
                [
                    'key'   => '_eventeule_start_date',
                    'value' => $date,
                ],
            ],
            'fields' => 'ids',
        ]);

        return !empty($query->posts) ? (int) $query->posts[0] : null;
    }

    private function create_event(
        int    $scheduleId,
        \WP_Post $schedule,
        string $date,
        string $startTime,
        string $endTime
    ): void {
        $postId = wp_insert_post([
            'post_type'   => EventPostType::POST_TYPE,
            'post_status' => 'publish',
            'post_title'  => $schedule->post_title,
            'post_content'=> $schedule->post_content,
            'post_excerpt'=> $schedule->post_excerpt,
        ]);

        if (is_wp_error($postId) || $postId === 0) {
            return;
        }

        // Date / time
        update_post_meta($postId, '_eventeule_start_date', $date);
        update_post_meta($postId, '_eventeule_end_date',   $date);
        if ($startTime !== '') {
            update_post_meta($postId, '_eventeule_start_time', $startTime);
        }
        if ($endTime !== '') {
            update_post_meta($postId, '_eventeule_end_time', $endTime);
        }

        // Back-reference
        update_post_meta($postId, '_eventeule_opening_id', $scheduleId);

        // Copy shared meta
        foreach (self::COPIED_META as $key) {
            $value = get_post_meta($scheduleId, $key, true);
            if ($value !== '') {
                update_post_meta($postId, $key, $value);
            }
        }

        // Copy featured image
        $thumbId = (int) get_post_thumbnail_id($scheduleId);
        if ($thumbId > 0) {
            set_post_thumbnail($postId, $thumbId);
        }
    }
}
