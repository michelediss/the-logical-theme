<?php
require_once get_template_directory() . '/template-parts/flexible/helpers.php';

$row = get_query_var('logical_flexible_row');
$row = is_array($row) ? $row : [];

$options = isset($row['options']) && is_array($row['options']) ? $row['options'] : [];
$layout_options = isset($options['layout']) && is_array($options['layout']) ? $options['layout'] : $options;

$section_class = logical_flexible_section_class($layout_options, 'white');
$container_class = logical_flexible_container_class($layout_options, 'container');
$breadcrumbs = strtolower((string) ($options['breadcrumbs'] ?? 'off')) === 'on';
?>
<section class="title-section py-4 py-lg-5 <?php echo esc_attr($section_class); ?>">
    <div class="<?php echo esc_attr($container_class); ?>">
        <h1 class="heading mb-3"><?php echo esc_html(get_the_title()); ?></h1>
        <?php if ($breadcrumbs): ?>
            <nav aria-label="<?php echo esc_attr__('Breadcrumb', 'logical-theme-child'); ?>" class="paragraph">
                <?php if (function_exists('bcn_display')): ?>
                    <?php bcn_display(); ?>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html__('Home', 'logical-theme-child'); ?></a>
                    <span class="mx-2">/</span>
                    <span><?php echo esc_html(get_the_title()); ?></span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>

