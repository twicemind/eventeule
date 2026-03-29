<?php

namespace EventEule\Domain;

class EventCategoryTaxonomy
{
    public const TAXONOMY = 'eventeule_category';

    public function register(): void
    {
        add_action('init', [$this, 'register_taxonomy']);
    }

    public function register_taxonomy(): void
    {
        $labels = [
            'name'                       => __('Event Categories', 'eventeule'),
            'singular_name'              => __('Event Category', 'eventeule'),
            'search_items'               => __('Search Event Categories', 'eventeule'),
            'all_items'                  => __('All Event Categories', 'eventeule'),
            'edit_item'                  => __('Edit Event Category', 'eventeule'),
            'update_item'                => __('Update Event Category', 'eventeule'),
            'add_new_item'               => __('Add New Event Category', 'eventeule'),
            'new_item_name'              => __('New Event Category Name', 'eventeule'),
            'menu_name'                  => __('Categories', 'eventeule'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'hierarchical'       => true,
            'show_ui'            => true,
            'show_admin_column'  => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => [
                'slug'       => 'event-category',
                'with_front' => false,
            ],
        ];

        register_taxonomy(
            self::TAXONOMY,
            [EventPostType::POST_TYPE],
            $args
        );
    }
}