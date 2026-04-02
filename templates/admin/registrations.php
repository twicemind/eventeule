<?php
/**
 * Admin registrations list template.
 *
 * @var int                         $eventId
 * @var string                      $eventTitle
 * @var array<int, array>           $registrations
 * @var int                         $total
 * @var int                         $paged
 * @var int                         $totalPages
 * @var int[]                       $registrationEvents
 * @var string                      $message
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap eventeule-registrations">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-groups" style="font-size: 1.3em; vertical-align: middle;"></span>
        <?php esc_html_e('Event Registrations', 'eventeule'); ?>
        <?php if ($eventTitle !== ''): ?>
            &mdash; <?php echo esc_html($eventTitle); ?>
        <?php endif; ?>
    </h1>

    <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule')); ?>" class="page-title-action">
        &larr; <?php esc_html_e('Back to EventEule', 'eventeule'); ?>
    </a>

    <hr class="wp-header-end">

    <?php if (!empty($message)): ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div>
    <?php endif; ?>

    <!-- Filter & Export bar -->
    <div style="display: flex; align-items: center; gap: 12px; margin: 16px 0; flex-wrap: wrap;">

        <!-- Event filter -->
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; gap:8px; align-items:center;">
            <input type="hidden" name="page" value="eventeule-registrations">
            <select name="event_id" id="eventeule_event_filter">
                <option value="0"><?php esc_html_e('— All events —', 'eventeule'); ?></option>
                <?php foreach ($registrationEvents as $eid): ?>
                    <option value="<?php echo esc_attr($eid); ?>" <?php selected($eventId, $eid); ?>>
                        <?php echo esc_html(get_the_title($eid)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button"><?php esc_html_e('Filter', 'eventeule'); ?></button>
        </form>

        <!-- Export CSV -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="eventeule_export_registrations">
            <input type="hidden" name="event_id" value="<?php echo esc_attr($eventId); ?>">
            <?php wp_nonce_field('eventeule_export_registrations', 'eventeule_nonce'); ?>
            <button type="submit" class="button button-secondary">
                <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                <?php esc_html_e('Export CSV', 'eventeule'); ?>
            </button>
        </form>

        <span style="color: #666; font-size: 13px;">
            <?php printf(
                /* translators: %d = total count */
                esc_html(_n('%d registration', '%d registrations', $total, 'eventeule')),
                esc_html($total)
            ); ?>
        </span>
    </div>

    <?php if (empty($registrations)): ?>
        <div style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 32px; text-align: center; color: #666;">
            <span class="dashicons dashicons-groups" style="font-size: 3em; height: auto; width: auto; color: #ccc;"></span>
            <p style="margin-top: 12px; font-size: 15px;">
                <?php esc_html_e('No registrations found.', 'eventeule'); ?>
            </p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <?php if ($eventId === 0): ?>
                        <th style="width:20%;"><?php esc_html_e('Event', 'eventeule'); ?></th>
                    <?php endif; ?>
                    <th><?php esc_html_e('Name', 'eventeule'); ?></th>
                    <th><?php esc_html_e('Email', 'eventeule'); ?></th>
                    <th><?php esc_html_e('Phone', 'eventeule'); ?></th>
                    <th style="width:50px; text-align:center;"><?php esc_html_e('Spots', 'eventeule'); ?></th>
                    <th><?php esc_html_e('Message', 'eventeule'); ?></th>
                    <th style="width:130px;"><?php esc_html_e('Registered at', 'eventeule'); ?></th>
                    <th style="width:80px;"><?php esc_html_e('Actions', 'eventeule'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $reg): ?>
                    <tr>
                        <?php if ($eventId === 0): ?>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=eventeule-registrations&event_id=' . (int) $reg['event_id'])); ?>">
                                    <?php echo esc_html($reg['event_title'] ?? get_the_title((int) $reg['event_id'])); ?>
                                </a>
                            </td>
                        <?php endif; ?>
                        <td>
                            <?php echo esc_html(trim($reg['firstname'] . ' ' . $reg['lastname'])); ?>
                        </td>
                        <td>
                            <?php if (!empty($reg['email'])): ?>
                                <a href="mailto:<?php echo esc_attr($reg['email']); ?>"><?php echo esc_html($reg['email']); ?></a>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td><?php echo !empty($reg['phone']) ? esc_html($reg['phone']) : '&mdash;'; ?></td>
                        <td style="text-align:center;"><?php echo esc_html($reg['participants']); ?></td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?php echo esc_attr($reg['message']); ?>">
                            <?php echo !empty($reg['message']) ? esc_html($reg['message']) : '&mdash;'; ?>
                        </td>
                        <td style="font-size:12px; color:#555;">
                            <?php echo esc_html(
                                wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($reg['registered_at']))
                            ); ?>
                        </td>
                        <td>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                  onsubmit="return confirm('<?php esc_attr_e('Delete this registration?', 'eventeule'); ?>');">
                                <input type="hidden" name="action" value="eventeule_delete_registration">
                                <input type="hidden" name="registration_id" value="<?php echo esc_attr($reg['id']); ?>">
                                <input type="hidden" name="event_id" value="<?php echo esc_attr($eventId); ?>">
                                <?php wp_nonce_field('eventeule_delete_registration', 'eventeule_nonce'); ?>
                                <button type="submit" class="button button-small button-link-delete">
                                    <?php esc_html_e('Delete', 'eventeule'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="tablenav bottom" style="margin-top: 12px;">
                <?php
                $pageLinks = paginate_links([
                    'base'      => add_query_arg('paged', '%#%'),
                    'format'    => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total'     => $totalPages,
                    'current'   => $paged,
                ]);
                if ($pageLinks) {
                    echo '<div class="tablenav-pages">' . $pageLinks . '</div>';
                }
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
