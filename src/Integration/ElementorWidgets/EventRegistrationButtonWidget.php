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

        // ── Button Style ─────────────────────────────────────────────────────
        $this->start_controls_section('section_button_style', [
            'label' => __('Button-Stil', 'eventeule'),
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
            [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .ee-reg-popup-trigger',
            ]
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
        $this->add_control('button_border_color', [
            'label'     => __('Rahmenfarbe', 'eventeule'),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .ee-reg-popup-trigger' => 'border-color: {{VALUE}};'],
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

        $this->add_control('button_border_radius', [
            'label'      => __('Eckenradius', 'eventeule'),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'range'      => ['px' => ['min' => 0, 'max' => 60]],
            'default'    => ['unit' => 'px', 'size' => 8],
            'selectors'  => ['{{WRAPPER}} .ee-reg-popup-trigger' => 'border-radius: {{SIZE}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .ee-reg-popup-trigger',
            ]
        );

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
