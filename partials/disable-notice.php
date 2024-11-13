<?php
function logical_hide_all_admin_notices() {
    global $wp_filter;

    if ( isset( $wp_filter['admin_notices'] ) ) {
        $wp_filter['admin_notices']->callbacks = array();
    }
}
add_action('admin_init', 'logical_hide_all_admin_notices');
 ?>
