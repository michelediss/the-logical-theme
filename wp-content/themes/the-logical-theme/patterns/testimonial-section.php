<?php

declare(strict_types=1);

register_block_pattern(
    'the-logical-theme/testimonial-section',
    [
        'title'       => __('Testimonial Section', 'the-logical-theme'),
        'description' => __('Minimal quote-led social proof section.', 'the-logical-theme'),
        'categories'  => ['the-logical-theme'],
        'content'     => <<<'HTML'
<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":2} -->
<h2>What people say</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"fontSize":"lg"} -->
<p class="has-lg-font-size">“Keep testimonials brief, direct, and easy to swap.”</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Person name, role</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p>Add a second quote or replace this column with metrics, logos, or additional context.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
HTML,
    ]
);
