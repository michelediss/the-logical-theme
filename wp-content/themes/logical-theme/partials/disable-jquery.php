<?php

function disable_jquery_default_scripts() {
        // Verifica se jQuery core è stato disabilitato
            wp_dequeue_script('jquery');
            wp_deregister_script('jquery');

        // Verifica se jQuery Migrate è stato disabilitato
            wp_dequeue_script('jquery-migrate');
            wp_deregister_script('jquery-migrate');
    } 


add_action('wp_enqueue_scripts', 'disable_jquery_default_scripts', 100);

?>



