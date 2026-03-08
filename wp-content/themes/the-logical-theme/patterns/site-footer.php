<?php
/**
 * Title: Site Footer
 * Slug: the-logical-theme/site-footer
 * Categories: the-logical-theme
 * Inserter: no
 * Description: Footer layout with site title, supporting copy, and navigation.
 */
?>
<!-- wp:group {"tagName":"div","style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--lg)","bottom":"var(--wp--preset--spacing--md)"}},"border":{"top":{"color":"var(--wp--preset--color--muted)","width":"1px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap"}} -->
<div class="wp-block-group"><!-- wp:site-title {"level":0} /-->

<!-- wp:paragraph -->
<p><?php esc_html_e('Minimal footer starter for future navigation, legal copy, or contact details.', 'the-logical-theme'); ?></p>
<!-- /wp:paragraph --></div>

<!-- wp:navigation {"overlayMenu":"never","layout":{"type":"flex","justifyContent":"right"}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
