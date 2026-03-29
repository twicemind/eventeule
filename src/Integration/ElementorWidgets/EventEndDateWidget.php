<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class EventEndDateWidget extends Widget_Base
{
    public function get_name()
    {
        return 'eventeule_event_end_date';
    }

    public function get_title()
    {
        return __('Event End Date', 'eventeule');
    }

    public function get_icon()
    {
        return 'eicon-calendar';
    }

    public function get_categories()
    {
        return ['eventeule'];
    }

    public function get_keywords()
    {
        return ['event', 'date', 'end', 'calendar', 'eventeule'];
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
            'date_format',
            [
                'label' => __('Date Format', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'd.m.Y',
                'options' => [
                    'd.m.Y' => __('DD.MM.YYYY (e.g. 31.12.2024)', 'eventeule'),
                    'Y-m-d' => __('YYYY-MM-DD (e.g. 2024-12-31)', 'eventeule'),
                    'm/d/Y' => __('MM/DD/YYYY (e.g. 12/31/2024)', 'eventeule'),
                    'l, j. F Y' => __('Saturday, 31. December 2024)', 'eventeule'),
                    'j. F Y' => __('31. December 2024', 'eventeule'),
                    'F j, Y' => __('December 31, 2024', 'eventeule'),
                    'custom' => __('Custom', 'eventeule'),
                ],
            ]
        );

        $this->add_control(
            'custom_date_format',
            [
                'label' => __('Custom Format', 'eventeule'),
                'type' => Controls_Manager::TEXT,
                'default' => 'd.m.Y',
                'placeholder' => 'd.m.Y',
                'condition' => [
                    'date_format' => 'custom',
                ],
                'description' => sprintf(
                    __('Use PHP date format. See %s', 'eventeule'),
                    '<a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">PHP date format</a>'
                ),
            ]
        );

        $this->add_control(
            'show_icon',
            [
                'label' => __('Show Icon', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'eventeule'),
                'label_off' => __('Hide', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'icon',
            [
                'label' => __('Icon', 'eventeule'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-calendar-check',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'show_icon' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'hide_if_empty',
            [
                'label' => __('Hide if No End Date', 'eventeule'),
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
                    '{{WRAPPER}} .eventeule-end-date' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .eventeule-end-date',
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
                    '{{WRAPPER}} .eventeule-end-date-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __('Icon Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-icon' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_icon' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eventeule-date-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eventeule-date-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_icon' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => __('Icon Spacing', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_icon' => 'yes',
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

        $end_date = get_post_meta($post_id, '_eventeule_end_date', true);

        if (empty($end_date)) {
            if ($settings['hide_if_empty'] !== 'yes') {
                echo '<div class="eventeule-end-date-wrapper eventeule-no-date">';
                echo '<span class="eventeule-end-date">' . esc_html__('No end date', 'eventeule') . '</span>';
                echo '</div>';
            }
            return;
        }

        // Determine date format
        $format = $settings['date_format'];
        if ($format === 'custom' && !empty($settings['custom_date_format'])) {
            $format = $settings['custom_date_format'];
        }

        // Format date
        $formatted_date = date_i18n($format, strtotime($end_date));

        echo '<div class="eventeule-end-date-wrapper">';
        
        // Show icon if enabled
        if ($settings['show_icon'] === 'yes') {
            echo '<span class="eventeule-date-icon" style="display: inline-flex; align-items: center; vertical-align: middle;">';
            \Elementor\Icons_Manager::render_icon($settings['icon'], ['aria-hidden' => 'true']);
            echo '</span>';
        }
        
        echo '<span class="eventeule-end-date">' . esc_html($formatted_date) . '</span>';
        echo '</div>';
    }

    protected function content_template()
    {
        ?>
        <#
        var format = settings.date_format === 'custom' ? settings.custom_date_format : settings.date_format;
        #>
        <div class="eventeule-end-date-wrapper">
            <# if (settings.show_icon === 'yes') { #>
                <span class="eventeule-date-icon">
                    <i class="{{ settings.icon.value }}" aria-hidden="true"></i>
                </span>
            <# } #>
            <span class="eventeule-end-date">31.12.2024</span>
        </div>
        <?php
    }
}
