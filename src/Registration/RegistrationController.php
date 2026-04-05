<?php

namespace EventEule\Registration;

class RegistrationController
{
    private RegistrationRepository $repository;

    public function __construct(RegistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(): void
    {
        add_action('wp_ajax_eventeule_register', [$this, 'handle_registration']);
        add_action('wp_ajax_nopriv_eventeule_register', [$this, 'handle_registration']);
        add_filter('the_content', [$this, 'append_registration_form'], 20);
        add_shortcode('eventeule_registration', [$this, 'render_registration_shortcode']);
    }

    public function handle_registration(): void
    {
        if (!check_ajax_referer('eventeule_register', 'nonce', false)) {
            wp_send_json_error(['message' => __('Security check failed. Please reload the page and try again.', 'eventeule')]);
        }

        $eventId = (int) ($_POST['event_id'] ?? 0);
        if ($eventId <= 0) {
            wp_send_json_error(['message' => __('Invalid event.', 'eventeule')]);
        }

        if (get_post_meta($eventId, '_eventeule_reg_enabled', true) !== '1') {
            wp_send_json_error(['message' => __('Registration is not available for this event.', 'eventeule')]);
        }

        $enabledFields  = $this->parse_fields('_eventeule_reg_fields', $eventId, ['firstname', 'email']);
        $requiredFields = $this->parse_fields('_eventeule_reg_required', $eventId, ['firstname', 'email']);

        // Collect and validate form data
        $formData = [];
        foreach (['firstname', 'lastname', 'email', 'phone', 'participants', 'message'] as $field) {
            if (!in_array($field, $enabledFields, true)) {
                continue;
            }

            if ($field === 'message') {
                $value = sanitize_textarea_field(wp_unslash($_POST[$field] ?? ''));
            } elseif ($field === 'email') {
                $value = sanitize_email(wp_unslash($_POST[$field] ?? ''));
            } elseif ($field === 'participants') {
                $value = max(1, (int) ($_POST[$field] ?? 1));
            } else {
                $value = sanitize_text_field(wp_unslash($_POST[$field] ?? ''));
            }

            if (in_array($field, $requiredFields, true) && ($value === '' || $value === 0)) {
                $labels = [
                    'firstname'    => __('First name', 'eventeule'),
                    'lastname'     => __('Last name', 'eventeule'),
                    'email'        => __('E-Mail', 'eventeule'),
                    'phone'        => __('Phone', 'eventeule'),
                    'participants' => __('Number of participants', 'eventeule'),
                    'message'      => __('Message', 'eventeule'),
                ];
                wp_send_json_error([
                    'message' => sprintf(
                        /* translators: %s = field label */
                        __('The field "%s" is required.', 'eventeule'),
                        $labels[$field] ?? $field
                    ),
                    'field' => $field,
                ]);
            }

            $formData[$field] = $value;
        }

        // Validate email format when provided
        if (!empty($formData['email']) && !is_email($formData['email'])) {
            wp_send_json_error(['message' => __('Please enter a valid email address.', 'eventeule'), 'field' => 'email']);
        }

        // Check max registrations
        $maxReg = (int) get_post_meta($eventId, '_eventeule_reg_max', true);
        if ($maxReg > 0) {
            $currentCount = $this->repository->count_by_event($eventId);
            $requested    = (int) ($formData['participants'] ?? 1);
            $available    = max(0, $maxReg - $currentCount);

            if ($available === 0) {
                wp_send_json_error(['message' => __('Sorry, this event is fully booked.', 'eventeule')]);
            }

            if ($requested > $available) {
                wp_send_json_error([
                    'message' => sprintf(
                        /* translators: %d = available spots */
                        _n(
                            'Only %d spot remaining. Please reduce the number of participants.',
                            'Only %d spots remaining. Please reduce the number of participants.',
                            $available,
                            'eventeule'
                        ),
                        $available
                    ),
                ]);
            }
        }

        // Prevent duplicate registration by email for the same event
        if (!empty($formData['email']) && $this->repository->email_exists_for_event($eventId, $formData['email'])) {
            wp_send_json_error([
                'message' => __('This email address is already registered for this event.', 'eventeule'),
                'field'   => 'email',
            ]);
        }

        $formData['event_id'] = $eventId;
        $insertId = $this->repository->insert($formData);

        if ($insertId === false) {
            wp_send_json_error(['message' => __('Registration failed. Please try again.', 'eventeule')]);
        }

        $this->send_confirmation_email($eventId, $formData);
        $this->send_admin_notification($eventId, $formData);

        $thankyouText = (string) get_post_meta($eventId, '_eventeule_reg_thankyou', true);
        if ($thankyouText === '') {
            $thankyouText = __('Thank you for your registration! You will receive a confirmation email shortly.', 'eventeule');
        }

        // Return updated available spots count
        $newCount = $this->repository->count_by_event($eventId);
        $remaining = $maxReg > 0 ? max(0, $maxReg - $newCount) : -1;

        wp_send_json_success([
            'message'   => $thankyouText,
            'remaining' => $remaining,
        ]);
    }

    /**
     * Automatically append the registration form to single event pages.
     */
    public function append_registration_form(string $content): string
    {
        if (!is_singular('eventeule_event') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        global $post;
        if (!$post || get_post_meta($post->ID, '_eventeule_reg_enabled', true) !== '1') {
            return $content;
        }

        // If the page is built with Elementor, the user should use the Popup-Widget instead.
        // Auto-appending would duplicate the form below the Elementor content.
        if (get_post_meta($post->ID, '_elementor_edit_mode', true) === 'builder') {
            return $content;
        }

        return $content . $this->render_registration_form((int) $post->ID);
    }

    /**
     * @param array<string, string> $atts
     */
    public function render_registration_shortcode(array $atts = []): string
    {
        $atts    = shortcode_atts(['event_id' => 0], $atts, 'eventeule_registration');
        $eventId = (int) $atts['event_id'];

        if ($eventId <= 0) {
            global $post;
            $eventId = $post ? (int) $post->ID : 0;
        }

        if ($eventId <= 0 || get_post_meta($eventId, '_eventeule_reg_enabled', true) !== '1') {
            return '';
        }

        return $this->render_registration_form($eventId);
    }

    private function render_registration_form(int $eventId): string
    {
        $enabledFields  = $this->parse_fields('_eventeule_reg_fields', $eventId, ['firstname', 'email']);
        $requiredFields = $this->parse_fields('_eventeule_reg_required', $eventId, ['firstname', 'email']);
        $maxReg         = (int) get_post_meta($eventId, '_eventeule_reg_max', true);
        $currentCount   = $maxReg > 0 ? $this->repository->count_by_event($eventId) : 0;
        $available      = $maxReg > 0 ? max(0, $maxReg - $currentCount) : -1; // -1 means unlimited
        $nonce          = wp_create_nonce('eventeule_register');
        $ajaxUrl        = admin_url('admin-ajax.php');

        ob_start();
        $template = EVENTEULE_PATH . 'templates/public/registration-form.php';
        if (file_exists($template)) {
            include $template;
        }
        return (string) ob_get_clean();
    }

    /**
     * @param string[] $defaults
     * @return string[]
     */
    private function parse_fields(string $metaKey, int $eventId, array $defaults): array
    {
        $raw = (string) get_post_meta($eventId, $metaKey, true);
        if ($raw === '') {
            return $defaults;
        }
        return array_filter(array_map('trim', explode(',', $raw)));
    }

    /**
     * @param array<string, mixed> $formData
     */
    private function send_confirmation_email(int $eventId, array $formData): void
    {
        if (empty($formData['email'])) {
            return;
        }

        $eventTitle = (string) get_the_title($eventId);
        $startDate  = (string) get_post_meta($eventId, '_eventeule_start_date', true);
        $startTime  = (string) get_post_meta($eventId, '_eventeule_start_time', true);
        $location   = (string) get_post_meta($eventId, '_eventeule_location', true);
        $siteName   = get_bloginfo('name');

        $name = trim(($formData['firstname'] ?? '') . ' ' . ($formData['lastname'] ?? ''));

        $subject = sprintf(
            /* translators: %s = event title */
            __('Registration confirmed: %s', 'eventeule'),
            $eventTitle
        );

        $body = '';
        if ($name !== '') {
            $body .= sprintf(__('Dear %s,', 'eventeule'), $name) . "\n\n";
        }
        $body .= sprintf(__('Your registration for "%s" has been confirmed.', 'eventeule'), $eventTitle) . "\n\n";

        if ($startDate !== '') {
            $body .= __('Date:', 'eventeule') . ' ' . date_i18n(get_option('date_format'), strtotime($startDate));
            if ($startTime !== '') {
                $body .= ', ' . $startTime;
            }
            $body .= "\n";
        }

        if ($location !== '') {
            $body .= __('Location:', 'eventeule') . ' ' . $location . "\n";
        }

        $participants = (int) ($formData['participants'] ?? 1);
        if ($participants > 1) {
            $body .= sprintf(__('Number of participants: %d', 'eventeule'), $participants) . "\n";
        }

        $body .= "\n" . sprintf(__('Best regards,\n%s', 'eventeule'), $siteName);

        $fromEmail = get_option('admin_email');
        wp_mail(
            (string) $formData['email'],
            $subject,
            $body,
            ['From: ' . $siteName . ' <' . $fromEmail . '>']
        );
    }

    /**
     * @param array<string, mixed> $formData
     */
    private function send_admin_notification(int $eventId, array $formData): void
    {
        $adminEmail = (string) get_post_meta($eventId, '_eventeule_reg_admin_email', true);
        if ($adminEmail === '') {
            $adminEmail = get_option('admin_email');
        }

        $eventTitle = (string) get_the_title($eventId);
        $subject    = sprintf(__('New registration: %s', 'eventeule'), $eventTitle);

        $body = sprintf(__('New registration for the event "%s":', 'eventeule'), $eventTitle) . "\n\n";

        $name = trim(($formData['firstname'] ?? '') . ' ' . ($formData['lastname'] ?? ''));
        if ($name !== '') {
            $body .= __('Name:', 'eventeule') . ' ' . $name . "\n";
        }
        if (!empty($formData['email'])) {
            $body .= __('Email:', 'eventeule') . ' ' . (string) $formData['email'] . "\n";
        }
        if (!empty($formData['phone'])) {
            $body .= __('Phone:', 'eventeule') . ' ' . (string) $formData['phone'] . "\n";
        }
        if (!empty($formData['participants'])) {
            $body .= __('Participants:', 'eventeule') . ' ' . (int) $formData['participants'] . "\n";
        }
        if (!empty($formData['message'])) {
            $body .= __('Message:', 'eventeule') . ' ' . (string) $formData['message'] . "\n";
        }

        $adminUrl = admin_url('admin.php?page=eventeule-registrations&event_id=' . $eventId);
        $body .= "\n" . sprintf(__('View registrations: %s', 'eventeule'), $adminUrl);

        wp_mail($adminEmail, $subject, $body);
    }
}
