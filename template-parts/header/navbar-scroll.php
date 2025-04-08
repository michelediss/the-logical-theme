<?php
/**
 * Template part for displaying the header content
 *
 * @package LogicalTheme
 */
?>

<nav id="navbar-scroll" class="transition navbar navbar-expand-lg bg-light text-primary fixed-top z-3 w-100 border-bottom border-light">
<div class="container">
    <!-- Logo -->
    <?php echo custom_logo('140px', ' ', 'fill-primary'); ?>

    <!-- Navbar toggler button for mobile -->
    <button class="navbar-toggler border border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
      aria-controls="offcanvasNavbar" aria-label="<?php esc_attr_e('Toggle navigation', 'your-theme-textdomain'); ?>">
      <span class="navbar-toggler-icon"></span>
    </button>

    <?php
        wp_nav_menu(array(
          'theme_location' => 'main-menu',
          'container' => false,
          'menu_class' => 'navbar-nav justify-content-end flex-grow-1 pe-3 d-none d-lg-flex',
          'fallback_cb' => '__return_false',
          'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
          'depth' => 2,
          'walker' => new WP_Bootstrap_Navwalker(),
          'item_class' => '',
          'link_class' => 'text-dark paragraph text-base',
        ));
        ?>

  </div>
</nav>