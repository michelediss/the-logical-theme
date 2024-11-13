    <!-- Offcanvas Navbar -->
    <div class="offcanvas offcanvas-end" data-bs-theme="light" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
          aria-label="<?php esc_attr_e('Close', 'your-theme-textdomain'); ?>"></button>
      </div>
      <div class="offcanvas-body">

        <!-- Main Menu -->
        <?php
        wp_nav_menu(array(
          'theme_location' => 'main-menu',
          'container' => false,
          'menu_class' => 'navbar-nav justify-content-end flex-grow-1 pe-3',
          'fallback_cb' => '__return_false',
          'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
          'depth' => 2,
          'walker' => new WP_Bootstrap_Navwalker(),
          'item_class' => 'text-dark paragraph text-base',
          'link_class' => 'text-dark paragraph text-base',
        ));
        ?>

      </div>
    </div>