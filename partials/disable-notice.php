<?php
/**
 * Nasconde tutte le admin notices (message box) in area admin.
 */
function logical_hide_all_admin_notices() {
    // Rimuove tutte le azioni collegate a questi hook
    remove_all_actions('admin_notices');
    remove_all_actions('all_admin_notices');
}
add_action('admin_init', 'logical_hide_all_admin_notices');
