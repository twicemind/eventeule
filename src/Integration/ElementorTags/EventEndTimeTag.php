<?php

namespace EventEule\Integration\ElementorTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class EventEndTimeTag extends Tag
{
    public function get_name()
    {
        return 'eventeule-end-time';
    }

    public function get_title()
    {
        return __('Event End Time', 'eventeule');
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
        $end_time = get_post_meta($post_id, '_eventeule_end_time', true);

        if (empty($end_time)) {
            return;
        }

        echo esc_html($end_time);
    }
}
