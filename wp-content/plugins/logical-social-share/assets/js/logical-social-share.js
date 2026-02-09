document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (event) {
        const mastodonAction = event.target.closest('.sss-mastodon-action');
        if (mastodonAction) {
            event.preventDefault();
            const instance = prompt(
                'Tuo server Mastodon (es. mastodon.social):',
                'mastodon.social'
            );
            if (instance) {
                const url = `https://${instance}/share?text=${encodeURIComponent(
                    mastodonAction.dataset.title
                )}%20${encodeURIComponent(mastodonAction.dataset.url)}`;
                window.open(url, '_blank');
            }
            return;
        }

        const copyAction = event.target.closest('.sss-copy-action');
        if (copyAction) {
            event.preventDefault();
            navigator.clipboard
                .writeText(copyAction.dataset.url)
                .then(() => alert('Link copiato!'))
                .catch(() => prompt('Copia manualmente:', copyAction.dataset.url));
        }
    });
});
