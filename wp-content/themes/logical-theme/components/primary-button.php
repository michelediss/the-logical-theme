<?php
/**
 * Centralized primary button for the child theme.
 *
 * @param string $text Button text
 * @param string $url Button link URL
 * @param string $class Additional CSS classes
 * @param string $aria_label Optional aria-label text
 * @param bool $is_block Set to true for a block-level button
 * @param string $padding_class Bootstrap padding classes (e.g. 'px-4 py-2')
 * @param string $font_size_class Font size utility class (e.g. 'text-base')
 * @param string $text_color_class Text color class
 * @param string $bg_color_class Background color class
 * @param string $border_color_class Border color class
 * @param string $hover_text_color_class Hover text color class
 * @param string $hover_bg_color_class Hover background color class
 * @param string $hover_border_color_class Hover border color class
 * @param string $extra_attrs Additional HTML attributes
 * @return string
 */
if (!function_exists('logical_primary_button')) {
    function logical_primary_button(
        $text = 'Click me',
        $url = '#',
        $class = '',
        $aria_label = '',
        $is_block = false,
        $padding_class = 'px-4 py-2',
        $font_size_class = 'text-base',
        $text_color_class = 'text-primary',
        $bg_color_class = 'bg-white',
        $border_color_class = 'border-primary',
        $hover_text_color_class = 'hover-text-white',
        $hover_bg_color_class = 'hover-bg-primary',
        $hover_border_color_class = 'hover-border-primary',
        $extra_attrs = ''
    ) {
        $text = esc_html($text);
        $url = esc_url($url);
        $block_class = $is_block ? ' btn-block' : '';
        $class_attr = esc_attr(trim(
            'button heading text-uppercase rounded-pill text-decoration-none ' .
            $padding_class . ' ' .
            $font_size_class . ' ' .
            $text_color_class . ' ' .
            $bg_color_class . ' ' .
            $border_color_class . ' ' .
            $hover_text_color_class . ' ' .
            $hover_bg_color_class . ' ' .
            $hover_border_color_class . ' ' .
            $block_class . ' ' .
            $class
        ));
        $aria_attr = $aria_label ? ' aria-label="' . esc_attr($aria_label) . '"' : '';
        $extra_attr = $extra_attrs ? ' ' . trim($extra_attrs) : '';

        return '<a href="' . $url . '" class="' . $class_attr . '"' . $aria_attr . $extra_attr . '>' . $text . '</a>';
    }
}

if (!function_exists('primary_button')) {
    /**
     * Backward-compatible wrapper for parent templates.
     *
     * @param string $text
     * @param string $url
     * @param string $color Unused in child theme but kept for signature compatibility
     * @param string $class
     * @param bool $is_block
     * @return string
     */
    function primary_button($text = 'Click me', $url = '#', $color = 'primary', $class = '', $is_block = false) {
        return logical_primary_button($text, $url, $class, '', $is_block);
    }
}
?>
