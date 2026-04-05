<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class EventLocationWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_location';
    }

    public function get_title(): string
    {
        return __('Event Ort', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-map-pin';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'location', 'ort', 'adresse', 'venue', 'eventeule'];
    }

    protected function register_controls(): void
    {
        // ── Content ───────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => __('Inhalt', 'eventeule'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('show_icon', [
            'label'        => __('Icon anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('prefix', [
            'label'       => __('Präfix', 'eventeule'),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'placeholder' => __('z. B. Ort:', 'eventeule'),
        ]);

        $this->add_control('maps_link', [
            'label'        => __('Google Maps-Link', 'eventeule'),
            'description'  => __('Ort als anklickbaren Google Maps-Link anzeigen.', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'no',
            'separator'    => 'before',
        ]);

        $this->add_control('link_target', [
            'label'     => __('Link öffnen in', 'eventeule'),
            'type'      => Controls_Manager::SELECT,
            'default'   => '_blank',
            'options'   => [
                '_blank' => __('Neuer Tab', 'eventeule'),
                '_self'  => __('Gleiches Fenster', 'eventeule'),
            ],
            'condition' => ['maps_link' => 'yes'],
        ]);

        $this->add_control('hide_if_empty', [
            'label'        => __('Ausblenden wenn leer', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
            'separator'    => 'before',
        ]);

        $this->end_controls_section();

        // ── Style ─────────────────────────────────────────────────────────
        $this->start_controls_section('section_style', [
            'label' => __('Stil', 'eventeule'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            ['name' => 'text_typography', 'selector' => '{{WRAPPER}} .ee-event-location']
        );

        $this->add_control('text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-location' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('icon_color', [
            'label'     => __('Icon-Farbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-location__icon' => 'color: {{VALUE}};'],
            'condition' => ['show_icon' => 'yes'],
        ]);

        $this->add_control('link_color', [
            'label'     => __('Link-Farbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-location a' => 'color: {{VALUE}};'],
            'condition' => ['maps_link' => 'yes'],
        ]);

        $this->add_control('gap', [
            'label'     => __('Abstand Icon – Text', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 24]],
            'default'   => ['unit' => 'px', 'size' => 6],
            'selectors' => ['{{WRAPPER}} .ee-event-location' => 'gap: {{SIZE}}{{UNIT}};'],
            'condition' => ['show_icon' => 'yes'],
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

        $location = (string) get_post_meta($post_id, '_eventeule_location', true);

        if ($location === '') {
            if ($settings['hide_if_empty'] === 'yes') {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    echo '<div class="ee-reg-popup-notice">' . esc_html__('Kein Veranstaltungsort gespeichert.', 'eventeule') . '</div>';
                }
                return;
            }
            $location = __('Kein Ort angegeben', 'eventeule');
        }

        $show_icon  = $settings['show_icon'] === 'yes';
        $maps_link  = $settings['maps_link'] === 'yes';
        $prefix     = $settings['prefix'];
        $target     = esc_attr($settings['link_target'] ?? '_blank');

        echo '<div class="ee-event-location">';
        if ($show_icon): ?>
            <span class="ee-event-location__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            </span>
        <?php endif;

        if ($prefix !== '') {
            echo '<span class="ee-event-location__prefix">' . esc_html($prefix) . '</span>';
        }

        if ($maps_link) {
            $maps_url = 'https://maps.google.com/?q=' . rawurlencode($location);
            echo '<a href="' . esc_url($maps_url) . '" target="' . $target . '" rel="noopener noreferrer">' . esc_html($location) . '</a>';
        } else {
            echo '<span class="ee-event-location__value">' . esc_html($location) . '</span>';
        }

        echo '</div>';
    }
}
