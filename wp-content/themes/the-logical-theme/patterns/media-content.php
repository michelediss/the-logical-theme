<?php

declare(strict_types=1);

register_block_pattern(
    'the-logical-theme/media-content',
    [
        'title'       => __('Media Content', 'the-logical-theme'),
        'description' => __('Text and media split layout.', 'the-logical-theme'),
        'categories'  => ['the-logical-theme'],
        'content'     => <<<'HTML'
<!-- wp:group {"tagName":"section","layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":2} -->
<h2>Pair media with a clear content block.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Use this starter for editorial storytelling, product content, or feature callouts.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>Keep copy short</li><li>Let imagery support hierarchy</li><li>Prefer block-native controls</li></ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
HTML,
    ]
);
