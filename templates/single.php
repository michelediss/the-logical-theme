<?php get_header(); ?>

<?php echo spacer(5); ?>

<?php echo __( 'Images', 'the-logical-theme' ); ?>

<div class="container pb-5">
    <div class="row d-flex justify-content-center">
        <div class="col-12 col-xl-10 col-3xl-8 col-5xl-7">
            <div class="post-content">
                <h1>
                    <?php the_title(); ?>
                </h1>

                <?php the_content(); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>

