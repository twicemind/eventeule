<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class EventNoteWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_note';
    }

    public function get_title(): string
    {
        return __('Event Hinweis', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-editor-list-ul';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'note', 'hinweis', 'notiz', 'additional', 'info', 'eventeule'];
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
            'label'       => __('Präfix / Titel', 'eventeule'),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'placeholder' => __('z. B. Hinweis:', 'eventeule'),
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
            ['name' => 'text_typography', 'selector' => '{{WRAPPER}} .ee-event-note']
        );

        $this->add_control('text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-note' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('icon_color', [
            'label'     => __('Icon-Farbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-note__icon' => 'color: {{VALUE}};'],
            'condition' => ['show_icon' => 'yes'],
        ]);

        $this->add_control('bg_color', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-note' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('border_radius', [
            'label'     => __('Eckenradius', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 20]],
            'default'   => ['unit' => 'px', 'size' => 0],
            'selectors' => ['{{WRAPPER}} .ee-event-note' => 'border-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_control('padding', [
            'label'      => __('Innenabstand', 'eventeule'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => ['{{WRAPPER}} .ee-event-note' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
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

        $note = (string) get_post_meta($post_id, '_eventeule_note', true);

        if ($note === '') {
            if ($settings['hide_if_empty'] === 'yes') {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    echo '<div class="ee-reg-popup-notice">' . esc_html__('Kein Hinweis gespeichert.', 'eventeule') . '</div>';
                }
                return;
            }
        }

        $show_icon = $settings['show_icon'] === 'yes';
        $prefix    = trim($settings['prefix']);

        echo '<div class="ee-event-note">';

        if ($show_icon || $prefix !== ''): ?>
            <div class="ee-event-note__header">
                <?php if ($show_icon): ?>
                    <span class="ee-event-note__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M11 17h2v-6h-2v6zm1-15C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zM11 9h2V7h-2v2z"/></svg>
                    </span>
                <?php endif; ?>
                <?php if ($prefix !== ''): ?>
                    <span class="ee-event-note__prefix"><?php echo esc_html($prefix); ?></span>
                <?php endif; ?>
            </div>
        <?php endif;

        echo '<div class="ee-event-note__text">' . nl2br(esc_html($note)) . '</div>';
        echo '</div>';
    }
}
