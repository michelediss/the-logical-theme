<?php
/**
 * Function to create a customizable Bootstrap button
 *
 * @param string $text Button text
 * @param string $url Button link URL
 * @param string $color Button color class (default is 'primary')
 * @param string $class Additional CSS classes for further customization
 * @param bool $is_block Set to true for a block-level button
 * @return string HTML code for the Bootstrap button
 */
function secondary_button($text = 'Click me', $url = '#', $color = 'primary', $class = '', $is_block = false) {
    // Escape attributes for security
    $text = esc_html($text);
    $url = esc_url($url);
    $color_class = 'btn-' . esc_attr($color);
    $block_class = $is_block ? ' btn-block' : '';
    $class_attr = esc_attr($class);

    // Build the button HTML
    $button_html = '<a href="' . $url . '" class="btn ' . $color_class . $block_class . ' ' . $class_attr . '">';
    $button_html .= $text;
    $button_html .= '</a>';

    return $button_html;
}
?>
