<?php
/**
 * Function to create a divider div with customizable height and width
 *
 * @param float $height Height of the spacer in px (default is 2px)
 * @param string|array $class Optional class or array of classes to add to the spacer div
 * @param float|null $width_percent Optional width in percent (e.g., 100 for 100%)
 * @param float|null $width_px Optional width in px (e.g., 200 for 200px) — overrides percent if provided
 * @return string HTML code for the spacer div
 *
 * Example usage:
 * echo divider(); // <div class="" style="height: 2px;"></div>
 * echo divider(10); // <div class="" style="height: 10px;"></div>
 * echo divider(20, 'my-divider'); // <div class="my-divider" style="height: 20px;"></div>
 * echo divider(30, ['spacer', 'mb-4']); // <div class="spacer mb-4" style="height: 30px;"></div>
 * echo divider(15, 'my-class', 100); // <div class="my-class" style="height: 15px; width: 100%;"></div>
 * echo divider(15, 'my-class', null, 250); // <div class="my-class" style="height: 15px; width: 250px;"></div>
 */
if ( ! function_exists( 'divider' ) ) {
    function divider($height = 2, $class = '', $width_percent = null, $width_px = null) {
        // Ensure height and widths are sanitized as numeric values
        $height = floatval($height);
        $width_percent = is_null($width_percent) ? null : floatval($width_percent);
        $width_px = is_null($width_px) ? null : floatval($width_px);

        // Handle class as string or array
        if (is_array($class)) {
            $class = implode(' ', array_map('sanitize_html_class', $class));
        } else {
            $class = sanitize_html_class($class);
        }

        // Build inline styles
        $styles = 'height: ' . esc_attr($height) . 'px;';
        if (!is_null($width_px)) {
            $styles .= ' width: ' . esc_attr($width_px) . 'px;';
        } elseif (!is_null($width_percent)) {
            $styles .= ' width: ' . esc_attr($width_percent) . '%;';
        }

        // Build the spacer div HTML
        $divider_html = '<div class="' . esc_attr($class) . '" style="' . $styles . '"></div>';

        return $divider_html;
    }
}
?>
