<?php
// TinyMCE: remove alignment, read-more, wp-statistics shortcode, horizontal rule, text color; keep only H2/H3.
add_filter('mce_buttons', function ($buttons) {
    $remove = array(
        'alignleft',
        'aligncenter',
        'alignright',
        'alignjustify',
        'wp_more',
        'hr',
        'wp_statistics',
        'wp-statistics',
        'wpstatistics',
        'wp_stats',
        'wp_statistics_shortcode',
        'wp-statistics-shortcode',
    );

    return array_values(array_diff($buttons, $remove));
});

add_filter('mce_buttons_2', function ($buttons) {
    $remove = array(
        'forecolor',
        'backcolor',
        'alignleft',
        'aligncenter',
        'alignright',
        'alignjustify',
        'wp_statistics',
        'wp-statistics',
        'wpstatistics',
        'wp_stats',
        'wp_statistics_shortcode',
        'wp-statistics-shortcode',
    );

    return array_values(array_diff($buttons, $remove));
});

add_filter('tiny_mce_before_init', function ($settings) {
    $settings['block_formats'] = 'Heading 2=h2;Heading 3=h3;';

    return $settings;
});
