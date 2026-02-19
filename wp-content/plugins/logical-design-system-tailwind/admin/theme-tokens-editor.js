(function (wp) {
  if (!wp || !wp.plugins || !wp.editPost || !wp.element || !wp.components || !wp.apiFetch) {
    return;
  }

  const { registerPlugin } = wp.plugins;
  const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
  const { createElement: el, useEffect, useState } = wp.element;
  const { PanelBody, Button, TextControl, SelectControl, Notice } = wp.components;
  const apiFetch = wp.apiFetch;

  if (!window.ldsTwTokensEditor) {
    return;
  }

  const runtimePreview = window.LDSTwRuntimePreview || null;
  apiFetch.use(apiFetch.createNonceMiddleware(window.ldsTwTokensEditor.nonce));

  const endpoint = '/lds-tw/v1';

  function normalizeColor(hex) {
    const raw = String(hex || '').trim().replace(/^#/, '').toLowerCase();
    if (/^[0-9a-f]{3}$/.test(raw)) return '#' + raw.split('').map((c) => c + c).join('');
    if (/^[0-9a-f]{6}$/.test(raw)) return '#' + raw;
    return '#000000';
  }

  function mapToRows(map) {
    return Object.entries(map || {}).map(([key, value]) => ({ key, value: String(value ?? '') }));
  }

  function rowsToMap(rows) {
    const out = {};
    (rows || []).forEach((row) => {
      const key = String(row && row.key ? row.key : '').trim();
      const value = String(row && row.value ? row.value : '').trim();
      if (!key) return;
      out[key] = value;
    });
    return out;
  }

  function normalizeState(rawState) {
    const state = rawState || {};
    return {
      ...state,
      palette: Array.isArray(state.palette) ? state.palette : [],
      fontPairings: Array.isArray(state.fontPairings) ? state.fontPairings : [],
      breakpointRows: mapToRows(state.breakpoints || {}),
      containerRows: mapToRows(state.containerMaxWidths || {})
    };
  }

  function deriveStateForPreview(state) {
    return {
      palette: state.palette || [],
      fontPairing: state.fontPairing || '',
      fontPairings: state.fontPairings || [],
      breakpoints: rowsToMap(state.breakpointRows || []),
      containerMaxWidths: rowsToMap(state.containerRows || []),
      baseSettings: state.baseSettings || {}
    };
  }

  function applyPreview(state) {
    if (!runtimePreview || typeof runtimePreview.applyPreviewToEditorAndCanvas !== 'function') {
      return;
    }
    runtimePreview.applyPreviewToEditorAndCanvas(deriveStateForPreview(state));
  }

  function TokenSidebar() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [state, setState] = useState(null);
    const [error, setError] = useState('');

    const setAndPreview = (updater) => {
      setState((prev) => {
        const next = typeof updater === 'function' ? updater(prev) : updater;
        if (next) {
          applyPreview(next);
        }
        return next;
      });
    };

    const fetchState = async () => {
      setError('');
      try {
        const response = await apiFetch({ path: endpoint + '/tokens' });
        const next = normalizeState(response.state || null);
        setState(next);
        applyPreview(next);
      } catch (err) {
        setError(err.message || 'Request failed');
      } finally {
        setLoading(false);
      }
    };

    useEffect(() => {
      fetchState();
      return function cleanup() {
        if (runtimePreview && typeof runtimePreview.clearPreviewCss === 'function') {
          runtimePreview.clearPreviewCss();
        }
      };
    }, []);

    const updatePalette = (index, key, value) => {
      setAndPreview((prev) => {
        if (!prev) return prev;
        const palette = [...(prev.palette || [])];
        palette[index] = { ...palette[index], [key]: value };
        return { ...prev, palette };
      });
    };

    const updateBase = (key, value) => {
      setAndPreview((prev) => prev ? { ...prev, baseSettings: { ...(prev.baseSettings || {}), [key]: Number(value) } } : prev);
    };

    const updateBreakpointRow = (index, key, value) => {
      setAndPreview((prev) => {
        if (!prev) return prev;
        const rows = [...(prev.breakpointRows || [])];
        rows[index] = { ...rows[index], [key]: value };
        return { ...prev, breakpointRows: rows };
      });
    };

    const updateContainerRow = (index, key, value) => {
      setAndPreview((prev) => {
        if (!prev) return prev;
        const rows = [...(prev.containerRows || [])];
        rows[index] = { ...rows[index], [key]: value };
        return { ...prev, containerRows: rows };
      });
    };

    const save = async () => {
      if (!state) return;
      setSaving(true);
      setError('');
      try {
        const payload = {
          palette: state.palette || [],
          breakpoints: rowsToMap(state.breakpointRows || []),
          containerMaxWidths: rowsToMap(state.containerRows || []),
          baseSettings: state.baseSettings || {},
          fontPairing: state.fontPairing || ''
        };
        const response = await apiFetch({ path: endpoint + '/tokens', method: 'POST', data: payload });
        const next = normalizeState(response.state || state);
        setState(next);
        applyPreview(next);
        window.alert('Saved.');
      } catch (err) {
        setError(err.message || 'Save failed');
      } finally {
        setSaving(false);
      }
    };

    if (loading) {
      return el('div', { style: { padding: '12px' } }, 'Loading tokens...');
    }

    if (!state) {
      return el('div', { style: { padding: '12px' } }, 'No token state available');
    }

    return el('div', { style: { padding: '12px' } },
      error ? el(Notice, { status: 'error', isDismissible: true, onRemove: () => setError('') }, error) : null,
      el(PanelBody, { title: 'Palette', initialOpen: true },
        ...(state.palette || []).map((entry, index) => el('div', { key: `p-${index}`, style: { marginBottom: '10px', borderBottom: '1px solid #eee', paddingBottom: '10px' } },
          el(TextControl, {
            label: `Slug ${index + 1}`,
            value: entry.slug || '',
            onChange: (value) => updatePalette(index, 'slug', value)
          }),
          el('label', { style: { display: 'block', marginBottom: '4px' } }, 'Color'),
          el('input', {
            type: 'color',
            value: normalizeColor(entry.color),
            onInput: (event) => updatePalette(index, 'color', event.target.value)
          })
        )),
        el(Button, {
          variant: 'secondary',
          onClick: () => setAndPreview((prev) => ({ ...prev, palette: [...(prev.palette || []), { slug: '', color: '#000000' }] }))
        }, 'Add color')
      ),
      el(PanelBody, { title: 'Typography', initialOpen: false },
        el(SelectControl, {
          label: 'Font pairing',
          value: state.fontPairing || '',
          options: (state.fontPairings || []).map((pair) => ({ label: pair.label, value: pair.id })),
          onChange: (value) => setAndPreview((prev) => ({ ...prev, fontPairing: value }))
        }),
        el(TextControl, {
          label: 'baseSize',
          type: 'number',
          step: '0.01',
          min: '0.01',
          value: String((state.baseSettings && state.baseSettings.baseSize) || 16),
          onChange: (v) => updateBase('baseSize', v)
        }),
        el(TextControl, {
          label: 'r',
          type: 'number',
          step: '0.001',
          min: '0.001',
          value: String((state.baseSettings && state.baseSettings.r) || 1.2),
          onChange: (v) => updateBase('r', v)
        }),
        el(TextControl, {
          label: 'incrementFactor',
          type: 'number',
          step: '0.001',
          min: '0.001',
          value: String((state.baseSettings && state.baseSettings.incrementFactor) || 1.01),
          onChange: (v) => updateBase('incrementFactor', v)
        })
      ),
      el(PanelBody, { title: 'Breakpoints', initialOpen: false },
        ...(state.breakpointRows || []).map((row, index) => el('div', { key: `bp-${index}`, style: { marginBottom: '8px', borderBottom: '1px solid #eee', paddingBottom: '8px' } },
          el(TextControl, {
            label: 'Key',
            value: row.key || '',
            onChange: (value) => updateBreakpointRow(index, 'key', value)
          }),
          el(TextControl, {
            label: 'Value',
            value: row.value || '',
            onChange: (value) => updateBreakpointRow(index, 'value', value)
          }),
          el(Button, {
            variant: 'secondary',
            isDestructive: true,
            onClick: () => setAndPreview((prev) => {
              const rows = [...(prev.breakpointRows || [])];
              rows.splice(index, 1);
              return { ...prev, breakpointRows: rows };
            })
          }, 'Remove')
        )),
        el(Button, {
          variant: 'secondary',
          onClick: () => setAndPreview((prev) => ({ ...prev, breakpointRows: [...(prev.breakpointRows || []), { key: '', value: '' }] }))
        }, 'Add breakpoint')
      ),
      el(PanelBody, { title: 'Container Max Widths', initialOpen: false },
        ...(state.containerRows || []).map((row, index) => el('div', { key: `ct-${index}`, style: { marginBottom: '8px', borderBottom: '1px solid #eee', paddingBottom: '8px' } },
          el(TextControl, {
            label: 'Key',
            value: row.key || '',
            onChange: (value) => updateContainerRow(index, 'key', value)
          }),
          el(TextControl, {
            label: 'Value',
            value: row.value || '',
            onChange: (value) => updateContainerRow(index, 'value', value)
          }),
          el(Button, {
            variant: 'secondary',
            isDestructive: true,
            onClick: () => setAndPreview((prev) => {
              const rows = [...(prev.containerRows || [])];
              rows.splice(index, 1);
              return { ...prev, containerRows: rows };
            })
          }, 'Remove')
        )),
        el(Button, {
          variant: 'secondary',
          onClick: () => setAndPreview((prev) => ({ ...prev, containerRows: [...(prev.containerRows || []), { key: '', value: '' }] }))
        }, 'Add container')
      ),
      el('div', { style: { marginTop: '12px' } },
        el(Button, { variant: 'primary', isBusy: saving, onClick: save }, 'Save')
      )
    );
  }

  registerPlugin('lds-tw-theme-tokens-sidebar', {
    render: function () {
      return el(wp.element.Fragment, null,
        el(PluginSidebarMoreMenuItem, { target: 'lds-tw-theme-tokens-sidebar' }, 'LDS Theme Tokens'),
        el(PluginSidebar, { name: 'lds-tw-theme-tokens-sidebar', title: 'LDS Theme Tokens', icon: 'admin-customizer' },
          el(TokenSidebar)
        )
      );
    }
  });
})(window.wp);
