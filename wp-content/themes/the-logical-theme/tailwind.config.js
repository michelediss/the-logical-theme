/*
 * Tailwind configuration scoped to theme templates, parts, patterns, and source files.
 * WordPress theme.json remains the source of truth for tokens and layout widths.
 */

export default {
  content: [
    './*.php',
    './*.html',
    './templates/**/*.html',
    './parts/**/*.html',
    './patterns/**/*.{php,html}',
    './inc/**/*.php',
    './src/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        canvas: 'var(--wp--preset--color--canvas)',
        foreground: 'var(--wp--preset--color--foreground)',
        accent: 'var(--wp--preset--color--accent)',
        muted: 'var(--wp--preset--color--muted)',
      },
      fontFamily: {
        sans: ['var(--wp--preset--font-family--sans)'],
        display: ['var(--wp--preset--font-family--display)'],
      },
      maxWidth: {
        content: 'var(--wp--style--global--content-size)',
        wide: 'var(--wp--style--global--wide-size)',
      },
      borderRadius: {
        sm: 'var(--wp--custom--radius--sm)',
        md: 'var(--wp--custom--radius--md)',
        lg: 'var(--wp--custom--radius--lg)',
      },
      spacing: {
        xs: 'var(--wp--preset--spacing--xs)',
        sm: 'var(--wp--preset--spacing--sm)',
        md: 'var(--wp--preset--spacing--md)',
        lg: 'var(--wp--preset--spacing--lg)',
        xl: 'var(--wp--preset--spacing--xl)',
      },
    },
  },
  plugins: [],
};
