<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
    <footer class="site-footer">
        <small>&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php bloginfo('name'); ?></small>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
