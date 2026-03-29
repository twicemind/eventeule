<?php

namespace EventEule\Admin;

use EventEule\Domain\EventPostType;

class EventMetaBoxes
{
    private const NONCE_ACTION = 'eventeule_save_event_meta';
    private const NONCE_NAME   = 'eventeule_event_meta_nonce';

    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes']);
    }

    public function add_meta_boxes(): void
    {
        add_meta_box(
            'eventeule_event_details',
            __('Event Details', 'eventeule'),
            [$this, 'render_meta_box'],
            EventPostType::POST_TYPE,
            'normal',
            'high'
        );
    }

    public function render_meta_box(\WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        $startDate = get_post_meta($post->ID, '_eventeule_start_date', true);
        $endDate   = get_post_meta($post->ID, '_eventeule_end_date', true);
        $startTime = get_post_meta($post->ID, '_eventeule_start_time', true);
        $endTime   = get_post_meta($post->ID, '_eventeule_end_time', true);
        $location  = get_post_meta($post->ID, '_eventeule_location', true);
        $url       = get_post_meta($post->ID, '_eventeule_registration_url', true);
        $note      = get_post_meta($post->ID, '_eventeule_note', true);
        $featured  = get_post_meta($post->ID, '_eventeule_featured', true);
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="eventeule_start_date"><?php esc_html_e('Start date', 'eventeule'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="eventeule_start_date" name="eventeule_start_date" value="<?php echo esc_attr($startDate); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="eventeule_end_date"><?php esc_html_e('End date', 'eventeule'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="eventeule_end_date" name="eventeule_end_date" value="<?php echo esc_attr($endDate); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="eventeule_start_time"><?php esc_html_e('Start time', 'eventeule'); ?></label>
                    </th>
                    <td>
                        <input type="time" id="eventeule_start_time" name="eventeule_start_time" value="<?php echo esc_attr($startTime); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="eventeule_end_time"><?php esc_html_e('End time', 'eventeule'); ?></label>
                    </th>
                    <td>
                        <input type="time" id="eventeule_end_time" name="eventeule_end_time" value="<?php echo esc_attr($endTime); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="eventeule_location"><?php esc_html_e('Location', 'eventeule'); ?></label>
                    </th>
                    <td>
                        <input type="text" class="regular-text" id="eventeule_location" name="eventeule_location" value="<?php echo esc_attr($location); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="eventeule_registration_url"><?php esc_html_e('Registration URL', 'eventeule'); ?></label>
                    </th>
                    <td>
                        <input type="url" class="regular-text" id="eventeule_registration_url" name="eventeule_registration_url" value="<?php echo esc_attr($url); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="eventeule_note"><?php esc_html_e('Note / Additional information', 'eventeule'); ?></label>
                    </th>
                    <td>
                        <textarea
                            id="eventeule_note"
                            name="eventeule_note"
                            rows="3"
                            style="width: 100%;"
                        ><?php echo esc_textarea($note); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="eventeule_featured"><?php esc_html_e('Featured', 'eventeule'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input
                                type="checkbox"
                                id="eventeule_featured"
                                name="eventeule_featured"
                                value="1"
                                <?php checked($featured, '1'); ?>
                            />
                            <?php esc_html_e('Highlight this event', 'eventeule'); ?>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    public function save_meta_boxes(int $postId): void
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

        $this->save_text_meta($postId, '_eventeule_start_date', 'eventeule_start_date');
        $this->save_text_meta($postId, '_eventeule_end_date', 'eventeule_end_date');
        $this->save_text_meta($postId, '_eventeule_start_time', 'eventeule_start_time');
        $this->save_text_meta($postId, '_eventeule_end_time', 'eventeule_end_time');
        $this->save_text_meta($postId, '_eventeule_location', 'eventeule_location');
        $this->save_url_meta($postId, '_eventeule_registration_url', 'eventeule_registration_url');
        $this->save_textarea_meta($postId, '_eventeule_note', 'eventeule_note');
        $this->save_checkbox_meta($postId, '_eventeule_featured', 'eventeule_featured');
    }

    private function save_text_meta(int $postId, string $metaKey, string $fieldName): void
    {
        if (!isset($_POST[$fieldName])) {
            delete_post_meta($postId, $metaKey);
            return;
        }

        $value = sanitize_text_field(wp_unslash($_POST[$fieldName]));

        if ($value === '') {
            delete_post_meta($postId, $metaKey);
            return;
        }

        update_post_meta($postId, $metaKey, $value);
    }

    private function save_textarea_meta(int $postId, string $metaKey, string $fieldName): void
    {
        if (!isset($_POST[$fieldName])) {
            delete_post_meta($postId, $metaKey);
            return;
        }

        $value = sanitize_textarea_field(wp_unslash($_POST[$fieldName]));

        if ($value === '') {
            delete_post_meta($postId, $metaKey);
            return;
        }

        update_post_meta($postId, $metaKey, $value);
    }

    private function save_url_meta(int $postId, string $metaKey, string $fieldName): void
    {
        if (!isset($_POST[$fieldName])) {
            delete_post_meta($postId, $metaKey);
            return;
        }

        $value = esc_url_raw(wp_unslash($_POST[$fieldName]));

        if ($value === '') {
            delete_post_meta($postId, $metaKey);
            return;
        }

        update_post_meta($postId, $metaKey, $value);
    }

    private function save_checkbox_meta(int $postId, string $metaKey, string $fieldName): void
    {
        $value = isset($_POST[$fieldName]) ? '1' : '0';
        update_post_meta($postId, $metaKey, $value);
    }
}