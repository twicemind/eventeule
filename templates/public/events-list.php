<?php
/**
 * @var array<int, array<string, mixed>> $events
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('eventeule_format_event_date')) {
    function eventeule_format_event_date(string $startDate, string $endDate = ''): string
    {
        if ($startDate === '') {
            return '';
        }

        $startTimestamp = strtotime($startDate);
        $endTimestamp   = $endDate !== '' ? strtotime($endDate) : false;

        if ($startTimestamp === false) {
            return esc_html($startDate);
        }

        $formattedStart = wp_date('d.m.Y', $startTimestamp);

        if ($endTimestamp !== false && $endDate !== '' && $endDate !== $startDate) {
            return $formattedStart . ' – ' . wp_date('d.m.Y', $endTimestamp);
        }

        return $formattedStart;
    }
}

if (!function_exists('eventeule_format_event_time')) {
    function eventeule_format_event_time(string $startTime, string $endTime = ''): string
    {
        if ($startTime === '') {
            return '';
        }

        if ($endTime !== '') {
            return $startTime . ' – ' . $endTime;
        }

        return $startTime;
    }
}
?>

<div class="ee-list">
    <?php if (empty($events)) : ?>
        <p class="ee-list__empty"><?php esc_html_e('Aktuell sind keine Veranstaltungen verfügbar.', 'eventeule'); ?></p>
    <?php else : ?>
        <?php foreach ($events as $event) :
            $startDate  = (string) ($event['start_date'] ?? '');
            $endDate    = (string) ($event['end_date'] ?? '');
            $startTime  = (string) ($event['start_time'] ?? '');
            $endTime    = (string) ($event['end_time'] ?? '');
            $location   = (string) ($event['location'] ?? '');
            $permalink  = (string) ($event['permalink'] ?? '');
            $title      = (string) ($event['title'] ?? '');
            $excerpt    = (string) ($event['excerpt'] ?? '');
            $note       = (string) ($event['note'] ?? '');
            $regUrl     = (string) ($event['registration_url'] ?? '');
            $featured   = !empty($event['featured']);
            $isCancelled = !empty($event['cancelled']);
            $postId     = (int) ($event['id'] ?? 0);
            $hasThumb   = $postId > 0 && has_post_thumbnail($postId);

            // Date badge parts
            $dayNum   = $startDate !== '' ? wp_date('d', strtotime($startDate))   : '';
            $monthAbb = $startDate !== '' ? wp_date('M', strtotime($startDate))   : '';
            $year     = $startDate !== '' ? wp_date('Y', strtotime($startDate))   : '';
            $weekday  = $startDate !== '' ? wp_date('D', strtotime($startDate))   : '';

            $cardClass = 'ee-card';
            if ($featured)   { $cardClass .= ' ee-card--featured'; }
            if ($isCancelled){ $cardClass .= ' ee-card--cancelled'; }
        ?>
            <article class="<?php echo esc_attr($cardClass); ?>">

                <?php if ($hasThumb): ?>
                    <a class="ee-card__thumb" href="<?php echo esc_url($permalink); ?>" tabindex="-1" aria-hidden="true">
                        <?php echo get_the_post_thumbnail($postId, 'medium', ['class' => 'ee-card__img', 'alt' => '']); ?>
                    </a>
                <?php endif; ?>

                <div class="ee-card__body">

                    <!-- Categories + badges -->
                    <div class="ee-card__top">
                        <?php if (!empty($event['categories']) && is_array($event['categories'])): ?>
                            <?php foreach ($event['categories'] as $term): ?>
                                <?php if ($term instanceof \WP_Term): ?>
                                    <span class="ee-tag"><?php echo esc_html($term->name); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if ($featured && !$isCancelled): ?>
                            <span class="ee-tag ee-tag--featured">&#9733; <?php esc_html_e('Highlight', 'eventeule'); ?></span>
                        <?php endif; ?>
                        <?php if ($isCancelled): ?>
                            <span class="ee-tag ee-tag--cancelled"><?php esc_html_e('Abgesagt', 'eventeule'); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Title -->
                    <h3 class="ee-card__title">
                        <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                    </h3>

                    <!-- Meta row -->
                    <ul class="ee-card__meta">
                        <?php if ($location !== ''): ?>
                            <li>
                                <svg class="ee-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M10 2a6 6 0 0 1 6 6c0 4-6 10-6 10S4 12 4 8a6 6 0 0 1 6-6z"/><circle cx="10" cy="8" r="2"/></svg>
                                <?php echo esc_html($location); ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($startDate !== ''): ?>
                            <li>
                                <svg class="ee-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="14" height="13" rx="2"/><path d="M3 8h14M7 2v3M13 2v3"/></svg>
                                <?php echo esc_html(eventeule_format_event_date($startDate, $endDate)); ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($startTime !== ''): ?>
                            <li>
                                <svg class="ee-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="10" cy="10" r="7"/><path d="M10 6v4l2.5 2.5"/></svg>
                                <?php echo esc_html($startTime); ?><?php if ($endTime !== ''): ?> – <?php echo esc_html($endTime); ?><?php endif; ?>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Excerpt -->
                    <?php if ($excerpt !== ''): ?>
                        <p class="ee-card__excerpt"><?php echo wp_kses_post($excerpt); ?></p>
                    <?php endif; ?>

                    <!-- Note -->
                    <?php if ($note !== ''): ?>
                        <div class="ee-card__note"><?php echo esc_html($note); ?></div>
                    <?php endif; ?>

                    <!-- Actions (always last / pinned to bottom) -->
                    <div class="ee-card__actions">
                        <a class="ee-btn ee-btn--ghost" href="<?php echo esc_url($permalink); ?>">
                            <?php esc_html_e('Weiterlesen', 'eventeule'); ?>
                            <svg class="ee-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h12M12 6l4 4-4 4"/></svg>
                        </a>
                        <?php if ($regUrl !== '' && !$isCancelled): ?>
                            <a class="ee-btn ee-btn--primary" href="<?php echo esc_url($regUrl); ?>" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e('Jetzt anmelden', 'eventeule'); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                </div><!-- .ee-card__body -->

            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<div class="eventeule-events">
    <?php if (empty($events)) : ?>
        <p><?php esc_html_e('There are currently no events available.', 'eventeule'); ?></p>
    <?php else : ?>
        <?php foreach ($events as $event) : ?>
            <article class="eventeule-event<?php echo !empty($event['featured']) ? ' is-featured' : ''; ?>">
                <header class="eventeule-event__header">
                    <div>
                        <h3 class="eventeule-event__title">
                            <a href="<?php echo esc_url((string) $event['permalink']); ?>">
                                <?php echo esc_html((string) $event['title']); ?>
                            </a>
                        </h3>

                        <?php if (!empty($event['categories']) && is_array($event['categories'])) : ?>
                            <div class="eventeule-event__categories">
                                <?php foreach ($event['categories'] as $term) : ?>
                                    <?php if ($term instanceof \WP_Term) : ?>
                                        <span class="eventeule-event__category">
                                            <?php echo esc_html($term->name); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($event['featured'])) : ?>
                        <span class="eventeule-event__badge">
                            <?php esc_html_e('Featured', 'eventeule'); ?>
                        </span>
                    <?php endif; ?>
                </header>

                <div class="eventeule-event__meta">
                    <?php if (!empty($event['start_date'])) : ?>
                        <p class="eventeule-event__meta-item">
                            <strong><?php esc_html_e('Date:', 'eventeule'); ?></strong>
                            <?php echo esc_html(eventeule_format_event_date((string) $event['start_date'], (string) $event['end_date'])); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($event['start_time'])) : ?>
                        <p class="eventeule-event__meta-item">
                            <strong><?php esc_html_e('Time:', 'eventeule'); ?></strong>
                            <?php echo esc_html(eventeule_format_event_time((string) $event['start_time'], (string) $event['end_time'])); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($event['location'])) : ?>
                        <p class="eventeule-event__meta-item">
                            <strong><?php esc_html_e('Location:', 'eventeule'); ?></strong>
                            <?php echo esc_html((string) $event['location']); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($event['excerpt'])) : ?>
                    <div class="eventeule-event__excerpt">
                        <?php echo wp_kses_post(wpautop((string) $event['excerpt'])); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($event['note'])) : ?>
                    <div class="eventeule-event__note">
                        <strong><?php esc_html_e('Note:', 'eventeule'); ?></strong>
                        <?php echo esc_html((string) $event['note']); ?>
                    </div>
                <?php endif; ?>

                <div class="eventeule-event__actions">
                    <a class="eventeule-event__link" href="<?php echo esc_url((string) $event['permalink']); ?>">
                        <?php esc_html_e('Learn more', 'eventeule'); ?>
                    </a>

                    <?php if (!empty($event['registration_url'])) : ?>
                        <a class="eventeule-event__link eventeule-event__link--primary" href="<?php echo esc_url((string) $event['registration_url']); ?>" target="_blank" rel="noopener noreferrer">
                            <?php esc_html_e('Register now', 'eventeule'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>