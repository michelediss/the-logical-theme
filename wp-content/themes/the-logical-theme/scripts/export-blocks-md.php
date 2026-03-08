<?php

$blocks = array_values(
    array_map(
        static function (WP_Block_Type $block_type): array {
            return [
                'name' => $block_type->name,
                'title' => $block_type->title ?: '-',
                'category' => $block_type->category ?: '',
            ];
        },
        WP_Block_Type_Registry::get_instance()->get_all_registered()
    )
);

usort(
    $blocks,
    static fn (array $left, array $right): int => [$left['category'], $left['name']] <=> [$right['category'], $right['name']]
);

echo "# Blocchi WordPress disponibili\n\n";
echo "Lista generata dal registry dei blocchi del sito tramite WP-CLI.\n\n";
echo 'Totale blocchi: ' . count($blocks) . "\n\n";
echo "| Name | Title | Category |\n";
echo "| --- | --- | --- |\n";

foreach ($blocks as $block) {
    $title = str_replace('|', '\|', $block['title']);

    echo sprintf(
        "| `%s` | %s | `%s` |\n",
        $block['name'],
        $title,
        $block['category']
    );
}
