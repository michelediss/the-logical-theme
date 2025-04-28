<?php if (have_rows('sezione_pagina')): ?>
 <div class="sezioni-container">
<?php while (have_rows('sezione_pagina')): the_row(); ?>
<?php
$opzioni_colonna = get_sub_field('opzioni_colonna');
$tipo_colonne = $opzioni_colonna['tipo_colonne'];
$invertita = $opzioni_colonna['invertita'];
$classe_riga = $tipo_colonne == 'Colonna Intera' ? 'row-single-column' : 'row-two-columns';
// Aggiungiamo una classe specifica per l'inversione mobile quando $invertita è true
if ($invertita) {
    $classe_riga .= ' mobile-row-inverse';
}
?>
<div class="row <?php echo $classe_riga; ?>">
<?php if (have_rows('colonna')): ?>
<?php
// Ottieni prima la classe della colonna in base al tipo
$classe_colonna = $tipo_colonne == 'Colonna Intera'
 ? 'col-12'
 : 'col-md-6';
?>
<?php while (have_rows('colonna')): the_row(); ?>
<div class="<?php echo $classe_colonna; ?>">
<?php
// Immagine
$immagine = get_sub_field('immagine');
if ($immagine) {
    echo wp_get_attachment_image($immagine['ID'], 'large', false, [
        'class' => 'img-fluid'
    ]);
}

// Titolo
$titolo = get_sub_field('titolo');
if ($titolo) {
    echo '<h2>' . esc_html($titolo) . '</h2>';
}

// Sottotitolo
$sottotitolo = get_sub_field('sottotitolo');
if ($sottotitolo) {
    echo '<h3>' . esc_html($sottotitolo) . '</h3>';
}

// Divider personalizzato (versione sicura)
$divider_code = get_sub_field('divider');
if ($divider_code && !empty($divider_code)) {
    // Verifica se è una stringa di codice divider o un valore booleano/numerico
    if ($divider_code === '1' || $divider_code === 'true') {
        // Compatibilità con il vecchio formato
        echo '<hr class="divider">';
    } else {
        // Usa la funzione di parsing sicura
        if (function_exists('parse_divider_code') && function_exists('divider')) {
            $divider_params = parse_divider_code($divider_code);
            
            if ($divider_params) {
                echo divider(
                    $divider_params['height'],
                    $divider_params['class'],
                    $divider_params['width_percent'],
                    $divider_params['width_px']
                );
            } else {
                // Fallback in caso di parsing fallito
                echo '<hr class="divider">';
            }
        } else {
            // Fallback se la funzione di parsing non esiste
            echo '<hr class="divider">';
        }
    }
}

// Contenuto
$testo = get_sub_field('testo');
if ($testo) {
    echo $testo;
}
?>
</div>
<?php endwhile; ?>
<?php endif; ?>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>

<style>
/* CSS per invertire l'ordine delle colonne solo su mobile quando la classe mobile-row-inverse è presente */
@media (max-width: 767.98px) {
    .mobile-row-inverse {
        display: flex;
        flex-direction: column-reverse;
    }
}
</style>