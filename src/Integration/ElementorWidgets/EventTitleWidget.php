<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class EventTitleWidget extends Widget_Base
{
    public function get_name()
    {
        return 'eventeule_event_title';
    }

    public function get_title()
    {
        return __('Event Title', 'eventeule');
    }

    public function get_icon()
    {
        return 'eicon-post-title';
    }

    public function get_categories()
    {
        return ['eventeule'];
    }

    public function get_keywords()
    {
        return ['event', 'title', 'name', 'eventeule'];
    }

    protected function register_controls()
    {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'eventeule'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'html_tag',
            [
                'label' => __('HTML Tag', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'h2',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
            ]
        );

        $this->add_control(
            'link_to_event',
            [
                'label' => __('Link to Event', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'eventeule'),
                'label_off' => __('No', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .eventeule-event-title',
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => __('Alignment', 'eventeule'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'eventeule'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'eventeule'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'eventeule'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $post_id = get_the_ID();

        if (!$post_id) {
            return;
        }

        $title = get_the_title($post_id);
        $html_tag = $settings['html_tag'];

        echo '<' . esc_html($html_tag) . ' class="eventeule-event-title">';
        
        if ($settings['link_to_event'] === 'yes') {
            echo '<a href="' . esc_url(get_permalink($post_id)) . '">';
            echo esc_html($title);
            echo '</a>';
        } else {
            echo esc_html($title);
        }
        
        echo '</' . esc_html($html_tag) . '>';
    }

    protected function content_template()
    {
        ?>
        <#
        var htmlTag = settings.html_tag;
        #>
        <{{{ htmlTag }}} class="eventeule-event-title">
            <# if (settings.link_to_event === 'yes') { #>
                <a href="#">{{ settings.title || 'Event Title' }}</a>
            <# } else { #>
                {{ settings.title || 'Event Title' }}
            <# } #>
        </{{{ htmlTag }}}>
        <?php
    }
}
