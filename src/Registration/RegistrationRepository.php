<?php

namespace EventEule\Registration;

class RegistrationRepository
{
    private const TABLE_SUFFIX = 'eventeule_registrations';

    private function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    public static function create_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . self::TABLE_SUFFIX;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            event_id bigint(20) unsigned NOT NULL,
            firstname varchar(100) NOT NULL DEFAULT '',
            lastname varchar(100) NOT NULL DEFAULT '',
            email varchar(200) NOT NULL DEFAULT '',
            phone varchar(50) NOT NULL DEFAULT '',
            participants tinyint(3) unsigned NOT NULL DEFAULT 1,
            message text NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'confirmed',
            registered_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY event_id (event_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int|false
    {
        global $wpdb;

        $result = $wpdb->insert(
            $this->table(),
            [
                'event_id'      => (int) $data['event_id'],
                'firstname'     => (string) ($data['firstname'] ?? ''),
                'lastname'      => (string) ($data['lastname'] ?? ''),
                'email'         => (string) ($data['email'] ?? ''),
                'phone'         => (string) ($data['phone'] ?? ''),
                'participants'  => max(1, (int) ($data['participants'] ?? 1)),
                'message'       => (string) ($data['message'] ?? ''),
                'status'        => 'confirmed',
                'registered_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );

        return $result !== false ? (int) $wpdb->insert_id : false;
    }

    /**
     * Returns the total number of booked spots (sum of participants) for an event.
     */
    public function count_by_event(int $eventId): int
    {
        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(participants), 0) FROM {$this->table()} WHERE event_id = %d AND status = 'confirmed'",
                $eventId
            )
        );

        return (int) $result;
    }

    public function email_exists_for_event(int $eventId, string $email): bool
    {
        global $wpdb;

        $count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table()} WHERE event_id = %d AND email = %s",
                $eventId,
                $email
            )
        );

        return $count > 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function get_by_event(int $eventId): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table()} WHERE event_id = %d ORDER BY registered_at ASC",
                $eventId
            ),
            ARRAY_A
        );

        return is_array($results) ? $results : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function get_all(int $limit = 200, int $offset = 0): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, p.post_title AS event_title
                 FROM {$this->table()} r
                 LEFT JOIN {$wpdb->posts} p ON r.event_id = p.ID
                 ORDER BY r.registered_at DESC
                 LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );

        return is_array($results) ? $results : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function get_all_by_event(int $eventId, int $limit = 200, int $offset = 0): array
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table()} WHERE event_id = %d ORDER BY registered_at ASC LIMIT %d OFFSET %d",
                $eventId,
                $limit,
                $offset
            ),
            ARRAY_A
        );

        return is_array($results) ? $results : [];
    }

    public function count_all(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table()}");
    }

    public function count_all_by_event(int $eventId): int
    {
        global $wpdb;
        return (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$this->table()} WHERE event_id = %d", $eventId)
        );
    }

    public function delete(int $id): bool
    {
        global $wpdb;
        return (bool) $wpdb->delete($this->table(), ['id' => $id], ['%d']);
    }

    public function get_by_id(int $id): array|null
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table()} WHERE id = %d", $id),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }
}
