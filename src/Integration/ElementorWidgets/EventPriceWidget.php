<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class EventPriceWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_price';
    }

    public function get_title(): string
    {
        return __('Event Preis', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-price-table';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'price', 'preis', 'kosten', 'ticket', 'eintritt', 'eventeule'];
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
            'placeholder' => __('z. B. Preis:', 'eventeule'),
        ]);

        $this->add_control('suffix', [
            'label'       => __('Suffix', 'eventeule'),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'placeholder' => __('z. B. pro Person', 'eventeule'),
        ]);

        $this->add_control('free_text', [
            'label'       => __('Text wenn kostenlos', 'eventeule'),
            'description' => __('Falls das Preisfeld leer ist, diesen Text anzeigen. Leer lassen um auszublenden.', 'eventeule'),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'placeholder' => __('z. B. Kostenlos', 'eventeule'),
            'separator'   => 'before',
        ]);

        $this->add_control('hide_if_empty', [
            'label'        => __('Ausblenden wenn kein Preis', 'eventeule'),
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
            ['name' => 'text_typography', 'selector' => '{{WRAPPER}} .ee-event-price']
        );

        $this->add_control('text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-price' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('icon_color', [
            'label'     => __('Icon-Farbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-event-price__icon' => 'color: {{VALUE}};'],
            'condition' => ['show_icon' => 'yes'],
        ]);

        $this->add_control('gap', [
            'label'     => __('Abstand Icon – Text', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 24]],
            'default'   => ['unit' => 'px', 'size' => 6],
            'selectors' => ['{{WRAPPER}} .ee-event-price' => 'gap: {{SIZE}}{{UNIT}};'],
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

        $price = (string) get_post_meta($post_id, '_eventeule_price', true);

        if ($price === '') {
            $free_text = trim($settings['free_text']);
            if ($free_text === '' || $settings['hide_if_empty'] === 'yes') {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode() && $settings['hide_if_empty'] === 'yes') {
                    echo '<div class="ee-reg-popup-notice">' . esc_html__('Kein Preis gespeichert.', 'eventeule') . '</div>';
                }
                return;
            }
            $price = $free_text;
        }

        $show_icon = $settings['show_icon'] === 'yes';
        $prefix    = trim($settings['prefix']);
        $suffix    = trim($settings['suffix']);

        echo '<div class="ee-event-price">';
        if ($show_icon): ?>
            <span class="ee-event-price__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
            </span>
        <?php endif;

        if ($prefix !== '') {
            echo '<span class="ee-event-price__prefix">' . esc_html($prefix) . '</span>';
        }
        echo '<span class="ee-event-price__value">' . esc_html($price) . '</span>';
        if ($suffix !== '') {
            echo '<span class="ee-event-price__suffix">' . esc_html($suffix) . '</span>';
        }

        echo '</div>';
    }
}
