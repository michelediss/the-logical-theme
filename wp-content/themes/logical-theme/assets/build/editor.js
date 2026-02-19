const paragraphSpec = {
  name: 'paragraph',
  title: 'Paragrafo',
  defaultVariant: '1',
  variants: [
    { value: '1', label: 'Checkerboard A (testo a sinistra)' },
    { value: '2', label: 'Checkerboard B (testo a destra)' }
  ],
  fields: [
    { key: 'pretitle', label: 'Pretitle', input: 'text', sanitize: 'text', default: '' },
    { key: 'title', label: 'Title', input: 'text', sanitize: 'text', default: '' },
    { key: 'text', label: 'Text', input: 'textarea', sanitize: 'html', default: '' }
  ]
};

const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl, Button, SelectControl } = wp.components;
const { createElement, useEffect, Fragment } = wp.element;
const { __ } = wp.i18n;
const { subscribe, select, dispatch } = wp.data;
const ServerSideRender = wp.serverSideRender;

const META_KEY = '_logical_content_json';
const VERSION_2 = '2.0';
const VERSION_3 = '3.0';
const PARAGRAPH_BLOCK_NAME = 'logical-theme/paragraph';
const LAYOUT_BLOCK_NAME = 'logical-theme/layout';

function generateId(prefix) {
  return `${prefix}_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 7)}`;
}

function toStr(value) {
  return typeof value === 'string' ? value : '';
}

function clampSpan(value) {
  const n = Number.parseInt(value, 10);
  if (Number.isNaN(n)) return 12;
  return Math.max(1, Math.min(12, n));
}

function parseMetaJson(rawValue) {
  if (!rawValue || typeof rawValue !== 'string') {
    return null;
  }

  try {
    const parsed = JSON.parse(rawValue);
    if (!parsed || typeof parsed !== 'object') {
      return null;
    }

    if (parsed.version === VERSION_2 && Array.isArray(parsed.sections)) {
      return parsed;
    }

    if (parsed.version === VERSION_3 && Array.isArray(parsed.layout)) {
      return parsed;
    }
  } catch (error) {
    return null;
  }

  return null;
}

function setData(attributes, setAttributes, data) {
  setAttributes({
    data: { ...(attributes.data || {}), ...data }
  });
}

function setSettings(attributes, setAttributes, settings) {
  setAttributes({
    settings: { ...(attributes.settings || {}), ...settings }
  });
}

function getThemePaletteOptions() {
  const blockEditorStore = select('core/block-editor');
  const settings = blockEditorStore?.getSettings ? blockEditorStore.getSettings() : null;
  const colors = Array.isArray(settings?.colors) ? settings.colors : [];
  const options = colors
    .map((color) => {
      if (!color || typeof color !== 'object') {
        return null;
      }

      const slug = toStr(color.slug);
      if (!slug) {
        return null;
      }

      const label = toStr(color.name) || slug;
      return { value: slug, label };
    })
    .filter(Boolean);

  return [
    { value: '', label: __('Default', 'wp-logical-theme') },
    ...options
  ];
}

function getParagraphControls(attributes, setAttributes) {
  const data = attributes.data || {};
  const settings = attributes.settings || {};
  const fields = Array.isArray(paragraphSpec?.fields) ? paragraphSpec.fields : [];
  const variants = Array.isArray(paragraphSpec?.variants) ? paragraphSpec.variants : [];
  const defaultVariant = typeof paragraphSpec?.defaultVariant === 'string' && paragraphSpec.defaultVariant !== ''
    ? paragraphSpec.defaultVariant
    : '1';
  const controls = [];

  const variantOptions = variants
    .map((variant) => {
      if (variant && typeof variant === 'object') {
        const value = typeof variant.value === 'string' ? variant.value : '';
        const label = typeof variant.label === 'string' && variant.label !== '' ? variant.label : value;
        return value ? { value, label } : null;
      }
      if (typeof variant === 'string' && variant !== '') {
        return { value: variant, label: variant };
      }
      return null;
    })
    .filter(Boolean);

  if (variantOptions.length > 0) {
    controls.push(createElement(SelectControl, {
      key: 'paragraph-variant',
      label: __('Variant', 'wp-logical-theme'),
      value: toStr(settings.variant || defaultVariant),
      options: variantOptions,
      onChange: (value) => setSettings(attributes, setAttributes, { variant: value })
    }));
  }

  controls.push(createElement(SelectControl, {
    key: 'paragraph-surface-color',
    label: __('Section background', 'wp-logical-theme'),
    value: toStr(settings.backgroundColor),
    options: getThemePaletteOptions(),
    onChange: (value) => setSettings(attributes, setAttributes, { backgroundColor: value })
  }));

  fields.forEach((field) => {
    if (!field || typeof field !== 'object' || typeof field.key !== 'string') {
      return;
    }

    const fieldKey = field.key;
    const fieldLabel = typeof field.label === 'string' && field.label !== '' ? field.label : fieldKey;
    const inputType = field.input === 'textarea' ? 'textarea' : 'text';
    const currentValue = toStr(data[fieldKey] ?? field.default ?? '');

    if (inputType === 'textarea') {
      controls.push(createElement(TextareaControl, {
        key: fieldKey,
        label: __(fieldLabel, 'wp-logical-theme'),
        value: currentValue,
        onChange: (value) => setData(attributes, setAttributes, { [fieldKey]: value })
      }));
      return;
    }

    controls.push(createElement(TextControl, {
      key: fieldKey,
      label: __(fieldLabel, 'wp-logical-theme'),
      value: currentValue,
      onChange: (value) => setData(attributes, setAttributes, { [fieldKey]: value })
    }));
  });

  const image = data.image && typeof data.image === 'object' ? data.image : {};
  controls.push(createElement(MediaUploadCheck, { key: 'paragraph-image-check' },
    createElement(MediaUpload, {
      onSelect: (media) => {
        const nextImage = {
          id: media?.id || 0,
          src: media?.url || '',
          alt: media?.alt || media?.title || ''
        };
        setData(attributes, setAttributes, { image: nextImage });
      },
      allowedTypes: ['image'],
      value: image.id || 0,
      render: ({ open }) => createElement(
        Button,
        { variant: 'secondary', onClick: open },
        image?.src ? __('Change image', 'wp-logical-theme') : __('Select image', 'wp-logical-theme')
      )
    })
  ));

  if (image?.src) {
    controls.push(createElement(TextControl, {
      key: 'paragraph-image-alt',
      label: __('Image Alt', 'wp-logical-theme'),
      value: toStr(image.alt),
      onChange: (value) => setData(attributes, setAttributes, { image: { ...image, alt: value } })
    }));

    controls.push(createElement(Button, {
      key: 'paragraph-image-remove',
      variant: 'secondary',
      isDestructive: true,
      onClick: () => setData(attributes, setAttributes, { image: { id: 0, src: '', alt: '' } })
    }, __('Remove image', 'wp-logical-theme')));
  }

  return controls;
}

function normalizeLayout(layout) {
  const rows = Array.isArray(layout) ? layout : [];
  return rows.map((row) => {
    const rowSettings = row?.settings && typeof row.settings === 'object' ? row.settings : {};
    const columns = Array.isArray(row?.columns) ? row.columns : [];

    return {
      id: toStr(row?.id) || generateId('row'),
      type: 'row',
      settings: {
        container: ['default', 'wide', 'full'].includes(rowSettings.container) ? rowSettings.container : 'default',
        gap: ['none', 'sm', 'md', 'lg'].includes(rowSettings.gap) ? rowSettings.gap : 'md',
        alignY: ['start', 'center', 'end', 'stretch'].includes(rowSettings.alignY) ? rowSettings.alignY : 'stretch',
        backgroundColor: toStr(rowSettings.backgroundColor)
      },
      columns: columns.map((column) => {
        const colSettings = column?.settings && typeof column.settings === 'object' ? column.settings : {};
        const items = Array.isArray(column?.items) ? column.items : [];

        return {
          id: toStr(column?.id) || generateId('col'),
          type: 'column',
          settings: {
            desktop: clampSpan(colSettings.desktop),
            tablet: clampSpan(colSettings.tablet),
            mobile: clampSpan(colSettings.mobile),
            alignY: ['start', 'center', 'end', 'stretch'].includes(colSettings.alignY) ? colSettings.alignY : 'stretch'
          },
          items: items.map((item) => {
            const type = ['paragraph', 'embed'].includes(toStr(item?.type)) ? toStr(item.type) : 'paragraph';
            if (type === 'embed') {
              return {
                id: toStr(item?.id) || generateId('item'),
                type: 'embed',
                data: {
                  url: toStr(item?.data?.url),
                  provider: toStr(item?.data?.provider)
                },
                settings: {}
              };
            }

            return {
              id: toStr(item?.id) || generateId('item'),
              type: 'paragraph',
              data: {
                pretitle: toStr(item?.data?.pretitle),
                title: toStr(item?.data?.title),
                text: toStr(item?.data?.text),
                image: item?.data?.image && typeof item.data.image === 'object' ? item.data.image : { id: 0, src: '', alt: '' }
              },
              settings: {
                variant: toStr(item?.settings?.variant) || '1',
                backgroundColor: toStr(item?.settings?.backgroundColor)
              }
            };
          })
        };
      })
    };
  });
}

function ensureDefaultLayout(layout) {
  const normalized = normalizeLayout(layout);
  if (normalized.length > 0) {
    return normalized;
  }

  return [{
    id: generateId('row'),
    type: 'row',
    settings: {
      container: 'default',
      gap: 'md',
      alignY: 'stretch',
      backgroundColor: ''
    },
    columns: [{
      id: generateId('col'),
      type: 'column',
      settings: {
        desktop: 12,
        tablet: 12,
        mobile: 12,
        alignY: 'stretch'
      },
      items: []
    }]
  }];
}

function withLayoutUpdate(layout, setAttributes, mutator) {
  const nextLayout = normalizeLayout(layout);
  mutator(nextLayout);
  setAttributes({ layout: normalizeLayout(nextLayout) });
}

function createDefaultItem(type) {
  if (type === 'embed') {
    return {
      id: generateId('item'),
      type: 'embed',
      data: { url: '', provider: '' },
      settings: {}
    };
  }

  return {
    id: generateId('item'),
    type: 'paragraph',
    data: { pretitle: '', title: '', text: '', image: { id: 0, src: '', alt: '' } },
    settings: { variant: '1', backgroundColor: '' }
  };
}

function getLayoutControls(layout, setAttributes) {
  const controls = [];
  const paletteOptions = getThemePaletteOptions();

  controls.push(createElement(Button, {
    key: 'layout-add-row',
    variant: 'primary',
    onClick: () => withLayoutUpdate(layout, setAttributes, (rows) => {
      rows.push(ensureDefaultLayout([])[0]);
    })
  }, __('Add row', 'wp-logical-theme')));

  layout.forEach((row, rowIndex) => {
    const rowControls = [];
    rowControls.push(createElement(SelectControl, {
      key: `row-${row.id}-container`,
      label: __('Container', 'wp-logical-theme'),
      value: row.settings.container,
      options: [
        { value: 'default', label: __('Default', 'wp-logical-theme') },
        { value: 'wide', label: __('Wide', 'wp-logical-theme') },
        { value: 'full', label: __('Full', 'wp-logical-theme') }
      ],
      onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
        rows[rowIndex].settings.container = value;
      })
    }));

    rowControls.push(createElement(SelectControl, {
      key: `row-${row.id}-gap`,
      label: __('Gap', 'wp-logical-theme'),
      value: row.settings.gap,
      options: [
        { value: 'none', label: __('None', 'wp-logical-theme') },
        { value: 'sm', label: __('Small', 'wp-logical-theme') },
        { value: 'md', label: __('Medium', 'wp-logical-theme') },
        { value: 'lg', label: __('Large', 'wp-logical-theme') }
      ],
      onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
        rows[rowIndex].settings.gap = value;
      })
    }));

    rowControls.push(createElement(SelectControl, {
      key: `row-${row.id}-align`,
      label: __('Vertical align', 'wp-logical-theme'),
      value: row.settings.alignY,
      options: [
        { value: 'stretch', label: __('Stretch', 'wp-logical-theme') },
        { value: 'start', label: __('Top', 'wp-logical-theme') },
        { value: 'center', label: __('Center', 'wp-logical-theme') },
        { value: 'end', label: __('Bottom', 'wp-logical-theme') }
      ],
      onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
        rows[rowIndex].settings.alignY = value;
      })
    }));

    rowControls.push(createElement(SelectControl, {
      key: `row-${row.id}-bg`,
      label: __('Background', 'wp-logical-theme'),
      value: row.settings.backgroundColor,
      options: paletteOptions,
      onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
        rows[rowIndex].settings.backgroundColor = value;
      })
    }));

    rowControls.push(createElement(Button, {
      key: `row-${row.id}-add-column`,
      variant: 'secondary',
      onClick: () => withLayoutUpdate(layout, setAttributes, (rows) => {
        if (rows[rowIndex].columns.length >= 6) return;
        rows[rowIndex].columns.push({
          id: generateId('col'),
          type: 'column',
          settings: { desktop: 12, tablet: 12, mobile: 12, alignY: 'stretch' },
          items: []
        });
      })
    }, __('Add column', 'wp-logical-theme')));

    if (layout.length > 1) {
      rowControls.push(createElement(Button, {
        key: `row-${row.id}-remove`,
        variant: 'secondary',
        isDestructive: true,
        onClick: () => withLayoutUpdate(layout, setAttributes, (rows) => {
          rows.splice(rowIndex, 1);
        })
      }, __('Remove row', 'wp-logical-theme')));
    }

    row.columns.forEach((column, columnIndex) => {
      rowControls.push(createElement('hr', { key: `row-${row.id}-col-${column.id}-separator` }));
      rowControls.push(createElement('strong', { key: `row-${row.id}-col-${column.id}-title` }, `${__('Column', 'wp-logical-theme')} ${columnIndex + 1}`));

      rowControls.push(createElement(SelectControl, {
        key: `row-${row.id}-col-${column.id}-desktop`,
        label: __('Desktop span', 'wp-logical-theme'),
        value: String(column.settings.desktop),
        options: Array.from({ length: 12 }).map((_, i) => ({ value: String(i + 1), label: String(i + 1) })),
        onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
          rows[rowIndex].columns[columnIndex].settings.desktop = clampSpan(value);
        })
      }));

      rowControls.push(createElement(SelectControl, {
        key: `row-${row.id}-col-${column.id}-tablet`,
        label: __('Tablet span', 'wp-logical-theme'),
        value: String(column.settings.tablet),
        options: Array.from({ length: 12 }).map((_, i) => ({ value: String(i + 1), label: String(i + 1) })),
        onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
          rows[rowIndex].columns[columnIndex].settings.tablet = clampSpan(value);
        })
      }));

      rowControls.push(createElement(SelectControl, {
        key: `row-${row.id}-col-${column.id}-mobile`,
        label: __('Mobile span', 'wp-logical-theme'),
        value: String(column.settings.mobile),
        options: Array.from({ length: 12 }).map((_, i) => ({ value: String(i + 1), label: String(i + 1) })),
        onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
          rows[rowIndex].columns[columnIndex].settings.mobile = clampSpan(value);
        })
      }));

      rowControls.push(createElement(Button, {
        key: `row-${row.id}-col-${column.id}-add-paragraph`,
        variant: 'secondary',
        onClick: () => withLayoutUpdate(layout, setAttributes, (rows) => {
          rows[rowIndex].columns[columnIndex].items.push(createDefaultItem('paragraph'));
        })
      }, __('Add paragraph item', 'wp-logical-theme')));

      rowControls.push(createElement(Button, {
        key: `row-${row.id}-col-${column.id}-add-embed`,
        variant: 'secondary',
        onClick: () => withLayoutUpdate(layout, setAttributes, (rows) => {
          rows[rowIndex].columns[columnIndex].items.push(createDefaultItem('embed'));
        })
      }, __('Add embed item', 'wp-logical-theme')));

      if (row.columns.length > 1) {
        rowControls.push(createElement(Button, {
          key: `row-${row.id}-col-${column.id}-remove`,
          variant: 'secondary',
          isDestructive: true,
          onClick: () => withLayoutUpdate(layout, setAttributes, (rows) => {
            rows[rowIndex].columns.splice(columnIndex, 1);
          })
        }, __('Remove column', 'wp-logical-theme')));
      }

      column.items.forEach((item, itemIndex) => {
        rowControls.push(createElement('div', { key: `row-${row.id}-col-${column.id}-item-${item.id}-title` }, `${__('Item', 'wp-logical-theme')} ${itemIndex + 1} (${item.type})`));

        rowControls.push(createElement(SelectControl, {
          key: `row-${row.id}-col-${column.id}-item-${item.id}-type`,
          label: __('Item type', 'wp-logical-theme'),
          value: item.type,
          options: [
            { value: 'paragraph', label: __('Paragraph', 'wp-logical-theme') },
            { value: 'embed', label: __('Embed', 'wp-logical-theme') }
          ],
          onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
            rows[rowIndex].columns[columnIndex].items[itemIndex] = createDefaultItem(value);
          })
        }));

        if (item.type === 'paragraph') {
          rowControls.push(createElement(TextControl, {
            key: `row-${row.id}-col-${column.id}-item-${item.id}-title`,
            label: __('Title', 'wp-logical-theme'),
            value: toStr(item.data.title),
            onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
              rows[rowIndex].columns[columnIndex].items[itemIndex].data.title = value;
            })
          }));

          rowControls.push(createElement(TextareaControl, {
            key: `row-${row.id}-col-${column.id}-item-${item.id}-text`,
            label: __('Text', 'wp-logical-theme'),
            value: toStr(item.data.text),
            onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
              rows[rowIndex].columns[columnIndex].items[itemIndex].data.text = value;
            })
          }));
        }

        if (item.type === 'embed') {
          rowControls.push(createElement(TextControl, {
            key: `row-${row.id}-col-${column.id}-item-${item.id}-url`,
            label: __('Embed URL', 'wp-logical-theme'),
            value: toStr(item.data.url),
            onChange: (value) => withLayoutUpdate(layout, setAttributes, (rows) => {
              rows[rowIndex].columns[columnIndex].items[itemIndex].data.url = value;
            })
          }));
        }

        rowControls.push(createElement(Button, {
          key: `row-${row.id}-col-${column.id}-item-${item.id}-remove`,
          variant: 'secondary',
          isDestructive: true,
          onClick: () => withLayoutUpdate(layout, setAttributes, (rows) => {
            rows[rowIndex].columns[columnIndex].items.splice(itemIndex, 1);
          })
        }, __('Remove item', 'wp-logical-theme')));
      });
    });

    controls.push(createElement(PanelBody, {
      key: `row-${row.id}`,
      title: `${__('Row', 'wp-logical-theme')} ${rowIndex + 1}`,
      initialOpen: false
    }, ...rowControls));
  });

  return controls;
}

registerBlockType(PARAGRAPH_BLOCK_NAME, {
  apiVersion: 2,
  title: __(paragraphSpec?.title || 'Paragrafo', 'wp-logical-theme'),
  icon: 'layout',
  category: 'widgets',
  attributes: {
    sectionId: { type: 'string', default: '' },
    sectionType: { type: 'string', default: 'paragraph' },
    data: { type: 'object', default: {} },
    settings: { type: 'object', default: {} }
  },
  supports: { html: false },
  edit: function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({ className: 'wp-block-logical-theme-paragraph' });

    useEffect(() => {
      if (!attributes.sectionId) {
        setAttributes({ sectionId: generateId('sec') });
      }
    }, [attributes.sectionId, setAttributes]);

    useEffect(() => {
      if (attributes.sectionType !== 'paragraph') {
        setAttributes({ sectionType: 'paragraph' });
      }
    }, [attributes.sectionType, setAttributes]);

    const controls = getParagraphControls(attributes, setAttributes);

    return createElement(
      'div',
      blockProps,
      createElement(InspectorControls, null, createElement(PanelBody, { title: __('Content', 'wp-logical-theme'), initialOpen: true }, ...controls)),
      createElement('strong', null, __(paragraphSpec?.title || 'Paragrafo', 'wp-logical-theme')),
      createElement(ServerSideRender, {
        key: `${PARAGRAPH_BLOCK_NAME}:${attributes.sectionId || 'new'}:${JSON.stringify(attributes.data || {})}:${JSON.stringify(attributes.settings || {})}`,
        block: PARAGRAPH_BLOCK_NAME,
        attributes
      })
    );
  },
  save: function Save() {
    return null;
  }
});

registerBlockType(LAYOUT_BLOCK_NAME, {
  apiVersion: 2,
  title: __('Layout', 'wp-logical-theme'),
  icon: 'screenoptions',
  category: 'widgets',
  attributes: {
    layout: {
      type: 'array',
      default: []
    }
  },
  supports: {
    html: false
  },
  edit: function LayoutEdit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({ className: 'wp-block-logical-theme-layout' });

    useEffect(() => {
      if (!Array.isArray(attributes.layout) || attributes.layout.length === 0) {
        setAttributes({ layout: ensureDefaultLayout([]) });
      }
    }, [attributes.layout, setAttributes]);

    const layout = ensureDefaultLayout(attributes.layout || []);
    const controls = getLayoutControls(layout, setAttributes);

    return createElement(
      'div',
      blockProps,
      createElement(InspectorControls, null,
        createElement(PanelBody, {
          title: __('Layout Builder', 'wp-logical-theme'),
          initialOpen: true
        }, ...controls)
      ),
      createElement('strong', null, __('Layout', 'wp-logical-theme')),
      createElement(ServerSideRender, {
        key: `${LAYOUT_BLOCK_NAME}:${JSON.stringify(layout)}`,
        block: LAYOUT_BLOCK_NAME,
        attributes: { layout }
      })
    );
  },
  save: function LayoutSave() {
    return null;
  }
});

let lastSerialized = null;
subscribe(() => {
  const editorSelect = select('core/editor');
  if (!editorSelect) return;

  const postType = editorSelect.getCurrentPostType();
  if (postType !== 'page') return;

  const blockEditorSelect = select('core/block-editor');
  if (!blockEditorSelect) return;

  const blocks = blockEditorSelect.getBlocks();
  const layoutBlocks = blocks.filter((block) => block.name === LAYOUT_BLOCK_NAME);

  let payload;
  if (layoutBlocks.length > 0) {
    payload = {
      version: VERSION_3,
      layout: normalizeLayout(layoutBlocks[0].attributes.layout || [])
    };
  } else {
    const paragraphBlocks = blocks.filter((block) => block.name === PARAGRAPH_BLOCK_NAME);
    payload = {
      version: VERSION_2,
      sections: paragraphBlocks.map((block) => ({
        id: block.attributes.sectionId || generateId('sec'),
        type: 'paragraph',
        data: block.attributes.data || {},
        settings: block.attributes.settings || {}
      }))
    };
  }

  const meta = editorSelect.getEditedPostAttribute('meta') || {};
  const parsed = parseMetaJson(meta[META_KEY]);
  if (parsed && JSON.stringify(parsed) === JSON.stringify(payload)) {
    return;
  }

  const serialized = JSON.stringify(payload);
  if (serialized === lastSerialized) return;

  const editorDispatch = dispatch('core/editor');
  if (!editorDispatch) return;

  editorDispatch.editPost({
    meta: {
      ...meta,
      [META_KEY]: serialized
    }
  });

  lastSerialized = serialized;
});
