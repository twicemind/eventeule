<?php

namespace EventEule\Admin;

use EventEule\Domain\EventPostType;

class EventAdminColumns
{
    public function register(): void
    {
        add_filter('manage_' . EventPostType::POST_TYPE . '_posts_columns', [$this, 'add_columns']);
        add_action('manage_' . EventPostType::POST_TYPE . '_posts_custom_column', [$this, 'render_column'], 10, 2);
        add_filter('manage_edit-' . EventPostType::POST_TYPE . '_sortable_columns', [$this, 'register_sortable_columns']);
        add_action('pre_get_posts', [$this, 'handle_sorting']);
    }

    public function add_columns(array $columns): array
    {
        $newColumns = [];

        foreach ($columns as $key => $label) {
            $newColumns[$key] = $label;

            if ($key === 'title') {
                $newColumns['eventeule_start_date'] = __('Start Date', 'eventeule');
                $newColumns['eventeule_location']   = __('Location', 'eventeule');
                $newColumns['eventeule_featured']   = __('Featured', 'eventeule');
            }
        }

        return $newColumns;
    }

    public function render_column(string $column, int $postId): void
    {
        switch ($column) {
            case 'eventeule_start_date':
                $startDate = get_post_meta($postId, '_eventeule_start_date', true);
                $startTime = get_post_meta($postId, '_eventeule_start_time', true);

                if ($startDate !== '') {
                    $output = esc_html($startDate);

                    if ($startTime !== '') {
                        $output .= '<br><small>' . esc_html($startTime) . '</small>';
                    }

                    echo wp_kses_post($output);
                } else {
                    echo '&mdash;';
                }
                break;

            case 'eventeule_location':
                $location = get_post_meta($postId, '_eventeule_location', true);

                if ($location !== '') {
                    echo esc_html($location);
                } else {
                    echo '&mdash;';
                }
                break;

            case 'eventeule_featured':
                $featured = get_post_meta($postId, '_eventeule_featured', true);

                echo $featured === '1' ? '⭐' : '&mdash;';
                break;
        }
    }

    public function register_sortable_columns(array $columns): array
    {
        $columns['eventeule_start_date'] = 'eventeule_start_date';
        $columns['eventeule_location']   = 'eventeule_location';
        $columns['eventeule_featured']   = 'eventeule_featured';

        return $columns;
    }

    public function handle_sorting(\WP_Query $query): void
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        global $pagenow;

        if ($pagenow !== 'edit.php') {
            return;
        }

        if ($query->get('post_type') !== EventPostType::POST_TYPE) {
            return;
        }

        $orderby = $query->get('orderby');

        switch ($orderby) {
            case 'eventeule_start_date':
                $query->set('meta_key', '_eventeule_start_date');
                $query->set('orderby', 'meta_value');
                break;

            case 'eventeule_location':
                $query->set('meta_key', '_eventeule_location');
                $query->set('orderby', 'meta_value');
                break;

            case 'eventeule_featured':
                $query->set('meta_key', '_eventeule_featured');
                $query->set('orderby', 'meta_value_num');
                break;
        }

        if ($orderby === '' || $orderby === null) {
            $query->set('meta_key', '_eventeule_start_date');
            $query->set('orderby', 'meta_value');
            $query->set('order', 'ASC');
        }
    }
}