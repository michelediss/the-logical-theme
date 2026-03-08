import { gsap } from 'gsap';

const PAGE_TRANSITION_READY_ATTR = 'data-page-transitions-ready';
const NAVIGATION_EXCLUDE_PREFIXES = ['/wp-admin', '/wp-login.php', '/wp-json'];

function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function isModifiedEvent(event) {
  return event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey;
}

function shouldSkipLink(link) {
  if (!link || !link.href || link.target === '_blank' || link.hasAttribute('download')) {
    return true;
  }

  if (link.origin !== window.location.origin) {
    return true;
  }

  const url = new URL(link.href, window.location.href);
  const currentUrl = new URL(window.location.href);

  if (url.pathname === currentUrl.pathname && url.search === currentUrl.search && url.hash) {
    return true;
  }

  return NAVIGATION_EXCLUDE_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));
}

export function initPageTransitions() {
  const shell = document.querySelector('body');

  if (!shell || shell.hasAttribute(PAGE_TRANSITION_READY_ATTR)) {
    return;
  }

  shell.setAttribute(PAGE_TRANSITION_READY_ATTR, 'true');
  let isTransitioning = false;
  const reducedMotion = prefersReducedMotion();

  gsap.fromTo(
    shell,
    { autoAlpha: 0 },
    {
      autoAlpha: 1,
      duration: reducedMotion ? 0.01 : 0.35,
      ease: 'power1.out',
      clearProps: 'opacity,visibility',
    }
  );

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    const link = event.target.closest('a[href]');

    if (isTransitioning || isModifiedEvent(event) || shouldSkipLink(link)) {
      return;
    }

    event.preventDefault();
    isTransitioning = true;
    shell.dataset.transitionIntent = 'leave';
    shell.classList.add('is-page-transitioning');

    const completeNavigation = () => {
      window.location.assign(link.href);
    };

    if (reducedMotion) {
      completeNavigation();
      return;
    }

    gsap.to(shell, {
      autoAlpha: 0,
      y: -12,
      duration: 0.22,
      ease: 'power1.in',
      overwrite: 'auto',
      onComplete: completeNavigation,
    });
  }, { capture: true });
}
