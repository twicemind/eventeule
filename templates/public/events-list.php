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