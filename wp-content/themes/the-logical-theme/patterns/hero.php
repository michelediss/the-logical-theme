<?php

declare(strict_types=1);

register_block_pattern(
    'the-logical-theme/hero',
    [
        'title'       => __('Hero', 'the-logical-theme'),
        'description' => __('Intro section with heading, copy, and primary CTA.', 'the-logical-theme'),
        'categories'  => ['the-logical-theme'],
        'content'     => <<<'HTML'
<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"fontSize":"sm"} -->
<p class="has-sm-font-size">Starter label</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>Use this hero as a clean starting point.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Replace content and media, keep the section rhythm and block structure.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Primary action</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
HTML,
    ]
);
