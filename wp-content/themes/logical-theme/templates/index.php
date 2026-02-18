<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main>
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class(); ?>>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php esc_html_e('No content found.', 'wp-logical-theme'); ?></p>
    <?php endif; ?>
</main>
<?php
get_footer();
