<?php
namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if (!defined('ABSPATH')) {
    exit;
}

class EventDateBoxWidget extends Widget_Base
{
    public function get_name()
    {
        return 'eventeule_date_box';
    }

    public function get_title()
    {
        return __('Event Date Box', 'eventeule');
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
        return ['event', 'date', 'calendar', 'box', 'eventeule'];
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
            'date_source',
            [
                'label' => __('Date Source', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'start',
                'options' => [
                    'start' => __('Start Date', 'eventeule'),
                    'end' => __('End Date', 'eventeule'),
                ],
            ]
        );

        $this->add_control(
            'month_format',
            [
                'label' => __('Month Format', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'M',
                'options' => [
                    'F' => __('Full (January)', 'eventeule'),
                    'M' => __('Short (Jan)', 'eventeule'),
                ],
            ]
        );

        $this->add_control(
            'hide_if_empty',
            [
                'label' => __('Hide if Date Empty', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'eventeule'),
                'label_off' => __('No', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'date_source' => 'end',
                ],
            ]
        );

        $this->end_controls_section();

        // Box Style Section
        $this->start_controls_section(
            'box_style_section',
            [
                'label' => __('Box Style', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'box_size',
            [
                'label' => __('Box Size', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 300,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-box' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'box_background',
                'label' => __('Background', 'eventeule'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .eventeule-date-box',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'default' => '#f5f5f5',
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'box_border',
                'label' => __('Border', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-date-box',
            ]
        );

        $this->add_responsive_control(
            'box_border_radius',
            [
                'label' => __('Border Radius', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'unit' => 'px',
                    'top' => 4,
                    'right' => 4,
                    'bottom' => 4,
                    'left' => 4,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'box_padding',
            [
                'label' => __('Padding', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'unit' => 'px',
                    'top' => 15,
                    'right' => 15,
                    'bottom' => 15,
                    'left' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'box_alignment',
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
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-box-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Day Style Section
        $this->start_controls_section(
            'day_style_section',
            [
                'label' => __('Day Style', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'day_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-day' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'day_typography',
                'label' => __('Typography', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-date-day',
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => 36,
                        ],
                    ],
                    'font_weight' => [
                        'default' => '700',
                    ],
                    'line_height' => [
                        'default' => [
                            'unit' => 'em',
                            'size' => 1,
                        ],
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'day_spacing',
            [
                'label' => __('Bottom Spacing', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-day' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Month Style Section
        $this->start_controls_section(
            'month_style_section',
            [
                'label' => __('Month Style', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'month_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#666666',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-date-month' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'month_typography',
                'label' => __('Typography', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-date-month',
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => 14,
                        ],
                    ],
                    'font_weight' => [
                        'default' => '400',
                    ],
                    'text_transform' => [
                        'default' => 'uppercase',
                    ],
                    'line_height' => [
                        'default' => [
                            'unit' => 'em',
                            'size' => 1,
                        ],
                    ],
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

        // Get the appropriate date
        $meta_key = $settings['date_source'] === 'start' ? '_eventeule_start_date' : '_eventeule_end_date';
        $date = get_post_meta($post_id, $meta_key, true);

        // Handle empty date
        if (empty($date)) {
            if ($settings['date_source'] === 'end' && $settings['hide_if_empty'] === 'yes') {
                return;
            }
            // Show placeholder for empty date
            echo '<div class="eventeule-date-box-wrapper">';
            echo '<div class="eventeule-date-box">';
            echo '<div class="eventeule-date-day">--</div>';
            echo '<div class="eventeule-date-month">' . esc_html__('No Date', 'eventeule') . '</div>';
            echo '</div>';
            echo '</div>';
            return;
        }

        // Format date parts
        $day = date_i18n('j', strtotime($date));
        $month = date_i18n($settings['month_format'], strtotime($date));

        // Output
        echo '<div class="eventeule-date-box-wrapper">';
        echo '<div class="eventeule-date-box">';
        echo '<div class="eventeule-date-day">' . esc_html($day) . '</div>';
        echo '<div class="eventeule-date-month">' . esc_html($month) . '</div>';
        echo '</div>';
        echo '</div>';
    }

    protected function content_template()
    {
        ?>
        <#
        var monthLabel = settings.month_format === 'F' ? 'January' : 'Jan';
        #>
        <div class="eventeule-date-box-wrapper">
            <div class="eventeule-date-box">
                <div class="eventeule-date-day">15</div>
                <div class="eventeule-date-month">{{ monthLabel }}</div>
            </div>
        </div>
        <?php
    }
}
