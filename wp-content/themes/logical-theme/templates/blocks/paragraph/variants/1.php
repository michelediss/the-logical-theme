<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<section class="w-full py-12">
  <div class="container">
    <div class="grid items-center gap-8 md:grid-cols-2">
      <div>
        <span class="heading text-sm text-primary"><?php echo esc_html($pretitle); ?></span>
        <h2 class="heading text-3xl text-secondary mt-2"><?php echo esc_html($title); ?></h2>
        <div class="paragraph text-base text-secondary mt-3"><?php echo wp_kses_post($text); ?></div>
      </div>
      <div>
        <?php if ($image_src !== '') : ?>
          <img src="<?php echo esc_url($image_src); ?>" alt="<?php echo esc_attr($image_alt); ?>" class="h-auto w-full rounded-lg object-cover" />
        <?php else : ?>
          <div class="flex min-h-[220px] items-center justify-center rounded-lg border border-secondary/20 bg-light text-secondary/70">
            <?php esc_html_e('Select an image from Media Library', 'wp-logical-theme'); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
