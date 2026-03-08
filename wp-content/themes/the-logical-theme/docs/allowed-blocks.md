# AI Available Blocks Guide

This file describes the blocks that are allowed in this theme and how an AI should use them when composing layouts or templates.

Custom theme blocks are also allowed. The runtime source of truth is:

- `partials/block-availability.php` for curated core blocks
- `blocks/*/block.json` for theme custom blocks discovered automatically

Do not treat this file as the only source of truth for custom blocks. When a custom block exists in `blocks/*/block.json`, it is considered allowed unless project instructions say otherwise.

For `figma-make-theme-sync`, this file is required development guidance, not the canonical runtime block registry. The skill should read the runtime export from `.agents/skills/figma-make-theme-sync/scripts/export-theme-blocks.php` first, then use this document to decide how the available blocks should be composed.

## General rules

- Prefer simple structure first: `group` -> content blocks -> optional spacing/separators.
- Use dynamic blocks only when the content must come from WordPress data such as the current post, query results, site identity, or navigation.
- Do not invent blocks outside this list plus the custom theme blocks discovered from `blocks/*/block.json`.
- Use `columns` only when content clearly benefits from side-by-side layout.
- Use `spacer` sparingly; prefer clean grouping and natural spacing from theme styles.
- Use `template-part`, navigation, and site identity blocks only in theme-level areas such as header, footer, hero, or shared page sections.

## Content blocks

| Block | Usage guidance |
| --- | --- |
| `core/group` | Primary layout wrapper. Use to create sections, control spacing, and keep related blocks together. |
| `core/columns` | Use when two or more content areas should sit side by side. Good for feature comparisons, split hero layouts, or media/text compositions. |
| `core/column` | Child of `columns`. Use only inside a `columns` block. |
| `core/spacer` | Add vertical breathing room only when grouping alone is not enough. Avoid stacking many spacers. |
| `core/separator` | Use to visually divide sections or content groups. |
| `core/heading` | Use for titles and section headings. Keep a clear hierarchy. |
| `core/paragraph` | Default block for body copy, intros, descriptions, and supporting text. |
| `core/list` | Use for bullet or ordered lists when content is naturally list-shaped. |
| `core/list-item` | Child of `list`. Use only inside a list. |
| `core/quote` | Use for testimonials, citations, highlighted statements, or editorial pullouts. |
| `core/details` | Use for expandable FAQ-style or secondary information that should stay collapsed by default. |
| `core/image` | Use for single images. Prefer when one image carries the message. |
| `core/gallery` | Use for multiple related images shown together. |
| `core/cover` | Use for hero or banner sections where text sits over an image or background media. |
| `core/media-text` | Use for side-by-side image/media plus text without manually building columns. |
| `core/buttons` | Wrapper for one or more call-to-action buttons. |
| `core/button` | Use for a single action such as contact, read more, sign up, or navigate. Usually place inside `buttons`. |
| `core/accordion` | Use for grouped collapsible content, especially FAQs or structured explanations. |
| `core/accordion-item` | One collapsible item inside an accordion. |
| `core/accordion-heading` | The clickable heading of an accordion item. |
| `core/accordion-panel` | The hidden/revealed body content of an accordion item. |
| `core/social-links` | Use to display a list of social profiles. Usually in header, footer, or contact sections. |
| `core/social-link` | One social profile inside `social-links`. |
| `core/search` | Use when the page should expose site search directly to the user. |

## Theme and site blocks

| Block | Usage guidance |
| --- | --- |
| `core/navigation` | Use for the main site navigation or a local menu area. Best in headers and footers. |
| `core/navigation-link` | One link inside navigation. |
| `core/navigation-submenu` | Use when navigation needs nested items. |
| `core/home-link` | Use inside navigation when an explicit home entry is needed. |
| `core/template-part` | Use to insert shared theme areas such as header, footer, hero, or page heading. Prefer this instead of rebuilding shared sections manually. |
| `core/site-logo` | Use when the site identity should display the configured logo. |
| `core/site-title` | Use when the site title should come from WordPress settings. |
| `core/site-tagline` | Use when the site tagline should come from WordPress settings. |

## Dynamic and post-data blocks

| Block | Usage guidance |
| --- | --- |
| `core/query` | Use to build lists of posts, archives, related content, or editorial feeds. |
| `core/post-template` | Child container inside `query`. Put post preview blocks here. |
| `core/query-title` | Use to show the current archive, search, or query context title. |
| `core/query-total` | Use when the interface should show how many results a query contains. |
| `core/query-no-results` | Use as the empty state for queries with no matching content. |
| `core/query-pagination` | Wrapper for pagination controls of a query loop. |
| `core/query-pagination-previous` | Previous-page control inside query pagination. |
| `core/query-pagination-numbers` | Numeric pagination inside query pagination. |
| `core/query-pagination-next` | Next-page control inside query pagination. |
| `core/post-title` | Use to render the title of the current post or each queried post. |
| `core/post-content` | Use to render full body content of the current post. Best in single templates. |
| `core/post-excerpt` | Use for previews, cards, archive entries, or summary layouts. |
| `core/post-date` | Use when publication date is meaningful in the layout. |
| `core/post-featured-image` | Use to show the current post thumbnail or featured image. |
| `core/post-terms` | Use to show categories, tags, or taxonomy terms attached to the post. |
| `core/post-navigation-link` | Use on single post layouts to link to the previous or next post. |

## Custom theme blocks

| Block | Usage guidance |
| --- | --- |
| `custom/social-share` | Use for contextual share actions on single content views, article endings, or post meta areas. Avoid using it in global header or footer areas. |
| `custom/breadcrumbs` | Use near the top of pages, single posts, archives, or taxonomy views when the layout benefits from navigational context. |

## Recommended composition patterns

- Simple page section: `group` + `heading` + `paragraph` + optional `buttons`.
- Media section: `media-text` or `columns` with `image` plus text content.
- FAQ section: `group` + `heading` + `accordion`.
- Archive grid: `query` + `post-template` + `post-featured-image` + `post-title` + `post-excerpt` + `query-pagination`.
- Single post meta area: `group` + `post-date` + `post-terms` + optional `post-navigation-link`.
- Shared layout area: `template-part` for header, hero, page heading, or footer instead of rebuilding them inline.

## Maintenance rule

- When a new custom block is added under `blocks/*/block.json`, update this file so AI-assisted theme generation can use it intentionally.
- Do not duplicate the full registration logic here; document usage guidance only.
