<?php

declare(strict_types=1);

register_block_pattern(
    'the-logical-theme/feature-grid',
    [
        'title'       => __('Feature Grid', 'the-logical-theme'),
        'description' => __('Three-column feature overview.', 'the-logical-theme'),
        'categories'  => ['the-logical-theme'],
        'content'     => <<<'HTML'
<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":2} -->
<h2>Feature grid</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3>Composable</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Build sections with patterns and keep structure editorially flexible.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3>Lean</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Use minimal glue code and keep frontend logic modular.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3>Extendable</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Add more patterns or JS modules without changing the theme core.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
HTML,
    ]
);
