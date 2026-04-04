<?php

namespace EventEule\Repository;

use EventEule\Domain\EventCategoryTaxonomy;
use EventEule\Domain\EventPostType;

class EventRepository
{
    /**
     * @param array<string, mixed> $args
     * @return \WP_Post[]
     */
    public function get_events(array $args = []): array
    {
        $defaults = [
            'limit'         => 10,
            'featured_only' => false,
            'show_past'     => false,
            'category'      => '',
        ];

        $args = wp_parse_args($args, $defaults);

        $metaQuery = [];
        $taxQuery  = [];

        if (!$args['show_past']) {
            $metaQuery[] = [
                'key'     => '_eventeule_start_date',
                'value'   => current_time('Y-m-d'),
                'compare' => '>=',
                'type'    => 'DATE',
            ];
        }

        if ($args['featured_only']) {
            $metaQuery[] = [
                'key'     => '_eventeule_featured',
                'value'   => '1',
                'compare' => '=',
            ];
        }

        if (!empty($args['category'])) {
            $taxQuery[] = [
                'taxonomy' => EventCategoryTaxonomy::TAXONOMY,
                'field'    => 'slug',
                'terms'    => (string) $args['category'],
            ];
        }

        $queryArgs = [
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => (int) $args['limit'],
            'meta_key'       => '_eventeule_start_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => $metaQuery,
        ];

        if (!empty($taxQuery)) {
            $queryArgs['tax_query'] = $taxQuery;
        }

        $query = new \WP_Query($queryArgs);

        return $query->posts;
    }

    /**
     * @return array<string, mixed>
     */
    public function get_event_data(\WP_Post $post): array
    {
        $terms = get_the_terms($post, EventCategoryTaxonomy::TAXONOMY);

        return [
            'id'                => $post->ID,
            'title'             => get_the_title($post),
            'permalink'         => get_permalink($post),
            'excerpt'           => get_the_excerpt($post),
            'content'           => apply_filters('the_content', $post->post_content),
            'start_date'        => (string) get_post_meta($post->ID, '_eventeule_start_date', true),
            'end_date'          => (string) get_post_meta($post->ID, '_eventeule_end_date', true),
            'start_time'        => (string) get_post_meta($post->ID, '_eventeule_start_time', true),
            'end_time'          => (string) get_post_meta($post->ID, '_eventeule_end_time', true),
            'location'          => (string) get_post_meta($post->ID, '_eventeule_location', true),
            'registration_url'  => (string) get_post_meta($post->ID, '_eventeule_registration_url', true),
            'note'              => (string) get_post_meta($post->ID, '_eventeule_note', true),
            'featured'          => get_post_meta($post->ID, '_eventeule_featured', true) === '1',
            'cancelled'         => get_post_meta($post->ID, '_eventeule_cancelled', true) === '1',
            'categories'        => is_array($terms) ? $terms : [],
        ];
    }
}