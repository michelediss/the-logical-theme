<?php
/**
 * Footer template with Bootstrap styling that includes a logo and navigation links.
 */

 function adjust_site_content_margin() {
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var footer = document.getElementById('footer');
            var siteContent = document.querySelector('.site-content');

            if (footer && siteContent) {
                var footerHeight = footer.offsetHeight;
                siteContent.style.marginBottom = footerHeight + 'px';
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'adjust_site_content_margin', 100);
?>

<footer id="footer" class="site-footer w-100 position-fixed bottom-0 z-n1" role="contentinfo">

    <div class="container-fluid text-light py-5 bg-primary-black">
        <div class="container">
            <div class="row">
                <!-- Logo Section -->
                <div class="col-md-4 mb-3">
                    <?php
                    // Display the custom logo with a custom size, color, and class
                    echo custom_logo('90%', '#fff', 'mb-3');
                    ?>

                    <?php
                    echo generate_social_icons(
                        '#fff',
                        '24',
                        '24',
                        'me-3',
                        [
                            'facebook' => 'facebook-extra-class', // Classi aggiuntive per icone specifiche
                            'instagram' => 'instagram-extra-class',
                        ]
                    );
                    ?>

                </div>

                <!-- Navigation Links -->
                <div class="col-md-4 mb-3">
                    <h5 class="heading text-xl mb-2">Sitemap</h5>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'main-menu',
                        'container' => false,
                        'menu_class' => 'navbar-nav justify-content-end flex-grow-1 pe-3',
                        'fallback_cb' => '__return_false',
                        'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        'depth' => 2,
                        'walker' => new WP_Bootstrap_Navwalker(),
                        'item_class' => 'text-light paragraph text-base',
                        'link_class' => 'text-light paragraph text-base py-1',
                    ));
                    ?>
                </div>

                <!-- Info -->
                <div class="col-md-4 mb-3">
                    <h5 class="heading text-xl mb-3">Info</h5>
                    <div class="d-block">
                        <a href="mailto:info@yourdomain"
                            class="text-base paragraph text-light text-decoration-none mb-4">info@yourdomain</a><br>
                        <a href="tel:1122334455" class="text-base paragraph text-light text-decoration-none mb-4">+39
                            339
                            112 2334</a>
                        <br><br>
                        <p class="text-base paragraph">123 Main Street, <br>City, Country</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>