import { gsap } from 'gsap';

const READY_ATTR = 'data-simple-cookie-consent-motion-ready';

function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function getBannerElements() {
  const banner = document.getElementById('simple-cookie-consent-banner');

  if (!banner) {
    return null;
  }

  return {
    banner,
    card: banner.querySelector('.scc-banner__card'),
    settings: banner.querySelector('#simple-cookie-consent-settings'),
    settingsRows: banner.querySelectorAll('.scc-banner__setting'),
    settingsFooter: banner.querySelector('.scc-banner__settings-footer'),
  };
}

function clearBannerStyles(elements) {
  if (!elements) {
    return;
  }

  gsap.set([elements.banner, elements.card, elements.settings, elements.settingsRows], {
    clearProps: 'opacity,visibility,transform,height,pointerEvents',
  });
}

function animateBannerIn(elements, openSettings) {
  if (!elements || !elements.card) {
    return;
  }

  if (prefersReducedMotion()) {
    clearBannerStyles(elements);
    return;
  }

  gsap.killTweensOf([elements.banner, elements.card, elements.settings, elements.settingsRows]);
  gsap.set(elements.banner, { autoAlpha: 1 });

  const timeline = gsap.timeline({
    defaults: {
      ease: 'power2.out',
      overwrite: 'auto',
    },
  });

  timeline.fromTo(
    elements.banner,
    { autoAlpha: 0 },
    { autoAlpha: 1, duration: 0.2 },
    0
  );

  timeline.fromTo(
    elements.card,
    { autoAlpha: 0, y: 28 },
    { autoAlpha: 1, y: 0, duration: 0.42, clearProps: 'opacity,visibility,transform' },
    0
  );

  if (openSettings && elements.settings && !elements.settings.classList.contains('scc-hidden')) {
    timeline.fromTo(
      [elements.settingsRows, elements.settingsFooter].filter(Boolean),
      { autoAlpha: 0, y: 12 },
      {
        autoAlpha: 1,
        y: 0,
        duration: 0.28,
        stagger: 0.035,
        clearProps: 'opacity,visibility,transform',
      },
      0.12
    );
  }
}

function animateBannerOut(elements, finish) {
  if (!elements || !elements.card) {
    finish();
    return;
  }

  if (prefersReducedMotion()) {
    finish();
    clearBannerStyles(elements);
    return;
  }

  gsap.killTweensOf([elements.banner, elements.card, elements.settings, elements.settingsRows]);

  gsap.timeline({
    defaults: {
      overwrite: 'auto',
    },
    onComplete: () => {
      finish();
      clearBannerStyles(elements);
    },
  })
    .to(elements.card, {
      autoAlpha: 0,
      y: 20,
      duration: 0.24,
      ease: 'power2.in',
    }, 0)
    .to(elements.banner, {
      autoAlpha: 0,
      duration: 0.2,
      ease: 'power1.in',
    }, 0);
}

function animateSettingsIn(elements) {
  if (!elements || !elements.settings || elements.settings.classList.contains('scc-hidden')) {
    return;
  }

  if (prefersReducedMotion()) {
    clearBannerStyles(elements);
    return;
  }

  gsap.killTweensOf([elements.settings, elements.settingsRows, elements.settingsFooter]);
  gsap.fromTo(
    [elements.settingsRows, elements.settingsFooter].filter(Boolean),
    { autoAlpha: 0, y: 10 },
    {
      autoAlpha: 1,
      y: 0,
      duration: 0.26,
      stagger: 0.03,
      ease: 'power2.out',
      overwrite: 'auto',
      clearProps: 'opacity,visibility,transform',
    }
  );
}

export function initSimpleCookieConsentBanner() {
  const elements = getBannerElements();

  if (!elements || elements.banner.hasAttribute(READY_ATTR)) {
    return;
  }

  elements.banner.setAttribute(READY_ATTR, 'true');

  if (!elements.banner.classList.contains('scc-hidden')) {
    animateBannerIn(elements, elements.banner.classList.contains('scc-settings-open'));
  }

  document.addEventListener('simple-cookie-consent:show', (event) => {
    const detail = event.detail || {};
    animateBannerIn(getBannerElements(), !!detail.openSettings);
  });

  document.addEventListener('simple-cookie-consent:before-hide', (event) => {
    const detail = event.detail || {};
    const current = getBannerElements();

    if (!current || typeof detail.finish !== 'function') {
      return;
    }

    event.preventDefault();
    animateBannerOut(current, detail.finish);
  });

  document.addEventListener('simple-cookie-consent:settings-change', (event) => {
    const detail = event.detail || {};

    if (!detail.open) {
      return;
    }

    animateSettingsIn(getBannerElements());
  });
}
