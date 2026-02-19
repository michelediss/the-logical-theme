#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { createRequire } from 'node:module';

function normalizeHex(hex) {
  const raw = String(hex ?? '').trim().toLowerCase().replace(/^#/, '');
  const full = raw.length === 3 ? raw.split('').map((c) => `${c}${c}`).join('') : raw;
  if (!/^[0-9a-f]{6}$/.test(full)) {
    return null;
  }
  return `#${full}`;
}

function generateTypographyScale(ratio) {
  const r = ratio > 0 ? ratio : 1.2;
  return {
    xs: Number((1 / (r ** 2)).toFixed(3)),
    sm: Number((1 / r).toFixed(3)),
    base: 1,
    lg: Number((r).toFixed(3)),
    xl: Number((r ** 2).toFixed(3)),
    '2xl': Number((r ** 3).toFixed(3)),
    '3xl': Number((r ** 4).toFixed(3)),
    '4xl': Number((r ** 5).toFixed(3)),
    '5xl': Number((r ** 6).toFixed(3)),
    '6xl': Number((r ** 7).toFixed(3)),
    '7xl': Number((r ** 8).toFixed(3)),
    '8xl': Number((r ** 9).toFixed(3)),
    '9xl': Number((r ** 10).toFixed(3)),
  };
}

function parseFontImport(value) {
  const v = String(value ?? '').trim();
  if (!v) return null;
  if (/^https?:\/\//i.test(v)) {
    return `@import url('${v}');`;
  }
  const prefix = 'font-pairing-list/_';
  if (!v.startsWith(prefix)) return null;
  const name = v.slice(prefix.length);
  const pairs = name.split('_+_').map((part) => part.replaceAll('_', ' '));
  const families = pairs.map((fontName) => `family=${encodeURIComponent(fontName)}:wght@400;700`);
  if (!families.length) return null;
  return `@import url('https://fonts.googleapis.com/css2?${families.join('&')}&display=swap');`;
}

function firstFontName(fontFamily) {
  const value = String(fontFamily ?? '').trim();
  if (!value) return null;
  const first = value.split(',')[0]?.trim();
  if (!first) return null;
  return first.replace(/^['"]+|['"]+$/g, '').trim() || null;
}

function createPairingImport(families) {
  const names = families
    .map((entry) => firstFontName(entry?.fontFamily) || String(entry?.name ?? '').trim())
    .filter(Boolean)
    .slice(0, 2)
    .map((name) => name.replace(/\s+/g, '_'));

  if (names.length === 0) return [];
  return [`font-pairing-list/_${names.join('_+_')}`];
}

function readThemeJson(themePath) {
  if (!fs.existsSync(themePath)) {
    throw new Error(`theme.json not found: ${themePath}`);
  }

  const raw = fs.readFileSync(themePath, 'utf8');
  let data;
  try {
    data = JSON.parse(raw);
  } catch (err) {
    throw new Error(`Invalid JSON in ${themePath}: ${err.message}`);
  }

  if (!data || typeof data !== 'object' || Array.isArray(data)) {
    throw new Error(`Invalid JSON structure in ${themePath}: root must be an object.`);
  }

  return data;
}

function themeToLds(themeJson) {
  const paletteItems = Array.isArray(themeJson?.settings?.color?.palette) ? themeJson.settings.color.palette : [];
  const breakpoints = themeJson?.settings?.custom?.lds?.breakpoints;
  const containerMaxWidths = themeJson?.settings?.custom?.lds?.containerMaxWidths;
  const customBase = themeJson?.settings?.custom?.lds?.baseSettings;

  const palette = {};
  const baseColors = {};

  for (const item of paletteItems) {
    const slug = String(item?.slug ?? '').trim().toLowerCase();
    const hex = normalizeHex(item?.color);
    if (!slug || !hex) continue;

    if (slug === 'white' || slug === 'black' || slug === 'gray') {
      baseColors[slug] = hex;
    } else {
      palette[slug] = hex;
    }
  }

  if (!baseColors.white) baseColors.white = '#ffffff';
  if (!baseColors.black) baseColors.black = '#000000';

  if (!palette.primary && paletteItems.length > 0) {
    const primary = paletteItems.find((item) => String(item?.slug ?? '').toLowerCase() === 'primary');
    const value = normalizeHex(primary?.color);
    if (value) palette.primary = value;
  }

  const defaultBreakpoints = {
    null: 0,
    sm: '576px',
    md: '768px',
    lg: '1024px',
    xl: '1280px',
    '2xl': '1600px',
    '3xl': '1920px',
    '4xl': '2560px',
    '5xl': '3840px',
  };

  const defaultContainers = {
    sm: '540px',
    md: '720px',
    lg: '960px',
    xl: '1140px',
    '2xl': '1440px',
    '3xl': '1680px',
    '4xl': '1920px',
    '5xl': '2560px',
  };

  const layoutContent = String(themeJson?.settings?.layout?.contentSize ?? '').trim();
  const layoutWide = String(themeJson?.settings?.layout?.wideSize ?? '').trim();

  const finalContainers = {
    ...defaultContainers,
    ...(containerMaxWidths && typeof containerMaxWidths === 'object' ? containerMaxWidths : {}),
  };

  if (!finalContainers.xl && layoutContent) finalContainers.xl = layoutContent;
  if (!finalContainers['2xl'] && layoutWide) finalContainers['2xl'] = layoutWide;

  const fontFamilies = Array.isArray(themeJson?.settings?.typography?.fontFamilies)
    ? themeJson.settings.typography.fontFamilies
    : [];

  const fontImports = createPairingImport(fontFamilies);

  const defaultBaseSettings = {
    baseSize: 16,
    r: 1.2,
    incrementFactor: 1.01,
  };

  return {
    baseSettings: {
      ...defaultBaseSettings,
      ...(customBase && typeof customBase === 'object' ? customBase : {}),
    },
    baseColors,
    palette,
    breakpoints: {
      ...defaultBreakpoints,
      ...(breakpoints && typeof breakpoints === 'object' ? breakpoints : {}),
    },
    containerMaxWidths: finalContainers,
    font: {
      imports: fontImports,
      classes: {},
    },
  };
}

function buildPaletteColors(data) {
  const baseColors = data.baseColors ?? {};
  const palette = data.palette ?? {};
  const output = {};

  for (const [name, color] of Object.entries(baseColors)) {
    const normalized = normalizeHex(color);
    if (!normalized) continue;
    output[name] = normalized;
  }

  for (const [name, color] of Object.entries(palette)) {
    const normalized = normalizeHex(color);
    if (!normalized) continue;
    output[name] = normalized;
  }

  if (!output.primary) output.primary = '#f05252';
  if (!output.secondary) output.secondary = '#c27803';
  if (!output.black) output.black = '#1e201f';
  if (!output.white) output.white = '#fcfcfe';

  return output;
}

function generateTokensCss(data) {
  const baseSettings = data.baseSettings ?? {};
  const baseSize = Number(baseSettings.baseSize ?? 16);
  const ratio = Number(baseSettings.r ?? 1.2);
  const increment = Number(baseSettings.incrementFactor ?? 1.01);
  const breakpoints = data.breakpoints ?? {
    null: 0,
    sm: '576px',
    md: '768px',
    lg: '1024px',
    xl: '1280px',
  };

  const font = data.font ?? {};
  const css = [];
  for (const item of font.imports ?? []) {
    const line = parseFontImport(item);
    if (line) css.push(line);
  }

  css.push(':root {');
  css.push(`  --lds-base-size: ${baseSize};`);
  css.push(`  --lds-ratio: ${ratio};`);
  css.push(`  --lds-increment-factor: ${increment};`);

  const rounded = data.rounded != null ? Number(Boolean(data.rounded)) : 1;
  if (rounded === 0) {
    css.push('  --lds-border-radius: 0;');
  }

  const paletteColors = buildPaletteColors(data);
  for (const [name, color] of Object.entries(paletteColors)) {
    css.push(`  --${name}: ${color};`);
  }

  const typoScale = generateTypographyScale(ratio);
  for (const [size, value] of Object.entries(typoScale)) {
    css.push(`  --text-${size}: ${value}rem;`);
  }
  css.push('}');

  css.push(`html { font-size: ${baseSize}px; }`);
  let idx = 0;
  for (const [key, value] of Object.entries(breakpoints)) {
    if (key === 'null') continue;
    idx += 1;
    const size = Number((baseSize * (increment ** idx)).toFixed(3));
    css.push(`@media (min-width: ${String(value).trim()}) { html { font-size: ${size}px; } }`);
  }

  for (const [selector, rules] of Object.entries(font.classes ?? {})) {
    if (!rules || typeof rules !== 'object' || Array.isArray(rules)) continue;
    css.push(`${selector} {`);
    for (const [prop, val] of Object.entries(rules)) {
      css.push(`  ${String(prop).trim()}: ${typeof val === 'number' ? String(val) : String(val).trim()};`);
    }
    css.push('}');
  }

  if (rounded === 0) {
    css.push('.rounded, .rounded-1, .rounded-2, .rounded-3, .rounded-4, .rounded-5, .rounded-pill { border-radius: 0 !important; }');
  }

  return `${css.join('\n')}\n`;
}

function generateRuntimeConfig(data) {
  const breakpoints = data.breakpoints ?? {};
  const containers = data.containerMaxWidths ?? {};
  const ratio = Number(data.baseSettings?.r ?? 1.2);
  const colors = buildPaletteColors(data);
  const colorNames = Object.keys(colors);
  const variants = Object.keys(breakpoints).filter((k) => k !== 'null');
  const screens = Object.fromEntries(
    Object.entries(breakpoints)
      .filter(([k]) => k !== 'null')
      .map(([k, v]) => [k, String(v).trim()]),
  );
  const containerScreens = Object.fromEntries(
    Object.entries(containers)
      .filter(([k]) => screens[k])
      .map(([k, v]) => [k, String(v).trim()]),
  );
  const colorPattern = colorNames.length ? colorNames.join('|') : 'primary|secondary';
  const utilityPattern = 'bg|text|decoration|border|outline|shadow|inset-shadow|ring|inset-ring|accent|caret|fill|stroke';
  const fontScale = generateTypographyScale(ratio);
  const fontSizes = Object.fromEntries(
    Object.entries(fontScale).map(([k, v]) => [k, [`${v}rem`, { lineHeight: '1.2' }]]),
  );
  const colorsWithPrimitives = {
    transparent: 'transparent',
    current: 'currentColor',
    inherit: 'inherit',
    ...colors,
  };

  const insetSafelist = [];
  for (const name of colorNames) {
    const a = `inset-shadow-${name}`;
    const b = `inset-ring-${name}`;
    insetSafelist.push(a, b);
    for (const variant of variants) {
      insetSafelist.push(`${variant}:${a}`, `${variant}:${b}`);
    }
  }

  return `export default ${JSON.stringify({
    content: [
      '../../themes/logical-theme/*.php',
      '../../themes/logical-theme/templates/**/*.php',
      '../../themes/logical-theme/template-parts/**/*.php',
      '../../themes/logical-theme/src/**/*.{js,jsx}',
    ],
    safelist: [
      {
        pattern: `__REGEX__^(${utilityPattern})-(${colorPattern})$__END__`,
        variants,
      },
      {
        pattern: '__REGEX__^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)$__END__',
        variants,
      },
      ...insetSafelist,
    ],
    theme: {
      screens,
      container: { screens: containerScreens },
      colors: colorsWithPrimitives,
      extend: { fontSize: fontSizes },
      ringColor: '__FUNC_COLORS__',
      ringOffsetColor: '__FUNC_COLORS__',
      borderColor: '__FUNC_COLORS__',
      outlineColor: '__FUNC_COLORS__',
      textColor: '__FUNC_COLORS__',
      backgroundColor: '__FUNC_COLORS__',
      decorationColor: '__FUNC_COLORS__',
      fill: '__FUNC_COLORS__',
      stroke: '__FUNC_COLORS__',
      caretColor: '__FUNC_COLORS__',
      accentColor: '__FUNC_COLORS__',
      boxShadowColor: '__FUNC_COLORS__',
    },
    plugins: ['__LDS_PLUGIN__'],
  }, null, 2)
    .replace(/"__REGEX__(.*?)__END__"/g, (_, body) => `new RegExp(${JSON.stringify(body)})`)
    .replace(/"__FUNC_COLORS__"/g, "({ theme }) => ({ ...theme('colors') })")
    .replace('"__LDS_PLUGIN__"', `function({ matchUtilities, theme }) {
      const flattenColors = (input, prefix = '') => Object.entries(input || {}).reduce((acc, [key, value]) => {
        const token = prefix ? (prefix + '-' + key) : key;
        if (typeof value === 'string') {
          acc[token] = value;
          return acc;
        }
        if (value && typeof value === 'object') {
          Object.assign(acc, flattenColors(value, token));
        }
        return acc;
      }, {});

      const colorValues = flattenColors(theme('colors'));

      matchUtilities(
        {
          'inset-shadow': (value) => ({
            '--tw-shadow-color': value,
            '--tw-shadow': 'inset 0 2px 4px 0 var(--tw-shadow-color)',
            'box-shadow':
              'var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)'
          })
        },
        {
          values: colorValues,
          type: ['color']
        }
      );

      matchUtilities(
        {
          'inset-ring': (value) => ({
            '--tw-inset-ring-color': value,
            '--tw-inset-ring-shadow': 'inset 0 0 0 1px var(--tw-inset-ring-color)',
            'box-shadow':
              'var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow, 0 0 #0000)'
          })
        },
        {
          values: colorValues,
          type: ['color']
        }
      );
    }`)};
`;
}

function resolveTailwindCli(pluginDir, themeDir) {
  const explicitBins = [
    path.join(pluginDir, 'node_modules', '.bin', 'tailwindcss'),
    path.join(themeDir, 'node_modules', '.bin', 'tailwindcss'),
  ];
  for (const bin of explicitBins) {
    if (fs.existsSync(bin)) {
      return { cmd: bin, args: [] };
    }
  }

  const requireCandidates = [
    path.join(pluginDir, 'package.json'),
    path.join(themeDir, 'package.json'),
  ];
  for (const pkgPath of requireCandidates) {
    if (!fs.existsSync(pkgPath)) continue;
    try {
      const req = createRequire(pkgPath);
      const cli = req.resolve('tailwindcss/lib/cli.js');
      return { cmd: process.execPath, args: [cli] };
    } catch (_) {
      // Continue.
    }
  }

  return { cmd: 'npx', args: ['tailwindcss'] };
}

function resolveActiveThemeDir(pluginDir, fallbackThemeDir) {
  const envThemeDir = process.env.LDS_TW_THEME_DIR;
  if (envThemeDir && fs.existsSync(envThemeDir)) {
    return path.resolve(envThemeDir);
  }

  const wpRoot = path.resolve(pluginDir, '..', '..', '..');
  const wpLoad = path.join(wpRoot, 'wp-load.php');
  if (!fs.existsSync(wpLoad)) {
    return fallbackThemeDir;
  }

  const phpCode = [
    `require ${JSON.stringify(wpLoad)};`,
    "if (!function_exists('get_stylesheet_directory')) { exit(2); }",
    'echo get_stylesheet_directory();',
  ].join(' ');

  const probe = spawnSync('php', ['-r', phpCode], {
    cwd: wpRoot,
    encoding: 'utf8',
  });

  if (probe.status === 0) {
    const activeDir = String(probe.stdout || '').trim();
    if (activeDir && fs.existsSync(activeDir)) {
      return path.resolve(activeDir);
    }
  }

  return fallbackThemeDir;
}

function main() {
  const pluginDir = path.resolve(process.argv[2] || process.cwd());
  const defaultThemeDir = path.resolve(process.argv[3] || path.join(pluginDir, '..', '..', 'themes', 'logical-theme'));
  const themeDir = resolveActiveThemeDir(pluginDir, defaultThemeDir);

  const themeJsonPath = process.argv[4] || path.join(themeDir, 'theme.json');

  const generatedDir = path.join(pluginDir, 'src', 'generated');
  const distDir = path.join(pluginDir, 'dist');
  const themeCssPath = process.argv[5] || path.join(themeDir, 'assets', 'css', 'lds-style.css');
  const themeMinCssPath = process.argv[6] || path.join(themeDir, 'assets', 'css', 'lds-style.min.css');

  const start = Date.now();
  const themeJson = readThemeJson(themeJsonPath);
  const data = themeToLds(themeJson);
  const tokensCss = generateTokensCss(data);
  const runtimeConfig = generateRuntimeConfig(data);

  fs.mkdirSync(generatedDir, { recursive: true });
  fs.mkdirSync(distDir, { recursive: true });
  fs.mkdirSync(path.dirname(themeCssPath), { recursive: true });

  const tokensPath = path.join(generatedDir, 'tokens.css');
  const buildInputPath = path.join(generatedDir, 'build-input.css');
  const runtimeConfigPath = path.join(generatedDir, 'tailwind.runtime.config.js');
  const distCssPath = path.join(distDir, 'lds-tw.css');
  const distMinCssPath = path.join(distDir, 'lds-tw.min.css');

  fs.writeFileSync(tokensPath, tokensCss);
  fs.writeFileSync(buildInputPath, `@tailwind base;\n@tailwind components;\n@tailwind utilities;\n\n${tokensCss}`);
  fs.writeFileSync(runtimeConfigPath, runtimeConfig);

  const tw = resolveTailwindCli(pluginDir, themeDir);
  const args = [...tw.args, '--config', runtimeConfigPath, '-i', buildInputPath, '-o', distCssPath, '--minify'];
  const run = spawnSync(tw.cmd, args, {
    cwd: pluginDir,
    encoding: 'utf8',
    env: {
      ...process.env,
      PATH: `/usr/local/bin:/opt/homebrew/bin:/usr/bin:/bin:${process.env.PATH ?? ''}`,
    },
  });

  if (run.status !== 0 || !fs.existsSync(distCssPath)) {
    const stderr = [run.stdout, run.stderr].filter(Boolean).join('\n').trim();
    throw new Error(stderr || 'Tailwind build failed');
  }

  const compiled = fs.readFileSync(distCssPath, 'utf8');
  fs.writeFileSync(distMinCssPath, compiled);
  fs.writeFileSync(themeCssPath, compiled);
  fs.writeFileSync(themeMinCssPath, compiled);

  const elapsed = Date.now() - start;
  process.stdout.write(JSON.stringify({
    ok: true,
    message: `Compilation completed in ${elapsed} ms using tailwindcss.`,
    compiler_note: 'tailwindcss',
  }));
}

try {
  main();
} catch (error) {
  process.stderr.write(String(error?.message || error));
  process.exit(1);
}
