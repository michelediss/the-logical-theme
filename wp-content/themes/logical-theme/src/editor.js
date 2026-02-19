import './styles.css';
import paragraphSpec from '../templates/blocks/paragraph/paragraph.json';

const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl, Button, SelectControl } = wp.components;
const { createElement, useEffect } = wp.element;
const { __ } = wp.i18n;
const { subscribe, select, dispatch } = wp.data;
const ServerSideRender = wp.serverSideRender;

const META_KEY = '_logical_content_json';
const VERSION = '2.0';
const BLOCK_NAME = 'logical-theme/paragraph';

function generateSectionId() {
  return `sec_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 8)}`;
}

function parseMetaJson(rawValue) {
  if (!rawValue || typeof rawValue !== 'string') {
    return { version: VERSION, sections: [] };
  }

  try {
    const parsed = JSON.parse(rawValue);
    if (parsed && parsed.version === VERSION && Array.isArray(parsed.sections)) {
      return parsed;
    }
  } catch (error) {
    return { version: VERSION, sections: [] };
  }

  return { version: VERSION, sections: [] };
}

function toStr(value) {
  return typeof value === 'string' ? value : '';
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

registerBlockType(BLOCK_NAME, {
  apiVersion: 2,
  title: __(paragraphSpec?.title || 'Paragrafo', 'wp-logical-theme'),
  icon: 'layout',
  category: 'widgets',
  attributes: {
    sectionId: {
      type: 'string',
      default: ''
    },
    sectionType: {
      type: 'string',
      default: 'paragraph'
    },
    data: {
      type: 'object',
      default: {}
    },
    settings: {
      type: 'object',
      default: {}
    }
  },
  supports: {
    html: false
  },
  edit: function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({ className: 'wp-block-logical-theme-paragraph' });

    useEffect(() => {
      if (!attributes.sectionId) {
        setAttributes({ sectionId: generateSectionId() });
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
      createElement(
        InspectorControls,
        null,
        createElement(
          PanelBody,
          { title: __('Content', 'wp-logical-theme'), initialOpen: true },
          ...controls
        )
      ),
      createElement('strong', null, __(paragraphSpec?.title || 'Paragrafo', 'wp-logical-theme')),
      createElement(ServerSideRender, {
        key: `${BLOCK_NAME}:${attributes.sectionId || 'new'}:${JSON.stringify(attributes.data || {})}:${JSON.stringify(attributes.settings || {})}`,
        block: BLOCK_NAME,
        attributes
      })
    );
  },
  save: function Save() {
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

  const blocks = blockEditorSelect.getBlocks().filter((block) => block.name === BLOCK_NAME);
  const sections = blocks.map((block) => ({
    id: block.attributes.sectionId || `sec_${block.clientId}`,
    type: 'paragraph',
    data: block.attributes.data || {},
    settings: block.attributes.settings || {}
  }));

  const meta = editorSelect.getEditedPostAttribute('meta') || {};
  const parsed = parseMetaJson(meta[META_KEY]);
  const payload = {
    version: VERSION,
    sections
  };

  if (parsed.version === VERSION && JSON.stringify(parsed.sections) === JSON.stringify(sections)) {
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
