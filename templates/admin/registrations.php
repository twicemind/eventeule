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
 * @var string                      $notice
 * @var string                      $noticeType  'success'|'error'
 */
if (!defined('ABSPATH')) {
    exit;
}

$isCancelled = $eventId > 0 && get_post_meta($eventId, '_eventeule_cancelled', true) === '1';
?>
<div class="wrap eventeule-registrations">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-groups" style="font-size:1.3em; vertical-align:middle;"></span>
        <?php esc_html_e('Anmeldungen', 'eventeule'); ?>
        <?php if ($eventTitle !== ''): ?>
            &mdash; <?php echo esc_html($eventTitle); ?>
            <?php if ($isCancelled): ?>
                <span style="display:inline-block; margin-left:8px; padding:2px 10px; background:#dc3545; color:#fff; border-radius:4px; font-size:13px; vertical-align:middle;">
                    <?php esc_html_e('Abgesagt', 'eventeule'); ?>
                </span>
            <?php endif; ?>
        <?php endif; ?>
    </h1>

    <?php if ($eventId > 0): ?>
        <a href="<?php echo esc_url(get_edit_post_link($eventId)); ?>" class="page-title-action">
            <?php esc_html_e('Veranstaltung bearbeiten', 'eventeule'); ?>
        </a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <?php if (!empty($notice)): ?>
        <div class="notice notice-<?php echo $noticeType === 'error' ? 'error' : 'success'; ?> is-dismissible">
            <p><?php echo esc_html($notice); ?></p>
        </div>
    <?php endif; ?>

    <!-- Filter & Export -->
    <div style="display:flex; align-items:center; gap:12px; margin:16px 0; flex-wrap:wrap;">

        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; gap:8px; align-items:center;">
            <input type="hidden" name="page" value="eventeule-registrations">
            <select name="event_id">
                <option value="0"><?php esc_html_e('— Alle Veranstaltungen —', 'eventeule'); ?></option>
                <?php foreach ($registrationEvents as $eid): ?>
                    <option value="<?php echo esc_attr($eid); ?>" <?php selected($eventId, $eid); ?>>
                        <?php echo esc_html(get_the_title($eid)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button"><?php esc_html_e('Filtern', 'eventeule'); ?></button>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="eventeule_export_registrations">
            <input type="hidden" name="event_id" value="<?php echo esc_attr($eventId); ?>">
            <?php wp_nonce_field('eventeule_export_registrations', 'eventeule_nonce'); ?>
            <button type="submit" class="button button-secondary">
                <span class="dashicons dashicons-download" style="vertical-align:middle;"></span>
                <?php esc_html_e('CSV exportieren', 'eventeule'); ?>
            </button>
        </form>

        <span style="color:#666; font-size:13px;">
            <?php printf(
                esc_html(_n('%d Anmeldung', '%d Anmeldungen', $total, 'eventeule')),
                esc_html($total)
            ); ?>
        </span>
    </div>

    <!-- Veranstaltung absagen -->
    <?php if ($eventId > 0 && !$isCancelled): ?>
        <div style="margin-bottom:20px; padding:16px; background:#fff8e1; border:1px solid #ffe082; border-radius:6px;">
            <h3 style="margin:0 0 8px; font-size:14px;">
                <span class="dashicons dashicons-dismiss" style="color:#e65100; vertical-align:middle;"></span>
                <?php esc_html_e('Veranstaltung absagen', 'eventeule'); ?>
            </h3>
            <p style="margin:0 0 12px; font-size:13px; color:#555;">
                <?php esc_html_e('Die Veranstaltung wird als abgesagt markiert. Optional werden alle Angemeldeten per E-Mail informiert.', 'eventeule'); ?>
            </p>
            <button type="button" id="eventeule-cancel-toggle" class="button button-secondary"
                    style="border-color:#e65100; color:#e65100;">
                <span class="dashicons dashicons-dismiss" style="vertical-align:middle;"></span>
                <?php esc_html_e('Veranstaltung absagen…', 'eventeule'); ?>
            </button>
            <div id="eventeule-cancel-form" style="display:none; margin-top:16px; padding-top:16px; border-top:1px solid #ffe082;">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                      onsubmit="return confirm('<?php esc_attr_e('Veranstaltung wirklich absagen?', 'eventeule'); ?>');">
                    <input type="hidden" name="action" value="eventeule_cancel_event">
                    <input type="hidden" name="event_id" value="<?php echo esc_attr($eventId); ?>">
                    <?php wp_nonce_field('eventeule_cancel_event', 'eventeule_nonce'); ?>
                    <p>
                        <label style="font-weight:600; display:block; margin-bottom:4px;">
                            <?php esc_html_e('Absagetext (optional)', 'eventeule'); ?>
                        </label>
                        <textarea name="cancellation_text" rows="3" class="large-text"
                                  placeholder="<?php esc_attr_e('Begründung oder weitere Informationen zur Absage…', 'eventeule'); ?>"></textarea>
                    </p>
                    <?php if ($total > 0): ?>
                        <p>
                            <label>
                                <input type="checkbox" name="send_cancellation_emails" value="1" checked>
                                <?php printf(
                                    esc_html(_n(
                                        'Absage-E-Mail an %d angemeldete Person senden',
                                        'Absage-E-Mail an %d angemeldete Personen senden',
                                        $total,
                                        'eventeule'
                                    )),
                                    esc_html($total)
                                ); ?>
                            </label>
                        </p>
                    <?php endif; ?>
                    <button type="submit" class="button" style="background:#e65100; color:#fff; border-color:#e65100;">
                        <span class="dashicons dashicons-dismiss" style="vertical-align:middle;"></span>
                        <?php esc_html_e('Jetzt absagen', 'eventeule'); ?>
                    </button>
                    <button type="button" class="button button-link" id="eventeule-cancel-close" style="margin-left:8px;">
                        <?php esc_html_e('Abbrechen', 'eventeule'); ?>
                    </button>
                </form>
            </div>
        </div>
    <?php elseif ($isCancelled): ?>
        <div style="margin-bottom:20px; padding:12px 16px; background:#f8d7da; border:1px solid #f5c6cb; border-radius:6px; color:#721c24;">
            <span class="dashicons dashicons-dismiss" style="vertical-align:middle;"></span>
            <strong><?php esc_html_e('Diese Veranstaltung wurde abgesagt.', 'eventeule'); ?></strong>
            <?php $ct = (string) get_post_meta($eventId, '_eventeule_cancellation_text', true);
            if ($ct !== ''): ?>
                <br><em><?php echo esc_html($ct); ?></em>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Anmeldungsliste -->
    <?php if (empty($registrations)): ?>
        <div style="background:#fff; border:1px solid #ddd; border-radius:4px; padding:32px; text-align:center; color:#666;">
            <span class="dashicons dashicons-groups" style="font-size:3em; height:auto; width:auto; color:#ccc;"></span>
            <p style="margin-top:12px; font-size:15px;">
                <?php esc_html_e('Noch keine Anmeldungen vorhanden.', 'eventeule'); ?>
            </p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <?php if ($eventId === 0): ?>
                        <th style="width:18%;"><?php esc_html_e('Veranstaltung', 'eventeule'); ?></th>
                    <?php endif; ?>
                    <th><?php esc_html_e('Name', 'eventeule'); ?></th>
                    <th><?php esc_html_e('E-Mail', 'eventeule'); ?></th>
                    <th><?php esc_html_e('Telefon', 'eventeule'); ?></th>
                    <th style="width:48px; text-align:center;"><?php esc_html_e('Pers.', 'eventeule'); ?></th>
                    <th><?php esc_html_e('Nachricht', 'eventeule'); ?></th>
                    <th style="width:115px;"><?php esc_html_e('Angemeldet am', 'eventeule'); ?></th>
                    <th style="width:115px;"><?php esc_html_e('Aktionen', 'eventeule'); ?></th>
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
                        <td><strong><?php echo esc_html(trim($reg['firstname'] . ' ' . $reg['lastname'])); ?></strong></td>
                        <td>
                            <?php if (!empty($reg['email'])): ?>
                                <a href="mailto:<?php echo esc_attr($reg['email']); ?>"><?php echo esc_html($reg['email']); ?></a>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td><?php echo !empty($reg['phone']) ? esc_html($reg['phone']) : '&mdash;'; ?></td>
                        <td style="text-align:center;"><?php echo esc_html($reg['participants']); ?></td>
                        <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                            title="<?php echo esc_attr($reg['message']); ?>">
                            <?php echo !empty($reg['message']) ? esc_html($reg['message']) : '&mdash;'; ?>
                        </td>
                        <td style="font-size:12px; color:#555;">
                            <?php echo esc_html(
                                wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($reg['registered_at']))
                            ); ?>
                        </td>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:4px;">
                                <?php if (!empty($reg['email'])): ?>
                                    <button type="button" class="button button-small eventeule-reply-btn"
                                            data-id="<?php echo esc_attr($reg['id']); ?>"
                                            data-email="<?php echo esc_attr($reg['email']); ?>"
                                            data-name="<?php echo esc_attr(trim($reg['firstname'] . ' ' . $reg['lastname'])); ?>"
                                            data-event-id="<?php echo esc_attr($reg['event_id']); ?>"
                                            data-event-title="<?php echo esc_attr(get_the_title((int) $reg['event_id'])); ?>">
                                        <span class="dashicons dashicons-email" style="vertical-align:middle; font-size:13px;"></span>
                                        <?php esc_html_e('Antworten', 'eventeule'); ?>
                                    </button>
                                <?php endif; ?>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                      onsubmit="return confirm('<?php esc_attr_e('Anmeldung wirklich löschen?', 'eventeule'); ?>');">
                                    <input type="hidden" name="action" value="eventeule_delete_registration">
                                    <input type="hidden" name="registration_id" value="<?php echo esc_attr($reg['id']); ?>">
                                    <input type="hidden" name="event_id" value="<?php echo esc_attr($eventId); ?>">
                                    <?php wp_nonce_field('eventeule_delete_registration', 'eventeule_nonce'); ?>
                                    <button type="submit" class="button button-small button-link-delete">
                                        <?php esc_html_e('Löschen', 'eventeule'); ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="tablenav bottom" style="margin-top:12px;">
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

<!-- Antwort-Modal -->
<div id="eventeule-reply-modal" style="display:none; position:fixed; inset:0; z-index:100010; background:rgba(0,0,0,.5); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:8px; padding:24px; width:520px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,.2);">
        <h2 style="margin:0 0 16px; font-size:16px;">
            <span class="dashicons dashicons-email" style="vertical-align:middle;"></span>
            <?php esc_html_e('E-Mail senden an', 'eventeule'); ?>
            <span id="eventeule-reply-name" style="color:#0073aa;"></span>
        </h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="eventeule_reply_registration">
            <input type="hidden" name="registration_id" id="eventeule-reply-reg-id" value="">
            <input type="hidden" name="event_id" id="eventeule-reply-event-id" value="">
            <?php wp_nonce_field('eventeule_reply_registration', 'eventeule_nonce'); ?>
            <p style="margin:0 0 12px; font-size:13px; color:#555;">
                <?php esc_html_e('An:', 'eventeule'); ?>
                <strong id="eventeule-reply-email-display"></strong>
            </p>
            <p>
                <label style="font-weight:600; display:block; margin-bottom:4px;">
                    <?php esc_html_e('Betreff', 'eventeule'); ?> <span style="color:#dc3545;">*</span>
                </label>
                <input type="text" name="reply_subject" id="eventeule-reply-subject" class="large-text" required>
            </p>
            <p>
                <label style="font-weight:600; display:block; margin-bottom:4px;">
                    <?php esc_html_e('Nachricht', 'eventeule'); ?> <span style="color:#dc3545;">*</span>
                </label>
                <textarea name="reply_body" rows="6" class="large-text" required></textarea>
            </p>
            <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:16px;">
                <button type="button" id="eventeule-reply-close" class="button">
                    <?php esc_html_e('Abbrechen', 'eventeule'); ?>
                </button>
                <button type="submit" class="button button-primary">
                    <span class="dashicons dashicons-email" style="vertical-align:middle;"></span>
                    <?php esc_html_e('E-Mail senden', 'eventeule'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    // Absagen-Toggle
    var cancelToggle = document.getElementById('eventeule-cancel-toggle');
    var cancelForm   = document.getElementById('eventeule-cancel-form');
    var cancelClose  = document.getElementById('eventeule-cancel-close');
    if (cancelToggle && cancelForm) {
        cancelToggle.addEventListener('click', function () {
            cancelForm.style.display = cancelForm.style.display === 'none' ? 'block' : 'none';
        });
    }
    if (cancelClose && cancelForm) {
        cancelClose.addEventListener('click', function () {
            cancelForm.style.display = 'none';
        });
    }

    // Antworten-Modal
    var modal     = document.getElementById('eventeule-reply-modal');
    var nameEl    = document.getElementById('eventeule-reply-name');
    var emailEl   = document.getElementById('eventeule-reply-email-display');
    var regIdEl   = document.getElementById('eventeule-reply-reg-id');
    var eventIdEl = document.getElementById('eventeule-reply-event-id');
    var subjectEl = document.getElementById('eventeule-reply-subject');

    document.querySelectorAll('.eventeule-reply-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            nameEl.textContent  = btn.dataset.name || btn.dataset.email;
            emailEl.textContent = btn.dataset.email;
            regIdEl.value       = btn.dataset.id;
            eventIdEl.value     = btn.dataset.eventId;
            subjectEl.value     = '<?php echo esc_js(__('Bezüglich deiner Anmeldung', 'eventeule')); ?>'
                                  + (btn.dataset.eventTitle ? ': ' + btn.dataset.eventTitle : '');
            modal.style.display = 'flex';
            subjectEl.focus();
        });
    });

    if (document.getElementById('eventeule-reply-close')) {
        document.getElementById('eventeule-reply-close').addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.style.display = 'none';
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') modal.style.display = 'none';
        });
    }
}());
</script>
