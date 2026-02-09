<?php
/**
 * Cookie consent banner markup (plugin fallback).
 *
 * Variables expected:
 * - $arrow_svg (string)
 * - $settings_groups (array)
 * - $cookie_policy_url (string)
 */
?>
<div id="lcc-banner" class="lcc-hidden" role="dialog" aria-modal="true" aria-labelledby="lcc-banner-title" aria-describedby="lcc-banner-desc" tabindex="-1">
  <div class="lcc-card">
    <div class="lcc-title heading bold-italic text-lg text-uppercase text-black" id="lcc-banner-title"><?php echo esc_html__('Cookie', 'lcc'); ?></div>
    <div class="lcc-desc paragraph regular text-base text-black" id="lcc-banner-desc">
      <?php echo esc_html__('Usiamo cookie necessari e, con il tuo consenso, cookie per preferenze ed embedding di servizi esterni.', 'lcc'); ?>
      <p class="lcc-desc-link mt-2 paragraph text-base text-black">Per ulteriori informazioni consulta la
        <a href="<?php echo esc_url($cookie_policy_url); ?>" target="_blank" rel="noopener" class="text-primary bold">
          <?php esc_html_e('cookie policy', 'lcc'); ?>
        </a>
        di <?php echo esc_html(get_bloginfo('name')); ?>
      </p>
    </div>

    <div class="lcc-actions lcc-actions-main">
      <?php
      if (function_exists('logical_primary_button')) {
        echo logical_primary_button(
          __('Accetta tutti', 'lcc'),
          '#',
          '',
          '',
          false,
          'px-4 py-2',
          'text-sm',
          'text-primary',
          'bg-white',
          'border-primary',
          'hover-text-white',
          'hover-bg-primary',
          'hover-border-primary',
          ' data-lcc-action="acceptAll"'
        );
        echo logical_primary_button(
          __('Rifiuta', 'lcc'),
          '#',
          '',
          '',
          false,
          'px-4 py-2',
          'text-sm',
          'text-primary',
          'bg-white',
          'border-primary',
          'hover-text-white',
          'hover-bg-primary',
          'hover-border-primary',
          ' data-lcc-action="rejectAll"'
        );
        echo logical_primary_button(
          __('Impostazioni', 'lcc'),
          '#',
          '',
          '',
          false,
          'px-4 py-2',
          'text-sm',
          'text-primary',
          'bg-white',
          'border-primary',
          'hover-text-white',
          'hover-bg-primary',
          'hover-border-primary',
          ' data-lcc-action="openSettings"'
        );
      } else {
      ?>
        <button type="button" class="lcc-btn lcc-primary heading text-sm bold text-uppercase border- border-2 border-primary" data-lcc-action="acceptAll"><?php echo esc_html__('Accetta tutti', 'lcc'); ?></button>
        <button type="button" class="lcc-btn heading text-sm bold text-uppercase border- border-2 border-primary" data-lcc-action="rejectAll"><?php echo esc_html__('Rifiuta', 'lcc'); ?></button>
        <button type="button" class="lcc-btn heading text-sm bold text-uppercase border- border-2 border-primary" data-lcc-action="openSettings"><?php echo esc_html__('Impostazioni', 'lcc'); ?></button>
      <?php } ?>
    </div>

    <div id="lcc-settings" class="lcc-settings lcc-hidden">
      <?php foreach ($settings_groups as $group) :
        $key = $group['key'];
        $label = $group['label'];
        $aria_label = sprintf(
          /* translators: %s: Cookie category label */
          esc_html__('Mostra i cookie per "%s"', 'lcc'),
          $label
        );
      ?>
        <div class="lcc-row lcc-row-details" data-lcc-cookie-row="<?php echo esc_attr($key); ?>">
          <button type="button" class="lcc-cookie-toggle-btn" data-lcc-cookie-toggle="<?php echo esc_attr($key); ?>" aria-expanded="false" aria-label="<?php echo esc_attr($aria_label); ?>">
            <?php
            if ($arrow_svg) {
              echo $arrow_svg;
            } else {
              echo '<span class="lcc-cookie-toggle-icon lcc-cookie-arrow" aria-hidden="true"></span>';
            }
            ?>
          </button>
          <div class="lcc-row-main">
            <span><?php echo esc_html($label); ?></span>
            <div class="form-check mb-0">
              <?php if ($group['supports_toggle']) : ?>
                <input type="checkbox" class="form-check-input lcc-toggle-input" data-lcc-toggle="<?php echo esc_attr($key); ?>" aria-label="<?php echo esc_attr(sprintf(__('Attiva categoria %s', 'lcc'), $label)); ?>" />
              <?php else : ?>
                <input type="checkbox" class="form-check-input lcc-toggle-input" checked disabled aria-disabled="true" aria-label="<?php echo esc_attr(sprintf(__('Categoria %s obbligatoria', 'lcc'), $label)); ?>" />
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="lcc-cookie-panel lcc-hidden" data-lcc-cookie-panel="<?php echo esc_attr($key); ?>">
          <div class="lcc-cookie-list" data-lcc-cookie-list="<?php echo esc_attr($key); ?>"></div>
        </div>
      <?php endforeach; ?>

      <div class="lcc-actions lcc-actions-settings">
        <?php
        if (function_exists('logical_primary_button')) {
          echo logical_primary_button(
            __('Salva', 'lcc'),
            '#',
            '',
            '',
            false,
            'px-4 py-2',
            'text-sm',
            'text-primary',
            'bg-white',
            'border-primary',
            'hover-text-white',
            'hover-bg-primary',
            'hover-border-primary',
            ' data-lcc-action="save"'
          );
        } else {
        ?>
          <button type="button" class="lcc-btn lcc-primary heading text-sm bold text-uppercase border- border-2 border-primary" data-lcc-action="save"><?php echo esc_html__('Salva', 'lcc'); ?></button>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
