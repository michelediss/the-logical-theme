<?php

declare(strict_types=1);

register_block_pattern(
    'the-logical-theme/page-heading',
    [
        'title'       => __('Page Heading', 'the-logical-theme'),
        'description' => __('Intro heading section for generic pages.', 'the-logical-theme'),
        'categories'  => ['the-logical-theme'],
        'content'     => <<<'HTML'
<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"fontSize":"sm"} -->
<p class="has-sm-font-size">Section label</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"fontSize":"xl"} -->
<h1 class="has-xl-font-size">Page heading starter</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"lg"} -->
<p class="has-lg-font-size">Use this at the top of pages that need a lightweight intro block before the main content.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
HTML,
    ]
);
