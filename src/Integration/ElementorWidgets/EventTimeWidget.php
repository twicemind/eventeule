<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class EventTimeWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_time';
    }

    public function get_title(): string
    {
        return __('Event Uhrzeit', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-clock-o';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'time', 'uhrzeit', 'start', 'end', 'eventeule'];
    }

    protected function register_controls(): void
    {
        // ── Content ───────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => __('Inhalt', 'eventeule'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('time_format', [
            'label'   => __('Zeitformat', 'eventeule'),
            'type'    => Controls_Manager::SELECT,
            'default' => 'H:i',
            'options' => [
                'H:i'     => __('24-Stunden (z. B. 14:30)', 'eventeule'),
                'h:i A'   => __('12-Stunden AM/PM (z. B. 2:30 PM)', 'eventeule'),
                'H:i \Uhr' => __('Mit „Uhr" (z. B. 14:30 Uhr)', 'eventeule'),
            ],
        ]);

        $this->add_control('show_end_time', [
            'label'        => __('Endzeit anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'no',
        ]);

        $this->add_control('time_separator', [
            'label'     => __('Trenner', 'eventeule'),
            'type'      => Controls_Manager::TEXT,
            'default'   => ' – ',
            'condition' => ['show_end_time' => 'yes'],
        ]);

        $this->add_control('show_icon', [
            'label'        => __('Icon anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
            'separator'    => 'before',
        ]);

        $this->add_control('prefix', [
            'label'       => __('Präfix', 'eventeule'),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'placeholder' => __('z. B. Beginn:', 'eventeule'),
        ]);

        $this->add_control('hide_if_empty', [
            'label'        => __('Ausblenden wenn leer', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->end_controls_section();

        // ── Style ─────────────────────────────────────────────────────────
        $this->start_controls_section('section_style', [
            'label' => __('Stil', 'eventeule'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            ['name' => 'text_typography', 'selector' => '{{WRAPPER}} .ee-event-time']
        );

        $this->add_control('text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-time' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('icon_color', [
            'label'     => __('Icon-Farbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-time__icon' => 'color: {{VALUE}};'],
            'condition' => ['show_icon' => 'yes'],
        ]);

        $this->add_control('gap', [
            'label'     => __('Abstand Icon – Text', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 24]],
            'default'   => ['unit' => 'px', 'size' => 6],
            'selectors' => ['{{WRAPPER}} .ee-event-time' => 'gap: {{SIZE}}{{UNIT}};'],
            'condition' => ['show_icon' => 'yes'],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $settings   = $this->get_settings_for_display();
        $post_id    = get_the_ID();

        if (!$post_id) {
            return;
        }

        $start_time = (string) get_post_meta($post_id, '_eventeule_start_time', true);

        if ($start_time === '' && $settings['hide_if_empty'] === 'yes') {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="ee-reg-popup-notice">' . esc_html__('Keine Uhrzeit gespeichert.', 'eventeule') . '</div>';
            }
            return;
        }

        $format       = $settings['time_format'];
        $show_end     = $settings['show_end_time'] === 'yes';
        $separator    = $settings['time_separator'] ?: ' – ';
        $show_icon    = $settings['show_icon'] === 'yes';
        $prefix       = $settings['prefix'];

        $formatted_start = $start_time !== '' ? date_i18n($format, strtotime('2000-01-01 ' . $start_time)) : '';

        $time_str = $formatted_start;

        if ($show_end) {
            $end_time = (string) get_post_meta($post_id, '_eventeule_end_time', true);
            if ($end_time !== '') {
                $formatted_end = date_i18n($format, strtotime('2000-01-01 ' . $end_time));
                $time_str .= esc_html($separator) . $formatted_end;
            }
        }

        echo '<div class="ee-event-time">';
        if ($show_icon): ?>
            <span class="ee-event-time__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
            </span>
        <?php endif;
        if ($prefix !== '') {
            echo '<span class="ee-event-time__prefix">' . esc_html($prefix) . '</span>';
        }
        echo '<span class="ee-event-time__value">' . esc_html($time_str) . '</span>';
        echo '</div>';
    }
}
