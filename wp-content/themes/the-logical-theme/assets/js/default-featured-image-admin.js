(function ($) {
    function initDefaultFeaturedImagePicker() {
        const selectButton = $('#tlt-default-featured-image-select');
        const removeButton = $('#tlt-default-featured-image-remove');
        const imageInput = $('#tlt-default-featured-image-id');
        const preview = $('.tlt-default-featured-image-preview');

        if (!selectButton.length) {
            return;
        }

        let mediaFrame;

        const labels = window.theLogicalThemeDefaultFeaturedImage || {};

        const escapeHtml = (text) =>
            String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

        const renderPlaceholder = () => {
            preview.html(
                '<div class="tlt-default-featured-image-placeholder">' +
                    (labels.placeholderText || '') +
                    '</div>'
            );
        };

        if (!imageInput.val()) {
            renderPlaceholder();
        }

        selectButton.on('click', (event) => {
            event.preventDefault();

            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = wp.media({
                title: labels.frameTitle || '',
                button: {
                    text: labels.chooseButton || '',
                },
                library: { type: 'image' },
                multiple: false,
            });

            mediaFrame.on('select', () => {
                const attachment = mediaFrame.state().get('selection').first();

                if (!attachment) {
                    return;
                }

                const details = attachment.toJSON();
                const imageUrl =
                    (details.sizes && details.sizes.medium && details.sizes.medium.url) ||
                    details.url;
                const imageAlt = details.alt || details.title || '';

                imageInput.val(details.id);
                preview.html('<img src="' + imageUrl + '" alt="' + escapeHtml(imageAlt) + '" />');
                removeButton.prop('disabled', false);
            });

            mediaFrame.open();
        });

        removeButton.on('click', (event) => {
            event.preventDefault();

            if (removeButton.prop('disabled')) {
                return;
            }

            if (labels.removeConfirm && !window.confirm(labels.removeConfirm)) {
                return;
            }

            imageInput.val('');
            removeButton.prop('disabled', true);
            renderPlaceholder();
        });
    }

    $(initDefaultFeaturedImagePicker);
})(jQuery);
