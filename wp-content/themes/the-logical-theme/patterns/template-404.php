<?php
/**
 * Title: 404 Content
 * Slug: the-logical-theme/template-404
 * Categories: the-logical-theme
 * Inserter: no
 * Description: Not found template content with CTA and search.
 */
?>
<!-- wp:group {"tagName":"main","layout":{"type":"constrained"},"style":{"spacing":{"blockGap":"var(--wp--preset--spacing--md)"}}} -->
<div class="wp-block-group"><!-- wp:heading {"level":1} -->
<h1><?php esc_html_e('Page not found', 'the-logical-theme'); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e('The page you requested could not be found. Try searching or continue browsing from the homepage.', 'the-logical-theme'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Back home', 'the-logical-theme'); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:search {"showLabel":false,"buttonUseIcon":true} /--></div>
<!-- /wp:group -->
