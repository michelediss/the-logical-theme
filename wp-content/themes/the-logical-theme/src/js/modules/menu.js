const MENU_READY_ATTR = 'data-menu-ready';

function isElementVisible(element) {
  return Boolean(element) && !element.hidden && window.getComputedStyle(element).display !== 'none';
}

function isNavigationOpen(navigation) {
  const openButton = navigation.querySelector('.wp-block-navigation__responsive-container-open');
  const responsiveContainer = navigation.querySelector('.wp-block-navigation__responsive-container');

  if (openButton?.getAttribute('aria-expanded') === 'true') {
    return true;
  }

  if (navigation.classList.contains('has-modal-open') || responsiveContainer?.classList.contains('is-menu-open')) {
    return true;
  }

  if (!responsiveContainer) {
    return false;
  }

  if (responsiveContainer.getAttribute('aria-hidden') === 'false') {
    return true;
  }

  return isElementVisible(responsiveContainer) && responsiveContainer.querySelector('.wp-block-navigation__responsive-container-close');
}

export function initMenuReveal() {
  const navigations = Array.from(document.querySelectorAll('.wp-block-navigation')).filter(
    (navigation) => !navigation.hasAttribute(MENU_READY_ATTR)
  );

  if (!navigations.length) {
    return;
  }

  const syncMenuState = () => {
    const isOpen = Array.from(document.querySelectorAll('.wp-block-navigation')).some((navigation) => isNavigationOpen(navigation));
    document.documentElement.classList.toggle('menu-is-open', isOpen);
  };

  navigations.forEach((navigation) => {
    navigation.setAttribute(MENU_READY_ATTR, 'true');

    const syncOnNextFrame = () => {
      window.requestAnimationFrame(syncMenuState);
    };

    navigation.addEventListener('click', (event) => {
      if (!(event.target instanceof Element)) {
        return;
      }

      if (
        event.target.closest('.wp-block-navigation__responsive-container-open, .wp-block-navigation__responsive-container-close')
      ) {
        syncOnNextFrame();
      }
    });

    navigation.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        syncOnNextFrame();
      }
    });

    const responsiveContainer = navigation.querySelector('.wp-block-navigation__responsive-container');

    if (responsiveContainer) {
      const observer = new MutationObserver(syncMenuState);
      observer.observe(responsiveContainer, {
        attributes: true,
        attributeFilter: ['class', 'hidden', 'style', 'aria-hidden'],
      });
    }
  });

  window.addEventListener('resize', syncMenuState, { passive: true });
  syncMenuState();
}
