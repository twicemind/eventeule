<?php
/**
 * Plugin Name: EventEule
 * Plugin URI: https://eventeule.twicemind.com
 * Description: WordPress Event Plugin for events, appointments and activities for Bücherei Huisheim
 * Version: 3.2.5
 * Author: Thomas Herfort
 * Author URI: https://twicemind.com
 * Text Domain: eventeule
 * Update URI: https://github.com/twicemind/eventeule
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EVENTEULE_VERSION', '3.2.5');
define('EVENTEULE_FILE', __FILE__);
define('EVENTEULE_PATH', plugin_dir_path(__FILE__));
define('EVENTEULE_URL', plugin_dir_url(__FILE__));

if (file_exists(EVENTEULE_PATH . 'vendor/autoload.php')) {
    require_once EVENTEULE_PATH . 'vendor/autoload.php';
}

register_activation_hook(EVENTEULE_FILE, ['EventEule\\Support\\Activator', 'activate']);
register_deactivation_hook(EVENTEULE_FILE, ['EventEule\\Support\\Deactivator', 'deactivate']);

function eventeule(): EventEule\Plugin {
    static $plugin = null;

    if ($plugin === null) {
        $plugin = new EventEule\Plugin();
    }

    return $plugin;
}

add_action('plugins_loaded', function () {
    eventeule()->run();
});