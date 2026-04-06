<?php

namespace EventEule\Integration\ElementorWidgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use EventEule\Domain\EventPostType;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * EventEule QR-Code Widget
 *
 * Generates a QR code for the event permalink using the qrserver.com API.
 * No server-side dependencies required — image is rendered client-side via URL.
 */
class EventQrCodeWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'eventeule_event_qr_code';
    }

    public function get_title(): string
    {
        return __('Event QR-Code', 'eventeule');
    }

    public function get_icon(): string
    {
        return 'eicon-qr-code';
    }

    public function get_categories(): array
    {
        return ['eventeule'];
    }

    public function get_keywords(): array
    {
        return ['event', 'qr', 'qrcode', 'teilen', 'share', 'eventeule'];
    }

    protected function register_controls(): void
    {
        // ── Content ──────────────────────────────────────────────────────
        $this->start_controls_section('section_content', [
            'label' => __('QR-Code', 'eventeule'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('qr_size', [
            'label'   => __('Größe (px)', 'eventeule'),
            'type'    => Controls_Manager::NUMBER,
            'default' => 200,
            'min'     => 64,
            'max'     => 600,
            'step'    => 8,
        ]);

        $this->add_control('qr_foreground', [
            'label'   => __('Vordergrundfarbe', 'eventeule'),
            'type'    => Controls_Manager::COLOR,
            'default' => '#000000',
        ]);

        $this->add_control('qr_background', [
            'label'   => __('Hintergrundfarbe', 'eventeule'),
            'type'    => Controls_Manager::COLOR,
            'default' => '#ffffff',
        ]);

        $this->add_responsive_control('qr_align', [
            'label'     => __('Ausrichtung', 'eventeule'),
            'type'      => Controls_Manager::CHOOSE,
            'options'   => [
                'left'   => ['title' => __('Links', 'eventeule'),  'icon' => 'eicon-text-align-left'],
                'center' => ['title' => __('Mitte', 'eventeule'),  'icon' => 'eicon-text-align-center'],
                'right'  => ['title' => __('Rechts', 'eventeule'), 'icon' => 'eicon-text-align-right'],
            ],
            'default'   => 'left',
            'selectors' => [
                '{{WRAPPER}} .ee-qr-wrap' => 'text-align: {{VALUE}};',
            ],
        ]);

        $this->add_control('show_url_text', [
            'label'        => __('URL-Text anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'no',
            'separator'    => 'before',
        ]);

        $this->add_control('url_label', [
            'label'     => __('Beschriftung', 'eventeule'),
            'type'      => Controls_Manager::TEXT,
            'default'   => __('Direktlink:', 'eventeule'),
            'condition' => ['show_url_text' => 'yes'],
        ]);

        $this->add_control('show_download', [
            'label'        => __('Download-Link anzeigen', 'eventeule'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'no',
            'separator'    => 'before',
        ]);

        $this->add_control('download_text', [
            'label'     => __('Download-Text', 'eventeule'),
            'type'      => Controls_Manager::TEXT,
            'default'   => __('QR-Code herunterladen', 'eventeule'),
            'condition' => ['show_download' => 'yes'],
        ]);

        $this->end_controls_section();

        // ── Style: Bild ───────────────────────────────────────────────────
        $this->start_controls_section('section_style_image', [
            'label' => __('QR-Code Bild', 'eventeule'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('qr_border_radius', [
            'label'      => __('Eckenradius', 'eventeule'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => [
                '{{WRAPPER}} .ee-qr-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('qr_padding', [
            'label'      => __('Innenabstand (weißer Rand um den Code)', 'eventeule'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'selectors'  => [
                '{{WRAPPER}} .ee-qr-img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('qr_margin', [
            'label'      => __('Außenabstand', 'eventeule'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'selectors'  => [
                '{{WRAPPER}} .ee-qr-img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();

        // ── Style: URL-Text ───────────────────────────────────────────────
        $this->start_controls_section('section_style_url', [
            'label'     => __('URL-Text', 'eventeule'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['show_url_text' => 'yes'],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'url_typography',
                'selector' => '{{WRAPPER}} .ee-qr-url',
            ]
        );

        $this->add_control('url_color', [
            'label'     => __('Textfarbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ee-qr-url' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('url_link_color', [
            'label'     => __('Link-Farbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ee-qr-url a' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('url_margin', [
            'label'      => __('Abstand', 'eventeule'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'selectors'  => [
                '{{WRAPPER}} .ee-qr-url' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();

        // ── Style: Download-Link ──────────────────────────────────────────
        $this->start_controls_section('section_style_download', [
            'label'     => __('Download-Link', 'eventeule'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['show_download' => 'yes'],
        ]);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'dl_typography',
                'selector' => '{{WRAPPER}} .ee-qr-download a',
            ]
        );

        $this->add_control('dl_color', [
            'label'     => __('Link-Farbe', 'eventeule'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ee-qr-download a' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('dl_margin', [
            'label'      => __('Abstand', 'eventeule'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'selectors'  => [
                '{{WRAPPER}} .ee-qr-download' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    /**
     * Converts a hex color (#rrggbb or #rgb) to the R-G-B format required by qrserver.com.
     */
    private function hex_to_qr_color(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = str_repeat($hex[0], 2) . str_repeat($hex[1], 2) . str_repeat($hex[2], 2);
        }
        if (strlen($hex) !== 6) {
            return '0-0-0';
        }

        return hexdec(substr($hex, 0, 2)) . '-'
             . hexdec(substr($hex, 2, 2)) . '-'
             . hexdec(substr($hex, 4, 2));
    }

    /**
     * Builds the qrserver.com URL for the given data.
     */
    private function build_qr_url(string $data, int $size, string $fg_hex, string $bg_hex): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
            'size'    => $size . 'x' . $size,
            'data'    => $data,
            'color'   => $this->hex_to_qr_color($fg_hex),
            'bgcolor' => $this->hex_to_qr_color($bg_hex),
            'qzone'   => 1,
            'format'  => 'png',
        ]);
    }

    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        $event_id = get_the_ID();

        $is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();

        if (!$event_id || get_post_type($event_id) !== EventPostType::POST_TYPE) {
            if ($is_edit) {
                echo '<div class="ee-qr-wrap ee-qr-wrap--placeholder">';
                echo '<img class="ee-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode(home_url('/')) . '&qzone=1&format=png" width="200" height="200" alt="QR-Code Vorschau" />';
                echo '<p style="color:#999;font-size:12px;margin:6px 0 0;">' . esc_html__('Vorschau – kein Event-Kontext', 'eventeule') . '</p>';
                echo '</div>';
            }
            return;
        }

        $permalink = get_permalink($event_id);
        if (!$permalink) {
            return;
        }

        $size     = max(64, min(600, (int) ($settings['qr_size'] ?? 200)));
        $fg       = $settings['qr_foreground'] ?? '#000000';
        $bg       = $settings['qr_background'] ?? '#ffffff';
        $show_url = ($settings['show_url_text'] ?? 'no') === 'yes';
        $show_dl  = ($settings['show_download']  ?? 'no') === 'yes';

        $qr_url = $this->build_qr_url($permalink, $size, $fg, $bg);

        echo '<div class="ee-qr-wrap">';

        echo '<img'
            . ' class="ee-qr-img"'
            . ' src="' . esc_url($qr_url) . '"'
            . ' width="' . esc_attr($size) . '"'
            . ' height="' . esc_attr($size) . '"'
            . ' alt="' . esc_attr(get_the_title($event_id) . ' – QR-Code') . '"'
            . ' loading="lazy"'
            . ' />';

        if ($show_url) {
            $label = $settings['url_label'] ?? '';
            echo '<p class="ee-qr-url">';
            if ($label !== '') {
                echo '<strong>' . esc_html($label) . '</strong> ';
            }
            echo '<a href="' . esc_url($permalink) . '">' . esc_html($permalink) . '</a>';
            echo '</p>';
        }

        if ($show_dl) {
            $dl_text = $settings['download_text'] ?? __('QR-Code herunterladen', 'eventeule');
            echo '<p class="ee-qr-download">';
            echo '<a href="' . esc_url($qr_url) . '" target="_blank" rel="noopener noreferrer">';
            echo esc_html($dl_text);
            echo '</a></p>';
        }

        echo '</div>';
    }

    protected function content_template(): void
    {
        ?>
        <div class="ee-qr-wrap">
            <img class="ee-qr-img"
                 src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https%3A%2F%2Fexample.com%2Fevent%2F&color=0-0-0&bgcolor=255-255-255&qzone=1&format=png"
                 width="200" height="200" alt="QR-Code Vorschau" loading="lazy" />
            <# if (settings.show_url_text === 'yes') { #>
                <p class="ee-qr-url">
                    <# if (settings.url_label) { #><strong>{{ settings.url_label }}</strong> <# } #>
                    <a href="#">https://example.com/event/veranstaltung/</a>
                </p>
            <# } #>
            <# if (settings.show_download === 'yes') { #>
                <p class="ee-qr-download">
                    <a href="#">{{ settings.download_text }}</a>
                </p>
            <# } #>
        </div>
        <?php
    }
}
