<?php
/**
 * Custom excerpt.
 *
 * @param string $content The content to extract.
 * @param int    $length  The length of the excerpt.
 * @return string
 */
function logical_get_custom_excerpt($content, $length = null) {
    if ( is_null($length) ) {
        $length = get_option('custom_excerpt_length', 55);
    }
    $content = wp_strip_all_tags($content); // Rimuove tutti i tag HTML.
    $words = explode(' ', $content, $length + 1);
    if ( count($words) > $length ) {
        array_pop($words);
        $words[] = '...';
    }
    return implode(' ', $words);
}