<?php

namespace EventEule\Support;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Updater
{
    private $updateChecker;

    public function register(): void
    {
        add_action('plugins_loaded', [$this, 'init_update_checker']);
    }

    public function init_update_checker(): void
    {
        if (!class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
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

        // GitHub token from environment variable or local config
        $githubToken = $this->get_github_token();
        
        if (!empty($githubToken)) {
            $this->updateChecker->setAuthentication($githubToken);
        }

        // Use release assets instead of source code
        $this->updateChecker->getVcsApi()->enableReleaseAssets();
        
        // Filter for asset selection (choose the ZIP file)
        add_filter('puc_request_info_result-eventeule', [$this, 'filter_plugin_info'], 10, 2);
    }

    /**
     * Get GitHub token from environment variable or local config
     */
    private function get_github_token(): string
    {
        // 1. Check environment variable (e.g. when .env is loaded)
        if (defined('GITHUB_ACCESS_TOKEN') && !empty(GITHUB_ACCESS_TOKEN)) {
            return GITHUB_ACCESS_TOKEN;
        }

        // 2. Check $_ENV (when server-level environment variables are set)
        if (isset($_ENV['GITHUB_ACCESS_TOKEN']) && !empty($_ENV['GITHUB_ACCESS_TOKEN'])) {
            return $_ENV['GITHUB_ACCESS_TOKEN'];
        }

        // 3. Check local config file (not committed)
        $configFile = EVENTEULE_PATH . 'config-local.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
            if (isset($config['github_token']) && !empty($config['github_token'])) {
                return $config['github_token'];
            }
        }

        // 4. No token found - only works for public repositories
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
}
