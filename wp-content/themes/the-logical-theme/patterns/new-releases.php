<?php
/**
 * Title: New Releases
 * Slug: the-logical-theme/new-releases
 * Categories: featured
 * Description: Sezione ultime pubblicazioni per Sputnik Press.
 */
?>
<!-- wp:group {"layout":{"type":"constrained","wideSize":"90rem"},"style":{"color":{"background":"#F5F0E8"},"spacing":{"padding":{"top":"6rem","bottom":"6rem"}}},"className":"sputnik-new-releases"} -->
<div class="wp-block-group has-background sputnik-new-releases" id="catalogo" style="background-color:#F5F0E8;padding-top:6rem;padding-bottom:6rem">

<!-- wp:columns {"verticalAlignment":"bottom","style":{"spacing":{"padding":{"right":"1.5rem","left":"1.5rem"},"margin":{"bottom":"4rem"}}}} -->
<div class="wp-block-columns are-vertically-aligned-bottom" style="margin-bottom:4rem;padding-right:1.5rem;padding-left:1.5rem">

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%">
<!-- wp:heading {"style":{"color":{"text":"#0A0A0A"},"typography":{"fontSize":"clamp(2.5rem, 5vw, 4.5rem)","lineHeight":"0.92","letterSpacing":"-0.02em","textTransform":"uppercase","fontWeight":"700"}},"fontFamily":"space-grotesk"} -->
<h2 class="wp-block-heading has-text-color has-space-grotesk-font-family" style="color:#0A0A0A;letter-spacing:-0.02em;line-height:0.92;text-transform:uppercase"><strong>Ultime<br><span style="color:#E8132A">Pubblicazioni</span></strong></h2>
<!-- /wp:heading -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%">
<!-- wp:group {"layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.75rem","textTransform":"uppercase","letterSpacing":"0.1em"},"color":{"text":"#0A0A0A"},"elements":{"link":{"color":{"text":"#0A0A0A"}}}}} -->
<p class="has-text-color" style="color:#0A0A0A;letter-spacing:0.1em;font-size:0.75rem;text-transform:uppercase"><a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" style="color:#0A0A0A"><?php esc_html_e( 'Catalogo completo', 'the-logical-theme' ); ?> ↗</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

<!-- wp:query {"queryId":1,"query":{"perPage":6,"postType":"product","order":"desc","orderBy":"date"},"layout":{"type":"grid","columnCount":6},"style":{"spacing":{"padding":{"right":"1.5rem","left":"1.5rem"}}}}} -->
<div class="wp-block-query" style="padding-right:1.5rem;padding-left:1.5rem">
<!-- wp:post-template -->
<!-- wp:group {"layout":{"type":"constrained"},"style":{"spacing":{"blockGap":"0.25rem"}}} -->
<div class="wp-block-group">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"2/3","width":"100%"} /-->
<!-- wp:post-title {"isLink":true,"level":4,"style":{"typography":{"fontSize":"0.875rem","fontWeight":"600","lineHeight":"1.3"},"color":{"text":"#0A0A0A"},"elements":{"link":{"color":{"text":"#0A0A0A"}}}}} /-->
<!-- wp:post-author {"fontSize":"sm","style":{"color":{"text":"#0A0A0A/70"},"typography":{"fontSize":"0.75rem"}}} /-->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->

</div>
<!-- /wp:group -->
