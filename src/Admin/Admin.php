<?php

namespace EventEule\Admin;

use EventEule\Domain\EventPostType;
use EventEule\Domain\OpeningHoursGenerator;
use EventEule\Domain\OpeningHoursPostType;
use EventEule\Registration\RegistrationRepository;
use EventEule\Repository\EventRepository;

class Admin
{
    private EventRepository $eventRepository;
    private RegistrationRepository $registrationRepository;

    public function __construct(EventRepository $eventRepository, RegistrationRepository $registrationRepository)
    {
        $this->eventRepository        = $eventRepository;
        $this->registrationRepository = $registrationRepository;
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_eventeule_save_settings', [$this, 'save_settings']);
        add_action('admin_notices', [$this, 'hide_other_plugin_notices']);
        // Redirect legacy standalone pages into the in-app sections
        add_action('admin_init', [$this, 'redirect_legacy_pages']);
        // AJAX: cancel / restore a single opening-hours occurrence
        add_action('admin_post_eventeule_cancel_opening_date',   [$this, 'handle_cancel_opening_date']);
        add_action('admin_post_eventeule_restore_opening_date',  [$this, 'handle_restore_opening_date']);
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

        // Hide native taxonomy + registrations submenu from the sidebar —
        // they now live inside the EventEule app under nav=kategorien / nav=anmeldungen.
        add_action('admin_head', function () {
            echo '<style>
                #menu-posts-eventeule_event .wp-submenu a[href*="taxonomy=eventeule_category"],
                #menu-posts-eventeule_event .wp-submenu a[href*="page=eventeule-registrations"] {
                    display: none !important;
                }
            </style>';
        });
    }

    /**
     * Redirect the legacy standalone Anmeldungen and Kategorien pages into
     * the unified in-app sections so old bookmarks/links still work.
     */
    public function redirect_legacy_pages(): void
    {
        if (!is_admin() || !isset($_GET['page']) && !isset($_GET['taxonomy'])) {
            return;
        }

        // eventeule-registrations → nav=anmeldungen
        if (isset($_GET['page']) && $_GET['page'] === 'eventeule-registrations') {
            $args = ['page' => 'eventeule', 'nav' => 'anmeldungen'];
            if (!empty($_GET['event_id'])) {
                $args['event_id'] = (int) $_GET['event_id'];
            }
            wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
            exit;
        }

        // edit-tags.php?taxonomy=eventeule_category → nav=kategorien
        // Preserve WP message codes (1=added, 3=deleted, 6=updated) so our flash notice works.
        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'eventeule_category'
            && isset($_SERVER['PHP_SELF'])
            && str_contains((string) $_SERVER['PHP_SELF'], 'edit-tags.php')
        ) {
            $args = ['page' => 'eventeule', 'nav' => 'kategorien'];
            if (!empty($_GET['message'])) {
                $args['message'] = (int) $_GET['message'];
            }
            wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
            exit;
        }
    }

    public function render_page(): void
    {
        $activeSection = isset($_GET['nav']) ? sanitize_text_field($_GET['nav']) : 'dashboard';

        $stats = $this->get_statistics();
        $upcomingEvents = $this->eventRepository->get_events([
            'limit' => 5,
            'show_past' => false,
        ]);

        // Get all events for calendar view
        $calendarEvents = $this->get_calendar_events();

        // Saved settings
        $settings = $this->get_settings();

        // Only query GitHub when the Einstellungen section is active
        $latestGithubVersion = ($activeSection === 'einstellungen') ? $this->get_latest_github_version() : null;

        // Veranstaltungen section: view + filter data
        $evtView     = isset($_GET['evtview']) ? sanitize_text_field($_GET['evtview']) : 'all';
        $evtCategory = isset($_GET['evtcat'])  ? sanitize_text_field($_GET['evtcat'])  : '';

        $allCategories = get_terms([
            'taxonomy'   => 'eventeule_category',
            'hide_empty' => false,
            'orderby'    => 'name',
        ]);
        if (is_wp_error($allCategories)) {
            $allCategories = [];
        }

        $evtQueryArgs = ['limit' => -1, 'show_past' => true];
        if ($evtCategory !== '') {
            $evtQueryArgs['category'] = $evtCategory;
        }
        if ($evtView === 'upcoming') {
            $evtQueryArgs['show_past'] = false;
        }

        $allEventsForSection = $this->get_all_events_for_section($evtQueryArgs, $evtView);

        // ── Anmeldungen section ────────────────────────────────────────────
        $regEventId   = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
        $regPaged     = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
        $regPerPage   = 25;
        $regOffset    = ($regPaged - 1) * $regPerPage;

        if ($activeSection === 'anmeldungen') {
            if ($regEventId > 0) {
                $registrations    = $this->registrationRepository->get_all_by_event($regEventId, $regPerPage, $regOffset);
                $regTotal         = $this->registrationRepository->count_all_by_event($regEventId);
                $regEventTitle    = (string) get_the_title($regEventId);
            } else {
                $registrations    = $this->registrationRepository->get_all($regPerPage, $regOffset);
                $regTotal         = $this->registrationRepository->count_all();
                $regEventTitle    = '';
            }
            $regTotalPages = (int) ceil($regTotal / $regPerPage);
            $regIsCancelled = $regEventId > 0 && get_post_meta($regEventId, '_eventeule_cancelled', true) === '1';

            $regEventsQuery = new \WP_Query([
                'post_type'      => EventPostType::POST_TYPE,
                'post_status'    => ['publish', 'draft'],
                'posts_per_page' => -1,
                'meta_key'       => '_eventeule_reg_enabled',
                'meta_value'     => '1',
                'orderby'        => 'title',
                'order'          => 'ASC',
                'fields'         => 'ids',
            ]);
            $registrationEvents = $regEventsQuery->posts;

            // Flash messages
            $regNotice     = '';
            $regNoticeType = 'success';
            if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
                $regNotice = __('Anmeldung wurde gelöscht.', 'eventeule');
            } elseif (isset($_GET['replied']) && $_GET['replied'] === '1') {
                $regNotice = __('Antwort wurde erfolgreich versendet.', 'eventeule');
            } elseif (isset($_GET['cancelled']) && $_GET['cancelled'] === '1') {
                $regNotice = __('Veranstaltung wurde abgesagt. Die Teilnehmer/-innen wurden per E-Mail informiert.', 'eventeule');
            } elseif (isset($_GET['reply_error'])) {
                $regNotice     = __('Fehler beim Senden der Antwort. Bitte prüfe die E-Mail-Adresse.', 'eventeule');
                $regNoticeType = 'error';
            }
        } else {
            $registrations      = [];
            $regTotal           = 0;
            $regTotalPages      = 0;
            $regEventTitle      = '';
            $regIsCancelled     = false;
            $registrationEvents = [];
            $regNotice          = '';
            $regNoticeType      = 'success';
        }

        // Pass registration repository to template
        $registrationRepository = $this->registrationRepository;

        // ── Öffnungszeiten section ─────────────────────────────────────────
        $openingSchedules = [];
        if ($activeSection === 'oeffnungszeiten') {
            $schQuery = new \WP_Query([
                'post_type'      => OpeningHoursPostType::POST_TYPE,
                'post_status'    => ['publish', 'draft'],
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]);
            $openingSchedules = $schQuery->posts;
        }

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
     * Renders a single calendar-view event row and returns the HTML string.
     * Called from the template via $this->render_event_row($evd).
     *
     * @param array<string, mixed> $evd  Result of EventRepository::get_event_data()
     */
    public function render_event_row(array $evd): string
    {
        $isCancelled = get_post_meta((int) $evd['id'], '_eventeule_cancelled', true) === '1';
        $isPast      = !empty($evd['start_date']) && strtotime($evd['start_date']) < strtotime(current_time('Y-m-d'));

        ob_start();
        ?>
        <div class="eventeule-calendar-event<?php echo $isPast ? ' is-past' : ''; ?><?php echo $isCancelled ? ' is-cancelled' : ''; ?>">
            <div class="eventeule-calendar-date">
                <div class="eventeule-calendar-day"><?php echo esc_html(date_i18n('d', strtotime($evd['start_date']))); ?></div>
                <div class="eventeule-calendar-weekday"><?php echo esc_html(date_i18n('D', strtotime($evd['start_date']))); ?></div>
            </div>
            <div class="eventeule-calendar-info">
                <h4>
                    <?php echo esc_html($evd['title']); ?>
                    <?php if ($evd['featured']): ?>
                        <span class="dashicons dashicons-star-filled" style="color:#f0b849;"></span>
                    <?php endif; ?>
                    <?php if ($isCancelled): ?>
                        <span class="ee-badge ee-badge--cancelled" style="margin-left:6px;"><?php esc_html_e('Abgesagt', 'eventeule'); ?></span>
                    <?php elseif ($isPast): ?>
                        <span class="ee-badge ee-badge--past" style="margin-left:6px;"><?php esc_html_e('Vergangen', 'eventeule'); ?></span>
                    <?php endif; ?>
                </h4>
                <div class="eventeule-calendar-meta">
                    <?php if (!empty($evd['start_time'])): ?>
                        <span><span class="dashicons dashicons-clock"></span><?php echo esc_html($evd['start_time']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($evd['location'])): ?>
                        <span><span class="dashicons dashicons-location"></span><?php echo esc_html($evd['location']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="eventeule-calendar-actions">
                <a href="<?php echo esc_url(admin_url('post.php?post=' . (int) $evd['id'] . '&action=edit')); ?>"
                   class="button button-small">
                    <?php esc_html_e('Bearbeiten', 'eventeule'); ?>
                </a>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Returns events for the Veranstaltungen section.
     * For 'calendar' view it returns an array grouped by month.
     * For all other views it returns a flat WP_Post[].
     *
     * @param array<string, mixed> $args
     * @param string               $view
     * @return WP_Post[]|array<string, array>
     */
    private function get_all_events_for_section(array $args, string $view): array
    {
        $queryArgs = [
            'post_type'   => EventPostType::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key'    => '_eventeule_start_date',
            'orderby'     => 'meta_value',
            'order'       => ($view === 'all') ? 'DESC' : 'ASC',
        ];

        if (!($args['show_past'] ?? true)) {
            $queryArgs['meta_query'] = [[
                'key'     => '_eventeule_start_date',
                'value'   => current_time('Y-m-d'),
                'compare' => '>=',
                'type'    => 'DATE',
            ]];
        }

        if (!empty($args['category'])) {
            $queryArgs['tax_query'] = [[
                'taxonomy' => 'eventeule_category',
                'field'    => 'slug',
                'terms'    => (string) $args['category'],
            ]];
        }

        $query = new \WP_Query($queryArgs);
        $posts = $query->posts;

        if ($view === 'calendar') {
            $grouped = [];
            foreach ($posts as $post) {
                $startDate = get_post_meta($post->ID, '_eventeule_start_date', true);
                if (empty($startDate)) {
                    continue;
                }
                $month = date('Y-m', strtotime($startDate));
                if (!isset($grouped[$month])) {
                    $grouped[$month] = [];
                }
                $grouped[$month][] = $this->eventRepository->get_event_data($post);
            }
            return $grouped;
        }

        return $posts;
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

    public function handle_cancel_opening_date(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }

        check_admin_referer('eventeule_cancel_opening_date');

        $scheduleId = isset($_POST['schedule_id']) ? (int) $_POST['schedule_id'] : 0;
        $date       = isset($_POST['date'])        ? sanitize_text_field(wp_unslash($_POST['date'])) : '';

        if ($scheduleId > 0 && $date !== '') {
            (new OpeningHoursGenerator())->exclude_date($scheduleId, $date);
        }

        wp_safe_redirect(add_query_arg([
            'page'       => 'eventeule',
            'nav'        => 'oeffnungszeiten',
            'schedule'   => $scheduleId,
            'cancelled'  => '1',
        ], admin_url('admin.php')));
        exit;
    }

    public function handle_restore_opening_date(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }

        check_admin_referer('eventeule_restore_opening_date');

        $scheduleId = isset($_POST['schedule_id']) ? (int) $_POST['schedule_id'] : 0;
        $date       = isset($_POST['date'])        ? sanitize_text_field(wp_unslash($_POST['date'])) : '';

        if ($scheduleId > 0 && $date !== '') {
            (new OpeningHoursGenerator())->include_date($scheduleId, $date);
        }

        wp_safe_redirect(add_query_arg([
            'page'      => 'eventeule',
            'nav'       => 'oeffnungszeiten',
            'schedule'  => $scheduleId,
            'restored'  => '1',
        ], admin_url('admin.php')));
        exit;
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
            'page'    => 'eventeule',
            'nav'     => 'einstellungen',
            'message' => 'saved',
        ], admin_url('admin.php')));
        exit;
    }
}