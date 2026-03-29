<?php

namespace EventEule\Integration\ElementorTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class EventLocationTag extends Tag
{
    public function get_name()
    {
        return 'eventeule-location';
    }

    public function get_title()
    {
        return __('Event Location', 'eventeule');
    }

    public function get_group()
    {
        return 'eventeule';
    }

    public function get_categories()
    {
        return [Module::TEXT_CATEGORY];
    }

    public function render()
    {
        $post_id = get_the_ID();
        $location = get_post_meta($post_id, '_eventeule_location', true);

        if (empty($location)) {
            return;
        }

        echo esc_html($location);
    }
}
