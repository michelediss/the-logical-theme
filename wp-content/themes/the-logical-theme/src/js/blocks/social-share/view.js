function handleMastodonShare(trigger) {
  const instance = window.prompt(
    'Tuo server Mastodon (es. mastodon.social):',
    'mastodon.social'
  );

  if (!instance) {
    return;
  }

  const title = trigger.dataset.socialTitle || '';
  const url = trigger.dataset.socialUrl || '';
  const shareUrl = `https://${instance}/share?text=${encodeURIComponent(title)}%20${encodeURIComponent(url)}`;

  window.open(shareUrl, '_blank', 'noopener,noreferrer');
}

function handleCopy(trigger) {
  const url = trigger.dataset.socialUrl || '';

  navigator.clipboard
    .writeText(url)
    .then(() => {
      window.alert('Link copiato!');
    })
    .catch(() => {
      window.prompt('Copia manualmente:', url);
    });
}

function onActionClick(event) {
  const trigger = event.target.closest('[data-social-action]');

  if (!trigger) {
    return;
  }

  event.preventDefault();

  if (trigger.dataset.socialAction === 'mastodon') {
    handleMastodonShare(trigger);
    return;
  }

  if (trigger.dataset.socialAction === 'copy') {
    handleCopy(trigger);
  }
}

document.addEventListener('click', onActionClick);
