const { blocks, blockEditor, components, element, i18n, serverSideRender } = window.wp;

const { registerBlockType } = blocks;
const { InspectorControls, useBlockProps } = blockEditor;
const { PanelBody, SelectControl, TextControl } = components;
const { Fragment, createElement } = element;
const { __ } = i18n;
const ServerSideRender = serverSideRender;

const PRESET_VALUES = ['', 'accent', 'foreground', 'muted', 'canvas'];

registerBlockType('custom/social-share', {
  title: __('Social Share', 'the-logical-theme'),
  description: __('Social sharing buttons for the current content.', 'the-logical-theme'),
  category: 'widgets',
  icon: 'share',
  attributes: {
    color: {
      type: 'string',
      default: '',
    },
  },
  edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();
    const color = attributes.color || '';
    const isPresetColor = PRESET_VALUES.includes(color);
    const selectedValue = isPresetColor ? color : '__custom__';

    return createElement(
      Fragment,
      null,
      createElement(
        InspectorControls,
        null,
        createElement(
          PanelBody,
          {
            title: __('Settings', 'the-logical-theme'),
            initialOpen: true,
          },
          createElement(SelectControl, {
            label: __('Color', 'the-logical-theme'),
            value: selectedValue,
            options: [
              { label: __('Default', 'the-logical-theme'), value: '' },
              { label: __('Accent', 'the-logical-theme'), value: 'accent' },
              { label: __('Foreground', 'the-logical-theme'), value: 'foreground' },
              { label: __('Muted', 'the-logical-theme'), value: 'muted' },
              { label: __('Canvas', 'the-logical-theme'), value: 'canvas' },
              { label: __('Custom hex color', 'the-logical-theme'), value: '__custom__' },
            ],
            onChange(value) {
              if (value === '__custom__') {
                setAttributes({ color: !isPresetColor && color ? color : '#b85c38' });
                return;
              }

              setAttributes({ color: value });
            },
          }),
          selectedValue === '__custom__'
            ? createElement(TextControl, {
                label: __('Hex color', 'the-logical-theme'),
                help: __('Use values like #b85c38 or #11223344.', 'the-logical-theme'),
                value: color,
                onChange(value) {
                  setAttributes({ color: value.trim() });
                },
              })
            : null
        )
      ),
      createElement(
        'div',
        blockProps,
        createElement(ServerSideRender, {
          block: 'custom/social-share',
          attributes,
        })
      )
    );
  },
  save() {
    return null;
  },
});
