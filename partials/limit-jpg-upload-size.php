<?php
/**
 * Limit the upload size of JPG files based on settings.
 *
 * @param array $file The file information.
 * @return array
 */
function logical_limit_jpg_upload_size($file) {
    // Debugging: Log the upload attempt
    error_log("Attempting to upload file: " . $file['name'] . " with type: " . $file['type'] . " and size: " . $file['size']);

    // Check if the file is a JPG image.
    if ( isset($file['type']) && $file['type'] === 'image/jpeg' ) {
        // Get the file size in kilobytes.
        $file_size_kb = $file['size'] / 1024;

        // Get the maximum allowed size from settings.
        $max_size_kb = get_option('max_jpg_upload_size_kb', 500); // Default: 500 KB

        // Debugging: Log the max size
        error_log("Max allowed JPG size: " . $max_size_kb . " KB");

        // If the file size exceeds the maximum allowed size, generate an error.
        if ( $file_size_kb > $max_size_kb ) {
            $file['error'] = sprintf(
                'Please do not upload JPG images larger than %d KB. Compress your image using https://compressjpeg.com.',
                $max_size_kb
            );
            // Debugging: Log the error
            error_log("Uploaded JPG exceeds the maximum size of {$max_size_kb} KB.");
        }
    }

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'logical_limit_jpg_upload_size');
?>