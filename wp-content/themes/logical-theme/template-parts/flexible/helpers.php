<?php
/**
 * Helpers for ACF flexible content template-parts.
 */

if (!function_exists('logical_flexible_section_class')) {
    function logical_flexible_section_class($source, $default = 'white')
    {
        $allowed = ['white', 'primary-white', 'secondary-white', 'primary', 'secondary', 'black'];

        $normalize = static function ($value) use ($allowed) {
            $value = strtolower(trim((string) $value));
            return in_array($value, $allowed, true) ? $value : '';
        };

        if (is_string($source)) {
            $match = $normalize($source);
            return $match !== '' ? $match : $default;
        }

        if (is_array($source)) {
            foreach (['section_color', 'color', 'value', 'label'] as $key) {
                if (array_key_exists($key, $source)) {
                    $match = logical_flexible_section_class($source[$key], '');
                    if ($match !== '') {
                        return $match;
                    }
                }
            }

            foreach ($source as $value) {
                $match = logical_flexible_section_class($value, '');
                if ($match !== '') {
                    return $match;
                }
            }
        }

        return $default;
    }
}

if (!function_exists('logical_flexible_container_class')) {
    function logical_flexible_container_class($source, $default = 'container')
    {
        $normalize_width = static function ($value) {
            $value = strtolower(trim((string) $value));
            if ($value === 'container') {
                return 'container';
            }
            if ($value === 'full' || $value === 'full-width' || $value === 'fullwidth') {
                return 'container-fluid px-0';
            }
            return '';
        };

        if (is_string($source)) {
            $match = $normalize_width($source);
            return $match !== '' ? $match : $default;
        }

        if (is_array($source)) {
            foreach (['width', 'layout'] as $key) {
                if (array_key_exists($key, $source)) {
                    $match = logical_flexible_container_class($source[$key], '');
                    if ($match !== '') {
                        return $match;
                    }
                }
            }

            foreach ($source as $value) {
                $match = logical_flexible_container_class($value, '');
                if ($match !== '') {
                    return $match;
                }
            }
        }

        return $default;
    }
}

if (!function_exists('logical_flexible_cta')) {
    function logical_flexible_cta($source)
    {
        $result = [
            'text' => '',
            'url' => '',
            'target' => '',
        ];

        if (!is_array($source)) {
            return $result;
        }

        if (isset($source['url']) && is_array($source['url'])) {
            $link = $source['url'];
            $result['text'] = isset($link['title']) ? (string) $link['title'] : '';
            $result['url'] = isset($link['url']) ? (string) $link['url'] : '';
            $result['target'] = isset($link['target']) ? (string) $link['target'] : '';
            return $result;
        }

        $result['text'] = isset($source['text']) ? (string) $source['text'] : '';
        if ($result['text'] === '' && isset($source['title'])) {
            $result['text'] = (string) $source['title'];
        }
        if ($result['text'] === '' && isset($source['button_text'])) {
            $result['text'] = (string) $source['button_text'];
        }

        if (isset($source['url']) && !is_array($source['url'])) {
            $result['url'] = (string) $source['url'];
        }
        if ($result['url'] === '' && isset($source['link'])) {
            $result['url'] = (string) $source['link'];
        }
        if ($result['url'] === '' && isset($source['button_url'])) {
            $result['url'] = (string) $source['button_url'];
        }

        if (isset($source['target'])) {
            $result['target'] = (string) $source['target'];
        }

        return $result;
    }
}

