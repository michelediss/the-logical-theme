<?php
/**
 * Title: Site Hero Part
 * Slug: the-logical-theme/site-hero-part
 * Categories: the-logical-theme
 * Inserter: no
 * Description: Shared hero template part with translatable starter copy.
 */
?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--xl)","bottom":"var(--wp--preset--spacing--xl)"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group"><!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"60%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:60%"><!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.08em"}},"fontSize":"sm"} -->
<p class="has-sm-font-size" style="letter-spacing:0.08em;text-transform:uppercase"><?php esc_html_e('Starter boilerplate', 'the-logical-theme'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"fontSize":"xl"} -->
<h1 class="has-xl-font-size"><?php esc_html_e('Build landing pages with core blocks, patterns, and a lean frontend pipeline.', 'the-logical-theme'); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"lg"} -->
<p class="has-lg-font-size"><?php esc_html_e('This hero stays generic on purpose: it defines structure, not branding.', 'the-logical-theme'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#main"><?php esc_html_e('Explore sections', 'the-logical-theme'); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%"><!-- wp:group {"className":"no-fadein","style":{"border":{"radius":"var(--wp--custom--radius--lg)"},"spacing":{"padding":{"top":"var(--wp--preset--spacing--lg)","bottom":"var(--wp--preset--spacing--lg)","left":"var(--wp--preset--spacing--lg)","right":"var(--wp--preset--spacing--lg)"}}},"backgroundColor":"muted","layout":{"type":"constrained"}} -->
<div class="wp-block-group no-fadein has-muted-background-color has-background" style="border-radius:var(--wp--custom--radius--lg)"><!-- wp:paragraph -->
<p><?php esc_html_e('Reserved visual slot for future imagery, motion, or editorial messaging.', 'the-logical-theme'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->
