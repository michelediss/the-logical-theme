(function (wp) {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { InspectorControls } = wp.blockEditor;
  const { PanelBody, SelectControl, ToggleControl } = wp.components;
  const { Fragment, createElement: el } = wp.element;
  const ServerSideRender = wp.serverSideRender;

  registerBlockType("simple-cookie-consent/banner", {
    title: __("Cookie Banner", "simple-cookie-consent"),
    icon: "shield",
    category: "widgets",
    edit: function () {
      return el(ServerSideRender, {
        block: "simple-cookie-consent/banner"
      });
    },
    save: function () {
      return null;
    }
  });

  registerBlockType("simple-cookie-consent/cookie-table", {
    title: __("Cookie Table", "simple-cookie-consent"),
    icon: "table-col-after",
    category: "widgets",
    edit: function (props) {
      const attributes = props.attributes;
      const setAttributes = props.setAttributes;

      return el(
        Fragment,
        null,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            {
              title: __("Cookie table settings", "simple-cookie-consent"),
              initialOpen: true
            },
            el(SelectControl, {
              label: __("Layout", "simple-cookie-consent"),
              value: attributes.layout || "table",
              options: [
                { label: __("Table", "simple-cookie-consent"), value: "table" },
                { label: __("Cards", "simple-cookie-consent"), value: "cards" }
              ],
              onChange: function (value) {
                setAttributes({ layout: value });
              }
            }),
            el(SelectControl, {
              label: __("Category filter", "simple-cookie-consent"),
              value: attributes.category || "",
              options: [
                { label: __("All categories", "simple-cookie-consent"), value: "" },
                { label: __("Necessary", "simple-cookie-consent"), value: "functional" },
                { label: __("Preferences", "simple-cookie-consent"), value: "preferences" },
                { label: __("Anonymous analytics", "simple-cookie-consent"), value: "statistics-anonymous" },
                { label: __("Analytics", "simple-cookie-consent"), value: "statistics" },
                { label: __("Marketing", "simple-cookie-consent"), value: "marketing" }
              ],
              onChange: function (value) {
                setAttributes({ category: value });
              }
            }),
            el(ToggleControl, {
              label: __("Show category column", "simple-cookie-consent"),
              checked: attributes.showCategory !== false,
              onChange: function (value) {
                setAttributes({ showCategory: value });
              }
            }),
            el(ToggleControl, {
              label: __("Show duration column", "simple-cookie-consent"),
              checked: attributes.showDuration !== false,
              onChange: function (value) {
                setAttributes({ showDuration: value });
              }
            })
          )
        ),
        el(ServerSideRender, {
          block: "simple-cookie-consent/cookie-table",
          attributes: attributes
        })
      );
    },
    save: function () {
      return null;
    }
  });
})(window.wp);
