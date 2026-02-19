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

  apiFetch.use(apiFetch.createNonceMiddleware(window.ldsTwTokensEditor.nonce));

  const endpoint = '/lds-tw/v1';

  function normalizeColor(hex) {
    const raw = String(hex || '').trim().replace(/^#/, '').toLowerCase();
    if (/^[0-9a-f]{3}$/.test(raw)) return '#' + raw.split('').map((c) => c + c).join('');
    if (/^[0-9a-f]{6}$/.test(raw)) return '#' + raw;
    return '#000000';
  }

  function applyPreview(palette) {
    const roots = [document.documentElement];
    const iframe = document.querySelector('iframe[name="editor-canvas"], iframe.edit-post-visual-editor__iframe');
    if (iframe && iframe.contentDocument && iframe.contentDocument.documentElement) {
      roots.push(iframe.contentDocument.documentElement);
    }

    roots.forEach((root) => {
      (palette || []).forEach((entry) => {
        const slug = String(entry.slug || '').trim();
        const color = normalizeColor(entry.color);
        if (!slug) return;
        const rgb = color.replace('#', '').match(/.{2}/g).map((c) => parseInt(c, 16)).join(' ');
        root.style.setProperty(`--color-${slug}`, color);
        root.style.setProperty(`--color-${slug}-rgb`, rgb);
      });
    });
  }

  function TokenSidebar() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [state, setState] = useState(null);
    const [error, setError] = useState('');

    const fetchState = async () => {
      setError('');
      try {
        const response = await apiFetch({ path: endpoint + '/tokens' });
        const next = response.state || null;
        setState(next);
        applyPreview((next && next.palette) || []);
      } catch (err) {
        setError(err.message || 'Request failed');
      } finally {
        setLoading(false);
      }
    };

    useEffect(() => {
      fetchState();
    }, []);

    const updatePalette = (index, key, value) => {
      setState((prev) => {
        if (!prev) return prev;
        const palette = [...(prev.palette || [])];
        palette[index] = { ...palette[index], [key]: value };
        applyPreview(palette);
        return { ...prev, palette };
      });
    };

    const updateBase = (key, value) => {
      setState((prev) => prev ? { ...prev, baseSettings: { ...(prev.baseSettings || {}), [key]: Number(value) } } : prev);
    };

    const save = async () => {
      if (!state) return;
      setSaving(true);
      setError('');
      try {
        const payload = {
          palette: state.palette || [],
          breakpoints: state.breakpoints || {},
          containerMaxWidths: state.containerMaxWidths || {},
          baseSettings: state.baseSettings || {},
          fontPairing: state.fontPairing || ''
        };
        const response = await apiFetch({ path: endpoint + '/tokens', method: 'POST', data: payload });
        const next = response.state || state;
        setState(next);
        applyPreview((next && next.palette) || []);
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
          onClick: () => setState((prev) => ({ ...prev, palette: [...(prev.palette || []), { slug: '', color: '#000000' }] }))
        }, 'Add color')
      ),
      el(PanelBody, { title: 'Typography', initialOpen: false },
        el(SelectControl, {
          label: 'Font pairing',
          value: state.fontPairing || '',
          options: (state.fontPairings || []).map((pair) => ({ label: pair.label, value: pair.id })),
          onChange: (value) => setState((prev) => ({ ...prev, fontPairing: value }))
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
