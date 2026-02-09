(function ($) {
    function initDefaultFeaturedImagePicker() {
        const selectButton = $('#pap-default-featured-image-select');
        const removeButton = $('#pap-default-featured-image-remove');
        const imageInput = $('#pap-default-featured-image-id');
        const preview = $('.pap-default-featured-image-preview');
        if (!selectButton.length) {
            return;
        }

        let mediaFrame;

        const escapeHtml = (text) =>
            String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

        const renderPlaceholder = () => {
            preview.html(
                '<div class="pap-default-featured-image-placeholder">' +
                    (papDefaultFeaturedImage?.placeholderText || '') +
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
                title: papDefaultFeaturedImage?.frameTitle || '',
                button: {
                    text: papDefaultFeaturedImage?.chooseButton || '',
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

            const confirmMessage = papDefaultFeaturedImage?.removeConfirm || '';
            if (confirmMessage && !window.confirm(confirmMessage)) {
                return;
            }

            imageInput.val('');
            removeButton.prop('disabled', true);
            renderPlaceholder();
        });
    }

    $(initDefaultFeaturedImagePicker);
})(jQuery);
