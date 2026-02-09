(() => {
    function ensurePreview(selectEl) {
      const field = selectEl.closest(".acf-field") || selectEl.parentElement;
  
      let preview = field.querySelector(".acf-bs-icon-preview");
      if (!preview) {
        preview = document.createElement("span");
        preview.className = "acf-bs-icon-preview";
        preview.style.display = "inline-flex";
        preview.style.alignItems = "center";
        preview.style.justifyContent = "center";
        preview.style.marginLeft = "10px";
        preview.style.fontSize = "22px";
        preview.style.minWidth = "28px";
  
        selectEl.insertAdjacentElement("afterend", preview);
      }
      return preview;
    }
  
    function updatePreview(selectEl) {
      const val = selectEl.value;
      const preview = ensurePreview(selectEl);
  
      if (!val) {
        preview.innerHTML = "";
        return;
      }
  
      // Bootstrap Icons: bi bi-<slug>
      preview.innerHTML = `<i class="bi bi-${val}" aria-hidden="true"></i>`;
    }
  
    function init(context) {
      const root = context && context.querySelector ? context : document;
  
      // Prende i select ACF che salvano bs_icon.
      // Se il campo è dentro repeater/flexible, il name diventa tipo: acf[field_xxx][0][field_yyy]
      const selects = root.querySelectorAll('select[name$="[bs_icon]"], select[name="bs_icon"]');
  
      selects.forEach((selectEl) => {
        updatePreview(selectEl);
  
        if (!selectEl.dataset.bsIconPreviewBound) {
          selectEl.addEventListener("change", () => updatePreview(selectEl));
          selectEl.dataset.bsIconPreviewBound = "1";
        }
      });
    }
  
    // ACF hooks (quando ACF è presente)
    if (window.acf && typeof window.acf.addAction === "function") {
      window.acf.addAction("ready", (el) => init(el || document));
      window.acf.addAction("append", (el) => init(el || document));
    } else {
      // Fallback: DOMContentLoaded + osserva cambiamenti (utile se ACF carica dopo)
      document.addEventListener("DOMContentLoaded", () => init(document));
  
      const obs = new MutationObserver((mutations) => {
        for (const m of mutations) {
          m.addedNodes.forEach((node) => {
            if (node.nodeType === 1) init(node);
          });
        }
      });
      obs.observe(document.documentElement, { childList: true, subtree: true });
    }
  })();
  