<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use EventEule\Registration\RegistrationRepository;

/**
 * Elementor widget displaying available / total spots for an event's registration.
 */
class EventCapacityWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_capacity';
    }

    public function get_title(): string
    {
        return __('Verfügbare Plätze', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-counter';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'capacity', 'spots', 'registration', 'plätze', 'anmeldung', 'verfügbar'];
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

        $this->add_control('display_style', [
            'label'   => __('Anzeigeformat', 'eventeule'),
            'type'    => Controls_Manager::SELECT,
            'default' => 'bar',
            'options' => [
                'bar'    => __('Fortschrittsbalken', 'eventeule'),
                'text'   => __('Text', 'eventeule'),
                'badges' => __('Badges', 'eventeule'),
            ],
        ]);

        $this->add_control('show_label', [
            'label'        => __('Beschriftung anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('label_text', [
            'label'     => __('Beschriftung', 'eventeule'),
            'type'      => Controls_Manager::TEXT,
            'default'   => __('Verfügbare Plätze', 'eventeule'),
            'condition' => ['show_label' => 'yes'],
        ]);

        $this->add_control('text_unlimited', [
            'label'       => __('Text: Unbegrenzte Plätze', 'eventeule'),
            'type'        => Controls_Manager::TEXT,
            'default'     => __('Freie Anmeldung', 'eventeule'),
            'description' => __('Angezeigt wenn kein Maximum festgelegt ist.', 'eventeule'),
        ]);

        $this->add_control('text_fully_booked', [
            'label'   => __('Text: Ausgebucht', 'eventeule'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('Ausgebucht', 'eventeule'),
        ]);

        $this->add_control('show_numbers', [
            'label'        => __('Zahlen anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
            'condition'    => ['display_style' => ['bar', 'text']],
        ]);

        $this->add_control('number_format', [
            'label'     => __('Zahlenformat', 'eventeule'),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'available_of_total',
            'options'   => [
                'available_of_total' => __('5 von 20', 'eventeule'),
                'available_only'     => __('5 frei', 'eventeule'),
                'booked_of_total'    => __('15 / 20 gebucht', 'eventeule'),
                'percent'            => __('75 % gebucht', 'eventeule'),
            ],
            'condition' => ['show_numbers' => 'yes', 'display_style' => ['bar', 'text']],
        ]);

        $this->add_control('hide_when_no_registration', [
            'label'        => __('Ausblenden wenn Anmeldung deaktiviert', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Ja', 'eventeule'),
            'label_off'    => __('Nein', 'eventeule'),
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->end_controls_section();

        // ── Style ─────────────────────────────────────────────────────────────
        $this->start_controls_section('section_style', [
            'label' => __('Stil', 'eventeule'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            ['name' => 'typography', 'selector' => '{{WRAPPER}} .ee-capacity']
        );

        $this->add_control('text_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#1f2937',
            'selectors' => ['{{WRAPPER}} .ee-capacity, {{WRAPPER}} .ee-capacity__label, {{WRAPPER}} .ee-capacity__numbers' => 'color: {{VALUE}};'],
        ]);

        // Bar-specific
        $this->add_control('bar_heading', [
            'label'     => __('Fortschrittsbalken', 'eventeule'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
            'condition' => ['display_style' => 'bar'],
        ]);

        $this->add_control('bar_height', [
            'label'     => __('Balkenhöhe', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 4, 'max' => 24]],
            'default'   => ['unit' => 'px', 'size' => 8],
            'selectors' => ['{{WRAPPER}} .ee-capacity__track' => 'height: {{SIZE}}{{UNIT}};'],
            'condition' => ['display_style' => 'bar'],
        ]);

        $this->add_control('bar_radius', [
            'label'     => __('Eckenradius', 'eventeule'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => ['px' => ['min' => 0, 'max' => 20]],
            'default'   => ['unit' => 'px', 'size' => 99],
            'selectors' => [
                '{{WRAPPER}} .ee-capacity__track'    => 'border-radius: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .ee-capacity__fill'     => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
            'condition' => ['display_style' => 'bar'],
        ]);

        $this->add_control('bar_bg_color', [
            'label'     => __('Hintergrundfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#e5e7eb',
            'selectors' => ['{{WRAPPER}} .ee-capacity__track' => 'background-color: {{VALUE}};'],
            'condition' => ['display_style' => 'bar'],
        ]);

        $this->add_control('bar_fill_color', [
            'label'     => __('Füllfarbe (normal)', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#6366f1',
            'selectors' => ['{{WRAPPER}} .ee-capacity__fill' => 'background-color: {{VALUE}};'],
            'condition' => ['display_style' => 'bar'],
        ]);

        $this->add_control('bar_fill_warning_color', [
            'label'       => __('Füllfarbe (fast voll, ≤ 20 %)', 'eventeule'),
            'type'        => Controls_Manager::COLOR,
            'default'     => '#f59e0b',
            'description' => __('Wird automatisch gesetzt wenn ≤ 20 % Plätze verfügbar sind.', 'eventeule'),
            'condition'   => ['display_style' => 'bar'],
        ]);

        $this->add_control('bar_fill_full_color', [
            'label'     => __('Füllfarbe (ausgebucht)', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ef4444',
            'condition' => ['display_style' => 'bar'],
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
                echo '<div class="ee-reg-popup-notice">' . esc_html__('Dieses Widget zeigt die Platzkapazität des aktuellen Events.', 'eventeule') . '</div>';
            }
            return;
        }

        $reg_enabled = get_post_meta($event_id, '_eventeule_reg_enabled', true) === '1';

        if (!$reg_enabled) {
            if (($settings['hide_when_no_registration'] ?? 'yes') === 'yes') {
                return;
            }
            if ($is_edit) {
                echo '<div class="ee-reg-popup-notice">' . esc_html__('Anmeldung für dieses Event ist nicht aktiviert.', 'eventeule') . '</div>';
            }
            return;
        }

        $max_reg    = (int) get_post_meta($event_id, '_eventeule_reg_max', true);
        $ext_count  = (int) get_post_meta($event_id, '_eventeule_reg_ext_count', true);
        $repo       = new RegistrationRepository();
        $internal   = $max_reg > 0 ? $repo->count_by_event($event_id) : 0;
        $booked     = $internal + $ext_count;
        $avail      = $max_reg > 0 ? max(0, $max_reg - $booked) : -1; // -1 = unlimited

        $style         = $settings['display_style']    ?? 'bar';
        $show_label    = ($settings['show_label']   ?? 'yes') === 'yes';
        $show_numbers  = ($settings['show_numbers'] ?? 'yes') === 'yes';
        $label_text    = $settings['label_text']        ?? __('Verfügbare Plätze', 'eventeule');
        $txt_unlimited = $settings['text_unlimited']    ?? __('Freie Anmeldung', 'eventeule');
        $txt_booked    = $settings['text_fully_booked'] ?? __('Ausgebucht', 'eventeule');
        $num_format    = $settings['number_format']     ?? 'available_of_total';

        // Derive percent booked (for bar fill width & color thresholds)
        $pct_booked   = ($max_reg > 0) ? min(100, round($booked / $max_reg * 100)) : 0;
        $pct_avail    = 100 - $pct_booked;
        $is_full      = ($avail === 0);
        $is_unlimited = ($avail === -1);

        // Bar fill color key (used as data-attr for CSS custom property override)
        $fill_key = 'normal';
        if ($is_full)              $fill_key = 'full';
        elseif ($pct_avail <= 20)  $fill_key = 'warning';

        // Build the human-readable number string
        $number_str = '';
        if (!$is_unlimited && $show_numbers && in_array($style, ['bar', 'text'], true)) {
            switch ($num_format) {
                case 'available_only':
                    $number_str = sprintf(
                        _n('%d Platz frei', '%d Plätze frei', $avail, 'eventeule'),
                        $avail
                    );
                    break;
                case 'booked_of_total':
                    $number_str = sprintf(
                        /* translators: %1$d = booked, %2$d = total */
                        __('%1$d / %2$d gebucht', 'eventeule'),
                        $booked,
                        $max_reg
                    );
                    break;
                case 'percent':
                    $number_str = sprintf(__('%d %% gebucht', 'eventeule'), $pct_booked);
                    break;
                default: // available_of_total
                    $number_str = sprintf(
                        /* translators: %1$d = available, %2$d = total */
                        __('%1$d von %2$d Plätzen frei', 'eventeule'),
                        $avail,
                        $max_reg
                    );
            }
        }

        // Bar fill inline style (color can be overridden by controls)
        $fill_color_normal  = $settings['bar_fill_color']         ?? '#6366f1';
        $fill_color_warning = $settings['bar_fill_warning_color'] ?? '#f59e0b';
        $fill_color_full    = $settings['bar_fill_full_color']    ?? '#ef4444';

        $fill_color = match ($fill_key) {
            'warning' => $fill_color_warning,
            'full'    => $fill_color_full,
            default   => $fill_color_normal,
        };

        // ── Output ───────────────────────────────────────────────────────────
        ?>
        <div class="ee-capacity ee-capacity--<?php echo esc_attr($style); ?> ee-capacity--<?php echo esc_attr($fill_key); ?>">

            <?php if ($show_label): ?>
            <div class="ee-capacity__label">
                <?php echo esc_html($label_text); ?>
            </div>
            <?php endif; ?>

            <?php if ($is_unlimited): ?>
                <div class="ee-capacity__unlimited"><?php echo esc_html($txt_unlimited); ?></div>

            <?php elseif ($style === 'bar'): ?>
                <?php if ($show_numbers): ?>
                <div class="ee-capacity__header">
                    <span class="ee-capacity__numbers"><?php echo esc_html($is_full ? $txt_booked : $number_str); ?></span>
                </div>
                <?php endif; ?>
                <div class="ee-capacity__track" role="progressbar"
                     aria-valuemin="0" aria-valuemax="<?php echo esc_attr($max_reg); ?>"
                     aria-valuenow="<?php echo esc_attr($booked); ?>"
                     aria-label="<?php esc_attr_e('Belegung', 'eventeule'); ?>">
                    <div class="ee-capacity__fill"
                         style="width: <?php echo esc_attr($pct_booked); ?>%; background-color: <?php echo esc_attr($fill_color); ?>;"></div>
                </div>

            <?php elseif ($style === 'text'): ?>
                <div class="ee-capacity__text">
                    <?php echo esc_html($is_full ? $txt_booked : ($number_str ?: $txt_unlimited)); ?>
                </div>

            <?php elseif ($style === 'badges'): ?>
                <div class="ee-capacity__badges">
                    <?php if ($is_full): ?>
                        <span class="ee-capacity__badge ee-capacity__badge--full"><?php echo esc_html($txt_booked); ?></span>
                    <?php else: ?>
                        <span class="ee-capacity__badge ee-capacity__badge--available">
                            <?php printf(
                                _n('%d Platz frei', '%d Plätze frei', $avail, 'eventeule'),
                                $avail
                            ); ?>
                        </span>
                        <?php if ($max_reg > 0): ?>
                        <span class="ee-capacity__badge ee-capacity__badge--total">
                            <?php printf(
                                /* translators: %d = total */
                                __('%d gesamt', 'eventeule'),
                                $max_reg
                            ); ?>
                        </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
        <?php
    }
}
