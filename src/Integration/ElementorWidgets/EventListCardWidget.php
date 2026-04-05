<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use EventEule\Domain\EventPostType;
use EventEule\Domain\EventCategoryTaxonomy;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * EventEule Event List Card Widget
 * 
 * Advanced event list with card layout, filters, and pagination
 * Based on Unlimited Elements style
 */
class EventListCardWidget extends Widget_Base
{
    public function get_name()
    {
        return 'eventeule_event_list_card';
    }

    public function get_title()
    {
        return __('Event List (Card)', 'eventeule');
    }

    public function get_icon()
    {
        return 'eicon-posts-grid';
    }

    public function get_categories()
    {
        return ['eventeule'];
    }

    public function get_keywords()
    {
        return ['event', 'list', 'card', 'eventeule', 'calendar'];
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
                'label' => __('Show Events', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'upcoming',
                'options' => [
                    'all' => __('All Events', 'eventeule'),
                    'upcoming' => __('Upcoming Events', 'eventeule'),
                    'past' => __('Past Events', 'eventeule'),
                    'current' => __('Current Events (Today)', 'eventeule'),
                ],
            ]
        );

        // Categories
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
                'label' => __('Filter by Category', 'eventeule'),
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

        $this->add_control(
            'opening_hours_mode',
            [
                'label' => __('Öffnungszeiten-Termine', 'eventeule'),
                'type' => Controls_Manager::SELECT,
                'default' => 'all',
                'options' => [
                    'all'      => __('Alle anzeigen', 'eventeule'),
                    'next_per_schedule' => __('Nur nächster Termin je Plan', 'eventeule'),
                    'next_one' => __('Nur allernächster Termin', 'eventeule'),
                ],
                'separator' => 'before',
                'description' => __('Filtert Events, die aus einem Öffnungszeiten-Plan stammen.', 'eventeule'),
            ]
        );

        $this->add_control(
            'soon_days',
            [
                'label' => __('„Bald"-Schwelle (Tage)', 'eventeule'),
                'type' => Controls_Manager::NUMBER,
                'default' => 7,
                'min' => 1,
                'max' => 30,
                'separator' => 'before',
                'description' => __('Events innerhalb dieser Anzahl Tage erhalten das „Bald"-Badge.', 'eventeule'),
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
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-list-card' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_responsive_control(
            'column_gap',
            [
                'label' => __('Column Gap', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 30,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-list-card' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Content Section
        $this->start_controls_section(
            'content_section',
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
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_category',
            [
                'label' => __('Show Category', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_location',
            [
                'label' => __('Show Location', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_date',
            [
                'label' => __('Show Date', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'date_format',
            [
                'label'   => __('Datumsformat', 'eventeule'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'site',
                'options' => [
                    'site'     => __('WordPress-Standard (Einstellungen)', 'eventeule'),
                    'd.m.Y'    => __('TT.MM.JJJJ (31.12.2025)', 'eventeule'),
                    'd. F Y'   => __('TT. Monat JJJJ (31. Dezember 2025)', 'eventeule'),
                    'j. F Y'   => __('T. Monat JJJJ (1. Januar 2025)', 'eventeule'),
                    'D, d.m.Y' => __('Wochentag TT.MM.JJJJ (Mi, 31.12.2025)', 'eventeule'),
                    'l, j. F Y'=> __('Wochentag T. Monat JJJJ (Mittwoch, 1. Januar 2025)', 'eventeule'),
                    'Y-m-d'    => __('JJJJ-MM-TT (2025-12-31)', 'eventeule'),
                    'custom'   => __('Benutzerdefiniert', 'eventeule'),
                ],
                'condition' => ['show_date' => 'yes'],
            ]
        );

        $this->add_control(
            'date_format_custom',
            [
                'label'       => __('Eigenes Format (PHP)', 'eventeule'),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'd.m.Y',
                'placeholder' => 'd.m.Y',
                'description' => __('PHP-Datumsformat, z.B. d.m.Y oder j. F Y', 'eventeule'),
                'condition'   => ['show_date' => 'yes', 'date_format' => 'custom'],
            ]
        );

        $this->add_control(
            'show_time',
            [
                'label' => __('Show Time', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_price',
            [
                'label' => __('Show Price', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_button',
            [
                'label' => __('Show Button', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'eventeule'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Tickets', 'eventeule'),
                'condition' => [
                    'show_button' => 'yes',
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
                    'event' => __('Event Page', 'eventeule'),
                    'registration' => __('Registration URL', 'eventeule'),
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_style',
            [
                'label'   => __('Button-Stil', 'eventeule'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'button',
                'options' => [
                    'button' => __('Button (gestylt)', 'eventeule'),
                    'link'   => __('Einfacher Link', 'eventeule'),
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Auszug anzeigen', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'excerpt_words',
            [
                'label' => __('Auszug Wörteranzahl', 'eventeule'),
                'type' => Controls_Manager::NUMBER,
                'default' => 20,
                'min' => 5,
                'max' => 100,
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_status_badge',
            [
                'label' => __('Status-Badge anzeigen', 'eventeule'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
                'description' => __('Zeigt „Heute", „Jetzt", „Bald" oder „Abgesagt"-Badges.', 'eventeule'),
            ]
        );

        $this->end_controls_section();

        // Responsive Visibility Section
        $this->start_controls_section(
            'responsive_section',
            [
                'label' => __('Responsive Visibility', 'eventeule'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'responsive_heading',
            [
                'label' => __('Control which elements are visible on different devices', 'eventeule'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        // Image Responsive
        $this->add_control(
            'hide_image_on',
            [
                'label' => __('Hide Image On', 'eventeule'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'desktop' => __('Desktop', 'eventeule'),
                    'tablet' => __('Tablet', 'eventeule'),
                    'mobile' => __('Mobile', 'eventeule'),
                ],
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

        // Category Responsive
        $this->add_control(
            'hide_category_on',
            [
                'label' => __('Hide Category On', 'eventeule'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'desktop' => __('Desktop', 'eventeule'),
                    'tablet' => __('Tablet', 'eventeule'),
                    'mobile' => __('Mobile', 'eventeule'),
                ],
                'condition' => [
                    'show_category' => 'yes',
                ],
            ]
        );

        // Title Responsive
        $this->add_control(
            'hide_title_on',
            [
                'label' => __('Hide Title On', 'eventeule'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'desktop' => __('Desktop', 'eventeule'),
                    'tablet' => __('Tablet', 'eventeule'),
                    'mobile' => __('Mobile', 'eventeule'),
                ],
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        // Location Responsive
        $this->add_control(
            'hide_location_on',
            [
                'label' => __('Hide Location On', 'eventeule'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'desktop' => __('Desktop', 'eventeule'),
                    'tablet' => __('Tablet', 'eventeule'),
                    'mobile' => __('Mobile', 'eventeule'),
                ],
                'condition' => [
                    'show_location' => 'yes',
                ],
            ]
        );

        // Date Responsive
        $this->add_control(
            'hide_date_on',
            [
                'label' => __('Hide Date On', 'eventeule'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'desktop' => __('Desktop', 'eventeule'),
                    'tablet' => __('Tablet', 'eventeule'),
                    'mobile' => __('Mobile', 'eventeule'),
                ],
                'condition' => [
                    'show_date' => 'yes',
                ],
            ]
        );

        // Time Responsive
        $this->add_control(
            'hide_time_on',
            [
                'label' => __('Hide Time On', 'eventeule'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'desktop' => __('Desktop', 'eventeule'),
                    'tablet' => __('Tablet', 'eventeule'),
                    'mobile' => __('Mobile', 'eventeule'),
                ],
                'condition' => [
                    'show_time' => 'yes',
                ],
            ]
        );

        // Price Responsive
        $this->add_control(
            'hide_price_on',
            [
                'label' => __('Hide Price On', 'eventeule'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'desktop' => __('Desktop', 'eventeule'),
                    'tablet' => __('Tablet', 'eventeule'),
                    'mobile' => __('Mobile', 'eventeule'),
                ],
                'condition' => [
                    'show_price' => 'yes',
                ],
            ]
        );

        // Button Responsive
        $this->add_control(
            'hide_button_on',
            [
                'label' => __('Hide Button On', 'eventeule'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'desktop' => __('Desktop', 'eventeule'),
                    'tablet' => __('Tablet', 'eventeule'),
                    'mobile' => __('Mobile', 'eventeule'),
                ],
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Card
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
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .eventeule-event-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'selector' => '{{WRAPPER}} .eventeule-event-card',
            ]
        );

        $this->add_responsive_control(
            'card_border_radius',
            [
                'label' => __('Border Radius', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .eventeule-event-card',
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('Padding', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_card_hover',
            [
                'label' => __('Hover', 'eventeule'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'card_hover_background',
            [
                'label' => __('Hintergrundfarbe', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'card_hover_border_color',
            [
                'label' => __('Rahmenfarbe', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Content area
        $this->start_controls_section(
            'content_style_section',
            [
                'label' => __('Inhaltsbereich', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __('Innenabstand', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_gap',
            [
                'label' => __('Zeilenabstand', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 48],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-content' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Image
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
            'image_width',
            [
                'label' => __('Width', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 20,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 200,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-image' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;',
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
                        'max' => 500,
                    ],
                ],
                'default' => [
                    'size' => 200,
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-image' => 'height: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .eventeule-event-card-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Category
        $this->start_controls_section(
            'category_style_section',
            [
                'label' => __('Category', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_category' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'category_typography',
                'selector' => '{{WRAPPER}} .eventeule-event-category',
            ]
        );

        $this->add_control(
            'category_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-category' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'category_background',
            [
                'label' => __('Background', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-category' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'category_padding',
            [
                'label' => __('Padding', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-category' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'category_margin',
            [
                'label' => __('Margin', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-category' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Title
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

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .eventeule-event-card-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .eventeule-event-card-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Hover Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-title a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Margin', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Meta
        $this->start_controls_section(
            'meta_style_section',
            [
                'label' => __('Meta', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'meta_typography',
                'selector' => '{{WRAPPER}} .eventeule-event-card-meta',
            ]
        );

        $this->add_control(
            'meta_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-meta' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'meta_spacing',
            [
                'label' => __('Spacing', 'eventeule'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-meta' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'meta_margin',
            [
                'label' => __('Margin', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_meta_icon_colors',
            [
                'label' => __('Icon-Farben', 'eventeule'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'meta_location_color',
            [
                'label' => __('Ort', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-meta-location' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'meta_date_color',
            [
                'label' => __('Datum', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-meta-date' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'meta_time_color',
            [
                'label' => __('Uhrzeit', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-meta-time' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'meta_price_color',
            [
                'label' => __('Preis', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-meta-price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Excerpt
        $this->start_controls_section(
            'excerpt_style_section',
            [
                'label' => __('Auszug', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt_typography',
                'selector' => '{{WRAPPER}} .eventeule-event-excerpt',
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label' => __('Textfarbe', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'excerpt_margin',
            [
                'label' => __('Abstand', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-excerpt' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Button
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Button', 'eventeule'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_button' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .eventeule-event-card-button',
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
            'button_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label' => __('Background', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-button' => 'background-color: {{VALUE}};',
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
            'button_hover_color',
            [
                'label' => __('Color', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background',
            [
                'label' => __('Background', 'eventeule'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .eventeule-event-card-button',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'eventeule'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_align',
            [
                'label' => __('Ausrichtung', 'eventeule'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __('Links', 'eventeule'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Mitte', 'eventeule'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => __('Rechts', 'eventeule'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'stretch' => [
                        'title' => __('Volle Breite', 'eventeule'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-footer' => 'display: flex; justify-content: {{VALUE}};',
                    '{{WRAPPER}} .eventeule-event-card-button' => '{{VALUE == "stretch" ? "width: 100%; text-align: center;" : ""}}',
                    '{{WRAPPER}} .eventeule-event-card-link'   => '{{VALUE == "stretch" ? "width: 100%; text-align: center;" : ""}}',
                ],
            ]
        );

        // ── Link-Stil (nur wenn Button-Stil = Einfacher Link) ──────────────
        $this->add_control(
            'link_style_heading',
            [
                'label'     => __('Link-Stil', 'eventeule'),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => ['button_style' => 'link'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'link_typography',
                'selector'  => '{{WRAPPER}} .eventeule-event-card-link',
                'condition' => ['button_style' => 'link'],
            ]
        );

        $this->start_controls_tabs(
            'link_tabs',
            ['condition' => ['button_style' => 'link']]
        );

        $this->start_controls_tab(
            'link_normal_tab',
            ['label' => __('Normal', 'eventeule')]
        );

        $this->add_control(
            'link_color',
            [
                'label'     => __('Farbe', 'eventeule'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'link_hover_tab',
            ['label' => __('Hover', 'eventeule')]
        );

        $this->add_control(
            'link_hover_color',
            [
                'label'     => __('Farbe', 'eventeule'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eventeule-event-card-link:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Get responsive hide classes for an element
     */
    private function get_responsive_classes($hide_setting)
    {
        $settings = $this->get_settings_for_display();
        $classes = [];

        if (isset($settings[$hide_setting]) && is_array($settings[$hide_setting])) {
            foreach ($settings[$hide_setting] as $device) {
                $classes[] = 'eventeule-hide-' . $device;
            }
        }

        return !empty($classes) ? ' ' . implode(' ', $classes) : '';
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // Build query
        $args = [
            'post_type' => EventPostType::POST_TYPE,
            'posts_per_page' => $settings['posts_per_page'],
            'orderby' => $settings['order_by'] === 'start_date' ? 'meta_value' : $settings['order_by'],
            'order' => $settings['order'],
        ];

        if ($settings['order_by'] === 'start_date') {
            $args['meta_key'] = '_eventeule_start_date';
            $args['meta_type'] = 'DATE';
        }

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
        $today = current_time('Y-m-d');
        
        switch ($settings['event_filter']) {
            case 'upcoming':
                $args['meta_query'] = [
                    [
                        'key' => '_eventeule_start_date',
                        'value' => $today,
                        'compare' => '>=',
                        'type' => 'DATE',
                    ],
                ];
                break;
            
            case 'past':
                $args['meta_query'] = [
                    [
                        'key' => '_eventeule_start_date',
                        'value' => $today,
                        'compare' => '<',
                        'type' => 'DATE',
                    ],
                ];
                break;
            
            case 'current':
                $args['meta_query'] = [
                    [
                        'key' => '_eventeule_start_date',
                        'value' => $today,
                        'compare' => '=',
                        'type' => 'DATE',
                    ],
                ];
                break;
        }

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            echo '<div class="eventeule-no-events">' . __('No events found.', 'eventeule') . '</div>';
            return;
        }

        // ── Öffnungszeiten-Filterlogik ────────────────────────────────────
        $oh_mode = $settings['opening_hours_mode'] ?? 'all';
        $post_ids_to_render = [];

        if ($oh_mode !== 'all') {
            // Collect all matching post IDs with their opening-id and date
            $oh_candidates = []; // ['post_id' => …, 'opening_id' => …, 'start_date' => …]
            $regular_ids   = [];

            while ($query->have_posts()) {
                $query->the_post();
                $pid    = (int) get_the_ID();
                $oh_id  = (int) get_post_meta($pid, '_eventeule_opening_id', true);
                $s_date = (string) get_post_meta($pid, '_eventeule_start_date', true);

                if ($oh_id > 0) {
                    $oh_candidates[] = ['post_id' => $pid, 'opening_id' => $oh_id, 'start_date' => $s_date];
                } else {
                    $regular_ids[] = $pid;
                }
            }
            wp_reset_postdata();

            // Sort candidates ascending by date so earliest is first
            usort($oh_candidates, fn($a, $b) => strcmp($a['start_date'], $b['start_date']));

            if ($oh_mode === 'next_per_schedule') {
                // Keep only the first (earliest) candidate per opening_id
                $seen = [];
                foreach ($oh_candidates as $c) {
                    if (!isset($seen[$c['opening_id']])) {
                        $seen[$c['opening_id']] = true;
                        $post_ids_to_render[]    = $c['post_id'];
                    }
                }
            } elseif ($oh_mode === 'next_one') {
                // Keep only the single earliest candidate across all plans
                if (!empty($oh_candidates)) {
                    $post_ids_to_render[] = $oh_candidates[0]['post_id'];
                }
            }

            // Merge regular events back (preserving original order)
            $post_ids_to_render = array_merge($regular_ids, $post_ids_to_render);

            if (empty($post_ids_to_render)) {
                echo '<div class="eventeule-no-events">' . __('No events found.', 'eventeule') . '</div>';
                return;
            }

            // Re-run query with the filtered IDs, preserving user's chosen sort
            $args2 = $args;
            $args2['posts_per_page'] = -1;
            $args2['post__in']       = $post_ids_to_render;
            unset($args2['meta_query']); // already filtered via post__in
            $query = new \WP_Query($args2);

            if (!$query->have_posts()) {
                echo '<div class="eventeule-no-events">' . __('No events found.', 'eventeule') . '</div>';
                return;
            }
        }
        // ─────────────────────────────────────────────────────────────────

        $now_ts      = current_time('timestamp');
        $today_ymd   = current_time('Y-m-d');
        $soon_days   = max(1, (int) ($settings['soon_days'] ?? 7));
        $soon_ts_end = strtotime("+{$soon_days} days", strtotime($today_ymd));

        echo '<div class="eventeule-event-list-card">';

        while ($query->have_posts()) {
            $query->the_post();
            $event_id = get_the_ID();

            // Get event meta
            $start_date = get_post_meta($event_id, '_eventeule_start_date', true);
            $start_time = get_post_meta($event_id, '_eventeule_start_time', true);
            $end_time   = get_post_meta($event_id, '_eventeule_end_time', true);
            $location = get_post_meta($event_id, '_eventeule_location', true);
            $registration_url = get_post_meta($event_id, '_eventeule_registration_url', true);
            $is_cancelled = get_post_meta($event_id, '_eventeule_cancelled', true) === '1';

            // ── Status-Badge berechnen ────────────────────────────────────
            $status_badge       = '';
            $status_badge_class = '';
            if (($settings['show_status_badge'] ?? 'yes') === 'yes' && $start_date !== '') {
                $event_start_ts = strtotime($start_date . ($start_time ? ' ' . $start_time : ' 00:00'));
                $event_end_ts   = $end_time
                    ? strtotime($start_date . ' ' . $end_time)
                    : ($event_start_ts + 3600); // assume 1 h if no end

                if ($is_cancelled) {
                    $status_badge       = __('Abgesagt', 'eventeule');
                    $status_badge_class = 'eventeule-status-badge--cancelled';
                } elseif ($now_ts >= $event_start_ts && $now_ts <= $event_end_ts) {
                    $status_badge       = __('Jetzt', 'eventeule');
                    $status_badge_class = 'eventeule-status-badge--now';
                } elseif ($start_date === $today_ymd) {
                    $status_badge       = __('Heute', 'eventeule');
                    $status_badge_class = 'eventeule-status-badge--today';
                } elseif ($event_start_ts > $now_ts && $event_start_ts <= $soon_ts_end) {
                    $status_badge       = __('Bald', 'eventeule');
                    $status_badge_class = 'eventeule-status-badge--soon';
                }
            }
            // ─────────────────────────────────────────────────────────────

            // Get categories
            $categories = get_the_terms($event_id, EventCategoryTaxonomy::TAXONOMY);
            $category_name = '';
            if ($categories && !is_wp_error($categories)) {
                $category_name = $categories[0]->name;
            }

            $card_class = 'eventeule-event-card';
            if ($is_cancelled) {
                $card_class .= ' eventeule-event-card--cancelled';
            }

            echo '<div class="' . esc_attr($card_class) . '">';

            // Image
            if ($settings['show_image'] === 'yes' && has_post_thumbnail()) {
                $image_classes = 'eventeule-event-card-image' . $this->get_responsive_classes('hide_image_on');
                echo '<div class="' . esc_attr($image_classes) . '">';
                echo '<a href="' . esc_url(get_permalink()) . '">';
                echo get_the_post_thumbnail($event_id, 'medium');
                echo '</a>';
                echo '</div>';
            }

            // Content
            echo '<div class="eventeule-event-card-content">';

            // Status badge
            if ($status_badge !== '') {
                echo '<span class="eventeule-status-badge ' . esc_attr($status_badge_class) . '">' . esc_html($status_badge) . '</span>';
            }

            // Category
            if ($settings['show_category'] === 'yes' && $category_name) {
                $category_classes = 'eventeule-event-category' . $this->get_responsive_classes('hide_category_on');
                echo '<div class="' . esc_attr($category_classes) . '">' . esc_html($category_name) . '</div>';
            }

            // Title
            if ($settings['show_title'] === 'yes') {
                $title_classes = 'eventeule-event-card-title' . $this->get_responsive_classes('hide_title_on');
                echo '<h3 class="' . esc_attr($title_classes) . '">';
                echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
                echo '</h3>';
            }

            // Meta information
            $meta_items = [];

            if ($settings['show_location'] === 'yes' && $location) {
                $location_classes = 'eventeule-meta-location' . $this->get_responsive_classes('hide_location_on');
                $meta_items[] = '<span class="' . esc_attr($location_classes) . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg> ' . esc_html($location) . '</span>';
            }

            if ($settings['show_date'] === 'yes' && $start_date) {
                $df = $settings['date_format'] ?? 'site';
                if ($df === 'site') {
                    $df = get_option('date_format');
                } elseif ($df === 'custom') {
                    $df = !empty($settings['date_format_custom']) ? $settings['date_format_custom'] : 'd.m.Y';
                }
                $formatted_date = date_i18n($df, strtotime($start_date));
                $date_classes = 'eventeule-meta-date' . $this->get_responsive_classes('hide_date_on');
                $meta_items[] = '<span class="' . esc_attr($date_classes) . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10z"/></svg> ' . esc_html($formatted_date) . '</span>';
            }

            if ($settings['show_time'] === 'yes' && $start_time) {
                $time_classes = 'eventeule-meta-time' . $this->get_responsive_classes('hide_time_on');
                $time_label = esc_html($start_time);
                if ($end_time) {
                    $time_label .= ' – ' . esc_html($end_time);
                }
                $meta_items[] = '<span class="' . esc_attr($time_classes) . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg> ' . $time_label . '</span>';
            }

            if ($settings['show_price'] === 'yes') {
                $price = get_post_meta($event_id, '_eventeule_price', true);
                if ($price) {
                    $price_classes = 'eventeule-meta-price' . $this->get_responsive_classes('hide_price_on');
                    $meta_items[] = '<span class="' . esc_attr($price_classes) . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg> ' . esc_html($price) . '</span>';
                }
            }

            if (!empty($meta_items)) {
                echo '<div class="eventeule-event-card-meta">';
                echo implode('', $meta_items);
                echo '</div>';
            }

            // Excerpt
            if ($settings['show_excerpt'] === 'yes') {
                $words = (int) ($settings['excerpt_words'] ?? 20);
                $excerpt = wp_trim_words(
                    wp_strip_all_tags(get_the_excerpt() ?: get_the_content()),
                    $words,
                    ' …'
                );
                if ($excerpt) {
                    echo '<p class="eventeule-event-excerpt">' . esc_html($excerpt) . '</p>';
                }
            }

            // Button (inside content so it sits as last column-item)
            if ($settings['show_button'] === 'yes') {
                $button_url = get_permalink();

                if ($settings['button_link_type'] === 'registration' && !empty($registration_url)) {
                    $button_url = $registration_url;
                }

                $btn_style = $settings['button_style'] ?? 'button';
                $btn_class = $btn_style === 'link' ? 'eventeule-event-card-link' : 'eventeule-event-card-button';
                $footer_classes = 'eventeule-event-card-footer' . $this->get_responsive_classes('hide_button_on');
                echo '<div class="' . esc_attr($footer_classes) . '">';
                echo '<a href="' . esc_url($button_url) . '" class="' . esc_attr($btn_class) . '">';
                echo esc_html($settings['button_text']);
                echo '</a>';
                echo '</div>';
            }

            echo '</div>'; // .eventeule-event-card-content

            echo '</div>'; // .eventeule-event-card
        }

        echo '</div>'; // .eventeule-event-list-card

        wp_reset_postdata();
    }

    protected function content_template()
    {
        ?>
        <div class="eventeule-event-list-card">
            <# for (var i = 0; i < 3; i++) { #>
                <div class="eventeule-event-card">
                    <# if (settings.show_image === 'yes') { #>
                        <div class="eventeule-event-card-image">
                            <img src="https://via.placeholder.com/400x300" alt="Event" />
                        </div>
                    <# } #>
                    
                    <div class="eventeule-event-card-content">
                        <# if (settings.show_category === 'yes') { #>
                            <div class="eventeule-event-category">Concert</div>
                        <# } #>
                        
                        <# if (settings.show_title === 'yes') { #>
                            <h3 class="eventeule-event-card-title">Event Title {{ i + 1 }}</h3>
                        <# } #>
                        
                        <div class="eventeule-event-card-meta">
                            <# if (settings.show_location === 'yes') { #>
                                <span class="eventeule-meta-location">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                    New York
                                </span>
                            <# } #>
                            <# if (settings.show_date === 'yes') { #>
                                <span class="eventeule-meta-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10z"/></svg>
                                    25/01/2024
                                </span>
                            <# } #>
                            <# if (settings.show_time === 'yes') { #>
                                <span class="eventeule-meta-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                    19:00
                                </span>
                            <# } #>
                            <# if (settings.show_price === 'yes') { #>
                                <span class="eventeule-meta-price">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                                    $25.00
                                </span>
                            <# } #>
                        </div>

                        <# if (settings.show_excerpt === 'yes') { #>
                            <p class="eventeule-event-excerpt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua …</p>
                        <# } #>

                        <# if (settings.show_button === 'yes') { #>
                            <div class="eventeule-event-card-footer">
                                <# var btnClass = (settings.button_style === 'link') ? 'eventeule-event-card-link' : 'eventeule-event-card-button'; #>
                                <a href="#" class="{{ btnClass }}">{{ settings.button_text }}</a>
                            </div>
                        <# } #>
                    </div>
                </div>
            <# } #>
        </div>
        <?php
    }
}
