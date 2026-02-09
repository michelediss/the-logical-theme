<?php

/**
 * Template part for displaying the inner navbar content.
 *
 * @package LogicalThemeChild
 */
?>

<!-- Logo -->
<a class="navbar-brand d-flex align-items-center py-0" href="<?php echo esc_url(ltc_home_url('/')); ?>" title="Torna alla Home">
  <img src="<?php echo esc_url(ltc_upload_url('/2024/11/resta-abitante.png')); ?>"
    alt="Logo Resta Abitante"
    width="60"
    height="auto"
    style="max-width: 100%; height: auto;">
</a>

<!-- Navbar toggler button for mobile -->
<button class="navbar-toggler border border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
  aria-controls="offcanvasNavbar" aria-label="<?php esc_attr_e('Toggle navigation', 'your-theme-textdomain'); ?>">
  <span class="navbar-toggler-icon"></span>
</button>

<div class="d-none d-xl-flex align-items-center ms-auto flex-wrap">
  <?php
  wp_nav_menu(array(
    'theme_location' => 'main-menu',
    'container' => false,
    'menu_class' => 'navbar-nav align-items-center flex-wrap me-2',
    'fallback_cb' => '__return_false',
    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
    'depth' => 2,
    'walker' => new WP_Bootstrap_Navwalker(),
    'item_class' => 'mx-2',
    'link_class' => 'heading text-sm text-uppercase',
    'dropdown_link_class' => 'heading text-sm',
  ));
  ?>

  <div class="w-auto p-0 d-flex align-items-center">
    <?php get_search_form(); ?>
  </div>
</div>
