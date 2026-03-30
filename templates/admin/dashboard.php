<div class="wrap eventeule-dashboard">
    <div class="eventeule-header">
        <div class="eventeule-logo">
            <span class="dashicons dashicons-calendar-alt"></span>
            <h1><?php esc_html_e('EventEule', 'eventeule'); ?></h1>
        </div>
        <p class="eventeule-tagline"><?php esc_html_e('Event Management Made Easy', 'eventeule'); ?></p>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
        <div class="eventeule-message eventeule-message-success notice is-dismissible" style="background: #d4edda; border-left: 4px solid #28a745; padding: 12px 15px; margin: 15px 0; border-radius: 4px;">
            <p style="margin: 0;"><?php esc_html_e('Settings saved successfully!', 'eventeule'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <nav class="eventeule-tabs">
        <a href="?page=eventeule&tab=overview" class="eventeule-tab <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-dashboard"></span>
            <?php esc_html_e('Overview', 'eventeule'); ?>
        </a>
        <a href="?page=eventeule&tab=calendar" class="eventeule-tab <?php echo $activeTab === 'calendar' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-calendar"></span>
            <?php esc_html_e('Calendar', 'eventeule'); ?>
        </a>
        <a href="?page=eventeule&tab=settings" class="eventeule-tab <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-admin-appearance"></span>
            <?php esc_html_e('Widget Styling', 'eventeule'); ?>
        </a>
        <a href="?page=eventeule&tab=updates" class="eventeule-tab <?php echo $activeTab === 'updates' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-update"></span>
            <?php esc_html_e('Updates', 'eventeule'); ?>
        </a>
    </nav>

    <!-- Tab Content -->
    <div class="eventeule-tab-content">
        
        <?php if ($activeTab === 'overview'): ?>
            <!-- Tab 1: Overview -->
            <div class="eventeule-tab-panel">
                <!-- Statistiken -->
                <div class="eventeule-stats">
                    <div class="eventeule-stat-card eventeule-stat-total">
                        <div class="eventeule-stat-icon">
                            <span class="dashicons dashicons-calendar-alt"></span>
                        </div>
                        <div class="eventeule-stat-content">
                            <h3><?php echo esc_html($stats['total']); ?></h3>
                            <p><?php esc_html_e('Total Events', 'eventeule'); ?></p>
                        </div>
                    </div>
                    
                    <div class="eventeule-stat-card eventeule-stat-upcoming">
                        <div class="eventeule-stat-icon">
                            <span class="dashicons dashicons-arrow-right-alt"></span>
                        </div>
                        <div class="eventeule-stat-content">
                            <h3><?php echo esc_html($stats['upcoming']); ?></h3>
                            <p><?php esc_html_e('Upcoming Events', 'eventeule'); ?></p>
                        </div>
                    </div>
                    
                    <div class="eventeule-stat-card eventeule-stat-past">
                        <div class="eventeule-stat-icon">
                            <span class="dashicons dashicons-backup"></span>
                        </div>
                        <div class="eventeule-stat-content">
                            <h3><?php echo esc_html($stats['past']); ?></h3>
                            <p><?php esc_html_e('Past Events', 'eventeule'); ?></p>
                        </div>
                    </div>
                    
                    <div class="eventeule-stat-card eventeule-stat-featured">
                        <div class="eventeule-stat-icon">
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                        <div class="eventeule-stat-content">
                            <h3><?php echo esc_html($stats['featured']); ?></h3>
                            <p><?php esc_html_e('Featured Events', 'eventeule'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
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
                        <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=eventeule_category&post_type=eventeule_event')); ?>" class="eventeule-action-button">
                            <span class="dashicons dashicons-category"></span>
                            <span><?php esc_html_e('Manage Categories', 'eventeule'); ?></span>
                        </a>
                    </div>
                </div>

                <!-- Upcoming Events -->
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
                                $eventData = $this->eventRepository->get_event_data($event);
                            ?>
                                <div class="eventeule-event-item">
                                    <div class="eventeule-event-date">
                                        <span class="eventeule-event-day">
                                            <?php echo esc_html(date_i18n('d', strtotime($eventData['start_date']))); ?>
                                        </span>
                                        <span class="eventeule-event-month">
                                            <?php echo esc_html(date_i18n('M', strtotime($eventData['start_date']))); ?>
                                        </span>
                                    </div>
                                    <div class="eventeule-event-info">
                                        <h4>
                                            <?php echo esc_html($eventData['title']); ?>
                                            <?php if ($eventData['featured']): ?>
                                                <span class="eventeule-badge-featured">
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                </span>
                                            <?php endif; ?>
                                        </h4>
                                        <div class="eventeule-event-meta">
                                            <?php if (!empty($eventData['start_time'])): ?>
                                                <span>
                                                    <span class="dashicons dashicons-clock"></span>
                                                    <?php echo esc_html($eventData['start_time']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($eventData['location'])): ?>
                                                <span>
                                                    <span class="dashicons dashicons-location"></span>
                                                    <?php echo esc_html($eventData['location']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="eventeule-event-actions">
                                        <a href="<?php echo esc_url(get_edit_post_link($event->ID)); ?>" class="button">
                                            <?php esc_html_e('Edit', 'eventeule'); ?>
                                        </a>
                                        <a href="<?php echo esc_url($eventData['permalink']); ?>" class="button" target="_blank">
                                            <?php esc_html_e('View', 'eventeule'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($activeTab === 'calendar'): ?>
            <!-- Tab 2: Calendar View -->
            <div class="eventeule-tab-panel">
                <div class="eventeule-card">
                    <h2><?php esc_html_e('Calendar View', 'eventeule'); ?></h2>
                    
                    <?php if (empty($calendarEvents)): ?>
                        <div class="eventeule-empty-state">
                            <span class="dashicons dashicons-calendar"></span>
                            <p><?php esc_html_e('No events found.', 'eventeule'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="eventeule-calendar">
                            <?php foreach ($calendarEvents as $month => $events): ?>
                                <div class="eventeule-month-section">
                                    <h3 class="eventeule-month-title">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                        <?php echo esc_html(date_i18n('F Y', strtotime($month . '-01'))); ?>
                                        <span class="eventeule-month-count">(<?php echo count($events); ?>)</span>
                                    </h3>
                                    <div class="eventeule-month-events">
                                        <?php foreach ($events as $eventData): ?>
                                            <div class="eventeule-calendar-event">
                                                <div class="eventeule-calendar-date">
                                                    <div class="eventeule-calendar-day">
                                                        <?php echo esc_html(date_i18n('d', strtotime($eventData['start_date']))); ?>
                                                    </div>
                                                    <div class="eventeule-calendar-weekday">
                                                        <?php echo esc_html(date_i18n('D', strtotime($eventData['start_date']))); ?>
                                                    </div>
                                                </div>
                                                <div class="eventeule-calendar-info">
                                                    <h4>
                                                        <?php echo esc_html($eventData['title']); ?>
                                                        <?php if ($eventData['featured']): ?>
                                                            <span class="dashicons dashicons-star-filled" style="color: #f0b849;"></span>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <div class="eventeule-calendar-meta">
                                                        <?php if (!empty($eventData['start_time'])): ?>
                                                            <span><span class="dashicons dashicons-clock"></span> <?php echo esc_html($eventData['start_time']); ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($eventData['location'])): ?>
                                                            <span><span class="dashicons dashicons-location"></span> <?php echo esc_html($eventData['location']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="eventeule-calendar-actions">
                                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $eventData['id'] . '&action=edit')); ?>" class="button button-small">
                                                        <?php esc_html_e('Edit', 'eventeule'); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        
        <?php elseif ($activeTab === 'settings'): ?>
            <!-- Tab 3: Widget Styling -->
            <div class="eventeule-tab-panel">
                <div class="eventeule-card">
                    <h2><?php esc_html_e('Widget Color Settings', 'eventeule'); ?></h2>
                    <p class="description"><?php esc_html_e('Customize the colors for EventEule widgets on your website.', 'eventeule'); ?></p>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="eventeule-settings-form">
                        <input type="hidden" name="action" value="eventeule_save_settings">
                        <?php wp_nonce_field('eventeule_settings'); ?>
                        
                        <div class="eventeule-color-grid">
                            <div class="eventeule-color-field">
                                <label for="primary_color"><?php esc_html_e('Primary Color', 'eventeule'); ?></label>
                                <div class="eventeule-color-input">
                                    <input type="color" id="primary_color" name="primary_color" value="<?php echo esc_attr($settings['primary_color']); ?>">
                                    <input type="text" value="<?php echo esc_attr($settings['primary_color']); ?>" readonly>
                                </div>
                                <p class="description"><?php esc_html_e('Main color for buttons and highlights', 'eventeule'); ?></p>
                            </div>

                            <div class="eventeule-color-field">
                                <label for="secondary_color"><?php esc_html_e('Secondary Color', 'eventeule'); ?></label>
                                <div class="eventeule-color-input">
                                    <input type="color" id="secondary_color" name="secondary_color" value="<?php echo esc_attr($settings['secondary_color']); ?>">
                                    <input type="text" value="<?php echo esc_attr($settings['secondary_color']); ?>" readonly>
                                </div>
                                <p class="description"><?php esc_html_e('Hover states and accents', 'eventeule'); ?></p>
                            </div>

                            <div class="eventeule-color-field">
                                <label for="accent_color"><?php esc_html_e('Accent Color', 'eventeule'); ?></label>
                                <div class="eventeule-color-input">
                                    <input type="color" id="accent_color" name="accent_color" value="<?php echo esc_attr($settings['accent_color']); ?>">
                                    <input type="text" value="<?php echo esc_attr($settings['accent_color']); ?>" readonly>
                                </div>
                                <p class="description"><?php esc_html_e('Featured badges and special elements', 'eventeule'); ?></p>
                            </div>

                            <div class="eventeule-color-field">
                                <label for="text_color"><?php esc_html_e('Text Color', 'eventeule'); ?></label>
                                <div class="eventeule-color-input">
                                    <input type="color" id="text_color" name="text_color" value="<?php echo esc_attr($settings['text_color']); ?>">
                                    <input type="text" value="<?php echo esc_attr($settings['text_color']); ?>" readonly>
                                </div>
                                <p class="description"><?php esc_html_e('Main text color', 'eventeule'); ?></p>
                            </div>

                            <div class="eventeule-color-field">
                                <label for="background_color"><?php esc_html_e('Background Color', 'eventeule'); ?></label>
                                <div class="eventeule-color-input">
                                    <input type="color" id="background_color" name="background_color" value="<?php echo esc_attr($settings['background_color']); ?>">
                                    <input type="text" value="<?php echo esc_attr($settings['background_color']); ?>" readonly>
                                </div>
                                <p class="description"><?php esc_html_e('Widget background color', 'eventeule'); ?></p>
                            </div>

                            <div class="eventeule-color-field">
                                <label for="border_color"><?php esc_html_e('Border Color', 'eventeule'); ?></label>
                                <div class="eventeule-color-input">
                                    <input type="color" id="border_color" name="border_color" value="<?php echo esc_attr($settings['border_color']); ?>">
                                    <input type="text" value="<?php echo esc_attr($settings['border_color']); ?>" readonly>
                                </div>
                                <p class="description"><?php esc_html_e('Border and separator lines', 'eventeule'); ?></p>
                            </div>
                        </div>

                        <div class="eventeule-form-actions">
                            <button type="submit" class="button button-primary button-large">
                                <span class="dashicons dashicons-saved"></span>
                                <?php esc_html_e('Save Settings', 'eventeule'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($activeTab === 'updates'): ?>
            <!-- Tab 4: Updates -->
            <div class="eventeule-tab-panel">
                <div class="eventeule-card">
                    <h2><span class="dashicons dashicons-update"></span> <?php esc_html_e('Plugin Updates', 'eventeule'); ?></h2>
                    
                    <?php
                    // Check for update check results - display only in this tab
                    if (isset($_GET['update-check']) && $activeTab === 'updates') {
                        $check_result = sanitize_text_field($_GET['update-check']);
                        
                        if ($check_result === 'available' && isset($_GET['version'])) {
                            echo '<div class="eventeule-message eventeule-message-success" style="background: #d4edda; border-left: 4px solid #28a745; padding: 12px 15px; margin: 15px 0; border-radius: 4px;"><p style="margin: 0;">';
                            printf(
                                esc_html__('Update found! Version %s is available. See below to install it.', 'eventeule'),
                                '<strong>' . esc_html($_GET['version']) .  '</strong>'
                            );
                            echo '</p></div>';
                        } elseif ($check_result === 'none') {
                            echo '<div class="eventeule-message eventeule-message-info" style="background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 12px 15px; margin: 15px 0; border-radius: 4px;"><p style="margin: 0;">';
                            esc_html_e('Your plugin is up to date!', 'eventeule');
                            echo '</p></div>';
                        } elseif ($check_result === 'error') {
                            $error_detail = isset($_GET['error-detail']) ? sanitize_text_field($_GET['error-detail']) : '';
                            $error_msg = esc_html__('Error checking for updates.', 'eventeule');
                            
                            if (!empty($error_detail)) {
                                if (stripos($error_detail, 'rate limit') !== false) {
                                    $error_msg .= ' ' . esc_html__('GitHub API rate limit exceeded. Please wait an hour or configure a GitHub token below to increase the limit.', 'eventeule');
                                } elseif (stripos($error_detail, 'could not resolve host') !== false || stripos($error_detail, 'connection') !== false) {
                                    $error_msg .= ' ' . esc_html__('Could not connect to GitHub. Please check your internet connection.', 'eventeule');
                                } else {
                                    $error_msg .= ' ' . sprintf(esc_html__('Details: %s', 'eventeule'), esc_html($error_detail));
                                }
                            }
                            
                            echo '<div class="eventeule-message eventeule-message-error" style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 12px 15px; margin: 15px 0; border-radius: 4px;"><p style="margin: 0;">' . $error_msg . '</p></div>';
                        }
                    }
                    ?>
                    
                    <div class="eventeule-section">
                        <h3><?php esc_html_e('Current Version', 'eventeule'); ?></h3>
                        <p class="eventeule-version-info">
                            <code><?php echo esc_html(EVENTEULE_VERSION); ?></code>
                        </p>
                        
                        <?php
                        // Check if update is available
                        $update_plugins = get_site_transient('update_plugins');
                        $plugin_file = 'eventeule/EventEule.php';
                        $has_update = false;
                        $new_version = '';
                        
                        if (isset($update_plugins->response[$plugin_file])) {
                            $has_update = true;
                            $new_version = $update_plugins->response[$plugin_file]->new_version;
                        }
                        
                        if ($has_update): ?>
                            <div class="notice notice-warning inline" style="margin: 15px 0; padding: 12px;">
                                <p style="margin: 0;">
                                    <strong><?php esc_html_e('Update Available!', 'eventeule'); ?></strong><br>
                                    <?php printf(
                                        esc_html__('Version %s is ready to install.', 'eventeule'),
                                        '<strong>' . esc_html($new_version) . '</strong>'
                                    ); ?>
                                </p>
                                <p style="margin: 10px 0 0 0;">
                                    <a href="<?php echo admin_url('update-core.php'); ?>" class="button button-primary">
                                        <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
                                        <?php esc_html_e('Install Update Now', 'eventeule'); ?>
                                    </a>
                                </p>
                            </div>
                        <?php else: ?>
                            <p style="color: #00a32a; margin: 10px 0;">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('Your plugin is up to date!', 'eventeule'); ?>
                            </p>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="eventeule_check_updates" />
                            <?php wp_nonce_field('eventeule_check_updates', 'eventeule_nonce'); ?>
                            <button type="submit" class="button button-secondary">
                                <span class="dashicons dashicons-update"></span>
                                <?php esc_html_e('Check for Updates Now', 'eventeule'); ?>
                            </button>
                        </form>
                    </div>
                    
                    <div class="eventeule-section">
                        <h3><?php esc_html_e('GitHub Repository', 'eventeule'); ?></h3>
                        <p>
                            <a href="https://github.com/twicemind/eventeule" target="_blank" class="button">
                                <span class="dashicons dashicons-external"></span>
                                twicemind/eventeule
                            </a>
                        </p>
                    </div>
                    
                    <div class="eventeule-section">
                        <h3><?php esc_html_e('GitHub Personal Access Token (Optional)', 'eventeule'); ?></h3>
                        <p class="description">
                            <?php esc_html_e('The plugin automatically checks for updates from the public GitHub repository.', 'eventeule'); ?>
                            <strong><?php esc_html_e('A token is optional and only needed to avoid API rate limits (60 requests/hour).', 'eventeule'); ?></strong>
                        </p>
                        
                        <details style="margin-top: 15px;">
                            <summary style="cursor: pointer; font-weight: 600;">
                                <?php esc_html_e('How to create a token (optional)', 'eventeule'); ?>
                            </summary>
                            <ol style="margin-top: 10px; padding-left: 20px;">
                                <li><?php printf(
                                    esc_html__('Go to %s', 'eventeule'),
                                    '<a href="https://github.com/settings/tokens" target="_blank">GitHub Settings → Developer settings → Personal access tokens</a>'
                                ); ?></li>
                                <li><?php esc_html_e('Click "Generate new token" → "Generate new token (classic)"', 'eventeule'); ?></li>
                                <li><?php esc_html_e('Give it a name (e.g. "EventEule WordPress Updates")', 'eventeule'); ?></li>
                                <li><?php esc_html_e('Select scopes: Check only "public_repo"', 'eventeule'); ?></li>
                                <li><?php esc_html_e('Click "Generate token" and copy the token', 'eventeule'); ?></li>
                                <li><?php esc_html_e('Paste the token below and save', 'eventeule'); ?></li>
                            </ol>
                        </details>
                        
                        <form method="post" action="options.php" style="margin-top: 20px;">
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
                                        <p class="description">
                                            <?php esc_html_e('Leave empty if you don\'t need a token.', 'eventeule'); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <?php submit_button(esc_html__('Save Token', 'eventeule'), 'secondary'); ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>