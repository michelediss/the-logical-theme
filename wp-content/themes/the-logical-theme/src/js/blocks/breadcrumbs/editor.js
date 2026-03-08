const { blocks, blockEditor, components, element, i18n, serverSideRender } = window.wp;

const { registerBlockType } = blocks;
const { InspectorControls, useBlockProps } = blockEditor;
const { PanelBody, TextControl, ToggleControl } = components;
const { Fragment, createElement } = element;
const { __ } = i18n;
const ServerSideRender = serverSideRender;

registerBlockType('custom/breadcrumbs', {
  title: __('Breadcrumbs', 'the-logical-theme'),
  description: __('Contextual navigation trail for the current view.', 'the-logical-theme'),
  category: 'widgets',
  icon: 'ellipsis',
  attributes: {
    showCurrent: {
      type: 'boolean',
      default: true,
    },
    separator: {
      type: 'string',
      default: '/',
    },
  },
  edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();

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
          createElement(ToggleControl, {
            label: __('Show current page', 'the-logical-theme'),
            checked: !!attributes.showCurrent,
            onChange(value) {
              setAttributes({ showCurrent: value });
            },
          }),
          createElement(TextControl, {
            label: __('Separator', 'the-logical-theme'),
            value: attributes.separator || '/',
            onChange(value) {
              setAttributes({ separator: value || '/' });
            },
          })
        )
      ),
      createElement(
        'div',
        blockProps,
        createElement(ServerSideRender, {
          block: 'custom/breadcrumbs',
          attributes,
        })
      )
    );
  },
  save() {
    return null;
  },
});
