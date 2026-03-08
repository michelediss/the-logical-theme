#!/usr/bin/env php
<?php

declare(strict_types=1);

$themeRoot = realpath(__DIR__ . '/../../../..');

if ($themeRoot === false) {
    fwrite(STDERR, "Unable to resolve theme root.\n");
    exit(1);
}

if (! defined('ABSPATH')) {
    define('ABSPATH', $themeRoot . '/');
}

if (! function_exists('get_theme_file_path')) {
    function get_theme_file_path(string $path = ''): string
    {
        global $themeRoot;

        return $themeRoot . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('add_action')) {
    function add_action(...$args): void
    {
    }
}

if (! function_exists('add_filter')) {
    function add_filter(...$args): void
    {
    }
}

require_once $themeRoot . '/inc/blocks.php';
require_once $themeRoot . '/partials/block-availability.php';

$groups = the_logical_theme_curated_block_groups();
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
        'curated_groups' => 'partials/block-availability.php',
        'custom_blocks' => 'blocks/*/block.json',
    ],
    'core_groups' => $groups,
    'custom_blocks' => $customBlocks,
    'custom_block_metadata' => $customBlockMetadata,
    'allowed_blocks' => $allowedBlocks,
];

fwrite(STDOUT, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
