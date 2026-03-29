<?php

namespace EventEule\Integration;

use EventEule\Domain\EventPostType;

class ElementorIntegration
{
    public function register(): void
    {
        // Register custom fields for Elementor
        add_action('elementor/init', [$this, 'register_custom_fields']);
        
        // Registriere Dynamic Tags
        add_action('elementor/dynamic_tags/register', [$this, 'register_dynamic_tags']);
    }

    public function register_custom_fields(): void
    {
        if (!did_action('elementor/loaded')) {
            return;
        }

        // Register ACF-compatible fields for Elementor
        add_filter('acf/get_field_groups', [$this, 'add_pseudo_acf_fields'], 10, 1);
    }

    /**
     * Makes our custom fields available to Elementor (ACF-compatible)
     */
    public function add_pseudo_acf_fields($field_groups)
    {
        global $post;
        
        if (!$post || $post->post_type !== EventPostType::POST_TYPE) {
            return $field_groups;
        }

        // Create a pseudo ACF field group for Elementor
        $field_groups[] = [
            'ID' => 'group_eventeule_fields',
            'title' => 'EventEule Fields',
            'fields' => [
                [
                    'key' => 'field_eventeule_start_date',
                    'label' => __('Event Start Date', 'eventeule'),
                    'name' => '_eventeule_start_date',
                    'type' => 'date',
                    'return_format' => 'Y-m-d',
                ],
                [
                    'key' => 'field_eventeule_end_date',
                    'label' => __('Event End Date', 'eventeule'),
                    'name' => '_eventeule_end_date',
                    'type' => 'date',
                    'return_format' => 'Y-m-d',
                ],
                [
                    'key' => 'field_eventeule_start_time',
                    'label' => __('Event Start Time', 'eventeule'),
                    'name' => '_eventeule_start_time',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_eventeule_end_time',
                    'label' => __('Event End Time', 'eventeule'),
                    'name' => '_eventeule_end_time',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_eventeule_location',
                    'label' => __('Event Location', 'eventeule'),
                    'name' => '_eventeule_location',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_eventeule_registration_url',
                    'label' => __('Event Registration URL', 'eventeule'),
                    'name' => '_eventeule_registration_url',
                    'type' => 'url',
                ],
                [
                    'key' => 'field_eventeule_note',
                    'label' => __('Event Note', 'eventeule'),
                    'name' => '_eventeule_note',
                    'type' => 'textarea',
                ],
                [
                    'key' => 'field_eventeule_featured',
                    'label' => __('Featured Event', 'eventeule'),
                    'name' => '_eventeule_featured',
                    'type' => 'true_false',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => EventPostType::POST_TYPE,
                    ],
                ],
            ],
        ];

        return $field_groups;
    }

    /**
     * Register custom dynamic tags for Elementor
     */
    public function register_dynamic_tags($dynamic_tags_manager): void
    {
        // Registriere die Tag-Gruppe
        $dynamic_tags_manager->register_group(
            'eventeule',
            [
                'title' => __('EventEule', 'eventeule'),
            ]
        );

        // Lade Custom Tag-Klassen
        require_once EVENTEULE_PATH . 'src/Integration/ElementorTags/EventStartDateTag.php';
        require_once EVENTEULE_PATH . 'src/Integration/ElementorTags/EventEndDateTag.php';
        require_once EVENTEULE_PATH . 'src/Integration/ElementorTags/EventStartTimeTag.php';
        require_once EVENTEULE_PATH . 'src/Integration/ElementorTags/EventEndTimeTag.php';
        require_once EVENTEULE_PATH . 'src/Integration/ElementorTags/EventLocationTag.php';
        require_once EVENTEULE_PATH . 'src/Integration/ElementorTags/EventRegistrationUrlTag.php';
        require_once EVENTEULE_PATH . 'src/Integration/ElementorTags/EventNoteTag.php';

        // Registriere alle Tags
        $dynamic_tags_manager->register(new \EventEule\Integration\ElementorTags\EventStartDateTag());
        $dynamic_tags_manager->register(new \EventEule\Integration\ElementorTags\EventEndDateTag());
        $dynamic_tags_manager->register(new \EventEule\Integration\ElementorTags\EventStartTimeTag());
        $dynamic_tags_manager->register(new \EventEule\Integration\ElementorTags\EventEndTimeTag());
        $dynamic_tags_manager->register(new \EventEule\Integration\ElementorTags\EventLocationTag());
        $dynamic_tags_manager->register(new \EventEule\Integration\ElementorTags\EventRegistrationUrlTag());
        $dynamic_tags_manager->register(new \EventEule\Integration\ElementorTags\EventNoteTag());
    }
}
