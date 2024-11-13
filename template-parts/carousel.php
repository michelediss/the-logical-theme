<?php
// Recupera il campo personalizzato 'carousel'
$carousel = get_field('carousel');

$slides = array();

// Raccoglie le slide popolate
for ($i = 1; $i <= 3; $i++) {
    $slide_key = 'slide_' . $i;
    if (isset($carousel[$slide_key]) && !empty($carousel[$slide_key]['image'])) {
        $slides[] = $carousel[$slide_key];
    }
}

if ($slides):
?>
<div id="carouselExample" class="carousel slide">
  <div class="carousel-inner">
    <?php foreach ($slides as $index => $slide): ?>
        <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
          <?php
          // Gestione dell'immagine
          if (is_array($slide['image'])) {
              $image_url = $slide['image']['url'];
              $image_alt = $slide['image']['alt'];
          } else {
              $image_url = wp_get_attachment_url($slide['image']);
              $image_alt = get_post_meta($slide['image'], '_wp_attachment_image_alt', true);
          }
          ?>
          <img src="<?php echo esc_url($image_url); ?>" class="d-block w-100" alt="<?php echo esc_attr($image_alt); ?>">
          <div class="carousel-caption d-none d-md-block">
            <?php if (!empty($slide['title'])): ?>
                <h5><?php echo esc_html($slide['title']); ?></h5>
            <?php endif; ?>
            <?php if (!empty($slide['subtitle'])): ?>
                <p><?php echo esc_html($slide['subtitle']); ?></p>
            <?php endif; ?>
            <?php
            // Gestione del pulsante
            if (!empty($slide['button'])):
                $button_url = $slide['button']['url'];
                $button_title = $slide['button']['title'];
                $button_target = $slide['button']['target'] ? $slide['button']['target'] : '_self';
            ?>
                <a href="<?php echo esc_url($button_url); ?>" target="<?php echo esc_attr($button_target); ?>" class="btn btn-primary">
                    <?php echo esc_html($button_title); ?>
                </a>
            <?php endif; ?>
          </div>
        </div>
    <?php endforeach; ?>
  </div>
  <?php if (count($slides) > 1): ?>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Precedente</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Successivo</span>
      </button>
  <?php endif; ?>
</div>
<?php
endif;
?>
