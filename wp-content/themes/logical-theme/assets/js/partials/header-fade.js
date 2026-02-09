gsap.registerPlugin(ScrollTrigger);

document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.querySelector("#navbar-scroll");
  if (!navbar) {
    return;
  }
  const shell = navbar.querySelector(".navbar-scroll-shell");
  const setShadow = (enabled) => {
    if (!shell) {
      return;
    }
    shell.classList.toggle("is-shadowed", enabled);
  };

  gsap.set(navbar, { y: -100 });
  setShadow(false);

  ScrollTrigger.create({
    trigger: "body",
    start: "top top",
    end: "bottom bottom",
    onUpdate: (self) => {
      if (self.direction === 1) {
        gsap.to(navbar, { y: -100, duration: 0.3, ease: "power2.out" });
        setShadow(false);
      } else if (self.direction === -1) {
        gsap.to(navbar, { y: 0, duration: 0.3, ease: "power2.out" });
        setShadow(true);
      }
    }
  });

  window.addEventListener("scroll", () => {
    if (window.scrollY === 0) {
      gsap.to(navbar, { y: -100, duration: 0.3, ease: "power2.out" });
      setShadow(false);
    }
  });
});
