/*
 * Entry point for the theme front-end. It initializes the small progressive-enhancement modules
 * used across block templates and patterns.
 */

import { initFadeIn } from './modules/fade-in';
import { initMenuReveal } from './modules/menu';
import { initPageTransitions } from './modules/page-transitions';
import { initSimpleCookieConsentBanner } from './modules/simple-cookie-consent-banner';
import { initSwipers } from './modules/swiper';
import { initPatternHooks } from './patterns/index';

function bootstrap() {
  initFadeIn();
  initMenuReveal();
  initPageTransitions();
  initSimpleCookieConsentBanner();
  initSwipers();
  initPatternHooks();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootstrap, { once: true });
} else {
  bootstrap();
}
