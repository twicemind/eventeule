<?php

namespace EventEule\Admin;

use EventEule\Domain\EventPostType;
use EventEule\Registration\RegistrationRepository;

class EventRegistrationMetaBox
{
    private const NONCE_ACTION = 'eventeule_save_registration_meta';
    private const NONCE_NAME   = 'eventeule_registration_meta_nonce';

    private const ALL_FIELDS = [
        'firstname'    => 'First name',
        'lastname'     => 'Last name',
        'email'        => 'E-Mail',
        'phone'        => 'Phone',
        'participants' => 'Number of participants',
        'message'      => 'Message / Note',
    ];

    private RegistrationRepository $repository;

    public function __construct(RegistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_box']);
    }

    public function add_meta_boxes(): void
    {
        add_meta_box(
            'eventeule_event_registration',
            __('Event Registration', 'eventeule'),
            [$this, 'render_meta_box'],
            EventPostType::POST_TYPE,
            'normal',
            'high'
        );
    }

    public function render_meta_box(\WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        $enabled      = get_post_meta($post->ID, '_eventeule_reg_enabled', true) === '1';
        $maxReg       = (int) get_post_meta($post->ID, '_eventeule_reg_max', true);
        $thankyou     = (string) get_post_meta($post->ID, '_eventeule_reg_thankyou', true);
        $adminEmail     = (string) get_post_meta($post->ID, '_eventeule_reg_admin_email', true);
        $regOpenFrom    = (string) get_post_meta($post->ID, '_eventeule_reg_open_from', true);
        $regOpenUntil   = (string) get_post_meta($post->ID, '_eventeule_reg_open_until', true);
        $regClosedText  = (string) get_post_meta($post->ID, '_eventeule_reg_closed_text', true);
        $fieldsRaw      = (string) get_post_meta($post->ID, '_eventeule_reg_fields', true);
        $requiredRaw  = (string) get_post_meta($post->ID, '_eventeule_reg_required', true);

        // Defaults
        $enabledFields  = $fieldsRaw !== ''
            ? array_map('trim', explode(',', $fieldsRaw))
            : ['firstname', 'email'];
        $requiredFields = $requiredRaw !== ''
            ? array_map('trim', explode(',', $requiredRaw))
            : ['firstname', 'email'];

        $registrationCount = $this->repository->count_by_event($post->ID);
        $adminListUrl = admin_url('admin.php?page=eventeule-registrations&event_id=' . $post->ID);
        ?>
        <style>
            .eventeule-reg-box { padding: 4px 0; }
            .eventeule-reg-box .reg-toggle { font-weight: 600; margin-bottom: 12px; }
            .eventeule-reg-fields-section { margin-top: 16px; padding-top: 16px; border-top: 1px solid #ddd; }
            .eventeule-reg-fields-table { border-collapse: collapse; width: 100%; max-width: 500px; }
            .eventeule-reg-fields-table th { text-align: left; padding: 6px 12px 6px 0; font-weight: 600; color: #555; font-size: 12px; text-transform: uppercase; }
            .eventeule-reg-fields-table td { padding: 5px 12px 5px 0; }
            .eventeule-reg-fields-table tr td:first-child { width: 160px; }
            .eventeule-reg-counter { display: inline-block; background: #e7f3ff; border: 1px solid #b3d4ff; border-radius: 4px; padding: 4px 10px; font-size: 13px; color: #0073aa; margin-bottom: 10px; }
        </style>
        <div class="eventeule-reg-box">
            <p class="reg-toggle">
                <label>
                    <input type="checkbox" name="eventeule_reg_enabled" value="1" <?php checked($enabled); ?> id="eventeule_reg_enabled_toggle" />
                    <?php esc_html_e('Enable registration for this event', 'eventeule'); ?>
                </label>
            </p>

            <?php if ($enabled && $registrationCount > 0): ?>
                <p>
                    <span class="eventeule-reg-counter">
                        <span class="dashicons dashicons-groups" style="vertical-align: middle; font-size: 1em; height: 1em; width: 1em;"></span>
                        <?php printf(
                            /* translators: %d = number of registrations */
                            esc_html(_n('%d spot booked', '%d spots booked', $registrationCount, 'eventeule')),
                            esc_html($registrationCount)
                        ); ?>
                    </span>
                    &nbsp;
                    <a href="<?php echo esc_url($adminListUrl); ?>" class="button button-small">
                        <?php esc_html_e('View Registrations', 'eventeule'); ?>
                    </a>
                </p>
            <?php endif; ?>

            <div id="eventeule_reg_settings" <?php echo $enabled ? '' : 'style="display:none;"'; ?>>
                <table class="form-table" role="presentation" style="margin-top: 0;">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="eventeule_reg_max"><?php esc_html_e('Max. registrations', 'eventeule'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="eventeule_reg_max" name="eventeule_reg_max"
                                       value="<?php echo esc_attr($maxReg ?: ''); ?>"
                                       min="0" step="1" style="width: 80px;" />
                                <p class="description"><?php esc_html_e('0 or empty = unlimited', 'eventeule'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="eventeule_reg_thankyou"><?php esc_html_e('Thank you text', 'eventeule'); ?></label>
                            </th>
                            <td>
                                <textarea id="eventeule_reg_thankyou" name="eventeule_reg_thankyou"
                                          rows="3" class="large-text"
                                          placeholder="<?php esc_attr_e('Thank you for your registration! You will receive a confirmation email.', 'eventeule'); ?>"
                                ><?php echo esc_textarea($thankyou); ?></textarea>
                                <p class="description"><?php esc_html_e('Displayed to the user after successful registration.', 'eventeule'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="eventeule_reg_admin_email"><?php esc_html_e('Notification email', 'eventeule'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="eventeule_reg_admin_email" name="eventeule_reg_admin_email"
                                       value="<?php echo esc_attr($adminEmail); ?>"
                                       class="regular-text"
                                       placeholder="<?php echo esc_attr(get_option('admin_email')); ?>" />
                                <p class="description"><?php esc_html_e('Email address to notify on new registrations. Defaults to admin email.', 'eventeule'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="eventeule_reg_open_from"><?php esc_html_e('Registration open from', 'eventeule'); ?></label>
                            </th>
                            <td>
                                <input type="datetime-local" id="eventeule_reg_open_from" name="eventeule_reg_open_from"
                                       value="<?php echo esc_attr($regOpenFrom); ?>" />
                                <p class="description"><?php esc_html_e('Date and time from which registration opens. Leave empty for no restriction.', 'eventeule'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="eventeule_reg_open_until"><?php esc_html_e('Registration open until', 'eventeule'); ?></label>
                            </th>
                            <td>
                                <input type="datetime-local" id="eventeule_reg_open_until" name="eventeule_reg_open_until"
                                       value="<?php echo esc_attr($regOpenUntil); ?>" />
                                <p class="description"><?php esc_html_e('Date and time until which registration is possible. Leave empty for no end date.', 'eventeule'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="eventeule_reg_closed_text"><?php esc_html_e('Text when registration is closed', 'eventeule'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="eventeule_reg_closed_text" name="eventeule_reg_closed_text"
                                       value="<?php echo esc_attr($regClosedText); ?>"
                                       class="regular-text"
                                       placeholder="<?php esc_attr_e('Registration currently not available.', 'eventeule'); ?>" />
                                <p class="description"><?php esc_html_e('Shown instead of the button when outside the registration window.', 'eventeule'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Registration fields', 'eventeule'); ?></th>
                            <td>
                                <table class="eventeule-reg-fields-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Field', 'eventeule'); ?></th>
                                            <th><?php esc_html_e('Show', 'eventeule'); ?></th>
                                            <th><?php esc_html_e('Required', 'eventeule'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (self::ALL_FIELDS as $key => $label): ?>
                                        <tr>
                                            <td><?php echo esc_html(__($label, 'eventeule')); ?></td>
                                            <td>
                                                <input type="checkbox"
                                                       name="eventeule_reg_field_show[]"
                                                       value="<?php echo esc_attr($key); ?>"
                                                       id="reg_show_<?php echo esc_attr($key); ?>"
                                                       <?php checked(in_array($key, $enabledFields, true)); ?> />
                                            </td>
                                            <td>
                                                <input type="checkbox"
                                                       name="eventeule_reg_field_required[]"
                                                       value="<?php echo esc_attr($key); ?>"
                                                       id="reg_req_<?php echo esc_attr($key); ?>"
                                                       <?php checked(in_array($key, $requiredFields, true)); ?> />
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <p class="description" style="margin-top: 8px;">
                                    <?php esc_html_e('"Required" only applies if the field is also shown.', 'eventeule'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <script>
        (function () {
            const toggle = document.getElementById('eventeule_reg_enabled_toggle');
            const settings = document.getElementById('eventeule_reg_settings');
            if (toggle && settings) {
                toggle.addEventListener('change', function () {
                    settings.style.display = this.checked ? '' : 'none';
                });
            }
        })();
        </script>
        <?php
    }

    public function save_meta_box(int $postId): void
    {
        if (!isset($_POST[self::NONCE_NAME])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (get_post_type($postId) !== EventPostType::POST_TYPE) {
            return;
        }

        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Enabled
        $enabled = isset($_POST['eventeule_reg_enabled']) ? '1' : '0';
        update_post_meta($postId, '_eventeule_reg_enabled', $enabled);

        // Max registrations
        $max = isset($_POST['eventeule_reg_max']) ? max(0, (int) $_POST['eventeule_reg_max']) : 0;
        update_post_meta($postId, '_eventeule_reg_max', $max);

        // Thank you text
        $thankyou = isset($_POST['eventeule_reg_thankyou'])
            ? sanitize_textarea_field(wp_unslash($_POST['eventeule_reg_thankyou']))
            : '';
        update_post_meta($postId, '_eventeule_reg_thankyou', $thankyou);

        // Admin email
        $adminEmail = isset($_POST['eventeule_reg_admin_email'])
            ? sanitize_email(wp_unslash($_POST['eventeule_reg_admin_email']))
            : '';
        update_post_meta($postId, '_eventeule_reg_admin_email', $adminEmail);

        // Registration window
        $regOpenFrom = isset($_POST['eventeule_reg_open_from'])
            ? sanitize_text_field(wp_unslash($_POST['eventeule_reg_open_from']))
            : '';
        update_post_meta($postId, '_eventeule_reg_open_from', $regOpenFrom);

        $regOpenUntil = isset($_POST['eventeule_reg_open_until'])
            ? sanitize_text_field(wp_unslash($_POST['eventeule_reg_open_until']))
            : '';
        update_post_meta($postId, '_eventeule_reg_open_until', $regOpenUntil);

        $regClosedText = isset($_POST['eventeule_reg_closed_text'])
            ? sanitize_text_field(wp_unslash($_POST['eventeule_reg_closed_text']))
            : '';
        update_post_meta($postId, '_eventeule_reg_closed_text', $regClosedText);

        // Shown fields
        $allowedFieldKeys = array_keys(self::ALL_FIELDS);
        $shownFields = [];
        if (isset($_POST['eventeule_reg_field_show']) && is_array($_POST['eventeule_reg_field_show'])) {
            foreach (array_map('sanitize_key', (array) $_POST['eventeule_reg_field_show']) as $field) {
                if (in_array($field, $allowedFieldKeys, true)) {
                    $shownFields[] = $field;
                }
            }
        }
        update_post_meta($postId, '_eventeule_reg_fields', implode(',', $shownFields));

        // Required fields (only valid if also shown)
        $requiredFields = [];
        if (isset($_POST['eventeule_reg_field_required']) && is_array($_POST['eventeule_reg_field_required'])) {
            foreach (array_map('sanitize_key', (array) $_POST['eventeule_reg_field_required']) as $field) {
                if (in_array($field, $allowedFieldKeys, true) && in_array($field, $shownFields, true)) {
                    $requiredFields[] = $field;
                }
            }
        }
        update_post_meta($postId, '_eventeule_reg_required', implode(',', $requiredFields));
    }
}
