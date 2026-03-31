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
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('EventEule: Using GitHub token for API requests');
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('EventEule: No GitHub token - using public API (60 req/hour limit)');
            }
        }

        // Use release assets instead of source code (regex picks the plugin ZIP)
        $this->updateChecker->getVcsApi()->enableReleaseAssets('/eventeule-.*\.zip/i');
        
        // Inject into transient at WRITE time so even after WP refreshes it, EventEule is included
        add_filter('pre_set_site_transient_update_plugins', [$this, 'ensure_update_in_transient'], 20, 1);

        // Add debug logging if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('puc_check_now-eventeule', [$this, 'log_update_check'], 10, 1);
            add_filter('puc_request_info_result-eventeule', [$this, 'log_api_response'], 20, 2);
        }
    }

    /**
     * Inject EventEule update into the transient when WordPress writes it.
     * PUC handles the READ side; this covers the WRITE side so the stored
     * transient already contains our update (belt-and-suspenders).
     */
    public function ensure_update_in_transient($transient) {
        if (!$this->updateChecker) {
            return $transient;
        }
        $update = $this->updateChecker->getUpdate();
        if ($update !== null) {
            if (!is_object($transient)) {
                $transient = new \stdClass();
                $transient->response = [];
            }
            if (!isset($transient->response)) {
                $transient->response = [];
            }
            $pluginFile = plugin_basename(EVENTEULE_FILE);
            $transient->response[$pluginFile] = $update->toWpFormat();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('EventEule: Injected v' . $update->version . ' into update transient (pre_set)');
            }
        }
        return $transient;
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
                // Clear Plugin Update Checker cache
                $this->updateChecker->resetUpdateState();
                
                // Force immediate check
                $this->updateChecker->checkForUpdates();
                
                // Get update info
                $update = $this->updateChecker->getUpdate();
                
                // Manually ensure the WordPress transient is set
                if ($update !== null && version_compare($update->version, EVENTEULE_VERSION, '>')) {
                    // Get current transient
                    $current = get_site_transient('update_plugins');
                    if (!is_object($current)) {
                        $current = new \stdClass();
                    }
                    if (!isset($current->response)) {
                        $current->response = [];
                    }
                    
                    // Add our plugin update to the transient
                    $plugin_file = 'eventeule/EventEule.php';
                    $current->response[$plugin_file] = (object)[
                        'slug' => 'eventeule',
                        'plugin' => $plugin_file,
                        'new_version' => $update->version,
                        'url' => 'https://github.com/twicemind/eventeule',
                        'package' => $update->download_url,
                        'tested' => '',
                        'requires_php' => '',
                        'compatibility' => new \stdClass(),
                    ];
                    
                    // Refresh last_checked so _maybe_update_plugins() won't immediately
                    // overwrite this transient with a fresh WP.org API call
                    $current->last_checked = time();

                    // Save the transient
                    set_site_transient('update_plugins', $current);
                    
                    // Log success
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('EventEule: Manually set update transient for version ' . $update->version);
                    }
                    
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

    /**
     * Log API response for debugging
     */
    public function log_api_response($pluginInfo, $result): mixed
    {
        if (!isset($result)) {
            error_log('EventEule: API returned no result');
        } elseif (is_wp_error($result)) {
            error_log('EventEule: API Error: ' . $result->get_error_message());
        } elseif (is_object($result)) {
            if (isset($result->message)) {
                error_log('EventEule: GitHub API Message: ' . $result->message);
            }
            if (isset($result->tag_name)) {
                error_log('EventEule: Latest GitHub Release: ' . $result->tag_name);
            }
            if (isset($result->assets) && is_array($result->assets)) {
                error_log('EventEule: Found ' . count($result->assets) . ' release assets');
                foreach ($result->assets as $asset) {
                    if (isset($asset->name)) {
                        error_log('EventEule: Asset: ' . $asset->name);
                    }
                }
            } else {
                error_log('EventEule: No assets found in release');
            }
        }
        
        return $pluginInfo;
    }
}
