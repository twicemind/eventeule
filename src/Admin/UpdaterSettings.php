<?php

namespace EventEule\Admin;

class UpdaterSettings
{
    const OPTION_NAME = 'eventeule_github_token';

    public function register(): void
    {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_notices', [$this, 'show_token_notice']);
    }

    public function add_settings_page(): void
    {
        add_submenu_page(
            'eventeule',  // Parent slug from Admin.php
            __('Update Settings', 'eventeule'),
            __('Update Settings', 'eventeule'),
            'manage_options',
            'eventeule-updater-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings(): void
    {
        register_setting(
            'eventeule_updater_settings',
            self::OPTION_NAME,
            [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            ]
        );

        add_settings_section(
            'eventeule_updater_section',
            __('GitHub Access Configuration', 'eventeule'),
            [$this, 'render_section_info'],
            'eventeule-updater-settings'
        );

        add_settings_field(
            'github_token',
            __('GitHub Personal Access Token', 'eventeule'),
            [$this, 'render_token_field'],
            'eventeule-updater-settings',
            'eventeule_updater_section'
        );
    }

    public function render_section_info(): void
    {
        echo '<p>' . esc_html__('The plugin automatically checks for updates from the public GitHub repository.', 'eventeule') . '</p>';
        echo '<p>' . esc_html__('A Personal Access Token is optional and only needed for private repositories or to avoid GitHub API rate limits.', 'eventeule') . '</p>';
        echo '<p><strong>' . esc_html__('Optional: How to create a token:', 'eventeule') . '</strong></p>';
        echo '<ol>';
        echo '<li>' . sprintf(
            __('Go to <a href="%s" target="_blank">GitHub Settings → Developer settings → Personal access tokens</a>', 'eventeule'),
            'https://github.com/settings/tokens'
        ) . '</li>';
        echo '<li>' . esc_html__('Click "Generate new token" → "Generate new token (classic)"', 'eventeule') . '</li>';
        echo '<li>' . esc_html__('Give it a name (e.g. "EventEule WordPress Updates")', 'eventeule') . '</li>';
        echo '<li>' . esc_html__('Select scopes: Check only "repo" (Full control of private repositories)', 'eventeule') . '</li>';
        echo '<li>' . esc_html__('Click "Generate token" and copy the token', 'eventeule') . '</li>';
        echo '<li>' . esc_html__('Paste the token in the field below and save', 'eventeule') . '</li>';
        echo '</ol>';
    }

    public function render_token_field(): void
    {
        $value = get_option(self::OPTION_NAME, '');
        $masked = !empty($value) ? str_repeat('*', 20) . substr($value, -4) : '';
        
        echo '<input type="password" id="eventeule_github_token" name="' . esc_attr(self::OPTION_NAME) . '" ';
        echo 'value="' . esc_attr($value) . '" class="regular-text" autocomplete="off" />';
        
        if (!empty($masked)) {
            echo '<p class="description">' . sprintf(
                __('Current token: %s', 'eventeule'),
                '<code>' . esc_html($masked) . '</code>'
            ) . '</p>';
        }
        
        echo '<p class="description">' . esc_html__('This token is stored securely and only used to check for plugin updates.', 'eventeule') . '</p>';
        
        echo '<p><button type="button" class="button" onclick="document.getElementById(\'eventeule_github_token\').type = document.getElementById(\'eventeule_github_token\').type === \'password\' ? \'text\' : \'password\';">';
        echo esc_html__('Show/Hide Token', 'eventeule');
        echo '</button></p>';
    }

    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check if settings were saved
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'eventeule_updater_messages',
                'eventeule_updater_message',
                __('Settings saved. Plugin updates will now be checked automatically.', 'eventeule'),
                'updated'
            );
        }

        // Check if manual update check was performed
        if (isset($_GET['update-check'])) {
            $check_result = $_GET['update-check'];
            if ($check_result === 'available' && isset($_GET['version'])) {
                add_settings_error(
                    'eventeule_updater_messages',
                    'eventeule_updater_message',
                    sprintf(__('Update available! Version %s is ready to install. Go to Dashboard → Updates.', 'eventeule'), esc_html($_GET['version'])),
                    'updated'
                );
            } elseif ($check_result === 'none') {
                add_settings_error(
                    'eventeule_updater_messages',
                    'eventeule_updater_message',
                    __('Your plugin is up to date!', 'eventeule'),
                    'info'
                );
            } elseif ($check_result === 'error') {
                $error_detail = isset($_GET['error-detail']) ? sanitize_text_field($_GET['error-detail']) : '';
                $error_msg = __('Error checking for updates.', 'eventeule');
                
                if (!empty($error_detail)) {
                    // Check for common error types
                    if (stripos($error_detail, 'rate limit') !== false) {
                        $error_msg .= ' ' . __('GitHub API rate limit exceeded. Please wait an hour or configure a GitHub token below to increase the limit.', 'eventeule');
                    } elseif (stripos($error_detail, 'could not resolve host') !== false || stripos($error_detail, 'connection') !== false) {
                        $error_msg .= ' ' . __('Could not connect to GitHub. Please check your internet connection.', 'eventeule');
                    } else {
                        $error_msg .= ' ' . sprintf(__('Details: %s', 'eventeule'), esc_html($error_detail));
                    }
                } else {
                    $error_msg .= ' ' . __('Make sure the GitHub token is correctly configured.', 'eventeule');
                }
                
                add_settings_error(
                    'eventeule_updater_messages',
                    'eventeule_updater_message',
                    $error_msg,
                    'error'
                );
            }
        }

        settings_errors('eventeule_updater_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('eventeule_updater_settings');
                do_settings_sections('eventeule-updater-settings');
                submit_button(__('Save Token', 'eventeule'));
                ?>
            </form>
            
            <hr />
            
            <h2><?php esc_html_e('Test Update Check', 'eventeule'); ?></h2>
            <p><?php esc_html_e('Use this button to manually check for updates:', 'eventeule'); ?></p>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="eventeule_check_updates" />
                <?php wp_nonce_field('eventeule_check_updates', 'eventeule_nonce'); ?>
                <?php submit_button(__('Check for Updates Now', 'eventeule'), 'secondary', 'submit', false); ?>
            </form>
            
            <hr />
            
            <h2><?php esc_html_e('Current Plugin Information', 'eventeule'); ?></h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <th><?php esc_html_e('Current Version', 'eventeule'); ?>:</th>
                        <td><code><?php echo esc_html(EVENTEULE_VERSION); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('GitHub Repository', 'eventeule'); ?>:</th>
                        <td><a href="https://github.com/twicemind/eventeule" target="_blank">twicemind/eventeule</a></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Token Status', 'eventeule'); ?>:</th>
                        <td>
                            <?php if (empty(get_option(self::OPTION_NAME, ''))): ?>
                                <span style="color: #d63638;">⚠️ <?php esc_html_e('No token configured', 'eventeule'); ?></span>
                            <?php else: ?>
                                <span style="color: #00a32a;">✓ <?php esc_html_e('Token configured', 'eventeule'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function show_token_notice(): void
    {
        // Token notice removed - not required for public repositories
        // Updates will work automatically without token
        return;
    }

    /**
     * Get the configured GitHub token
     */
    public static function get_token(): string
    {
        return get_option(self::OPTION_NAME, '');
    }
}
