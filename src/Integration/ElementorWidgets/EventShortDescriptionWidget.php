<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class EventShortDescriptionWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_short_description';
    }

    public function get_title(): string
    {
        return __('Event Kurzbeschreibung', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-text';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'description', 'beschreibung', 'kurzbeschreibung', 'text', 'excerpt', 'eventeule'];
    }

    protected function register_controls(): void
    {
        // ── Content ───────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => __('Inhalt', 'eventeule'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('fallback_to_excerpt', [
            'label'        => __('Auf Auszug zurückfallen', 'eventeule'),
            'description'  => __('Wenn keine Kurzbeschreibung vorhanden ist, den WordPress-Auszug anzeigen.', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('excerpt_length', [
            'label'     => __('Auszug Wortanzahl', 'eventeule'),
            'type'      => Controls_Manager::NUMBER,
            'default'   => 30,
            'min'       => 5,
            'max'       => 200,
            'condition' => ['fallback_to_excerpt' => 'yes'],
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
            ['name' => 'text_typography', 'selector' => '{{WRAPPER}} .ee-event-short-desc']
        );

        $this->add_control('text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-short-desc' => 'color: {{VALUE}};'],
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

        $text = (string) get_post_meta($post_id, '_eventeule_short_description', true);

        if ($text === '' && $settings['fallback_to_excerpt'] === 'yes') {
            $length = max(5, (int) ($settings['excerpt_length'] ?? 30));
            $text   = wp_trim_words(get_the_excerpt($post_id), $length, '…');
        }

        if ($text === '') {
            if ($settings['hide_if_empty'] === 'yes') {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    echo '<div class="ee-reg-popup-notice">' . esc_html__('Keine Kurzbeschreibung gespeichert.', 'eventeule') . '</div>';
                }
                return;
            }
        }

        echo '<div class="ee-event-short-desc">' . nl2br(esc_html($text)) . '</div>';
    }
}
