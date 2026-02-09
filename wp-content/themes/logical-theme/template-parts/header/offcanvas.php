<!-- Offcanvas Navbar -->
<div class="offcanvas offcanvas-end bg-gradientY" data-bs-theme="dark" tabindex="-1" id="offcanvasNavbar"
  aria-labelledby="offcanvasNavbarLabel">
  <div class="offcanvas-header pb-0">
    <h5 class="offcanvas-title heading text-white text-lg" id="offcanvasNavbarLabel">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
      aria-label="<?php esc_attr_e('Close', 'your-theme-textdomain'); ?>"></button>
  </div>

  <div class="offcanvas-body d-flex flex-column justify-content-between">

    <div>
      <!-- Search (mobile) - Simple form without animations -->
      <div class="mt-2">
        <form role="search" method="get" class="search-form-mobile rounded-pill border border-2 border-white"
          action="<?php echo esc_url(ltc_home_url('/')); ?>">
          <div class="input-group">
            <input type="search"
              class="form-control py-2 px-3 bg-transparent border-0 text-white paragraph text-sm  me-2"
              placeholder="<?php echo esc_attr__('Cerca...', 'textdomain'); ?>"
              value="<?php echo get_search_query(); ?>" name="s" required />
            <button type="submit" class="btn bg-transparent d-flex align-items-center justify-content-center"
              aria-label="<?php esc_attr_e('Cerca', 'textdomain'); ?>">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="bi bi-search fill-white"
                viewBox="0 0 16 16">
                <path
                  d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
              </svg>
            </button>
          </div>
        </form>
      </div>

      <?php
      wp_nav_menu(array(
        'theme_location' => 'main-menu',
        'container' => false,
        'menu_class' => ' mt-4 p-0',
        'fallback_cb' => '__return_false',
        'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'depth' => 2,
        'walker' => new WP_Bootstrap_Navwalker(),
        'item_class' => 'my-2',
        'link_class' => 'paragraph text-base text-white',
      ));
      ?>
      <div class="d-flex justify-content-start gap-3 mt-4">
        <?php get_template_part('template-parts/partials/social-links-buttons'); ?>
      </div>
    </div>

    <div>

    <div class="container">
        <div class="row no-gsap">
            <div class="col-lg-10 col-xl-8 mx-auto">
                <div class="row">
                    <div class="px-0 col-12 col-xxl-7 d-block d-xxl-flex justify-content-start flex-wrap align-items-center">
                        <p  class="d-block d-md-inline-block paragraph text-white text-xs m-0">© <?php echo date('Y'); ?> - Resta
                            Abitante
                        </p>
                        <span class="paragraph text-white text-xs d-none d-md-inline"> - </span>
                        <div class="spacer pb-1 d-block d-md-none"></div>
                        <p  class="d-block d-md-inline-block paragraph text-white text-xs m-0">Quest'opera
                            è distribuita con licenza <a class="text-white paragraph bold" target="_blank"
                                href="https://creativecommons.org/licenses/by-nc-nd/4.0/">CC BY-NC-ND 4.0.</a>
                        </p>
                    </div>
                    <div class="px-0 col-12 col-xxl-5 d-block d-xxl-flex justify-content-start flex-wrap align-items-center">
                        <div class="spacer pb-1 d-block d-xxl-none"></div>
                        <p class="paragraph text-xs m-0">
                            <a href="#"
                                class="text-white text-uppercase text-decoration-none hover-secondary cookie-consent"
                                data-lcc-open-settings="1">Gestione cookie</a>
                                <span> | </span>
                            <a href="<?php echo esc_url(ltc_home_url('/privacy-policy/')); ?>"
                                class="text-white text-uppercase text-decoration-none hover-secondary">Privacy
                                policy</a>
                            <span> | </span>
                            <a href="<?php echo esc_url(ltc_home_url('/cookie-policy/')); ?>"
                                class="text-white text-uppercase text-decoration-none hover-secondary">Cookie policy</a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>


    </div>
  </div>
</div>