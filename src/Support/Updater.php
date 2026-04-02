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
        add_action('admin_post_eventeule_direct_update', [$this, 'handle_direct_update']);
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
                    
                    // Clear the cached GitHub version so the Direct Update section
                    // immediately reflects the newly discovered version.
                    delete_transient('eventeule_latest_github_version');

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
     * Handle direct update from GitHub.
     *
     * Downloads the latest release ZIP, extracts it with PHP's ZipArchive,
     * and replaces the plugin directory using an atomic rename sequence.
     * Uses only native PHP file operations – no WP_Filesystem dependency,
     * no Plugin_Upgrader deactivation, no "Cannot redeclare class" issues.
     */
    public function handle_direct_update(): void
    {
        if (!current_user_can('update_plugins')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'eventeule'));
        }

        check_admin_referer('eventeule_direct_update', 'eventeule_nonce');

        // ── 1. Fetch latest release info from GitHub ────────────────────────
        $api_url = 'https://api.github.com/repos/twicemind/eventeule/releases/latest';
        $request_args = [
            'headers' => [
                'User-Agent' => 'EventEule-WordPress-Plugin/' . EVENTEULE_VERSION,
                'Accept'     => 'application/vnd.github.v3+json',
            ],
            'timeout'   => 20,
            'sslverify' => true,
        ];

        $token = $this->get_github_token();
        if (!empty($token)) {
            $request_args['headers']['Authorization'] = 'Bearer ' . $token;
        }

        $response = wp_remote_get($api_url, $request_args);

        if (is_wp_error($response)) {
            $this->redirect_update_error($response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        if (200 !== $code) {
            $this->redirect_update_error('GitHub API returned HTTP ' . $code);
        }

        $body           = json_decode(wp_remote_retrieve_body($response), true);
        $latest_version = ltrim($body['tag_name'] ?? '', 'v');

        if (empty($latest_version)) {
            $this->redirect_update_error('No release version found on GitHub');
        }

        // ── 2. Locate the plugin ZIP asset ──────────────────────────────────
        $download_url = null;
        if (!empty($body['assets'])) {
            foreach ($body['assets'] as $asset) {
                if (preg_match('/eventeule-.*\.zip$/i', $asset['name'] ?? '')) {
                    $download_url = $asset['browser_download_url'];
                    break;
                }
            }
        }
        if (empty($download_url)) {
            $download_url = $body['zipball_url'] ?? null;
        }
        if (empty($download_url)) {
            $this->redirect_update_error('No download URL found for release v' . $latest_version);
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('EventEule: Direct update – downloading v' . $latest_version . ' from ' . $download_url);
        }

        // ── 3. Download ZIP ──────────────────────────────────────────────────
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $zip_file = download_url($download_url, 60);
        if (is_wp_error($zip_file)) {
            $this->redirect_update_error('Download failed: ' . $zip_file->get_error_message());
        }

        // ── 4. Extract with ZipArchive ───────────────────────────────────────
        $tmp_dir = WP_CONTENT_DIR . '/upgrade/eventeule-tmp-' . time();
        if (!wp_mkdir_p($tmp_dir)) {
            @unlink($zip_file);
            $this->redirect_update_error('Could not create temporary directory');
        }

        if (!class_exists('ZipArchive')) {
            @unlink($zip_file);
            $this->rmdir_recursive($tmp_dir);
            $this->redirect_update_error('PHP ZipArchive extension is not available on this server');
        }

        $zip = new \ZipArchive();
        $opened = $zip->open($zip_file);
        if ($opened !== true) {
            @unlink($zip_file);
            $this->rmdir_recursive($tmp_dir);
            $this->redirect_update_error('Could not open ZIP file (ZipArchive error ' . $opened . ')');
        }

        $zip->extractTo($tmp_dir);
        $zip->close();
        @unlink($zip_file);

        // ── 5. Locate EventEule.php inside extracted content ─────────────────
        // The GitHub Actions ZIP has files at root level (no subdirectory).
        // Fallback: handle ZIPs that do have a subdirectory.
        $source_dir = '';

        if (file_exists($tmp_dir . '/EventEule.php')) {
            // Files are at the root of the extracted directory
            $source_dir = $tmp_dir;
        } else {
            // Files are inside a subdirectory
            foreach ((array) glob($tmp_dir . '/*', GLOB_ONLYDIR) as $dir) {
                if (file_exists($dir . '/EventEule.php')) {
                    $source_dir = $dir;
                    break;
                }
            }
        }

        if (empty($source_dir)) {
            $this->rmdir_recursive($tmp_dir);
            $this->redirect_update_error('EventEule.php not found in the downloaded release ZIP');
        }

        // ── 6. Atomic directory replacement ─────────────────────────────────
        // Strategy: copy into a staging dir, then rename-swap with existing dir.
        // rename() on the same filesystem is atomic and avoids partial states.
        $plugin_dir  = WP_PLUGIN_DIR . '/eventeule';
        $staging_dir = WP_PLUGIN_DIR . '/eventeule-staging-' . time();
        $backup_dir  = WP_PLUGIN_DIR . '/eventeule-backup-' . time();

        // Copy extracted files into staging directory
        if (!$this->copy_dir_native($source_dir, $staging_dir)) {
            $this->rmdir_recursive($tmp_dir);
            $this->rmdir_recursive($staging_dir);
            $this->redirect_update_error('Failed to copy new plugin files to staging directory');
        }

        // Cleanup temp extraction dir
        $this->rmdir_recursive($tmp_dir);

        // Swap: backup old → install new
        if (!rename($plugin_dir, $backup_dir)) {
            $this->rmdir_recursive($staging_dir);
            $this->redirect_update_error('Failed to back up existing plugin directory');
        }

        if (!rename($staging_dir, $plugin_dir)) {
            // Rollback: restore old plugin
            rename($backup_dir, $plugin_dir);
            $this->rmdir_recursive($staging_dir);
            $this->redirect_update_error('Failed to move new plugin into place (rollback performed)');
        }

        // Remove backup
        $this->rmdir_recursive($backup_dir);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('EventEule: Direct update to v' . $latest_version . ' completed – directory replaced, plugin remains active');
        }

        // ── 7. Clear caches ──────────────────────────────────────────────────
        delete_transient('eventeule_latest_github_version');

        $current = get_site_transient('update_plugins');
        if (is_object($current) && isset($current->response)) {
            $plugin_file = plugin_basename(EVENTEULE_FILE);
            unset($current->response[$plugin_file]);
            set_site_transient('update_plugins', $current);
        }

        wp_safe_redirect(add_query_arg(
            ['direct-update' => 'success', 'version' => $latest_version, 'tab' => 'updates'],
            admin_url('admin.php?page=eventeule')
        ));
        exit;
    }

    /**
     * Recursively copy a directory using native PHP (no WP_Filesystem).
     */
    private function copy_dir_native(string $from, string $to): bool
    {
        if (!is_dir($to) && !mkdir($to, 0755, true)) {
            return false;
        }

        $items = scandir($from);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $src = $from . DIRECTORY_SEPARATOR . $item;
            $dst = $to . DIRECTORY_SEPARATOR . $item;

            if (is_dir($src)) {
                if (!$this->copy_dir_native($src, $dst)) {
                    return false;
                }
            } else {
                if (!copy($src, $dst)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recursively delete a directory using native PHP.
     */
    private function rmdir_recursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->rmdir_recursive($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    /**
     * Redirect back to the Updates tab with an error message.
     */
    private function redirect_update_error(string $detail): never
    {
        wp_safe_redirect(add_query_arg(
            ['direct-update' => 'error', 'error-detail' => rawurlencode($detail), 'tab' => 'updates'],
            admin_url('admin.php?page=eventeule')
        ));
        exit;
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
