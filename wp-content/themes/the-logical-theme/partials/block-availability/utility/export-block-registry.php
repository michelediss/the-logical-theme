<?php

function the_logical_theme_block_availability_utility_theme_root(): string
{
    $themeRoot = realpath(__DIR__ . '/../../../');

    if ($themeRoot === false) {
        throw new RuntimeException('Unable to resolve theme root.');
    }

    return $themeRoot;
}

function the_logical_theme_block_availability_utility_output_dir(): string
{
    return the_logical_theme_block_availability_utility_theme_root() . '/docs/block';
}

function the_logical_theme_block_availability_utility_registry_output_path(): string
{
    return the_logical_theme_block_availability_utility_output_dir() . '/block-registry.json';
}

function the_logical_theme_block_availability_utility_bootstrap_wordpress_for_cli(): void
{
    if (defined('ABSPATH')) {
        return;
    }

    $themeRoot = the_logical_theme_block_availability_utility_theme_root();
    $wpRoot = realpath($themeRoot . '/../../..');

    if ($wpRoot === false) {
        throw new RuntimeException('Unable to resolve WordPress root.');
    }

    $wpLoadPath = $wpRoot . '/wp-load.php';

    if (! file_exists($wpLoadPath)) {
        throw new RuntimeException('Unable to bootstrap WordPress: wp-load.php not found.');
    }

    require_once $wpLoadPath;
}

function the_logical_theme_block_availability_utility_export_callable_name($callable): ?string
{
    if (! is_callable($callable)) {
        return null;
    }

    if (is_string($callable)) {
        return $callable;
    }

    if (is_array($callable) && count($callable) === 2) {
        $classOrObject = $callable[0];
        $method = $callable[1];
        $className = is_object($classOrObject) ? get_class($classOrObject) : (string) $classOrObject;

        return $className . '::' . $method;
    }

    if ($callable instanceof Closure) {
        return 'Closure';
    }

    if (is_object($callable) && method_exists($callable, '__invoke')) {
        return get_class($callable) . '::__invoke';
    }

    return 'callable';
}

function the_logical_theme_block_availability_utility_export_block_origin(string $blockName): string
{
    if (str_starts_with($blockName, 'core/')) {
        return 'core';
    }

    if (str_starts_with($blockName, 'woocommerce/')) {
        return 'woocommerce';
    }

    if (str_starts_with($blockName, 'custom/')) {
        return 'custom';
    }

    return 'third_party';
}

function the_logical_theme_block_availability_utility_export_support_flag(array $supports, string $key): bool
{
    if (! array_key_exists($key, $supports)) {
        return false;
    }

    $value = $supports[$key];

    if (is_bool($value)) {
        return $value;
    }

    if (is_array($value)) {
        return ! empty($value);
    }

    return (bool) $value;
}

function the_logical_theme_export_block_registry_json(?string $outputPath = null): string
{
    if (! function_exists('the_logical_theme_get_custom_block_metadata_files')) {
        require_once the_logical_theme_block_availability_utility_theme_root() . '/inc/blocks.php';
    }

    if (! function_exists('the_logical_theme_get_block_catalog')) {
        require_once the_logical_theme_block_availability_utility_theme_root() . '/partials/block-availability/runtime.php';
    }

    if (! class_exists('WP_Block_Type_Registry')) {
        throw new RuntimeException('WP_Block_Type_Registry is not available.');
    }

    $themeRoot = the_logical_theme_block_availability_utility_theme_root();
    $outputPath = $outputPath ?: the_logical_theme_block_availability_utility_registry_output_path();
    $catalog = the_logical_theme_get_block_catalog();
    $allowedBlocks = the_logical_theme_allowed_blocks();
    $allowedLookup = array_fill_keys($allowedBlocks, true);
    $categoryLookup = [];

    foreach ($catalog as $categoryKey => $category) {
        foreach ($category['blocks'] ?? [] as $blockName) {
            if (is_string($blockName) && $blockName !== '') {
                $categoryLookup[$blockName] = $categoryKey;
            }
        }
    }

    $customMetadataLookup = [];

    foreach (the_logical_theme_get_custom_block_metadata_files() as $metadataFile) {
        $decoded = json_decode((string) file_get_contents($metadataFile), true);

        if (! is_array($decoded)) {
            continue;
        }

        $name = $decoded['name'] ?? null;

        if (! is_string($name) || $name === '') {
            continue;
        }

        $customMetadataLookup[$name] = [
            'path' => str_replace($themeRoot . '/', '', $metadataFile),
            'block_json' => $decoded,
        ];
    }

    $registry = WP_Block_Type_Registry::get_instance()->get_all_registered();
    $blocks = [];

    foreach ($registry as $blockName => $blockType) {
        if (! is_string($blockName) || ! $blockType instanceof WP_Block_Type) {
            continue;
        }

        $supports = is_array($blockType->supports ?? null) ? $blockType->supports : [];
        $styleHandles = array_values(array_filter((array) ($blockType->style_handles ?? []), 'is_string'));
        $editorStyleHandles = array_values(array_filter((array) ($blockType->editor_style_handles ?? []), 'is_string'));
        $scriptHandles = array_values(array_filter((array) ($blockType->script_handles ?? []), 'is_string'));
        $editorScriptHandles = array_values(array_filter((array) ($blockType->editor_script_handles ?? []), 'is_string'));
        $viewScriptHandles = array_values(array_filter((array) ($blockType->view_script_handles ?? []), 'is_string'));
        $viewStyleHandles = array_values(array_filter((array) ($blockType->view_style_handles ?? []), 'is_string'));
        $parent = array_values(array_filter((array) ($blockType->parent ?? []), 'is_string'));
        $ancestor = array_values(array_filter((array) ($blockType->ancestor ?? []), 'is_string'));
        $keywords = array_values(array_filter((array) ($blockType->keywords ?? []), 'is_string'));
        $usesContext = array_values(array_filter((array) ($blockType->uses_context ?? []), 'is_string'));
        $providesContext = is_array($blockType->provides_context ?? null) ? $blockType->provides_context : [];
        $selectors = is_array($blockType->selectors ?? null) ? $blockType->selectors : [];
        $attributes = is_array($blockType->attributes ?? null) ? $blockType->attributes : [];
        $example = is_array($blockType->example ?? null) ? $blockType->example : null;
        $renderCallback = the_logical_theme_block_availability_utility_export_callable_name($blockType->render_callback ?? null);
        $categoryBucket = $categoryLookup[$blockName] ?? 'unclassified';
        $origin = the_logical_theme_block_availability_utility_export_block_origin($blockName);
        $customMetadata = $customMetadataLookup[$blockName] ?? null;

        $blocks[] = [
            'name' => $blockName,
            'title' => is_string($blockType->title ?? null) ? $blockType->title : null,
            'description' => is_string($blockType->description ?? null) ? $blockType->description : null,
            'origin' => $origin,
            'category_bucket' => $categoryBucket,
            'currently_allowed' => isset($allowedLookup[$blockName]),
            'currently_blacklisted' => ! isset($allowedLookup[$blockName]),
            'is_dynamic' => method_exists($blockType, 'is_dynamic') ? (bool) $blockType->is_dynamic() : false,
            'api_version' => is_numeric($blockType->api_version ?? null) ? (int) $blockType->api_version : null,
            'category' => is_string($blockType->category ?? null) ? $blockType->category : null,
            'icon' => is_string($blockType->icon ?? null) ? $blockType->icon : null,
            'keywords' => $keywords,
            'parent' => $parent,
            'ancestor' => $ancestor,
            'uses_context' => $usesContext,
            'provides_context' => $providesContext,
            'selectors' => $selectors,
            'supports' => $supports,
            'supports_summary' => [
                'anchor' => the_logical_theme_block_availability_utility_export_support_flag($supports, 'anchor'),
                'align' => the_logical_theme_block_availability_utility_export_support_flag($supports, 'align'),
                'spacing' => the_logical_theme_block_availability_utility_export_support_flag($supports, 'spacing'),
                'color' => the_logical_theme_block_availability_utility_export_support_flag($supports, 'color'),
                'typography' => the_logical_theme_block_availability_utility_export_support_flag($supports, 'typography'),
                'html' => the_logical_theme_block_availability_utility_export_support_flag($supports, 'html'),
                'multiple' => the_logical_theme_block_availability_utility_export_support_flag($supports, 'multiple'),
                'reusable' => the_logical_theme_block_availability_utility_export_support_flag($supports, 'reusable'),
            ],
            'attributes' => $attributes,
            'example' => $example,
            'render_callback' => $renderCallback,
            'has_render_callback' => $renderCallback !== null,
            'style_handles' => $styleHandles,
            'editor_style_handles' => $editorStyleHandles,
            'script_handles' => $scriptHandles,
            'editor_script_handles' => $editorScriptHandles,
            'view_script_handles' => $viewScriptHandles,
            'view_style_handles' => $viewStyleHandles,
            'custom_metadata' => $customMetadata,
        ];
    }

    usort($blocks, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

    $payload = [
        'source' => [
            'wp_root' => defined('ABSPATH') ? ABSPATH : null,
            'theme_root' => $themeRoot,
            'script' => 'partials/block-availability/utility/export-block-registry.php',
            'theme_bootstrap' => 'partials/block-availability.php',
            'block_runtime' => 'partials/block-availability/runtime.php',
            'custom_blocks' => 'blocks/*/block.json',
        ],
        'generated_at_utc' => gmdate('c'),
        'totals' => [
            'registered_blocks' => count($blocks),
            'allowed_blocks' => count($allowedBlocks),
        ],
        'blocks' => $blocks,
    ];

    $outputDir = dirname($outputPath);

    if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
        throw new RuntimeException("Unable to create output directory: {$outputDir}");
    }

    if (! is_writable($outputDir)) {
        throw new RuntimeException("Output directory is not writable: {$outputDir}");
    }

    if (file_exists($outputPath) && ! is_writable($outputPath)) {
        if (! unlink($outputPath)) {
            throw new RuntimeException("Existing output file is not writable and could not be replaced: {$outputPath}");
        }
    }

    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if (! is_string($json) || file_put_contents($outputPath, $json . PHP_EOL) === false) {
        throw new RuntimeException("Unable to write JSON output: {$outputPath}");
    }

    @chmod($outputPath, 0666);

    return $outputPath;
}

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath((string) $argv[0]) === __FILE__) {
    try {
        the_logical_theme_block_availability_utility_bootstrap_wordpress_for_cli();

        $outputPath = null;

        foreach (array_slice($argv, 1) as $index => $arg) {
            if ('--output' === $arg) {
                $candidate = $argv[$index + 2] ?? null;

                if (! is_string($candidate) || $candidate === '') {
                    throw new RuntimeException('Missing value for --output.');
                }

                $outputPath = $candidate;
            }
        }

        fwrite(STDOUT, the_logical_theme_export_block_registry_json($outputPath) . PHP_EOL);
    } catch (Throwable $throwable) {
        fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
        exit(1);
    }
}
