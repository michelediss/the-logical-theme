# CF7 Sync

`cf7-sync` is a small WordPress plugin that synchronizes Contact Form 7 forms from versioned JSON manifests through WP-CLI.

## Purpose

- Keep form definitions in source control instead of editing them only in the WordPress admin.
- Create missing Contact Form 7 forms from manifests.
- Update existing forms only when the stored CF7 configuration differs from the manifest snapshot.
- Support safe previews through a dry-run mode.

## Requirements

- WordPress with WP-CLI available.
- Contact Form 7 installed and active.
- One JSON manifest per form.

## CLI Command

The plugin registers this command:

```bash
wp cf7 sync
```

Supported options:

- `--dir=<path>`: manifest directory. Relative paths are resolved from WordPress `ABSPATH`.
- `--slug=<slug>`: sync only one manifest identified by slug.
- `--dry-run`: preview actions without writing to the database.

Examples:

```bash
wp cf7 sync --dir=wp-content/themes/the-logical-theme/cf7-forms
wp cf7 sync --slug=contatti --dry-run
```

If `--dir` is omitted, the default directory is:

```text
wp-content/themes/the-logical-theme/cf7-forms
```

## How Matching Works

- Each manifest must define a unique `slug`.
- The plugin stores that slug in the CF7 post meta key `_cf7_sync_slug`.
- On sync, the plugin looks up an existing `wpcf7_contact_form` post by that meta value.
- If no matching form exists, it creates one.
- If a matching form exists, it compares the normalized current snapshot against the manifest snapshot and updates only when something changed.

## Manifest Format

Each manifest must decode to a JSON object and include at least:

- `slug`
- `title`
- `form`
- `mail`

Optional top-level properties:

- `status`
- `locale`
- `mail_2`
- `messages`
- `additional_settings`

Minimal example:

```json
{
  "slug": "contact",
  "title": "Contact",
  "status": "publish",
  "locale": "en_US",
  "form": "[text* your-name] [email* your-email] [textarea your-message]",
  "mail": {
    "active": true,
    "subject": "[your-name] contact request",
    "sender": "WordPress <wordpress@example.com>",
    "recipient": "hello@example.com",
    "body": "Name: [your-name]\nEmail: [your-email]\n\n[your-message]",
    "additional_headers": "Reply-To: [your-email]",
    "attachments": "",
    "use_html": false,
    "exclude_blank": false
  },
  "mail_2": {
    "active": false,
    "subject": "",
    "sender": "",
    "recipient": "",
    "body": "",
    "additional_headers": "",
    "attachments": "",
    "use_html": false,
    "exclude_blank": false
  },
  "messages": {
    "mail_sent_ok": "Thank you for your message."
  },
  "additional_settings": ""
}
```

## Validation Rules

- `slug` is sanitized with `sanitize_key()` and must remain non-empty.
- `title` must be present.
- `form` must be present and non-empty after trimming.
- `mail` must be an object and must include non-empty `subject`, `sender`, `recipient`, and `body`.
- `mail_2`, when present, must also be an object.
- Duplicate slugs across manifest files cause the command to stop with an error.
- Invalid JSON or unreadable files stop the command with an error.

## Sync Output

The command prints a WP-CLI table with:

- `slug`
- `post_id`
- `action`
- `title`

Actions are:

- `create`
- `update`
- `no-change`

At the end, the command prints a summary with created, updated, and unchanged counts.

## Compatibility Notes

- When available, the plugin uses Contact Form 7 object methods such as `set_properties()` and `set_status()`.
- For older or different runtime behavior, it falls back to updating CF7 post meta directly.
- The plugin is intentionally CLI-only. It does not register admin UI screens or automatic background sync.
