<?php
namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;
use EventEule\Domain\EventPostType;
use EventEule\Domain\EventCategoryTaxonomy;

if (!defined('ABSPATH')) {
    exit;
}

class EventListWidget extends Widget_Base
{
    public function get_name()
    {
        return 'eventeule_event_list';
    }

    public function get_title()
    {
        return __('Event List', 'eventeule');
    }

    public function get_icon()
    {
        return 'eicon-post-list';
    }

    public function get_categories()
    {
        return ['eventeule'];
    }

    public function get_keywords()
    {
        return ['event', 'list', 'grid', 'posts', 'eventeule'];
    }

    protected function register_controls()
    {
        // Query Section
        $this->start_controls_section(
            'query_section',
            [
                'label' => __('Query', 'eventeule'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Events Per Page', 'eventeule'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 100,
            ]
        );

        $this->add_control(
            'event_filter',
            [
                'label' => __('Event Filter', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'upcoming',
                'options' => [
                    'all' => __('All Events', 'eventeule'),
                    'upcoming' => __('Upcoming Events', 'eventeule'),
                    'past' => __('Past Events', 'eventeule'),
                    'current' => __('Current Events', 'eventeule'),
                ],
            ]
        );

        $categories = get_terms([
            'taxonomy' => EventCategoryTaxonomy::TAXONOMY,
            'hide_empty' => false,
        ]);

        $category_options = ['' => __('All Categories', 'eventeule')];
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $category_options[$category->term_id] = $category->name;
            }
        }

        $this->add_control(
            'event_category',
            [
                'label' => __('Category', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => $category_options,
            ]
        );

        $this->add_control(
            'order_by',
            [
                'label' => __('Order By', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'start_date',
                'options' => [
                    'start_date' => __('Start Date', 'eventeule'),
                    'title' => __('Title', 'eventeule'),
                    'date' => __('Publish Date', 'eventeule'),
                    'modified' => __('Modified Date', 'eventeule'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => [
                    'ASC' => __('Ascending', 'eventeule'),
                    'DESC' => __('Descending', 'eventeule'),
                ],
            ]
        );

        $this->end_controls_section();

        // Layout Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => __('Layout', 'eventeule'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'layout_style',
            [
                'label' => __('Layout Style', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'horizontal',
                'options' => [
                    'horizontal' => __('Horizontal (Date + Content)', 'eventeule'),
                    'vertical' => __('Vertical (Card Style)', 'eventeule'),
                ],
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => '1',
                'tablet_default' => '1',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-list' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_responsive_control(
            'column_gap',
            [
                'label' => __('Column Gap', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-list' => 'column-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'row_gap',
            [
                'label' => __('Row Gap', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 30,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-list' => 'row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Content Section
        $this->start_controls_section(
            'content_options_section',
            [
                'label' => __('Content', 'eventeule'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label' => __('Show Image', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'eventeule'),
                'label_off' => __('No', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'image',
                'default' => 'medium',
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'eventeule'),
                'label_off' => __('No', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_date',
            [
                'label' => __('Show Date', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'eventeule'),
                'label_off' => __('No', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'date_position',
            [
                'label' => __('Date Position', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'separate',
                'options' => [
                    'separate' => __('Separate (Before Image)', 'eventeule'),
                    'overlay' => __('Overlay (Over Image)', 'eventeule'),
                ],
                'condition' => [
                    'show_date' => 'yes',
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'date_style',
            [
                'label' => __('Date Style', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'box',
                'options' => [
                    'box' => __('Date Box (Modern)', 'eventeule'),
                    'inline' => __('Inline Text', 'eventeule'),
                ],
                'condition' => [
                    'show_date' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'date_format',
            [
                'label' => __('Date Format', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'd.m.Y',
                'options' => [
                    'd.m.Y' => __('DD.MM.YYYY (31.12.2024)', 'eventeule'),
                    'm/d/Y' => __('MM/DD/YYYY (12/31/2024)', 'eventeule'),
                    'Y-m-d' => __('YYYY-MM-DD (2024-12-31)', 'eventeule'),
                    'F j, Y' => __('Full (December 31, 2024)', 'eventeule'),
                    'M j, Y' => __('Short (Dec 31, 2024)', 'eventeule'),
                    'j. F Y' => __('Day Month Year (31. December 2024)', 'eventeule'),
                ],
                'condition' => [
                    'show_date' => 'yes',
                    'date_style' => 'inline',
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
                'condition' => [
                    'show_date' => 'yes',
                    'date_style' => 'box',
                ],
            ]
        );

        $this->add_control(
            'show_location',
            [
                'label' => __('Show Location', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'eventeule'),
                'label_off' => __('No', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_time',
            [
                'label' => __('Show Time', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'eventeule'),
                'label_off' => __('No', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_description',
            [
                'label' => __('Show Description', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'eventeule'),
                'label_off' => __('No', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'description_source',
            [
                'label' => __('Description Source', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'short',
                'options' => [
                    'short' => __('Short Description (Meta Field)', 'eventeule'),
                    'excerpt' => __('Excerpt (Auto-generated)', 'eventeule'),
                ],
                'condition' => [
                    'show_description' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Length', 'eventeule'),
                'type' => Controls_Manager::NUMBER,
                'default' => 20,
                'min' => 0,
                'max' => 100,
                'condition' => [
                    'show_description' => 'yes',
                    'description_source' => 'excerpt',
                ],
            ]
        );

        $this->add_control(
            'show_read_more',
            [
                'label' => __('Show Button', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'eventeule'),
                'label_off' => __('No', 'eventeule'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'read_more_text',
            [
                'label' => __('Button Text', 'eventeule'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Read More', 'eventeule'),
                'condition' => [
                    'show_read_more' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_link_type',
            [
                'label' => __('Button Link', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'event',
                'options' => [
                    'event' => __('Event Detail Page', 'eventeule'),
                    'registration' => __('Registration URL', 'eventeule'),
                ],
                'condition' => [
                    'show_read_more' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Card Style
        $this->start_controls_section(
            'card_style_section',
            [
                'label' => __('Card', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'card_background',
                'label' => __('Background', 'eventeule'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .eventeule-event-item',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'default' => '#ffffff',
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'label' => __('Border', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-item',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => '1',
                            'right' => '1',
                            'bottom' => '1',
                            'left' => '1',
                            'unit' => 'px',
                        ],
                    ],
                    'color' => [
                        'default' => '#e5e7eb',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'card_border_radius',
            [
                'label' => __('Border Radius', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => '12',
                    'right' => '12',
                    'bottom' => '12',
                    'left' => '12',
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'label' => __('Box Shadow', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-item',
                'fields_options' => [
                    'box_shadow_type' => [
                        'default' => 'yes',
                    ],
                    'box_shadow' => [
                        'default' => [
                            'horizontal' => 0,
                            'vertical' => 2,
                            'blur' => 8,
                            'spread' => 0,
                            'color' => 'rgba(0,0,0,0.1)',
                        ],
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('Padding', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Image Style
        $this->start_controls_section(
            'image_style_section',
            [
                'label' => __('Image', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => __('Height', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 600,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'size' => 250,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-image' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_width',
            [
                'label' => __('Width', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 600,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'size' => 150,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-layout-horizontal .eventeule-event-image' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'layout_style' => 'horizontal',
                ],
            ]
        );

        $this->add_control(
            'image_object_fit',
            [
                'label' => __('Object Fit', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'cover',
                'options' => [
                    'cover' => __('Cover', 'eventeule'),
                    'contain' => __('Contain', 'eventeule'),
                    'fill' => __('Fill', 'eventeule'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-image img' => 'object-fit: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_border_radius',
            [
                'label' => __('Border Radius', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Title Style
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => __('Title', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-title',
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => 20,
                        ],
                    ],
                    'font_weight' => [
                        'default' => '600',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => __('Spacing', 'eventeule'),
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
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Meta Style
        $this->start_controls_section(
            'meta_style_section',
            [
                'label' => __('Meta', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'meta_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#666666',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-meta' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'meta_typography',
                'label' => __('Typography', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-meta',
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => 14,
                        ],
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'meta_spacing',
            [
                'label' => __('Spacing', 'eventeule'),
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
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-meta' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Date Box Style
        $this->start_controls_section(
            'date_box_style_section',
            [
                'label' => __('Date Box', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_date' => 'yes',
                    'date_style' => 'box',
                ],
            ]
        );

        $this->add_responsive_control(
            'date_box_size',
            [
                'label' => __('Box Size', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 150,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'size' => 90,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-date-box' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'date_box_background',
                'label' => __('Background', 'eventeule'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .eventeule-event-date-box',
                'fields_options' => [
                    'background' => [
                        'default' => 'gradient',
                    ],
                    'color' => [
                        'default' => '#667eea',
                    ],
                    'gradient_type' => [
                        'default' => 'linear',
                    ],
                    'color_b' => [
                        'default' => '#764ba2',
                    ],
                    'gradient_angle' => [
                        'default' => [
                            'unit' => 'deg',
                            'size' => 135,
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'date_box_day_color',
            [
                'label' => __('Day Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-date-day' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'date_box_day_typography',
                'label' => __('Day Typography', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-date-day',
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => 28,
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

        $this->add_control(
            'date_box_month_color',
            [
                'label' => __('Month Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-date-month' => 'color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'date_box_month_typography',
                'label' => __('Month Typography', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-date-month',
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => 11,
                        ],
                    ],
                    'font_weight' => [
                        'default' => '600',
                    ],
                    'text_transform' => [
                        'default' => 'uppercase',
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'date_box_border',
                'label' => __('Border', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-date-box',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'date_box_border_radius',
            [
                'label' => __('Border Radius', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-date-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'date_box_shadow',
                'label' => __('Box Shadow', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-date-box',
            ]
        );

        $this->end_controls_section();

        // Excerpt Style
        $this->start_controls_section(
            'excerpt_style_section',
            [
                'label' => __('Excerpt', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#666666',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt_typography',
                'label' => __('Typography', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-excerpt',
            ]
        );

        $this->add_responsive_control(
            'excerpt_spacing',
            [
                'label' => __('Spacing', 'eventeule'),
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
                    'size' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Button Style
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Button', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_read_more' => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs('button_tabs');

        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => __('Normal', 'eventeule'),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-read-more' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('Background Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-read-more' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_hover_tab',
            [
                'label' => __('Hover', 'eventeule'),
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label' => __('Text Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-read-more:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background_color',
            [
                'label' => __('Background Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'default' => '#005177',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-read-more:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typography', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-read-more',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'label' => __('Border', 'eventeule'),
                'selector' => '{{WRAPPER}} .eventeule-event-read-more',
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-read-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 10,
                    'right' => 20,
                    'bottom' => 10,
                    'left' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-read-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        
        // Build query args
        $args = [
            'post_type' => EventPostType::POST_TYPE,
            'posts_per_page' => $settings['posts_per_page'],
            'post_status' => 'publish',
        ];

        // Order
        if ($settings['order_by'] === 'start_date') {
            $args['meta_key'] = '_eventeule_start_date';
            $args['orderby'] = 'meta_value';
        } else {
            $args['orderby'] = $settings['order_by'];
        }
        $args['order'] = $settings['order'];

        // Category filter
        if (!empty($settings['event_category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => EventCategoryTaxonomy::TAXONOMY,
                    'field' => 'term_id',
                    'terms' => $settings['event_category'],
                ],
            ];
        }

        // Date filter
        $today = date('Y-m-d');
        if ($settings['event_filter'] === 'upcoming') {
            $args['meta_query'] = [
                [
                    'key' => '_eventeule_start_date',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE',
                ],
            ];
        } elseif ($settings['event_filter'] === 'past') {
            $args['meta_query'] = [
                [
                    'key' => '_eventeule_end_date',
                    'value' => $today,
                    'compare' => '<',
                    'type' => 'DATE',
                ],
            ];
        } elseif ($settings['event_filter'] === 'current') {
            $args['meta_query'] = [
                'relation' => 'AND',
                [
                    'key' => '_eventeule_start_date',
                    'value' => $today,
                    'compare' => '<=',
                    'type' => 'DATE',
                ],
                [
                    'key' => '_eventeule_end_date',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE',
                ],
            ];
        }

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            echo '<div class="eventeule-no-events">' . esc_html__('No events found.', 'eventeule') . '</div>';
            return;
        }

        $layout_class = 'eventeule-layout-' . $settings['layout_style'];
        echo '<div class="eventeule-event-list ' . esc_attr($layout_class) . '">';

        while ($query->have_posts()) {
            $query->the_post();
            $event_id = get_the_ID();
            
            $start_date = get_post_meta($event_id, '_eventeule_start_date', true);
            $show_date_box = $settings['show_date'] === 'yes' && $settings['date_style'] === 'box';
            $date_is_overlay = $settings['show_image'] === 'yes' && isset($settings['date_position']) && $settings['date_position'] === 'overlay';
            $is_horizontal = $settings['layout_style'] === 'horizontal';
            
            echo '<article class="eventeule-event-item">';
            
            // Date Box - Separate (before content in horizontal, or before image in vertical)
            if ($show_date_box && !$date_is_overlay && $start_date && !$is_horizontal) {
                $day = date_i18n('j', strtotime($start_date));
                $month = date_i18n($settings['month_format'], strtotime($start_date));
                
                echo '<div class="eventeule-event-date-box-wrapper">';
                echo '<div class="eventeule-event-date-box">';
                echo '<div class="eventeule-event-date-day">' . esc_html($day) . '</div>';
                echo '<div class="eventeule-event-date-month">' . esc_html($month) . '</div>';
                echo '</div>';
                echo '</div>';
            }
            
            // Horizontal layout: Date box comes first
            if ($is_horizontal && $show_date_box && $start_date) {
                $day = date_i18n('j', strtotime($start_date));
                $month = date_i18n($settings['month_format'], strtotime($start_date));
                
                echo '<div class="eventeule-event-date-box-wrapper eventeule-horizontal-date">';
                echo '<div class="eventeule-event-date-box">';
                echo '<div class="eventeule-event-date-day">' . esc_html($day) . '</div>';
                echo '<div class="eventeule-event-date-month">' . esc_html($month) . '</div>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '<div class="eventeule-event-content-wrapper">';
            
            // Image (with optional overlay date)
            if ($settings['show_image'] === 'yes' && has_post_thumbnail()) {
                echo '<div class="eventeule-event-image">';
                echo '<a href="' . esc_url(get_permalink()) . '">';
                echo get_the_post_thumbnail($event_id, $settings['image_size']);
                echo '</a>';
                
                // Date Box - Overlay (over image)
                if ($show_date_box && $date_is_overlay && $start_date) {
                    $day = date_i18n('j', strtotime($start_date));
                    $month = date_i18n($settings['month_format'], strtotime($start_date));
                    
                    echo '<div class="eventeule-event-date-box-wrapper eventeule-date-overlay">';
                    echo '<div class="eventeule-event-date-box">';
                    echo '<div class="eventeule-event-date-day">' . esc_html($day) . '</div>';
                    echo '<div class="eventeule-event-date-month">' . esc_html($month) . '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>';
            }
            
            echo '<div class="eventeule-event-content">';
            
            // Title
            if ($settings['show_title'] === 'yes') {
                echo '<h3 class="eventeule-event-title">';
                echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
                echo '</h3>';
            }
            
            // Meta (Date inline + Location + Time)
            if (($settings['show_date'] === 'yes' && $settings['date_style'] === 'inline') || $settings['show_location'] === 'yes' || $settings['show_time'] === 'yes') {
                echo '<div class="eventeule-event-meta">';
                
                if ($settings['show_date'] === 'yes' && $settings['date_style'] === 'inline' && $start_date) {
                    $formatted_date = date_i18n($settings['date_format'], strtotime($start_date));
                    echo '<span class="eventeule-event-meta-item eventeule-event-date"><span class="eventeule-meta-icon"></span>' . esc_html($formatted_date) . '</span>';
                }
                
                if ($settings['show_time'] === 'yes') {
                    $start_time = get_post_meta($event_id, '_eventeule_start_time', true);
                    if ($start_time) {
                        echo '<span class="eventeule-event-meta-item eventeule-event-time"><span class="eventeule-meta-icon"></span>' . esc_html($start_time) . '</span>';
                    }
                }
                
                if ($settings['show_location'] === 'yes') {
                    $location = get_post_meta($event_id, '_eventeule_location', true);
                    if ($location) {
                        echo '<span class="eventeule-event-meta-item eventeule-event-location"><span class="eventeule-meta-icon"></span>' . esc_html($location) . '</span>';
                    }
                }
                
                echo '</div>';
            }
            
            // Description (Short Description or Excerpt)
            if ($settings['show_description'] === 'yes') {
                $description = '';
                
                if ($settings['description_source'] === 'short') {
                    $description = get_post_meta($event_id, '_eventeule_short_description', true);
                }
                
                // Fallback to excerpt if no short description
                if (empty($description) && $settings['description_source'] === 'excerpt') {
                    $description = wp_trim_words(get_the_excerpt(), $settings['excerpt_length'], '...');
                }
                
                if (!empty($description)) {
                    echo '<div class="eventeule-event-excerpt">' . esc_html($description) . '</div>';
                }
            }
            
            // Button
            if ($settings['show_read_more'] === 'yes') {
                $button_url = get_permalink();
                
                // Use registration URL if selected and available
                if ($settings['button_link_type'] === 'registration') {
                    $registration_url = get_post_meta($event_id, '_eventeule_registration_url', true);
                    if (!empty($registration_url)) {
                        $button_url = $registration_url;
                    }
                }
                
                echo '<a href="' . esc_url($button_url) . '" class="eventeule-event-read-more">';
                echo esc_html($settings['read_more_text']);
                echo '</a>';
            }
            
            echo '</div>'; // .eventeule-event-content
            echo '</div>'; // .eventeule-event-content-wrapper
            echo '</article>';
        }

        echo '</div>';

        wp_reset_postdata();
    }

    protected function content_template()
    {
        ?>
        <#
        var layoutClass = 'eventeule-layout-' + settings.layout_style;
        #>
        <div class="eventeule-event-list {{ layoutClass }}">
            <# for (var i = 0; i < 3; i++) { #>
                <article class="eventeule-event-item">
                    <# 
                    var showDateBox = settings.show_date === 'yes' && settings.date_style === 'box';
                    var dateIsOverlay = settings.show_image === 'yes' && settings.date_position === 'overlay';
                    var isHorizontal = settings.layout_style === 'horizontal';
                    #>
                    
                    <# if (showDateBox && !dateIsOverlay && !isHorizontal) { #>
                        <div class="eventeule-event-date-box-wrapper">
                            <div class="eventeule-event-date-box">
                                <div class="eventeule-event-date-day">15</div>
                                <div class="eventeule-event-date-month"><# if (settings.month_format === 'F') { #>January<# } else { #>Jan<# } #></div>
                            </div>
                        </div>
                    <# } #>
                    
                    <# if (isHorizontal && showDateBox) { #>
                        <div class="eventeule-event-date-box-wrapper eventeule-horizontal-date">
                            <div class="eventeule-event-date-box">
                                <div class="eventeule-event-date-day">15</div>
                                <div class="eventeule-event-date-month"><# if (settings.month_format === 'F') { #>January<# } else { #>Jan<# } #></div>
                            </div>
                        </div>
                    <# } #>
                    
                    <div class="eventeule-event-content-wrapper">
                        <# if (settings.show_image === 'yes') { #>
                            <div class="eventeule-event-image">
                                <img src="https://via.placeholder.com/400x300" alt="Event" />
                                
                                <# if (showDateBox && dateIsOverlay) { #>
                                    <div class="eventeule-event-date-box-wrapper eventeule-date-overlay">
                                        <div class="eventeule-event-date-box">
                                            <div class="eventeule-event-date-day">15</div>
                                            <div class="eventeule-event-date-month"><# if (settings.month_format === 'F') { #>January<# } else { #>Jan<# } #></div>
                                        </div>
                                    </div>
                                <# } #>
                            </div>
                        <# } #>
                        
                        <div class="eventeule-event-content">
                            <# if (settings.show_title === 'yes') { #>
                                <h3 class="eventeule-event-title">Event Title {{ i + 1 }}</h3>
                            <# } #>
                            
                            <# if ((settings.show_date === 'yes' && settings.date_style === 'inline') || settings.show_location === 'yes' || settings.show_time === 'yes') { #>
                                <div class="eventeule-event-meta">
                                    <# if (settings.show_date === 'yes' && settings.date_style === 'inline') { #>
                                        <span class="eventeule-event-meta-item eventeule-event-date"><span class="eventeule-meta-icon"></span>31.12.2024</span>
                                    <# } #>
                                    <# if (settings.show_time === 'yes') { #>
                                        <span class="eventeule-event-meta-item eventeule-event-time"><span class="eventeule-meta-icon"></span>18:00</span>
                                    <# } #>
                                    <# if (settings.show_location === 'yes') { #>
                                        <span class="eventeule-event-meta-item eventeule-event-location"><span class="eventeule-meta-icon"></span>Sample Location</span>
                                    <# } #>
                                </div>
                            <# } #>
                            
                            <# if (settings.show_description === 'yes') { #>
                                <div class="eventeule-event-excerpt">
                                    <# if (settings.description_source === 'short') { #>
                                        This is a short description for the event...
                                    <# } else { #>
                                        This is an auto-generated excerpt...
                                    <# } #>
                                </div>
                            <# } #>
                            
                            <# if (settings.show_read_more === 'yes') { #>
                                <a href="#" class="eventeule-event-read-more">{{ settings.read_more_text }}</a>
                            <# } #>
                        </div>
                    </div>
                </article>
            <# } #>
        </div>
        <?php
    }
}
