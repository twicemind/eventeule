<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Elementor widget that displays the current status of an event
 * (Jetzt, Heute, Bald, Vergangen, Abgesagt) as a styled badge or label.
 */
class EventStatusWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_status';
    }

    public function get_title(): string
    {
        return __('Veranstaltungs-Status', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-info-circle-o';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'status', 'badge', 'jetzt', 'heute', 'bald', 'abgesagt', 'vergangen', 'tag'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Controls
    // ─────────────────────────────────────────────────────────────────────────

    protected function register_controls(): void
    {
        // ── Content ──────────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => __('Inhalt', 'eventeule'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('soon_days', [
            'label'       => __('„Bald"-Schwelle (Tage)', 'eventeule'),
            'type'        => Controls_Manager::NUMBER,
            'min'         => 1,
            'max'         => 90,
            'default'     => 7,
            'description' => __('Events innerhalb dieser Anzahl Tage gelten als „Bald".', 'eventeule'),
        ]);

        $this->add_control('hide_when_no_status', [
            'label'        => __('Ausblenden wenn kein Status', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
            'description'  => __('Versteckt das Widget komplett wenn kein Status-Label passt (z. B. normales zukünftiges Event).', 'eventeule'),
        ]);

        $this->add_control('show_past', [
            'label'        => __('„Vergangen" anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'no',
        ]);

        // Custom label overrides
        $this->add_control('labels_heading', [
            'label'     => __('Bezeichnungen anpassen', 'eventeule'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
        ]);

        $this->add_control('label_now', [
            'label'   => __('„Jetzt"', 'eventeule'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('Jetzt', 'eventeule'),
        ]);

        $this->add_control('label_today', [
            'label'   => __('„Heute"', 'eventeule'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('Heute', 'eventeule'),
        ]);

        $this->add_control('label_soon', [
            'label'   => __('„Bald"', 'eventeule'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('Bald', 'eventeule'),
        ]);

        $this->add_control('label_cancelled', [
            'label'   => __('„Abgesagt"', 'eventeule'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('Abgesagt', 'eventeule'),
        ]);

        $this->add_control('label_past', [
            'label'     => __('„Vergangen"', 'eventeule'),
            'type'      => Controls_Manager::TEXT,
            'default'   => __('Vergangen', 'eventeule'),
            'condition' => ['show_past' => 'yes'],
        ]);

        $this->end_controls_section();

        // ── Style ─────────────────────────────────────────────────────────────
        $this->start_controls_section('section_style', [
            'label' => __('Stil', 'eventeule'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('style_preset', [
            'label'   => __('Stil-Vorlage', 'eventeule'),
            'type'    => Controls_Manager::SELECT,
            'default' => 'badge',
            'options' => [
                'badge'   => __('Badge (gefüllt)', 'eventeule'),
                'outline' => __('Badge (Rahmen)', 'eventeule'),
                'pill'    => __('Pill', 'eventeule'),
                'tag'     => __('Tag', 'eventeule'),
                'plain'   => __('Nur Text', 'eventeule'),
            ],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            ['name' => 'badge_typography', 'selector' => '{{WRAPPER}} .ee-status-badge']
        );

        $this->add_control('badge_align', [
            'label'   => __('Ausrichtung', 'eventeule'),
            'type'    => Controls_Manager::CHOOSE,
            'options' => [
                'flex-start' => ['title' => __('Links', 'eventeule'),  'icon' => 'eicon-text-align-left'],
                'center'     => ['title' => __('Mitte', 'eventeule'),  'icon' => 'eicon-text-align-center'],
                'flex-end'   => ['title' => __('Rechts', 'eventeule'), 'icon' => 'eicon-text-align-right'],
            ],
            'default' => 'flex-start',
            'toggle'  => false,
            'selectors' => [
                '{{WRAPPER}} .ee-status-badge-wrap' => 'display: flex; justify-content: {{VALUE}};',
            ],
        ]);

        // Custom colors per status (collapsible with HEADING+COLOR pairs)
        foreach ([
            'now'       => ['label' => __('„Jetzt" Farbe',       'eventeule'), 'default_bg' => '#10b981', 'default_text' => '#fff'],
            'today'     => ['label' => __('„Heute" Farbe',       'eventeule'), 'default_bg' => '#3b82f6', 'default_text' => '#fff'],
            'soon'      => ['label' => __('„Bald" Farbe',        'eventeule'), 'default_bg' => '#f59e0b', 'default_text' => '#fff'],
            'cancelled' => ['label' => __('„Abgesagt" Farbe',    'eventeule'), 'default_bg' => '#ef4444', 'default_text' => '#fff'],
            'past'      => ['label' => __('„Vergangen" Farbe',   'eventeule'), 'default_bg' => '#9ca3af', 'default_text' => '#fff'],
        ] as $key => $cfg) {
            $this->add_control("color_heading_{$key}", [
                'label'     => $cfg['label'],
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]);

            $this->add_control("bg_color_{$key}", [
                'label'     => __('Hintergrund', 'eventeule'),
                'type'      => Controls_Manager::COLOR,
                'default'   => $cfg['default_bg'],
                'selectors' => [
                    "{{WRAPPER}} .ee-status-badge--{$key}" => 'background-color: {{VALUE}};',
                    "{{WRAPPER}} .ee-status-badge--{$key}.is-outline" => 'border-color: {{VALUE}}; color: {{VALUE}};',
                ],
            ]);

            $this->add_control("text_color_{$key}", [
                'label'     => __('Textfarbe', 'eventeule'),
                'type'      => Controls_Manager::COLOR,
                'default'   => $cfg['default_text'],
                'selectors' => [
                    "{{WRAPPER}} .ee-status-badge--{$key}" => 'color: {{VALUE}};',
                ],
            ]);
        }

        $this->add_control('border_radius', [
            'label'     => __('Eckenradius', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 60]],
            'default'   => ['unit' => 'px', 'size' => 4],
            'separator' => 'before',
            'selectors' => ['{{WRAPPER}} .ee-status-badge' => 'border-radius: {{SIZE}}{{UNIT}};'],
        ]);

        $this->add_control('padding', [
            'label'      => __('Innenabstand', 'eventeule'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'default'    => ['top' => '4', 'right' => '12', 'bottom' => '4', 'left' => '12', 'unit' => 'px', 'isLinked' => false],
            'selectors'  => ['{{WRAPPER}} .ee-status-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_controls_section();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────────────────────────────────

    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        $is_edit  = \Elementor\Plugin::$instance->editor->is_edit_mode();

        global $post;
        $event_id = $post ? (int) $post->ID : 0;

        if ($event_id <= 0) {
            if ($is_edit) {
                echo '<div class="ee-reg-popup-notice">' . esc_html__('Dieses Widget zeigt den Status des aktuellen Events.', 'eventeule') . '</div>';
            }
            return;
        }

        // ── Compute status ───────────────────────────────────────────────────
        $start_date = (string) get_post_meta($event_id, '_eventeule_start_date', true);
        $start_time = (string) get_post_meta($event_id, '_eventeule_start_time', true);
        $end_time   = (string) get_post_meta($event_id, '_eventeule_end_time',   true);
        $is_cancelled = get_post_meta($event_id, '_eventeule_cancelled', true) === '1';

        $soon_days = max(1, (int) ($settings['soon_days'] ?? 7));
        $now_local = current_time('Y-m-d H:i');
        $today_ymd = current_time('Y-m-d');
        $soon_ymd  = date('Y-m-d', strtotime("+{$soon_days} days", current_time('timestamp')));

        $status_key   = '';   // now | today | soon | past | cancelled
        $status_label = '';

        if ($is_cancelled) {
            $status_key   = 'cancelled';
            $status_label = $settings['label_cancelled'] ?? __('Abgesagt', 'eventeule');
        } elseif ($start_date !== '') {
            $event_start_local = $start_date . ' ' . ($start_time ?: '00:00');
            $end_hi            = $end_time
                ? substr(trim($end_time), 0, 5)
                : date('H:i', strtotime('+1 hour', strtotime($event_start_local)));
            $event_end_local   = $start_date . ' ' . $end_hi;

            if ($now_local >= $event_start_local && $now_local <= $event_end_local) {
                $status_key   = 'now';
                $status_label = $settings['label_now'] ?? __('Jetzt', 'eventeule');
            } elseif ($start_date === $today_ymd) {
                $status_key   = 'today';
                $status_label = $settings['label_today'] ?? __('Heute', 'eventeule');
            } elseif ($start_date > $today_ymd && $start_date <= $soon_ymd) {
                $status_key   = 'soon';
                $status_label = $settings['label_soon'] ?? __('Bald', 'eventeule');
            } elseif ($event_end_local < $now_local) {
                if (($settings['show_past'] ?? 'no') !== 'yes') {
                    // No status assigned; may hide below
                } else {
                    $status_key   = 'past';
                    $status_label = $settings['label_past'] ?? __('Vergangen', 'eventeule');
                }
            }
        }

        // Hide if no status matched
        if ($status_key === '') {
            if (($settings['hide_when_no_status'] ?? 'yes') === 'yes') {
                return;
            }
            if ($is_edit) {
                echo '<div class="ee-reg-popup-notice">'
                    . esc_html__('Kein Status passend — Widget wird auf der Seite ausgeblendet.', 'eventeule')
                    . '</div>';
            }
            return;
        }

        // ── Build CSS classes ─────────────────────────────────────────────────
        $preset    = $settings['style_preset'] ?? 'badge';
        $is_outline = $preset === 'outline';

        $classes = ['ee-status-badge', "ee-status-badge--{$status_key}", "ee-status-badge--preset-{$preset}"];
        if ($is_outline) {
            $classes[] = 'is-outline';
        }

        ?>
        <div class="ee-status-badge-wrap">
            <span class="<?php echo esc_attr(implode(' ', $classes)); ?>">
                <?php echo esc_html($status_label); ?>
            </span>
        </div>
        <?php
    }
}
