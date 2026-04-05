<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class EventRegistrationUrlWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_registration_url';
    }

    public function get_title(): string
    {
        return __('Event Anmeldungs-Link', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-button';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'registration', 'url', 'link', 'button', 'anmeldung', 'anmeldelink', 'eventeule'];
    }

    protected function register_controls(): void
    {
        // ── Content ───────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => __('Inhalt', 'eventeule'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('button_text', [
            'label'   => __('Button-Text', 'eventeule'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('Jetzt anmelden', 'eventeule'),
        ]);

        $this->add_control('display_as', [
            'label'   => __('Anzeigen als', 'eventeule'),
            'type'    => Controls_Manager::SELECT,
            'default' => 'button',
            'options' => [
                'button' => __('Button', 'eventeule'),
                'link'   => __('Textlink', 'eventeule'),
            ],
        ]);

        $this->add_control('link_target', [
            'label'   => __('Link öffnen in', 'eventeule'),
            'type'    => Controls_Manager::SELECT,
            'default' => '_blank',
            'options' => [
                '_blank' => __('Neuer Tab', 'eventeule'),
                '_self'  => __('Gleiches Fenster', 'eventeule'),
            ],
        ]);

        $this->add_control('align', [
            'label'     => __('Ausrichtung', 'eventeule'),
            'type'      => Controls_Manager::CHOOSE,
            'options'   => [
                'left'   => ['title' => __('Links', 'eventeule'),   'icon' => 'eicon-text-align-left'],
                'center' => ['title' => __('Zentriert', 'eventeule'), 'icon' => 'eicon-text-align-center'],
                'right'  => ['title' => __('Rechts', 'eventeule'),  'icon' => 'eicon-text-align-right'],
            ],
            'default'   => 'left',
            'selectors' => ['{{WRAPPER}} .ee-event-regurl-wrap' => 'text-align: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->add_control('hide_if_empty', [
            'label'        => __('Ausblenden wenn kein Link', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
            'separator'    => 'before',
        ]);

        $this->end_controls_section();

        // ── Style: Button ──────────────────────────────────────────────────
        $this->start_controls_section('section_button_style', [
            'label'     => __('Button-Stil', 'eventeule'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['display_as' => 'button'],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            ['name' => 'btn_typography', 'selector' => '{{WRAPPER}} .ee-event-regurl-btn']
        );

        $this->start_controls_tabs('btn_tabs');

        $this->start_controls_tab('btn_tab_normal', ['label' => __('Normal', 'eventeule')]);
        $this->add_control('btn_bg', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#6366f1',
            'selectors' => ['{{WRAPPER}} .ee-event-regurl-btn' => 'background-color: {{VALUE}};'],
        ]);
        $this->add_control('btn_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} .ee-event-regurl-btn' => 'color: {{VALUE}};'],
        ]);
        $this->end_controls_tab();

        $this->start_controls_tab('btn_tab_hover', ['label' => __('Hover', 'eventeule')]);
        $this->add_control('btn_bg_hover', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#4f46e5',
            'selectors' => ['{{WRAPPER}} .ee-event-regurl-btn:hover' => 'background-color: {{VALUE}};'],
        ]);
        $this->add_control('btn_color_hover', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} .ee-event-regurl-btn:hover' => 'color: {{VALUE}};'],
        ]);
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            ['name' => 'btn_border', 'selector' => '{{WRAPPER}} .ee-event-regurl-btn', 'separator' => 'before']
        );

        $this->add_control('btn_border_radius', [
            'label'     => __('Eckenradius', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 60]],
            'default'   => ['unit' => 'px', 'size' => 8],
            'selectors' => ['{{WRAPPER}} .ee-event-regurl-btn' => 'border-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_control('btn_padding', [
            'label'      => __('Innenabstand', 'eventeule'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'default'    => ['top' => '10', 'right' => '24', 'bottom' => '10', 'left' => '24', 'unit' => 'px', 'isLinked' => false],
            'selectors'  => ['{{WRAPPER}} .ee-event-regurl-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            ['name' => 'btn_box_shadow', 'selector' => '{{WRAPPER}} .ee-event-regurl-btn']
        );

        $this->end_controls_section();

        // ── Style: Link ───────────────────────────────────────────────────
        $this->start_controls_section('section_link_style', [
            'label'     => __('Link-Stil', 'eventeule'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['display_as' => 'link'],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            ['name' => 'link_typography', 'selector' => '{{WRAPPER}} .ee-event-regurl-link']
        );

        $this->add_control('link_color', [
            'label'     => __('Linkfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-regurl-link' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('link_color_hover', [
            'label'     => __('Hover-Farbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-regurl-link:hover' => 'color: {{VALUE}};'],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        $post_id  = get_the_ID();

        if (!$post_id) {
            return;
        }

        $url = (string) get_post_meta($post_id, '_eventeule_registration_url', true);

        if ($url === '') {
            if ($settings['hide_if_empty'] === 'yes') {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    echo '<div class="ee-reg-popup-notice">' . esc_html__('Kein Anmeldungs-Link gespeichert.', 'eventeule') . '</div>';
                }
                return;
            }
            $url = '#';
        }

        $text       = $settings['button_text'] ?: __('Jetzt anmelden', 'eventeule');
        $display_as = $settings['display_as'];
        $target     = esc_attr($settings['link_target'] ?? '_blank');
        $rel        = $target === '_blank' ? ' rel="noopener noreferrer"' : '';

        echo '<div class="ee-event-regurl-wrap">';

        if ($display_as === 'button') {
            echo '<a href="' . esc_url($url) . '" target="' . $target . '"' . $rel . ' class="ee-event-regurl-btn">'
                . esc_html($text) . '</a>';
        } else {
            echo '<a href="' . esc_url($url) . '" target="' . $target . '"' . $rel . ' class="ee-event-regurl-link">'
                . esc_html($text) . '</a>';
        }

        echo '</div>';
    }
}
