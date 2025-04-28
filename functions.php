<?php
// ===================================================
// Parent Theme Setup
// ===================================================

/**
 * Logical Theme Setup
 */
function logical_theme_setup()
{
    // Load textdomain
    load_theme_textdomain( 'the-logical-theme', get_template_directory() . '/languages' );

    // Enable support for featured images
    add_theme_support('post-thumbnails');

    // Enable support for the title tag
    add_theme_support('title-tag');

    // Enable support for HTML5 elements
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    // Register navigation menus
    register_nav_menus(array(
        'main-menu' => __('Main Menu', 'the-logical-theme'),
        'footer-menu' => __('Footer Menu', 'the-logical-theme'),
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
function add_custom_menu_link_classes($atts, $item, $args)
{
    if (isset($args->link_class)) {
        $atts['class'] = isset($atts['class']) ? $atts['class'] . ' ' . $args->link_class : $args->link_class;
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_custom_menu_link_classes', 10, 3);


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
}

add_action('wp_enqueue_scripts', 'logical_theme_enqueue_scripts');


// ===================================================
// Enqueue Admin Styles
// ===================================================

/**
 * Enqueue custom admin styles.
 */
function the_logical_theme_enqueue_admin_styles()
{
    wp_enqueue_style(
        'the-logical-theme-admin-style',
        get_template_directory_uri() . '/assets/css/admin-style.css',
        array(),
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'the_logical_theme_enqueue_admin_styles');


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
        'limit-jpg-upload-size.php'
    ];

    foreach ($partials as $partial) {
        $partial_path = get_template_directory() . '/partials/' . $partial;
        if (file_exists($partial_path)) {
            include_once $partial_path;
        }
    }

    // Include theme settings file if it exists
    $settings_path = get_template_directory() . '/settings/settings-page.php';
    if (file_exists($settings_path)) {
        include_once $settings_path;
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
        'snippet_disable_notice' => 'partials/disable-notice.php',
        'snippet_disable_gutenberg' => 'partials/disable-gutenberg.php',
        'snippet_disable_classic_editor' => 'partials/disable-classic-editor.php',
        'snippet_disable_jquery' => 'partials/disable-jquery.php',
        'snippet_class_role' => 'partials/class-role.php'
    ];

    foreach ($snippets as $option => $file) {
        if (get_option($option)) {
            $file_path = get_template_directory() . '/' . $file;
            if (file_exists($file_path)) {
                include_once $file_path;
            }
        }
    }
}

add_action('after_setup_theme', 'logical_theme_conditional_snippets');



// ===================================================
// Components
// ===================================================

/**
 * Automatically include all PHP files in /components/ directory
 */
function include_all_component_files() {
    $child_dir  = get_stylesheet_directory() . '/components/';
    $parent_dir = get_template_directory() . '/components/';

    // Carica i file del parent, ma se esiste una versione nel child, includi quella.
    $parent_files = glob($parent_dir . '*.php');
    foreach ($parent_files as $file) {
        $basename = basename($file);
        if ( file_exists( $child_dir . $basename ) ) {
            require_once $child_dir . $basename;
        } else {
            require_once $file;
        }
    }

    // Carica eventuali file presenti nel child che non esistono nel parent
    if ( is_dir( $child_dir ) ) {
        $child_files = glob( $child_dir . '*.php' );
        foreach ( $child_files as $file ) {
            $basename = basename( $file );
            if ( ! file_exists( $parent_dir . $basename ) ) {
                require_once $file;
            }
        }
    }
}

add_action('after_setup_theme', 'include_all_component_files');


// ===================================================
// Installation of the Logical Plugin Manager 
// ===================================================

function install_logical_plugin_manager() {
    $plugin_zip_url   = 'https://github.com/michelediss/wp-logical-plugin-manager/releases/latest/download/logical-plugin-manager.zip';
    $plugin_slug      = 'logical-plugin-manager';
    $plugin_main_file = 'logical-plugin-manager.php';
    $plugin_dir       = WP_PLUGIN_DIR . '/' . $plugin_slug;

    if (!function_exists('request_filesystem_credentials')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    if (is_plugin_active($plugin_slug . '/' . $plugin_main_file)) {
        return;
    }

    WP_Filesystem();
    global $wp_filesystem;

    $zip_file = download_url($plugin_zip_url);
    if (is_wp_error($zip_file)) {
        return;
    }

    $temp_dir = sys_get_temp_dir() . '/wp-plugin-installer-logical';
    if (!$wp_filesystem->mkdir($temp_dir) && !is_dir($temp_dir)) {
        @unlink($zip_file);
        return;
    }

    $result = unzip_file($zip_file, $temp_dir);
    if (is_wp_error($result)) {
        @unlink($zip_file);
        $wp_filesystem->delete($temp_dir, true);
        return;
    }

    $source      = $temp_dir . '/' . $plugin_slug;
    $destination = WP_PLUGIN_DIR . '/' . $plugin_slug;

    if (!copy_dir($source, $destination)) {
        @unlink($zip_file);
        $wp_filesystem->delete($temp_dir, true);
        return;
    }

    $wp_filesystem->delete($temp_dir, true);
    @unlink($zip_file);

    if (file_exists($destination . '/' . $plugin_main_file)) {
        $activate_result = activate_plugin($plugin_slug . '/' . $plugin_main_file);
        if (is_wp_error($activate_result)) {
            return;
        }
    }
}


// ===================================================
// Creation and Activation of the Child Theme
// ===================================================

function logical_create_and_activate_child_theme()
{
    $parent_theme = 'the-logical-theme'; // Nome della cartella del tema genitore
    $child_theme = 'the-logical-theme-child'; // Nome della cartella del tema child
    $child_theme_dir = get_theme_root() . '/' . $child_theme;
    $functions_php_content = "<?php\n\n// Funzioni del tema child\n";

    // Checks whether the child theme already exists
    if (is_dir($child_theme_dir)) {
        return;
    }

    // Create the child theme folder
    if (!wp_mkdir_p($child_theme_dir)) {
        return;
    }

    // Writes the style.css file for the child theme with the minimal header
    $style_css_content = "
    /*
    Theme Name: The Logical Theme Child
    Template: $parent_theme
    Text Domain: the-logical-theme-child
    */
    ";

    if (!file_put_contents($child_theme_dir . '/style.css', $style_css_content)) {
        return;
    }

    // Writes the functions.php file for the child theme
    if (!file_put_contents($child_theme_dir . '/functions.php', $functions_php_content)) {
        return;
    }

    // Function for recursively copying files and folders
    function logical_copy_recursive($source, $destination)
    {
        $dir = opendir($source);
        @mkdir($destination, 0755, true);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($source . '/' . $file)) {
                    logical_copy_recursive($source . '/' . $file, $destination . '/' . $file);
                } else {
                    copy($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    // Copy specific files and folders from the parent theme to the child theme
    $files_to_copy = ['index.php', 'header.php', 'footer.php'];
    $folders_to_copy = ['template-parts', 'assets', 'components', 'templates', 'pods'];

    foreach ($files_to_copy as $file) {
        $source = get_template_directory() . '/' . $file;
        $destination = $child_theme_dir . '/' . $file;
        if (file_exists($source)) {
            copy($source, $destination);
        }
    }

    foreach ($folders_to_copy as $folder) {
        $source = get_template_directory() . '/' . $folder;
        $destination = $child_theme_dir . '/' . $folder;
        if (is_dir($source)) {
            logical_copy_recursive($source, $destination);
        }
    }

    // Activate child theme
    switch_theme($child_theme);
    
    return true;
}

// Call the function to create and activate the child theme
logical_create_and_activate_child_theme();


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
