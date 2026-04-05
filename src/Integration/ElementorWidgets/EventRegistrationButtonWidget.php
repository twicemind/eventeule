<?php

namespace EventEule\Integration\ElementorWidgets;

use EventEule\Registration\RegistrationRepository;

/**
 * Elementor widget that renders a button opening a registration popup.
 */
class EventRegistrationButtonWidget extends \Elementor\Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_registration_button';
    }

    public function get_title(): string
    {
        return __('Anmelde-Button', 'eventeule');
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
        return ['event', 'registration', 'anmeldung', 'button', 'popup', 'form', 'anmelden'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Controls
    // ─────────────────────────────────────────────────────────────────────────

    protected function register_controls(): void
    {
        // ── Content ──────────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => __('Inhalt', 'eventeule'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        // Populate event select options
        $event_options = ['0' => __('— Aktueller Beitrag (automatisch) —', 'eventeule')];
        $events = get_posts([
            'post_type'      => 'eventeule_event',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value',
            'meta_key'       => '_eventeule_start_date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        ]);
        foreach ($events as $event) {
            $event_options[(string) $event->ID] = $event->post_title;
        }

        $this->add_control('event_id', [
            'label'       => __('Event', 'eventeule'),
            'type'        => \Elementor\Controls_Manager::SELECT,
            'default'     => '0',
            'options'     => $event_options,
            'description' => __('Wähle das Event für diesen Anmelde-Button. Auf einzelnen Event-Seiten kann „automatisch" gewählt werden.', 'eventeule'),
        ]);

        $this->add_control('button_text', [
            'label'       => __('Button-Text', 'eventeule'),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => __('Jetzt anmelden', 'eventeule'),
            'placeholder' => __('Jetzt anmelden', 'eventeule'),
            'separator'   => 'before',
        ]);

        $this->add_control('button_align', [
            'label'   => __('Ausrichtung', 'eventeule'),
            'type'    => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'left'   => ['title' => __('Links', 'eventeule'),  'icon' => 'eicon-text-align-left'],
                'center' => ['title' => __('Mitte', 'eventeule'),  'icon' => 'eicon-text-align-center'],
                'right'  => ['title' => __('Rechts', 'eventeule'), 'icon' => 'eicon-text-align-right'],
            ],
            'default'   => 'left',
            'toggle'    => false,
            'selectors' => [
                '{{WRAPPER}} .ee-reg-popup-trigger-wrap' => 'text-align: {{VALUE}};',
            ],
        ]);

        $this->add_control('popup_title', [
            'label'     => __('Popup-Titel', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => __('Anmeldung', 'eventeule'),
            'separator' => 'before',
        ]);

        $this->add_control('show_event_info', [
            'label'        => __('Event-Infos im Popup anzeigen', 'eventeule'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->end_controls_section();

        // ══════════════════════════════════════════════════════════════════════
        // STYLE TABS
        // ══════════════════════════════════════════════════════════════════════

        // ── 1. Button-Stil ────────────────────────────────────────────────────
        $this->start_controls_section('section_button_style', [
            'label' => __('Button', 'eventeule'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('button_type', [
            'label'   => __('Typ', 'eventeule'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'primary',
            'options' => [
                'primary'   => __('Primär (lila)', 'eventeule'),
                'secondary' => __('Sekundär (violett)', 'eventeule'),
                'outline'   => __('Rahmen', 'eventeule'),
            ],
        ]);

        $this->add_control('button_size', [
            'label'   => __('Größe', 'eventeule'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'md',
            'options' => [
                'sm' => __('Klein', 'eventeule'),
                'md' => __('Mittel', 'eventeule'),
                'lg' => __('Groß', 'eventeule'),
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            ['name' => 'button_typography', 'selector' => '{{WRAPPER}} .ee-reg-popup-trigger']
        );

        $this->start_controls_tabs('button_color_tabs');

        $this->start_controls_tab('button_tab_normal', ['label' => __('Normal', 'eventeule')]);
        $this->add_control('button_bg_color', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-trigger' => 'background-color: {{VALUE}};'],
        ]);
        $this->add_control('button_text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-trigger' => 'color: {{VALUE}};'],
        ]);
        $this->end_controls_tab();

        $this->start_controls_tab('button_tab_hover', ['label' => __('Hover', 'eventeule')]);
        $this->add_control('button_bg_color_hover', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-trigger:hover' => 'background-color: {{VALUE}};'],
        ]);
        $this->add_control('button_text_color_hover', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-trigger:hover' => 'color: {{VALUE}};'],
        ]);
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'      => 'button_border',
                'label'     => __('Rahmen', 'eventeule'),
                'selector'  => '{{WRAPPER}} .ee-reg-popup-trigger',
                'separator' => 'before',
            ]
        );

        $this->add_control('button_border_radius', [
            'label'     => __('Eckenradius', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 60]],
            'default'   => ['unit' => 'px', 'size' => 8],
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-trigger' => 'border-radius: {{SIZE}}{{UNIT}};'],
            'separator' => 'before',
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            ['name' => 'button_box_shadow', 'selector' => '{{WRAPPER}} .ee-reg-popup-trigger']
        );

        $this->end_controls_section();

        // ── 2. Popup-Fenster ──────────────────────────────────────────────────
        $this->start_controls_section('section_popup_style', [
            'label' => __('Popup-Fenster', 'eventeule'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name'     => 'popup_bg',
                'label'    => __('Hintergrund', 'eventeule'),
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .ee-reg-popup-dialog',
            ]
        );

        $this->add_control('popup_border_radius', [
            'label'     => __('Eckenradius', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 40]],
            'default'   => ['unit' => 'px', 'size' => 16],
            'selectors' => [
                '{{WRAPPER}} .ee-reg-popup-dialog'  => 'border-radius: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .ee-reg-popup-header'  => 'border-radius: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0 0;',
            ],
        ]);

        $this->add_control('popup_max_width', [
            'label'     => __('Max. Breite', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 300, 'max' => 900]],
            'default'   => ['unit' => 'px', 'size' => 560],
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-dialog' => 'max-width: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            ['name' => 'popup_box_shadow', 'selector' => '{{WRAPPER}} .ee-reg-popup-dialog']
        );

        $this->add_control('overlay_bg_color', [
            'label'     => __('Overlay-Farbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => 'rgba(0,0,0,0.55)',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-overlay.is-open:not(.ee-reg-popup-overlay--editor)' => 'background-color: {{VALUE}};'],
            'separator' => 'before',
        ]);

        $this->end_controls_section();

        // ── 3. Popup-Header ───────────────────────────────────────────────────
        $this->start_controls_section('section_header_style', [
            'label' => __('Popup-Header', 'eventeule'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('header_bg_color', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-header' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('header_border_color', [
            'label'     => __('Trennlinienfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f0f0f0',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-header' => 'border-bottom-color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            ['name' => 'title_typography', 'selector' => '{{WRAPPER}} .ee-reg-popup-title']
        );

        $this->add_control('title_color', [
            'label'     => __('Titelfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#1f2937',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-title' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('title_icon_color', [
            'label'     => __('Icon-Farbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#6366f1',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-title .dashicons' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('header_padding', [
            'label'      => __('Innenabstand', 'eventeule'),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'default'    => ['top' => '20', 'right' => '24', 'bottom' => '16', 'left' => '24', 'unit' => 'px', 'isLinked' => false],
            'selectors'  => ['{{WRAPPER}} .ee-reg-popup-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        // Close button
        $this->add_control('close_btn_heading', [
            'label'     => __('Schließen-Button', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
        ]);

        $this->add_control('close_btn_bg', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f9fafb',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-close' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('close_btn_color', [
            'label'     => __('Iconfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#6b7280',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-close' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('close_btn_border_color', [
            'label'     => __('Rahmenfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#e5e7eb',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-close' => 'border-color: {{VALUE}};'],
        ]);

        $this->add_control('close_btn_border_radius', [
            'label'     => __('Eckenradius', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 50]],
            'default'   => ['unit' => 'px', 'size' => 8],
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-close' => 'border-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_controls_section();

        // ── 4. Event-Info-Leiste ──────────────────────────────────────────────
        $this->start_controls_section('section_eventmeta_style', [
            'label' => __('Event-Info-Leiste', 'eventeule'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('meta_bg_color', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f9fafb',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-event-meta' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('meta_border_color', [
            'label'     => __('Trennlinienfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f0f0f0',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-event-meta' => 'border-bottom-color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            ['name' => 'meta_typography', 'selector' => '{{WRAPPER}} .ee-reg-popup-event-meta']
        );

        $this->add_control('meta_name_color', [
            'label'     => __('Event-Titelfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#1f2937',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-event-name' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('meta_text_color', [
            'label'     => __('Meta-Textfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#4b5563',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-event-meta' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('meta_icon_color', [
            'label'     => __('Icon-Farbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#6366f1',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-event-meta svg' => 'color: {{VALUE}};'],
        ]);

        $this->end_controls_section();

        // ── 5. Kapazitätsleiste ───────────────────────────────────────────────
        $this->start_controls_section('section_capacity_style', [
            'label' => __('Kapazitätsleiste', 'eventeule'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('cap_bg_color', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#e0f2fe',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-capacity' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('cap_text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#0369a1',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-capacity' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            ['name' => 'cap_typography', 'selector' => '{{WRAPPER}} .ee-reg-popup-capacity']
        );

        $this->end_controls_section();

        // ── 6. Formular-Felder ────────────────────────────────────────────────
        $this->start_controls_section('section_form_style', [
            'label' => __('Formular-Felder', 'eventeule'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('form_body_bg', [
            'label'     => __('Body-Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-body' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('form_body_padding', [
            'label'      => __('Body-Innenabstand', 'eventeule'),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'default'    => ['top' => '20', 'right' => '24', 'bottom' => '28', 'left' => '24', 'unit' => 'px', 'isLinked' => false],
            'selectors'  => ['{{WRAPPER}} .ee-reg-popup-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_control('label_heading', [
            'label'     => __('Beschriftungen', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            ['name' => 'label_typography', 'selector' => '{{WRAPPER}} .eventeule-registration__field label']
        );

        $this->add_control('label_color', [
            'label'     => __('Beschriftungsfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#374151',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__field label' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('required_color', [
            'label'     => __('Pflichtfeld-Sternfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#dc3545',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__required' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('input_heading', [
            'label'     => __('Eingabefelder', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            ['name' => 'input_typography', 'selector' => '{{WRAPPER}} .eventeule-registration__field input, {{WRAPPER}} .eventeule-registration__field textarea, {{WRAPPER}} .eventeule-registration__field select']
        );

        $this->add_control('input_bg_color', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .eventeule-registration__field input'    => 'background-color: {{VALUE}};',
                '{{WRAPPER}} .eventeule-registration__field textarea' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .eventeule-registration__field input'    => 'color: {{VALUE}};',
                '{{WRAPPER}} .eventeule-registration__field textarea' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_border_color', [
            'label'     => __('Rahmenfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#d1d5db',
            'selectors' => [
                '{{WRAPPER}} .eventeule-registration__field input'    => 'border-color: {{VALUE}};',
                '{{WRAPPER}} .eventeule-registration__field textarea' => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_focus_border_color', [
            'label'     => __('Rahmen bei Fokus', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#6366f1',
            'selectors' => [
                '{{WRAPPER}} .eventeule-registration__field input:focus'    => 'border-color: {{VALUE}};',
                '{{WRAPPER}} .eventeule-registration__field textarea:focus' => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_border_radius', [
            'label'     => __('Eckenradius', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 24]],
            'default'   => ['unit' => 'px', 'size' => 6],
            'selectors' => [
                '{{WRAPPER}} .eventeule-registration__field input'    => 'border-radius: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .eventeule-registration__field textarea' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_control('input_padding', [
            'label'      => __('Innenabstand', 'eventeule'),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'default'    => ['top' => '8', 'right' => '12', 'bottom' => '8', 'left' => '12', 'unit' => 'px', 'isLinked' => false],
            'selectors'  => [
                '{{WRAPPER}} .eventeule-registration__field input'    => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                '{{WRAPPER}} .eventeule-registration__field textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();

        // ── 7. Absende-Button ─────────────────────────────────────────────────
        $this->start_controls_section('section_submit_style', [
            'label' => __('Absende-Button', 'eventeule'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            ['name' => 'submit_typography', 'selector' => '{{WRAPPER}} .eventeule-registration__submit']
        );

        $this->start_controls_tabs('submit_color_tabs');

        $this->start_controls_tab('submit_tab_normal', ['label' => __('Normal', 'eventeule')]);
        $this->add_control('submit_bg_color', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#6366f1',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__submit' => 'background-color: {{VALUE}};'],
        ]);
        $this->add_control('submit_text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__submit' => 'color: {{VALUE}};'],
        ]);
        $this->end_controls_tab();

        $this->start_controls_tab('submit_tab_hover', ['label' => __('Hover', 'eventeule')]);
        $this->add_control('submit_bg_color_hover', [
            'label'     => __('Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#4f46e5',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__submit:hover' => 'background-color: {{VALUE}};'],
        ]);
        $this->add_control('submit_text_color_hover', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__submit:hover' => 'color: {{VALUE}};'],
        ]);
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control('submit_border_radius', [
            'label'     => __('Eckenradius', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 60]],
            'default'   => ['unit' => 'px', 'size' => 8],
            'selectors' => ['{{WRAPPER}} .eventeule-registration__submit' => 'border-radius: {{SIZE}}{{UNIT}};'],
            'separator' => 'before',
        ]);

        $this->add_control('submit_padding', [
            'label'      => __('Innenabstand', 'eventeule'),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'default'    => ['top' => '10', 'right' => '24', 'bottom' => '10', 'left' => '24', 'unit' => 'px', 'isLinked' => false],
            'selectors'  => ['{{WRAPPER}} .eventeule-registration__submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_control('submit_full_width', [
            'label'        => __('Volle Breite', 'eventeule'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'no',
            'selectors'    => [
                '{{WRAPPER}} .eventeule-registration__submit' => 'width: 100%;',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            ['name' => 'submit_box_shadow', 'selector' => '{{WRAPPER}} .eventeule-registration__submit']
        );

        $this->end_controls_section();

        // ── 8. Erfolgs-/Fehlermeldungen ───────────────────────────────────────
        $this->start_controls_section('section_messages_style', [
            'label' => __('Meldungen', 'eventeule'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            ['name' => 'msg_typography', 'selector' => '{{WRAPPER}} .eventeule-registration__message']
        );

        $this->add_control('msg_success_bg', [
            'label'     => __('Erfolg: Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#d4edda',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__message--success' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('msg_success_color', [
            'label'     => __('Erfolg: Textfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#155724',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__message--success' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('msg_success_border', [
            'label'     => __('Erfolg: Linienfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#28a745',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__message--success' => 'border-left-color: {{VALUE}};'],
        ]);

        $this->add_control('msg_error_bg', [
            'label'     => __('Fehler: Hintergrund', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f8d7da',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__message--error' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('msg_error_color', [
            'label'     => __('Fehler: Textfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#721c24',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__message--error' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('msg_error_border', [
            'label'     => __('Fehler: Linienfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#dc3545',
            'selectors' => ['{{WRAPPER}} .eventeule-registration__message--error' => 'border-left-color: {{VALUE}};'],
        ]);

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────────────────────────────────

    protected function render(): void
    {
        $settings = $this->get_settings_for_display();

        // Resolve event ID
        $event_id = (int) ($settings['event_id'] ?? 0);
        if ($event_id <= 0) {
            global $post;
            $event_id = $post ? (int) $post->ID : 0;
        }

        $is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();

        if ($event_id <= 0) {
            if ($is_edit) {
                echo '<div class="ee-reg-popup-notice">' . esc_html__('Bitte wähle ein Event in den Widget-Einstellungen.', 'eventeule') . '</div>';
            }
            return;
        }

        $reg_enabled = get_post_meta($event_id, '_eventeule_reg_enabled', true) === '1';
        if (!$reg_enabled) {
            if ($is_edit) {
                echo '<div class="ee-reg-popup-notice">'
                    . sprintf(
                        /* translators: %s = event title */
                        esc_html__('Anmeldung für „%s" ist nicht aktiviert. Bitte in den Event-Einstellungen aktivieren.', 'eventeule'),
                        esc_html(get_the_title($event_id))
                    )
                    . '</div>';
            }
            return;
        }

        // Registration meta
        $enabled_fields  = $this->parse_reg_fields('_eventeule_reg_fields',   $event_id, ['firstname', 'email']);
        $required_fields = $this->parse_reg_fields('_eventeule_reg_required',  $event_id, ['firstname', 'email']);
        $max_reg         = (int) get_post_meta($event_id, '_eventeule_reg_max', true);

        $repo      = new RegistrationRepository();
        $current   = $max_reg > 0 ? $repo->count_by_event($event_id) : 0;
        $available = $max_reg > 0 ? max(0, $max_reg - $current) : -1; // -1 = unlimited

        $nonce       = wp_create_nonce('eventeule_register');
        $ajax_url    = admin_url('admin-ajax.php');
        $event_title = get_the_title($event_id);

        // Event info for popup header
        $start_date = (string) get_post_meta($event_id, '_eventeule_start_date', true);
        $start_time = (string) get_post_meta($event_id, '_eventeule_start_time', true);
        $location   = (string) get_post_meta($event_id, '_eventeule_location',   true);

        // Button display settings
        $btn_type        = esc_attr($settings['button_type'] ?? 'primary');
        $btn_size        = esc_attr($settings['button_size'] ?? 'md');
        $btn_text        = $settings['button_text'] ?? __('Jetzt anmelden', 'eventeule');
        $popup_title     = $settings['popup_title']  ?? __('Anmeldung', 'eventeule');
        $show_event_info = ($settings['show_event_info'] ?? 'yes') === 'yes';
        $popup_id        = 'ee-reg-popup-' . esc_attr($this->get_id());
        // In editor mode the overlay should be pre-opened so all controls are live-editable
        $overlay_extra   = $is_edit ? ' is-open ee-reg-popup-overlay--editor' : '';

        // Field labels / types
        $field_labels = [
            'firstname'    => __('Vorname', 'eventeule'),
            'lastname'     => __('Nachname', 'eventeule'),
            'email'        => __('E-Mail', 'eventeule'),
            'phone'        => __('Telefon', 'eventeule'),
            'participants' => __('Anzahl Teilnehmer', 'eventeule'),
            'message'      => __('Nachricht / Anmerkung', 'eventeule'),
        ];
        $field_types = [
            'firstname'    => 'text',
            'lastname'     => 'text',
            'email'        => 'email',
            'phone'        => 'tel',
            'participants' => 'number',
            'message'      => 'textarea',
        ];
        $autocomplete_map = [
            'firstname' => 'given-name',
            'lastname'  => 'family-name',
            'email'     => 'email',
            'phone'     => 'tel',
        ];
        ?>
        <div class="ee-reg-popup-wrap">

            <!-- ── Trigger button ──────────────────────────────────────── -->
            <div class="ee-reg-popup-trigger-wrap">
                <button
                    type="button"
                    class="ee-reg-popup-trigger ee-reg-popup-trigger--<?php echo $btn_type; ?> ee-reg-popup-trigger--<?php echo $btn_size; ?>"
                    aria-haspopup="dialog"
                    aria-controls="<?php echo $popup_id; ?>"
                    <?php echo $available === 0 ? 'disabled' : ''; ?>
                >
                    <?php if ($available === 0): ?>
                        <?php esc_html_e('Ausgebucht', 'eventeule'); ?>
                    <?php else: ?>
                        <?php echo esc_html($btn_text); ?>
                    <?php endif; ?>
                </button>

                <?php if ($max_reg > 0 && $available > 0): ?>
                    <span class="ee-reg-popup-spots">
                        <?php printf(
                            /* translators: %1$d = available, %2$d = total */
                            esc_html__('%1$d von %2$d Plätzen frei', 'eventeule'),
                            $available,
                            $max_reg
                        ); ?>
                    </span>
                <?php elseif ($max_reg > 0 && $available === 0): ?>
                    <span class="ee-reg-popup-spots ee-reg-popup-spots--full">
                        <?php esc_html_e('Ausgebucht', 'eventeule'); ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- ── Registration popup ─────────────────────────────────── -->
            <div
                id="<?php echo $popup_id; ?>"
                class="ee-reg-popup-overlay<?php echo $overlay_extra; ?>"
                role="dialog"
                aria-modal="true"
                aria-labelledby="<?php echo $popup_id; ?>-title"
            >
                <div class="ee-reg-popup-dialog">

                    <!-- Header -->
                    <div class="ee-reg-popup-header">
                        <h2 id="<?php echo $popup_id; ?>-title" class="ee-reg-popup-title">
                            <span class="dashicons dashicons-groups" aria-hidden="true"></span>
                            <?php echo esc_html($popup_title); ?>
                        </h2>
                        <button
                            type="button"
                            class="ee-reg-popup-close"
                            aria-label="<?php esc_attr_e('Schließen', 'eventeule'); ?>"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Event info row -->
                    <?php if ($show_event_info && ($event_title || $start_date || $location)): ?>
                    <div class="ee-reg-popup-event-meta">
                        <span class="ee-reg-popup-event-name"><?php echo esc_html($event_title); ?></span>

                        <?php if ($start_date): ?>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <?php
                            echo esc_html(date_i18n('j. F Y', strtotime($start_date)));
                            if ($start_time) {
                                echo ', ' . esc_html(substr($start_time, 0, 5)) . ' ' . esc_html__('Uhr', 'eventeule');
                            }
                            ?>
                        </span>
                        <?php endif; ?>

                        <?php if ($location): ?>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <?php echo esc_html($location); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Capacity bar -->
                    <?php if ($max_reg > 0): ?>
                    <div class="ee-reg-popup-capacity<?php
                        if ($available === 0) echo ' ee-reg-popup-capacity--full';
                        elseif ($available > 0 && $available <= 3) echo ' ee-reg-popup-capacity--warning';
                    ?>"
                        data-max="<?php echo esc_attr($max_reg); ?>"
                        data-available="<?php echo esc_attr($available); ?>">
                        <?php if ($available > 0): ?>
                            <?php printf(
                                esc_html__('%1$d von %2$d Plätzen verfügbar', 'eventeule'),
                                $available,
                                $max_reg
                            ); ?>
                        <?php else: ?>
                            <?php esc_html_e('Leider ausgebucht.', 'eventeule'); ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Body / Form -->
                    <div class="ee-reg-popup-body">
                        <?php if ($available === 0): ?>
                            <p class="eventeule-registration__booked">
                                <?php esc_html_e('Leider ist dieses Event bereits ausgebucht.', 'eventeule'); ?>
                            </p>
                        <?php else: ?>
                            <div class="eventeule-registration__messages" aria-live="polite"></div>

                            <form
                                class="eventeule-registration__form"
                                data-ajax-url="<?php echo esc_url($ajax_url); ?>"
                                data-nonce="<?php echo esc_attr($nonce); ?>"
                                data-event-id="<?php echo esc_attr($event_id); ?>"
                                novalidate
                            >
                                <?php foreach ($enabled_fields as $field):
                                    $label    = $field_labels[$field] ?? $field;
                                    $type     = $field_types[$field]  ?? 'text';
                                    $required = in_array($field, $required_fields, true);
                                    $input_id = 'ee_reg_' . $event_id . '_' . $this->get_id() . '_' . $field;
                                    $ac       = $autocomplete_map[$field] ?? 'off';
                                ?>
                                    <div class="eventeule-registration__field" data-field="<?php echo esc_attr($field); ?>">
                                        <label for="<?php echo esc_attr($input_id); ?>">
                                            <?php echo esc_html($label); ?>
                                            <?php if ($required): ?><span class="eventeule-registration__required" aria-hidden="true">*</span><?php endif; ?>
                                        </label>

                                        <?php if ($type === 'textarea'): ?>
                                            <textarea
                                                id="<?php echo esc_attr($input_id); ?>"
                                                name="<?php echo esc_attr($field); ?>"
                                                rows="3"
                                                <?php echo $required ? 'required aria-required="true"' : ''; ?>
                                            ></textarea>
                                        <?php elseif ($type === 'number'): ?>
                                            <input
                                                type="number"
                                                id="<?php echo esc_attr($input_id); ?>"
                                                name="<?php echo esc_attr($field); ?>"
                                                value="1"
                                                min="1"
                                                max="<?php echo $available > 0 ? esc_attr($available) : '50'; ?>"
                                                <?php echo $required ? 'required aria-required="true"' : ''; ?>
                                            />
                                        <?php else: ?>
                                            <input
                                                type="<?php echo esc_attr($type); ?>"
                                                id="<?php echo esc_attr($input_id); ?>"
                                                name="<?php echo esc_attr($field); ?>"
                                                autocomplete="<?php echo esc_attr($ac); ?>"
                                                <?php echo $required ? 'required aria-required="true"' : ''; ?>
                                            />
                                        <?php endif; ?>

                                        <span class="eventeule-registration__field-error" role="alert"></span>
                                    </div>
                                <?php endforeach; ?>

                                <div class="eventeule-registration__actions">
                                    <button type="submit" class="eventeule-registration__submit">
                                        <span class="eventeule-registration__submit-text">
                                            <?php esc_html_e('Jetzt anmelden', 'eventeule'); ?>
                                        </span>
                                        <span class="eventeule-registration__submit-spinner" aria-hidden="true" style="display:none"></span>
                                    </button>
                                </div>

                                <?php if (!empty($required_fields)): ?>
                                <p class="eventeule-registration__privacy">
                                    <small><?php esc_html_e('Mit * gekennzeichnete Felder sind Pflichtfelder.', 'eventeule'); ?></small>
                                </p>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </div><!-- .ee-reg-popup-body -->

                </div><!-- .ee-reg-popup-dialog -->
            </div><!-- .ee-reg-popup-overlay -->

        </div><!-- .ee-reg-popup-wrap -->
        <?php
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Read a comma-separated post-meta field with a fallback default.
     * Fields are stored as e.g. "firstname,lastname,email,phone".
     *
     * @param  string   $meta_key
     * @param  int      $event_id
     * @param  string[] $defaults
     * @return string[]
     */
    private function parse_reg_fields(string $meta_key, int $event_id, array $defaults): array
    {
        $raw = (string) get_post_meta($event_id, $meta_key, true);
        if ($raw === '') {
            return $defaults;
        }
        $fields = array_values(array_filter(array_map('trim', explode(',', $raw))));
        return $fields ?: $defaults;
    }
}
