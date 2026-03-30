<?php

namespace EventEule\Admin;

class UpdaterSettings
{
    const OPTION_NAME = 'eventeule_github_token';

    public function register(): void
    {
        // Remove add_menu_page - now integrated into dashboard as tab
        add_action('admin_init', [$this, 'register_settings']);
    }

    // Removed add_settings_page() method - no longer needed

    /**
     * Register plugin settings for GitHub token (optional)
     * Token is only used to increase GitHub API rate limits
     */
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
    }

    /**
     * Get the configured GitHub token
     */
    public static function get_token(): string
    {
        return get_option(self::OPTION_NAME, '');
    }
}
