<?php

declare(strict_types=1);

register_block_pattern(
    'the-logical-theme/cta-band',
    [
        'title'       => __('CTA Band', 'the-logical-theme'),
        'description' => __('Simple full-width call to action.', 'the-logical-theme'),
        'categories'  => ['the-logical-theme'],
        'content'     => <<<'HTML'
<!-- wp:group {"tagName":"section","backgroundColor":"foreground","textColor":"canvas","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-canvas-color has-foreground-background-color has-text-color has-background"><!-- wp:heading {"level":2} -->
<h2>Ready for your next section or conversion moment.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Drop this band between editorial sections to create rhythm and directional intent.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"canvas","textColor":"foreground"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-foreground-color has-canvas-background-color has-text-color has-background wp-element-button">Call to action</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
HTML,
    ]
);
