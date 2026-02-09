<?php
/**
* Plugin Name: Default Featured Image
* Description: Lets you set a default featured image from the admin area for content that does not have one.
* Version: 0.1.0
* Plugin URI: https://github.com/michelediss/the-logical-theme
* Author: Michele Paolino
* Author URI: https://michelepaolino.com
*/

if (! defined('ABSPATH')) {
    exit;
}

class PAP_Default_Featured_Image {
    private const OPTION_NAME = 'pap_default_featured_image_id';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_setting']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('save_post', [$this, 'assign_default_featured_image'], 20, 3);
    }

    public function register_setting(): void {
        register_setting('pap_default_featured_image', self::OPTION_NAME, [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 0,
        ]);
    }

    public function add_settings_page(): void {
        add_options_page(
            __('Featured Image', 'pap-default-featured-image'),
            __('Featured Image', 'pap-default-featured-image'),
            'manage_options',
            'pap-default-featured-image',
            [$this, 'render_settings_page']
        );
    }

    public function enqueue_assets(string $hook_suffix): void {
        if ('settings_page_pap-default-featured-image' !== $hook_suffix) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'pap-default-featured-image-admin',
            plugins_url('assets/admin.js', __FILE__),
            ['jquery'],
            '0.1.0',
            true
        );
        wp_localize_script('pap-default-featured-image-admin', 'papDefaultFeaturedImage', [
            'frameTitle' => __('Seleziona la featured image di default', 'pap-default-featured-image'),
            'chooseButton' => __('Usa questa immagine', 'pap-default-featured-image'),
            'removeConfirm' => __('Vuoi rimuovere l\'immagine di default?', 'pap-default-featured-image'),
            'placeholderText' => __('Nessuna immagine selezionata', 'pap-default-featured-image'),
        ]);
        wp_enqueue_style(
            'pap-default-featured-image-admin',
            plugins_url('assets/admin.css', __FILE__),
            [],
            '0.1.0'
        );
    }

    public function render_settings_page(): void {
        if (! current_user_can('manage_options')) {
            return;
        }

        $attachment_id = absint(get_option(self::OPTION_NAME));
        ?>
        <div class="wrap pap-default-featured-image-settings">
            <h1><?php esc_html_e('Featured Image di default', 'pap-default-featured-image'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('pap_default_featured_image'); ?>
                <div class="pap-default-featured-image-field">
                    <div class="pap-default-featured-image-preview">
                        <?php
                        if ($attachment_id) {
                            echo wp_get_attachment_image($attachment_id, 'medium');
                        } else {
                            echo '<div class="pap-default-featured-image-placeholder">' .
                                esc_html__('Nessuna immagine selezionata', 'pap-default-featured-image') .
                                '</div>';
                        }
                        ?>
                    </div>
                    <input type="hidden" id="pap-default-featured-image-id" name="<?php echo esc_attr(self::OPTION_NAME); ?>" value="<?php echo esc_attr($attachment_id); ?>">
                    <div class="pap-default-featured-image-actions">
                        <button type="button" class="button button-primary" id="pap-default-featured-image-select">
                            <?php esc_html_e('Scegli dalla libreria', 'pap-default-featured-image'); ?>
                        </button>
                        <button type="button" class="button" id="pap-default-featured-image-remove" <?php disabled(! $attachment_id); ?>>
                            <?php esc_html_e('Rimuovi', 'pap-default-featured-image'); ?>
                        </button>
                    </div>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function assign_default_featured_image(int $post_id, WP_Post $post, bool $update): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($post_id) || 'revision' === $post->post_type) {
            return;
        }

        if (get_post_thumbnail_id($post_id)) {
            return;
        }

        $default_image_id = absint(get_option(self::OPTION_NAME));

        if (! $default_image_id) {
            return;
        }

        set_post_thumbnail($post_id, $default_image_id);
    }
}

new PAP_Default_Featured_Image();
