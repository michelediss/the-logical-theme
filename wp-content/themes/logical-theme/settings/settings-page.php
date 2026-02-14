<?php
// Add the settings page to the admin menu
function add_theme_settings_page()
{
    add_menu_page(
        'Logical Theme Settings', // Page title
        'Theme Settings', // Menu title
        'manage_options', // Required capability
        'theme-settings', // Menu slug
        'render_theme_settings_page' // Callback function
    );
}
add_action('admin_menu', 'add_theme_settings_page');

// Render the settings page
function render_theme_settings_page()
{

    // Handle form submissions for installing plugin and creating child theme
    if (isset($_POST['install_plugin_button'])) {
        check_admin_referer('theme_settings_nonce_action', 'theme_settings_nonce_field');
        $result = install_logical_plugin_manager();
        if ($result === true) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-success is-dismissible"><p>Logical Plugin Manager installed successfully.</p></div>';
            });
        } else {
            add_action('admin_notices', function () use ($result) {
                echo '<div class="notice notice-error is-dismissible"><p>Error installing Logical Plugin Manager: ' . esc_html($result) . '</p></div>';
            });
        }
    }

    if (isset($_POST['create_child_theme_button'])) {
        check_admin_referer('theme_settings_nonce_action', 'theme_settings_nonce_field');
        $result = logical_create_and_activate_child_theme();
        if ($result === true) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-success is-dismissible"><p>Child theme created and activated successfully.</p></div>';
            });
        } else {
            add_action('admin_notices', function () use ($result) {
                echo '<div class="notice notice-error is-dismissible"><p>Error creating and activating child theme: ' . esc_html($result) . '</p></div>';
            });
        }
    }

    // Get all pages
    $pages = get_pages();
    // Get selected pages to disable the editor
    $selected_pages = get_option('disable_editor_pages', []);
    // Get the custom excerpt length value
    $custom_excerpt_length = get_option('custom_excerpt_length', 55);
    // Get the maximum JPG upload size value
    $max_jpg_upload_size_kb = get_option('max_jpg_upload_size_kb', 500); // Default value: 500 KB
    // Get the allowed file extensions
    $allowed_file_extensions = get_option('allowed_file_extensions', 'jpg,jpeg,png,gif,svg'); // Default value

    // Get options for snippets
    $snippet_disable_notice = get_option('snippet_disable_notice', 0);
    $snippet_disable_comment = get_option('snippet_disable_comment', 0);
    $snippet_disable_jquery = get_option('snippet_disable_jquery', 0);
    $snippet_disable_emoji = get_option('snippet_disable_emoji', 0);
    $snippet_disable_gutenberg = get_option('snippet_disable_gutenberg', 0);
    $snippet_hierarchical_tag = get_option('snippet_hierarchical_tag', 0);

    ?>
    <div class="wrap">
        <h1>Theme Settings</h1>

        <!-- Form for installing plugin -->
        <form method="post" action="">
            <?php wp_nonce_field('theme_settings_nonce_action', 'theme_settings_nonce_field'); ?>
            <p>
                <input type="submit" name="install_plugin_button" value="Install Logical Plugin Manager"
                    class="button button-primary" />
            </p>
        </form>

        <!-- Form for creating child theme -->
        <form method="post" action="">
            <?php wp_nonce_field('theme_settings_nonce_action', 'theme_settings_nonce_field'); ?>
            <p>
                <input type="submit" name="create_child_theme_button" value="Create and Activate Child Theme"
                    class="button button-secondary" />
            </p>
        </form>

        <form method="post" action="options.php">
            <?php settings_fields('theme_settings_group'); ?>

            <!-- No Enqueue Section -->
            <div class="collapsible-section">
                <h2 class="section-title">Libraries</h2>
                <div class="section-content">
                    <h3>Disable default libraries</h3>
                    <div class="no-enqueue-options">
                        <div class="select-all-checkbox">
                            <input type="checkbox" id="select_all_no_enqueue_snippets" />
                            <label for="select_all_no_enqueue_snippets">Select All</label>
                        </div>
                        <div class="snippet-options-container">
                            <?php
                            // Snippets for 'No Enqueue'
                            $snippets_no_enqueue = [
                                'disable_jquery' => 'Disable jQuery',
                                'disable_emoji' => 'Disable Emojis',
                                'disable_gutenberg' => 'Disable Gutenberg CSS',
                            ];
                            foreach ($snippets_no_enqueue as $slug => $label) {
                                $option = get_option('snippet_' . $slug);
                                ?>
                                <label>
                                    <input type="checkbox" name="<?php echo 'snippet_' . $slug; ?>" value="1" <?php checked(1, $option, true); ?> />
                                    <?php echo $label; ?>
                                </label>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Disable Section -->
            <div class="collapsible-section">
                <h2 class="section-title">Disable functions</h2>
                <div class="section-content">
                    <div class="select-all-checkbox">
                        <input type="checkbox" id="select_all_disable" />
                        <label for="select_all_disable">Select All</label>
                    </div>
                    <div class="snippet-options-container">
                        <?php
                        // Snippets for 'Disable'
                        $snippets_disable = [
                            'disable_comment' => 'Disable Comments',
                            'disable_notice' => 'Disable Notice',
                        ];
                        foreach ($snippets_disable as $slug => $label) {
                            $option = get_option('snippet_' . $slug);
                            ?>
                            <label>
                                <input type="checkbox" name="<?php echo 'snippet_' . $slug; ?>" value="1" <?php checked(1, $option, true); ?> />
                                <?php echo $label; ?>
                            </label>
                            <?php
                        }
                        ?>
                    </div>
                    <h3>Disable Main Editor on Pages</h3>
                    <select name="disable_editor_pages[]" multiple size="10" style="width: 100%;">
                        <?php
                        foreach ($pages as $page) {
                            echo '<option value="' . esc_attr($page->ID) . '"' . selected(in_array($page->ID, $selected_pages), true, false) . '>' . esc_html($page->post_title) . '</option>';
                        }
                        ?>
                    </select>
                    <p>Select the pages for which you want to disable the main editor.</p>
                </div>
            </div>

            <!-- Upload Settings Section -->
            <div class="collapsible-section">
                <h2 class="section-title">Upload Settings</h2>
                <div class="section-content">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="max_jpg_upload_size_kb">Maximum JPG Upload Size (KB)</label></th>
                            <td>
                                <input type="number" id="max_jpg_upload_size_kb" name="max_jpg_upload_size_kb"
                                    value="<?php echo esc_attr($max_jpg_upload_size_kb); ?>" min="1" max="5000" />
                                <p class="description">Specify the maximum size for JPG image uploads in kilobytes (KB).</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="allowed_file_extensions">Allowed File Extensions</label></th>
                            <td>
                                <input type="text" id="allowed_file_extensions" name="allowed_file_extensions"
                                    value="<?php echo esc_attr($allowed_file_extensions); ?>" />
                                <p class="description">Enter allowed file extensions, separated by commas (e.g.,
                                    jpg,jpeg,png,gif,svg).</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Miscellaneous Section -->
            <div class="collapsible-section">
                <h2 class="section-title">Miscellaneous</h2>
                <div class="section-content">
                    <div class="select-all-checkbox">
                        <input type="checkbox" id="select_all_misc" />
                        <label for="select_all_misc">Select All</label>
                    </div>
                    <div class="snippet-options-container">
                        <?php
                        // Snippets for 'Miscellaneous'
                        $snippets_misc = [
                            'hierarchical_tag' => 'Hierarchical Tags',
                        ];
                        foreach ($snippets_misc as $slug => $label) {
                            $option = get_option('snippet_' . $slug);
                            ?>
                            <label>
                                <input type="checkbox" name="<?php echo 'snippet_' . $slug; ?>" value="1" <?php checked(1, $option, true); ?> />
                                <?php echo $label; ?>
                            </label>
                            <?php
                        }
                        ?>
                    </div>
                    <h3>Custom Excerpt Settings</h3>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><label for="custom_excerpt_length">Excerpt Length</label></th>
                            <td>
                                <input type="number" id="custom_excerpt_length" name="custom_excerpt_length"
                                    value="<?php echo esc_attr($custom_excerpt_length); ?>" min="1" max="1000" />
                                <p class="description">Specify the number of words for the custom excerpt.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php submit_button(); ?>
        </form>

        <style>
            .collapsible-section {
                margin-bottom: 20px;
                border: 1px solid #e5e5e5;
                border-radius: 4px;
            }

            .collapsible-section .section-title {
                background: #f1f1f1;
                padding: 10px;
                cursor: pointer;
                margin: 0;
            }

            .collapsible-section .section-content {
                display: none;
                padding: 10px;
                background: #fff;
            }

            .snippet-options-container {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .snippet-options-container label {
                display: inline-flex;
                align-items: center;
            }

            .select-all-checkbox {
                margin-bottom: 10px;
            }

            .select-all-checkbox label {
                font-weight: bold;
            }
        </style>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                // Handle collapsible sections
                const sectionTitles = document.querySelectorAll('.collapsible-section .section-title');
                sectionTitles.forEach(title => {
                    title.addEventListener('click', function () {
                        const content = this.nextElementSibling;
                        content.style.display = content.style.display === 'block' ? 'none' : 'block';
                    });
                });

                // Handle select all checkboxes
                function handleSelectAll(selectAllId, containerSelector) {
                    const selectAllCheckbox = document.getElementById(selectAllId);
                    const container = document.querySelector(containerSelector);
                    if (!selectAllCheckbox || !container) {
                        return;
                    }
                    const checkboxes = container.querySelectorAll('input[type="checkbox"][name]');
                    selectAllCheckbox.addEventListener('change', function () {
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });
                }

                // No Enqueue Snippets
                handleSelectAll('select_all_no_enqueue_snippets', '.no-enqueue-options .snippet-options-container');

                // Altre sezioni...
                handleSelectAll('select_all_disable', '.disable-options .snippet-options-container');
                handleSelectAll('select_all_misc', '.misc-options .snippet-options-container');
            });
        </script>

    </div>
    <?php
}

// Initialize settings
function initialize_theme_settings()
{

    register_setting('theme_settings_group', 'snippet_hierarchical_tag');
    register_setting('theme_settings_group', 'snippet_disable_comment');
    register_setting('theme_settings_group', 'snippet_disable_emoji');
    register_setting('theme_settings_group', 'snippet_disable_gutenberg');
    register_setting('theme_settings_group', 'snippet_disable_jquery');
    register_setting('theme_settings_group', 'snippet_disable_notice');

    // Register the new setting for selected pages
    register_setting('theme_settings_group', 'disable_editor_pages', [
        'sanitize_callback' => 'logical_sanitize_disable_editor_pages'
    ]);

    // Register the new setting for excerpt length
    register_setting('theme_settings_group', 'custom_excerpt_length', [
        'sanitize_callback' => 'logical_sanitize_custom_excerpt_length'
    ]);

    // Register the new setting for maximum JPG upload size
    register_setting('theme_settings_group', 'max_jpg_upload_size_kb', [
        'sanitize_callback' => 'logical_sanitize_max_jpg_upload_size_kb',
        'default' => 500
    ]);

    // Register the new setting for allowed file extensions
    register_setting('theme_settings_group', 'allowed_file_extensions', [
        'sanitize_callback' => 'logical_sanitize_allowed_file_extensions',
        'default' => 'jpg,jpeg,png,gif,svg'
    ]);
}
add_action('admin_init', 'initialize_theme_settings');

// Sanitize the disable_editor_pages input
function logical_sanitize_disable_editor_pages($input)
{
    if (!is_array($input)) {
        $input = [];
    }
    $sanitized = array_map('intval', $input);
    return $sanitized;
}

// Sanitize the custom_excerpt_length input
function logical_sanitize_custom_excerpt_length($input)
{
    $input = intval($input);
    if ($input < 1) {
        $input = 55; // Default value if input is less than 1
    }
    return $input;
}

// Sanitize the max_jpg_upload_size_kb input
function logical_sanitize_max_jpg_upload_size_kb($input)
{
    $input = intval($input);
    if ($input < 1) {
        $input = 500; // Default value if input is less than 1
    }
    return $input;
}

// Sanitize the allowed_file_extensions input
function logical_sanitize_allowed_file_extensions($input)
{
    // Remove any spaces and convert to lowercase
    $input = strtolower(trim($input));
    // Remove any spaces after commas
    $input = preg_replace('/\s*,\s*/', ',', $input);
    // Remove non-alphanumeric characters and commas
    $input = preg_replace('/[^a-z,]/', '', $input);
    return $input;
}
?>
