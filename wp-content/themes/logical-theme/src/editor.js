import './styles.css';

const { registerBlockType } = wp.blocks;
const { useBlockProps } = wp.blockEditor;
const { createElement } = wp.element;
const { __ } = wp.i18n;

registerBlockType('logical-theme/hello', {
  apiVersion: 2,
  title: __('Logical Hello', 'wp-logical-theme'),
  icon: 'smiley',
  category: 'widgets',
  supports: {
    html: false
  },
  edit: function Edit() {
    const blockProps = useBlockProps({
      className: 'wp-block-logical-theme-hello'
    });

    return createElement('div', blockProps, __('Hello from Logical Theme block.', 'wp-logical-theme'));
  },
  save: function Save() {
    const blockProps = wp.blockEditor.useBlockProps.save({
      className: 'wp-block-logical-theme-hello'
    });

    return createElement('div', blockProps, __('Hello from Logical Theme block.', 'wp-logical-theme'));
  }
});
