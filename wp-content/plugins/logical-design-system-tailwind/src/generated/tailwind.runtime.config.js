export default {
  "content": [
    "../../themes/logical-theme/*.php",
    "../../themes/logical-theme/templates/**/*.php",
    "../../themes/logical-theme/template-parts/**/*.php",
    "../../themes/logical-theme/src/**/*.{js,jsx}"
  ],
  "safelist": [
    {
      "pattern": new RegExp("^(bg|text|decoration|border|outline|shadow|inset-shadow|ring|inset-ring|accent|caret|fill|stroke)-(black|white|light|primary|secondary)$"),
      "variants": [
        "sm",
        "md",
        "lg",
        "xl",
        "2xl",
        "3xl",
        "4xl",
        "5xl"
      ]
    },
    {
      "pattern": new RegExp("^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)$"),
      "variants": [
        "sm",
        "md",
        "lg",
        "xl",
        "2xl",
        "3xl",
        "4xl",
        "5xl"
      ]
    },
    "inset-shadow-black",
    "inset-ring-black",
    "sm:inset-shadow-black",
    "sm:inset-ring-black",
    "md:inset-shadow-black",
    "md:inset-ring-black",
    "lg:inset-shadow-black",
    "lg:inset-ring-black",
    "xl:inset-shadow-black",
    "xl:inset-ring-black",
    "2xl:inset-shadow-black",
    "2xl:inset-ring-black",
    "3xl:inset-shadow-black",
    "3xl:inset-ring-black",
    "4xl:inset-shadow-black",
    "4xl:inset-ring-black",
    "5xl:inset-shadow-black",
    "5xl:inset-ring-black",
    "inset-shadow-white",
    "inset-ring-white",
    "sm:inset-shadow-white",
    "sm:inset-ring-white",
    "md:inset-shadow-white",
    "md:inset-ring-white",
    "lg:inset-shadow-white",
    "lg:inset-ring-white",
    "xl:inset-shadow-white",
    "xl:inset-ring-white",
    "2xl:inset-shadow-white",
    "2xl:inset-ring-white",
    "3xl:inset-shadow-white",
    "3xl:inset-ring-white",
    "4xl:inset-shadow-white",
    "4xl:inset-ring-white",
    "5xl:inset-shadow-white",
    "5xl:inset-ring-white",
    "inset-shadow-light",
    "inset-ring-light",
    "sm:inset-shadow-light",
    "sm:inset-ring-light",
    "md:inset-shadow-light",
    "md:inset-ring-light",
    "lg:inset-shadow-light",
    "lg:inset-ring-light",
    "xl:inset-shadow-light",
    "xl:inset-ring-light",
    "2xl:inset-shadow-light",
    "2xl:inset-ring-light",
    "3xl:inset-shadow-light",
    "3xl:inset-ring-light",
    "4xl:inset-shadow-light",
    "4xl:inset-ring-light",
    "5xl:inset-shadow-light",
    "5xl:inset-ring-light",
    "inset-shadow-primary",
    "inset-ring-primary",
    "sm:inset-shadow-primary",
    "sm:inset-ring-primary",
    "md:inset-shadow-primary",
    "md:inset-ring-primary",
    "lg:inset-shadow-primary",
    "lg:inset-ring-primary",
    "xl:inset-shadow-primary",
    "xl:inset-ring-primary",
    "2xl:inset-shadow-primary",
    "2xl:inset-ring-primary",
    "3xl:inset-shadow-primary",
    "3xl:inset-ring-primary",
    "4xl:inset-shadow-primary",
    "4xl:inset-ring-primary",
    "5xl:inset-shadow-primary",
    "5xl:inset-ring-primary",
    "inset-shadow-secondary",
    "inset-ring-secondary",
    "sm:inset-shadow-secondary",
    "sm:inset-ring-secondary",
    "md:inset-shadow-secondary",
    "md:inset-ring-secondary",
    "lg:inset-shadow-secondary",
    "lg:inset-ring-secondary",
    "xl:inset-shadow-secondary",
    "xl:inset-ring-secondary",
    "2xl:inset-shadow-secondary",
    "2xl:inset-ring-secondary",
    "3xl:inset-shadow-secondary",
    "3xl:inset-ring-secondary",
    "4xl:inset-shadow-secondary",
    "4xl:inset-ring-secondary",
    "5xl:inset-shadow-secondary",
    "5xl:inset-ring-secondary"
  ],
  "theme": {
    "screens": {
      "sm": "576px",
      "md": "768px",
      "lg": "1024px",
      "xl": "1280px",
      "2xl": "1600px",
      "3xl": "1920px",
      "4xl": "2560px",
      "5xl": "3840px"
    },
    "container": {
      "screens": {
        "sm": "540px",
        "md": "720px",
        "lg": "960px",
        "xl": "1140px",
        "2xl": "1440px",
        "3xl": "1680px",
        "4xl": "1920px",
        "5xl": "2560px"
      }
    },
    "colors": {
      "transparent": "transparent",
      "current": "currentColor",
      "inherit": "inherit",
      "black": "#1e201f",
      "white": "#fcfcfe",
      "light": "#fcfcfe",
      "primary": "#bf4a4a",
      "secondary": "#c27803"
    },
    "extend": {
      "fontSize": {
        "xs": [
          "0.694rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "sm": [
          "0.833rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "base": [
          "1rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "lg": [
          "1.2rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "xl": [
          "1.44rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "2xl": [
          "1.728rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "3xl": [
          "2.074rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "4xl": [
          "2.488rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "5xl": [
          "2.986rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "6xl": [
          "3.583rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "7xl": [
          "4.3rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "8xl": [
          "5.16rem",
          {
            "lineHeight": "1.2"
          }
        ],
        "9xl": [
          "6.192rem",
          {
            "lineHeight": "1.2"
          }
        ]
      }
    },
    "ringColor": ({ theme }) => ({ ...theme('colors') }),
    "ringOffsetColor": ({ theme }) => ({ ...theme('colors') }),
    "borderColor": ({ theme }) => ({ ...theme('colors') }),
    "outlineColor": ({ theme }) => ({ ...theme('colors') }),
    "textColor": ({ theme }) => ({ ...theme('colors') }),
    "backgroundColor": ({ theme }) => ({ ...theme('colors') }),
    "decorationColor": ({ theme }) => ({ ...theme('colors') }),
    "fill": ({ theme }) => ({ ...theme('colors') }),
    "stroke": ({ theme }) => ({ ...theme('colors') }),
    "caretColor": ({ theme }) => ({ ...theme('colors') }),
    "accentColor": ({ theme }) => ({ ...theme('colors') }),
    "boxShadowColor": ({ theme }) => ({ ...theme('colors') })
  },
  "plugins": [
    function({ matchUtilities, theme }) {
      const flattenColors = (input, prefix = '') => Object.entries(input || {}).reduce((acc, [key, value]) => {
        const token = prefix ? (prefix + '-' + key) : key;
        if (typeof value === 'string') {
          acc[token] = value;
          return acc;
        }
        if (value && typeof value === 'object') {
          Object.assign(acc, flattenColors(value, token));
        }
        return acc;
      }, {});

      const colorValues = flattenColors(theme('colors'));

      matchUtilities(
        {
          'inset-shadow': (value) => ({
            '--tw-shadow-color': value,
            '--tw-shadow': 'inset 0 2px 4px 0 var(--tw-shadow-color)',
            'box-shadow':
              'var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)'
          })
        },
        {
          values: colorValues,
          type: ['color']
        }
      );

      matchUtilities(
        {
          'inset-ring': (value) => ({
            '--tw-inset-ring-color': value,
            '--tw-inset-ring-shadow': 'inset 0 0 0 1px var(--tw-inset-ring-color)',
            'box-shadow':
              'var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow, 0 0 #0000)'
          })
        },
        {
          values: colorValues,
          type: ['color']
        }
      );
    }
  ]
};
