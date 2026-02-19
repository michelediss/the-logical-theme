(function () {
  const root = document.getElementById('lds-tw-theme-tokens-root');
  if (!root || !window.ldsTwTokensUi) return;

  const api = {
    restBase: String(window.ldsTwTokensUi.restBase || '').replace(/\/$/, ''),
    nonce: window.ldsTwTokensUi.nonce || ''
  };

  let state = null;

  function request(path, options) {
    return fetch(api.restBase + path, {
      method: options && options.method ? options.method : 'GET',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-WP-Nonce': api.nonce
      },
      body: options && options.body ? JSON.stringify(options.body) : undefined
    }).then(async (res) => {
      const json = await res.json().catch(() => ({}));
      if (!res.ok) {
        const message = (json && json.message) || 'Request failed';
        throw new Error(message);
      }
      return json;
    });
  }

  function toRowMap(rows) {
    const out = {};
    (rows || []).forEach((row) => {
      const key = String(row.key || '').trim();
      const value = String(row.value || '').trim();
      if (!key) return;
      out[key] = value;
    });
    return out;
  }

  function fromMap(map) {
    return Object.entries(map || {}).map(([key, value]) => ({ key, value: String(value ?? '') }));
  }

  function normalizeColor(hex) {
    const raw = String(hex || '').trim().replace(/^#/, '').toLowerCase();
    if (/^[0-9a-f]{3}$/.test(raw)) {
      return '#' + raw.split('').map((c) => c + c).join('');
    }
    if (/^[0-9a-f]{6}$/.test(raw)) {
      return '#' + raw;
    }
    return '#000000';
  }

  function applyPreview(palette) {
    const targetRoots = [document.documentElement];
    const iframe = document.querySelector('iframe[name="editor-canvas"], iframe.edit-post-visual-editor__iframe');
    if (iframe && iframe.contentDocument && iframe.contentDocument.documentElement) {
      targetRoots.push(iframe.contentDocument.documentElement);
    }

    targetRoots.forEach((el) => {
      (palette || []).forEach((entry) => {
        const slug = String(entry.slug || '').trim();
        const color = normalizeColor(entry.color);
        if (!slug) return;
        const rgb = color
          .replace('#', '')
          .match(/.{2}/g)
          .map((c) => parseInt(c, 16))
          .join(' ');
        el.style.setProperty(`--color-${slug}`, color);
        el.style.setProperty(`--color-${slug}-rgb`, rgb);
      });
    });
  }

  function render() {
    if (!state) {
      root.innerHTML = '<p>Loading...</p>';
      return;
    }

    const paletteRows = (state.palette || []).map((item, idx) => `
      <tr>
        <td><input data-kind="palette-slug" data-index="${idx}" type="text" value="${item.slug || ''}" class="regular-text"></td>
        <td><input data-kind="palette-color" data-index="${idx}" type="color" value="${normalizeColor(item.color)}"></td>
        <td><input data-kind="palette-hex" data-index="${idx}" type="text" value="${normalizeColor(item.color)}" class="regular-text"></td>
        <td><button data-action="remove-palette" data-index="${idx}" class="button" type="button">Remove</button></td>
      </tr>
    `).join('');

    const breakpointRows = (state.breakpointsRows || []).map((row, idx) => `
      <tr>
        <td><input data-kind="bp-key" data-index="${idx}" type="text" value="${row.key || ''}" class="small-text"></td>
        <td><input data-kind="bp-value" data-index="${idx}" type="text" value="${row.value || ''}" class="regular-text"></td>
        <td><button data-action="remove-bp" data-index="${idx}" class="button" type="button">Remove</button></td>
      </tr>
    `).join('');

    const containerRows = (state.containerRows || []).map((row, idx) => `
      <tr>
        <td><input data-kind="ct-key" data-index="${idx}" type="text" value="${row.key || ''}" class="small-text"></td>
        <td><input data-kind="ct-value" data-index="${idx}" type="text" value="${row.value || ''}" class="regular-text"></td>
        <td><button data-action="remove-ct" data-index="${idx}" class="button" type="button">Remove</button></td>
      </tr>
    `).join('');

    const fontOptions = (state.fontPairings || []).map((pair) => {
      const selected = pair.id === state.fontPairing ? 'selected' : '';
      return `<option value="${pair.id}" ${selected}>${pair.label}</option>`;
    }).join('');

    root.innerHTML = `
      <div class="card" style="max-width:1200px;padding:16px;">
        <h2>Palette</h2>
        <table class="widefat striped"><thead><tr><th>Slug</th><th>Color</th><th>Hex</th><th></th></tr></thead><tbody>${paletteRows}</tbody></table>
        <p><button class="button" data-action="add-palette" type="button">Add color</button></p>

        <h2>Font Pairing</h2>
        <p><select id="lds-font-pairing" class="regular-text">${fontOptions}</select></p>

        <h2>Base Settings</h2>
        <p style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
          <label>baseSize <input id="lds-base-size" type="number" step="0.01" min="0.01" value="${state.baseSettings.baseSize}"></label>
          <label>r <input id="lds-ratio" type="number" step="0.001" min="0.001" value="${state.baseSettings.r}"></label>
          <label>incrementFactor <input id="lds-increment" type="number" step="0.001" min="0.001" value="${state.baseSettings.incrementFactor}"></label>
        </p>

        <h2>Breakpoints</h2>
        <table class="widefat striped"><thead><tr><th>Key</th><th>Value</th><th></th></tr></thead><tbody>${breakpointRows}</tbody></table>
        <p><button class="button" data-action="add-bp" type="button">Add breakpoint</button></p>

        <h2>Container Max Widths</h2>
        <table class="widefat striped"><thead><tr><th>Key</th><th>Value</th><th></th></tr></thead><tbody>${containerRows}</tbody></table>
        <p><button class="button" data-action="add-ct" type="button">Add container</button></p>

        <h2>Derived Layout (read-only)</h2>
        <p>contentSize: <code id="lds-derived-content-size">${state.layout.contentSize || ''}</code> &nbsp; wideSize: <code id="lds-derived-wide-size">${state.layout.wideSize || ''}</code></p>

        <p>
          <button class="button button-primary" data-action="save" type="button">Save</button>
        </p>
      </div>
    `;

    attachHandlers();
  }

  function renderDerivedLayout() {
    const contentSizeEl = root.querySelector('#lds-derived-content-size');
    const wideSizeEl = root.querySelector('#lds-derived-wide-size');
    if (contentSizeEl) {
      contentSizeEl.textContent = state && state.layout ? (state.layout.contentSize || '') : '';
    }
    if (wideSizeEl) {
      wideSizeEl.textContent = state && state.layout ? (state.layout.wideSize || '') : '';
    }
  }

  function recalcLayout() {
    const map = toRowMap(state.containerRows || []);
    state.layout = {
      contentSize: map.xl || '1140px',
      wideSize: map['2xl'] || map.xl || '1440px'
    };
  }

  function attachHandlers() {
    root.querySelectorAll('[data-action="add-palette"]').forEach((btn) => btn.addEventListener('click', () => {
      state.palette.push({ slug: '', color: '#000000' });
      render();
    }));
    root.querySelectorAll('[data-action="remove-palette"]').forEach((btn) => btn.addEventListener('click', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) state.palette.splice(i, 1);
      render();
    }));

    root.querySelectorAll('[data-kind="palette-slug"]').forEach((el) => el.addEventListener('input', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) state.palette[i].slug = e.currentTarget.value;
    }));
    root.querySelectorAll('[data-kind="palette-color"]').forEach((el) => el.addEventListener('input', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) {
        state.palette[i].color = normalizeColor(e.currentTarget.value);
        const hex = root.querySelector(`[data-kind="palette-hex"][data-index="${i}"]`);
        if (hex) hex.value = state.palette[i].color;
        applyPreview(state.palette);
      }
    }));
    root.querySelectorAll('[data-kind="palette-hex"]').forEach((el) => el.addEventListener('input', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) {
        state.palette[i].color = normalizeColor(e.currentTarget.value);
        const picker = root.querySelector(`[data-kind="palette-color"][data-index="${i}"]`);
        if (picker) picker.value = state.palette[i].color;
        applyPreview(state.palette);
      }
    }));

    root.querySelector('#lds-font-pairing')?.addEventListener('change', (e) => {
      state.fontPairing = e.currentTarget.value;
    });

    root.querySelector('#lds-base-size')?.addEventListener('input', (e) => {
      state.baseSettings.baseSize = Number(e.currentTarget.value || 16);
    });
    root.querySelector('#lds-ratio')?.addEventListener('input', (e) => {
      state.baseSettings.r = Number(e.currentTarget.value || 1.2);
    });
    root.querySelector('#lds-increment')?.addEventListener('input', (e) => {
      state.baseSettings.incrementFactor = Number(e.currentTarget.value || 1.01);
    });

    root.querySelectorAll('[data-action="add-bp"]').forEach((btn) => btn.addEventListener('click', () => {
      state.breakpointsRows.push({ key: '', value: '' });
      render();
    }));
    root.querySelectorAll('[data-action="remove-bp"]').forEach((btn) => btn.addEventListener('click', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) state.breakpointsRows.splice(i, 1);
      render();
    }));
    root.querySelectorAll('[data-kind="bp-key"]').forEach((el) => el.addEventListener('input', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) state.breakpointsRows[i].key = e.currentTarget.value;
    }));
    root.querySelectorAll('[data-kind="bp-value"]').forEach((el) => el.addEventListener('input', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) state.breakpointsRows[i].value = e.currentTarget.value;
    }));

    root.querySelectorAll('[data-action="add-ct"]').forEach((btn) => btn.addEventListener('click', () => {
      state.containerRows.push({ key: '', value: '' });
      render();
    }));
    root.querySelectorAll('[data-action="remove-ct"]').forEach((btn) => btn.addEventListener('click', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) state.containerRows.splice(i, 1);
      recalcLayout();
      render();
    }));
    root.querySelectorAll('[data-kind="ct-key"]').forEach((el) => el.addEventListener('input', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) state.containerRows[i].key = e.currentTarget.value;
      recalcLayout();
      renderDerivedLayout();
    }));
    root.querySelectorAll('[data-kind="ct-value"]').forEach((el) => el.addEventListener('input', (e) => {
      const i = Number(e.currentTarget.dataset.index || '-1');
      if (i >= 0) state.containerRows[i].value = e.currentTarget.value;
      recalcLayout();
      renderDerivedLayout();
    }));

    root.querySelector('[data-action="save"]')?.addEventListener('click', saveState);
  }

  async function saveState() {
    const payload = {
      palette: state.palette,
      breakpoints: toRowMap(state.breakpointsRows),
      containerMaxWidths: toRowMap(state.containerRows),
      baseSettings: state.baseSettings,
      fontPairing: state.fontPairing
    };

    try {
      const response = await request('/tokens', { method: 'POST', body: payload });
      const newState = response.state || {};
      state = {
        ...newState,
        breakpointsRows: fromMap(newState.breakpoints || {}),
        containerRows: fromMap(newState.containerMaxWidths || {})
      };
      applyPreview(state.palette);
      render();
      window.alert('Saved.');
    } catch (err) {
      window.alert('Save failed: ' + err.message);
    }
  }

  async function init() {
    try {
      const response = await request('/tokens', { method: 'GET' });
      const s = response.state || {};
      state = {
        ...s,
        breakpointsRows: fromMap(s.breakpoints || {}),
        containerRows: fromMap(s.containerMaxWidths || {})
      };
      applyPreview(state.palette);
      render();
    } catch (err) {
      root.innerHTML = `<div class="notice notice-error"><p>${err.message}</p></div>`;
    }
  }

  init();
})();
