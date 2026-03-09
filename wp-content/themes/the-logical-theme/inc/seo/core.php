<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns public post types supported by the SEO toolbox.
 */
function the_logical_theme_seo_supported_post_types(): array
{
    $post_types = get_post_types(
        [
            'public' => true,
        ],
        'names'
    );

    unset($post_types['attachment']);

    return array_values($post_types);
}

/**
 * Checks whether a post type is supported by the SEO toolbox.
 */
function the_logical_theme_seo_is_supported_post_type(string $post_type): bool
{
    return in_array($post_type, the_logical_theme_seo_supported_post_types(), true);
}

/**
 * Registers REST-exposed post meta used by the block editor.
 */
function the_logical_theme_seo_register_post_meta(): void
{
    $meta_keys = [
        '_seo_title' => [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ],
        '_seo_description' => [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
        ],
        '_seo_noindex' => [
            'type' => 'boolean',
            'sanitize_callback' => static fn ($value): bool => (bool) $value,
        ],
        '_seo_nofollow' => [
            'type' => 'boolean',
            'sanitize_callback' => static fn ($value): bool => (bool) $value,
        ],
    ];

    foreach (the_logical_theme_seo_supported_post_types() as $post_type) {
        foreach ($meta_keys as $meta_key => $meta_args) {
            register_post_meta(
                $post_type,
                $meta_key,
                [
                    'single' => true,
                    'show_in_rest' => true,
                    'type' => $meta_args['type'],
                    'default' => $meta_args['type'] === 'boolean' ? false : '',
                    'sanitize_callback' => $meta_args['sanitize_callback'],
                    'auth_callback' => static function () use ($post_type): bool {
                        $post_type_object = get_post_type_object($post_type);
                        $capability = $post_type_object instanceof WP_Post_Type
                            ? $post_type_object->cap->edit_posts
                            : 'edit_posts';

                        return current_user_can($capability);
                    },
                ]
            );
        }
    }
}
add_action('init', 'the_logical_theme_seo_register_post_meta');

/**
 * Returns the current post-like object used for front-end meta output.
 */
function the_logical_theme_seo_target_post(): ?WP_Post
{
    if (is_home() && ! is_front_page()) {
        $blog_page_id = (int) get_option('page_for_posts');

        return $blog_page_id > 0 ? get_post($blog_page_id) : null;
    }

    if (is_front_page() || is_singular()) {
        $target = get_queried_object();

        return $target instanceof WP_Post ? $target : null;
    }

    return null;
}

/**
 * Generates a default SEO description from the available post content.
 */
function the_logical_theme_seo_default_description(WP_Post $post): string
{
    $content = has_excerpt($post) ? $post->post_excerpt : $post->post_content;
    $description = wp_trim_words(wp_strip_all_tags((string) $content), 30, '...');

    if ($description !== '') {
        return $description;
    }

    return (string) get_bloginfo('description', 'display');
}

/**
 * Returns the effective SEO title and description for a post.
 *
 * @return array{title: string, description: string}
 */
function the_logical_theme_seo_resolve_meta(WP_Post $post): array
{
    $site_name = (string) get_bloginfo('name');
    $post_title = trim((string) get_the_title($post));
    $default_title = $post_title !== '' ? sprintf('%s - %s', $post_title, $site_name) : $site_name;

    return [
        'title' => (string) (get_post_meta($post->ID, '_seo_title', true) ?: $default_title),
        'description' => (string) (get_post_meta($post->ID, '_seo_description', true) ?: the_logical_theme_seo_default_description($post)),
    ];
}

/**
 * Builds the effective robots directives for the current request.
 *
 * @return array{noindex: bool, nofollow: bool}
 */
function the_logical_theme_seo_resolve_robots(): array
{
    $target = the_logical_theme_seo_target_post();

    $noindex = get_option('minimal_seo_noindex', '0') === '1';
    $nofollow = get_option('minimal_seo_nofollow', '0') === '1';

    if ($target instanceof WP_Post) {
        $noindex = $noindex || rest_sanitize_boolean(get_post_meta($target->ID, '_seo_noindex', true));
        $nofollow = $nofollow || rest_sanitize_boolean(get_post_meta($target->ID, '_seo_nofollow', true));
    }

    return [
        'noindex' => $noindex,
        'nofollow' => $nofollow,
    ];
}

/**
 * Injects SEO robots directives through the core robots API.
 *
 * @param array<string, bool|string> $robots
 * @return array<string, bool|string>
 */
function the_logical_theme_seo_filter_wp_robots(array $robots): array
{
    $directives = the_logical_theme_seo_resolve_robots();

    if ($directives['noindex']) {
        unset($robots['index']);
        $robots['noindex'] = true;
    }

    if ($directives['nofollow']) {
        unset($robots['follow']);
        $robots['nofollow'] = true;
    }

    return $robots;
}
add_filter('wp_robots', 'the_logical_theme_seo_filter_wp_robots');

/**
 * Returns the preferred image URL for social previews.
 */
function the_logical_theme_seo_resolve_image_url(?WP_Post $post = null): string
{
    if ($post instanceof WP_Post) {
        $image_url = (string) get_the_post_thumbnail_url($post, 'full');
        if ($image_url !== '') {
            return $image_url;
        }
    }

    $front_page_id = (int) get_option('page_on_front');
    if ($front_page_id > 0) {
        $front_page_image = (string) get_the_post_thumbnail_url($front_page_id, 'full');
        if ($front_page_image !== '') {
            return $front_page_image;
        }
    }

    return (string) get_site_icon_url();
}

/**
 * Outputs canonical, description, Open Graph, and Twitter tags.
 */
function the_logical_theme_seo_output_head_tags(): void
{
    $target = the_logical_theme_seo_target_post();
    $image_url = the_logical_theme_seo_resolve_image_url($target);

    if (! $target instanceof WP_Post) {
        if ($image_url !== '') {
            echo '<meta property="og:image" content="' . esc_url($image_url) . '" />' . "\n";
            echo '<meta property="og:image:width" content="1200" />' . "\n";
            echo '<meta property="og:image:height" content="630" />' . "\n";
            echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url($image_url) . '" />' . "\n";
        }

        return;
    }

    $meta = the_logical_theme_seo_resolve_meta($target);
    $permalink = (string) get_permalink($target);

    if (! is_singular()) {
        echo '<link rel="canonical" href="' . esc_url($permalink) . '" />' . "\n";
    }

    echo '<meta name="description" content="' . esc_attr($meta['description']) . '" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($meta['title']) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($meta['description']) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($permalink) . '" />' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($meta['title']) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($meta['description']) . '" />' . "\n";

    if ($image_url !== '') {
        echo '<meta property="og:image" content="' . esc_url($image_url) . '" />' . "\n";
        echo '<meta property="og:image:width" content="1200" />' . "\n";
        echo '<meta property="og:image:height" content="630" />' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($image_url) . '" />' . "\n";
    }
}
add_action('wp_head', 'the_logical_theme_seo_output_head_tags', 5);

/**
 * Returns whether a root-level SEO file can be written safely.
 */
function the_logical_theme_seo_can_write_root_file(string $path): bool
{
    $directory = dirname($path);

    if (! is_dir($directory) || ! is_writable($directory)) {
        return false;
    }

    if (file_exists($path) && ! is_writable($path)) {
        return false;
    }

    return true;
}

/**
 * Writes SEO file contents without emitting PHP warnings to the response.
 */
function the_logical_theme_seo_write_root_file(string $path, string $contents): bool
{
    if (! the_logical_theme_seo_can_write_root_file($path)) {
        error_log(sprintf('The Logical Theme SEO could not write "%s": path is not writable.', $path));

        return false;
    }

    $bytes = @file_put_contents($path, $contents, LOCK_EX);

    if ($bytes === false) {
        error_log(sprintf('The Logical Theme SEO failed to write "%s".', $path));

        return false;
    }

    return true;
}

/**
 * Generates the sitemap.xml file in the WordPress root.
 */
function the_logical_theme_seo_generate_sitemap(): void
{
    $posts = get_posts([
        'post_type' => the_logical_theme_seo_supported_post_types(),
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);

    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

    foreach ($posts as $post) {
        $url = $xml->addChild('url');
        $url->addChild('loc', (string) get_permalink($post));
        $url->addChild('lastmod', get_the_modified_time('Y-m-d', $post));
        $url->addChild('changefreq', 'monthly');
        $url->addChild('priority', '0.8');
    }

    $sitemap_contents = $xml->asXML();

    if ($sitemap_contents === false) {
        error_log('The Logical Theme SEO failed to generate sitemap XML contents.');

        return;
    }

    the_logical_theme_seo_write_root_file(ABSPATH . 'sitemap.xml', $sitemap_contents);
}

/**
 * Regenerates the sitemap when supported posts change publishing state.
 */
function the_logical_theme_seo_maybe_regenerate_sitemap(string $new_status, string $old_status, WP_Post $post): void
{
    if (! the_logical_theme_seo_is_supported_post_type($post->post_type)) {
        return;
    }

    if ($new_status !== 'publish' && $old_status !== 'publish') {
        return;
    }

    the_logical_theme_seo_generate_sitemap();
}
add_action('transition_post_status', 'the_logical_theme_seo_maybe_regenerate_sitemap', 10, 3);

/**
 * Regenerates the sitemap when a published supported post is deleted.
 */
function the_logical_theme_seo_regenerate_sitemap_on_delete(int $post_id): void
{
    $post = get_post($post_id);

    if (! $post instanceof WP_Post || $post->post_status !== 'publish') {
        return;
    }

    if (! the_logical_theme_seo_is_supported_post_type($post->post_type)) {
        return;
    }

    the_logical_theme_seo_generate_sitemap();
}
add_action('before_delete_post', 'the_logical_theme_seo_regenerate_sitemap_on_delete');

/**
 * Regenerates the sitemap when the theme is activated.
 */
function the_logical_theme_seo_generate_sitemap_on_switch(): void
{
    the_logical_theme_seo_generate_sitemap();
}
add_action('after_switch_theme', 'the_logical_theme_seo_generate_sitemap_on_switch');
