<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<?php
    global $post;
    $extra_classes = [];

    // Se è una singola pagina o post
    if ( is_singular() && isset($post) ) {
        $extra_classes[] = 'page-id-' . $post->ID;
        $extra_classes[] = 'page-slug-' . $post->post_name;
    }

    // Se è la pagina blog assegnata nelle impostazioni
    if ( is_home() && !is_front_page() ) {
        $blog_page_id = get_option('page_for_posts');
        if ( $blog_page_id ) {
            $extra_classes[] = 'page-id-' . $blog_page_id;
            $extra_classes[] = 'page-slug-' . get_post_field( 'post_name', $blog_page_id );
        }
    }
?>

<body class="bg-primary-white" <?php body_class( $extra_classes ); ?>>

        <header id="header" class="site-header" role="banner">

            <?php get_template_part('template-parts/header/navbar-static', 'content'); ?>
            <?php get_template_part('template-parts/header/navbar-scroll', 'content'); ?>
            <?php get_template_part('template-parts/header/offcanvas', 'content'); ?>

        </header>

        <div class="site-content bg-primary-white">




