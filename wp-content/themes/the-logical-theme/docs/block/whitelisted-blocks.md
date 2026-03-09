# Whitelisted Blocks Reference

This file is generated from `docs/block/block-registry.json`.

It contains only the blocks currently marked as allowed by the theme block availability system, together with the metadata exported in the registry JSON.

## Source

- Input JSON: `/var/www/html/wp-content/themes/the-logical-theme/docs/block/block-registry.json`
- Output file: `/var/www/html/wp-content/themes/the-logical-theme/docs/block/whitelisted-blocks.md`
- Generated at UTC: `2026-03-09T22:46:13+00:00`
- Whitelisted blocks: `78`

## Content (`core/post-content`)

- `title`: `Content`
- `description`: `Displays the contents of a post or page.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_post_content`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- `postId`
- `postType`
- `queryId`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "layout": true,
    "background": {
        "backgroundImage": true,
        "backgroundSize": true,
        "__experimentalDefaultControls": {
            "backgroundImage": true
        }
    },
    "dimensions": {
        "minHeight": true
    },
    "spacing": {
        "blockGap": true,
        "padding": true,
        "margin": true,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false
        }
    },
    "color": {
        "gradients": true,
        "heading": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": false,
            "text": false
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "__experimentalBorder": {
        "radius": true,
        "color": true,
        "width": true,
        "style": true,
        "__experimentalDefaultControls": {
            "radius": true,
            "color": true,
            "width": true,
            "style": true
        }
    }
}
```

### Attributes

```json
{
    "tagName": {
        "type": "string",
        "default": "div"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

```json
{
    "viewportWidth": 350
}
```

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Date (`core/post-date`)

- `title`: `Date`
- `description`: `Display a custom date.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_post_date`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- `postId`
- `postType`
- `queryId`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "color": {
        "gradients": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true,
            "link": true
        }
    },
    "spacing": {
        "margin": true,
        "padding": true
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "__experimentalBorder": {
        "radius": true,
        "color": true,
        "width": true,
        "style": true,
        "__experimentalDefaultControls": {
            "radius": true,
            "color": true,
            "width": true,
            "style": true
        }
    }
}
```

### Attributes

```json
{
    "datetime": {
        "type": "string",
        "role": "content"
    },
    "textAlign": {
        "type": "string"
    },
    "format": {
        "type": "string"
    },
    "isLink": {
        "type": "boolean",
        "default": false,
        "role": "content"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "viewportWidth": 350
}
```

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Excerpt (`core/post-excerpt`)

- `title`: `Excerpt`
- `description`: `Display the excerpt.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_post_excerpt`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- `postId`
- `postType`
- `queryId`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "color": {
        "gradients": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true,
            "link": true
        }
    },
    "spacing": {
        "margin": true,
        "padding": true
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "__experimentalBorder": {
        "radius": true,
        "color": true,
        "width": true,
        "style": true,
        "__experimentalDefaultControls": {
            "radius": true,
            "color": true,
            "width": true,
            "style": true
        }
    }
}
```

### Attributes

```json
{
    "textAlign": {
        "type": "string"
    },
    "moreText": {
        "type": "string",
        "role": "content"
    },
    "showMoreOnNewLine": {
        "type": "boolean",
        "default": true
    },
    "excerptLength": {
        "type": "number",
        "default": 55
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "viewportWidth": 350
}
```

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Featured Image (`core/post-featured-image`)

- `title`: `Featured Image`
- `description`: `Display a post's featured image.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_post_featured_image`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- `postId`
- `postType`
- `queryId`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": [
        "left",
        "right",
        "center",
        "wide",
        "full"
    ],
    "color": {
        "text": false,
        "background": false
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "width": true,
        "__experimentalSkipSerialization": true,
        "__experimentalDefaultControls": {
            "color": true,
            "radius": true,
            "width": true
        }
    },
    "filter": {
        "duotone": true
    },
    "shadow": {
        "__experimentalSkipSerialization": true
    },
    "html": false,
    "spacing": {
        "margin": true,
        "padding": true
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "isLink": {
        "type": "boolean",
        "default": false,
        "role": "content"
    },
    "aspectRatio": {
        "type": "string"
    },
    "width": {
        "type": "string"
    },
    "height": {
        "type": "string"
    },
    "scale": {
        "type": "string",
        "default": "cover"
    },
    "sizeSlug": {
        "type": "string"
    },
    "rel": {
        "type": "string",
        "attribute": "rel",
        "default": "",
        "role": "content"
    },
    "linkTarget": {
        "type": "string",
        "default": "_self",
        "role": "content"
    },
    "overlayColor": {
        "type": "string"
    },
    "customOverlayColor": {
        "type": "string"
    },
    "dimRatio": {
        "type": "number",
        "default": 0
    },
    "gradient": {
        "type": "string"
    },
    "customGradient": {
        "type": "string"
    },
    "useFirstImageFromPost": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "viewportWidth": 350
}
```

### Selectors

- `border`: `.wp-block-post-featured-image img, .wp-block-post-featured-image .block-editor-media-placeholder, .wp-block-post-featured-image .wp-block-post-featured-image__overlay`
- `shadow`: `.wp-block-post-featured-image img, .wp-block-post-featured-image .components-placeholder`
- `filter`:
```json
{
    "duotone": ".wp-block-post-featured-image img, .wp-block-post-featured-image .wp-block-post-featured-image__placeholder, .wp-block-post-featured-image .components-placeholder__illustration, .wp-block-post-featured-image .components-placeholder::before"
}
```

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Post Template (`core/post-template`)

- `title`: `Post Template`
- `description`: `Contains the block elements used to render a post, like the title, date, featured image, content or excerpt, and more.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_post_template`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- `core/query`

### Uses Context

- `queryId`
- `query`
- `displayLayout`
- `templateSlug`
- `previewPostType`
- `enhancedPagination`
- `postType`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "reusable": false,
    "html": false,
    "align": [
        "wide",
        "full"
    ],
    "layout": true,
    "color": {
        "gradients": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "spacing": {
        "margin": true,
        "padding": true,
        "blockGap": {
            "__experimentalDefault": "1.25em"
        },
        "__experimentalDefaultControls": {
            "blockGap": true,
            "padding": false,
            "margin": false
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "__experimentalBorder": {
        "radius": true,
        "color": true,
        "width": true,
        "style": true
    }
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Post Terms (`core/post-terms`)

- `title`: `Post Terms`
- `description`: `Post terms.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_post_terms`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- `postId`
- `postType`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "color": {
        "gradients": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true,
            "link": true
        }
    },
    "spacing": {
        "margin": true,
        "padding": true
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "__experimentalBorder": {
        "radius": true,
        "color": true,
        "width": true,
        "style": true,
        "__experimentalDefaultControls": {
            "radius": true,
            "color": true,
            "width": true,
            "style": true
        }
    }
}
```

### Attributes

```json
{
    "term": {
        "type": "string"
    },
    "textAlign": {
        "type": "string"
    },
    "separator": {
        "type": "string",
        "default": ", "
    },
    "prefix": {
        "type": "string",
        "default": "",
        "role": "content"
    },
    "suffix": {
        "type": "string",
        "default": "",
        "role": "content"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "viewportWidth": 350
}
```

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Title (`core/post-title`)

- `title`: `Title`
- `description`: `Displays the title of a post, page, or any other content-type.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_post_title`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- `postId`
- `postType`
- `queryId`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "color": {
        "gradients": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true,
            "link": true
        }
    },
    "spacing": {
        "margin": true,
        "padding": true
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "__experimentalBorder": {
        "radius": true,
        "color": true,
        "width": true,
        "style": true,
        "__experimentalDefaultControls": {
            "radius": true,
            "color": true,
            "width": true,
            "style": true
        }
    }
}
```

### Attributes

```json
{
    "textAlign": {
        "type": "string"
    },
    "level": {
        "type": "number",
        "default": 2
    },
    "levelOptions": {
        "type": "array"
    },
    "isLink": {
        "type": "boolean",
        "default": false,
        "role": "content"
    },
    "rel": {
        "type": "string",
        "attribute": "rel",
        "default": "",
        "role": "content"
    },
    "linkTarget": {
        "type": "string",
        "default": "_self",
        "role": "content"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "viewportWidth": 350
}
```

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Query Loop (`core/query`)

- `title`: `Query Loop`
- `description`: `An advanced block that allows displaying post types based on different query parameters and visual configurations.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_query`
- `has_render_callback`: `true`

### Keywords

- `posts`
- `list`
- `blog`
- `blogs`
- `custom post types`

### Parent

- None

### Ancestor

- None

### Uses Context

- `templateSlug`

### Provides Context

- `queryId`: `queryId`
- `query`: `query`
- `displayLayout`: `displayLayout`
- `enhancedPagination`: `enhancedPagination`

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "layout": true,
    "interactivity": true,
    "contentRole": true
}
```

### Attributes

```json
{
    "queryId": {
        "type": "number"
    },
    "query": {
        "type": "object",
        "default": {
            "perPage": null,
            "pages": 0,
            "offset": 0,
            "postType": "post",
            "order": "desc",
            "orderBy": "date",
            "author": "",
            "search": "",
            "exclude": [],
            "sticky": "",
            "inherit": true,
            "taxQuery": null,
            "parents": [],
            "format": []
        }
    },
    "tagName": {
        "type": "string",
        "default": "div"
    },
    "namespace": {
        "type": "string"
    },
    "enhancedPagination": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## No Results (`core/query-no-results`)

- `title`: `No Results`
- `description`: `Contains the block elements used to render content when no query results are found.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_query_no_results`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- `core/query`

### Uses Context

- `queryId`
- `query`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": true,
    "reusable": false,
    "html": false,
    "color": {
        "gradients": true,
        "link": true
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Pagination (`core/query-pagination`)

- `title`: `Pagination`
- `description`: `Displays a paginated navigation to next/previous set of posts, when applicable.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_query_pagination`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- `core/query`

### Uses Context

- `queryId`
- `query`

### Provides Context

- `paginationArrow`: `paginationArrow`
- `showLabel`: `showLabel`

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": true,
    "reusable": false,
    "html": false,
    "color": {
        "gradients": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true,
            "link": true
        }
    },
    "layout": {
        "allowSwitching": false,
        "allowInheriting": false,
        "default": {
            "type": "flex"
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "paginationArrow": {
        "type": "string",
        "default": "none"
    },
    "showLabel": {
        "type": "boolean",
        "default": true
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Next Page (`core/query-pagination-next`)

- `title`: `Next Page`
- `description`: `Displays the next posts page link.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_query_pagination_next`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- `core/query-pagination`

### Ancestor

- None

### Uses Context

- `queryId`
- `query`
- `paginationArrow`
- `showLabel`
- `enhancedPagination`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "reusable": false,
    "html": false,
    "color": {
        "gradients": true,
        "text": false,
        "__experimentalDefaultControls": {
            "background": true
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "label": {
        "type": "string"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Page Numbers (`core/query-pagination-numbers`)

- `title`: `Page Numbers`
- `description`: `Displays a list of page numbers for pagination.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_query_pagination_numbers`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- `core/query-pagination`

### Ancestor

- None

### Uses Context

- `queryId`
- `query`
- `enhancedPagination`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "reusable": false,
    "html": false,
    "color": {
        "gradients": true,
        "text": false,
        "__experimentalDefaultControls": {
            "background": true
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "midSize": {
        "type": "number",
        "default": 2
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Previous Page (`core/query-pagination-previous`)

- `title`: `Previous Page`
- `description`: `Displays the previous posts page link.`
- `origin`: `core`
- `category_bucket`: `blog`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_query_pagination_previous`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- `core/query-pagination`

### Ancestor

- None

### Uses Context

- `queryId`
- `query`
- `paginationArrow`
- `showLabel`
- `enhancedPagination`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "reusable": false,
    "html": false,
    "color": {
        "gradients": true,
        "text": false,
        "__experimentalDefaultControls": {
            "background": true
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "label": {
        "type": "string"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Accordion Heading (`core/accordion-heading`)

- `title`: `Accordion Heading`
- `description`: `Displays a heading that toggles the accordion panel.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- None

### Parent

- `core/accordion-item`

### Ancestor

- None

### Uses Context

- `core/accordion-icon-position`
- `core/accordion-show-icon`
- `core/accordion-heading-level`

### Provides Context

- None

### Supports Summary

- `anchor`: `true`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "anchor": true,
    "color": {
        "background": true,
        "gradients": true
    },
    "align": false,
    "interactivity": true,
    "spacing": {
        "padding": true,
        "__experimentalDefaultControls": {
            "padding": true
        },
        "__experimentalSkipSerialization": true,
        "__experimentalSelector": ".wp-block-accordion-heading__toggle"
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": true,
            "radius": true,
            "style": true,
            "width": true
        }
    },
    "typography": {
        "__experimentalSkipSerialization": [
            "textDecoration",
            "letterSpacing"
        ],
        "fontSize": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true,
            "fontFamily": true
        }
    },
    "shadow": true,
    "visibility": false,
    "lock": false
}
```

### Attributes

```json
{
    "openByDefault": {
        "type": "boolean",
        "default": false
    },
    "title": {
        "type": "rich-text",
        "source": "rich-text",
        "selector": ".wp-block-accordion-heading__toggle-title",
        "role": "content"
    },
    "level": {
        "type": "number"
    },
    "iconPosition": {
        "type": "string",
        "enum": [
            "left",
            "right"
        ],
        "default": "right"
    },
    "showIcon": {
        "type": "boolean",
        "default": true
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- `typography`:
```json
{
    "letterSpacing": ".wp-block-accordion-heading .wp-block-accordion-heading__toggle-title",
    "textDecoration": ".wp-block-accordion-heading .wp-block-accordion-heading__toggle-title"
}
```

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Buttons (`core/buttons`)

- `title`: `Buttons`
- `description`: `Prompt visitors to take action with a group of button-style links.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- `link`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `true`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "anchor": true,
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "__experimentalExposeControlsToChildren": true,
    "color": {
        "gradients": true,
        "text": false,
        "__experimentalDefaultControls": {
            "background": true
        }
    },
    "spacing": {
        "blockGap": [
            "horizontal",
            "vertical"
        ],
        "padding": true,
        "margin": [
            "top",
            "bottom"
        ],
        "__experimentalDefaultControls": {
            "blockGap": true
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": true,
            "radius": true,
            "style": true,
            "width": true
        }
    },
    "layout": {
        "allowSwitching": false,
        "allowInheriting": false,
        "default": {
            "type": "flex"
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "contentRole": true
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Column (`core/column`)

- `title`: `Column`
- `description`: `A single column within a columns block.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- None

### Parent

- `core/columns`

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `true`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "__experimentalOnEnter": true,
    "anchor": true,
    "reusable": false,
    "html": false,
    "color": {
        "gradients": true,
        "heading": true,
        "button": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true
        }
    },
    "shadow": true,
    "spacing": {
        "blockGap": true,
        "padding": true,
        "__experimentalDefaultControls": {
            "padding": true,
            "blockGap": true
        }
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": true,
            "radius": true,
            "style": true,
            "width": true
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "layout": true,
    "interactivity": {
        "clientNavigation": true
    },
    "allowedBlocks": true
}
```

### Attributes

```json
{
    "verticalAlignment": {
        "type": "string"
    },
    "width": {
        "type": "string"
    },
    "templateLock": {
        "type": [
            "string",
            "boolean"
        ],
        "enum": [
            "all",
            "insert",
            "contentOnly",
            false
        ]
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Columns (`core/columns`)

- `title`: `Columns`
- `description`: `Display content in multiple columns, with blocks added to each column.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `true`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "anchor": true,
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "color": {
        "gradients": true,
        "link": true,
        "heading": true,
        "button": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true
        }
    },
    "spacing": {
        "blockGap": {
            "__experimentalDefault": "2em",
            "sides": [
                "horizontal",
                "vertical"
            ]
        },
        "margin": [
            "top",
            "bottom"
        ],
        "padding": true,
        "__experimentalDefaultControls": {
            "padding": true,
            "blockGap": true
        }
    },
    "layout": {
        "allowSwitching": false,
        "allowInheriting": false,
        "allowEditing": false,
        "default": {
            "type": "flex",
            "flexWrap": "nowrap"
        }
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": true,
            "radius": true,
            "style": true,
            "width": true
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "shadow": true
}
```

### Attributes

```json
{
    "verticalAlignment": {
        "type": "string"
    },
    "isStackedOnMobile": {
        "type": "boolean",
        "default": true
    },
    "templateLock": {
        "type": [
            "string",
            "boolean"
        ],
        "enum": [
            "all",
            "insert",
            "contentOnly",
            false
        ]
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Group (`core/group`)

- `title`: `Group`
- `description`: `Gather blocks in a layout container.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- `container`
- `wrapper`
- `row`
- `section`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `true`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "__experimentalOnEnter": true,
    "__experimentalOnMerge": true,
    "__experimentalSettings": true,
    "align": [
        "wide",
        "full"
    ],
    "anchor": true,
    "ariaLabel": true,
    "html": false,
    "background": {
        "backgroundImage": true,
        "backgroundSize": true,
        "__experimentalDefaultControls": {
            "backgroundImage": true
        }
    },
    "color": {
        "gradients": true,
        "heading": true,
        "button": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true
        }
    },
    "shadow": true,
    "spacing": {
        "margin": [
            "top",
            "bottom"
        ],
        "padding": true,
        "blockGap": true,
        "__experimentalDefaultControls": {
            "padding": true,
            "blockGap": true
        }
    },
    "dimensions": {
        "minHeight": true
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": true,
            "radius": true,
            "style": true,
            "width": true
        }
    },
    "position": {
        "sticky": true
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "layout": {
        "allowSizingOnChildren": true
    },
    "interactivity": {
        "clientNavigation": true
    },
    "allowedBlocks": true
}
```

### Attributes

```json
{
    "tagName": {
        "type": "string",
        "default": "div"
    },
    "templateLock": {
        "type": [
            "string",
            "boolean"
        ],
        "enum": [
            "all",
            "insert",
            "contentOnly",
            false
        ]
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    },
    "ariaLabel": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Home Link (`core/home-link`)

- `title`: `Home Link`
- `description`: `Create a link that always points to the homepage of the site. Usually not necessary if there is already a site title link present in the header.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `render_block_core_home_link`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- `core/navigation`

### Ancestor

- None

### Uses Context

- `textColor`
- `customTextColor`
- `backgroundColor`
- `customBackgroundColor`
- `fontSize`
- `customFontSize`
- `style`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "reusable": false,
    "html": false,
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "label": {
        "type": "string",
        "role": "content"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## List Item (`core/list-item`)

- `title`: `List Item`
- `description`: `An individual item within a list.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `text`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- None

### Parent

- `core/list`

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `true`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "anchor": true,
    "className": false,
    "splitting": true,
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true
    },
    "color": {
        "gradients": true,
        "link": true,
        "background": true,
        "__experimentalDefaultControls": {
            "text": true
        }
    },
    "spacing": {
        "margin": true,
        "padding": true,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "placeholder": {
        "type": "string"
    },
    "content": {
        "type": "rich-text",
        "source": "rich-text",
        "selector": "li",
        "role": "content"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- `root`: `.wp-block-list > li`
- `border`: `.wp-block-list:not(.wp-block-list .wp-block-list) > li`

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Navigation (`core/navigation`)

- `title`: `Navigation`
- `description`: `A collection of blocks that allow visitors to get around your site.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_navigation`
- `has_render_callback`: `true`

### Keywords

- `menu`
- `navigation`
- `links`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- `textColor`: `textColor`
- `customTextColor`: `customTextColor`
- `backgroundColor`: `backgroundColor`
- `customBackgroundColor`: `customBackgroundColor`
- `overlayTextColor`: `overlayTextColor`
- `customOverlayTextColor`: `customOverlayTextColor`
- `overlayBackgroundColor`: `overlayBackgroundColor`
- `customOverlayBackgroundColor`: `customOverlayBackgroundColor`
- `fontSize`: `fontSize`
- `customFontSize`: `customFontSize`
- `showSubmenuIcon`: `showSubmenuIcon`
- `openSubmenusOnClick`: `openSubmenusOnClick`
- `style`: `style`
- `maxNestingLevel`: `maxNestingLevel`

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `false`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": [
        "wide",
        "full"
    ],
    "ariaLabel": true,
    "html": false,
    "inserter": true,
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontStyle": true,
        "__experimentalFontWeight": true,
        "__experimentalTextTransform": true,
        "__experimentalFontFamily": true,
        "__experimentalLetterSpacing": true,
        "__experimentalTextDecoration": true,
        "__experimentalSkipSerialization": [
            "textDecoration"
        ],
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "spacing": {
        "blockGap": true,
        "units": [
            "px",
            "em",
            "rem",
            "vh",
            "vw"
        ],
        "__experimentalDefaultControls": {
            "blockGap": true
        }
    },
    "layout": {
        "allowSwitching": false,
        "allowInheriting": false,
        "allowVerticalAlignment": false,
        "allowSizingOnChildren": true,
        "default": {
            "type": "flex"
        }
    },
    "interactivity": true,
    "renaming": false,
    "contentRole": true
}
```

### Attributes

```json
{
    "ref": {
        "type": "number"
    },
    "textColor": {
        "type": "string"
    },
    "customTextColor": {
        "type": "string"
    },
    "rgbTextColor": {
        "type": "string"
    },
    "backgroundColor": {
        "type": "string"
    },
    "customBackgroundColor": {
        "type": "string"
    },
    "rgbBackgroundColor": {
        "type": "string"
    },
    "showSubmenuIcon": {
        "type": "boolean",
        "default": true
    },
    "openSubmenusOnClick": {
        "type": "boolean",
        "default": false
    },
    "overlayMenu": {
        "type": "string",
        "default": "mobile"
    },
    "icon": {
        "type": "string",
        "default": "handle"
    },
    "hasIcon": {
        "type": "boolean",
        "default": true
    },
    "__unstableLocation": {
        "type": "string"
    },
    "overlayBackgroundColor": {
        "type": "string"
    },
    "customOverlayBackgroundColor": {
        "type": "string"
    },
    "overlayTextColor": {
        "type": "string"
    },
    "customOverlayTextColor": {
        "type": "string"
    },
    "maxNestingLevel": {
        "type": "number",
        "default": 5
    },
    "templateLock": {
        "type": [
            "string",
            "boolean"
        ],
        "enum": [
            "all",
            "insert",
            "contentOnly",
            false
        ]
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    },
    "ariaLabel": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Custom Link (`core/navigation-link`)

- `title`: `Custom Link`
- `description`: `Add a page, link, or another item to your navigation.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `render_block_core_navigation_link`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- `core/navigation`

### Ancestor

- None

### Uses Context

- `textColor`
- `customTextColor`
- `backgroundColor`
- `customBackgroundColor`
- `overlayTextColor`
- `customOverlayTextColor`
- `overlayBackgroundColor`
- `customOverlayBackgroundColor`
- `fontSize`
- `customFontSize`
- `showSubmenuIcon`
- `maxNestingLevel`
- `style`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "reusable": false,
    "html": false,
    "__experimentalSlashInserter": true,
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "renaming": false,
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "label": {
        "type": "string",
        "role": "content"
    },
    "type": {
        "type": "string"
    },
    "description": {
        "type": "string"
    },
    "rel": {
        "type": "string"
    },
    "id": {
        "type": "number"
    },
    "opensInNewTab": {
        "type": "boolean",
        "default": false
    },
    "url": {
        "type": "string"
    },
    "title": {
        "type": "string"
    },
    "kind": {
        "type": "string"
    },
    "isTopLevelLink": {
        "type": "boolean"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Submenu (`core/navigation-submenu`)

- `title`: `Submenu`
- `description`: `Add a submenu to your navigation.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `render_block_core_navigation_submenu`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- `core/navigation`

### Ancestor

- None

### Uses Context

- `textColor`
- `customTextColor`
- `backgroundColor`
- `customBackgroundColor`
- `overlayTextColor`
- `customOverlayTextColor`
- `overlayBackgroundColor`
- `customOverlayBackgroundColor`
- `fontSize`
- `customFontSize`
- `showSubmenuIcon`
- `maxNestingLevel`
- `openSubmenusOnClick`
- `style`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "reusable": false,
    "html": false,
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontWeight": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "label": {
        "type": "string",
        "role": "content"
    },
    "type": {
        "type": "string"
    },
    "description": {
        "type": "string"
    },
    "rel": {
        "type": "string"
    },
    "id": {
        "type": "number"
    },
    "opensInNewTab": {
        "type": "boolean",
        "default": false
    },
    "url": {
        "type": "string"
    },
    "title": {
        "type": "string"
    },
    "kind": {
        "type": "string"
    },
    "isTopLevelItem": {
        "type": "boolean"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Page List Item (`core/page-list-item`)

- `title`: `Page List Item`
- `description`: `Displays a page inside a list of all pages.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `widgets`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- `page`
- `menu`
- `navigation`

### Parent

- `core/page-list`

### Ancestor

- None

### Uses Context

- `textColor`
- `customTextColor`
- `backgroundColor`
- `customBackgroundColor`
- `overlayTextColor`
- `customOverlayTextColor`
- `overlayBackgroundColor`
- `customOverlayBackgroundColor`
- `fontSize`
- `customFontSize`
- `showSubmenuIcon`
- `style`
- `openSubmenusOnClick`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "reusable": false,
    "html": false,
    "lock": false,
    "inserter": false,
    "__experimentalToolbar": false,
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "id": {
        "type": "number"
    },
    "label": {
        "type": "string"
    },
    "title": {
        "type": "string"
    },
    "link": {
        "type": "string"
    },
    "hasChildren": {
        "type": "boolean"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Paragraph (`core/paragraph`)

- `title`: `Paragraph`
- `description`: `Start with the basic building block of all narrative.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `text`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- `text`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `true`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "splitting": true,
    "anchor": true,
    "className": false,
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true
    },
    "color": {
        "gradients": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true
        }
    },
    "spacing": {
        "margin": true,
        "padding": true,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalTextDecoration": true,
        "__experimentalFontStyle": true,
        "__experimentalFontWeight": true,
        "__experimentalLetterSpacing": true,
        "__experimentalTextTransform": true,
        "__experimentalWritingMode": true,
        "fitText": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "__experimentalSelector": "p",
    "__unstablePasteTextInline": true,
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "align": {
        "type": "string"
    },
    "content": {
        "type": "rich-text",
        "source": "rich-text",
        "selector": "p",
        "role": "content"
    },
    "dropCap": {
        "type": "boolean",
        "default": false
    },
    "placeholder": {
        "type": "string"
    },
    "direction": {
        "type": "string",
        "enum": [
            "ltr",
            "rtl"
        ]
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Separator (`core/separator`)

- `title`: `Separator`
- `description`: `Create a break between ideas or sections with a horizontal separator.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- `horizontal-line`
- `hr`
- `divider`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `true`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "anchor": true,
    "align": [
        "center",
        "wide",
        "full"
    ],
    "color": {
        "enableContrastChecker": false,
        "__experimentalSkipSerialization": true,
        "gradients": true,
        "background": true,
        "text": false,
        "__experimentalDefaultControls": {
            "background": true
        }
    },
    "spacing": {
        "margin": [
            "top",
            "bottom"
        ]
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "opacity": {
        "type": "string",
        "default": "alpha-channel"
    },
    "tagName": {
        "type": "string",
        "enum": [
            "hr",
            "div"
        ],
        "default": "hr"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Site Logo (`core/site-logo`)

- `title`: `Site Logo`
- `description`: `Display an image to represent this site. Update this block and the changes apply everywhere.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_site_logo`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "align": true,
    "alignWide": false,
    "color": {
        "text": false,
        "background": false
    },
    "spacing": {
        "margin": true,
        "padding": true,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "filter": {
        "duotone": true
    }
}
```

### Attributes

```json
{
    "width": {
        "type": "number"
    },
    "isLink": {
        "type": "boolean",
        "default": true,
        "role": "content"
    },
    "linkTarget": {
        "type": "string",
        "default": "_self",
        "role": "content"
    },
    "shouldSyncIcon": {
        "type": "boolean"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    }
}
```

### Example

```json
{
    "viewportWidth": 500,
    "attributes": {
        "width": 350,
        "className": "block-editor-block-types-list__site-logo-example"
    }
}
```

### Selectors

- `filter`:
```json
{
    "duotone": ".wp-block-site-logo img, .wp-block-site-logo .components-placeholder__illustration, .wp-block-site-logo .components-placeholder::before"
}
```

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Site Tagline (`core/site-tagline`)

- `title`: `Site Tagline`
- `description`: `Describe in a few words what this site is about. This is important for search results, sharing on social media, and gives overall clarity to visitors.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_site_tagline`
- `has_render_callback`: `true`

### Keywords

- `description`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "color": {
        "gradients": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true
        }
    },
    "contentRole": true,
    "spacing": {
        "margin": true,
        "padding": true,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalFontStyle": true,
        "__experimentalFontWeight": true,
        "__experimentalLetterSpacing": true,
        "__experimentalWritingMode": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "__experimentalBorder": {
        "radius": true,
        "color": true,
        "width": true,
        "style": true
    }
}
```

### Attributes

```json
{
    "textAlign": {
        "type": "string"
    },
    "level": {
        "type": "number",
        "default": 0
    },
    "levelOptions": {
        "type": "array",
        "default": [
            0,
            1,
            2,
            3,
            4,
            5,
            6
        ]
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "viewportWidth": 350,
    "attributes": {
        "textAlign": "center"
    }
}
```

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Site Title (`core/site-title`)

- `title`: `Site Title`
- `description`: `Displays the name of this site. Update the block, and the changes apply everywhere it’s used. This will also appear in the browser title bar and in search results.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `render_block_core_site_title`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "color": {
        "gradients": true,
        "link": true,
        "__experimentalDefaultControls": {
            "background": true,
            "text": true,
            "link": true
        }
    },
    "spacing": {
        "padding": true,
        "margin": true,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalFontStyle": true,
        "__experimentalFontWeight": true,
        "__experimentalLetterSpacing": true,
        "__experimentalWritingMode": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "__experimentalBorder": {
        "radius": true,
        "color": true,
        "width": true,
        "style": true
    }
}
```

### Attributes

```json
{
    "level": {
        "type": "number",
        "default": 1
    },
    "levelOptions": {
        "type": "array",
        "default": [
            0,
            1,
            2,
            3,
            4,
            5,
            6
        ]
    },
    "textAlign": {
        "type": "string"
    },
    "isLink": {
        "type": "boolean",
        "default": true,
        "role": "content"
    },
    "linkTarget": {
        "type": "string",
        "default": "_self",
        "role": "content"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "viewportWidth": 500
}
```

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Social Icon (`core/social-link`)

- `title`: `Social Icon`
- `description`: `Display an icon linking to a social profile or site.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `widgets`
- `icon`: `null`
- `render_callback`: `render_block_core_social_link`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- `core/social-links`

### Ancestor

- None

### Uses Context

- `openInNewTab`
- `showLabels`
- `iconColor`
- `iconColorValue`
- `iconBackgroundColor`
- `iconBackgroundColorValue`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "reusable": false,
    "html": false,
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "url": {
        "type": "string",
        "role": "content"
    },
    "service": {
        "type": "string"
    },
    "label": {
        "type": "string",
        "role": "content"
    },
    "rel": {
        "type": "string"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Social Icons (`core/social-links`)

- `title`: `Social Icons`
- `description`: `Display icons linking to your social profiles or sites.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `widgets`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- `links`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- `openInNewTab`: `openInNewTab`
- `showLabels`: `showLabels`
- `iconColor`: `iconColor`
- `iconColorValue`: `iconColorValue`
- `iconBackgroundColor`: `iconBackgroundColor`
- `iconBackgroundColorValue`: `iconBackgroundColorValue`

### Supports Summary

- `anchor`: `true`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": [
        "left",
        "center",
        "right"
    ],
    "anchor": true,
    "html": false,
    "__experimentalExposeControlsToChildren": true,
    "layout": {
        "allowSwitching": false,
        "allowInheriting": false,
        "allowVerticalAlignment": false,
        "default": {
            "type": "flex"
        }
    },
    "color": {
        "enableContrastChecker": false,
        "background": true,
        "gradients": true,
        "text": false,
        "__experimentalDefaultControls": {
            "background": false
        }
    },
    "spacing": {
        "blockGap": [
            "horizontal",
            "vertical"
        ],
        "margin": true,
        "padding": true,
        "units": [
            "px",
            "em",
            "rem",
            "vh",
            "vw"
        ],
        "__experimentalDefaultControls": {
            "blockGap": true,
            "margin": true,
            "padding": false
        }
    },
    "interactivity": {
        "clientNavigation": true
    },
    "__experimentalBorder": {
        "radius": true,
        "color": true,
        "width": true,
        "style": true,
        "__experimentalDefaultControls": {
            "radius": true,
            "color": true,
            "width": true,
            "style": true
        }
    },
    "contentRole": true
}
```

### Attributes

```json
{
    "iconColor": {
        "type": "string"
    },
    "customIconColor": {
        "type": "string"
    },
    "iconColorValue": {
        "type": "string"
    },
    "iconBackgroundColor": {
        "type": "string"
    },
    "customIconBackgroundColor": {
        "type": "string"
    },
    "iconBackgroundColorValue": {
        "type": "string"
    },
    "openInNewTab": {
        "type": "boolean",
        "default": false
    },
    "showLabels": {
        "type": "boolean",
        "default": false
    },
    "size": {
        "type": "string"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "gradient": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Spacer (`core/spacer`)

- `title`: `Spacer`
- `description`: `Add white space between blocks and customize its height.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `design`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- `orientation`

### Provides Context

- None

### Supports Summary

- `anchor`: `true`
- `align`: `false`
- `spacing`: `true`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "anchor": true,
    "spacing": {
        "margin": [
            "top",
            "bottom"
        ],
        "__experimentalDefaultControls": {
            "margin": true
        }
    },
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "height": {
        "type": "string",
        "default": "100px"
    },
    "width": {
        "type": "string"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Template Part (`core/template-part`)

- `title`: `Template Part`
- `description`: `Edit the different global regions of your site, like the header, footer, sidebar, or create your own.`
- `origin`: `core`
- `category_bucket`: `core`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `theme`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTemplatesController::render_woocommerce_template_part`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "align": true,
    "html": false,
    "reusable": false,
    "renaming": false,
    "interactivity": {
        "clientNavigation": true
    }
}
```

### Attributes

```json
{
    "slug": {
        "type": "string"
    },
    "theme": {
        "type": "string"
    },
    "tagName": {
        "type": "string"
    },
    "area": {
        "type": "string"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- None

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Breadcrumbs (`custom/breadcrumbs`)

- `title`: `Breadcrumbs`
- `description`: `Contextual navigation trail for the current view.`
- `origin`: `custom`
- `category_bucket`: `custom`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `widgets`
- `icon`: `ellipsis`
- `render_callback`: `Closure`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false
}
```

### Attributes

```json
{
    "showCurrent": {
        "type": "boolean",
        "default": true
    },
    "separator": {
        "type": "string",
        "default": "/"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `the-logical-theme-blocks-style`

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `the-logical-theme-blocks-editor`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

```json
{
    "path": "blocks/breadcrumbs/block.json",
    "block_json": {
        "$schema": "https://schemas.wp.org/trunk/block.json",
        "apiVersion": 3,
        "name": "custom/breadcrumbs",
        "version": "0.1.0",
        "title": "Breadcrumbs",
        "category": "widgets",
        "icon": "ellipsis",
        "description": "Contextual navigation trail for the current view.",
        "textdomain": "the-logical-theme",
        "attributes": {
            "showCurrent": {
                "type": "boolean",
                "default": true
            },
            "separator": {
                "type": "string",
                "default": "/"
            }
        },
        "supports": {
            "html": false
        },
        "style": "the-logical-theme-blocks-style",
        "editorScript": "the-logical-theme-blocks-editor"
    }
}
```

## Social Share (`custom/social-share`)

- `title`: `Social Share`
- `description`: `Social sharing buttons for the current content.`
- `origin`: `custom`
- `category_bucket`: `custom`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `widgets`
- `icon`: `share`
- `render_callback`: `Closure`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false
}
```

### Attributes

```json
{
    "color": {
        "type": "string",
        "default": ""
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `the-logical-theme-blocks-style`

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `the-logical-theme-blocks-editor`

### View Script Handles

- `the-logical-theme-blocks-view`

### View Style Handles

- None

### Custom Metadata

```json
{
    "path": "blocks/social-share/block.json",
    "block_json": {
        "$schema": "https://schemas.wp.org/trunk/block.json",
        "apiVersion": 3,
        "name": "custom/social-share",
        "version": "0.1.0",
        "title": "Social Share",
        "category": "widgets",
        "icon": "share",
        "description": "Social sharing buttons for the current content.",
        "textdomain": "the-logical-theme",
        "attributes": {
            "color": {
                "type": "string",
                "default": ""
            }
        },
        "supports": {
            "html": false
        },
        "style": "the-logical-theme-blocks-style",
        "editorScript": "the-logical-theme-blocks-editor",
        "viewScript": "the-logical-theme-blocks-view"
    }
}
```

## Contact Form 7 (`contact-form-7/contact-form-selector`)

- `title`: `Contact Form 7`
- `description`: `Insert a contact form you have created with Contact Form 7.`
- `origin`: `third_party`
- `category_bucket`: `third_party`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `false`
- `api_version`: `3`
- `category`: `widgets`
- `icon`: `null`
- `render_callback`: `null`
- `has_render_callback`: `false`

### Keywords

- `form`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

- None

### Attributes

```json
{
    "id": {
        "type": "integer"
    },
    "hash": {
        "type": "string"
    },
    "title": {
        "type": "string"
    },
    "htmlId": {
        "type": "string"
    },
    "htmlName": {
        "type": "string"
    },
    "htmlTitle": {
        "type": "string"
    },
    "htmlClass": {
        "type": "string"
    },
    "output": {
        "enum": [
            "form",
            "raw_form"
        ],
        "default": "form"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `contact-form-7-contact-form-selector-editor-script`
- `contact-form-7-block-editor`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Cookie Banner (`simple-cookie-consent/banner`)

- `title`: `Cookie Banner`
- `description`: `Displays the cookie consent banner managed by the plugin.`
- `origin`: `third_party`
- `category_bucket`: `third_party`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `widgets`
- `icon`: `shield`
- `render_callback`: `Simple_Cookie_Consent_Plugin::render_banner_block`
- `has_render_callback`: `true`

### Keywords

- `cookie`
- `privacy`
- `consent`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "multiple": false
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `simple-cookie-consent`

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `simple-cookie-consent-editor`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Cookie Table (`simple-cookie-consent/cookie-table`)

- `title`: `Cookie Table`
- `description`: `Displays the registered cookies as a table or card list.`
- `origin`: `third_party`
- `category_bucket`: `third_party`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `widgets`
- `icon`: `table-col-after`
- `render_callback`: `Simple_Cookie_Consent_Plugin::render_cookie_table_block`
- `has_render_callback`: `true`

### Keywords

- `cookie`
- `table`
- `privacy`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false
}
```

### Attributes

```json
{
    "layout": {
        "type": "string",
        "default": "table"
    },
    "category": {
        "type": "string",
        "default": ""
    },
    "showCategory": {
        "type": "boolean",
        "default": true
    },
    "showDuration": {
        "type": "boolean",
        "default": true
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `simple-cookie-consent`

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `simple-cookie-consent-editor`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Active Filters Controls (`woocommerce/active-filters`)

- `title`: `Active Filters Controls`
- `description`: `Display the currently active filters.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ActiveFilters::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "html": false,
    "multiple": false,
    "inserter": false,
    "color": {
        "text": true,
        "background": false
    },
    "lock": false
}
```

### Attributes

```json
{
    "displayStyle": {
        "type": "string",
        "default": "list"
    },
    "headingLevel": {
        "type": "number",
        "default": 3
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-active-filters`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-active-filters-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## All Reviews (`woocommerce/all-reviews`)

- `title`: `All Reviews`
- `description`: `Show a list of all product reviews.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\AllReviews::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "interactivity": {
        "clientNavigation": true
    },
    "color": {
        "background": false
    },
    "typography": {
        "fontSize": true
    }
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-all-reviews`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-all-reviews-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Filter by Attribute Controls (`woocommerce/attribute-filter`)

- `title`: `Filter by Attribute Controls`
- `description`: `Enable customers to filter the product grid by selecting one or more attributes, such as color.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\AttributeFilter::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "color": {
        "text": true,
        "background": false
    },
    "inserter": false,
    "lock": false,
    "interactivity": false
}
```

### Attributes

```json
{
    "className": {
        "type": "string",
        "default": ""
    },
    "attributeId": {
        "type": "number",
        "default": 0
    },
    "showCounts": {
        "type": "boolean",
        "default": false
    },
    "queryType": {
        "type": "string",
        "default": "or"
    },
    "headingLevel": {
        "type": "number",
        "default": 3
    },
    "displayStyle": {
        "type": "string",
        "default": "list"
    },
    "showFilterButton": {
        "type": "boolean",
        "default": false
    },
    "selectType": {
        "type": "string",
        "default": "multiple"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-attribute-filter`
- `wc-blocks-packages-style`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-attribute-filter-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Store Breadcrumbs (`woocommerce/breadcrumbs`)

- `title`: `Store Breadcrumbs`
- `description`: `Enable customers to keep track of their location within the store and navigate back to parent pages.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\Breadcrumbs::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": true
    },
    "align": [
        "wide",
        "full"
    ],
    "color": {
        "background": false,
        "link": true
    },
    "html": false,
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontStyle": true,
        "__experimentalFontWeight": true,
        "__experimentalTextTransform": true,
        "__experimentalDefaultControls": {
            "fontSize": true
        }
    }
}
```

### Attributes

```json
{
    "contentJustification": {
        "type": "string"
    },
    "fontSize": {
        "type": "string",
        "default": "small"
    },
    "align": {
        "type": "string",
        "default": "wide"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-breadcrumbs`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-breadcrumbs-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Cart Link (`woocommerce/cart-link`)

- `title`: `Cart Link`
- `description`: `Display a link to the cart.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `cart`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\CartLink::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": true
    },
    "html": false,
    "multiple": false,
    "typography": {
        "fontSize": true
    },
    "color": {
        "text": false,
        "link": true
    },
    "spacing": {
        "padding": true
    }
}
```

### Attributes

```json
{
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "cartIcon": {
        "type": "string",
        "default": "cart"
    },
    "content": {
        "type": "string",
        "default": null
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    }
}
```

### Example

```json
{
    "attributes": {
        "isPreview": true,
        "cartIcon": "cart",
        "content": "Cart"
    }
}
```

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-cart-link`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-cart-link-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Catalog Sorting (`woocommerce/catalog-sorting`)

- `title`: `Catalog Sorting`
- `description`: `Enable customers to change the sorting order of the products.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\CatalogSorting::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": true
    },
    "color": {
        "text": true,
        "background": false
    },
    "typography": {
        "fontSize": true
    }
}
```

### Attributes

```json
{
    "fontSize": {
        "type": "string",
        "default": "small"
    },
    "useLabel": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-catalog-sorting`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-catalog-sorting-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Classic Shortcode (`woocommerce/classic-shortcode`)

- `title`: `Classic Shortcode`
- `description`: `Renders classic WooCommerce shortcodes.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ClassicShortcode::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "align": true,
    "html": false,
    "multiple": false,
    "reusable": false,
    "inserter": true
}
```

### Attributes

```json
{
    "shortcode": {
        "type": "string",
        "default": "cart",
        "enum": [
            "cart",
            "checkout"
        ]
    },
    "align": {
        "type": "string",
        "default": "wide"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-classic-shortcode-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Customer account (`woocommerce/customer-account`)

- `title`: `Customer account`
- `description`: `A block that allows your customers to log in and out of their accounts in your store.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\CustomerAccount::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`
- `My Account`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": true
    },
    "align": true,
    "color": {
        "text": true
    },
    "typography": {
        "fontSize": true,
        "__experimentalFontFamily": true
    },
    "spacing": {
        "margin": true,
        "padding": true
    }
}
```

### Attributes

```json
{
    "displayStyle": {
        "type": "string",
        "default": "icon_and_text"
    },
    "iconStyle": {
        "type": "string",
        "default": "default"
    },
    "iconClass": {
        "type": "string",
        "default": "icon"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-customer-account`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-customer-account-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Featured Category (`woocommerce/featured-category`)

- `title`: `Featured Category`
- `description`: `Visually highlight a product category and encourage prompt action.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\FeaturedCategory::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- `termId`
- `termTaxonomy`

### Provides Context

- `termId`: `categoryId`
- `termTaxonomy`: `termTaxonomy`

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": true
    },
    "align": [
        "wide",
        "full"
    ],
    "ariaLabel": true,
    "color": {
        "background": true,
        "text": true
    },
    "html": false,
    "filter": {
        "duotone": true
    },
    "spacing": {
        "padding": true,
        "__experimentalDefaultControls": {
            "padding": true
        },
        "__experimentalSkipSerialization": true
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": true,
            "radius": true,
            "width": true
        },
        "__experimentalSkipSerialization": true
    }
}
```

### Attributes

```json
{
    "alt": {
        "type": "string",
        "default": ""
    },
    "contentAlign": {
        "type": "string",
        "default": "center"
    },
    "dimRatio": {
        "type": "number",
        "default": 50
    },
    "focalPoint": {
        "type": "object",
        "default": {
            "x": 0.5,
            "y": 0.5
        }
    },
    "imageFit": {
        "type": "string",
        "default": "none"
    },
    "hasParallax": {
        "type": "boolean",
        "default": false
    },
    "isRepeated": {
        "type": "boolean",
        "default": false
    },
    "mediaId": {
        "type": "number",
        "default": 0
    },
    "mediaSrc": {
        "type": "string",
        "default": ""
    },
    "minHeight": {
        "type": "number",
        "default": 500
    },
    "linkText": {
        "default": "Shop now",
        "type": "string"
    },
    "categoryId": {
        "type": "number"
    },
    "overlayColor": {
        "type": "string",
        "default": "#000000"
    },
    "overlayGradient": {
        "type": "string"
    },
    "previewCategory": {
        "type": "object",
        "default": null
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    },
    "ariaLabel": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- `filter`:
```json
{
    "duotone": ".wp-block-woocommerce-featured-category .wc-block-featured-category__background-image"
}
```

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-featured-category`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-featured-category-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Featured Product (`woocommerce/featured-product`)

- `title`: `Featured Product`
- `description`: `Highlight a product or variation.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\FeaturedProduct::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `true`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": true
    },
    "align": [
        "wide",
        "full"
    ],
    "ariaLabel": true,
    "html": false,
    "filter": {
        "duotone": true
    },
    "color": {
        "background": true,
        "text": true
    },
    "spacing": {
        "padding": true,
        "__experimentalDefaultControls": {
            "padding": true
        },
        "__experimentalSkipSerialization": true
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": true,
            "radius": true,
            "width": true
        },
        "__experimentalSkipSerialization": true
    },
    "multiple": true
}
```

### Attributes

```json
{
    "alt": {
        "type": "string",
        "default": ""
    },
    "contentAlign": {
        "type": "string",
        "default": "center"
    },
    "dimRatio": {
        "type": "number",
        "default": 50
    },
    "focalPoint": {
        "type": "object",
        "default": {
            "x": 0.5,
            "y": 0.5
        }
    },
    "imageFit": {
        "type": "string",
        "default": "none"
    },
    "hasParallax": {
        "type": "boolean",
        "default": false
    },
    "isRepeated": {
        "type": "boolean",
        "default": false
    },
    "mediaId": {
        "type": "number",
        "default": 0
    },
    "mediaSrc": {
        "type": "string",
        "default": ""
    },
    "minHeight": {
        "type": "number",
        "default": 500
    },
    "linkText": {
        "type": "string",
        "default": "Shop now"
    },
    "overlayColor": {
        "type": "string",
        "default": "#000000"
    },
    "overlayGradient": {
        "type": "string"
    },
    "productId": {
        "type": "number"
    },
    "previewProduct": {
        "type": "object",
        "default": null
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    },
    "ariaLabel": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- `filter`:
```json
{
    "duotone": ".wp-block-woocommerce-featured-product .wc-block-featured-product__background-image"
}
```

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-featured-product`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-featured-product-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Filter Block (`woocommerce/filter-wrapper`)

- `title`: `Filter Block`
- `description`: `null`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\FilterWrapper::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "inserter": false
}
```

### Attributes

```json
{
    "filterType": {
        "type": "string"
    },
    "heading": {
        "type": "string"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-filter-wrapper-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Hand-picked Products (`woocommerce/handpicked-products`)

- `title`: `Hand-picked Products`
- `description`: `Display a selection of hand-picked products in a grid.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\HandpickedProducts::render_callback`
- `has_render_callback`: `true`

### Keywords

- `Handpicked Products`
- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "inserter": false
}
```

### Attributes

```json
{
    "align": {
        "type": "string"
    },
    "columns": {
        "type": "number",
        "default": 3
    },
    "contentVisibility": {
        "type": "object",
        "default": {
            "image": true,
            "title": true,
            "price": true,
            "rating": true,
            "button": true
        },
        "properties": {
            "image": {
                "type": "boolean",
                "image": true
            },
            "title": {
                "type": "boolean",
                "title": true
            },
            "price": {
                "type": "boolean",
                "price": true
            },
            "rating": {
                "type": "boolean",
                "rating": true
            },
            "button": {
                "type": "boolean",
                "button": true
            }
        }
    },
    "orderby": {
        "type": "string",
        "enum": [
            "date",
            "popularity",
            "price_asc",
            "price_desc",
            "rating",
            "title",
            "menu_order"
        ],
        "default": "date"
    },
    "products": {
        "type": "array",
        "default": []
    },
    "alignButtons": {
        "type": "boolean",
        "default": false
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-all-products`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-handpicked-products-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Mini-Cart (`woocommerce/mini-cart`)

- `title`: `Mini-Cart`
- `description`: `Display a button for shoppers to quickly view their cart.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `miniCartAlt`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\MiniCart::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `true`
- `color`: `false`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "multiple": false,
    "typography": {
        "fontSize": true
    },
    "spacing": {
        "margin": true,
        "padding": true
    },
    "interactivity": true
}
```

### Attributes

```json
{
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "miniCartIcon": {
        "type": "string",
        "default": "cart"
    },
    "addToCartBehaviour": {
        "type": "string",
        "default": "none"
    },
    "onCartClickBehaviour": {
        "type": "string",
        "default": "open_drawer"
    },
    "hasHiddenPrice": {
        "type": "boolean",
        "default": true
    },
    "cartAndCheckoutRenderStyle": {
        "type": "string",
        "default": "hidden"
    },
    "priceColor": {
        "type": "object"
    },
    "priceColorValue": {
        "type": "string"
    },
    "iconColor": {
        "type": "object"
    },
    "iconColorValue": {
        "type": "string"
    },
    "productCountColor": {
        "type": "object"
    },
    "productCountColorValue": {
        "type": "string"
    },
    "productCountVisibility": {
        "type": "string",
        "default": "greater_than_zero"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "fontSize": {
        "type": "string"
    }
}
```

### Example

```json
{
    "attributes": {
        "isPreview": true,
        "className": "wc-block-mini-cart--preview"
    }
}
```

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-mini-cart`
- `wc-blocks-packages-style`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-mini-cart-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Filter by Price Controls (`woocommerce/price-filter`)

- `title`: `Filter by Price Controls`
- `description`: `Enable customers to filter the product grid by choosing a price range.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\PriceFilter::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "html": false,
    "multiple": false,
    "color": {
        "text": true,
        "background": false
    },
    "inserter": false,
    "lock": false
}
```

### Attributes

```json
{
    "className": {
        "type": "string",
        "default": ""
    },
    "showInputFields": {
        "type": "boolean",
        "default": true
    },
    "inlineInput": {
        "type": "boolean",
        "default": false
    },
    "showFilterButton": {
        "type": "boolean",
        "default": false
    },
    "headingLevel": {
        "type": "number",
        "default": 3
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-price-filter`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-price-filter-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Best Selling Products (`woocommerce/product-best-sellers`)

- `title`: `Best Selling Products`
- `description`: `Display a grid of your all-time best selling products.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductBestSellers::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "inserter": false
}
```

### Attributes

```json
{
    "columns": {
        "type": "number",
        "default": 3
    },
    "rows": {
        "type": "number",
        "default": 3
    },
    "alignButtons": {
        "type": "boolean",
        "default": false
    },
    "contentVisibility": {
        "type": "object",
        "default": {
            "image": true,
            "title": true,
            "price": true,
            "rating": true,
            "button": true
        },
        "properties": {
            "image": {
                "type": "boolean",
                "default": true
            },
            "title": {
                "type": "boolean",
                "default": true
            },
            "price": {
                "type": "boolean",
                "default": true
            },
            "rating": {
                "type": "boolean",
                "default": true
            },
            "button": {
                "type": "boolean",
                "default": true
            }
        }
    },
    "categories": {
        "type": "array",
        "default": []
    },
    "catOperator": {
        "type": "string",
        "default": "any"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "stockStatus": {
        "type": "array"
    },
    "editMode": {
        "type": "boolean",
        "default": true
    },
    "orderby": {
        "type": "string",
        "enum": [
            "date",
            "popularity",
            "price_asc",
            "price_desc",
            "rating",
            "title",
            "menu_order"
        ],
        "default": "popularity"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-all-products`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-best-sellers-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Product Categories List (`woocommerce/product-categories`)

- `title`: `Product Categories List`
- `description`: `Show all product categories as a list or dropdown.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductCategories::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": true
    },
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "color": {
        "background": false,
        "link": true
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true
    }
}
```

### Attributes

```json
{
    "align": {
        "type": "string"
    },
    "hasCount": {
        "type": "boolean",
        "default": true
    },
    "hasImage": {
        "type": "boolean",
        "default": false
    },
    "hasEmpty": {
        "type": "boolean",
        "default": false
    },
    "isDropdown": {
        "type": "boolean",
        "default": false
    },
    "isHierarchical": {
        "type": "boolean",
        "default": true
    },
    "showChildrenOnly": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    }
}
```

### Example

```json
{
    "attributes": {
        "hasCount": true,
        "hasImage": false
    }
}
```

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-product-categories`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-categories-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Products by Category (`woocommerce/product-category`)

- `title`: `Products by Category`
- `description`: `Display a grid of products from your selected categories.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductCategory::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "inserter": false
}
```

### Attributes

```json
{
    "columns": {
        "type": "number",
        "default": 3
    },
    "rows": {
        "type": "number",
        "default": 3
    },
    "alignButtons": {
        "type": "boolean",
        "default": false
    },
    "contentVisibility": {
        "type": "object",
        "default": {
            "image": true,
            "title": true,
            "price": true,
            "rating": true,
            "button": true
        },
        "properties": {
            "image": {
                "type": "boolean",
                "default": true
            },
            "title": {
                "type": "boolean",
                "default": true
            },
            "price": {
                "type": "boolean",
                "default": true
            },
            "rating": {
                "type": "boolean",
                "default": true
            },
            "button": {
                "type": "boolean",
                "default": true
            }
        }
    },
    "categories": {
        "type": "array",
        "default": []
    },
    "catOperator": {
        "type": "string",
        "default": "any"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "stockStatus": {
        "type": "array"
    },
    "editMode": {
        "type": "boolean",
        "default": true
    },
    "orderby": {
        "type": "string",
        "enum": [
            "date",
            "popularity",
            "price_asc",
            "price_desc",
            "rating",
            "title",
            "menu_order"
        ],
        "default": "date"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-all-products`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-category-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Active Filters (`woocommerce/product-filter-active`)

- `title`: `Active Filters`
- `description`: `Display the currently active filters.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterActive::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- `woocommerce/product-filters`

### Uses Context

- `activeFilters`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `true`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": true,
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": false,
            "radius": false,
            "style": false,
            "width": false
        }
    },
    "spacing": {
        "margin": true,
        "padding": true,
        "blockGap": false,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false,
            "blockGap": false
        }
    }
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-active-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Attribute Filter (`woocommerce/product-filter-attribute`)

- `title`: `Attribute Filter`
- `description`: `Enable customers to filter the product grid by selecting one or more attributes, such as color.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterAttribute::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- `woocommerce/product-filters`

### Uses Context

- `query`
- `filterParams`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": true,
    "color": {
        "text": true,
        "background": false,
        "__experimentalDefaultControls": {
            "text": false
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontWeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": false
        }
    },
    "spacing": {
        "margin": true,
        "padding": true,
        "blockGap": true,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false,
            "blockGap": false
        }
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": false,
            "radius": false,
            "style": false,
            "width": false
        }
    }
}
```

### Attributes

```json
{
    "attributeId": {
        "type": "number",
        "default": 0
    },
    "showCounts": {
        "type": "boolean",
        "default": false
    },
    "queryType": {
        "type": "string",
        "default": "or"
    },
    "displayStyle": {
        "type": "string",
        "default": "woocommerce/product-filter-checkbox-list"
    },
    "selectType": {
        "type": "string",
        "default": "multiple"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "sortOrder": {
        "type": "string",
        "default": "count-desc"
    },
    "hideEmpty": {
        "type": "boolean",
        "default": true
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "attributes": {
        "isPreview": true
    }
}
```

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-product-filter-attribute`

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-attribute-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## List (`woocommerce/product-filter-checkbox-list`)

- `title`: `List`
- `description`: `Display a list of filter options.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterCheckboxList::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- `woocommerce/product-filter-attribute`
- `woocommerce/product-filter-status`
- `woocommerce/product-filter-taxonomy`
- `woocommerce/product-filter-rating`

### Uses Context

- `filterData`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": true
}
```

### Attributes

```json
{
    "optionElementBorder": {
        "type": "string",
        "default": ""
    },
    "customOptionElementBorder": {
        "type": "string",
        "default": ""
    },
    "optionElementSelected": {
        "type": "string",
        "default": ""
    },
    "customOptionElementSelected": {
        "type": "string",
        "default": ""
    },
    "optionElement": {
        "type": "string",
        "default": ""
    },
    "customOptionElement": {
        "type": "string",
        "default": ""
    },
    "labelElement": {
        "type": "string",
        "default": ""
    },
    "customLabelElement": {
        "type": "string",
        "default": ""
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `woocommerce-product-filter-checkbox-list-style`

### Editor Style Handles

- `woocommerce-product-filter-checkbox-list-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-checkbox-list-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Chips (`woocommerce/product-filter-chips`)

- `title`: `Chips`
- `description`: `Display filter options as chips.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterChips::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- `woocommerce/product-filter-attribute`
- `woocommerce/product-filter-taxonomy`
- `woocommerce/product-filter-status`

### Uses Context

- `filterData`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": true
}
```

### Attributes

```json
{
    "chipText": {
        "type": "string"
    },
    "customChipText": {
        "type": "string"
    },
    "chipBackground": {
        "type": "string"
    },
    "customChipBackground": {
        "type": "string"
    },
    "chipBorder": {
        "type": "string"
    },
    "customChipBorder": {
        "type": "string"
    },
    "selectedChipText": {
        "type": "string"
    },
    "customSelectedChipText": {
        "type": "string"
    },
    "selectedChipBackground": {
        "type": "string"
    },
    "customSelectedChipBackground": {
        "type": "string"
    },
    "selectedChipBorder": {
        "type": "string"
    },
    "customSelectedChipBorder": {
        "type": "string"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `woocommerce-product-filter-chips-style`

### Editor Style Handles

- `woocommerce-product-filter-chips-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-chips-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Clear filters (`woocommerce/product-filter-clear-button`)

- `title`: `Clear filters`
- `description`: `Allows shoppers to clear active filters.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterClearButton::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`
- `clear filters`

### Parent

- None

### Ancestor

- `woocommerce/product-filter-active`

### Uses Context

- `filterData`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": true,
    "inserter": true
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-product-filter-clear-button`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-clear-button-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Price Filter (`woocommerce/product-filter-price`)

- `title`: `Price Filter`
- `description`: `Let shoppers filter products by choosing a price range.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterPrice::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- `woocommerce/product-filters`

### Uses Context

- `query`
- `filterParams`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": true,
    "html": false
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- None

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-price-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Price Slider (`woocommerce/product-filter-price-slider`)

- `title`: `Price Slider`
- `description`: `A slider helps shopper choose a price range.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterPriceSlider::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- `woocommerce/product-filter-price`

### Uses Context

- `filterData`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "color": {
        "enableContrastChecker": false,
        "background": false,
        "text": false
    },
    "interactivity": true
}
```

### Attributes

```json
{
    "showInputFields": {
        "type": "boolean",
        "default": true
    },
    "inlineInput": {
        "type": "boolean",
        "default": false
    },
    "sliderHandle": {
        "type": "string",
        "default": ""
    },
    "customSliderHandle": {
        "type": "string",
        "default": ""
    },
    "sliderHandleBorder": {
        "type": "string",
        "default": ""
    },
    "customSliderHandleBorder": {
        "type": "string",
        "default": ""
    },
    "slider": {
        "type": "string",
        "default": ""
    },
    "customSlider": {
        "type": "string",
        "default": ""
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `woocommerce-product-filter-price-slider-style`

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-price-slider-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Rating Filter (`woocommerce/product-filter-rating`)

- `title`: `Rating Filter`
- `description`: `Enable customers to filter the product collection by rating.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterRating::render_callback`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- `woocommerce/product-filters`

### Uses Context

- `query`
- `filterParams`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": true,
    "color": {
        "background": false,
        "text": true
    }
}
```

### Attributes

```json
{
    "className": {
        "type": "string",
        "default": ""
    },
    "showCounts": {
        "type": "boolean",
        "default": false
    },
    "minRating": {
        "type": "string",
        "default": "0"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-product-filter-rating`

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-rating-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Chips (`woocommerce/product-filter-removable-chips`)

- `title`: `Chips`
- `description`: `Display removable active filters as chips.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterRemovableChips::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- `woocommerce/product-filter-active`

### Uses Context

- `queryId`
- `filterData`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "layout": {
        "allowSwitching": false,
        "allowInheriting": false,
        "allowVerticalAlignment": false,
        "default": {
            "type": "flex"
        }
    },
    "interactivity": true
}
```

### Attributes

```json
{
    "chipText": {
        "type": "string"
    },
    "customChipText": {
        "type": "string"
    },
    "chipBackground": {
        "type": "string"
    },
    "customChipBackground": {
        "type": "string"
    },
    "chipBorder": {
        "type": "string"
    },
    "customChipBorder": {
        "type": "string"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `woocommerce-product-filter-removable-chips-style`

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-removable-chips-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Status Filter (`woocommerce/product-filter-status`)

- `title`: `Status Filter`
- `description`: `Let shoppers filter products by choosing stock status.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterStatus::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- `woocommerce/product-filters`

### Uses Context

- `query`
- `filterParams`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": true,
    "html": false,
    "color": {
        "text": true,
        "background": false,
        "__experimentalDefaultControls": {
            "text": false
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontWeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": false
        }
    },
    "spacing": {
        "margin": true,
        "padding": true,
        "blockGap": true,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false,
            "blockGap": false
        }
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": false,
            "radius": false,
            "style": false,
            "width": false
        }
    }
}
```

### Attributes

```json
{
    "showCounts": {
        "type": "boolean",
        "default": false
    },
    "displayStyle": {
        "type": "string",
        "default": "woocommerce/product-filter-checkbox-list"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "hideEmpty": {
        "type": "boolean",
        "default": true
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "attributes": {
        "isPreview": true
    }
}
```

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-product-filter-status`

### Editor Style Handles

- None

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-status-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Taxonomy Filter (`woocommerce/product-filter-taxonomy`)

- `title`: `Taxonomy Filter`
- `description`: `Enable customers to filter the product collection by selecting one or more taxonomy terms, such as categories, brands, or tags.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilterTaxonomy::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- `woocommerce/product-filters`

### Uses Context

- `query`
- `filterParams`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": true,
    "color": {
        "text": true,
        "background": false,
        "__experimentalDefaultControls": {
            "text": false
        }
    },
    "typography": {
        "fontSize": true,
        "lineHeight": true,
        "__experimentalFontWeight": true,
        "__experimentalFontFamily": true,
        "__experimentalFontStyle": true,
        "__experimentalTextTransform": true,
        "__experimentalTextDecoration": true,
        "__experimentalLetterSpacing": true,
        "__experimentalDefaultControls": {
            "fontSize": false
        }
    },
    "spacing": {
        "margin": true,
        "padding": true,
        "blockGap": true,
        "__experimentalDefaultControls": {
            "margin": false,
            "padding": false,
            "blockGap": false
        }
    },
    "__experimentalBorder": {
        "color": true,
        "radius": true,
        "style": true,
        "width": true,
        "__experimentalDefaultControls": {
            "color": false,
            "radius": false,
            "style": false,
            "width": false
        }
    }
}
```

### Attributes

```json
{
    "taxonomy": {
        "type": "string",
        "default": "product_cat"
    },
    "showCounts": {
        "type": "boolean",
        "default": false
    },
    "displayStyle": {
        "type": "string",
        "default": "woocommerce/product-filter-checkbox-list"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "sortOrder": {
        "type": "string",
        "default": "count-desc"
    },
    "hideEmpty": {
        "type": "boolean",
        "default": true
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "fontFamily": {
        "type": "string"
    },
    "borderColor": {
        "type": "string"
    }
}
```

### Example

```json
{
    "attributes": {
        "isPreview": true
    }
}
```

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-product-filter-taxonomy`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-filter-taxonomy-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Product Filters (`woocommerce/product-filters`)

- `title`: `Product Filters`
- `description`: `Let shoppers filter products displayed on the page.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductFilters::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- `postId`
- `query`
- `queryId`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `true`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `true`
- `reusable`: `false`

### Supports

```json
{
    "align": true,
    "color": {
        "background": true,
        "text": true,
        "heading": true,
        "enableContrastChecker": false,
        "button": true
    },
    "multiple": true,
    "inserter": true,
    "interactivity": true,
    "typography": {
        "fontSize": true
    },
    "layout": {
        "default": {
            "type": "flex",
            "orientation": "vertical",
            "flexWrap": "nowrap",
            "justifyContent": "stretch"
        },
        "allowEditing": false
    },
    "spacing": {
        "blockGap": true
    }
}
```

### Attributes

```json
{
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    },
    "layout": {
        "type": "object"
    }
}
```

### Example

```json
{
    "attributes": {
        "isPreview": true
    }
}
```

### Selectors

- None

### Style Handles

- `woocommerce-product-filters-style`

### Editor Style Handles

- `woocommerce-product-filters-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-filters-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Newest Products (`woocommerce/product-new`)

- `title`: `Newest Products`
- `description`: `Display a grid of your newest products.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductNew::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "inserter": false
}
```

### Attributes

```json
{
    "columns": {
        "type": "number",
        "default": 3
    },
    "rows": {
        "type": "number",
        "default": 3
    },
    "alignButtons": {
        "type": "boolean",
        "default": false
    },
    "contentVisibility": {
        "type": "object",
        "default": {
            "image": true,
            "title": true,
            "price": true,
            "rating": true,
            "button": true
        },
        "properties": {
            "image": {
                "type": "boolean",
                "default": true
            },
            "title": {
                "type": "boolean",
                "default": true
            },
            "price": {
                "type": "boolean",
                "default": true
            },
            "rating": {
                "type": "boolean",
                "default": true
            },
            "button": {
                "type": "boolean",
                "default": true
            }
        }
    },
    "categories": {
        "type": "array",
        "default": []
    },
    "catOperator": {
        "type": "string",
        "default": "any"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "stockStatus": {
        "type": "array"
    },
    "editMode": {
        "type": "boolean",
        "default": true
    },
    "orderby": {
        "type": "string",
        "enum": [
            "date",
            "popularity",
            "price_asc",
            "price_desc",
            "rating",
            "title",
            "menu_order"
        ],
        "default": "date"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-all-products`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-new-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## On Sale Products (`woocommerce/product-on-sale`)

- `title`: `On Sale Products`
- `description`: `Display a grid of products currently on sale.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductOnSale::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "inserter": false
}
```

### Attributes

```json
{
    "columns": {
        "type": "number",
        "default": 3
    },
    "rows": {
        "type": "number",
        "default": 3
    },
    "alignButtons": {
        "type": "boolean",
        "default": false
    },
    "categories": {
        "type": "array",
        "default": []
    },
    "catOperator": {
        "type": "string",
        "default": "any"
    },
    "contentVisibility": {
        "type": "object",
        "default": {
            "image": true,
            "title": true,
            "price": true,
            "rating": true,
            "button": true
        },
        "properties": {
            "image": {
                "type": "boolean",
                "default": true
            },
            "title": {
                "type": "boolean",
                "default": true
            },
            "price": {
                "type": "boolean",
                "default": true
            },
            "rating": {
                "type": "boolean",
                "default": true
            },
            "button": {
                "type": "boolean",
                "default": true
            }
        }
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "stockStatus": {
        "type": "array"
    },
    "orderby": {
        "type": "string",
        "default": "date"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-all-products`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-on-sale-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Product Results Count (`woocommerce/product-results-count`)

- `title`: `Product Results Count`
- `description`: `Display the number of products on the archive page or search result page.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductResultsCount::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- `queryId`

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": true
    },
    "color": {
        "text": true,
        "background": false
    },
    "typography": {
        "fontSize": true
    }
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-product-results-count`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-results-count-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Untitled (`woocommerce/product-search`)

- `title`: `null`
- `description`: `null`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `1`
- `category`: `null`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductSearch::render_callback`
- `has_render_callback`: `true`

### Keywords

- None

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

- None

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-product-search`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-search-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Products by Tag (`woocommerce/product-tag`)

- `title`: `Products by Tag`
- `description`: `Display a grid of products with selected tags.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductTag::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "inserter": false
}
```

### Attributes

```json
{
    "columns": {
        "type": "number",
        "default": 3
    },
    "rows": {
        "type": "number",
        "default": 3
    },
    "alignButtons": {
        "type": "boolean",
        "default": false
    },
    "contentVisibility": {
        "type": "object",
        "default": {
            "image": true,
            "title": true,
            "price": true,
            "rating": true,
            "button": true
        },
        "properties": {
            "image": {
                "type": "boolean",
                "default": true
            },
            "title": {
                "type": "boolean",
                "default": true
            },
            "price": {
                "type": "boolean",
                "default": true
            },
            "rating": {
                "type": "boolean",
                "default": true
            },
            "button": {
                "type": "boolean",
                "default": true
            }
        }
    },
    "tags": {
        "type": "array",
        "default": []
    },
    "tagOperator": {
        "type": "string",
        "default": "any"
    },
    "orderby": {
        "type": "string",
        "default": "date"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "stockStatus": {
        "type": "array"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-all-products`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-tag-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Top Rated Products (`woocommerce/product-top-rated`)

- `title`: `Top Rated Products`
- `description`: `Display a grid of your top rated products.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductTopRated::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "inserter": false
}
```

### Attributes

```json
{
    "columns": {
        "type": "number",
        "default": 3
    },
    "rows": {
        "type": "number",
        "default": 3
    },
    "alignButtons": {
        "type": "boolean",
        "default": false
    },
    "contentVisibility": {
        "type": "object",
        "default": {
            "image": true,
            "title": true,
            "price": true,
            "rating": true,
            "button": true
        },
        "properties": {
            "image": {
                "type": "boolean",
                "default": true
            },
            "title": {
                "type": "boolean",
                "default": true
            },
            "price": {
                "type": "boolean",
                "default": true
            },
            "rating": {
                "type": "boolean",
                "default": true
            },
            "button": {
                "type": "boolean",
                "default": true
            }
        }
    },
    "categories": {
        "type": "array",
        "default": []
    },
    "catOperator": {
        "type": "string",
        "default": "any"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "stockStatus": {
        "type": "array"
    },
    "editMode": {
        "type": "boolean",
        "default": true
    },
    "orderby": {
        "type": "string",
        "enum": [
            "date",
            "popularity",
            "price_asc",
            "price_desc",
            "rating",
            "title",
            "menu_order"
        ],
        "default": "rating"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-all-products`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-product-top-rated-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Products by Attribute (`woocommerce/products-by-attribute`)

- `title`: `Products by Attribute`
- `description`: `Display a grid of products with selected attributes.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ProductsByAttribute::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `true`
- `spacing`: `false`
- `color`: `false`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "align": [
        "wide",
        "full"
    ],
    "html": false,
    "inserter": false
}
```

### Attributes

```json
{
    "attributes": {
        "type": "array",
        "default": []
    },
    "attrOperator": {
        "type": "string",
        "enum": [
            "all",
            "any"
        ],
        "default": "any"
    },
    "columns": {
        "type": "number",
        "default": 3
    },
    "contentVisibility": {
        "type": "object",
        "default": {
            "image": true,
            "title": true,
            "price": true,
            "rating": true,
            "button": true
        },
        "properties": {
            "image": {
                "type": "boolean",
                "default": true
            },
            "title": {
                "type": "boolean",
                "default": true
            },
            "price": {
                "type": "boolean",
                "default": true
            },
            "rating": {
                "type": "boolean",
                "default": true
            },
            "button": {
                "type": "boolean",
                "default": true
            }
        }
    },
    "orderby": {
        "type": "string",
        "enum": [
            "date",
            "popularity",
            "price_asc",
            "price_desc",
            "rating",
            "title",
            "menu_order"
        ],
        "default": "date"
    },
    "rows": {
        "type": "number",
        "default": 3
    },
    "alignButtons": {
        "type": "boolean",
        "default": false
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "stockStatus": {
        "type": "array"
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "align": {
        "type": "string",
        "enum": [
            "left",
            "center",
            "right",
            "wide",
            "full",
            ""
        ]
    },
    "className": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-all-products`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-products-by-attribute-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Filter by Rating Controls (`woocommerce/rating-filter`)

- `title`: `Filter by Rating Controls`
- `description`: `Enable customers to filter the product grid by rating.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\RatingFilter::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "html": false,
    "multiple": false,
    "color": {
        "background": true,
        "text": true,
        "button": true
    },
    "inserter": false,
    "lock": false
}
```

### Attributes

```json
{
    "className": {
        "type": "string",
        "default": ""
    },
    "showCounts": {
        "type": "boolean",
        "default": false
    },
    "displayStyle": {
        "type": "string",
        "default": "list"
    },
    "showFilterButton": {
        "type": "boolean",
        "default": false
    },
    "selectType": {
        "type": "string",
        "default": "multiple"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-rating-filter`
- `wc-blocks-packages-style`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-rating-filter-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Reviews by Category (`woocommerce/reviews-by-category`)

- `title`: `Reviews by Category`
- `description`: `Show product reviews from specific categories.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ReviewsByCategory::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "interactivity": {
        "clientNavigation": true
    },
    "color": {
        "background": false
    },
    "typography": {
        "fontSize": true
    }
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-reviews-by-category`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-reviews-by-category-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Reviews by Product (`woocommerce/reviews-by-product`)

- `title`: `Reviews by Product`
- `description`: `Display reviews for your products.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\ReviewsByProduct::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `true`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "html": false,
    "interactivity": {
        "clientNavigation": true
    },
    "color": {
        "background": false
    },
    "typography": {
        "fontSize": true
    }
}
```

### Attributes

```json
{
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "className": {
        "type": "string"
    },
    "style": {
        "type": "object"
    },
    "textColor": {
        "type": "string"
    },
    "fontSize": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-reviews-by-product`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-reviews-by-product-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

## Filter by Stock Controls (`woocommerce/stock-filter`)

- `title`: `Filter by Stock Controls`
- `description`: `Enable customers to filter the product grid by stock status.`
- `origin`: `woocommerce`
- `category_bucket`: `woocommerce`
- `currently_allowed`: `true`
- `currently_blacklisted`: `false`
- `is_dynamic`: `true`
- `api_version`: `3`
- `category`: `woocommerce`
- `icon`: `null`
- `render_callback`: `Automattic\WooCommerce\Blocks\BlockTypes\StockFilter::render_callback`
- `has_render_callback`: `true`

### Keywords

- `WooCommerce`

### Parent

- None

### Ancestor

- None

### Uses Context

- None

### Provides Context

- None

### Supports Summary

- `anchor`: `false`
- `align`: `false`
- `spacing`: `false`
- `color`: `true`
- `typography`: `false`
- `html`: `false`
- `multiple`: `false`
- `reusable`: `false`

### Supports

```json
{
    "interactivity": {
        "clientNavigation": false
    },
    "html": false,
    "multiple": false,
    "color": {
        "background": true,
        "text": true,
        "button": true
    },
    "inserter": false,
    "lock": false
}
```

### Attributes

```json
{
    "className": {
        "type": "string",
        "default": ""
    },
    "headingLevel": {
        "type": "number",
        "default": 3
    },
    "showCounts": {
        "type": "boolean",
        "default": false
    },
    "showFilterButton": {
        "type": "boolean",
        "default": false
    },
    "displayStyle": {
        "type": "string",
        "default": "list"
    },
    "selectType": {
        "type": "string",
        "default": "multiple"
    },
    "isPreview": {
        "type": "boolean",
        "default": false
    },
    "lock": {
        "type": "object"
    },
    "metadata": {
        "type": "object"
    },
    "style": {
        "type": "object"
    },
    "backgroundColor": {
        "type": "string"
    },
    "textColor": {
        "type": "string"
    }
}
```

### Example

- None

### Selectors

- None

### Style Handles

- `wc-blocks-style`
- `wc-blocks-style-stock-filter`
- `wc-blocks-packages-style`

### Editor Style Handles

- `wc-blocks-editor-style`

### Script Handles

- None

### Editor Script Handles

- `wc-stock-filter-block`

### View Script Handles

- None

### View Style Handles

- None

### Custom Metadata

- None

