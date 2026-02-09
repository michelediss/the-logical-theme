(function () {
  if (!window.ldsPreviewConfig || !window.ldsPreviewConfig.initialConfig) {
    return;
  }

  const cfg = window.ldsPreviewConfig;
  const scaleAbove = ['base', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', '8xl', '9xl'];
  const scaleBelow = ['xs', 'sm'];

  const state = {
    config: deepClone(cfg.initialConfig),
    initial: deepClone(cfg.initialConfig),
    pending: false
  };

  let dynamicStyle = null;
  let ui = {};

  function getFontPair(value) {
    if (!Array.isArray(cfg.fontPairs)) {
      return null;
    }
    return cfg.fontPairs.find((pair) => pair && pair.value === value) || null;
  }

  function deepClone(obj) {
    return JSON.parse(JSON.stringify(obj || {}));
  }

  function get(obj, path, fallback) {
    const parts = path.split('.');
    let cursor = obj;
    for (const part of parts) {
      if (!cursor || typeof cursor !== 'object' || !(part in cursor)) {
        return fallback;
      }
      cursor = cursor[part];
    }
    return cursor;
  }

  function set(obj, path, value) {
    const parts = path.split('.');
    let cursor = obj;
    for (let i = 0; i < parts.length - 1; i += 1) {
      if (!cursor[parts[i]] || typeof cursor[parts[i]] !== 'object') {
        cursor[parts[i]] = {};
      }
      cursor = cursor[parts[i]];
    }
    cursor[parts[parts.length - 1]] = value;
  }

  function ensureObject(obj, key) {
    if (!obj[key] || typeof obj[key] !== 'object') {
      obj[key] = {};
    }
    return obj[key];
  }

  function parseColor(input) {
    if (typeof input !== 'string') {
      return null;
    }

    const v = input.trim();

    if (/^#([\da-f]{3}|[\da-f]{6})$/i.test(v)) {
      let hex = v.slice(1);
      if (hex.length === 3) {
        hex = hex.split('').map((ch) => ch + ch).join('');
      }
      const num = parseInt(hex, 16);
      return {
        r: (num >> 16) & 255,
        g: (num >> 8) & 255,
        b: num & 255
      };
    }

    const m = v.match(/^rgba?\(([^)]+)\)$/i);
    if (m) {
      const raw = m[1].split(',').slice(0, 3).map((x) => parseFloat(x.trim()));
      if (raw.length === 3 && raw.every((n) => Number.isFinite(n))) {
        return {
          r: clamp(raw[0], 0, 255),
          g: clamp(raw[1], 0, 255),
          b: clamp(raw[2], 0, 255)
        };
      }
    }

    return null;
  }

  function toHex(rgb) {
    if (!rgb) {
      return '#000000';
    }
    const n = (Math.round(rgb.r) << 16) | (Math.round(rgb.g) << 8) | Math.round(rgb.b);
    return `#${n.toString(16).padStart(6, '0')}`;
  }

  function toRgbString(rgb) {
    return `rgb(${round3(rgb.r)}, ${round3(rgb.g)}, ${round3(rgb.b)})`;
  }

  function toRgbCsv(rgb) {
    return `${Math.round(rgb.r)}, ${Math.round(rgb.g)}, ${Math.round(rgb.b)}`;
  }

  function round3(n) {
    return Math.round(n * 1000) / 1000;
  }

  function clamp(n, min, max) {
    return Math.max(min, Math.min(max, n));
  }

  function mix(c1, c2, weightPct) {
    const w = clamp(Number(weightPct) / 100, 0, 1);
    return {
      r: c1.r * w + c2.r * (1 - w),
      g: c1.g * w + c2.g * (1 - w),
      b: c1.b * w + c2.b * (1 - w)
    };
  }

  function normalizeConfig(config) {
    ensureObject(config, 'baseSettings');
    // Migrate legacy shape { paragraph: {...}, heading: {...} } to a single global scale.
    if (
      !Object.prototype.hasOwnProperty.call(config.baseSettings, 'baseSize') &&
      config.baseSettings.paragraph &&
      typeof config.baseSettings.paragraph === 'object'
    ) {
      const p = config.baseSettings.paragraph;
      config.baseSettings = {
        baseSize: Number.isFinite(Number(p.baseSize)) ? Number(p.baseSize) : 16,
        r: Number.isFinite(Number(p.r)) ? Number(p.r) : 1.2,
        incrementFactor: Number.isFinite(Number(p.incrementFactor)) ? Number(p.incrementFactor) : 1.01
      };
    }
    if (!Number.isFinite(Number(config.baseSettings.baseSize))) {
      config.baseSettings.baseSize = 16;
    }
    if (!Number.isFinite(Number(config.baseSettings.r))) {
      config.baseSettings.r = 1.2;
    }
    if (!Number.isFinite(Number(config.baseSettings.incrementFactor))) {
      config.baseSettings.incrementFactor = 1.01;
    }
    ensureObject(config, 'baseColors');
    ensureObject(config, 'bootstrap');
    ensureObject(config, 'colorVariations');
    ensureObject(config, 'breakpoints');
    ensureObject(config, 'containerMaxWidths');
    ensureObject(config, 'font');

    if (!Array.isArray(config.font.imports)) {
      config.font.imports = [];
    }
    ensureObject(config.font, 'classes');

    if (typeof config.rounded === 'undefined') {
      config.rounded = 0;
    }

    return config;
  }

  function pow(base, exponent) {
    return Math.pow(base, exponent);
  }

  function generateTypographicScale(ratio) {
    const out = {};
    for (let i = 0; i < scaleAbove.length; i += 1) {
      out[scaleAbove[i]] = round3(pow(Number(ratio), i));
    }
    for (let i = 1; i <= scaleBelow.length; i += 1) {
      const key = scaleBelow[scaleBelow.length - i];
      out[key] = round3(1 / pow(Number(ratio), i));
    }
    return out;
  }

  function buildColorScale(name, baseColor, variation, blackColor, whiteColor) {
    const base = parseColor(baseColor);
    const black = parseColor(blackColor) || { r: 30, g: 32, b: 31 };
    const white = parseColor(whiteColor) || { r: 255, g: 255, b: 255 };
    if (!base) {
      return null;
    }

    const map = {};
    const whitePct = variation && variation.white;
    const lightPct = variation && variation.light;
    const darkPct = variation && variation.dark;
    const blackPct = variation && variation.black;

    if (whitePct !== undefined && whitePct !== null && whitePct !== '') {
      map[`${name}-white`] = toRgbString(mix(white, base, Number(whitePct)));
    }
    if (lightPct !== undefined && lightPct !== null && lightPct !== '') {
      map[`${name}-light`] = toRgbString(mix(white, base, Number(lightPct)));
    }
    map[name] = toHex(base);
    if (darkPct !== undefined && darkPct !== null && darkPct !== '') {
      map[`${name}-dark`] = toRgbString(mix(black, base, Number(darkPct)));
    }
    if (blackPct !== undefined && blackPct !== null && blackPct !== '') {
      map[`${name}-black`] = toRgbString(mix(black, base, Number(blackPct)));
    }

    return map;
  }

  function buildPreviewCss(inputConfig) {
    const config = normalizeConfig(deepClone(inputConfig));

    const baseColors = config.baseColors || {};
    const bootstrap = config.bootstrap || {};
    const themeExtra = bootstrap.themeColors && typeof bootstrap.themeColors === 'object' ? bootstrap.themeColors : {};
    const variations = config.colorVariations || {};
    const breakpoints = config.breakpoints || {};
    const containerMaxWidths = config.containerMaxWidths || {};

    const black = baseColors.black || '#1e201f';
    const white = baseColors.white || '#ffffff';
    const gray = baseColors.gray || '#666666';
    const primary = bootstrap.primary || '#f53b3e';
    const secondary = bootstrap.secondary || '#0e2258';

    const themeColors = Object.assign(
      {
        primary,
        secondary,
        black,
        white,
        gray
      },
      themeExtra
    );

    const css = [];
    const importsCss = [];
    const rootVars = [];

    if (config.font && Array.isArray(config.font.imports)) {
      config.font.imports.forEach((entry) => {
        if (typeof entry !== 'string' || !entry.trim()) {
          return;
        }
        if (/^https?:\/\//i.test(entry)) {
          importsCss.push(`@import url('${entry}');`);
        } else {
          const pair = getFontPair(entry);
          if (pair && Array.isArray(pair.imports)) {
            pair.imports.forEach((url) => {
              if (typeof url === 'string' && /^https?:\/\//i.test(url)) {
                importsCss.push(`@import url('${url}');`);
              }
            });
          }
          if (pair && pair.classes && typeof pair.classes === 'object') {
            Object.entries(pair.classes).forEach(([selector, rules]) => {
              if (!selector || !rules || typeof rules !== 'object') {
                return;
              }
              const props = Object.entries(rules)
                .map(([prop, value]) => `${prop}:${value};`)
                .join('');
              css.push(`${selector}{${props}}`);
            });
          }
        }
      });
    }

    rootVars.push(`--black: ${black};`);
    rootVars.push(`--white: ${white};`);
    rootVars.push(`--bs-black: ${black};`);
    rootVars.push(`--bs-white: ${white};`);
    rootVars.push(`--bs-gray-500: ${gray};`);
    rootVars.push(`--bs-primary: ${primary};`);
    rootVars.push(`--bs-secondary: ${secondary};`);

    Object.entries(themeColors).forEach(([name, colorValue]) => {
      const rgb = parseColor(colorValue);
      if (!rgb) {
        return;
      }
      rootVars.push(`--bs-${name}: ${colorValue};`);
      rootVars.push(`--bs-${name}-rgb: ${toRgbCsv(rgb)};`);
    });

    const primaryRgb = parseColor(primary);
    if (primaryRgb) {
      rootVars.push(`--bs-link-color: ${primary};`);
      rootVars.push(`--bs-link-color-rgb: ${toRgbCsv(primaryRgb)};`);
    }

    const roundedOff = Number(config.rounded) === 0;
    if (roundedOff) {
      rootVars.push('--bs-border-radius: 0;');
      rootVars.push('--bs-border-radius-sm: 0;');
      rootVars.push('--bs-border-radius-lg: 0;');
      rootVars.push('--bs-border-radius-xl: 0;');
      rootVars.push('--bs-border-radius-xxl: 0;');
      rootVars.push('--bs-border-radius-pill: 0;');
    } else {
      // Restore Bootstrap defaults for rounded mode.
      rootVars.push('--bs-border-radius: 0.375rem;');
      rootVars.push('--bs-border-radius-sm: 0.25rem;');
      rootVars.push('--bs-border-radius-lg: 0.5rem;');
      rootVars.push('--bs-border-radius-xl: 1rem;');
      rootVars.push('--bs-border-radius-xxl: 2rem;');
      rootVars.push('--bs-border-radius-pill: 50rem;');
    }

    // Force runtime rounded behavior even when compiled CSS has fixed radii.
    css.push('.btn,.form-control,.form-select,.input-group-text,.card,.dropdown-menu,.offcanvas,.modal-content,.alert,.badge,.nav-link,.page-link,.list-group-item,.popover,.tooltip-inner,.toast,.navbar-toggler,.accordion-item,.accordion-button{border-radius:var(--bs-border-radius)!important;}');
    css.push('.btn-sm,.form-control-sm,.form-select-sm,.input-group-sm>.form-control,.input-group-sm>.form-select,.input-group-sm>.input-group-text{border-radius:var(--bs-border-radius-sm)!important;}');
    css.push('.btn-lg,.form-control-lg,.form-select-lg,.input-group-lg>.form-control,.input-group-lg>.form-select,.input-group-lg>.input-group-text{border-radius:var(--bs-border-radius-lg)!important;}');
    css.push('.rounded{border-radius:var(--bs-border-radius)!important;}.rounded-1{border-radius:var(--bs-border-radius-sm)!important;}.rounded-2{border-radius:var(--bs-border-radius)!important;}.rounded-3{border-radius:var(--bs-border-radius-lg)!important;}.rounded-4{border-radius:var(--bs-border-radius-xl)!important;}.rounded-5{border-radius:var(--bs-border-radius-xxl)!important;}.rounded-pill{border-radius:var(--bs-border-radius-pill)!important;}');

    Object.entries(breakpoints).forEach(([name, value]) => {
      rootVars.push(`--bs-breakpoint-${name === 'null' ? '' : name}: ${value};`);
    });

    const globalRatio = Number(get(config, 'baseSettings.r', 1.2));
    const globalScale = generateTypographicScale(globalRatio);
    ['paragraph', 'heading', 'caption'].forEach((type) => {
      Object.entries(globalScale).forEach(([scaleName, rem]) => {
        rootVars.push(`--${type}-font-${scaleName}: ${rem}rem;`);
      });
      Object.entries(globalScale).forEach(([scaleName, rem]) => {
        css.push(`.${type}.text-${scaleName}{font-size:${rem}rem;}`);
      });
      Object.entries(breakpoints).forEach(([bp, bpWidth]) => {
        css.push(`@media (min-width:${bpWidth}){`);
        Object.entries(globalScale).forEach(([scaleName, rem]) => {
          css.push(`.${type}.text-${bp}-${scaleName}{font-size:${rem}rem;}`);
        });
        css.push('}');
      });
    });

    const paragraphBase = get(config, 'baseSettings.baseSize', 16);
    const paragraphIncrement = get(config, 'baseSettings.incrementFactor', 1.01);
    const bpEntries = Object.entries(breakpoints);
    if (Number.isFinite(Number(paragraphBase))) {
      css.push(`html{font-size:${Number(paragraphBase)}px;}`);
      bpEntries.forEach(([_, width], index) => {
        const size = round3(Number(paragraphBase) * pow(Number(paragraphIncrement), index + 1));
        css.push(`@media (min-width:${width}){html{font-size:${size}px;}}`);
      });
    }

    Object.entries(themeColors).forEach(([name, baseColor]) => {
      if (name === 'black' || name === 'white') {
        return;
      }
      const scale = buildColorScale(name, baseColor, variations[name] || {}, black, white);
      if (!scale) {
        return;
      }

      Object.entries(scale).forEach(([variantName, variantColor]) => {
        rootVars.push(`--${variantName}: ${variantColor};`);
        css.push(`.bg-${variantName}{background-color:${variantColor} !important;}`);
        css.push(`.text-${variantName}{color:${variantColor} !important;}`);
        css.push(`.border-${variantName}{border-color:${variantColor} !important;}`);
        css.push(`.fill-${variantName}{fill:${variantColor} !important;}`);
      });
    });

    css.push(`.bg-black{background-color:${black} !important;}`);
    css.push(`.text-black{color:${black} !important;}`);
    css.push(`.border-black{border-color:${black} !important;}`);
    css.push(`.fill-black{fill:${black} !important;}`);
    css.push(`.bg-white{background-color:${white} !important;}`);
    css.push(`.text-white{color:${white} !important;}`);
    css.push(`.border-white{border-color:${white} !important;}`);
    css.push(`.fill-white{fill:${white} !important;}`);

    Object.entries(breakpoints).forEach(([bp, bpWidth]) => {
      if (containerMaxWidths[bp]) {
        css.push(`@media (min-width:${bpWidth}){.container{max-width:${containerMaxWidths[bp]};}}`);
      }
    });

    if (config.font && config.font.classes && typeof config.font.classes === 'object') {
      Object.entries(config.font.classes).forEach(([selector, rules]) => {
        if (!selector || !rules || typeof rules !== 'object') {
          return;
        }
        const props = Object.entries(rules)
          .map(([prop, value]) => `${prop}:${value};`)
          .join('');
        css.push(`${selector}{${props}}`);
      });
    }

    css.unshift(`:root{${rootVars.join('')}}`);
    return importsCss.join('') + css.join('');
  }

  function ensureDynamicStyleTag() {
    if (!dynamicStyle) {
      dynamicStyle = document.createElement('style');
      dynamicStyle.id = 'lds-preview-dynamic-style';
      document.head.appendChild(dynamicStyle);
    }
    return dynamicStyle;
  }

  function syncJsonTextarea() {
    if (ui.rawJson) {
      ui.rawJson.value = JSON.stringify(state.config, null, 2);
    }
  }

  function updateStatus(message, isError) {
    if (!ui.status) {
      return;
    }
    ui.status.textContent = message;
    ui.status.style.color = isError ? '#b91c1c' : '#374151';
  }

  function applyPreview() {
    normalizeConfig(state.config);
    const css = buildPreviewCss(state.config);
    ensureDynamicStyleTag().textContent = css;
    updateStatus('Preview aggiornata in tempo reale.', false);
  }

  function updateBasicFieldsFromConfig() {
    const p = parseColor(get(state.config, 'bootstrap.primary', '#f53b3e'));
    const s = parseColor(get(state.config, 'bootstrap.secondary', '#0e2258'));
    const b = parseColor(get(state.config, 'baseColors.black', '#1e201f'));
    const w = parseColor(get(state.config, 'baseColors.white', '#ffffff'));
    const g = parseColor(get(state.config, 'baseColors.gray', '#666666'));

    if (ui.primary && p) ui.primary.value = toHex(p);
    if (ui.secondary && s) ui.secondary.value = toHex(s);
    if (ui.black && b) ui.black.value = toHex(b);
    if (ui.white && w) ui.white.value = toHex(w);
    if (ui.gray && g) ui.gray.value = toHex(g);

    if (ui.rounded) {
      ui.rounded.value = Number(state.config.rounded) === 0 ? '0' : '1';
    }

    if (ui.globalBase) ui.globalBase.value = get(state.config, 'baseSettings.baseSize', 16);
    if (ui.globalR) ui.globalR.value = get(state.config, 'baseSettings.r', 1.2);
    if (ui.globalIncrement) ui.globalIncrement.value = get(state.config, 'baseSettings.incrementFactor', 1.01);
    if (ui.globalBaseRange && ui.globalBase) ui.globalBaseRange.value = ui.globalBase.value;
    if (ui.globalRRange && ui.globalR) ui.globalRRange.value = ui.globalR.value;
    if (ui.globalIncrementRange && ui.globalIncrement) ui.globalIncrementRange.value = ui.globalIncrement.value;

    if (ui.fontPair) {
      const imports = get(state.config, 'font.imports', []);
      const first = Array.isArray(imports) && imports.length ? imports[0] : '';
      let matched = '';
      if (Array.isArray(cfg.fontPairs)) {
        const found = cfg.fontPairs.find((p) => {
          if (!p) return false;
          if (p.value === first) return true;
          if (Array.isArray(p.imports) && p.imports.length && p.imports[0] === first) return true;
          return false;
        });
        matched = found ? found.value : '';
      }
      ui.fontPair.value = matched;
    }
  }

  function onBasicInput(path, cast, linkedField) {
    return function (evt) {
      const raw = evt.target.value;
      set(state.config, path, cast ? cast(raw) : raw);
      if (linkedField) {
        linkedField.value = raw;
      }
      syncJsonTextarea();
      applyPreview();
    };
  }

  function bindBasicInputs() {
    ui.primary.addEventListener('input', onBasicInput('bootstrap.primary'));
    ui.secondary.addEventListener('input', onBasicInput('bootstrap.secondary'));
    ui.black.addEventListener('input', onBasicInput('baseColors.black'));
    ui.white.addEventListener('input', onBasicInput('baseColors.white'));
    ui.gray.addEventListener('input', onBasicInput('baseColors.gray'));
    ui.rounded.addEventListener('change', onBasicInput('rounded', (v) => Number(v)));

    ui.globalBase.addEventListener('input', onBasicInput('baseSettings.baseSize', (v) => Number(v), ui.globalBaseRange));
    ui.globalBaseRange.addEventListener('input', onBasicInput('baseSettings.baseSize', (v) => Number(v), ui.globalBase));
    ui.globalR.addEventListener('input', onBasicInput('baseSettings.r', (v) => Number(v), ui.globalRRange));
    ui.globalRRange.addEventListener('input', onBasicInput('baseSettings.r', (v) => Number(v), ui.globalR));
    ui.globalIncrement.addEventListener('input', onBasicInput('baseSettings.incrementFactor', (v) => Number(v), ui.globalIncrementRange));
    ui.globalIncrementRange.addEventListener('input', onBasicInput('baseSettings.incrementFactor', (v) => Number(v), ui.globalIncrement));

    if (ui.fontPair) {
      ui.fontPair.addEventListener('change', function (evt) {
        ensureObject(state.config, 'font');
        const selected = evt.target.value;
        const pair = getFontPair(selected);
        if (!selected || !pair) {
          state.config.font.imports = [];
          ensureObject(state.config.font, 'classes');
          state.config.font.classes = {};
        } else {
          if (Array.isArray(pair.imports) && pair.imports.length) {
            state.config.font.imports = pair.imports.slice();
          } else {
            state.config.font.imports = [pair.value];
          }
          ensureObject(state.config.font, 'classes');
          state.config.font.classes = pair.classes && typeof pair.classes === 'object'
            ? deepClone(pair.classes)
            : {};
        }
        syncJsonTextarea();
        applyPreview();
      });
    }
  }

  function setPending(flag) {
    state.pending = flag;
    if (ui.apply) {
      ui.apply.disabled = flag;
      ui.apply.textContent = flag ? 'Build in corso...' : 'Applica e Builda';
    }
  }

  function cycleFontPair(step) {
    if (!ui.fontPair) {
      return;
    }
    const options = Array.from(ui.fontPair.options).filter((opt) => opt.value);
    if (!options.length) {
      return;
    }
    const currentIndex = options.findIndex((opt) => opt.value === ui.fontPair.value);
    let nextIndex = currentIndex + step;
    if (currentIndex < 0) {
      nextIndex = step > 0 ? 0 : options.length - 1;
    }
    if (nextIndex < 0) {
      nextIndex = options.length - 1;
    }
    if (nextIndex >= options.length) {
      nextIndex = 0;
    }

    ui.fontPair.value = options[nextIndex].value;
    ui.fontPair.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function bindFontPairKeyboardNavigation() {
    document.addEventListener('keydown', function (evt) {
      if (!ui.panel || !ui.panel.classList.contains('is-open') || !ui.fontPair) {
        return;
      }
      if (evt.key !== 'ArrowUp' && evt.key !== 'ArrowDown') {
        return;
      }

      const target = evt.target;
      const tag = target && target.tagName ? target.tagName.toLowerCase() : '';
      const type = target && target.type ? String(target.type).toLowerCase() : '';
      const isEditableInput = tag === 'textarea' || (tag === 'input' && type !== 'button' && type !== 'submit' && type !== 'reset' && type !== 'checkbox' && type !== 'radio');
      if (isEditableInput) {
        return;
      }
      if (tag === 'select' && target !== ui.fontPair) {
        return;
      }

      evt.preventDefault();
      cycleFontPair(evt.key === 'ArrowDown' ? 1 : -1);
    });
  }

  function reloadAfterCompilation() {
    window.setTimeout(function () {
      window.location.reload();
    }, 500);
  }

  async function postAjax(action, payload) {
    const body = new URLSearchParams();
    body.set('action', action);
    body.set('nonce', cfg.nonce);
    Object.entries(payload || {}).forEach(([k, v]) => body.set(k, v));

    const res = await fetch(cfg.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: body.toString()
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    return res.json();
  }

  async function handleApply() {
    if (!cfg.pipelineReady) {
      updateStatus('Pipeline LDS non disponibile. Attiva logical-design-system.', true);
      return;
    }

    setPending(true);
    updateStatus('Salvataggio JSON e compilazione in corso...', false);

    try {
      const response = await postAjax('lds_preview_apply', {
        config: JSON.stringify(state.config)
      });

      if (!response || !response.success) {
        const message = response && response.data ? response.data : 'Errore in fase di apply.';
        throw new Error(message);
      }

      updateStatus(response.data.message || 'Build completata.', false);
      reloadAfterCompilation();
    } catch (err) {
      updateStatus(`Errore: ${err.message}`, true);
    } finally {
      setPending(false);
    }
  }

  function bindJsonActions() {
    ui.previewJson.addEventListener('click', function () {
      try {
        const parsed = JSON.parse(ui.rawJson.value);
        state.config = normalizeConfig(parsed);
        updateBasicFieldsFromConfig();
        applyPreview();
      } catch (err) {
        updateStatus(`JSON non valido: ${err.message}`, true);
      }
    });

    ui.reset.addEventListener('click', function () {
      state.config = deepClone(state.initial);
      updateBasicFieldsFromConfig();
      syncJsonTextarea();
      applyPreview();
    });

    ui.apply.addEventListener('click', handleApply);
  }

  function bindPanel() {
    ui.open.addEventListener('click', function () {
      ui.panel.classList.add('is-open');
      ui.backdrop.classList.add('is-open');
    });

    ui.close.addEventListener('click', function () {
      ui.panel.classList.remove('is-open');
      ui.backdrop.classList.remove('is-open');
    });

    ui.backdrop.addEventListener('click', function () {
      ui.panel.classList.remove('is-open');
      ui.backdrop.classList.remove('is-open');
    });
  }

  function createUi() {
    const fontPairOptions = (Array.isArray(cfg.fontPairs) ? cfg.fontPairs : [])
      .map((pair) => `<option value="${pair.value}">${pair.label}</option>`)
      .join('');

    const html = `
      <button id="lds-preview-toggle" type="button">LDS Live Editor</button>
      <div id="lds-preview-backdrop"></div>
      <aside id="lds-preview-panel" aria-hidden="true">
        <div class="lds-preview-head">
          <h3>Logical Design System Live Editor</h3>
          <button id="lds-preview-close" type="button" aria-label="Chiudi">×</button>
        </div>
        <div class="lds-preview-body">
          <div id="lds-preview-status"></div>

          <div class="lds-preview-title">Token Principali</div>
          <div class="lds-preview-grid">
            <div class="lds-preview-field"><label>Primary</label><input class="lds-color-input" id="lds-primary" type="color" /></div>
            <div class="lds-preview-field"><label>Secondary</label><input class="lds-color-input" id="lds-secondary" type="color" /></div>
            <div class="lds-preview-field"><label>Black</label><input class="lds-color-input" id="lds-black" type="color" /></div>
            <div class="lds-preview-field"><label>White</label><input class="lds-color-input" id="lds-white" type="color" /></div>
            <div class="lds-preview-field"><label>Gray</label><input class="lds-color-input" id="lds-gray" type="color" /></div>
            <div class="lds-preview-field"><label>Coppia Font</label>
              <select id="lds-font-pair"><option value="">Custom/Manuale</option>${fontPairOptions}</select>
            </div>
          </div>

          <div class="lds-preview-title">Typography Globale</div>
          <div class="lds-preview-grid">
            <div class="lds-preview-field"><label>baseSize</label><input id="lds-global-base" type="number" step="0.001" /><input id="lds-global-base-range" type="range" min="12" max="24" step="0.1" /></div>
            <div class="lds-preview-field"><label>r</label><input id="lds-global-r" type="number" step="0.001" /><input id="lds-global-r-range" type="range" min="1" max="2" step="0.005" /></div>
            <div class="lds-preview-field"><label>incrementFactor</label><input id="lds-global-inc" type="number" step="0.001" /><input id="lds-global-inc-range" type="range" min="1" max="1.08" step="0.001" /></div>
          </div>

          <div class="lds-preview-title">Rounded</div>
          <div class="lds-preview-grid">
            <div class="lds-preview-field"><label>Bordi Arrotondati</label>
              <select id="lds-rounded"><option value="0">No</option><option value="1">Yes</option></select>
            </div>
          </div>

          <div class="lds-preview-title">JSON Completo</div>
          <div class="lds-preview-field">
            <label>Modifica completa (baseSettings globale, breakpoint, container, font.classes, variations, ecc.)</label>
            <textarea id="lds-raw-json"></textarea>
          </div>

          <div class="lds-preview-actions">
            <button id="lds-reset" type="button">Reset</button>
            <button id="lds-preview-json" type="button">Preview JSON</button>
            <button id="lds-apply" type="button" data-role="apply" style="grid-column:1 / -1">Applica e Builda</button>
          </div>
        </div>
      </aside>
    `;

    const wrap = document.createElement('div');
    wrap.id = 'lds-preview-root';
    wrap.innerHTML = html;
    document.body.appendChild(wrap);

    ui = {
      root: wrap,
      open: wrap.querySelector('#lds-preview-toggle'),
      backdrop: wrap.querySelector('#lds-preview-backdrop'),
      panel: wrap.querySelector('#lds-preview-panel'),
      close: wrap.querySelector('#lds-preview-close'),
      status: wrap.querySelector('#lds-preview-status'),
      primary: wrap.querySelector('#lds-primary'),
      secondary: wrap.querySelector('#lds-secondary'),
      black: wrap.querySelector('#lds-black'),
      white: wrap.querySelector('#lds-white'),
      gray: wrap.querySelector('#lds-gray'),
      fontPair: wrap.querySelector('#lds-font-pair'),
      rounded: wrap.querySelector('#lds-rounded'),
      globalBase: wrap.querySelector('#lds-global-base'),
      globalBaseRange: wrap.querySelector('#lds-global-base-range'),
      globalR: wrap.querySelector('#lds-global-r'),
      globalRRange: wrap.querySelector('#lds-global-r-range'),
      globalIncrement: wrap.querySelector('#lds-global-inc'),
      globalIncrementRange: wrap.querySelector('#lds-global-inc-range'),
      rawJson: wrap.querySelector('#lds-raw-json'),
      reset: wrap.querySelector('#lds-reset'),
      previewJson: wrap.querySelector('#lds-preview-json'),
      apply: wrap.querySelector('#lds-apply')
    };
  }

  function init() {
    normalizeConfig(state.config);
    createUi();
    bindPanel();
    bindBasicInputs();
    bindJsonActions();
    bindFontPairKeyboardNavigation();
    updateBasicFieldsFromConfig();
    syncJsonTextarea();
    applyPreview();

    if (!cfg.pipelineReady) {
      updateStatus('logical-design-system non disponibile: preview attiva, apply disabilitato.', true);
      ui.apply.disabled = true;
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
