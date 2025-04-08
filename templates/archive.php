<?php
// index.php – Main template file for your WordPress theme

// Define the path to the page-home template inside the active theme
$template_path = get_stylesheet_directory() . '/templates/home.php';

// Check if the template exists, and load it if available
if ( file_exists( $template_path ) ) {
    include $template_path;
} else {
    echo '<p>Template templates/home.php non trovato. Controlla percorso ed esistenza del file.</p>';
}
?>
