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
    }

    public function register_menu(): void
    {
        add_submenu_page(
            'eventeule',
            __('Registrations', 'eventeule'),
            __('Registrations', 'eventeule'),
            'manage_options',
            'eventeule-registrations',
            [$this, 'render_page']
        );
    }

    public function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'eventeule'));
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
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_eventeule_reg_enabled',
            'meta_value'     => '1',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ]);
        $registrationEvents = $eventsQuery->posts;

        // Flash messages
        $message = '';
        if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
            $message = __('Registration deleted.', 'eventeule');
        }

        include EVENTEULE_PATH . 'templates/admin/registrations.php';
    }

    public function handle_delete(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'eventeule'));
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

    public function handle_export(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'eventeule'));
        }

        check_admin_referer('eventeule_export_registrations', 'eventeule_nonce');

        $eventId = (int) ($_POST['event_id'] ?? 0);

        if ($eventId > 0) {
            $rows       = $this->repository->get_all_by_event($eventId, 10000, 0);
            $filename   = 'registrations-' . sanitize_title(get_the_title($eventId)) . '-' . date('Y-m-d') . '.csv';
        } else {
            $rows     = $this->repository->get_all(10000, 0);
            $filename = 'registrations-all-' . date('Y-m-d') . '.csv';
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8 compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Header row
        $headers = ['ID', 'Event', 'First name', 'Last name', 'Email', 'Phone', 'Participants', 'Message', 'Status', 'Registered at'];
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
