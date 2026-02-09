gsap.registerPlugin(ScrollTrigger);

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".row").forEach((row) => {
    if (row.closest(".wpcf7")) {
      return;
    }

    if (
      row.closest(".hero-followup") ||
      row.closest(".no-gsap") ||
      row.classList.contains("no-gsap")
    ) {
      gsap.set(row.querySelectorAll(":scope > *"), { opacity: 1, y: 0 });
      return;
    }

    ScrollTrigger.create({
      trigger: row,
      start: "top 80%",
      once: true,
      onEnter: () => {
        gsap.fromTo(
          row.querySelectorAll(":scope > *"),
          { opacity: 0, y: 20 },
          { opacity: 1, y: 0, duration: 0.6, stagger: 0.2, clearProps: "transform" }
        );
      }
    });
  });

  const navbar = document.querySelector("#navbar-scroll");
  if (navbar) {
    gsap.set(navbar, { y: -100 });

    ScrollTrigger.create({
      trigger: "body",
      start: "top top",
      end: "bottom bottom",
      onUpdate: (self) => {
        if (self.direction === 1) {
          gsap.to(navbar, { y: -100, duration: 0.3, ease: "power2.out" });
        } else if (self.direction === -1) {
          gsap.to(navbar, { y: 0, duration: 0.3, ease: "power2.out" });
        }
      }
    });

    window.addEventListener("scroll", () => {
      if (window.scrollY === 0) {
        gsap.to(navbar, { y: -100, duration: 0.3, ease: "power2.out" });
      }
    });
  }

  const hamburger = document.querySelector(".mobile-offcanvas-toggle");
  if (hamburger) {
    gsap.set(hamburger, { y: 120 });
    let isVisible = false;

    ScrollTrigger.create({
      trigger: "body",
      start: "top -80",
      onEnter: () => {
        if (isVisible) {
          return;
        }
        isVisible = true;
        gsap.to(hamburger, {
          y: 0,
          duration: 0.35,
          ease: "power3.out",
          overwrite: "auto"
        });
      },
      onLeaveBack: () => {
        if (!isVisible) {
          return;
        }
        isVisible = false;
        gsap.to(hamburger, {
          y: 120,
          duration: 0.3,
          ease: "power3.in",
          overwrite: "auto"
        });
      }
    });
  }
});
