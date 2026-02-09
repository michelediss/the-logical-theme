<?php
/**
 * Restrict allowed file extensions for uploads based on theme settings.
 *
 * @param array $mimes Existing array of mime types.
 * @return array Modified array of mime types.
 */
function logical_restrict_mime_types( $mimes ) {
    // Get the allowed file extensions from settings
    $allowed_extensions = get_option('allowed_file_extensions', 'jpg,jpeg,png,gif,svg'); // Default extensions
    $allowed_extensions = array_map('trim', explode(',', $allowed_extensions));

    // Clear existing mime types to apply strict restrictions
    $mimes = [];

    foreach ( $allowed_extensions as $ext ) {
        switch ( strtolower($ext) ) {
            case 'jpg':
            case 'jpeg':
                $mimes['jpg|jpeg'] = 'image/jpeg';
                break;
            case 'png':
                $mimes['png'] = 'image/png';
                break;
            case 'gif':
                $mimes['gif'] = 'image/gif';
                break;
            case 'svg':
                $mimes['svg'] = 'image/svg+xml';
                break;
            case 'pdf':
                $mimes['pdf'] = 'application/pdf';
                break;
            case 'doc':
                $mimes['doc'] = 'application/msword';
                break;
            case 'docx':
                $mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            case 'xls':
                $mimes['xls'] = 'application/vnd.ms-excel';
                break;
            case 'xlsx':
                $mimes['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'ppt':
                $mimes['ppt'] = 'application/vnd.ms-powerpoint';
                break;
            case 'pptx':
                $mimes['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                break;
            case 'csv':
                $mimes['csv'] = 'text/csv';
                break;
            // Add additional cases for more extensions if necessary
            default:
                // Optionally log or handle unsupported extensions
                error_log("Unsupported file extension: {$ext}");
                break;
        }
    }

    return $mimes;
}
add_filter('upload_mimes', 'logical_restrict_mime_types');
?>
