import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const FADE_READY_ATTR = 'data-fade-in-ready';
const FADE_IN_SELECTOR = 'main > *:not(.no-fadein), main [data-fade-in]:not(.no-fadein)';

function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function getFadeTargets() {
  return Array.from(document.querySelectorAll(FADE_IN_SELECTOR)).filter((element) => {
    if (element.hasAttribute(FADE_READY_ATTR)) {
      return false;
    }

    return !element.closest('[data-fade-in-ready] [data-fade-in]');
  });
}

function revealImmediately(element) {
  element.classList.remove('fade-target');
  gsap.set(element, {
    autoAlpha: 1,
    y: 0,
    clearProps: 'opacity,visibility,transform',
  });
}

export function initFadeIn() {
  const targets = getFadeTargets();

  if (!targets.length) {
    return;
  }

  const reducedMotion = prefersReducedMotion();

  targets.forEach((element) => {
    element.setAttribute(FADE_READY_ATTR, 'true');

    if (reducedMotion) {
      revealImmediately(element);
      return;
    }

    element.classList.add('fade-target');
    gsap.set(element, { autoAlpha: 0, y: 24 });

    gsap.to(element, {
      autoAlpha: 1,
      y: 0,
      duration: 0.55,
      ease: 'power2.out',
      overwrite: 'auto',
      clearProps: 'opacity,visibility,transform',
      scrollTrigger: {
        trigger: element,
        start: 'top 80%',
        once: true,
      },
    });
  });
}
