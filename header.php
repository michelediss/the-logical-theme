<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body data-barba="wrapper" <?php body_class(); ?>>

    <div data-barba="container" data-barba-namespace="<?php echo get_post_type(); ?>">

        <header id="header" class="site-header" role="banner">

            <?php get_template_part('template-parts/header/navbar-static', 'content'); ?>
            <?php get_template_part('template-parts/header/navbar-scroll', 'content'); ?>
            <?php get_template_part('template-parts/header/offcanvas', 'content'); ?>

        </header>

        <div id="content" class="site-content">