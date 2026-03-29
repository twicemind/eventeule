<?php

namespace EventEule\Frontend;

use EventEule\Repository\EventRepository;

class Frontend
{
    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_shortcode('eventeule_events', [$this, 'render_events_shortcode']);
    }

    public function enqueue_assets(): void
    {
        wp_enqueue_style(
            'eventeule-public',
            EVENTEULE_URL . 'assets/css/public.css',
            [],
            EVENTEULE_VERSION
        );

        wp_enqueue_script(
            'eventeule-public',
            EVENTEULE_URL . 'assets/js/public.js',
            [],
            EVENTEULE_VERSION,
            true
        );
    }

    /**
     * @param array<string, string> $atts
     */
    public function render_events_shortcode(array $atts = []): string
    {
        $atts = shortcode_atts([
            'limit'         => 10,
            'featured_only' => 'false',
            'show_past'     => 'false',
            'category'      => '',
        ], $atts, 'eventeule_events');

        $repository = new EventRepository();

        $events = $repository->get_events([
            'limit'         => (int) $atts['limit'],
            'featured_only' => filter_var($atts['featured_only'], FILTER_VALIDATE_BOOLEAN),
            'show_past'     => filter_var($atts['show_past'], FILTER_VALIDATE_BOOLEAN),
            'category'      => sanitize_title($atts['category']),
        ]);

        $eventData = array_map(
            static fn(\WP_Post $post): array => $repository->get_event_data($post),
            $events
        );

        ob_start();

        $template = EVENTEULE_PATH . 'templates/public/events-list.php';

        if (file_exists($template)) {
            $events = $eventData;
            include $template;
        }

        return (string) ob_get_clean();
    }
}