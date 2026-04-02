<?php

namespace EventEule\Admin;

use EventEule\Domain\EventPostType;
use EventEule\Registration\RegistrationRepository;

class RegistrationsAdmin
{
    private RegistrationRepository $repository;

    public function __construct(RegistrationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_post_eventeule_delete_registration', [$this, 'handle_delete']);
        add_action('admin_post_eventeule_export_registrations', [$this, 'handle_export']);
        add_action('admin_post_eventeule_reply_registration', [$this, 'handle_reply']);
        add_action('admin_post_eventeule_cancel_event', [$this, 'handle_cancel_event']);
    }

    public function register_menu(): void
    {
        // Registrations as submenu under the Events post type (Veranstaltungen)
        add_submenu_page(
            'edit.php?post_type=' . EventPostType::POST_TYPE,
            __('Anmeldungen', 'eventeule'),
            __('Anmeldungen', 'eventeule'),
            'manage_options',
            'eventeule-registrations',
            [$this, 'render_page']
        );
    }

    public function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Du hast keine Berechtigung für diese Seite.', 'eventeule'));
        }

        $eventId  = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
        $paged    = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
        $perPage  = 25;
        $offset   = ($paged - 1) * $perPage;

        if ($eventId > 0) {
            $registrations = $this->repository->get_all_by_event($eventId, $perPage, $offset);
            $total         = $this->repository->count_all_by_event($eventId);
            $eventTitle    = get_the_title($eventId);
        } else {
            $registrations = $this->repository->get_all($perPage, $offset);
            $total         = $this->repository->count_all();
            $eventTitle    = '';
        }

        $totalPages = (int) ceil($total / $perPage);

        // Get all events with registration enabled for filter dropdown
        $eventsQuery = new \WP_Query([
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => ['publish', 'draft'],
            'posts_per_page' => -1,
            'meta_key'       => '_eventeule_reg_enabled',
            'meta_value'     => '1',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ]);
        $registrationEvents = $eventsQuery->posts;

        // Flash messages
        $notice = '';
        $noticeType = 'success';
        if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
            $notice = __('Anmeldung wurde gelöscht.', 'eventeule');
        } elseif (isset($_GET['replied']) && $_GET['replied'] === '1') {
            $notice = __('Antwort wurde erfolgreich versendet.', 'eventeule');
        } elseif (isset($_GET['cancelled']) && $_GET['cancelled'] === '1') {
            $notice = __('Veranstaltung wurde abgesagt. Die Teilnehmer/-innen wurden per E-Mail informiert.', 'eventeule');
        } elseif (isset($_GET['reply_error'])) {
            $notice = __('Fehler beim Senden der Antwort. Bitte prüfe die E-Mail-Adresse.', 'eventeule');
            $noticeType = 'error';
        }

        include EVENTEULE_PATH . 'templates/admin/registrations.php';
    }

    public function handle_delete(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Du hast keine Berechtigung für diese Aktion.', 'eventeule'));
        }

        check_admin_referer('eventeule_delete_registration', 'eventeule_nonce');

        $id      = (int) ($_POST['registration_id'] ?? 0);
        $eventId = (int) ($_POST['event_id'] ?? 0);

        if ($id > 0) {
            $this->repository->delete($id);
        }

        $redirect = admin_url('admin.php?page=eventeule-registrations&deleted=1');
        if ($eventId > 0) {
            $redirect = add_query_arg('event_id', $eventId, $redirect);
        }

        wp_safe_redirect($redirect);
        exit;
    }

    public function handle_reply(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Du hast keine Berechtigung für diese Aktion.', 'eventeule'));
        }

        check_admin_referer('eventeule_reply_registration', 'eventeule_nonce');

        $registrationId = (int) ($_POST['registration_id'] ?? 0);
        $eventId        = (int) ($_POST['event_id'] ?? 0);
        $subject        = sanitize_text_field(wp_unslash($_POST['reply_subject'] ?? ''));
        $body           = sanitize_textarea_field(wp_unslash($_POST['reply_body'] ?? ''));

        $reg = $registrationId > 0 ? $this->repository->get_by_id($registrationId) : null;

        $redirect = admin_url('admin.php?page=eventeule-registrations');
        if ($eventId > 0) {
            $redirect = add_query_arg('event_id', $eventId, $redirect);
        }

        if (!$reg || empty($reg['email']) || empty($subject) || empty($body)) {
            wp_safe_redirect(add_query_arg('reply_error', '1', $redirect));
            exit;
        }

        $siteName  = get_bloginfo('name');
        $fromEmail = get_option('admin_email');

        $sent = wp_mail(
            $reg['email'],
            $subject,
            $body,
            ['From: ' . $siteName . ' <' . $fromEmail . '>', 'Content-Type: text/plain; charset=UTF-8']
        );

        wp_safe_redirect(add_query_arg($sent ? 'replied' : 'reply_error', '1', $redirect));
        exit;
    }

    public function handle_cancel_event(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Du hast keine Berechtigung für diese Aktion.', 'eventeule'));
        }

        check_admin_referer('eventeule_cancel_event', 'eventeule_nonce');

        $eventId = (int) ($_POST['event_id'] ?? 0);
        if ($eventId <= 0) {
            wp_safe_redirect(admin_url('admin.php?page=eventeule-registrations'));
            exit;
        }

        $cancellationText = sanitize_textarea_field(wp_unslash($_POST['cancellation_text'] ?? ''));
        $sendEmails       = isset($_POST['send_cancellation_emails']);

        // Mark event as cancelled via meta
        update_post_meta($eventId, '_eventeule_cancelled', '1');
        update_post_meta($eventId, '_eventeule_cancellation_text', $cancellationText);

        // Optionally send cancellation emails to all registrants
        if ($sendEmails) {
            $this->send_cancellation_emails($eventId, $cancellationText);
        }

        wp_safe_redirect(add_query_arg(
            ['event_id' => $eventId, 'cancelled' => '1'],
            admin_url('admin.php?page=eventeule-registrations')
        ));
        exit;
    }

    private function send_cancellation_emails(int $eventId, string $customText): void
    {
        $registrations = $this->repository->get_all_by_event($eventId, 10000, 0);
        $eventTitle    = (string) get_the_title($eventId);
        $startDate     = (string) get_post_meta($eventId, '_eventeule_start_date', true);
        $location      = (string) get_post_meta($eventId, '_eventeule_location', true);
        $siteName      = get_bloginfo('name');
        $fromEmail     = (string) get_option('admin_email');
        $adminEmail    = (string) get_post_meta($eventId, '_eventeule_reg_admin_email', true);
        if ($adminEmail === '') {
            $adminEmail = $fromEmail;
        }

        $subject = sprintf(
            /* translators: %s = event title */
            __('Absage: %s', 'eventeule'),
            $eventTitle
        );

        foreach ($registrations as $reg) {
            if (empty($reg['email'])) {
                continue;
            }

            $name = trim(($reg['firstname'] ?? '') . ' ' . ($reg['lastname'] ?? ''));

            $body = '';
            if ($name !== '') {
                $body .= sprintf(__('Liebe/r %s,', 'eventeule'), $name) . "\n\n";
            } else {
                $body .= __('Hallo,', 'eventeule') . "\n\n";
            }

            $body .= sprintf(
                __('wir müssen dir leider mitteilen, dass die Veranstaltung „%s" abgesagt wurde.', 'eventeule'),
                $eventTitle
            ) . "\n\n";

            if ($startDate !== '') {
                $body .= __('Ursprüngliches Datum:', 'eventeule') . ' '
                    . date_i18n(get_option('date_format'), strtotime($startDate));
                if ($location !== '') {
                    $body .= ' · ' . $location;
                }
                $body .= "\n\n";
            }

            if ($customText !== '') {
                $body .= $customText . "\n\n";
            }

            $body .= __('Wir entschuldigen uns für die Unannehmlichkeiten.', 'eventeule') . "\n\n";
            $body .= sprintf(__('Mit freundlichen Grüßen,\n%s', 'eventeule'), $siteName);

            wp_mail(
                $reg['email'],
                $subject,
                $body,
                ['From: ' . $siteName . ' <' . $fromEmail . '>', 'Content-Type: text/plain; charset=UTF-8']
            );
        }
    }

    public function handle_export(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Du hast keine Berechtigung für diese Aktion.', 'eventeule'));
        }

        check_admin_referer('eventeule_export_registrations', 'eventeule_nonce');

        $eventId = (int) ($_POST['event_id'] ?? 0);

        if ($eventId > 0) {
            $rows     = $this->repository->get_all_by_event($eventId, 10000, 0);
            $filename = 'anmeldungen-' . sanitize_title(get_the_title($eventId)) . '-' . date('Y-m-d') . '.csv';
        } else {
            $rows     = $this->repository->get_all(10000, 0);
            $filename = 'anmeldungen-alle-' . date('Y-m-d') . '.csv';
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8 compatibility
        fwrite($output, "\xEF\xBB\xBF");

        $headers = ['ID', 'Veranstaltung', 'Vorname', 'Nachname', 'E-Mail', 'Telefon', 'Teilnehmer', 'Nachricht', 'Status', 'Angemeldet am'];
        fputcsv($output, $headers, ';');

        foreach ($rows as $row) {
            $eventTitle = isset($row['event_title'])
                ? $row['event_title']
                : (string) get_the_title((int) $row['event_id']);

            fputcsv($output, [
                $row['id'],
                $eventTitle,
                $row['firstname'],
                $row['lastname'],
                $row['email'],
                $row['phone'],
                $row['participants'],
                $row['message'],
                $row['status'],
                $row['registered_at'],
            ], ';');
        }

        fclose($output);
        exit;
    }
}
