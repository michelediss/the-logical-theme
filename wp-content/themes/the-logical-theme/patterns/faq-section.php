<?php

declare(strict_types=1);

register_block_pattern(
    'the-logical-theme/faq-section',
    [
        'title'       => __('FAQ Section', 'the-logical-theme'),
        'description' => __('Starter FAQ layout using headings and paragraphs.', 'the-logical-theme'),
        'categories'  => ['the-logical-theme'],
        'content'     => <<<'HTML'
<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":2} -->
<h2>Frequently asked questions</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3>How should this evolve?</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Add or remove entries as needed, or replace this with a richer future block pattern.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Why keep it simple?</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The boilerplate should model structure and rhythm without locking final UX decisions too early.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
HTML,
    ]
);
