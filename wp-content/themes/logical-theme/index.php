<?php
if (!defined('ABSPATH')) {
    exit;
}

$template_path = 'templates/index.php';
if (file_exists(get_theme_file_path($template_path))) {
    include get_theme_file_path($template_path);
    return;
}

get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();
        the_title('<h2>', '</h2>');
        the_content();
    }
} else {
    echo '<p>' . esc_html__('No content found.', 'wp-logical-theme') . '</p>';
}

get_footer();
