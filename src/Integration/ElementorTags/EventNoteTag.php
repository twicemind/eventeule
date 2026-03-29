<?php

namespace EventEule\Integration\ElementorTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class EventNoteTag extends Tag
{
    public function get_name()
    {
        return 'eventeule-note';
    }

    public function get_title()
    {
        return __('Event Note', 'eventeule');
    }

    public function get_group()
    {
        return 'eventeule';
    }

    public function get_categories()
    {
        return [
            Module::TEXT_CATEGORY,
            Module::POST_META_CATEGORY,
        ];
    }

    public function render()
    {
        $post_id = get_the_ID();
        $note = get_post_meta($post_id, '_eventeule_note', true);

        if (empty($note)) {
            return;
        }

        echo wp_kses_post(nl2br($note));
    }
}
