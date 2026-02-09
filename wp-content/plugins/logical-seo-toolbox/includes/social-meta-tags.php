<?php
function lst_get_target_post_for_meta() {
    if ( is_home() && ! is_front_page() ) {
        $blog_page_id = (int) get_option( 'page_for_posts' );
        return $blog_page_id ? get_post( $blog_page_id ) : null;
    }
    if ( is_front_page() || is_singular() ) {
        return get_queried_object();
    }
    return null;
}

function add_social_meta_tags() {
    $target = lst_get_target_post_for_meta();
    if ( ! $target instanceof WP_Post ) {
        // Fallback: use front page featured image for non-covered views (e.g., archives).
        $front_page_id = (int) get_option( 'page_on_front' );
        if ( $front_page_id ) {
            $fallback_image = get_the_post_thumbnail_url( $front_page_id, 'full' );
            if ( $fallback_image ) {
                echo '<meta property="og:image" content="' . esc_url( $fallback_image ) . '" />' . "\n";
                echo '<meta property="og:image:width" content="1200" />' . "\n";
                echo '<meta property="og:image:height" content="630" />' . "\n";
                echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
                echo '<meta name="twitter:image" content="' . esc_url( $fallback_image ) . '" />' . "\n";
            }
        }
        return;
    }

    $seo_title       = get_post_meta( $target->ID, '_seo_title', true );
    $seo_description = get_post_meta( $target->ID, '_seo_description', true );

    // Fallbacks
    if ( ! $seo_title ) {
        $seo_title = get_the_title( $target );
    }
    if ( ! $seo_description ) {
        $content = has_excerpt( $target ) ? $target->post_excerpt : $target->post_content;
        $seo_description = wp_trim_words( wp_strip_all_tags( $content ), 30, '...' );
    }
    if ( ! $seo_description ) {
        $seo_description = get_bloginfo( 'description', 'display' );
    }

    $post_thumbnail = get_the_post_thumbnail_url( $target, 'full' );
    $site_favicon   = get_site_icon_url();
    $image_url      = $post_thumbnail ? $post_thumbnail : $site_favicon;
    $permalink      = get_permalink( $target );

    // Open Graph tags
    if ( $seo_title && $seo_description ) {
        echo '<meta property="og:title" content="' . esc_html( $seo_title ) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_html( $seo_description ) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $permalink ) . '" />' . "\n";
        if ( $image_url ) {
            echo '<meta property="og:image" content="' . esc_url( $image_url ) . '" />' . "\n";
            echo '<meta property="og:image:width" content="1200" />' . "\n";
            echo '<meta property="og:image:height" content="630" />' . "\n";
        }

        // Twitter Card tags
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_html( $seo_title ) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_html( $seo_description ) . '" />' . "\n";
        if ( $image_url ) {
            echo '<meta name="twitter:image" content="' . esc_url( $image_url ) . '" />' . "\n";
        }
    }

    // Meta description (sempre, con fallback)
    if ( $seo_description ) {
        echo '<meta name="description" content="' . esc_attr( $seo_description ) . '" />' . "\n";
    }
}
add_action('wp_head', 'add_social_meta_tags', 5);
?>
