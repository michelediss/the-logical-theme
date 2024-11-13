<?php

// ===================================================
// Tag gerarchici
// ===================================================

/*
 * Meta Box Removal
 */
function rudr_post_tags_meta_box_remove() {
    $id = 'tagsdiv-post_tag'; // ID della metabox predefinita
    $post_type = 'post'; // Rimuovere solo dalla schermata di modifica dei post
    $position = 'side';
    remove_meta_box($id, $post_type, $position);
}
add_action('admin_menu', 'rudr_post_tags_meta_box_remove');

/*
 * Add
 */
function rudr_add_new_tags_metabox() {
    $id = 'rudrtagsdiv-post_tag'; // ID univoco
    $heading = 'Tags'; // Titolo della metabox
    $callback = 'rudr_metabox_content'; // Funzione callback per il contenuto della metabox
    $post_type = 'post';
    $position = 'side';
    $pri = 'default'; // Priorità
    add_meta_box($id, $heading, $callback, $post_type, $position, $pri);
}
add_action('admin_menu', 'rudr_add_new_tags_metabox');

/*
 * Fill
 */
function rudr_metabox_content($post) {
    // Ottieni tutti i tag del blog come array di oggetti
    $all_tags = get_terms(array('taxonomy' => 'post_tag', 'hide_empty' => 0));

    // Ottieni tutti i tag assegnati a un post
    $all_tags_of_post = get_the_terms($post->ID, 'post_tag');

    // Crea un array degli ID dei tag del post
    $ids = array();
    if ($all_tags_of_post) {
        foreach ($all_tags_of_post as $tag) {
            $ids[] = $tag->term_id;
        }
    }

    // HTML per la metabox
    echo '<div id="taxonomy-post_tag" class="categorydiv">';
    echo '<input type="hidden" name="tax_input[post_tag][]" value="0" />';
    echo '<ul>';
    foreach ($all_tags as $tag) {
        $checked = "";
        if (in_array($tag->term_id, $ids)) {
            $checked = " checked='checked'";
        }
        $id = 'post_tag-' . $tag->term_id;
        echo "<li id='{$id}'>";
        echo "<label><input type='checkbox' name='tax_input[post_tag][]' id='in-$id'" . $checked . " value='$tag->slug' /> $tag->name</label><br />";
        echo "</li>";
    }
    echo '</ul></div>';
}
