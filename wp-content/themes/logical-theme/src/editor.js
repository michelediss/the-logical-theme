const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck, InnerBlocks } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl, Button, SelectControl } = wp.components;
const { createElement, useEffect } = wp.element;
const { __ } = wp.i18n;
const { select } = wp.data;

const VERSION_4 = '4.0';
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

function generateId(prefix) {
  return `${prefix}_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 8)}`;
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
    ...options,
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
      text: { type: 'string', default: '' },
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
              onChange: (value) => setAttributes({ text: value }),
            })
          )
        ),
        createElement('span', { className: 'text-sm font-semibold uppercase logical-color-eyebrow' }, toStr(attributes.text) || __('Pretitle', 'wp-logical-theme'))
      );
    },
    save: function PretitleSave() {
      return null;
    },
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
      level: { type: 'string', default: 'h2' },
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
              onChange: (value) => setAttributes({ level: value }),
            }),
            createElement(TextControl, {
              label: __('Title', 'wp-logical-theme'),
              value: toStr(attributes.text),
              onChange: (value) => setAttributes({ text: value }),
            })
          )
        ),
        createElement(level, { className: 'text-3xl font-bold logical-color-heading' }, toStr(attributes.text) || __('Title', 'wp-logical-theme'))
      );
    },
    save: function TitleSave() {
      return null;
    },
  });

  registerBlockType(TEXT_BLOCK_NAME, {
    apiVersion: 2,
    title: __('Text', 'wp-logical-theme'),
    icon: 'text',
    category: 'widgets',
    ancestor: [COLUMN_BLOCK_NAME],
    attributes: {
      itemId: { type: 'string', default: '' },
      text: { type: 'string', default: '' },
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
              onChange: (value) => setAttributes({ text: value }),
            })
          )
        ),
        createElement('div', { className: 'logical-layout-text logical-color-body' }, toStr(attributes.text) || __('Text', 'wp-logical-theme'))
      );
    },
    save: function TextSave() {
      return null;
    },
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
      alt: { type: 'string', default: '' },
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
                  alt: media?.alt || media?.title || '',
                }),
                allowedTypes: ['image'],
                value: attributes.id || 0,
                render: ({ open }) => createElement(Button, { variant: 'secondary', onClick: open }, attributes.src ? __('Change image', 'wp-logical-theme') : __('Select image', 'wp-logical-theme')),
              })
            ),
            attributes.src
              ? createElement(TextControl, {
                label: __('Image alt', 'wp-logical-theme'),
                value: toStr(attributes.alt),
                onChange: (value) => setAttributes({ alt: value }),
              })
              : null
          )
        ),
        attributes.src
          ? createElement('img', { src: attributes.src, alt: toStr(attributes.alt), className: 'h-auto w-full rounded-lg object-cover' })
          : createElement('div', { className: 'logical-layout-text logical-color-muted' }, __('No image selected', 'wp-logical-theme'))
      );
    },
    save: function ImageSave() {
      return null;
    },
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
      target: { type: 'string', default: '_self' },
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
              onChange: (value) => setAttributes({ label: value }),
            }),
            createElement(TextControl, {
              label: __('Button URL', 'wp-logical-theme'),
              value: toStr(attributes.url),
              onChange: (value) => setAttributes({ url: value }),
            }),
            createElement(SelectControl, {
              label: __('Variant', 'wp-logical-theme'),
              value: variant,
              options: [
                { value: 'primary', label: __('Primary', 'wp-logical-theme') },
                { value: 'secondary', label: __('Secondary', 'wp-logical-theme') },
                { value: 'outline', label: __('Outline', 'wp-logical-theme') },
                { value: 'link', label: __('Link', 'wp-logical-theme') },
              ],
              onChange: (value) => setAttributes({ variant: value }),
            }),
            createElement(SelectControl, {
              label: __('Target', 'wp-logical-theme'),
              value: target,
              options: [
                { value: '_self', label: __('Same tab', 'wp-logical-theme') },
                { value: '_blank', label: __('New tab', 'wp-logical-theme') },
              ],
              onChange: (value) => setAttributes({ target: value === '_blank' ? '_blank' : '_self' }),
            })
          )
        ),
        createElement('span', { className: 'logical-layout-button inline-flex items-center justify-center rounded-lg border px-5 py-3 text-sm font-semibold' }, toStr(attributes.label) || __('Button', 'wp-logical-theme'))
      );
    },
    save: function ButtonSave() {
      return null;
    },
  });
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
    alignY: { type: 'string', default: 'stretch' },
  },
  supports: {
    html: false,
    reusable: false,
  },
  edit: function ColumnEdit({ attributes, setAttributes }) {
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
        '--logical-col-align': ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch',
      },
    });

    return createElement(
      'div',
      blockProps,
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
            onChange: (value) => setAttributes({ desktop: clampSpan(value) }),
          }),
          createElement(SelectControl, {
            label: __('Tablet span', 'wp-logical-theme'),
            value: String(clampSpan(attributes.tablet)),
            options: Array.from({ length: 12 }).map((_, i) => ({ value: String(i + 1), label: String(i + 1) })),
            onChange: (value) => setAttributes({ tablet: clampSpan(value) }),
          }),
          createElement(SelectControl, {
            label: __('Mobile span', 'wp-logical-theme'),
            value: String(clampSpan(attributes.mobile)),
            options: Array.from({ length: 12 }).map((_, i) => ({ value: String(i + 1), label: String(i + 1) })),
            onChange: (value) => setAttributes({ mobile: clampSpan(value) }),
          }),
          createElement(SelectControl, {
            label: __('Vertical align', 'wp-logical-theme'),
            value: ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch',
            options: [
              { value: 'stretch', label: __('Stretch', 'wp-logical-theme') },
              { value: 'start', label: __('Top', 'wp-logical-theme') },
              { value: 'center', label: __('Center', 'wp-logical-theme') },
              { value: 'end', label: __('Bottom', 'wp-logical-theme') },
            ],
            onChange: (value) => setAttributes({ alignY: value }),
          })
        )
      ),
      createElement(InnerBlocks, {
        allowedBlocks: ['core/embed', ...MINI_LAYOUT_BLOCKS],
        orientation: 'vertical',
        renderAppender: InnerBlocks.ButtonBlockAppender,
      })
    );
  },
  save: function ColumnSave() {
    return createElement(InnerBlocks.Content);
  },
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
    backgroundColor: { type: 'string', default: '' },
  },
  supports: {
    html: false,
    reusable: false,
  },
  edit: function RowEdit({ attributes, setAttributes }) {
    useEffect(() => {
      if (!attributes.rowId) {
        setAttributes({ rowId: generateId('row') });
      }
    }, [attributes.rowId, setAttributes]);

    const gapMap = { none: '0', sm: '0.75rem', md: '1.25rem', lg: '2rem' };
    const container = ['default', 'wide', 'full'].includes(toStr(attributes.container)) ? toStr(attributes.container) : 'default';
    let containerClass = 'container';
    if (container === 'wide') {
      containerClass = 'container logical-layout-container-wide';
    } else if (container === 'full') {
      containerClass = 'container logical-layout-container-full';
    }

    const rowProps = useBlockProps({
      className: 'logical-layout-row',
      style: {
        '--logical-row-gap': gapMap[toStr(attributes.gap)] || gapMap.md,
        '--logical-row-align': ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch',
      },
    });

    const sectionClassName = [
      'logical-layout-section',
      'logical-theme-color-surface',
      toStr(attributes.backgroundColor) ? 'has-surface-color' : '',
      toStr(attributes.backgroundColor) ? `has-${toStr(attributes.backgroundColor)}-background-color` : '',
    ].filter(Boolean).join(' ');

    return createElement(
      'section',
      {
        className: sectionClassName,
        'data-surface-color': toStr(attributes.backgroundColor),
      },
      createElement(
        'div',
        { className: containerClass },
        createElement(
          'div',
          rowProps,
          createElement(
            InspectorControls,
            null,
            createElement(
              PanelBody,
              { title: __('Row Settings', 'wp-logical-theme'), initialOpen: true },
              createElement(SelectControl, {
                label: __('Container', 'wp-logical-theme'),
                value: container,
                options: [
                  { value: 'default', label: __('Default', 'wp-logical-theme') },
                  { value: 'wide', label: __('Wide', 'wp-logical-theme') },
                  { value: 'full', label: __('Full', 'wp-logical-theme') },
                ],
                onChange: (value) => setAttributes({ container: value }),
              }),
              createElement(SelectControl, {
                label: __('Gap', 'wp-logical-theme'),
                value: ['none', 'sm', 'md', 'lg'].includes(toStr(attributes.gap)) ? toStr(attributes.gap) : 'md',
                options: [
                  { value: 'none', label: __('None', 'wp-logical-theme') },
                  { value: 'sm', label: __('Small', 'wp-logical-theme') },
                  { value: 'md', label: __('Medium', 'wp-logical-theme') },
                  { value: 'lg', label: __('Large', 'wp-logical-theme') },
                ],
                onChange: (value) => setAttributes({ gap: value }),
              }),
              createElement(SelectControl, {
                label: __('Vertical align', 'wp-logical-theme'),
                value: ['start', 'center', 'end', 'stretch'].includes(toStr(attributes.alignY)) ? toStr(attributes.alignY) : 'stretch',
                options: [
                  { value: 'stretch', label: __('Stretch', 'wp-logical-theme') },
                  { value: 'start', label: __('Top', 'wp-logical-theme') },
                  { value: 'center', label: __('Center', 'wp-logical-theme') },
                  { value: 'end', label: __('Bottom', 'wp-logical-theme') },
                ],
                onChange: (value) => setAttributes({ alignY: value }),
              }),
              createElement(SelectControl, {
                label: __('Background', 'wp-logical-theme'),
                value: toStr(attributes.backgroundColor),
                options: getThemePaletteOptions(),
                onChange: (value) => setAttributes({ backgroundColor: value }),
              })
            )
          ),
          createElement(InnerBlocks, {
            allowedBlocks: [COLUMN_BLOCK_NAME],
            orientation: 'horizontal',
            template: [[COLUMN_BLOCK_NAME]],
            renderAppender: InnerBlocks.ButtonBlockAppender,
          })
        )
      )
    );
  },
  save: function RowSave() {
    return createElement(InnerBlocks.Content);
  },
});

registerBlockType(LAYOUT_BLOCK_NAME, {
  apiVersion: 2,
  title: __('Layout', 'wp-logical-theme'),
  icon: 'screenoptions',
  category: 'widgets',
  attributes: {
    layoutVersion: { type: 'string', default: VERSION_4 },
  },
  supports: {
    html: false,
    color: {
      text: true,
      background: true,
      link: true,
    },
    typography: {
      fontSize: true,
      lineHeight: true,
      fontFamily: true,
    },
    spacing: {
      margin: true,
      padding: true,
    },
  },
  edit: function LayoutEdit() {
    const blockProps = useBlockProps({ className: 'logical-layout-block' });

    return createElement(
      'div',
      blockProps,
      createElement(InnerBlocks, {
        allowedBlocks: [ROW_BLOCK_NAME],
        template: [[ROW_BLOCK_NAME, {}, [[COLUMN_BLOCK_NAME]]]],
        renderAppender: InnerBlocks.ButtonBlockAppender,
      })
    );
  },
  save: function LayoutSave() {
    return createElement(InnerBlocks.Content);
  },
});
