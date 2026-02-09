<?php
/**
 * Function to create a spacer div with customizable height in rem
 *
 * @param float $height Height of the spacer in rem (default is 1rem)
 * @return string HTML code for the spacer div
 */
function spacer($height = 1) {
    // Ensure height is sanitized as a numeric value
    $height = floatval($height);

    // Build the spacer div HTML
    $spacer_html = '<div style="height: ' . esc_attr($height) . 'rem;"></div>';

    return $spacer_html;
}
?>
