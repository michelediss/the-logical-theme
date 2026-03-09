<?php
/**
 * Title: Hero Home
 * Slug: the-logical-theme/hero-home
 * Categories: hero
 * Description: Hero section per la home page di Sputnik Press.
 */
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hero-ink-bg.png' ) ); ?>","dimRatio":85,"customOverlayColor":"#0A0A0A","minHeight":100,"minHeightUnit":"vh","contentPosition":"bottom left","isDark":true,"className":"sputnik-hero","style":{"spacing":{"padding":{"bottom":"5rem","left":"1.5rem"}}}} -->
<div class="wp-block-cover is-dark has-custom-content-position is-position-bottom-left sputnik-hero" style="min-height:100vh;padding-bottom:5rem;padding-left:1.5rem">
<span aria-hidden="true" class="wp-block-cover__background has-background-dim-85 has-background-dim" style="background-color:#0A0A0A"></span>
<img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero-ink-bg.png' ) ); ?>" data-object-fit="cover" />
<div class="wp-block-cover__inner-container">

<!-- wp:heading {"level":1,"style":{"color":{"text":"#F5F0E8"},"typography":{"fontSize":"clamp(4rem, 9vw, 8rem)","fontWeight":"700","lineHeight":"0.88","letterSpacing":"-0.03em","textTransform":"uppercase"}},"fontFamily":"space-grotesk"} -->
<h1 class="wp-block-heading has-text-color has-space-grotesk-font-family" style="color:#F5F0E8;letter-spacing:-0.03em;line-height:0.88;text-transform:uppercase"><strong>DOOMED<br><span style="color:#E8132A">UNO</span></strong></h1>
<!-- /wp:heading -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
<div class="wp-block-buttons" style="margin-top:1.5rem">
<!-- wp:button {"style":{"color":{"background":"#E8132A","text":"#F5F0E8"},"typography":{"fontSize":"0.75rem","textTransform":"uppercase","letterSpacing":"0.15em"},"spacing":{"padding":{"top":"1rem","bottom":"1rem","left":"2rem","right":"2rem"}}},"className":"is-style-fill"} -->
<a class="wp-block-button__link has-text-color has-background" style="background-color:#E8132A;color:#F5F0E8;letter-spacing:0.15em;padding-top:1rem;padding-bottom:1rem;padding-left:2rem;padding-right:2rem;font-size:0.75rem;text-transform:uppercase" href="<?php echo esc_url( get_permalink( 42 ) ); ?>">
<?php esc_html_e( 'Scopri il libro', 'the-logical-theme' ); ?> →
</a>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
</div>
<!-- /wp:cover -->
