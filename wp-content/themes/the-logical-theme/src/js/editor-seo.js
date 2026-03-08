(function (wp, config) {
  if (!wp?.plugins || !wp?.editPost || !wp?.components || !wp?.data || !wp?.element || !wp?.i18n) {
    return;
  }

  const { registerPlugin } = wp.plugins;
  const { PluginDocumentSettingPanel } = wp.editPost;
  const { TextControl, TextareaControl, ToggleControl, ExternalLink } = wp.components;
  const { createElement: el, Fragment } = wp.element;
  const { useDispatch, useSelect } = wp.data;
  const { __ } = wp.i18n;

  function stripHtml(value) {
    return String(value || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
  }

  function trimWords(value, limit) {
    const words = stripHtml(value).split(' ').filter(Boolean);
    return words.slice(0, limit).join(' ');
  }

  function normalizeFieldValue(value) {
    if (value && typeof value === 'object') {
      return value.raw || value.rendered || '';
    }

    return String(value || '');
  }

  function buildDefaultTitle(postTitle) {
    const cleanTitle = String(postTitle || '').trim();
    return cleanTitle ? `${cleanTitle} - ${config.siteName}` : config.siteName;
  }

  function buildDefaultDescription(excerpt, content) {
    const description = trimWords(excerpt || content, 30);
    return description || String(config.siteDescription || '');
  }

  function SeoPanel() {
    const { editPost } = useDispatch('core/editor');

    const editorData = useSelect((select) => {
      const editorStore = select('core/editor');
      const meta = editorStore.getEditedPostAttribute('meta') || {};
      const title = normalizeFieldValue(editorStore.getEditedPostAttribute('title') || editorStore.getCurrentPostAttribute('title'));
      const excerpt = normalizeFieldValue(editorStore.getEditedPostAttribute('excerpt') || editorStore.getCurrentPostAttribute('excerpt'));
      const content = editorStore.getEditedPostContent() || '';
      const permalink = editorStore.getPermalink() || config.homeUrl;

      return { meta, title, excerpt, content, permalink };
    }, []);

    const meta = editorData.meta;
    const defaultTitle = buildDefaultTitle(editorData.title);
    const defaultDescription = buildDefaultDescription(editorData.excerpt, editorData.content);
    const previewTitle = String(meta._seo_title || defaultTitle).trim() || defaultTitle;
    const previewDescription = String(meta._seo_description || defaultDescription).trim() || defaultDescription;

    function updateMeta(key, value) {
      editPost({
        meta: {
          ...meta,
          [key]: value,
        },
      });
    }

    return el(
      PluginDocumentSettingPanel,
      {
        name: 'the-logical-theme-seo-panel',
        title: __('SEO', 'the-logical-theme'),
        icon: 'search',
      },
      el(
        'div',
        {
          style: {
            border: '1px solid #dcdcde',
            borderRadius: '6px',
            padding: '12px',
            backgroundColor: '#fff',
            marginBottom: '16px',
          },
        },
        el(
          'div',
          {
            style: {
              fontSize: '12px',
              color: '#50575e',
              marginBottom: '4px',
            },
          },
          config.siteName
        ),
        el(
          'div',
          {
            style: {
              fontSize: '12px',
              color: '#646970',
              marginBottom: '8px',
              wordBreak: 'break-word',
            },
          },
          editorData.permalink
        ),
        el(
          'div',
          {
            style: {
              fontSize: '18px',
              lineHeight: '1.4',
              color: '#1a0dab',
              marginBottom: '6px',
            },
          },
          previewTitle
        ),
        el(
          'div',
          {
            style: {
              fontSize: '13px',
              lineHeight: '1.5',
              color: '#3c434a',
            },
          },
          previewDescription
        )
      ),
      el(TextControl, {
        label: __('SEO title', 'the-logical-theme'),
        help: __('If empty, the post title and site name are used.', 'the-logical-theme'),
        value: meta._seo_title || '',
        onChange: (value) => updateMeta('_seo_title', value),
      }),
      el(TextareaControl, {
        label: __('Meta description', 'the-logical-theme'),
        help: __('If empty, the excerpt or content summary is used.', 'the-logical-theme'),
        value: meta._seo_description || '',
        onChange: (value) => updateMeta('_seo_description', value),
      }),
      el(ToggleControl, {
        label: __('Noindex', 'the-logical-theme'),
        checked: !!meta._seo_noindex,
        onChange: (value) => updateMeta('_seo_noindex', !!value),
      }),
      el(ToggleControl, {
        label: __('Nofollow', 'the-logical-theme'),
        checked: !!meta._seo_nofollow,
        onChange: (value) => updateMeta('_seo_nofollow', !!value),
      }),
      el(
        Fragment,
        null,
        el(
          'p',
          {
            style: {
              fontSize: '12px',
              color: '#646970',
              marginTop: '12px',
            },
          },
          __('Global robots and sitemap settings remain available in Settings > SEO.', 'the-logical-theme')
        ),
        el(
          ExternalLink,
          { href: `${config.homeUrl.replace(/\/$/, '')}/wp-admin/options-general.php?page=the-logical-theme-seo` },
          __('Open SEO settings', 'the-logical-theme')
        )
      )
    );
  }

  registerPlugin('the-logical-theme-seo', {
    render: SeoPanel,
  });
})(window.wp, window.theLogicalThemeSeoEditor || {});
