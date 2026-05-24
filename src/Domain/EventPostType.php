<?php

namespace EventEule\Domain;

class EventPostType
{
    public const POST_TYPE = 'eventeule_event';

    public function register(): void
    {
        add_action('init', [$this, 'register_post_type']);
        add_filter('elementor/utils/get_public_post_types', [$this, 'add_elementor_support']);
        add_filter('use_block_editor_for_post_type', [$this, 'disable_block_editor'], 10, 2);
    }

    public function register_post_type(): void
    {
        $labels = [
            'name'                  => __('Events', 'eventeule'),
            'singular_name'         => __('Event', 'eventeule'),
            'add_new'               => __('Add New', 'eventeule'),
            'add_new_item'          => __('Add New Event', 'eventeule'),
            'edit_item'             => __('Edit Event', 'eventeule'),
            'new_item'              => __('New Event', 'eventeule'),
            'view_item'             => __('View Event', 'eventeule'),
            'search_items'          => __('Search Events', 'eventeule'),
            'not_found'             => __('No events found', 'eventeule'),
            'not_found_in_trash'    => __('No events found in Trash', 'eventeule'),
            'menu_name'             => __('Events', 'eventeule'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'elementor'],
            'has_archive'        => 'events',
            'rewrite'            => [
                'slug'       => 'events',
                'with_front' => false,
            ],
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Elementor may pass non-array values depending on version/state.
     * Keep this callback defensive to avoid admin fatals.
     *
     * @param mixed $post_types
     */
    public function add_elementor_support($post_types): array
    {
        if (!is_array($post_types)) {
            $post_types = [];
        }

        $post_types[self::POST_TYPE] = self::POST_TYPE;
        return $post_types;
    }

    /**
     * @param mixed $postType
     */
    public function disable_block_editor(bool $useBlockEditor, $postType): bool
    {
        if (!is_string($postType)) {
            return $useBlockEditor;
        }

        // Compatibility fallback: on affected stacks the block editor can crash
        // for core types (post/page). Keep editing available via classic editor.
        if (in_array($postType, [self::POST_TYPE, 'post', 'page'], true)) {
            return false;
        }

        return $useBlockEditor;
    }
}