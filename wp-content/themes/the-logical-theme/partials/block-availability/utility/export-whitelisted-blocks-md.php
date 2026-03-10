<?php

if (! function_exists('the_logical_theme_block_availability_utility_registry_output_path')) {
    require_once __DIR__ . '/export-block-registry.php';
}

function the_logical_theme_block_availability_utility_whitelist_output_path(): string
{
    return the_logical_theme_block_availability_utility_output_dir() . '/whitelisted-blocks.md';
}

function the_logical_theme_block_availability_md_scalar($value): string
{
    if ($value === null || $value === '') {
        return '`null`';
    }

    if (is_bool($value)) {
        return $value ? '`true`' : '`false`';
    }

    if (is_int($value) || is_float($value)) {
        return '`' . (string) $value . '`';
    }

    return '`' . str_replace('`', '\`', (string) $value) . '`';
}

function the_logical_theme_block_availability_md_list($value): string
{
    if (! is_array($value) || $value === []) {
        return '- None';
    }

    $lines = [];

    foreach ($value as $item) {
        if (is_scalar($item) || $item === null) {
            $lines[] = '- ' . the_logical_theme_block_availability_md_scalar($item);
            continue;
        }

        $encoded = json_encode($item, JSON_UNESCAPED_SLASHES);
        $lines[] = '- `' . str_replace('`', '\`', (string) $encoded) . '`';
    }

    return implode(PHP_EOL, $lines);
}

function the_logical_theme_block_availability_md_map($value): string
{
    if (! is_array($value) || $value === []) {
        return '- None';
    }

    $lines = [];

    foreach ($value as $key => $item) {
        if (is_scalar($item) || $item === null) {
            $lines[] = '- `' . (string) $key . '`: ' . the_logical_theme_block_availability_md_scalar($item);
            continue;
        }

        $encoded = json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $lines[] = '- `' . (string) $key . '`:' . PHP_EOL . '```json' . PHP_EOL . $encoded . PHP_EOL . '```';
    }

    return implode(PHP_EOL, $lines);
}

function the_logical_theme_export_whitelisted_blocks_markdown(?string $inputPath = null, ?string $outputPath = null): string
{
    $inputPath = $inputPath ?: the_logical_theme_block_availability_utility_registry_output_path();
    $outputPath = $outputPath ?: the_logical_theme_block_availability_utility_whitelist_output_path();

    if (! file_exists($inputPath)) {
        throw new RuntimeException("Input JSON not found: {$inputPath}");
    }

    $decoded = json_decode((string) file_get_contents($inputPath), true);

    if (! is_array($decoded) || ! isset($decoded['blocks']) || ! is_array($decoded['blocks'])) {
        throw new RuntimeException('Invalid block registry JSON.');
    }

    $whitelistedBlocks = array_values(array_filter(
        $decoded['blocks'],
        static fn ($block): bool => is_array($block) && ! empty($block['currently_allowed'])
    ));

    usort($whitelistedBlocks, static function (array $a, array $b): int {
        $bucketCompare = strcmp((string) ($a['category_bucket'] ?? ''), (string) ($b['category_bucket'] ?? ''));

        if ($bucketCompare !== 0) {
            return $bucketCompare;
        }

        return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
    });

    $lines = [
        '# Whitelisted Blocks Reference',
        '',
        'This file is generated from `docs/block/block-registry.json`.',
        '',
        'It contains only the blocks currently marked as allowed by the theme block availability system, together with the metadata exported in the registry JSON.',
        '',
        '## Source',
        '',
        '- Input JSON: `' . $inputPath . '`',
        '- Output file: `' . $outputPath . '`',
        '- Generated at UTC: ' . the_logical_theme_block_availability_md_scalar(gmdate('c')),
        '- Whitelisted blocks: ' . the_logical_theme_block_availability_md_scalar(count($whitelistedBlocks)),
        '',
    ];

    foreach ($whitelistedBlocks as $block) {
        $name = (string) ($block['name'] ?? 'unknown');
        $title = is_string($block['title'] ?? null) && $block['title'] !== '' ? $block['title'] : 'Untitled';

        $lines[] = '## ' . $title . ' (`' . $name . '`)';
        $lines[] = '';
        $lines[] = '- `title`: ' . the_logical_theme_block_availability_md_scalar($block['title'] ?? null);
        $lines[] = '- `description`: ' . the_logical_theme_block_availability_md_scalar($block['description'] ?? null);
        $lines[] = '- `origin`: ' . the_logical_theme_block_availability_md_scalar($block['origin'] ?? null);
        $lines[] = '- `category_bucket`: ' . the_logical_theme_block_availability_md_scalar($block['category_bucket'] ?? null);
        $lines[] = '- `currently_allowed`: ' . the_logical_theme_block_availability_md_scalar($block['currently_allowed'] ?? null);
        $lines[] = '- `currently_blacklisted`: ' . the_logical_theme_block_availability_md_scalar($block['currently_blacklisted'] ?? null);
        $lines[] = '- `is_dynamic`: ' . the_logical_theme_block_availability_md_scalar($block['is_dynamic'] ?? null);
        $lines[] = '- `api_version`: ' . the_logical_theme_block_availability_md_scalar($block['api_version'] ?? null);
        $lines[] = '- `category`: ' . the_logical_theme_block_availability_md_scalar($block['category'] ?? null);
        $lines[] = '- `icon`: ' . the_logical_theme_block_availability_md_scalar($block['icon'] ?? null);
        $lines[] = '- `render_callback`: ' . the_logical_theme_block_availability_md_scalar($block['render_callback'] ?? null);
        $lines[] = '- `has_render_callback`: ' . the_logical_theme_block_availability_md_scalar($block['has_render_callback'] ?? null);
        $lines[] = '';
        $lines[] = '### Keywords';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['keywords'] ?? []);
        $lines[] = '';
        $lines[] = '### Parent';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['parent'] ?? []);
        $lines[] = '';
        $lines[] = '### Ancestor';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['ancestor'] ?? []);
        $lines[] = '';
        $lines[] = '### Uses Context';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['uses_context'] ?? []);
        $lines[] = '';
        $lines[] = '### Provides Context';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_map($block['provides_context'] ?? []);
        $lines[] = '';
        $lines[] = '### Supports Summary';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_map($block['supports_summary'] ?? []);
        $lines[] = '';
        $lines[] = '### Supports';
        $lines[] = '';
        $lines[] = $block['supports'] ? '```json' . PHP_EOL . json_encode($block['supports'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL . '```' : '- None';
        $lines[] = '';
        $lines[] = '### Attributes';
        $lines[] = '';
        $lines[] = $block['attributes'] ? '```json' . PHP_EOL . json_encode($block['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL . '```' : '- None';
        $lines[] = '';
        $lines[] = '### Example';
        $lines[] = '';
        $lines[] = $block['example'] ? '```json' . PHP_EOL . json_encode($block['example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL . '```' : '- None';
        $lines[] = '';
        $lines[] = '### Selectors';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_map($block['selectors'] ?? []);
        $lines[] = '';
        $lines[] = '### Style Handles';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['style_handles'] ?? []);
        $lines[] = '';
        $lines[] = '### Editor Style Handles';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['editor_style_handles'] ?? []);
        $lines[] = '';
        $lines[] = '### Script Handles';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['script_handles'] ?? []);
        $lines[] = '';
        $lines[] = '### Editor Script Handles';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['editor_script_handles'] ?? []);
        $lines[] = '';
        $lines[] = '### View Script Handles';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['view_script_handles'] ?? []);
        $lines[] = '';
        $lines[] = '### View Style Handles';
        $lines[] = '';
        $lines[] = the_logical_theme_block_availability_md_list($block['view_style_handles'] ?? []);
        $lines[] = '';
        $lines[] = '### Custom Metadata';
        $lines[] = '';
        $lines[] = is_array($block['custom_metadata'] ?? null) && $block['custom_metadata'] !== []
            ? '```json' . PHP_EOL . json_encode($block['custom_metadata'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL . '```'
            : '- None';
        $lines[] = '';
    }

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

    if (file_put_contents($outputPath, implode(PHP_EOL, $lines) . PHP_EOL) === false) {
        throw new RuntimeException("Unable to write Markdown output: {$outputPath}");
    }

    @chmod($outputPath, 0666);

    return $outputPath;
}

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath((string) $argv[0]) === __FILE__) {
    try {
        $inputPath = null;
        $outputPath = null;

        foreach (array_slice($argv, 1) as $index => $arg) {
            if ('--input' === $arg) {
                $candidate = $argv[$index + 2] ?? null;

                if (! is_string($candidate) || $candidate === '') {
                    throw new RuntimeException('Missing value for --input.');
                }

                $inputPath = $candidate;
            }

            if ('--output' === $arg) {
                $candidate = $argv[$index + 2] ?? null;

                if (! is_string($candidate) || $candidate === '') {
                    throw new RuntimeException('Missing value for --output.');
                }

                $outputPath = $candidate;
            }
        }

        fwrite(STDOUT, the_logical_theme_export_whitelisted_blocks_markdown($inputPath, $outputPath) . PHP_EOL);
    } catch (Throwable $throwable) {
        fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
        exit(1);
    }
}
