<?php
/**
 * Admin dashboard template.
 *
 * @var string       $activeSection  'dashboard'|'veranstaltungen'|'einstellungen'|'system'
 * @var array        $stats
 * @var WP_Post[]    $upcomingEvents
 * @var array        $calendarEvents
 * @var array        $settings
 * @var string|null  $latestGithubVersion
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="ee-app">

    <!-- ══ SIDEBAR ══ -->
    <aside class="ee-sidebar">

        <div class="ee-sidebar-header">
            <span class="dashicons dashicons-calendar-alt"></span>
            <div>
                <strong>EventEule</strong>
                <span class="ee-sidebar-version">v<?php echo esc_html(EVENTEULE_VERSION); ?></span>
            </div>
        </div>

        <nav class="ee-sidebar-nav">
            <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule&nav=dashboard')); ?>"
               class="ee-nav-item<?php echo $activeSection === 'dashboard' ? ' is-active' : ''; ?>">
                <span class="dashicons dashicons-dashboard"></span>
                <span><?php esc_html_e('Dashboard', 'eventeule'); ?></span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule&nav=veranstaltungen')); ?>"
               class="ee-nav-item<?php echo $activeSection === 'veranstaltungen' ? ' is-active' : ''; ?>">
                <span class="dashicons dashicons-calendar-alt"></span>
                <span><?php esc_html_e('Veranstaltungen', 'eventeule'); ?></span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule&nav=einstellungen')); ?>"
               class="ee-nav-item<?php echo $activeSection === 'einstellungen' ? ' is-active' : ''; ?>">
                <span class="dashicons dashicons-admin-settings"></span>
                <span><?php esc_html_e('Einstellungen', 'eventeule'); ?></span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule&nav=system')); ?>"
               class="ee-nav-item<?php echo $activeSection === 'system' ? ' is-active' : ''; ?>">
                <span class="dashicons dashicons-admin-tools"></span>
                <span><?php esc_html_e('System', 'eventeule'); ?></span>
            </a>
        </nav>

        <div class="ee-sidebar-footer">
            <span class="dashicons dashicons-admin-generic"></span>
            <span>Bücherei Huisheim</span>
        </div>

    </aside>

    <!-- ══ MAIN CONTENT ══ -->
    <main class="ee-main">

        <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
            <div class="notice notice-success is-dismissible" style="margin:0 0 24px;">
                <p><?php esc_html_e('Settings saved successfully!', 'eventeule'); ?></p>
            </div>
        <?php endif; ?>

        <!-- ─── DASHBOARD ─── -->
        <?php if ($activeSection === 'dashboard'): ?>

            <div class="ee-section-header">
                <h1><?php esc_html_e('Dashboard', 'eventeule'); ?></h1>
                <p><?php esc_html_e('Event Management Made Easy', 'eventeule'); ?> &mdash; v<?php echo esc_html(EVENTEULE_VERSION); ?></p>
            </div>

            <div class="eventeule-stats">
                <div class="eventeule-stat-card eventeule-stat-total">
                    <div class="eventeule-stat-icon"><span class="dashicons dashicons-calendar-alt"></span></div>
                    <div class="eventeule-stat-content">
                        <h3><?php echo esc_html($stats['total']); ?></h3>
                        <p><?php esc_html_e('Total Events', 'eventeule'); ?></p>
                    </div>
                </div>
                <div class="eventeule-stat-card eventeule-stat-upcoming">
                    <div class="eventeule-stat-icon"><span class="dashicons dashicons-arrow-right-alt"></span></div>
                    <div class="eventeule-stat-content">
                        <h3><?php echo esc_html($stats['upcoming']); ?></h3>
                        <p><?php esc_html_e('Upcoming Events', 'eventeule'); ?></p>
                    </div>
                </div>
                <div class="eventeule-stat-card eventeule-stat-past">
                    <div class="eventeule-stat-icon"><span class="dashicons dashicons-backup"></span></div>
                    <div class="eventeule-stat-content">
                        <h3><?php echo esc_html($stats['past']); ?></h3>
                        <p><?php esc_html_e('Past Events', 'eventeule'); ?></p>
                    </div>
                </div>
                <div class="eventeule-stat-card eventeule-stat-featured">
                    <div class="eventeule-stat-icon"><span class="dashicons dashicons-star-filled"></span></div>
                    <div class="eventeule-stat-content">
                        <h3><?php echo esc_html($stats['featured']); ?></h3>
                        <p><?php esc_html_e('Featured Events', 'eventeule'); ?></p>
                    </div>
                </div>
            </div>

            <div class="eventeule-card">
                <h2><?php esc_html_e('Quick Actions', 'eventeule'); ?></h2>
                <div class="eventeule-action-grid">
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=eventeule_event')); ?>" class="eventeule-action-button primary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <span><?php esc_html_e('Add New Event', 'eventeule'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=eventeule_event')); ?>" class="eventeule-action-button">
                        <span class="dashicons dashicons-list-view"></span>
                        <span><?php esc_html_e('All Events', 'eventeule'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule-registrations')); ?>" class="eventeule-action-button">
                        <span class="dashicons dashicons-groups"></span>
                        <span><?php esc_html_e('Anmeldungen', 'eventeule'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=eventeule_category&post_type=eventeule_event')); ?>" class="eventeule-action-button">
                        <span class="dashicons dashicons-category"></span>
                        <span><?php esc_html_e('Manage Categories', 'eventeule'); ?></span>
                    </a>
                </div>
            </div>

            <div class="eventeule-card">
                <h2><?php esc_html_e('Upcoming Events', 'eventeule'); ?></h2>
                <?php if (empty($upcomingEvents)): ?>
                    <div class="eventeule-empty-state">
                        <span class="dashicons dashicons-calendar"></span>
                        <p><?php esc_html_e('No upcoming events found.', 'eventeule'); ?></p>
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=eventeule_event')); ?>" class="button button-primary">
                            <?php esc_html_e('Create Your First Event', 'eventeule'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="eventeule-events-list">
                        <?php foreach ($upcomingEvents as $event):
                            $eventData = $this->eventRepository->get_event_data($event); ?>
                            <div class="eventeule-event-item">
                                <div class="eventeule-event-date">
                                    <span class="eventeule-event-day"><?php echo esc_html(date_i18n('d', strtotime($eventData['start_date']))); ?></span>
                                    <span class="eventeule-event-month"><?php echo esc_html(date_i18n('M', strtotime($eventData['start_date']))); ?></span>
                                </div>
                                <div class="eventeule-event-info">
                                    <h4>
                                        <?php echo esc_html($eventData['title']); ?>
                                        <?php if ($eventData['featured']): ?>
                                            <span class="eventeule-badge-featured"><span class="dashicons dashicons-star-filled"></span></span>
                                        <?php endif; ?>
                                    </h4>
                                    <div class="eventeule-event-meta">
                                        <?php if (!empty($eventData['start_time'])): ?>
                                            <span><span class="dashicons dashicons-clock"></span><?php echo esc_html($eventData['start_time']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($eventData['location'])): ?>
                                            <span><span class="dashicons dashicons-location"></span><?php echo esc_html($eventData['location']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="eventeule-event-actions">
                                    <a href="<?php echo esc_url(get_edit_post_link($event->ID)); ?>" class="button"><?php esc_html_e('Edit', 'eventeule'); ?></a>
                                    <a href="<?php echo esc_url($eventData['permalink']); ?>" class="button" target="_blank"><?php esc_html_e('View', 'eventeule'); ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <!-- ─── VERANSTALTUNGEN ─── -->
        <?php elseif ($activeSection === 'veranstaltungen'): ?>

            <?php
            // View switcher values passed from Admin.php
            $evtView     = isset($evtView)     ? $evtView     : 'all';
            $evtCategory = isset($evtCategory) ? $evtCategory : '';
            $evtLabels   = [
                'all'      => __('Alle Veranstaltungen', 'eventeule'),
                'calendar' => __('Kalenderansicht', 'eventeule'),
                'upcoming' => __('Kommende', 'eventeule'),
                'category' => __('Nach Kategorie', 'eventeule'),
            ];
            ?>

            <div class="ee-section-header">
                <h1><?php esc_html_e('Veranstaltungen', 'eventeule'); ?></h1>
                <p><?php echo esc_html($evtLabels[$evtView] ?? $evtLabels['all']); ?></p>
            </div>

            <!-- Quick-Action-Bar -->
            <div class="ee-action-bar">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=eventeule_event')); ?>"
                   class="button button-primary">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e('Neue Veranstaltung', 'eventeule'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule-registrations')); ?>"
                   class="button">
                    <span class="dashicons dashicons-groups"></span>
                    <?php esc_html_e('Anmeldungen', 'eventeule'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=eventeule_category&post_type=eventeule_event')); ?>"
                   class="button">
                    <span class="dashicons dashicons-category"></span>
                    <?php esc_html_e('Kategorien', 'eventeule'); ?>
                </a>
            </div>

            <!-- View-Switcher + Category-Filter -->
            <div class="ee-view-bar">
                <?php foreach ($evtLabels as $key => $label): ?>
                    <a href="<?php echo esc_url(add_query_arg(['nav' => 'veranstaltungen', 'evtview' => $key, 'evtcat' => $evtCategory], admin_url('admin.php?page=eventeule'))); ?>"
                       class="ee-view-btn<?php echo $evtView === $key ? ' is-active' : ''; ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>

                <?php if (!empty($allCategories)): ?>
                    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>"
                          class="ee-cat-filter">
                        <input type="hidden" name="page" value="eventeule">
                        <input type="hidden" name="nav"  value="veranstaltungen">
                        <input type="hidden" name="evtview" value="<?php echo esc_attr($evtView); ?>">
                        <select name="evtcat" onchange="this.form.submit()">
                            <option value=""><?php esc_html_e('— Alle Kategorien —', 'eventeule'); ?></option>
                            <?php foreach ($allCategories as $cat): ?>
                                <option value="<?php echo esc_attr($cat->slug); ?>"
                                    <?php selected($evtCategory, $cat->slug); ?>>
                                    <?php echo esc_html($cat->name); ?>
                                    (<?php echo (int) $cat->count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (empty($allEventsForSection)): ?>
                <div class="eventeule-card">
                    <div class="eventeule-empty-state">
                        <span class="dashicons dashicons-calendar"></span>
                        <p><?php esc_html_e('Keine Veranstaltungen gefunden.', 'eventeule'); ?></p>
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=eventeule_event')); ?>"
                           class="button button-primary">
                            <?php esc_html_e('Erste Veranstaltung erstellen', 'eventeule'); ?>
                        </a>
                    </div>
                </div>

            <?php elseif ($evtView === 'calendar'): ?>
                <!-- ── Kalenderansicht ── -->
                <div class="eventeule-calendar">
                    <?php foreach ($allEventsForSection as $month => $events): ?>
                        <div class="eventeule-month-section">
                            <h3 class="eventeule-month-title">
                                <span class="dashicons dashicons-calendar-alt"></span>
                                <?php echo esc_html(date_i18n('F Y', strtotime($month . '-01'))); ?>
                                <span class="eventeule-month-count"><?php echo count($events); ?></span>
                            </h3>
                            <div class="eventeule-month-events">
                                <?php foreach ($events as $evd): ?>
                                    <?php echo $this->render_event_row($evd); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- ── Listen-/Upcoming-Ansicht ── -->
                <div class="ee-event-list">
                    <?php foreach ($allEventsForSection as $event):
                        $evd = $this->eventRepository->get_event_data($event);
                        $regCount   = $registrationRepository ? $registrationRepository->count_by_event($event->ID) : 0;
                        $isCancelled = get_post_meta($event->ID, '_eventeule_cancelled', true) === '1';
                        $isPast     = !empty($evd['start_date']) && strtotime($evd['start_date']) < strtotime(current_time('Y-m-d'));
                    ?>
                        <div class="ee-event-card<?php echo $isPast ? ' is-past' : ''; ?><?php echo $isCancelled ? ' is-cancelled' : ''; ?>">

                            <!-- Date badge -->
                            <div class="ee-event-card__date">
                                <?php if (!empty($evd['start_date'])): ?>
                                    <span class="ee-date-day"><?php echo esc_html(date_i18n('d', strtotime($evd['start_date']))); ?></span>
                                    <span class="ee-date-month"><?php echo esc_html(date_i18n('M', strtotime($evd['start_date']))); ?></span>
                                    <span class="ee-date-year"><?php echo esc_html(date_i18n('Y', strtotime($evd['start_date']))); ?></span>
                                <?php else: ?>
                                    <span class="ee-date-day">—</span>
                                <?php endif; ?>
                            </div>

                            <!-- Info -->
                            <div class="ee-event-card__info">
                                <div class="ee-event-card__title-row">
                                    <h3><?php echo esc_html($evd['title']); ?></h3>
                                    <div class="ee-event-card__badges">
                                        <?php if ($evd['featured']): ?>
                                            <span class="ee-badge ee-badge--featured">
                                                <span class="dashicons dashicons-star-filled"></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($isCancelled): ?>
                                            <span class="ee-badge ee-badge--cancelled"><?php esc_html_e('Abgesagt', 'eventeule'); ?></span>
                                        <?php elseif ($isPast): ?>
                                            <span class="ee-badge ee-badge--past"><?php esc_html_e('Vergangen', 'eventeule'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ee-event-card__meta">
                                    <?php if (!empty($evd['start_time'])): ?>
                                        <span><span class="dashicons dashicons-clock"></span><?php echo esc_html($evd['start_time']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($evd['location'])): ?>
                                        <span><span class="dashicons dashicons-location"></span><?php echo esc_html($evd['location']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($evd['categories'])): ?>
                                        <span><span class="dashicons dashicons-category"></span>
                                            <?php echo esc_html(implode(', ', array_map(fn($t) => $t->name, $evd['categories']))); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($regCount > 0): ?>
                                        <span>
                                            <span class="dashicons dashicons-groups"></span>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule-registrations&event_id=' . $event->ID)); ?>">
                                                <?php printf(
                                                    esc_html(_n('%d Anmeldung', '%d Anmeldungen', $regCount, 'eventeule')),
                                                    $regCount
                                                ); ?>
                                            </a>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="ee-event-card__actions">
                                <a href="<?php echo esc_url(get_edit_post_link($event->ID)); ?>"
                                   class="button button-small">
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php esc_html_e('Bearbeiten', 'eventeule'); ?>
                                </a>
                                <a href="<?php echo esc_url(get_permalink($event->ID)); ?>"
                                   class="button button-small" target="_blank">
                                    <span class="dashicons dashicons-external"></span>
                                    <?php esc_html_e('Ansehen', 'eventeule'); ?>
                                </a>
                                <?php if ($regCount > 0): ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule-registrations&event_id=' . $event->ID)); ?>"
                                       class="button button-small">
                                        <span class="dashicons dashicons-groups"></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <!-- ─── EINSTELLUNGEN ─── -->

            <div class="ee-section-header">
                <h1><?php esc_html_e('Settings', 'eventeule'); ?></h1>
                <p><?php esc_html_e('Widget colors, updates and further configuration', 'eventeule'); ?></p>
            </div>

            <?php if (isset($_GET['update-check'])): $cr = sanitize_text_field($_GET['update-check']); ?>
                <?php if ($cr === 'available' && !empty($_GET['version'])): ?>
                    <div class="notice notice-success is-dismissible" style="margin:0 0 20px;"><p><?php printf(esc_html__('Update found! Version %s is available. See below to install it.', 'eventeule'), '<strong>' . esc_html(sanitize_text_field($_GET['version'])) . '</strong>'); ?></p></div>
                <?php elseif ($cr === 'none'): ?>
                    <div class="notice notice-info is-dismissible" style="margin:0 0 20px;"><p><?php esc_html_e('Your plugin is up to date!', 'eventeule'); ?></p></div>
                <?php elseif ($cr === 'error'): ?>
                    <?php $ed = isset($_GET['error-detail']) ? sanitize_text_field($_GET['error-detail']) : ''; ?>
                    <div class="notice notice-error is-dismissible" style="margin:0 0 20px;"><p><?php echo esc_html(__('Error checking for updates.', 'eventeule') . (stripos($ed, 'rate limit') !== false ? ' ' . __('GitHub API rate limit exceeded. Please wait an hour or configure a GitHub token below to increase the limit.', 'eventeule') : ($ed ? ' ' . sprintf(__('Details: %s', 'eventeule'), $ed) : ''))); ?></p></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($_GET['direct-update'])): $dr = sanitize_text_field($_GET['direct-update']); ?>
                <?php if ($dr === 'success'): ?>
                    <div class="notice notice-success is-dismissible" style="margin:0 0 20px;"><p><strong><?php $iv = !empty($_GET['version']) ? sanitize_text_field($_GET['version']) : ''; echo $iv ? sprintf(esc_html__('Plugin successfully updated to version %s!', 'eventeule'), esc_html($iv)) : esc_html__('Plugin successfully updated!', 'eventeule'); ?></strong></p></div>
                <?php elseif ($dr === 'error'): ?>
                    <div class="notice notice-error is-dismissible" style="margin:0 0 20px;"><p><?php $ed = !empty($_GET['error-detail']) ? sanitize_text_field($_GET['error-detail']) : ''; echo esc_html(__('Direct update failed.', 'eventeule') . ($ed ? ' ' . $ed : '')); ?></p></div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="eventeule-card">
                <h2><?php esc_html_e('Widget Color Settings', 'eventeule'); ?></h2>
                <p class="description"><?php esc_html_e('Customize the colors for EventEule widgets on your website.', 'eventeule'); ?></p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="eventeule-settings-form">
                    <input type="hidden" name="action" value="eventeule_save_settings">
                    <?php wp_nonce_field('eventeule_settings'); ?>
                    <div class="eventeule-color-grid">
                        <?php $colorFields = [
                            'primary_color'    => [__('Primary Color', 'eventeule'),    __('Main color for buttons and highlights', 'eventeule')],
                            'secondary_color'  => [__('Secondary Color', 'eventeule'),  __('Hover states and accents', 'eventeule')],
                            'accent_color'     => [__('Accent Color', 'eventeule'),     __('Featured badges and special elements', 'eventeule')],
                            'text_color'       => [__('Text Color', 'eventeule'),       __('Main text color', 'eventeule')],
                            'background_color' => [__('Background Color', 'eventeule'), __('Widget background color', 'eventeule')],
                            'border_color'     => [__('Border Color', 'eventeule'),     __('Border and separator lines', 'eventeule')],
                        ];
                        foreach ($colorFields as $key => [$label, $desc]): ?>
                            <div class="eventeule-color-field">
                                <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label>
                                <div class="eventeule-color-input">
                                    <input type="color" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($settings[$key]); ?>">
                                    <input type="text" value="<?php echo esc_attr($settings[$key]); ?>" readonly>
                                </div>
                                <p class="description"><?php echo esc_html($desc); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="eventeule-form-actions">
                        <button type="submit" class="button button-primary button-large">
                            <span class="dashicons dashicons-saved"></span>
                            <?php esc_html_e('Save Settings', 'eventeule'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <div class="eventeule-card">
                <h2><span class="dashicons dashicons-update"></span> <?php esc_html_e('Plugin Updates', 'eventeule'); ?></h2>

                <div class="eventeule-section">
                    <h3><?php esc_html_e('Current Version', 'eventeule'); ?></h3>
                    <p class="eventeule-version-info"><code><?php echo esc_html(EVENTEULE_VERSION); ?></code></p>

                    <?php
                    $update_plugins = get_site_transient('update_plugins');
                    $plugin_file    = 'eventeule/EventEule.php';
                    $has_update     = false;
                    $new_version    = '';
                    if (isset($_GET['update-check']) && sanitize_text_field($_GET['update-check']) === 'available' && !empty($_GET['version'])) {
                        $has_update = true; $new_version = sanitize_text_field($_GET['version']);
                    } elseif (isset($update_plugins->response[$plugin_file])) {
                        $has_update = true; $new_version = $update_plugins->response[$plugin_file]->new_version;
                    }
                    if ($has_update): ?>
                        <div class="notice notice-warning inline" style="margin:15px 0; padding:12px;">
                            <p style="margin:0;"><strong><?php esc_html_e('Update Available!', 'eventeule'); ?></strong><br>
                            <?php printf(esc_html__('Version %s is ready to install.', 'eventeule'), '<strong>' . esc_html($new_version) . '</strong>'); ?></p>
                            <p style="margin:10px 0 0;">
                                <a href="<?php echo esc_url(wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=eventeule/EventEule.php'), 'upgrade-plugin_eventeule/EventEule.php')); ?>" class="button button-primary">
                                    <span class="dashicons dashicons-download" style="margin-top:3px;"></span>
                                    <?php esc_html_e('Install Update Now', 'eventeule'); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('update-core.php')); ?>" class="button button-secondary" style="margin-left:10px;">
                                    <?php esc_html_e('View in Updates Dashboard', 'eventeule'); ?>
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="margin-top:15px;">
                        <input type="hidden" name="action" value="eventeule_check_updates" />
                        <?php wp_nonce_field('eventeule_check_updates', 'eventeule_nonce'); ?>
                        <button type="submit" class="button button-secondary">
                            <span class="dashicons dashicons-update"></span>
                            <?php esc_html_e('Check for Updates Now', 'eventeule'); ?>
                        </button>
                    </form>
                </div>

                <?php
                $github_has_update    = isset($latestGithubVersion) && $latestGithubVersion !== null && version_compare($latestGithubVersion, EVENTEULE_VERSION, '>');
                $github_version_label = $latestGithubVersion ?? __('unknown', 'eventeule');
                ?>
                <div class="eventeule-section" style="border-top:1px solid #ddd; margin-top:20px; padding-top:20px;">
                    <h3><?php esc_html_e('Direct Update from GitHub', 'eventeule'); ?></h3>
                    <?php if (!isset($latestGithubVersion) || $latestGithubVersion === null): ?>
                        <p class="description"><?php esc_html_e('Could not retrieve the latest version from GitHub right now. Please try later or check your internet connection.', 'eventeule'); ?></p>
                    <?php elseif (!$github_has_update): ?>
                        <p class="description"><?php printf(esc_html__('You are already running the latest version (%s). No update available.', 'eventeule'), '<strong>' . esc_html($github_version_label) . '</strong>'); ?></p>
                    <?php else: ?>
                        <p class="description"><?php printf(esc_html__('Version %s is available on GitHub. Click the button below to install it now.', 'eventeule'), '<strong>' . esc_html($github_version_label) . '</strong>'); ?></p>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:12px;">
                            <input type="hidden" name="action" value="eventeule_direct_update" />
                            <?php wp_nonce_field('eventeule_direct_update', 'eventeule_nonce'); ?>
                            <button type="submit" class="button button-primary"
                                    onclick="return confirm('<?php esc_attr_e('This will download and install the latest release from GitHub. Continue?', 'eventeule'); ?>');">
                                <span class="dashicons dashicons-download" style="margin-top:3px;"></span>
                                <?php printf(esc_html__('Install v%s from GitHub', 'eventeule'), esc_html($github_version_label)); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="eventeule-section" style="border-top:1px solid #ddd; margin-top:20px; padding-top:20px;">
                    <h3><?php esc_html_e('GitHub Repository', 'eventeule'); ?></h3>
                    <p>
                        <a href="https://github.com/twicemind/eventeule" target="_blank" class="button">
                            <span class="dashicons dashicons-external"></span>
                            twicemind/eventeule
                        </a>
                    </p>
                </div>

                <div class="eventeule-section" style="border-top:1px solid #ddd; margin-top:20px; padding-top:20px;">
                    <h3><?php esc_html_e('GitHub Personal Access Token (Optional)', 'eventeule'); ?></h3>
                    <p class="description">
                        <?php esc_html_e('The plugin automatically checks for updates from the public GitHub repository.', 'eventeule'); ?>
                        <strong><?php esc_html_e('A token is optional and only needed to avoid API rate limits (60 requests/hour).', 'eventeule'); ?></strong>
                    </p>
                    <details style="margin-top:15px;">
                        <summary style="cursor:pointer; font-weight:600;"><?php esc_html_e('How to create a token (optional)', 'eventeule'); ?></summary>
                        <ol style="margin-top:10px; padding-left:20px;">
                            <li><?php printf(esc_html__('Go to %s', 'eventeule'), '<a href="https://github.com/settings/tokens" target="_blank">GitHub Settings → Developer settings → Personal access tokens</a>'); ?></li>
                            <li><?php esc_html_e('Click "Generate new token" → "Generate new token (classic)"', 'eventeule'); ?></li>
                            <li><?php esc_html_e('Give it a name (e.g. "EventEule WordPress Updates")', 'eventeule'); ?></li>
                            <li><?php esc_html_e('Select scopes: Check only "public_repo"', 'eventeule'); ?></li>
                            <li><?php esc_html_e('Click "Generate token" and copy the token', 'eventeule'); ?></li>
                            <li><?php esc_html_e('Paste the token below and save', 'eventeule'); ?></li>
                        </ol>
                    </details>
                    <form method="post" action="options.php" style="margin-top:20px;">
                        <?php settings_fields('eventeule_updater_settings'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="eventeule_github_token"><?php esc_html_e('GitHub Token', 'eventeule'); ?></label>
                                </th>
                                <td>
                                    <input type="password"
                                           id="eventeule_github_token"
                                           name="eventeule_github_token"
                                           value="<?php echo esc_attr(get_option('eventeule_github_token', '')); ?>"
                                           class="regular-text"
                                           autocomplete="off"
                                           placeholder="<?php esc_attr_e('ghp_xxxxxxxxxxxx (optional)', 'eventeule'); ?>" />
                                    <p class="description"><?php esc_html_e('Leave empty if you don\'t need a token.', 'eventeule'); ?></p>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button(esc_html__('Save Token', 'eventeule'), 'secondary'); ?>
                    </form>
                </div>
            </div>

        <!-- ─── SYSTEM ─── -->
        <?php elseif ($activeSection === 'system'): ?>

            <div class="ee-section-header">
                <h1><?php esc_html_e('System', 'eventeule'); ?></h1>
                <p><?php esc_html_e('System info and advanced settings', 'eventeule'); ?></p>
            </div>

            <div class="eventeule-card">
                <div class="eventeule-empty-state">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <p><?php esc_html_e('Dieser Bereich wird in einer zukünftigen Version weiter ausgebaut.', 'eventeule'); ?></p>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>
