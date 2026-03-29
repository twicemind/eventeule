<?php

namespace EventEule\Integration\ElementorTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class EventRegistrationUrlTag extends Tag
{
    public function get_name()
    {
        return 'eventeule-registration-url';
    }

    public function get_title()
    {
        return __('Event Registration URL', 'eventeule');
    }

    public function get_group()
    {
        return 'eventeule';
    }

    public function get_categories()
    {
        return [
            Module::TEXT_CATEGORY,
            Module::URL_CATEGORY,
        ];
    }

    public function render()
    {
        $post_id = get_the_ID();
        $url = get_post_meta($post_id, '_eventeule_registration_url', true);

        if (empty($url)) {
            return;
        }

        echo esc_url($url);
    }
}
