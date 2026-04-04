<?php

namespace EventEule\Support;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Updater
{
    private const SLUG = 'eventeule';
    private const REPOSITORY_URL = 'https://github.com/twicemind/eventeule';
    private const RELEASE_ASSET_REGEX = '/eventeule-.*\.zip/i';

    private $updateChecker = null;

    public function register(): void
    {
        $this->init_update_checker();

        add_action('admin_post_eventeule_check_updates', [$this, 'handle_manual_update_check']);

        /**
         * Optionaler Fallback:
         * Falls WordPress den von PUC ermittelten Update-Eintrag nicht stabil
         * im update_plugins-Transient hält, injizieren wir ihn beim Schreiben.
         */
        add_filter('pre_set_site_transient_update_plugins', [$this, 'ensure_update_in_transient'], 20);
    }

    private function init_update_checker(): void
    {
        if ($this->updateChecker !== null) {
            return;
        }

        if (!class_exists(PucFactory::class)) {
            if ($this->is_debug()) {
                error_log('EventEule Updater: Plugin Update Checker library not found.');
            }
            return;
        }

        $this->updateChecker = PucFactory::buildUpdateChecker(
            self::REPOSITORY_URL,
            EVENTEULE_FILE,
            self::SLUG
        );

        $githubToken = $this->get_github_token();
        if ($githubToken !== '') {
            $this->updateChecker->setAuthentication($githubToken);
        }

        $this->updateChecker->getVcsApi()->enableReleaseAssets(self::RELEASE_ASSET_REGEX);

        if ($this->is_debug()) {
            add_action('puc_check_now-' . self::SLUG, [$this, 'log_update_check'], 10, 1);
            add_filter('puc_request_info_result-' . self::SLUG, [$this, 'log_api_response'], 20, 2);

            error_log('EventEule Updater: initialized');
            error_log('EventEule Updater: plugin_basename=' . plugin_basename(EVENTEULE_FILE));
            error_log('EventEule Updater: current_version=' . EVENTEULE_VERSION);
        }
    }

    public function handle_manual_update_check(): void
    {
        if (!current_user_can('update_plugins')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'eventeule'));
        }

        check_admin_referer('eventeule_check_updates', 'eventeule_nonce');

        $this->init_update_checker();

        if ($this->updateChecker === null) {
            wp_safe_redirect(add_query_arg(
                [
                    'update-check' => 'error',
                    'error-detail' => rawurlencode('Updater could not be initialized'),
                    'nav' => 'einstellungen',
                ],
                admin_url('admin.php?page=eventeule')
            ));
            exit;
        }

        try {
            $this->updateChecker->resetUpdateState();

            delete_site_transient('update_plugins');
            wp_clean_plugins_cache(true);

            $this->updateChecker->checkForUpdates();

            delete_site_transient('update_plugins');
            wp_clean_plugins_cache(true);

            $update = $this->updateChecker->getUpdate();

            $status = (
                $update !== null
                && isset($update->version)
                && version_compare($update->version, EVENTEULE_VERSION, '>')
            ) ? 'available' : 'none';

            wp_safe_redirect(add_query_arg(
                [
                    'update-check' => $status,
                    'version'      => $update->version ?? '',
                    'nav'          => 'einstellungen',
                ],
                admin_url('admin.php?page=eventeule')
            ));
            exit;
        } catch (\Throwable $e) {
            if ($this->is_debug()) {
                error_log('EventEule Updater Error: ' . $e->getMessage());
            }

            wp_safe_redirect(add_query_arg(
                [
                    'update-check' => 'error',
                    'error-detail' => rawurlencode($e->getMessage()),
                    'nav'          => 'einstellungen',
                ],
                admin_url('admin.php?page=eventeule')
            ));
            exit;
        }
    }

    public function ensure_update_in_transient($transient)
    {
        if ($this->updateChecker === null) {
            return $transient;
        }

        $update = $this->updateChecker->getUpdate();
        if ($update === null) {
            return $transient;
        }

        if (!is_object($transient)) {
            $transient = new \stdClass();
        }

        if (!isset($transient->response) || !is_array($transient->response)) {
            $transient->response = [];
        }

        if (!isset($transient->no_update) || !is_array($transient->no_update)) {
            $transient->no_update = [];
        }

        $pluginFile = plugin_basename(EVENTEULE_FILE);
        $transient->response[$pluginFile] = $update->toWpFormat();
        unset($transient->no_update[$pluginFile]);

        if ($this->is_debug()) {
            error_log(
                sprintf(
                    'EventEule Updater: injected update into transient: plugin=%s current=%s latest=%s',
                    $pluginFile,
                    EVENTEULE_VERSION,
                    $update->version
                )
            );
        }

        return $transient;
    }

    private function get_github_token(): string
    {
        $token = get_option('eventeule_github_token', '');
        if (is_string($token) && $token !== '') {
            return trim($token);
        }

        if (defined('GITHUB_ACCESS_TOKEN') && GITHUB_ACCESS_TOKEN !== '') {
            return trim((string) GITHUB_ACCESS_TOKEN);
        }

        if (!empty($_ENV['GITHUB_ACCESS_TOKEN'])) {
            return trim((string) $_ENV['GITHUB_ACCESS_TOKEN']);
        }

        $configFile = EVENTEULE_PATH . 'config-local.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
            if (is_array($config) && !empty($config['github_token'])) {
                return trim((string) $config['github_token']);
            }
        }

        return '';
    }

    public function log_update_check($checkerInstance): void
    {
        if (!$this->is_debug()) {
            return;
        }

        error_log('EventEule Updater: checking for updates');
        error_log('EventEule Updater: plugin_basename=' . plugin_basename(EVENTEULE_FILE));
        error_log('EventEule Updater: current_version=' . EVENTEULE_VERSION);

        if ($this->updateChecker !== null) {
            $update = $this->updateChecker->getUpdate();

            if ($update !== null) {
                error_log('EventEule Updater: update_available=' . $update->version);
                error_log('EventEule Updater: download_url=' . ($update->download_url ?? 'n/a'));
            } else {
                error_log('EventEule Updater: no update available');
            }
        }
    }

    public function log_api_response($pluginInfo, $result)
    {
        if (!$this->is_debug()) {
            return $pluginInfo;
        }

        if ($result === null) {
            error_log('EventEule Updater: API returned null');
            return $pluginInfo;
        }

        if (is_wp_error($result)) {
            error_log('EventEule Updater: API error: ' . $result->get_error_message());
            return $pluginInfo;
        }

        if (is_object($result)) {
            if (isset($result->version)) {
                error_log('EventEule Updater: API version=' . $result->version);
            }

            if (isset($result->download_url)) {
                error_log('EventEule Updater: API download_url=' . $result->download_url);
            }
        }

        return $pluginInfo;
    }

    private function is_debug(): bool
    {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
}