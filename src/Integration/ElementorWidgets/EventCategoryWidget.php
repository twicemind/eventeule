<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use EventEule\Domain\EventCategoryTaxonomy;

class EventCategoryWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_category';
    }

    public function get_title(): string
    {
        return __('Event Kategorie', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-tags';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'category', 'kategorie', 'tag', 'eventeule'];
    }

    protected function register_controls(): void
    {
        // ── Content ───────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => __('Inhalt', 'eventeule'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('show_all', [
            'label'        => __('Alle Kategorien anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Alle', 'eventeule'),
            'label_off'    => __('Erste', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('separator', [
            'label'     => __('Trenner', 'eventeule'),
            'type'      => Controls_Manager::TEXT,
            'default'   => ', ',
            'condition' => ['show_all' => 'yes'],
        ]);

        $this->add_control('link_categories', [
            'label'        => __('Kategorien verlinken', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'no',
        ]);

        $this->add_control('display_as_badge', [
            'label'        => __('Als Badge anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'no',
            'separator'    => 'before',
        ]);

        $this->add_control('prefix', [
            'label'       => __('Präfix', 'eventeule'),
            'type'        => Controls_Manager::TEXT,
            'default'     => '',
            'placeholder' => __('z. B. Kategorie:', 'eventeule'),
        ]);

        $this->add_control('hide_if_empty', [
            'label'        => __('Ausblenden wenn keine Kategorie', 'eventeule'),
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
            ['name' => 'text_typography', 'selector' => '{{WRAPPER}} .ee-event-category']
        );

        $this->add_control('text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ee-event-category'           => 'color: {{VALUE}};',
                '{{WRAPPER}} .ee-event-category a'         => 'color: {{VALUE}};',
                '{{WRAPPER}} .ee-event-category__badge'    => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('badge_bg_color', [
            'label'     => __('Badge Hintergrund', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ede9fe',
            'selectors' => ['{{WRAPPER}} .ee-event-category__badge' => 'background-color: {{VALUE}};'],
            'condition' => ['display_as_badge' => 'yes'],
        ]);

        $this->add_control('badge_border_radius', [
            'label'     => __('Badge Eckenradius', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 50]],
            'default'   => ['unit' => 'px', 'size' => 20],
            'selectors' => ['{{WRAPPER}} .ee-event-category__badge' => 'border-radius: {{SIZE}}{{UNIT}};'],
            'condition' => ['display_as_badge' => 'yes'],
        ]);

        $this->add_control('gap', [
            'label'     => __('Abstand zwischen Badges', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 24]],
            'default'   => ['unit' => 'px', 'size' => 6],
            'selectors' => ['{{WRAPPER}} .ee-event-category' => 'gap: {{SIZE}}{{UNIT}};'],
            'condition' => ['display_as_badge' => 'yes'],
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

        $terms = get_the_terms($post_id, EventCategoryTaxonomy::TAXONOMY);

        if (!$terms || is_wp_error($terms) || empty($terms)) {
            if ($settings['hide_if_empty'] === 'yes') {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    echo '<div class="ee-reg-popup-notice">' . esc_html__('Keine Kategorie zugewiesen.', 'eventeule') . '</div>';
                }
                return;
            }
            $terms = [];
        }

        $show_all      = $settings['show_all'] === 'yes';
        $link_cats     = $settings['link_categories'] === 'yes';
        $as_badge      = $settings['display_as_badge'] === 'yes';
        $separator     = $settings['separator'] ?: ', ';
        $prefix        = trim($settings['prefix']);

        $display_terms = $show_all ? $terms : [$terms[0]];

        $wrap_class = 'ee-event-category' . ($as_badge ? ' ee-event-category--badges' : '');
        echo '<div class="' . esc_attr($wrap_class) . '">';

        if ($prefix !== '') {
            echo '<span class="ee-event-category__prefix">' . esc_html($prefix) . '</span>';
        }

        $parts = [];
        foreach ($display_terms as $term) {
            $label = esc_html($term->name);

            if ($link_cats) {
                $url   = get_term_link($term);
                $label = '<a href="' . (is_wp_error($url) ? '#' : esc_url($url)) . '">' . $label . '</a>';
            }

            if ($as_badge) {
                $parts[] = '<span class="ee-event-category__badge">' . $label . '</span>';
            } else {
                $parts[] = $label;
            }
        }

        if ($as_badge) {
            echo implode('', $parts);
        } else {
            echo implode(esc_html($separator), $parts);
        }

        echo '</div>';
    }
}
