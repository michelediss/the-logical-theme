<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$maintenance_plugin = the_logical_maintenance();
$maintenance_page = $maintenance_plugin->get_maintenance_page();

if ($maintenance_plugin->is_maintenance_response()) {
    status_header(503);
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(['the-logical-maintenance-template']); ?>>
<?php wp_body_open(); ?>

<main class="the-logical-maintenance-shell no-fadein">
    <div class="the-logical-maintenance-shell__inner no-fadein">
        <?php if ($maintenance_page instanceof WP_Post) : ?>
            <article class="the-logical-maintenance-content no-fadein">
                <?php echo $maintenance_plugin->get_maintenance_page_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </article>
        <?php else : ?>
            <article class="the-logical-maintenance-content no-fadein">
                <h1><?php esc_html_e('Maintenance page unavailable', 'the-logical-maintenance'); ?></h1>
                <p><?php esc_html_e('Create and publish the page with slug "maintenance" to use this plugin.', 'the-logical-maintenance'); ?></p>
            </article>
        <?php endif; ?>
    </div>
</main>

<?php wp_footer(); ?>
</body>
</html>
