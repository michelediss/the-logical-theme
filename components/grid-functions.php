<?php
function modify_home_posts_per_page( $query ) {
    // Assicurati di agire solo sulla query principale nella parte pubblica e sulla pagina blog
    if ( ! is_admin() && $query->is_main_query() && $query->is_home() ) {
         $query->set('posts_per_page', 8);
         $query->set('ignore_sticky_posts', true);
    }
}
add_action( 'pre_get_posts', 'modify_home_posts_per_page', 1 );

function render_post_grid($args = []) {
    $defaults = [
        'pagination' => true,
    ];
    $args = wp_parse_args($args, $defaults);

    global $wp_query;

    set_query_var('post_grid_query', $wp_query);
    set_query_var('post_grid_args', $args);

    get_template_part('template-parts/grid-template');
}