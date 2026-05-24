<?php

namespace EventEule\Domain;

class OpeningHoursPostType
{
    public const POST_TYPE = 'eventeule_opening';

    public function register(): void
    {
        add_action('init', [$this, 'register_post_type']);
        add_filter('elementor/utils/get_public_post_types', [$this, 'add_elementor_support']);
        add_filter('use_block_editor_for_post_type', [$this, 'disable_block_editor'], 10, 2);
    }

    public function register_post_type(): void
    {
        $labels = [
            'name'               => __('Öffnungszeiten', 'eventeule'),
            'singular_name'      => __('Öffnungszeit', 'eventeule'),
            'add_new'            => __('Neu hinzufügen', 'eventeule'),
            'add_new_item'       => __('Neue Öffnungszeit hinzufügen', 'eventeule'),
            'edit_item'          => __('Öffnungszeit bearbeiten', 'eventeule'),
            'new_item'           => __('Neue Öffnungszeit', 'eventeule'),
            'view_item'          => __('Öffnungszeit ansehen', 'eventeule'),
            'search_items'       => __('Öffnungszeiten durchsuchen', 'eventeule'),
            'not_found'          => __('Keine Öffnungszeiten gefunden', 'eventeule'),
            'not_found_in_trash' => __('Keine Öffnungszeiten im Papierkorb', 'eventeule'),
            'menu_name'          => __('Öffnungszeiten', 'eventeule'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false, // hidden from WP menu – lives in EventEule sidebar
            'show_in_rest'       => true,
            'query_var'          => true,
            'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'elementor'],
            'has_archive'        => false,
            'rewrite'            => false,
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
        if (is_string($postType) && $postType === self::POST_TYPE) {
            return false;
        }

        return $useBlockEditor;
    }
}
