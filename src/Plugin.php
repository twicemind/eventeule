<?php

namespace EventEule;

use EventEule\Admin\Admin;
use EventEule\Admin\EventAdminColumns;
use EventEule\Admin\EventMetaBoxes;
use EventEule\Admin\EventRegistrationMetaBox;
use EventEule\Admin\RegistrationsAdmin;
use EventEule\Admin\UpdaterSettings;
use EventEule\Api\Api;
use EventEule\Domain\EventCategoryTaxonomy;
use EventEule\Domain\EventPostType;
use EventEule\Frontend\Frontend;
use EventEule\Integration\ElementorIntegration;
use EventEule\Registration\RegistrationController;
use EventEule\Registration\RegistrationRepository;
use EventEule\Repository\EventRepository;
use EventEule\Support\I18n;
use EventEule\Support\Updater;

class Plugin
{
    public function run(): void
    {
        $this->register_i18n();
        $this->register_updater();
        $this->register_domain();
        $this->register_admin();
        $this->register_frontend();
        $this->register_registration();
        $this->register_integrations();
        $this->register_api();
    }

    private function register_i18n(): void
    {
        $i18n = new I18n();
        $i18n->register();
    }

    private function register_updater(): void
    {
        $updater = new Updater();
        $updater->register();
    }

    private function register_domain(): void
    {
        $eventPostType = new EventPostType();
        $eventPostType->register();

        $eventCategoryTaxonomy = new EventCategoryTaxonomy();
        $eventCategoryTaxonomy->register();
    }

    private function register_admin(): void
    {
        if (is_admin()) {
            $eventRepository       = new EventRepository();
            $registrationRepository = new RegistrationRepository();

            $admin = new Admin($eventRepository, $registrationRepository);
            $admin->register();

            $metaBoxes = new EventMetaBoxes();
            $metaBoxes->register();

            $adminColumns = new EventAdminColumns();
            $adminColumns->register();

            $updaterSettings = new UpdaterSettings();
            $updaterSettings->register();

            $registrationMetaBox = new EventRegistrationMetaBox($registrationRepository);
            $registrationMetaBox->register();

            $registrationsAdmin = new RegistrationsAdmin($registrationRepository);
            $registrationsAdmin->register();
        }
    }

    private function register_registration(): void
    {
        $registrationRepository = new RegistrationRepository();

        // Ensure the DB table exists on existing installs (idempotent)
        $dbVersion = get_option('eventeule_reg_db_version', '');
        if ($dbVersion !== '1.0') {
            RegistrationRepository::create_table();
            update_option('eventeule_reg_db_version', '1.0');
        }

        $registrationController = new RegistrationController($registrationRepository);
        $registrationController->register();
    }

    private function register_frontend(): void
    {
        $frontend = new Frontend();
        $frontend->register();
    }

    private function register_api(): void
    {
        $api = new Api();
        $api->register();
    }

    private function register_integrations(): void
    {
        // Elementor Integration
        if (did_action('elementor/loaded')) {
            $elementorIntegration = new ElementorIntegration();
            $elementorIntegration->register();
        }
    }
}