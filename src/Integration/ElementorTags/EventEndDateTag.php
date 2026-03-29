<?php

namespace EventEule\Integration\ElementorTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class EventEndDateTag extends Tag
{
    public function get_name()
    {
        return 'eventeule-end-date';
    }

    public function get_title()
    {
        return __('Event End Date', 'eventeule');
    }

    public function get_group()
    {
        return 'eventeule';
    }

    public function get_categories()
    {
        return [Module::TEXT_CATEGORY];
    }

    protected function register_controls()
    {
        $this->add_control(
            'date_format',
            [
                'label' => __('Date Format', 'eventeule'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'd.m.Y',
                'options' => [
                    'd.m.Y' => __('DD.MM.YYYY (e.g. 31.12.2024)', 'eventeule'),
                    'm/d/Y' => __('MM/DD/YYYY (e.g. 12/31/2024)', 'eventeule'),
                    'Y-m-d' => __('YYYY-MM-DD (e.g. 2024-12-31)', 'eventeule'),
                    'F j, Y' => __('Full (e.g. December 31, 2024)', 'eventeule'),
                    'j. F Y' => __('European (e.g. 31. Dezember 2024)', 'eventeule'),
                    'l, j. F Y' => __('Full with weekday (e.g. Montag, 31. Dezember 2024)', 'eventeule'),
                ],
            ]
        );
    }

    public function render()
    {
        $post_id = get_the_ID();
        $end_date = get_post_meta($post_id, '_eventeule_end_date', true);

        if (empty($end_date)) {
            return;
        }

        $format = $this->get_settings('date_format');
        $timestamp = strtotime($end_date);
        
        echo esc_html(date_i18n($format, $timestamp));
    }
}
