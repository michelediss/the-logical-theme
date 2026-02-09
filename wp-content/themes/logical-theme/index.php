<?php
// index.php - Main template file for your WordPress theme

// Define the path to the page-home template
$template_path = 'app/public/wp-content/themes/logical-theme/templates/front-page.php';

// Check if the template exists, and load it if available
if (file_exists(get_theme_file_path($template_path))) {
    include get_theme_file_path($template_path);
} else {
    // Fallback content if page-home.php is not found
    echo '<p>Template page-home.php not found. Please check the path and file existence.</p>';
}
?>
