const { registerBlockType, createBlock } = wp.blocks;
const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck, InnerBlocks } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl, Button, SelectControl } = wp.components;
const { createElement, useEffect } = wp.element;
const { __ } = wp.i18n;
const { subscribe, select, dispatch } = wp.data;

const META_KEY = '_logical_content_json';
const VERSION_3 = '3.0';
const LAYOUT_BLOCK_NAME = 'logical-theme/layout';
const ROW_BLOCK_NAME = 'logical-theme/row';
const COLUMN_BLOCK_NAME = 'logical-theme/column';
const PRETITLE_BLOCK_NAME = 'logical-theme/pretitle';
const TITLE_BLOCK_NAME = 'logical-theme/title';
const TEXT_BLOCK_NAME = 'logical-theme/text';
const IMAGE_BLOCK_NAME = 'logical-theme/image';
const BUTTON_BLOCK_NAME = 'logical-theme/button';

const MINI_LAYOUT_BLOCKS = [
  PRETITLE_BLOCK_NAME,
  TITLE_BLOCK_NAME,
  TEXT_BLOCK_NAME,
  IMAGE_BLOCK_NAME,
  BUTTON_BLOCK_NAME,
];
const hydratedPostIds = new Set();
const fallbackTriedPostIds = new Set();

function generateId(prefix) {
  return `${prefix}_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 8)}`;
}

function getStableBlockId(block, attributeKey, prefix) {
  const attrId = toStr(block?.attributes?.[attributeKey]);
  if (attrId) {
    return attrId;
  }

  const clientId = toStr(block?.clientId);
  if (clientId) {
    return `${prefix}_${clientId}`;
  }

  return generateId(prefix);
}

function toStr(value) {
  return typeof value === 'string' ? value : '';
}

function clampSpan(value) {
  const n = Number.parseInt(value, 10);
  if (Number.isNaN(n)) {
    return 12;
  }
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

    if (parsed.version === VERSION_3 && Array.isArray(parsed.layout)) {
      return parsed;
    }
  } catch (error) {
    return null;
  }

  return null;
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

function registerMiniLayoutBlocks() {
  registerBlockType(PRETITLE_BLOCK_NAME, {
    apiVersion: 2,
    title: __('Pretitle', 'wp-logical-theme'),
    icon: 'editor-kitchensink',
    category: 'widgets',
    ancestor: [COLUMN_BLOCK_NAME],
    attributes: {
      itemId: { type: 'string', default: '' },
      text: { type: 'string', default: '' }
    },
    supports: { html: false, reusable: false, inserter: true },
    edit: function PretitleEdit({ attributes, setAttributes }) {
      useEffect(() => {
        if (!attributes.itemId) {
          setAttributes({ itemId: generateId('item') });
        }
      }, [attributes.itemId, setAttributes]);
      const blockProps = useBlockProps({ className: 'logical-layout-mini-pretitle' });
      return createElement(
        'div',
        blockProps,
        createElement(
          InspectorControls,
          null,
          createElement(
            PanelBody,
            { title: __('Pretitle Settings', 'wp-logical-theme'), initialOpen: true },
            createElement(TextControl, {
              label: __('Pretitle', 'wp-logical-theme'),
              value: toStr(attributes.text),
              onChange: (value) => setAttributes({ text: value })
            })
          )
        ),
        createElement('span', { className: 'text-sm font-semibold uppercase logical-color-eyebrow' }, toStr(attributes.text) || __('Pretitle', 'wp-logical-theme'))
      );
    },
    save: function PretitleSave({ attributes }) {
      return createElement('span', { className: 'logical-layout-pretitle text-sm font-semibold uppercase logical-color-eyebrow' }, toStr(attributes.text));
    }
  });

  registerBlockType(TITLE_BLOCK_NAME, {
    apiVersion: 2,
    title: __('Title', 'wp-logical-theme'),
    icon: 'heading',
    category: 'widgets',
    ancestor: [COLUMN_BLOCK_NAME],
    attributes: {
      itemId: { type: 'string', default: '' },
      text: { type: 'string', default: '' },
      level: { type: 'string', default: 'h2' }
    },
    supports: { html: false, reusable: false, inserter: true },
    edit: function TitleEdit({ attributes, setAttributes }) {
      useEffect(() => {
        if (!attributes.itemId) {
          setAttributes({ itemId: generateId('item') });
        }
      }, [attributes.itemId, setAttributes]);
      const level = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(toStr(attributes.level)) ? toStr(attributes.level) : 'h2';
      const blockProps = useBlockProps({ className: 'logical-layout-mini-title' });
      return createElement(
        'div',
        blockProps,
        createElement(
          InspectorControls,
          null,
          createElement(
            PanelBody,
            { title: __('Title Settings', 'wp-logical-theme'), initialOpen: true },
            createElement(SelectControl, {
              label: __('Level', 'wp-logical-theme'),
              value: level,
              options: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].map((value) => ({ value, label: value.toUpperCase() })),
              onChange: (value) => setAttributes({ level: value })
            }),
            createElement(TextControl, {
              label: __('Title', 'wp-logical-theme'),
              value: toStr(attributes.text),
              onChange: (value) => setAttributes({ text: value })
            })
          )
        ),
        createElement(level, { className: 'text-3xl font-bold logical-color-heading' }, toStr(attributes.text) || __('Title', 'wp-logical-theme'))
      );
    },
    save: function TitleSave({ attributes }) {
      const level = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(toStr(attributes.level)) ? toStr(attributes.level) : 'h2';
      return createElement(level, { className: 'logical-layout-title text-3xl font-bold logical-color-heading' }, toStr(attributes.text));
    }
  });

  registerBlockType(TEXT_BLOCK_NAME, {
    apiVersion: 2,
    title: __('Text', 'wp-logical-theme'),
    icon: 'text',
    category: 'widgets',
    ancestor: [COLUMN_BLOCK_NAME],
    attributes: {
      itemId: { type: 'string', default: '' },
      text: { type: 'string', default: '' }
    },
    supports: { html: false, reusable: false, inserter: true },
    edit: function TextEdit({ attributes, setAttributes }) {
      useEffect(() => {
        if (!attributes.itemId) {
          setAttributes({ itemId: generateId('item') });
        }
      }, [attributes.itemId, setAttributes]);
      const blockProps = useBlockProps({ className: 'logical-layout-mini-text' });
      return createElement(
        'div',
        blockProps,
        createElement(
          InspectorControls,
          null,
          createElement(
            PanelBody,
            { title: __('Text Settings', 'wp-logical-theme'), initialOpen: true },
            createElement(TextareaControl, {
              label: __('Text', 'wp-logical-theme'),
              value: toStr(attributes.text),
              onChange: (value) => setAttributes({ text: value })
            })
          )
        ),
        createElement('div', { className: 'logical-layout-text logical-color-body' }, toStr(attributes.text) || __('Text', 'wp-logical-theme'))
      );
    },
    save: function TextSave({ attributes }) {
      return createElement('div', { className: 'logical-layout-text logical-color-body' }, toStr(attributes.text));
    }
  });

  registerBlockType(IMAGE_BLOCK_NAME, {
    apiVersion: 2,
    title: __('Image', 'wp-logical-theme'),
    icon: 'format-image',
    category: 'widgets',
    ancestor: [COLUMN_BLOCK_NAME],
    attributes: {
      itemId: { type: 'string', default: '' },
      id: { type: 'number', default: 0 },
      src: { type: 'string', default: '' },
      alt: { type: 'string', default: '' }
    },
    supports: { html: false, reusable: false, inserter: true },
    edit: function ImageEdit({ attributes, setAttributes }) {
      useEffect(() => {
        if (!attributes.itemId) {
          setAttributes({ itemId: generateId('item') });
        }
      }, [attributes.itemId, setAttributes]);
      const blockProps = useBlockProps({ className: 'logical-layout-mini-image' });
      return createElement(
        'div',
        blockProps,
        createElement(
          InspectorControls,
          null,
          createElement(
            PanelBody,
            { title: __('Image Settings', 'wp-logical-theme'), initialOpen: true },
            createElement(MediaUploadCheck, null,
              createElement(MediaUpload, {
                onSelect: (media) => setAttributes({
                  id: media?.id || 0,
                  src: media?.url || '',
                  alt: media?.alt || media?.title || ''
                }),
                allowedTypes: ['image'],
                value: attributes.id || 0,
                render: ({ open }) => createElement(Button, { variant: 'secondary', onClick: open }, attributes.src ? __('Change image', 'wp-logical-theme') : __('Select image', 'wp-logical-theme'))
              })
            ),
            attributes.src
              ? createElement(TextControl, {
                label: __('Image alt', 'wp-logical-theme'),
                value: toStr(attributes.alt),
                onChange: (value) => setAttributes({ alt: value })
              })
              : null
          )
        ),
        attributes.src
          ? createElement('img', { src: attributes.src, alt: toStr(attributes.alt), className: 'h-auto w-full rounded-lg object-cover' })
          : createElement('div', { className: 'logical-layout-text logical-color-muted' }, __('No image selected', 'wp-logical-theme'))
      );
    },
    save: function ImageSave({ attributes }) {
      if (!toStr(attributes.src)) {
        return null;
      }
      return createElement('img', { src: toStr(attributes.src), alt: toStr(attributes.alt), className: 'logical-layout-image h-auto w-full rounded-lg object-cover' });
    }
  });

  registerBlockType(BUTTON_BLOCK_NAME, {
    apiVersion: 2,
    title: __('Button', 'wp-logical-theme'),
    icon: 'button',
    category: 'widgets',
    ancestor: [COLUMN_BLOCK_NAME],
    attributes: {
      itemId: { type: 'string', default: '' },
      label: { type: 'string', default: '' },
      url: { type: 'string', default: '' },
      variant: { type: 'string', default: 'primary' },
      target: { type: 'string', default: '_self' }
    },
    supports: { html: false, reusable: false, inserter: true },
    edit: function ButtonEdit({ attributes, setAttributes }) {
      useEffect(() => {
        if (!attributes.itemId) {
          setAttributes({ itemId: generateId('item') });
        }
      }, [attributes.itemId, setAttributes]);
      const variant = ['primary', 'secondary', 'outline', 'link'].includes(toStr(attributes.variant)) ? toStr(attributes.variant) : 'primary';
      const target = toStr(attributes.target) === '_blank' ? '_blank' : '_self';
      const blockProps = useBlockProps({ className: 'logical-layout-mini-button' });

      return createElement(
        'div',
        blockProps,
        createElement(
          InspectorControls,
          null,
          createElement(
            PanelBody,
            { title: __('Button Settings', 'wp-logical-theme'), initialOpen: true },
            createElement(TextControl, {
              label: __('Button label', 'wp-logical-theme'),
              value: toStr(attributes.label),
              onChange: (value) => setAttributes({ label: value })
            }),
            createElement(TextControl, {
              label: __('Button URL', 'wp-logical-theme'),
              value: toStr(attributes.url),
              onChange: (value) => setAttributes({ url: value })
            }),
            createElement(SelectControl, {
              label: __('Variant', 'wp-logical-theme'),
              value: variant,
              options: [
                { value: 'primary', label: __('Primary', 'wp-logical-theme') },
                { value: 'secondary', label: __('Secondary', 'wp-logical-theme') },
                { value: 'outline', label: __('Outline', 'wp-logical-theme') },
                { value: 'link', label: __('Link', 'wp-logical-theme') }
              ],
              onChange: (value) => setAttributes({ variant: value })
            }),
            createElement(SelectControl, {
              label: __('Target', 'wp-logical-theme'),
              value: target,
              options: [
                { value: '_self', label: __('Same tab', 'wp-logical-theme') },
                { value: '_blank', label: __('New tab', 'wp-logical-theme') }
              ],
              onChange: (value) => setAttributes({ target: value === '_blank' ? '_blank' : '_self' })
            })
          )
        ),
        createElement('span', { className: 'logical-layout-button inline-flex items-center justify-center rounded-lg border px-5 py-3 text-sm font-semibold' }, toStr(attributes.label) || __('Button', 'wp-logical-theme'))
      );
    },
    save: function ButtonSave({ attributes }) {
      const label = toStr(attributes.label);
      const url = toStr(attributes.url);
      if (!label || !url) {
        return null;
      }

      const variant = ['primary', 'secondary', 'outline', 'link'].includes(toStr(attributes.variant)) ? toStr(attributes.variant) : 'primary';
      const classMap = {
        primary: 'border-primary bg-primary text-light',
        secondary: 'border-secondary bg-secondary text-light',
        outline: 'border-primary bg-transparent text-primary',
        link: 'border-transparent bg-transparent p-0 text-primary underline underline-offset-4'
      };
      const target = toStr(attributes.target) === '_blank' ? '_blank' : '_self';

      return createElement('a', {
        href: url,
        target: target === '_blank' ? '_blank' : undefined,
        rel: target === '_blank' ? 'noopener noreferrer' : undefined,
        className: `logical-layout-button inline-flex items-center justify-center rounded-lg border px-5 py-3 text-sm font-semibold ${classMap[variant]}`
      }, label);
    }
  });
}

function mapLayoutItemFromBlock(block) {
  if (!block || typeof block !== 'object' || typeof block.name !== 'string') {
    return null;
  }

  if (block.name === 'core/embed') {
    return {
      id: getStableBlockId(block, '', 'embed'),
      type: 'embed',
      data: {
        url: toStr(block.attributes?.url),
        provider: toStr(block.attributes?.providerNameSlug)
      },
      settings: {}
    };
  }

  if (block.name === PRETITLE_BLOCK_NAME) {
    return {
      id: getStableBlockId(block, 'itemId', 'item'),
      type: 'pretitle',
      data: { text: toStr(block.attributes?.text) },
      settings: {}
    };
  }

  if (block.name === TITLE_BLOCK_NAME) {
    const level = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(toStr(block.attributes?.level)) ? toStr(block.attributes.level) : 'h2';
    return {
      id: getStableBlockId(block, 'itemId', 'item'),
      type: 'title',
      data: { text: toStr(block.attributes?.text), level },
      settings: {}
    };
  }

  if (block.name === TEXT_BLOCK_NAME) {
    return {
      id: getStableBlockId(block, 'itemId', 'item'),
      type: 'text',
      data: { text: toStr(block.attributes?.text) },
      settings: {}
    };
  }

  if (block.name === IMAGE_BLOCK_NAME) {
    return {
      id: getStableBlockId(block, 'itemId', 'item'),
      type: 'image',
      data: {
        id: Number.parseInt(block.attributes?.id || 0, 10) || 0,
        src: toStr(block.attributes?.src),
        alt: toStr(block.attributes?.alt)
      },
      settings: {}
    };
  }

  if (block.name === BUTTON_BLOCK_NAME) {
    const variant = ['primary', 'secondary', 'outline', 'link'].includes(toStr(block.attributes?.variant)) ? toStr(block.attributes.variant) : 'primary';
    const target = toStr(block.attributes?.target) === '_blank' ? '_blank' : '_self';
    return {
      id: getStableBlockId(block, 'itemId', 'item'),
      type: 'button',
      data: {
        label: toStr(block.attributes?.label),
        url: toStr(block.attributes?.url),
        variant,
        target,
        rel: target === '_blank' ? 'noopener noreferrer' : ''
      },
      settings: {}
    };
  }

  return null;
}

function mapLayoutFromBlocks(blocks) {
  const layoutBlock = Array.isArray(blocks) ? blocks.find((block) => block?.name === LAYOUT_BLOCK_NAME) : null;
  if (!layoutBlock || !Array.isArray(layoutBlock.innerBlocks)) {
    return [];
  }

  return layoutBlock.innerBlocks
    .filter((row) => row?.name === ROW_BLOCK_NAME)
    .map((row) => {
      const rowAttrs = row.attributes && typeof row.attributes === 'object' ? row.attributes : {};
      return {
        id: getStableBlockId(row, 'rowId', 'row'),
        type: 'row',
        settings: {
          container: ['default', 'wide', 'full'].includes(toStr(rowAttrs.container)) ? toStr(rowAttrs.container) : 'default',
          gap: ['none', 'sm', 'md', 'lg'].includes(toStr(rowAttrs.gap)) ? toStr(rowAttrs.gap) : 'md',
          alignY: ['start', 'center', 'end', 'stretch'].includes(toStr(rowAttrs.alignY)) ? toStr(rowAttrs.alignY) : 'stretch',
          backgroundColor: toStr(rowAttrs.backgroundColor)
        },
        columns: (Array.isArray(row.innerBlocks) ? row.innerBlocks : [])
          .filter((column) => column?.name === COLUMN_BLOCK_NAME)
          .map((column) => {
            const colAttrs = column.attributes && typeof column.attributes === 'object' ? column.attributes : {};
            return {
              id: getStableBlockId(column, 'columnId', 'col'),
              type: 'column',
              settings: {
                desktop: clampSpan(colAttrs.desktop),
                tablet: clampSpan(colAttrs.tablet),
                mobile: clampSpan(colAttrs.mobile),
                alignY: ['start', 'center', 'end', 'stretch'].includes(toStr(colAttrs.alignY)) ? toStr(colAttrs.alignY) : 'stretch'
              },
              items: (Array.isArray(column.innerBlocks) ? column.innerBlocks : [])
                .map((itemBlock) => mapLayoutItemFromBlock(itemBlock))
                .filter(Boolean)
            };
          })
      };
    });
}

function countLayoutItems(layout) {
  const rows = Array.isArray(layout) ? layout : [];
  let total = 0;

  rows.forEach((row) => {
    const columns = Array.isArray(row?.columns) ? row.columns : [];
    columns.forEach((column) => {
      const items = Array.isArray(column?.items) ? column.items : [];
      total += items.length;
    });
  });

  return total;
}

function createItemBlockFromLayoutItem(item) {
  if (!item || typeof item !== 'object') {
    return null;
  }

  const type = toStr(item.type);
  const id = toStr(item.id);
  const data = item.data && typeof item.data === 'object' ? item.data : {};

  if (type === 'pretitle') {
    return createBlock(PRETITLE_BLOCK_NAME, {
      itemId: id,
      text: toStr(data.text)
    });
  }

  if (type === 'title') {
    const level = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(toStr(data.level)) ? toStr(data.level) : 'h2';
    return createBlock(TITLE_BLOCK_NAME, {
      itemId: id,
      text: toStr(data.text),
      level
    });
  }

  if (type === 'text') {
    return createBlock(TEXT_BLOCK_NAME, {
      itemId: id,
      text: toStr(data.text)
    });
  }

  if (type === 'image') {
    return createBlock(IMAGE_BLOCK_NAME, {
      itemId: id,
      id: Number.parseInt(data.id || 0, 10) || 0,
      src: toStr(data.src),
      alt: toStr(data.alt)
    });
  }

  if (type === 'button') {
    return createBlock(BUTTON_BLOCK_NAME, {
      itemId: id,
      label: toStr(data.label),
      url: toStr(data.url),
      variant: ['primary', 'secondary', 'outline', 'link'].includes(toStr(data.variant)) ? toStr(data.variant) : 'primary',
      target: toStr(data.target) === '_blank' ? '_blank' : '_self'
    });
  }

  if (type === 'embed') {
    return createBlock('core/embed', {
      url: toStr(data.url),
      providerNameSlug: toStr(data.provider)
    });
  }

  return null;
}

function createLayoutBlocksFromMetaLayout(layout) {
  const rowBlocks = createRowBlocksFromMetaLayout(layout);
  return [createBlock(LAYOUT_BLOCK_NAME, { layoutVersion: VERSION_3 }, rowBlocks)];
}

function createRowBlocksFromMetaLayout(layout) {
  const rows = Array.isArray(layout) ? layout : [];
  return rows.map((row) => {
    if (!row || typeof row !== 'object') {
      return null;
    }

    const rowSettings = row.settings && typeof row.settings === 'object' ? row.settings : {};
    const rowAttrs = {
      rowId: toStr(row.id),
      container: ['default', 'wide', 'full'].includes(toStr(rowSettings.container)) ? toStr(rowSettings.container) : 'default',
      gap: ['none', 'sm', 'md', 'lg'].includes(toStr(rowSettings.gap)) ? toStr(rowSettings.gap) : 'md',
      alignY: ['start', 'center', 'end', 'stretch'].includes(toStr(rowSettings.alignY)) ? toStr(rowSettings.alignY) : 'stretch',
      backgroundColor: toStr(rowSettings.backgroundColor)
    };

    const columns = Array.isArray(row.columns) ? row.columns : [];
    const columnBlocks = columns.map((column) => {
      if (!column || typeof column !== 'object') {
        return null;
      }

      const columnSettings = column.settings && typeof column.settings === 'object' ? column.settings : {};
      const columnAttrs = {
        columnId: toStr(column.id),
        desktop: clampSpan(columnSettings.desktop),
        tablet: clampSpan(columnSettings.tablet),
        mobile: clampSpan(columnSettings.mobile),
        alignY: ['start', 'center', 'end', 'stretch'].includes(toStr(columnSettings.alignY)) ? toStr(columnSettings.alignY) : 'stretch'
      };

      const itemBlocks = (Array.isArray(column.items) ? column.items : [])
        .map((item) => createItemBlockFromLayoutItem(item))
        .filter(Boolean);

      return createBlock(COLUMN_BLOCK_NAME, columnAttrs, itemBlocks);
    }).filter(Boolean);

    return createBlock(ROW_BLOCK_NAME, rowAttrs, columnBlocks);
  }).filter(Boolean);
}

registerBlockType(COLUMN_BLOCK_NAME, {
  apiVersion: 2,
  title: __('Column', 'wp-logical-theme'),
  icon: 'columns',
  category: 'widgets',
  parent: [ROW_BLOCK_NAME],
  attributes: {
    columnId: { type: 'string', default: '' },
    desktop: { type: 'number', default: 12 },
    tablet: { type: 'number', default: 12 },
    mobile: { type: 'number', default: 12 },
    alignY: { type: 'string', default: 'stretch' }
  },
  supports: {
    html: false,
    reusable: false
  },
  edit: function ColumnEdit({ attributes, setAttributes, clientId }) {
    useEffect(() => {
      if (!attributes.columnId) {
        setAttributes({ columnId: generateId('col') });
      }
    }, [attributes.columnId, setAttributes]);

    const blockProps = useBlockProps({
      className: 'logical-layout-col',
      style: {
        '--logical-col-mobile': clampSpan(attributes.mobile),
        '--logical-col-tablet': clampSpan(attributes.tablet),
        '--logical-col-desktop': clampSpan(attributes.desktop),
        '--logical-col-align': ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch'
      }
    });
    const blockEditorDispatch = dispatch('core/block-editor');
    const addMiniBlock = (name) => {
      if (!blockEditorDispatch || typeof blockEditorDispatch.insertBlocks !== 'function') {
        return;
      }
      blockEditorDispatch.insertBlocks(createBlock(name), undefined, clientId);
    };

    return createElement(
      'div',
      blockProps,
      createElement(
        'div',
        { className: 'logical-layout-mini-toolbar flex gap-2' },
        createElement(Button, { variant: 'secondary', onClick: () => addMiniBlock(PRETITLE_BLOCK_NAME) }, __('Add Pretitle', 'wp-logical-theme')),
        createElement(Button, { variant: 'secondary', onClick: () => addMiniBlock(TITLE_BLOCK_NAME) }, __('Add Title', 'wp-logical-theme')),
        createElement(Button, { variant: 'secondary', onClick: () => addMiniBlock(TEXT_BLOCK_NAME) }, __('Add Text', 'wp-logical-theme')),
        createElement(Button, { variant: 'secondary', onClick: () => addMiniBlock(IMAGE_BLOCK_NAME) }, __('Add Image', 'wp-logical-theme')),
        createElement(Button, { variant: 'secondary', onClick: () => addMiniBlock(BUTTON_BLOCK_NAME) }, __('Add Button', 'wp-logical-theme'))
      ),
      createElement(
        InspectorControls,
        null,
        createElement(
          PanelBody,
          { title: __('Column Settings', 'wp-logical-theme'), initialOpen: true },
          createElement(SelectControl, {
            label: __('Desktop span', 'wp-logical-theme'),
            value: String(clampSpan(attributes.desktop)),
            options: Array.from({ length: 12 }).map((_, i) => ({ value: String(i + 1), label: String(i + 1) })),
            onChange: (value) => setAttributes({ desktop: clampSpan(value) })
          }),
          createElement(SelectControl, {
            label: __('Tablet span', 'wp-logical-theme'),
            value: String(clampSpan(attributes.tablet)),
            options: Array.from({ length: 12 }).map((_, i) => ({ value: String(i + 1), label: String(i + 1) })),
            onChange: (value) => setAttributes({ tablet: clampSpan(value) })
          }),
          createElement(SelectControl, {
            label: __('Mobile span', 'wp-logical-theme'),
            value: String(clampSpan(attributes.mobile)),
            options: Array.from({ length: 12 }).map((_, i) => ({ value: String(i + 1), label: String(i + 1) })),
            onChange: (value) => setAttributes({ mobile: clampSpan(value) })
          }),
          createElement(SelectControl, {
            label: __('Vertical align', 'wp-logical-theme'),
            value: ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch',
            options: [
              { value: 'stretch', label: __('Stretch', 'wp-logical-theme') },
              { value: 'start', label: __('Top', 'wp-logical-theme') },
              { value: 'center', label: __('Center', 'wp-logical-theme') },
              { value: 'end', label: __('Bottom', 'wp-logical-theme') }
            ],
            onChange: (value) => setAttributes({ alignY: value })
          })
        )
      ),
      createElement(InnerBlocks, {
        allowedBlocks: ['core/embed', ...MINI_LAYOUT_BLOCKS],
        orientation: 'vertical',
        renderAppender: InnerBlocks.ButtonBlockAppender
      })
    );
  },
  save: function ColumnSave({ attributes }) {
    const blockProps = useBlockProps.save({
      className: 'logical-layout-col',
      style: {
        '--logical-col-mobile': clampSpan(attributes.mobile),
        '--logical-col-tablet': clampSpan(attributes.tablet),
        '--logical-col-desktop': clampSpan(attributes.desktop),
        '--logical-col-align': ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch'
      }
    });

    return createElement('div', blockProps, createElement(InnerBlocks.Content));
  }
});

registerMiniLayoutBlocks();

registerBlockType(ROW_BLOCK_NAME, {
  apiVersion: 2,
  title: __('Row', 'wp-logical-theme'),
  icon: 'menu-alt3',
  category: 'widgets',
  parent: [LAYOUT_BLOCK_NAME],
  attributes: {
    rowId: { type: 'string', default: '' },
    container: { type: 'string', default: 'default' },
    gap: { type: 'string', default: 'md' },
    alignY: { type: 'string', default: 'stretch' },
    backgroundColor: { type: 'string', default: '' }
  },
  supports: {
    html: false,
    reusable: false
  },
  edit: function RowEdit({ attributes, setAttributes }) {
    useEffect(() => {
      if (!attributes.rowId) {
        setAttributes({ rowId: generateId('row') });
      }
    }, [attributes.rowId, setAttributes]);

    const gapMap = { none: '0', sm: '0.75rem', md: '1.25rem', lg: '2rem' };
    const blockProps = useBlockProps({
      className: 'logical-layout-row logical-theme-color-surface',
      style: {
        '--logical-row-gap': gapMap[toStr(attributes.gap)] || gapMap.md,
        '--logical-row-align': ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch'
      },
      'data-surface-color': toStr(attributes.backgroundColor)
    });

    return createElement(
      'div',
      blockProps,
      createElement(
        InspectorControls,
        null,
        createElement(
          PanelBody,
          { title: __('Row Settings', 'wp-logical-theme'), initialOpen: true },
          createElement(SelectControl, {
            label: __('Container', 'wp-logical-theme'),
            value: ['default', 'wide', 'full'].includes(toStr(attributes.container)) ? toStr(attributes.container) : 'default',
            options: [
              { value: 'default', label: __('Default', 'wp-logical-theme') },
              { value: 'wide', label: __('Wide', 'wp-logical-theme') },
              { value: 'full', label: __('Full', 'wp-logical-theme') }
            ],
            onChange: (value) => setAttributes({ container: value })
          }),
          createElement(SelectControl, {
            label: __('Gap', 'wp-logical-theme'),
            value: ['none', 'sm', 'md', 'lg'].includes(toStr(attributes.gap)) ? toStr(attributes.gap) : 'md',
            options: [
              { value: 'none', label: __('None', 'wp-logical-theme') },
              { value: 'sm', label: __('Small', 'wp-logical-theme') },
              { value: 'md', label: __('Medium', 'wp-logical-theme') },
              { value: 'lg', label: __('Large', 'wp-logical-theme') }
            ],
            onChange: (value) => setAttributes({ gap: value })
          }),
          createElement(SelectControl, {
            label: __('Vertical align', 'wp-logical-theme'),
            value: ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch',
            options: [
              { value: 'stretch', label: __('Stretch', 'wp-logical-theme') },
              { value: 'start', label: __('Top', 'wp-logical-theme') },
              { value: 'center', label: __('Center', 'wp-logical-theme') },
              { value: 'end', label: __('Bottom', 'wp-logical-theme') }
            ],
            onChange: (value) => setAttributes({ alignY: value })
          }),
          createElement(SelectControl, {
            label: __('Background', 'wp-logical-theme'),
            value: toStr(attributes.backgroundColor),
            options: getThemePaletteOptions(),
            onChange: (value) => setAttributes({ backgroundColor: value })
          })
        )
      ),
      createElement(InnerBlocks, {
        allowedBlocks: [COLUMN_BLOCK_NAME],
        orientation: 'horizontal',
        template: [[COLUMN_BLOCK_NAME]],
        renderAppender: InnerBlocks.ButtonBlockAppender
      })
    );
  },
  save: function RowSave({ attributes }) {
    const gapMap = { none: '0', sm: '0.75rem', md: '1.25rem', lg: '2rem' };
    const blockProps = useBlockProps.save({
      className: 'logical-layout-row logical-theme-color-surface',
      style: {
        '--logical-row-gap': gapMap[toStr(attributes.gap)] || gapMap.md,
        '--logical-row-align': ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch'
      },
      'data-surface-color': toStr(attributes.backgroundColor)
    });

    const containerClass = ['default', 'wide', 'full'].includes(toStr(attributes.container)) ? toStr(attributes.container) : 'default';
    const containerWrapper = containerClass === 'full' ? 'logical-layout-container-full' : (containerClass === 'wide' ? 'container logical-layout-container-wide' : 'container');

    return createElement('div', { className: containerWrapper }, createElement('div', blockProps, createElement(InnerBlocks.Content)));
  }
});

registerBlockType(LAYOUT_BLOCK_NAME, {
  apiVersion: 2,
  title: __('Layout', 'wp-logical-theme'),
  icon: 'screenoptions',
  category: 'widgets',
  attributes: {
    layoutVersion: { type: 'string', default: VERSION_3 }
  },
  supports: {
    html: false
  },
  edit: function LayoutEdit({ clientId }) {
    const blockProps = useBlockProps({ className: 'logical-layout-block' });
    const editorSelect = select('core/editor');
    const meta = (editorSelect?.getEditedPostAttribute('meta')) || {};
    const parsedMeta = parseMetaJson(meta[META_KEY]);
    const slug = toStr(editorSelect?.getEditedPostAttribute('slug'));
    const postId = Number.parseInt(editorSelect?.getCurrentPostId?.(), 10) || 0;
    const hasMetaLayout = !!(parsedMeta && parsedMeta.version === VERSION_3 && Array.isArray(parsedMeta.layout) && parsedMeta.layout.length > 0);

    useEffect(() => {
      if (!clientId || postId <= 0) {
        return;
      }
      const postKey = String(postId);
      if (hydratedPostIds.has(postKey)) {
        return;
      }

      const blockEditorSelect = select('core/block-editor');
      const blockEditorDispatch = dispatch('core/block-editor');
      const editorDispatch = dispatch('core/editor');
      const editorStore = select('core/editor');
      if (!blockEditorSelect || !blockEditorDispatch || !editorStore) {
        return;
      }

      const blocks = blockEditorSelect.getBlocks();
      const mappedLayout = mapLayoutFromBlocks(blocks);
      const mappedItemsCount = countLayoutItems(mappedLayout);

      if (parsedMeta && parsedMeta.version === VERSION_3 && Array.isArray(parsedMeta.layout) && countLayoutItems(parsedMeta.layout) > 0) {
        if (mappedItemsCount === 0 && typeof blockEditorDispatch.replaceInnerBlocks === 'function') {
          blockEditorDispatch.replaceInnerBlocks(clientId, createRowBlocksFromMetaLayout(parsedMeta.layout), false);
        }
        hydratedPostIds.add(postKey);
        return;
      }

      if (fallbackTriedPostIds.has(postKey) || !slug) {
        return;
      }

      fallbackTriedPostIds.add(postKey);
      fetch(`/wp-content/themes/logical-theme/assets/json/${slug}.json`, { credentials: 'same-origin' })
        .then((response) => {
          if (!response || !response.ok) {
            return null;
          }
          return response.json();
        })
        .then((json) => {
          if (!json || json.version !== VERSION_3 || !Array.isArray(json.layout) || countLayoutItems(json.layout) === 0) {
            return;
          }

          const latestBlocks = blockEditorSelect.getBlocks();
          const latestLayout = mapLayoutFromBlocks(latestBlocks);
          if (countLayoutItems(latestLayout) === 0 && typeof blockEditorDispatch.replaceInnerBlocks === 'function') {
            blockEditorDispatch.replaceInnerBlocks(clientId, createRowBlocksFromMetaLayout(json.layout), false);
          }

          const latestMeta = (editorStore.getEditedPostAttribute('meta')) || {};
          if (editorDispatch && typeof editorDispatch.editPost === 'function') {
            editorDispatch.editPost({
              meta: {
                ...latestMeta,
                [META_KEY]: JSON.stringify({
                  version: VERSION_3,
                  layout: json.layout
                })
              }
            });
          }
          hydratedPostIds.add(postKey);
        })
        .catch(() => {});
    }, [clientId, postId, slug, meta[META_KEY]]);

    return createElement(
      'section',
      blockProps,
      createElement(InnerBlocks, {
        allowedBlocks: [ROW_BLOCK_NAME],
        template: hasMetaLayout ? undefined : [[ROW_BLOCK_NAME, {}, [[COLUMN_BLOCK_NAME]]]],
        renderAppender: InnerBlocks.ButtonBlockAppender
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
  const meta = editorSelect.getEditedPostAttribute('meta') || {};
  const parsedMeta = parseMetaJson(meta[META_KEY]);
  const mappedLayout = mapLayoutFromBlocks(blocks);
  const mappedItemsCount = countLayoutItems(mappedLayout);
  const metaLayout = parsedMeta && parsedMeta.version === VERSION_3 && Array.isArray(parsedMeta.layout) ? parsedMeta.layout : [];
  const metaItemsCount = countLayoutItems(metaLayout);
  if (metaItemsCount > 0 && mappedItemsCount === 0) {
    // Do not overwrite existing meta with an empty layout before hydration completes.
    return;
  }
  const payload = {
    version: VERSION_3,
    layout: mappedLayout
  };

  const serialized = JSON.stringify(payload);
  if (meta[META_KEY] === serialized) {
    return;
  }

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
