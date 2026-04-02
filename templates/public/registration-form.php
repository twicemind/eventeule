<?php
/**
 * Registration form template for a single event.
 *
 * Available variables:
 * @var int      $eventId
 * @var string[] $enabledFields
 * @var string[] $requiredFields
 * @var int      $maxReg        0 = unlimited
 * @var int      $available     -1 = unlimited, 0 = fully booked, >0 = spots left
 * @var string   $nonce
 * @var string   $ajaxUrl
 */

if (!defined('ABSPATH')) {
    exit;
}

$isFullyBooked = $available === 0;

$fieldLabels = [
    'firstname'    => __('First name', 'eventeule'),
    'lastname'     => __('Last name', 'eventeule'),
    'email'        => __('E-Mail', 'eventeule'),
    'phone'        => __('Phone', 'eventeule'),
    'participants' => __('Number of participants', 'eventeule'),
    'message'      => __('Message / Note', 'eventeule'),
];

$fieldTypes = [
    'firstname'    => 'text',
    'lastname'     => 'text',
    'email'        => 'email',
    'phone'        => 'tel',
    'participants' => 'number',
    'message'      => 'textarea',
];
?>
<div class="eventeule-registration" id="eventeule-registration-<?php echo esc_attr($eventId); ?>">
    <h3 class="eventeule-registration__title">
        <span class="dashicons dashicons-groups"></span>
        <?php esc_html_e('Register for this event', 'eventeule'); ?>
    </h3>

    <?php if ($maxReg > 0): ?>
        <p class="eventeule-registration__counter"
           data-max="<?php echo esc_attr($maxReg); ?>"
           data-available="<?php echo esc_attr($available); ?>">
            <?php if ($available > 0): ?>
                <?php printf(
                    /* translators: %1$d = available, %2$d = total */
                    esc_html__('%1$d of %2$d spots available', 'eventeule'),
                    esc_html($available),
                    esc_html($maxReg)
                ); ?>
            <?php elseif ($available === 0): ?>
                <?php esc_html_e('Fully booked', 'eventeule'); ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if ($isFullyBooked): ?>
        <p class="eventeule-registration__booked">
            <?php esc_html_e('Unfortunately, this event is fully booked.', 'eventeule'); ?>
        </p>
    <?php else: ?>

        <div class="eventeule-registration__messages" aria-live="polite"></div>

        <form class="eventeule-registration__form"
              data-ajax-url="<?php echo esc_url($ajaxUrl); ?>"
              data-nonce="<?php echo esc_attr($nonce); ?>"
              data-event-id="<?php echo esc_attr($eventId); ?>"
              novalidate>

            <?php foreach ($enabledFields as $field):
                $label    = $fieldLabels[$field] ?? $field;
                $type     = $fieldTypes[$field] ?? 'text';
                $required = in_array($field, $requiredFields, true);
                $inputId  = 'eventeule_reg_' . $eventId . '_' . $field;
            ?>
                <div class="eventeule-registration__field" data-field="<?php echo esc_attr($field); ?>">
                    <label for="<?php echo esc_attr($inputId); ?>">
                        <?php echo esc_html($label); ?>
                        <?php if ($required): ?>
                            <span class="eventeule-registration__required" aria-hidden="true">*</span>
                        <?php endif; ?>
                    </label>

                    <?php if ($type === 'textarea'): ?>
                        <textarea id="<?php echo esc_attr($inputId); ?>"
                                  name="<?php echo esc_attr($field); ?>"
                                  rows="3"
                                  <?php echo $required ? 'required aria-required="true"' : ''; ?>></textarea>
                    <?php elseif ($type === 'number'): ?>
                        <input type="number"
                               id="<?php echo esc_attr($inputId); ?>"
                               name="<?php echo esc_attr($field); ?>"
                               value="1"
                               min="1"
                               max="<?php echo $available > 0 ? esc_attr($available) : '50'; ?>"
                               <?php echo $required ? 'required aria-required="true"' : ''; ?> />
                    <?php else: ?>
                        <input type="<?php echo esc_attr($type); ?>"
                               id="<?php echo esc_attr($inputId); ?>"
                               name="<?php echo esc_attr($field); ?>"
                               autocomplete="<?php echo $type === 'email' ? 'email' : ($field === 'firstname' ? 'given-name' : ($field === 'lastname' ? 'family-name' : ($field === 'phone' ? 'tel' : 'off'))); ?>"
                               <?php echo $required ? 'required aria-required="true"' : ''; ?> />
                    <?php endif; ?>

                    <span class="eventeule-registration__field-error" role="alert"></span>
                </div>
            <?php endforeach; ?>

            <div class="eventeule-registration__actions">
                <button type="submit" class="eventeule-registration__submit">
                    <span class="eventeule-registration__submit-text"><?php esc_html_e('Register now', 'eventeule'); ?></span>
                    <span class="eventeule-registration__submit-spinner" aria-hidden="true" style="display:none;">&#9696;</span>
                </button>
            </div>

            <p class="eventeule-registration__privacy">
                <small>
                    <?php if ($required): ?>
                        <?php esc_html_e('Fields marked with * are required.', 'eventeule'); ?>
                    <?php endif; ?>
                </small>
            </p>
        </form>

    <?php endif; ?>
</div>
