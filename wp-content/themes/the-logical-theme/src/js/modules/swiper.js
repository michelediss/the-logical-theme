import Swiper from 'swiper';
import 'swiper/css';

export function initSwipers() {
  const sliders = document.querySelectorAll('[data-swiper]');

  if (!sliders.length) {
    return;
  }

  sliders.forEach((element) => {
    new Swiper(element, {
      slidesPerView: 1,
      spaceBetween: 24,
    });
  });
}
