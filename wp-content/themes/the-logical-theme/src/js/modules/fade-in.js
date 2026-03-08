import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export function initFadeIn() {
  const targets = Array.from(document.querySelectorAll('main > *:not(.no-fadein), .wp-block-group:not(.no-fadein)'));

  if (!targets.length) {
    return;
  }

  targets.forEach((element) => {
    element.classList.add('fade-target');

    gsap.to(element, {
      autoAlpha: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: element,
        start: 'top 80%',
        once: true,
      },
    });
  });
}
