<?php
/**
 * Render the page flexible-content sections defined in the schema.
 */

if (!function_exists('have_rows') || !function_exists('get_row_layout')) {
    return;
}

if (!have_rows('page_section')) {
    return;
}

while (have_rows('page_section')) {
    the_row();

    $layout_name = (string) get_row_layout();
    if ($layout_name === '') {
        continue;
    }

    $row = get_row(true);
    $row = is_array($row) ? $row : [];

    // Backward compatibility for old single-layout structure (acf_fc_layout = page_section).
    if ($layout_name === 'page_section') {
        $legacy_map = [
            'title_section' => isset($row['title_section']) && is_array($row['title_section']) ? $row['title_section'] : [],
            'paragraph' => isset($row['paragraph']) && is_array($row['paragraph']) ? $row['paragraph'] : [],
            'gallery' => isset($row['gallery']) && is_array($row['gallery']) ? $row['gallery'] : [],
            'accordion' => isset($row['accordion']) && is_array($row['accordion']) ? $row['accordion'] : [],
        ];

        if (isset($row['cards']) && is_array($row['cards']) && !empty($row['cards'])) {
            $legacy_map['card'] = ['card_repeater' => $row['cards']];
        }

        foreach ($legacy_map as $legacy_layout_name => $legacy_row) {
            if (!is_array($legacy_row) || empty($legacy_row)) {
                continue;
            }

            set_query_var('logical_flexible_field_name', 'page_section');
            set_query_var('logical_flexible_layout_name', $legacy_layout_name);
            set_query_var('logical_flexible_row', $legacy_row);

            get_template_part('template-parts/flexible/' . $legacy_layout_name);
        }

        continue;
    }

    set_query_var('logical_flexible_field_name', 'page_section');
    set_query_var('logical_flexible_layout_name', $layout_name);
    set_query_var('logical_flexible_row', $row);

    get_template_part('template-parts/flexible/' . $layout_name);
}
