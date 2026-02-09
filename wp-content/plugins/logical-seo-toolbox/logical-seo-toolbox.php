<?php
/*
 * Plugin Name: Logical SEO Toolbox
 * Plugin URI: https://github.com/michelediss/logical-seo-toolbox
 * Description: A minimal WP plugin for SEO: managing meta tags, sitemap, robots.txt, noindex, canonical, and social optimization.
 * Version: 1.0.0
 * Author: Michele Paolino
 * Author URI: https://www.michelepaolino.me
 * Text Domain: logical-seo-toolbox
*/

// Ensure the file is not accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retrieve post types supported by the plugin.
 *
 * @return array
 */
function lst_get_supported_post_types() {
    $post_types = get_post_types(
        [
            'public' => true,
        ],
        'names'
    );

    unset($post_types['attachment']);

    return apply_filters('lst_supported_post_types', array_values($post_types));
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/meta-box.php';
require_once plugin_dir_path(__FILE__) . 'includes/social-meta-tags.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/noindex-nofollow.php';

// Add canonical URL
function add_canonical_url() {
    if (is_singular()) {
        echo '<link rel="canonical" href="' . esc_url(get_permalink()) . '" />';
    }
}
add_action('wp_head', 'add_canonical_url');

/**
 * Trigger sitemap generation when supported post types are saved.
 *
 * @param int     $post_id
 * @param WP_Post $post
 * @param bool    $update
 */
function lst_maybe_generate_sitemap($post_id, $post, $update) {
    if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    if (!($post instanceof WP_Post)) {
        return;
    }

    if ('publish' !== $post->post_status) {
        return;
    }

    if (!in_array($post->post_type, lst_get_supported_post_types(), true)) {
        return;
    }

    generate_sitemap();
}
add_action('save_post', 'lst_maybe_generate_sitemap', 20, 3);

// Generate sitemap
function generate_sitemap() {
    $post_types = lst_get_supported_post_types();

    $posts = get_posts([
        'post_type' => $post_types,
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);

    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

    foreach ($posts as $post) {
        $url = $xml->addChild('url');
        $url->addChild('loc', get_permalink($post));
        $url->addChild('lastmod', get_the_modified_time('Y-m-d', $post));
        $url->addChild('changefreq', 'monthly');
        $url->addChild('priority', '0.8');
    }

    $sitemap_file = ABSPATH . 'sitemap.xml';
    $xml->asXML($sitemap_file);
}

