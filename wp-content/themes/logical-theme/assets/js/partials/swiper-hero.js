document.addEventListener("DOMContentLoaded", function () {
  if (typeof Swiper === "undefined") {
    return;
  }

  const container = document.querySelector("[data-hero-swiper]");
  if (!container) {
    return;
  }

  const nextEl = container.querySelector(".swiper-button-next");
  const prevEl = container.querySelector(".swiper-button-prev");
  const paginationEl = container.querySelector(".swiper-pagination");
  const hasMultipleSlides = container.querySelectorAll(".swiper-slide").length > 1;

  const config = {
    autoHeight: true,
    slidesPerView: 1,
    loop: hasMultipleSlides,
    watchOverflow: true
  };

  if (nextEl && prevEl) {
    config.navigation = { nextEl, prevEl };
  }

  if (paginationEl) {
    config.pagination = { el: paginationEl, clickable: true };
  }

  new Swiper(container, config);
});
