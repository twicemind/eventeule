<?php

namespace EventEule\Support;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Updater
{
    private $updateChecker = null;

    public function register(): void
    {
        add_action('plugins_loaded', [$this, 'init_update_checker']);
        add_action('admin_post_eventeule_check_updates', [$this, 'handle_manual_update_check']);
    }

    public function init_update_checker(): void
    {
        if (!class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('EventEule: Plugin Update Checker library not found. Run: composer install');
            }
            return;
        }

        // GitHub Repository URL
        $githubUrl = 'https://github.com/twicemind/eventeule';
        
        // Initialize Update Checker
        $this->updateChecker = PucFactory::buildUpdateChecker(
            $githubUrl,
            EVENTEULE_FILE,
            'eventeule'
        );

        // GitHub token from environment variable or local config (optional for public repos)
        $githubToken = $this->get_github_token();
        
        if (!empty($githubToken)) {
            $this->updateChecker->setAuthentication($githubToken);
        }

        // Use release assets instead of source code
        $this->updateChecker->getVcsApi()->enableReleaseAssets();
        
        // Set branch for updates (main branch)
        $this->updateChecker->setBranch('main');
        
        // Filter for asset selection (choose the ZIP file)
        add_filter('puc_request_info_result-eventeule', [$this, 'filter_plugin_info'], 10, 2);
        
        // Add debug logging if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('puc_check_now-eventeule', [$this, 'log_update_check'], 10, 1);
        }
    }

    /**
     * Get GitHub token from various sources
     */
    private function get_github_token(): string
    {
        // 1. Check WordPress option (from admin settings)
        $token = get_option('eventeule_github_token', '');
        if (!empty($token)) {
            return $token;
        }

        // 2. Check environment variable (e.g. when .env is loaded)
        if (defined('GITHUB_ACCESS_TOKEN') && !empty(GITHUB_ACCESS_TOKEN)) {
            return GITHUB_ACCESS_TOKEN;
        }

        // 3. Check $_ENV (when server-level environment variables are set)
        if (isset($_ENV['GITHUB_ACCESS_TOKEN']) && !empty($_ENV['GITHUB_ACCESS_TOKEN'])) {
            return $_ENV['GITHUB_ACCESS_TOKEN'];
        }

        // 4. Check local config file (not committed)
        $configFile = EVENTEULE_PATH . 'config-local.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
            if (isset($config['github_token']) && !empty($config['github_token'])) {
                return $config['github_token'];
            }
        }

        // 5. No token found - only works for public repositories
        return '';
    }

    /**
     * Filters plugin information and selects the correct asset
     */
    public function filter_plugin_info($pluginInfo, $result)
    {
        if (!isset($result) || !is_object($result)) {
            return $pluginInfo;
        }

        // Suche nach dem ZIP-Asset in den Releases
        if (isset($result->assets) && is_array($result->assets)) {
            foreach ($result->assets as $asset) {
                if (isset($asset->name) && strpos($asset->name, 'eventeule-') === 0 && strpos($asset->name, '.zip') !== false) {
                    $pluginInfo->download_url = $asset->browser_download_url;
                    break;
                }
            }
        }

        return $pluginInfo;
    }

    /**
     * Handle manual update check from admin
     */
    public function handle_manual_update_check(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'eventeule'));
        }

        check_admin_referer('eventeule_check_updates', 'eventeule_nonce');

        // Ensure update checker is initialized
        if ($this->updateChecker === null) {
            $this->init_update_checker();
        }

        // Force update check by clearing the cache
        if ($this->updateChecker) {
            try {
                // Clear WordPress update cache
                delete_site_transient('update_plugins');
                
                // Clear Plugin Update Checker cache
                $this->updateChecker->resetUpdateState();
                
                // Force immediate check
                $this->updateChecker->checkForUpdates();
                
                // Get update info
                $update = $this->updateChecker->getUpdate();
                
                // Check if update is actually newer than current version
                if ($update !== null && version_compare($update->version, EVENTEULE_VERSION, '>')) {
                    wp_redirect(add_query_arg(
                        ['update-check' => 'available', 'version' => $update->version, 'tab' => 'updates'],
                        admin_url('admin.php?page=eventeule')
                    ));
                    exit;
                } else {
                    wp_redirect(add_query_arg(
                        ['update-check' => 'none', 'tab' => 'updates'],
                        admin_url('admin.php?page=eventeule')
                    ));
                    exit;
                }
            } catch (\Throwable $e) {
                // Log error for debugging
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('EventEule Update Check Error: ' . $e->getMessage());
                }
                
                wp_redirect(add_query_arg(
                    ['update-check' => 'error', 'error-detail' => urlencode($e->getMessage()), 'tab' => 'updates'],
                    admin_url('admin.php?page=eventeule')
                ));
                exit;
            }
        } else {
            // Check if it's because the library is missing
            $error_detail = class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory') 
                ? 'Update checker not initialized' 
                : 'Plugin Update Checker library not found. Please run: composer install';
                
            wp_redirect(add_query_arg(
                ['update-check' => 'error', 'error-detail' => urlencode($error_detail), 'tab' => 'updates'],
                admin_url('admin.php?page=eventeule')
            ));
            exit;
        }
    }

    /**
     * Log update checks for debugging
     */
    public function log_update_check($checkerInstance): void
    {
        error_log('EventEule: Checking for updates from GitHub...');
        
        if ($this->updateChecker) {
            $update = $this->updateChecker->getUpdate();
            if ($update) {
                error_log('EventEule: Update available - Version ' . $update->version);
            } else {
                error_log('EventEule: No updates available. Current version: ' . EVENTEULE_VERSION);
            }
        }
    }
}
