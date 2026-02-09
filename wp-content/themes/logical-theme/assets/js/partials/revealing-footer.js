(function () {
    document.addEventListener("DOMContentLoaded", function () {
      initFooterSpacing();
      initContattiOpacity();
    });
  
    function initFooterSpacing() {
      const content = document.querySelector("#content");
      const footer = document.querySelector("footer");
      const offsetHeight = 80;
  
      function adjustContentMargin() {
        if (!content || !footer) {
          return;
        }
  
        const footerHeight = footer.offsetHeight;
        const marginBottom = Math.max(0, footerHeight - offsetHeight);
        content.style.marginBottom = `${marginBottom}px`;
      }
  
      adjustContentMargin();
      window.addEventListener("resize", adjustContentMargin);
    }
  
    function initContattiOpacity() {
      const contattiSection = document.querySelector("#contatti");
      if (!contattiSection) {
        return;
      }
  
      function updateContattiBackground() {
        const docHeight = document.documentElement.scrollHeight;
        const viewportHeight = window.innerHeight;
        const scrollTop = window.scrollY || document.documentElement.scrollTop || 0;
        const scrollableHeight = Math.max(1, docHeight - viewportHeight);
        const scrollPercent = (scrollTop / scrollableHeight) * 100;
  
        if (scrollPercent <= 35) {
          contattiSection.style.setProperty("opacity", "0", "important");
        } else {
          contattiSection.style.removeProperty("opacity");
        }
      }
  
      updateContattiBackground();
      window.addEventListener("scroll", updateContattiBackground, { passive: true });
      window.addEventListener("resize", updateContattiBackground);
    }
  })();
  