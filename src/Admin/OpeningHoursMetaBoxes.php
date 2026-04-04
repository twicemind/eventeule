<?php

namespace EventEule\Admin;

use EventEule\Domain\OpeningHoursGenerator;
use EventEule\Domain\OpeningHoursPostType;

/**
 * Adds a meta-box to the eventeule_opening post-edit screen so the
 * schedule (days, times, date range, horizon) can be configured there.
 *
 * Also hooks into save_post to trigger event generation.
 */
class OpeningHoursMetaBoxes
{
    private const NONCE_ACTION = 'eventeule_save_opening_meta';
    private const NONCE_NAME   = 'eventeule_opening_meta_nonce';

    private const DAYS = [
        1 => 'Montag',
        2 => 'Dienstag',
        3 => 'Mittwoch',
        4 => 'Donnerstag',
        5 => 'Freitag',
        6 => 'Samstag',
        7 => 'Sonntag',
    ];

    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post',      [$this, 'save_meta_boxes']);
    }

    public function add_meta_boxes(): void
    {
        add_meta_box(
            'eventeule_opening_schedule',
            __('Öffnungszeiten-Konfiguration', 'eventeule'),
            [$this, 'render_schedule_box'],
            OpeningHoursPostType::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'eventeule_opening_eventmeta',
            __('Veranstaltungs-Details (werden auf generierte Events kopiert)', 'eventeule'),
            [$this, 'render_event_meta_box'],
            OpeningHoursPostType::POST_TYPE,
            'normal',
            'high'
        );
    }

    // ── Schedule box ──────────────────────────────────────────────────────

    public function render_schedule_box(\WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        $days          = get_post_meta($post->ID, '_oh_days_of_week', true);
        $selectedDays  = is_array($days) ? array_map('intval', $days) : [];
        $startTime     = (string) get_post_meta($post->ID, '_oh_start_time', true);
        $endTime       = (string) get_post_meta($post->ID, '_oh_end_time', true);
        $dateFrom      = (string) get_post_meta($post->ID, '_oh_date_from', true);
        $dateUntil     = (string) get_post_meta($post->ID, '_oh_date_until', true);
        $horizonWeeks  = (string) (get_post_meta($post->ID, '_oh_horizon_weeks', true) ?: '8');
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Wochentage', 'eventeule'); ?></th>
                    <td>
                        <?php foreach (self::DAYS as $iso => $label): ?>
                            <label style="display:inline-flex; align-items:center; gap:4px; margin-right:14px;">
                                <input type="checkbox" name="oh_days_of_week[]"
                                       value="<?php echo esc_attr($iso); ?>"
                                       <?php checked(in_array($iso, $selectedDays, true)); ?>>
                                <?php echo esc_html($label); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description"><?php esc_html_e('An welchen Wochentagen sollen automatisch Veranstaltungen erstellt werden?', 'eventeule'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="oh_start_time"><?php esc_html_e('Startzeit', 'eventeule'); ?></label></th>
                    <td>
                        <input type="time" id="oh_start_time" name="oh_start_time"
                               value="<?php echo esc_attr($startTime); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="oh_end_time"><?php esc_html_e('Endzeit', 'eventeule'); ?></label></th>
                    <td>
                        <input type="time" id="oh_end_time" name="oh_end_time"
                               value="<?php echo esc_attr($endTime); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="oh_date_from"><?php esc_html_e('Gültig ab', 'eventeule'); ?></label></th>
                    <td>
                        <input type="date" id="oh_date_from" name="oh_date_from"
                               value="<?php echo esc_attr($dateFrom); ?>">
                        <p class="description"><?php esc_html_e('Frühestes Datum für generierte Events (leer = heute)', 'eventeule'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="oh_date_until"><?php esc_html_e('Gültig bis', 'eventeule'); ?></label></th>
                    <td>
                        <input type="date" id="oh_date_until" name="oh_date_until"
                               value="<?php echo esc_attr($dateUntil); ?>">
                        <p class="description"><?php esc_html_e('Leer lassen für unbegrenzte Wiederholung.', 'eventeule'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="oh_horizon_weeks"><?php esc_html_e('Vorausgenerierung (Wochen)', 'eventeule'); ?></label></th>
                    <td>
                        <input type="number" id="oh_horizon_weeks" name="oh_horizon_weeks"
                               value="<?php echo esc_attr($horizonWeeks); ?>"
                               min="1" max="52" style="width:80px;">
                        <p class="description"><?php esc_html_e('Wie viele Wochen in die Zukunft sollen Events automatisch generiert werden?', 'eventeule'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    // ── Event meta box ────────────────────────────────────────────────────

    public function render_event_meta_box(\WP_Post $post): void
    {
        $location  = (string) get_post_meta($post->ID, '_eventeule_location', true);
        $url       = (string) get_post_meta($post->ID, '_eventeule_registration_url', true);
        $shortDesc = (string) get_post_meta($post->ID, '_eventeule_short_description', true);
        $price     = (string) get_post_meta($post->ID, '_eventeule_price', true);
        $note      = (string) get_post_meta($post->ID, '_eventeule_note', true);
        $featured  = (string) get_post_meta($post->ID, '_eventeule_featured', true);
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="oh_ev_location"><?php esc_html_e('Ort', 'eventeule'); ?></label></th>
                    <td>
                        <input type="text" class="regular-text" id="oh_ev_location" name="oh_ev_location"
                               value="<?php echo esc_attr($location); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="oh_ev_url"><?php esc_html_e('Anmeldungs-URL', 'eventeule'); ?></label></th>
                    <td>
                        <input type="url" class="regular-text" id="oh_ev_url" name="oh_ev_url"
                               value="<?php echo esc_attr($url); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="oh_ev_short_desc"><?php esc_html_e('Kurzbeschreibung', 'eventeule'); ?></label></th>
                    <td>
                        <textarea id="oh_ev_short_desc" name="oh_ev_short_desc" rows="2" class="large-text"><?php echo esc_textarea($shortDesc); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="oh_ev_price"><?php esc_html_e('Preis', 'eventeule'); ?></label></th>
                    <td>
                        <input type="text" class="regular-text" id="oh_ev_price" name="oh_ev_price"
                               value="<?php echo esc_attr($price); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="oh_ev_note"><?php esc_html_e('Hinweis', 'eventeule'); ?></label></th>
                    <td>
                        <textarea id="oh_ev_note" name="oh_ev_note" rows="3" style="width:100%;"><?php echo esc_textarea($note); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Hervorheben', 'eventeule'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="oh_ev_featured" value="1" <?php checked($featured, '1'); ?>>
                            <?php esc_html_e('Als hervorgehobene Veranstaltung markieren', 'eventeule'); ?>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    // ── Save ──────────────────────────────────────────────────────────────

    public function save_meta_boxes(int $postId): void
    {
        if (!isset($_POST[self::NONCE_NAME])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (get_post_type($postId) !== OpeningHoursPostType::POST_TYPE) {
            return;
        }

        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Days of week (array of ints 1-7)
        $rawDays = isset($_POST['oh_days_of_week']) && is_array($_POST['oh_days_of_week'])
            ? array_map('intval', $_POST['oh_days_of_week'])
            : [];
        $daysOfWeek = array_values(array_filter($rawDays, fn($d) => $d >= 1 && $d <= 7));
        update_post_meta($postId, '_oh_days_of_week', $daysOfWeek);

        // Time fields
        $this->save_time_meta($postId, '_oh_start_time', 'oh_start_time');
        $this->save_time_meta($postId, '_oh_end_time',   'oh_end_time');

        // Date fields
        $this->save_date_meta($postId, '_oh_date_from',  'oh_date_from');
        $this->save_date_meta($postId, '_oh_date_until', 'oh_date_until');

        // Horizon weeks
        $horizonWeeks = isset($_POST['oh_horizon_weeks']) ? max(1, min(52, (int) $_POST['oh_horizon_weeks'])) : 8;
        update_post_meta($postId, '_oh_horizon_weeks', $horizonWeeks);

        // Event meta fields
        $this->save_text_meta($postId, '_eventeule_location',          'oh_ev_location');
        $this->save_url_meta($postId,  '_eventeule_registration_url',  'oh_ev_url');
        $this->save_textarea_meta($postId, '_eventeule_short_description', 'oh_ev_short_desc');
        $this->save_text_meta($postId, '_eventeule_price',             'oh_ev_price');
        $this->save_textarea_meta($postId, '_eventeule_note',          'oh_ev_note');
        $this->save_checkbox_meta($postId, '_eventeule_featured',      'oh_ev_featured');

        // Trigger event generation after save (only on publish)
        if (get_post_status($postId) === 'publish' && !empty($daysOfWeek)) {
            (new OpeningHoursGenerator())->generate_for_schedule($postId);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function save_time_meta(int $postId, string $metaKey, string $fieldName): void
    {
        if (empty($_POST[$fieldName])) {
            delete_post_meta($postId, $metaKey);
            return;
        }

        $value = sanitize_text_field(wp_unslash($_POST[$fieldName]));
        // Validate HH:MM format
        if (!preg_match('/^\d{2}:\d{2}$/', $value)) {
            return;
        }

        update_post_meta($postId, $metaKey, $value);
    }

    private function save_date_meta(int $postId, string $metaKey, string $fieldName): void
    {
        if (empty($_POST[$fieldName])) {
            delete_post_meta($postId, $metaKey);
            return;
        }

        $value = sanitize_text_field(wp_unslash($_POST[$fieldName]));
        // Validate Y-m-d format
        $dt = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$dt || $dt->format('Y-m-d') !== $value) {
            return;
        }

        update_post_meta($postId, $metaKey, $value);
    }

    private function save_text_meta(int $postId, string $metaKey, string $fieldName): void
    {
        if (!isset($_POST[$fieldName]) || $_POST[$fieldName] === '') {
            delete_post_meta($postId, $metaKey);
            return;
        }
        update_post_meta($postId, $metaKey, sanitize_text_field(wp_unslash($_POST[$fieldName])));
    }

    private function save_url_meta(int $postId, string $metaKey, string $fieldName): void
    {
        if (!isset($_POST[$fieldName]) || $_POST[$fieldName] === '') {
            delete_post_meta($postId, $metaKey);
            return;
        }
        update_post_meta($postId, $metaKey, esc_url_raw(wp_unslash($_POST[$fieldName])));
    }

    private function save_textarea_meta(int $postId, string $metaKey, string $fieldName): void
    {
        if (!isset($_POST[$fieldName]) || $_POST[$fieldName] === '') {
            delete_post_meta($postId, $metaKey);
            return;
        }
        update_post_meta($postId, $metaKey, sanitize_textarea_field(wp_unslash($_POST[$fieldName])));
    }

    private function save_checkbox_meta(int $postId, string $metaKey, string $fieldName): void
    {
        if (!empty($_POST[$fieldName])) {
            update_post_meta($postId, $metaKey, '1');
        } else {
            delete_post_meta($postId, $metaKey);
        }
    }
}
