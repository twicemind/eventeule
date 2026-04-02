<?php

namespace EventEule\Admin;

use EventEule\Domain\EventPostType;
use EventEule\Repository\EventRepository;

class Admin
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_eventeule_save_settings', [$this, 'save_settings']);
        add_action('admin_notices', [$this, 'hide_other_plugin_notices']);
    }

    /**
     * Hide notices from other plugins on EventEule pages
     */
    public function hide_other_plugin_notices(): void
    {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'eventeule') !== false) {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            // Re-add our own notice handler after removing others
            add_action('admin_notices', [$this, 'hide_other_plugin_notices']);
        }
    }

    public function register_menu(): void
    {
        add_menu_page(
            'EventEule',
            'EventEule',
            'manage_options',
            'eventeule',
            [$this, 'render_page'],
            'dashicons-calendar-alt',
            26
        );
    }

    public function render_page(): void
    {
        // Speichere Tab-Status
        $activeTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
        
        $stats = $this->get_statistics();
        $upcomingEvents = $this->eventRepository->get_events([
            'limit' => 5,
            'show_past' => false,
        ]);
        
        // Get all events for calendar view
        $calendarEvents = $this->get_calendar_events();
        
        // Hole gespeicherte Einstellungen
        $settings = $this->get_settings();

        // Only query GitHub when the Updates tab is active
        $latestGithubVersion = ($activeTab === 'updates') ? $this->get_latest_github_version() : null;

        $template = EVENTEULE_PATH . 'templates/admin/dashboard.php';

        if (file_exists($template)) {
            include $template;
        }
    }

    /**
     * @return array<string, int>
     */
    private function get_statistics(): array
    {
        $allEvents = wp_count_posts(EventPostType::POST_TYPE);
        
        $upcomingCount = $this->count_upcoming_events();
        $pastCount = $this->count_past_events();
        $featuredCount = $this->count_featured_events();
        
        return [
            'total' => $allEvents->publish ?? 0,
            'upcoming' => $upcomingCount,
            'past' => $pastCount,
            'featured' => $featuredCount,
        ];
    }

    private function count_upcoming_events(): int
    {
        $query = new \WP_Query([
            'post_type' => EventPostType::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => '_eventeule_start_date',
                'value' => current_time('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE',
            ]],
            'fields' => 'ids',
        ]);
        
        return $query->found_posts;
    }

    private function count_past_events(): int
    {
        $query = new \WP_Query([
            'post_type' => EventPostType::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => '_eventeule_start_date',
                'value' => current_time('Y-m-d'),
                'compare' => '<',
                'type' => 'DATE',
            ]],
            'fields' => 'ids',
        ]);
        
        return $query->found_posts;
    }

    private function count_featured_events(): int
    {
        $query = new \WP_Query([
            'post_type' => EventPostType::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [[
                'key' => '_eventeule_featured',
                'value' => '1',
                'compare' => '=',
            ]],
            'fields' => 'ids',
        ]);
        
        return $query->found_posts;
    }

    public function enqueue_assets(string $hook): void
    {
        if ($hook !== 'toplevel_page_eventeule') {
            return;
        }

        wp_enqueue_style(
            'eventeule-admin',
            EVENTEULE_URL . 'assets/css/admin.css',
            [],
            EVENTEULE_VERSION
        );

        wp_enqueue_script(
            'eventeule-admin',
            EVENTEULE_URL . 'assets/js/admin.js',
            ['jquery'],
            EVENTEULE_VERSION,
            true
        );
        
        wp_localize_script('eventeule-admin', 'eventeuleAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eventeule_admin'),
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function get_calendar_events(): array
    {
        $query = new \WP_Query([
            'post_type' => EventPostType::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => '_eventeule_start_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
        ]);

        $eventsByMonth = [];
        
        foreach ($query->posts as $post) {
            $startDate = get_post_meta($post->ID, '_eventeule_start_date', true);
            if (empty($startDate)) {
                continue;
            }
            
            $month = date('Y-m', strtotime($startDate));
            
            if (!isset($eventsByMonth[$month])) {
                $eventsByMonth[$month] = [];
            }
            
            $eventsByMonth[$month][] = $this->eventRepository->get_event_data($post);
        }
        
        return $eventsByMonth;
    }

    public function register_settings(): void
    {
        register_setting('eventeule_settings', 'eventeule_widget_colors');
    }

    /**
     * @return array<string, string>
     */
    private function get_settings(): array
    {
        $defaults = [
            'primary_color' => '#2271b1',
            'secondary_color' => '#135e96',
            'accent_color' => '#f0b849',
            'text_color' => '#1d2327',
            'background_color' => '#ffffff',
            'border_color' => '#dcdcde',
        ];

        $saved = get_option('eventeule_widget_colors', []);
        
        return wp_parse_args($saved, $defaults);
    }

    /**
     * Fetch the latest release tag from GitHub, cached in a transient for 1 hour.
     * Returns the version string (e.g. "2.6.1") or null on error.
     */
    private function get_latest_github_version(): ?string
    {
        $transient_key = 'eventeule_latest_github_version';

        // If the WordPress update transient already knows about a newer version
        // (e.g. from a manual check), trust that and skip/update our own cache.
        $wp_transient = get_site_transient('update_plugins');
        $plugin_file  = plugin_basename(EVENTEULE_FILE);
        $wp_version   = isset($wp_transient->response[$plugin_file]->new_version)
            ? (string) $wp_transient->response[$plugin_file]->new_version
            : null;

        $cached = get_transient($transient_key);
        $cached_version = ($cached !== false && $cached !== '') ? (string) $cached : null;

        // Pick the highest version we know about from any source
        $best = $cached_version;
        if ($wp_version !== null && ($best === null || version_compare($wp_version, $best, '>'))) {
            $best = $wp_version;
            // Update the transient so subsequent loads are also correct
            set_transient($transient_key, $best, HOUR_IN_SECONDS);
        }

        if ($cached !== false) {
            return $best;
        }

        $token = get_option('eventeule_github_token', '');
        $headers = [
            'User-Agent' => 'EventEule-WordPress-Plugin/' . EVENTEULE_VERSION,
            'Accept'     => 'application/vnd.github.v3+json',
        ];
        if (!empty($token)) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = wp_remote_get('https://api.github.com/repos/twicemind/eventeule/releases/latest', [
            'headers' => $headers,
            'timeout' => 10,
        ]);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            // Cache failure briefly so we don't hammer the API on every page load
            set_transient($transient_key, '', 5 * MINUTE_IN_SECONDS);
            return null;
        }

        $body    = json_decode(wp_remote_retrieve_body($response), true);
        $version = ltrim($body['tag_name'] ?? '', 'v');

        if (empty($version)) {
            set_transient($transient_key, '', 5 * MINUTE_IN_SECONDS);
            return null;
        }

        set_transient($transient_key, $version, HOUR_IN_SECONDS);
        return $version;
    }

    public function save_settings(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        check_admin_referer('eventeule_settings');

        $colors = [
            'primary_color' => sanitize_hex_color($_POST['primary_color'] ?? ''),
            'secondary_color' => sanitize_hex_color($_POST['secondary_color'] ?? ''),
            'accent_color' => sanitize_hex_color($_POST['accent_color'] ?? ''),
            'text_color' => sanitize_hex_color($_POST['text_color'] ?? ''),
            'background_color' => sanitize_hex_color($_POST['background_color'] ?? ''),
            'border_color' => sanitize_hex_color($_POST['border_color'] ?? ''),
        ];

        update_option('eventeule_widget_colors', $colors);

        wp_redirect(add_query_arg([
            'page' => 'eventeule',
            'tab' => 'settings',
            'message' => 'saved',
        ], admin_url('admin.php')));
        exit;
    }
}