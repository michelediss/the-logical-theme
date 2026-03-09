<?php
/**
 * Title: Newsletter Section
 * Slug: the-logical-theme/newsletter-section
 * Categories: featured
 * Description: Sezione newsletter per Sputnik Press.
 */
?>
<!-- wp:group {"layout":{"type":"constrained","wideSize":"90rem"},"style":{"color":{"background":"#E8132A"},"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}},"className":"sputnik-newsletter"} -->
<div class="wp-block-group has-background sputnik-newsletter" style="background-color:#E8132A;padding-top:5rem;padding-bottom:5rem">

<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"padding":{"right":"1.5rem","left":"1.5rem"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center" style="padding-right:1.5rem;padding-left:1.5rem">

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">
<!-- wp:heading {"style":{"color":{"text":"#F5F0E8"},"typography":{"fontSize":"clamp(1.75rem, 3vw, 3rem)","lineHeight":"1.1","fontWeight":"700"}},"fontFamily":"space-grotesk"} -->
<h2 class="wp-block-heading has-text-color has-space-grotesk-font-family" style="color:#F5F0E8;font-size:clamp(1.75rem, 3vw, 3rem);line-height:1.1"><strong>Iscriviti alla newsletter</strong></h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"color":{"text":"#F5F0E8/90"},"typography":{"fontSize":"1rem"}}} -->
<p class="has-text-color" style="color:#F5F0E8/90;font-size:1rem">Rimani aggiornato sulle novit&agrave; di Sputnik Press.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%">
<!-- wp:html -->
<form action="#" method="post" class="sputnik-newsletter-form">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"},"style":{"spacing":{"gap":"0.5rem"}}} -->
<div class="wp-block-group" style="gap:0.5rem">
<input type="email" name="email" placeholder="la-tua@email.it" required style="flex:1;padding:0.875rem 1rem;border:none;background:#F5F0E8;color:#0A0A0A;font-size:0.875rem;">
<button type="submit" style="padding:0.875rem 1.5rem;border:none;background:#0A0A0A;color:#F5F0E8;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;cursor:pointer;">
<?php esc_html_e( 'Iscriviti', 'the-logical-theme' ); ?> &rarr;
</button>
</div>
<!-- /wp:group -->
</form>
<!-- /wp:html -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->
