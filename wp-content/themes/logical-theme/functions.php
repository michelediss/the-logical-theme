<?php

// ===================================================
// Parent Theme Setup
// ===================================================

/**
 * Logical Theme Setup
 */
function logical_theme_setup()
{
    // Enable support for featured images
    add_theme_support('post-thumbnails');

    // Enable support for the title tag
    add_theme_support('title-tag');

    // Enable support for HTML5 elements
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    // Register navigation menus
    register_nav_menus(array(
        'main-menu' => __('Main Menu', 'the_logical_theme'),
        'footer-menu' => __('Footer Menu', 'the_logical_theme'),
    ));
}

add_action('after_setup_theme', 'logical_theme_setup');

/**
 * Relocate templates to the theme folder
 */
function relocate()
{
    $predefined_names = [
        '404',
        'archive',
        'attachment',
        'author',
        'category',
        'date',
        'embed',
        'frontpage',
        'home',
        'index',
        'page',
        'paged',
        'privacypolicy',
        'search',
        'single',
        'singular',
        'tag',
        'taxonomy',
    ];

    foreach ($predefined_names as $type) {
        add_filter("{$type}_template_hierarchy", function ($templates) {
            return array_map(function ($template_name) {
                return "templates/$template_name";
            }, $templates);
        });
    }
}

relocate();


// ===================================================
// Bootstrap Navigation Walker
// ===================================================

/**
 * Register the Custom Navigation Walker
 */
function register_navwalker()
{
    require_once get_template_directory() . '/partials/class-wp-bootstrap-navwalker.php';
}
add_action('after_setup_theme', 'register_navwalker');

/**
 * Customize Bootstrap dropdowns data attributes
 *
 * @param array    $atts HTML attributes for the <a> element
 * @param WP_Post  $item Current menu item
 * @param stdClass $args Menu arguments
 * @return array
 */
function prefix_bs5_dropdown_data_attribute($atts, $item, $args)
{
    if (is_a($args->walker, 'WP_Bootstrap_Navwalker')) {
        if (array_key_exists('data-toggle', $atts)) {
            unset($atts['data-toggle']);
            $atts['data-bs-toggle'] = 'dropdown';
        }
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'prefix_bs5_dropdown_data_attribute', 20, 3);

/**
 * Add custom classes to menu items
 */
function add_custom_menu_item_classes($classes, $item, $args)
{
    if (isset($args->item_class)) {
        $classes[] = $args->item_class;
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'add_custom_menu_item_classes', 10, 3);

/**
 * Add custom classes to menu links
 */
function add_custom_menu_link_classes($atts, $item, $args, $depth)
{
    $extra_class = null;

    if ($depth > 0 && isset($args->dropdown_link_class)) {
        $extra_class = $args->dropdown_link_class;
    } elseif (isset($args->link_class)) {
        $extra_class = $args->link_class;
    }

    if ($extra_class) {
        $atts['class'] = isset($atts['class']) ? $atts['class'] . ' ' . $extra_class : $extra_class;
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_custom_menu_link_classes', 10, 4);


// ===================================================
// CSS, JS & Libraries
// ===================================================

/**
 * Enqueue main CSS and JS files
 */
function logical_theme_enqueue_scripts()
{
    // Enqueue Bootstrap Icons CSS if enabled in options
    if (get_option('include_bootstrap_icons')) {
        wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css', array(), '1.8.3');
    }

    // Enqueue Swiper CSS (kept outside the Sass build due to nesting syntax)
    $swiper_css_path = get_template_directory() . '/assets/css/swiper/swiper-bundle.min.css';
    if (file_exists($swiper_css_path)) {
        wp_enqueue_style(
            'swiper-bundle',
            get_template_directory_uri() . '/assets/css/swiper/swiper-bundle.min.css',
            array(),
            (string) filemtime($swiper_css_path)
        );
    }
}

add_action('wp_enqueue_scripts', 'logical_theme_enqueue_scripts');


// ===================================================
// Enqueue Admin Styles
// ===================================================

/**
 * Enqueue custom admin styles.
 */
function logical_theme_enqueue_admin_styles()
{
    $child_admin_css = get_stylesheet_directory() . '/assets/css/admin-style.css';
    $admin_css_uri = get_template_directory_uri() . '/assets/css/admin-style.css';

    if (file_exists($child_admin_css)) {
        $admin_css_uri = get_stylesheet_directory_uri() . '/assets/css/admin-style.css';
    }

    wp_enqueue_style(
        'logical-theme-admin-style',
        $admin_css_uri,
        array(),
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'logical_theme_enqueue_admin_styles');


// ===================================================
// Partials
// ===================================================

/**
 * Include partial files from the parent theme if they exist
 */
function logical_theme_include_partials()
{
    $partials = [
        'custom-excerpt.php',
        'limit-upload-mimes.php',
        'limit-jpg-upload-size.php',
        'bs-icons.php'
    ];

    foreach ($partials as $partial) {
        $partial_path = get_template_directory() . '/partials/' . $partial;
        if (file_exists($partial_path)) {
            include_once $partial_path;
        } else {
            error_log("Partial {$partial} not found.");
        }
    }

    // Include theme settings file if it exists
    $settings_path = get_template_directory() . '/settings/settings-page.php';
    if (file_exists($settings_path)) {
        include_once $settings_path;
    } else {
        error_log('Settings-page.php file not found.');
    }
}

add_action('after_setup_theme', 'logical_theme_include_partials');

/**
 * Conditionally include snippets based on options
 */
function logical_theme_conditional_snippets()
{
    $snippets = [
        'snippet_hierarchical_tag' => 'partials/hierarchical-tag.php',
        'snippet_custom_excerpt' => 'partials/custom-excerpt.php',
        'snippet_disable_comment' => 'partials/disable-comment.php',
        'snippet_disable_emoji' => 'partials/disable-emoji.php',
        'snippet_disable_main_editor' => 'partials/disable-main-editor.php',
        'snippet_disable_notice' => 'partials/disable-notice.php',
        'snippet_disable_gutenberg' => 'partials/disable-gutenberg.php',
        'snippet_disable_jquery' => 'partials/disable-jquery.php'
    ];

    foreach ($snippets as $option => $file) {
        if (get_option($option)) {
            $file_path = get_template_directory() . '/' . $file;
            if (file_exists($file_path)) {
                include_once $file_path;
            } else {
                error_log("Snippet {$file} not found.");
            }
        }
    }

    // Always include role-based admin body class.
    $class_role_path = get_template_directory() . '/partials/class-role.php';
    if (file_exists($class_role_path)) {
        include_once $class_role_path;
    } else {
        error_log('Snippet partials/class-role.php not found.');
    }
}

add_action('after_setup_theme', 'logical_theme_conditional_snippets');


// ===================================================
// Components
// ===================================================

/**
 * Automatically include all PHP files in /components/ directory
 */
function include_all_component_files()
{
    $parent_components_dir = get_template_directory() . '/components/';
    $child_components_dir = get_stylesheet_directory() . '/components/';

    $component_files = [];
    $parent_files = [];
    $child_files = [];

    if (is_dir($parent_components_dir)) {
        $parent_files = glob($parent_components_dir . '*.php') ?: [];
    }

    if (is_dir($child_components_dir) && $child_components_dir !== $parent_components_dir) {
        $child_files = glob($child_components_dir . '*.php') ?: [];
    }

    foreach ($parent_files as $file) {
        $component_files[basename($file)] = $file;
    }

    foreach ($child_files as $file) {
        $component_files[basename($file)] = $file; // child overrides parent by name
    }

    ksort($component_files, SORT_STRING);

    foreach ($component_files as $file) {
        require_once $file;
    }
}

// Execute the function to include components
include_all_component_files();


// ===================================================
// Installation of the Logical Plugin Manager Plugin
// ===================================================

function install_logical_plugin_manager()
{
    // URL for the plugin zip file from GitHub
    $plugin_zip_url = 'https://github.com/michelediss/wp-logical-plugin-manager/archive/refs/heads/main.zip';
    $plugin_slug = 'wp-logical-plugin-manager-main';
    $plugin_main_file = 'logical-plugin-manager.php';
    $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;

    if (!function_exists('request_filesystem_credentials')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // Check if the plugin is already active
    if (function_exists('is_plugin_active') && is_plugin_active($plugin_slug . '/' . $plugin_main_file)) {
        error_log('Logical Plugin Manager plugin is already active.');
        return;
    }
    WP_Filesystem();

    global $wp_filesystem;

    // Download the plugin zip file
    $zip_file = download_url($plugin_zip_url);

    if (is_wp_error($zip_file)) {
        error_log('Error downloading plugin zip file: ' . $zip_file->get_error_message());
        return;
    }

    // Use a temporary directory for extraction
    $temp_dir = sys_get_temp_dir() . '/wp-plugin-installer-logical';
    if (!$wp_filesystem->mkdir($temp_dir) && !is_dir($temp_dir)) {
        error_log('Error creating temporary directory for plugin extraction.');
        @unlink($zip_file);
        return;
    }
    error_log('Temporary directory created for plugin extraction.');

    // Extract the zip file to the temporary directory
    $result = unzip_file($zip_file, $temp_dir);

    if (is_wp_error($result)) {
        error_log('Error extracting plugin zip file: ' . $result->get_error_message());
        @unlink($zip_file);
        $wp_filesystem->delete($temp_dir, true);
        return;
    }
    error_log('Plugin zip file extracted.');

    // Set source and destination paths
    $source = $temp_dir . '/' . $plugin_slug;
    if (!is_dir($source)) {
        error_log("Extracted plugin directory does not exist: $source");
        @unlink($zip_file);
        $wp_filesystem->delete($temp_dir, true);
        return;
    }

    $destination = WP_PLUGIN_DIR . '/' . $plugin_slug;

    // Move the plugin to the WordPress plugins directory
    if (!$wp_filesystem->move($source, $destination, true)) {
        error_log('Error moving the plugin to the plugins directory.');
        @unlink($zip_file);
        $wp_filesystem->delete($temp_dir, true);
        return;
    }
    error_log('Plugin successfully moved to plugins directory.');

    // Clean up temporary files
    $wp_filesystem->delete($temp_dir, true);
    @unlink($zip_file);
    error_log('Temporary files cleaned up.');

    if (!function_exists('activate_plugin')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // Activate the plugin
    $plugin_main_file_path = $destination . '/' . $plugin_main_file;
    if (file_exists($plugin_main_file_path)) {
        $activate_result = activate_plugin($plugin_slug . '/' . $plugin_main_file);

        if (is_wp_error($activate_result)) {
            error_log('Error activating plugin: ' . $activate_result->get_error_message());
        } else {
            error_log('Logical Plugin Manager activated successfully.');
        }
    } else {
        error_log("Plugin main file does not exist: $plugin_main_file_path");
    }

    return true;
}


// ===================================================
// Creation and Activation of the Child Theme
// ===================================================

$functions_php_content = "<?php\n\n// Child theme functions\n";

function logical_create_and_activate_child_theme()
{
    $parent_theme = 'the-logical-theme'; // Parent theme folder name
    $child_theme = 'the-logical-theme-child'; // Child theme folder name
    $child_theme_dir = get_theme_root() . '/' . $child_theme;
    $functions_php_content = "<?php
        function logical_theme_child_enqueue_styles() {
            wp_enqueue_style( 'the-logical-theme-style', get_template_directory_uri() . '/style.css' );
            wp_enqueue_style( 'the-logical-theme-child-style', get_stylesheet_directory_uri() . '/style.css', array( 'logical-theme-style' ) );
        }
        add_action( 'wp_enqueue_scripts', 'logical_theme_child_enqueue_styles' );
    ?>";

    // Check if child theme already exists
    if (is_dir($child_theme_dir)) {
        error_log('Child theme directory already exists');
        return;
    }

    // Create child theme folder
    if (!wp_mkdir_p($child_theme_dir)) {
        error_log('Failed to create child theme directory');
        return;
    }
    error_log('Child theme directory created');

    // Write the style.css file for child theme
    $style_css_content = "
    /*
    Theme Name: The Logical Theme Child
    Template: $parent_theme
    Text Domain: the_logical_theme_child
    */
    ";

    if (!file_put_contents($child_theme_dir . '/style.css', $style_css_content)) {
        error_log('Failed to create style.css for child theme');
        return;
    }
    error_log('style.css created for child theme');

    // Write the functions.php file for child theme
    if (!file_put_contents($child_theme_dir . '/functions.php', $functions_php_content)) {
        error_log('Failed to create functions.php for child theme');
        return;
    }
    error_log('functions.php created for child theme');

    // Function to recursively copy files and folders
    function logical_copy_recursive($source, $destination)
    {
        $dir = opendir($source);
        @mkdir($destination, 0755, true);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($source . '/' . $file)) {
                    logical_copy_recursive($source . '/' . $file, $destination . '/' . $file);
                } else {
                    if (!copy($source . '/' . $file, $destination . '/' . $file)) {
                        error_log("Failed to copy {$file} to child theme");
                    } else {
                        error_log("{$file} copied to child theme");
                    }
                }
            }
        }
        closedir($dir);
    }

    // Copy specific files and folders from the parent theme to the child theme
    $files_to_copy = ['index.php', 'header.php', 'footer.php'];
    $folders_to_copy = ['template-parts', 'assets', 'components', 'templates'];

    foreach ($files_to_copy as $file) {
        $source = get_template_directory() . '/' . $file;
        $destination = $child_theme_dir . '/' . $file;
        if (file_exists($source)) {
            if (!copy($source, $destination)) {
                error_log("Failed to copy {$file} to child theme");
            } else {
                error_log("{$file} copied to child theme");
            }
        } else {
            error_log("File {$file} not found in the parent theme");
        }
    }

    foreach ($folders_to_copy as $folder) {
        $source = get_template_directory() . '/' . $folder;
        $destination = $child_theme_dir . '/' . $folder;
        if (is_dir($source)) {
            logical_copy_recursive($source, $destination);
            error_log("Folder {$folder} copied to child theme");
        } else {
            error_log("Folder {$folder} not found in the parent theme");
        }
    }

    // Activate the child theme
    switch_theme($child_theme);
    error_log('Child theme activated');

    return true;
}


// ===================================================
// Options Page
// ===================================================

// Add direct link to ACF page in admin menu
add_action('admin_menu', 'linked_url');
function linked_url()
{
    $post_id = 1;

    // Check if user has permission to edit the post
    if (current_user_can('edit_post', $post_id)) {
        add_menu_page(
            'Linked URL',
            'Options',
            'edit_posts',
            'post.php?post=' . $post_id . '&action=edit',
            '',
            'dashicons-admin-links',
            90
        );
    }
}

// Exclude a specific page from admin page list
add_filter('parse_query', 'exclude_page_from_admin');
function exclude_page_from_admin($query)
{
    global $pagenow, $post_type;

    if (is_admin() && $pagenow == 'edit.php' && $post_type == 'page') {
        $settings_page = get_page_by_path("options", NULL, "page")->ID;

        $query->query_vars['post__not_in'] = array($settings_page);
    }
}

// Remove all metaboxes except ACF fields and "Publish/Update" for page with ID 1
add_action('add_meta_boxes', 'keep_only_acf_and_publish_metabox', 99);
function keep_only_acf_and_publish_metabox()
{
    global $post;

    if (isset($post->ID) && $post->ID == 1) {
        remove_meta_box('slugdiv', 'page', 'normal');
        remove_meta_box('pageparentdiv', 'page', 'side');
        remove_meta_box('postimagediv', 'page', 'side');
        remove_meta_box('commentsdiv', 'page', 'normal');
        remove_meta_box('revisionsdiv', 'page', 'normal');
    }
}

// Hide classic editor for page ID 1, keeping "Publish" box
add_action('admin_head', 'hide_editor_for_acf_page_with_publish');
function hide_editor_for_acf_page_with_publish()
{
    global $pagenow, $post;

    if ($pagenow == 'post.php' && isset($post->ID) && $post->ID == 1) {
        echo '<style>
            #postdivrich, #titlediv, #slugdiv, #postimagediv, #pageparentdiv, .misc-pub-section {
                display: none !important;
            }
        </style>';
    }
}


// ===================================================
// JS setup
// ===================================================

/**
 * Minify JavaScript content
 *
 * @param string $content JavaScript content
 * @return string Minified JavaScript content
 */
function minify_js_content($content)
{
    $content = preg_replace('#/\*[^*]*\*+([^/][^*]*\*+)*/#', '', $content);
    $content = preg_replace('#(?<!:)//[^\n\r]*#', '', $content);
    $content = preg_replace('/\s+/', ' ', $content);
    $content = preg_replace('/\s?([{};,:])\s?/', '$1', $content);
    return trim($content);
}

/**
 * Combine and minify all JavaScript files
 */
function combine_and_minify_js()
{
    $directories = [
        'libs' => get_stylesheet_directory() . '/assets/js/libs',
        'partials' => get_stylesheet_directory() . '/assets/js/partials',
    ];
    $combined_js = '';

    foreach ($directories as $dir_path) {
        if (is_dir($dir_path)) {
            $files = scandir($dir_path);
            sort($files);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'js') {
                    $file_content = file_get_contents($dir_path . '/' . $file);
                    $combined_js .= minify_js_content($file_content) . "\n";
                }
            }
        }
    }

    $output_path = get_stylesheet_directory() . '/assets/js/main.min.js';
    file_put_contents($output_path, $combined_js);

    return get_stylesheet_directory_uri() . '/assets/js/main.min.js';
}

/**
 * Handle AJAX minify JS action
 */
function handle_ajax_minify_js_action()
{
    if (!current_user_can('manage_options') || !check_ajax_referer('minify_js_nonce', 'security', false)) {
        wp_send_json_error('Unauthorized.');
    }

    combine_and_minify_js();
    wp_send_json_success('JS successfully minified.');
}
add_action('wp_ajax_minify_js', 'handle_ajax_minify_js_action');

/**
 * Add "Minify JS" button to the admin bar with AJAX trigger
 */
function add_minify_js_button($wp_admin_bar)
{
    if (current_user_can('manage_options')) {
        $args = array(
            'id' => 'minify_js_button',
            'title' => 'Minify JS',
            'href' => '#',
            'meta' => array('class' => 'minify-js-button', 'onclick' => 'triggerMinifyJS(event)')
        );
        $wp_admin_bar->add_node($args);
    }
}
add_action('admin_bar_menu', 'add_minify_js_button', 100);

/**
 * Enqueue the main JavaScript file
 */
function enqueue_main_script()
{
    $stylesheet_directory = get_stylesheet_directory();
    $stylesheet_directory_uri = get_stylesheet_directory_uri();

    if (file_exists($stylesheet_directory . '/assets/js/main.min.js')) {
        wp_enqueue_script('main-js', $stylesheet_directory_uri . '/assets/js/main.min.js', array(), '1.0', true);
    } else {
        wp_enqueue_script('main-js', $stylesheet_directory_uri . '/assets/js/main.js', array(), '1.0', true);

        $directories = [
            'libs' => $stylesheet_directory . '/assets/js/libs',
            'partials' => $stylesheet_directory . '/assets/js/partials',
        ];
        $base_uri = $stylesheet_directory_uri . '/assets/js/';
        $file_list = array();

        foreach ($directories as $dir_key => $dir_path) {
            if (is_dir($dir_path)) {
                $files = scandir($dir_path);
                sort($files);
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'js') {
                        $file_list[] = $base_uri . $dir_key . '/' . $file;
                    }
                }
            }
        }

        wp_localize_script('main-js', 'partialFiles', $file_list);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_main_script');

/**
 * Enqueue admin script for Minify JS button
 */
function enqueue_admin_minify_js_script()
{
    wp_enqueue_script(
        'admin-minify-js',
        get_stylesheet_directory_uri() . '/assets/js/minify-js.js',
        array(),
        '1.0',
        true
    );

    wp_localize_script('admin-minify-js', 'minify_js_data', array(
        'nonce' => wp_create_nonce('minify_js_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_admin_minify_js_script');










