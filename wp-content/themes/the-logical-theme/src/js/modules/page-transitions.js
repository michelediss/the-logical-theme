import { gsap } from 'gsap';

export function initPageTransitions() {
  const shell = document.querySelector('body');

  if (!shell) {
    return;
  }

  gsap.from(shell, {
    autoAlpha: 0,
    duration: 0.35,
    ease: 'power1.out',
  });

  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');

    if (!link || link.target === '_blank' || link.origin !== window.location.origin) {
      return;
    }

    shell.dataset.transitionIntent = 'leave';
  });
}
