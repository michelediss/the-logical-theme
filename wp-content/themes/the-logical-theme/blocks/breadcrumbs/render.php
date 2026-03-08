<?php

if (! defined('ABSPATH')) {
    exit;
}

$show_current = ! isset($attributes['showCurrent']) || (bool) $attributes['showCurrent'];
$separator = '/';

if (isset($attributes['separator']) && is_string($attributes['separator'])) {
    $separator_candidate = trim($attributes['separator']);

    if ($separator_candidate !== '') {
        $separator = $separator_candidate;
    }
}

$items = [
    [
        'label' => __('Home', 'the-logical-theme'),
        'url' => home_url('/'),
        'current' => false,
    ],
];

$append_item = static function (string $label, ?string $url = null, bool $current = false) use (&$items): void {
    $items[] = [
        'label' => $label,
        'url' => $url,
        'current' => $current,
    ];
};

$append_post_type_archive = static function (WP_Post_Type $post_type) use (&$append_item): void {
    if (! $post_type->has_archive) {
        return;
    }

    $archive_url = get_post_type_archive_link($post_type->name);

    if ($archive_url) {
        $append_item($post_type->labels->name, $archive_url, false);
    }
};

if (is_home()) {
    if ($show_current) {
        $posts_page_id = (int) get_option('page_for_posts');
        $append_item(
            $posts_page_id ? get_the_title($posts_page_id) : __('Blog', 'the-logical-theme'),
            null,
            true
        );
    }
} elseif (is_front_page()) {
    if (! $show_current) {
        $items = [];
    } else {
        $items[0]['current'] = true;
        $items[0]['url'] = null;
    }
} elseif (is_page()) {
    $post_id = get_the_ID();
    $ancestor_ids = array_reverse(get_post_ancestors($post_id));

    foreach ($ancestor_ids as $ancestor_id) {
        $append_item(get_the_title($ancestor_id), get_permalink($ancestor_id), false);
    }

    if ($show_current && $post_id) {
        $append_item(get_the_title($post_id), null, true);
    }
} elseif (is_single()) {
    $post_id = get_the_ID();
    $post_type = get_post_type_object((string) get_post_type($post_id));

    if ($post_type instanceof WP_Post_Type) {
        if ($post_type->name === 'post') {
            $posts_page_id = (int) get_option('page_for_posts');

            if ($posts_page_id > 0) {
                $append_item(get_the_title($posts_page_id), get_permalink($posts_page_id), false);
            }
        } else {
            $append_post_type_archive($post_type);
        }
    }

    if (is_post_type_hierarchical((string) get_post_type($post_id))) {
        $ancestor_ids = array_reverse(get_post_ancestors($post_id));

        foreach ($ancestor_ids as $ancestor_id) {
            $append_item(get_the_title($ancestor_id), get_permalink($ancestor_id), false);
        }
    }

    if ($show_current && $post_id) {
        $append_item(get_the_title($post_id), null, true);
    }
} elseif (is_post_type_archive()) {
    if ($show_current) {
        $post_type = get_queried_object();

        if ($post_type instanceof WP_Post_Type) {
            $append_item($post_type->labels->name, null, true);
        }
    }
} elseif (is_category() || is_tag() || is_tax()) {
    $term = get_queried_object();

    if ($term instanceof WP_Term) {
        $taxonomy = get_taxonomy($term->taxonomy);

        if ($taxonomy && ! empty($taxonomy->object_type[0])) {
            $post_type = get_post_type_object($taxonomy->object_type[0]);

            if ($post_type instanceof WP_Post_Type && $post_type->name !== 'post') {
                $append_post_type_archive($post_type);
            } elseif ($post_type instanceof WP_Post_Type && $post_type->name === 'post') {
                $posts_page_id = (int) get_option('page_for_posts');

                if ($posts_page_id > 0) {
                    $append_item(get_the_title($posts_page_id), get_permalink($posts_page_id), false);
                }
            }
        }

        if (is_taxonomy_hierarchical($term->taxonomy)) {
            $ancestor_ids = array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy'));

            foreach ($ancestor_ids as $ancestor_id) {
                $ancestor = get_term($ancestor_id, $term->taxonomy);

                if ($ancestor instanceof WP_Term && ! is_wp_error($ancestor)) {
                    $ancestor_link = get_term_link($ancestor);

                    if (! is_wp_error($ancestor_link)) {
                        $append_item($ancestor->name, $ancestor_link, false);
                    }
                }
            }
        }

        if ($show_current) {
            $append_item(single_term_title('', false), null, true);
        }
    }
} elseif (is_search()) {
    if ($show_current) {
        $append_item(
            sprintf(
                /* translators: %s: search query. */
                __('Search results for "%s"', 'the-logical-theme'),
                get_search_query()
            ),
            null,
            true
        );
    }
} elseif (is_404()) {
    if ($show_current) {
        $append_item(__('Not found', 'the-logical-theme'), null, true);
    }
} elseif (is_archive()) {
    if ($show_current) {
        $append_item(get_the_archive_title(), null, true);
    }
}

if (! $show_current) {
    $items = array_values(array_filter(
        $items,
        static fn (array $item): bool => ! $item['current']
    ));
}

if (empty($items)) {
    return;
}

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'breadcrumbs',
]);
?>
<nav <?php echo $wrapper_attributes; ?> aria-label="<?php esc_attr_e('Breadcrumb', 'the-logical-theme'); ?>">
    <ol class="breadcrumbs__list">
        <?php foreach ($items as $index => $item) : ?>
            <li class="breadcrumbs__item">
                <?php if (! empty($item['url']) && ! $item['current']) : ?>
                    <a class="breadcrumbs__link" href="<?php echo esc_url($item['url']); ?>">
                        <?php echo esc_html($item['label']); ?>
                    </a>
                <?php else : ?>
                    <span class="breadcrumbs__current"<?php echo $item['current'] ? ' aria-current="page"' : ''; ?>>
                        <?php echo esc_html($item['label']); ?>
                    </span>
                <?php endif; ?>

                <?php if ($index < count($items) - 1) : ?>
                    <span class="breadcrumbs__separator" aria-hidden="true">
                        <?php echo esc_html($separator); ?>
                    </span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
