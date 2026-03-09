#!/usr/bin/env php
<?php

declare(strict_types=1);

$themeRoot = realpath(__DIR__ . '/../../../..');
$wpRoot = $themeRoot !== false ? realpath($themeRoot . '/../../..') : false;

if ($themeRoot === false) {
    fwrite(STDERR, "Unable to resolve theme root.\n");
    exit(1);
}

if ($wpRoot === false) {
    fwrite(STDERR, "Unable to resolve WordPress root.\n");
    exit(1);
}

$wpLoadPath = $wpRoot . '/wp-load.php';

if (! file_exists($wpLoadPath)) {
    fwrite(STDERR, "Unable to bootstrap WordPress: wp-load.php not found.\n");
    exit(1);
}

require_once $wpLoadPath;

if (! function_exists('the_logical_theme_get_custom_block_metadata_files')) {
    require_once $themeRoot . '/inc/blocks.php';
}

if (! function_exists('the_logical_theme_get_block_catalog')) {
    require_once $themeRoot . '/partials/block-availability.php';
}

$curatedGroups = the_logical_theme_curated_block_groups();
$catalog = the_logical_theme_get_block_catalog();
$settings = the_logical_theme_get_block_availability_settings();
$customBlocks = the_logical_theme_get_custom_block_names();
$allowedBlocks = the_logical_theme_allowed_blocks();
$customBlockMetadata = [];

foreach (the_logical_theme_get_custom_block_metadata_files() as $metadataFile) {
    $decoded = json_decode((string) file_get_contents($metadataFile), true);

    if (! is_array($decoded)) {
        continue;
    }

    $name = $decoded['name'] ?? null;

    if (! is_string($name) || $name === '') {
        continue;
    }

    $customBlockMetadata[] = [
        'name' => $name,
        'title' => is_string($decoded['title'] ?? null) ? $decoded['title'] : null,
        'description' => is_string($decoded['description'] ?? null) ? $decoded['description'] : null,
        'category' => is_string($decoded['category'] ?? null) ? $decoded['category'] : null,
        'path' => str_replace($themeRoot . '/', '', $metadataFile),
    ];
}

$payload = [
    'source' => [
        'wp_root' => $wpRoot,
        'theme_bootstrap' => 'partials/block-availability.php',
        'block_runtime' => 'partials/block-availability/runtime.php',
        'custom_blocks' => 'blocks/*/block.json',
    ],
    'curated_groups' => $curatedGroups,
    'catalog' => $catalog,
    'settings' => $settings,
    'custom_blocks' => $customBlocks,
    'custom_block_metadata' => $customBlockMetadata,
    'allowed_blocks' => $allowedBlocks,
];

fwrite(STDOUT, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
