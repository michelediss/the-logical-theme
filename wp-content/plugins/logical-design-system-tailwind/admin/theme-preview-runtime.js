(function () {
  const STYLE_ID = 'lds-tw-runtime-preview-style';
  const FONT_LINK_ID = 'lds-tw-runtime-preview-fonts';
  const DEFAULT_BREAKPOINTS = {
    null: 0,
    sm: '640px',
    md: '768px',
    lg: '1024px',
    xl: '1280px',
    '2xl': '1536px',
    '3xl': '1920px',
    '4xl': '2560px',
    '5xl': '3840px'
  };

  function normalizeHex(hex) {
    const raw = String(hex || '').trim().replace(/^#/, '').toLowerCase();
    if (/^[0-9a-f]{3}$/.test(raw)) {
      return '#' + raw.split('').map((c) => c + c).join('');
    }
    if (/^[0-9a-f]{6}$/.test(raw)) {
      return '#' + raw;
    }
    return null;
  }

  function hexToRgbChannels(hex) {
    const normalized = normalizeHex(hex);
    if (!normalized) return null;
    const parts = normalized.replace('#', '').match(/.{2}/g) || [];
    if (parts.length !== 3) return null;
    return parts.map((part) => parseInt(part, 16)).join(' ');
  }

  function clampPositive(value, fallback) {
    const num = Number(value);
    return Number.isFinite(num) && num > 0 ? num : fallback;
  }

  function toEntriesMap(input, fallback) {
    const out = { ...(fallback || {}) };
    if (!input || typeof input !== 'object') return out;
    Object.entries(input).forEach(([key, value]) => {
      if (!key) return;
      out[key] = String(value || '').trim();
    });
    return out;
  }

  function generateTypeScale(ratio) {
    const r = clampPositive(ratio, 1.2);
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
      '9xl': Number((r ** 10).toFixed(3))
    };
  }

  function selectedPair(state) {
    const selectedId = String(state && state.fontPairing ? state.fontPairing : '');
    const list = Array.isArray(state && state.fontPairings) ? state.fontPairings : [];
    return list.find((pair) => pair.id === selectedId) || list[0] || null;
  }

  function escapeClassToken(token) {
    return String(token || '').replace(/([:\\])/g, '\\$1');
  }

  function buildGoogleFontUrl(pair) {
    if (!pair) return '';
    const families = [];
    const first = String(pair.first || '').trim();
    const second = String(pair.second || '').trim();
    if (first) {
      families.push(`family=${encodeURIComponent(first)}:wght@400;700`);
    }
    if (second) {
      families.push(`family=${encodeURIComponent(second)}:wght@400;700`);
    }
    if (families.length === 0) return '';
    return `https://fonts.googleapis.com/css2?${families.join('&')}&display=swap`;
  }

  function buildRootCss(state) {
    const palette = Array.isArray(state && state.palette) ? state.palette : [];
    const base = state && state.baseSettings ? state.baseSettings : {};
    const baseSize = clampPositive(base.baseSize, 16);
    const ratio = clampPositive(base.r, 1.2);
    const increment = clampPositive(base.incrementFactor, 1.01);
    const typeScale = generateTypeScale(ratio);

    const lines = [];
    lines.push(':root {');
    lines.push(`  --lds-base-size: ${baseSize};`);
    lines.push(`  --lds-ratio: ${ratio};`);
    lines.push(`  --lds-increment-factor: ${increment};`);

    palette.forEach((entry) => {
      const slug = String(entry && entry.slug ? entry.slug : '').trim();
      const hex = normalizeHex(entry && entry.color);
      const rgb = hexToRgbChannels(hex);
      if (!slug || !hex || !rgb) return;
      lines.push(`  --color-${slug}: ${hex};`);
      lines.push(`  --color-${slug}-rgb: ${rgb};`);
    });

    Object.entries(typeScale).forEach(([key, value]) => {
      lines.push(`  --text-${key}: ${value}rem;`);
    });

    lines.push('}');
    lines.push(`html { font-size: ${baseSize}px; }`);

    const breakpoints = toEntriesMap(state && state.breakpoints, DEFAULT_BREAKPOINTS);
    let idx = 0;
    Object.entries(breakpoints).forEach(([key, value]) => {
      if (key === 'null') return;
      const min = String(value || '').trim();
      if (!min) return;
      idx += 1;
      const size = Number((baseSize * (increment ** idx)).toFixed(3));
      lines.push(`@media (min-width: ${min}) { html { font-size: ${size}px; } }`);
    });

    return lines.join('\n');
  }

  function buildTextUtilityOverrides(state) {
    const base = state && state.baseSettings ? state.baseSettings : {};
    const breakpoints = toEntriesMap(state && state.breakpoints, DEFAULT_BREAKPOINTS);
    const ratio = clampPositive(base.r, 1.2);
    const typeScale = generateTypeScale(ratio);
    const lines = [];

    Object.entries(typeScale).forEach(([key, value]) => {
      const rem = `${value}rem`;
      lines.push(`.text-${key}, .editor-styles-wrapper .text-${key} { font-size: ${rem} !important; line-height: 1.2; }`);
    });

    Object.entries(breakpoints).forEach(([bp, minWidth]) => {
      if (bp === 'null') return;
      const min = String(minWidth || '').trim();
      if (!min) return;
      lines.push(`@media (min-width: ${min}) {`);
      Object.entries(typeScale).forEach(([key, value]) => {
        const rem = `${value}rem`;
        const cls = `.${escapeClassToken(bp)}\\:text-${key}`;
        lines.push(`  ${cls}, .editor-styles-wrapper ${cls} { font-size: ${rem} !important; line-height: 1.2; }`);
      });
      lines.push('}');
    });

    return lines.join('\n');
  }

  function buildContainerCss(state) {
    const lines = [];
    const containers = state && state.containerMaxWidths && typeof state.containerMaxWidths === 'object'
      ? state.containerMaxWidths
      : {};
    const breakpoints = toEntriesMap(state && state.breakpoints, DEFAULT_BREAKPOINTS);

    lines.push('.container{width:100%;margin-right:auto;margin-left:auto;padding-right:1rem;padding-left:1rem;}');

    Object.entries(containers).forEach(([key, value]) => {
      const maxWidth = String(value || '').trim();
      const minWidth = String(breakpoints[key] || '').trim();
      if (!maxWidth || !minWidth || key === 'null') return;
      lines.push(`@media (min-width: ${minWidth}) { .container { max-width: ${maxWidth}; } }`);
    });

    return lines.join('\n');
  }

  function buildFontCss(state) {
    const pair = selectedPair(state);
    if (!pair) return '';

    const first = String(pair.first || '').trim();
    const second = String(pair.second || '').trim();
    if (!first && !second) return '';

    const primary = first || second;
    const secondary = second || first;

    return [
      ':root {',
      `  --wp--preset--font-family--primary: ${primary}, ui-sans-serif, system-ui, sans-serif;`,
      `  --wp--preset--font-family--secondary: ${secondary}, ui-sans-serif, system-ui, sans-serif;`,
      '}',
      `body, .editor-styles-wrapper { font-family: var(--wp--preset--font-family--primary) !important; }`,
      `.font-primary, .editor-styles-wrapper .font-primary { font-family: var(--wp--preset--font-family--primary) !important; }`,
      `.font-secondary, .editor-styles-wrapper .font-secondary { font-family: var(--wp--preset--font-family--secondary) !important; }`,
      `.paragraph, .editor-styles-wrapper .paragraph { font-family: var(--wp--preset--font-family--primary) !important; }`,
      `.heading, .editor-styles-wrapper .heading { font-family: var(--wp--preset--font-family--secondary) !important; }`
    ].join('\n');
  }

  function buildPreviewCss(state) {
    return [
      '/* LDS runtime preview */',
      buildRootCss(state),
      buildContainerCss(state),
      buildFontCss(state),
      buildTextUtilityOverrides(state)
    ].join('\n\n');
  }

  function setStyle(doc, cssText) {
    if (!doc || !doc.head) return;
    let tag = doc.getElementById(STYLE_ID);
    if (!tag) {
      tag = doc.createElement('style');
      tag.id = STYLE_ID;
      doc.head.appendChild(tag);
    }
    tag.textContent = cssText;
  }

  function setFontLink(doc, href) {
    if (!doc || !doc.head) return;
    let link = doc.getElementById(FONT_LINK_ID);
    if (!href) {
      if (link && link.parentNode) {
        link.parentNode.removeChild(link);
      }
      return;
    }
    if (!link) {
      link = doc.createElement('link');
      link.id = FONT_LINK_ID;
      link.rel = 'stylesheet';
      doc.head.appendChild(link);
    }
    if (link.href !== href) {
      link.href = href;
    }
  }

  function clearStyle(doc) {
    if (!doc) return;
    const tag = doc.getElementById(STYLE_ID);
    if (tag && tag.parentNode) {
      tag.parentNode.removeChild(tag);
    }
  }

  function getTargetDocs() {
    const docs = [document];
    const iframe = document.querySelector('iframe[name="editor-canvas"], iframe.edit-post-visual-editor__iframe');
    if (iframe && iframe.contentDocument) {
      docs.push(iframe.contentDocument);
    }
    return docs;
  }

  function applyPreviewToEditorAndCanvas(state) {
    const css = buildPreviewCss(state || {});
    const fontHref = buildGoogleFontUrl(selectedPair(state || {}));
    getTargetDocs().forEach((doc) => {
      setStyle(doc, css);
      setFontLink(doc, fontHref);
    });
  }

  function clearPreviewCss() {
    getTargetDocs().forEach((doc) => {
      clearStyle(doc);
      setFontLink(doc, '');
    });
  }

  window.LDSTwRuntimePreview = {
    buildPreviewCss,
    applyPreviewToEditorAndCanvas,
    clearPreviewCss
  };
})();
