(() => {
  "use strict";

  const FIELD_NAME = "icon";
  const PREVIEW_CLASS = "acf-bs-icon-preview";
  const OPTION_ICON_CLASS = "acf-bs-icon-option";

  const sanitizeSlug = (value) => {
    if (!value) return "";
    let slug = String(value);
    slug = slug.replace(/\.svg$/i, "");
    slug = slug.toLowerCase().replace(/[^a-z0-9\-]/g, "");
    if (slug.endsWith("svg") && !slug.endsWith("-svg")) {
      slug = slug.slice(0, -3);
    }
    return slug;
  };

  let currentSelect = null;

  const ensurePreview = (selectEl) => {
    if (!selectEl || selectEl.dataset.bsIconPreviewBound === "1") return;

    const inputWrap = selectEl.closest(".acf-input");
    if (!inputWrap) return;

    let preview = inputWrap.querySelector(`.${PREVIEW_CLASS}`);
    if (!preview) {
      preview = document.createElement("span");
      preview.className = PREVIEW_CLASS;
      preview.setAttribute("aria-hidden", "true");
      preview.style.display = "inline-flex";
      preview.style.alignItems = "center";
      preview.style.marginLeft = "8px";
      preview.style.fontSize = "2.1em";
      preview.style.lineHeight = "1";
      preview.style.minWidth = "1.8em";
      preview.style.paddingTop = "4px";
      inputWrap.appendChild(preview);
    }

    const update = () => {
      const slug = sanitizeSlug(selectEl.value);
      preview.className = PREVIEW_CLASS + (slug ? ` bi bi-${slug}` : "");
    };

    selectEl.addEventListener("change", update);
    selectEl.addEventListener("input", update);
    update();

    const select2 = selectEl.nextElementSibling;
    if (select2 && select2.classList.contains("select2")) {
      const selection = select2.querySelector(".select2-selection");
      if (selection) {
        selection.addEventListener("mousedown", () => {
          currentSelect = selectEl;
          setTimeout(decorateSelect2Options, 0);
        });
      }
    }

    selectEl.dataset.bsIconPreviewBound = "1";
  };

  const initIn = (root) => {
    const scope = root || document;
    const fields = scope.querySelectorAll(`.acf-field[data-name="${FIELD_NAME}"] select`);
    fields.forEach(ensurePreview);
  };

  if (window.acf && typeof window.acf.addAction === "function") {
    window.acf.addAction("ready", (el) => {
      initIn(el && el[0] ? el[0] : document);
    });
    window.acf.addAction("append", (el) => {
      initIn(el && el[0] ? el[0] : document);
    });
  } else {
    document.addEventListener("DOMContentLoaded", () => initIn(document));
  }

  const decorateSelect2Options = () => {
    if (!currentSelect) return;
    const openContainer = document.querySelector(".select2-container--open");
    if (!openContainer) return;
    if (openContainer.previousElementSibling !== currentSelect) return;
    if (!currentSelect.closest(`.acf-field[data-name="${FIELD_NAME}"]`)) return;

    const map = new Map();
    Array.from(currentSelect.options).forEach((opt) => {
      const label = opt.textContent ? opt.textContent.trim() : "";
      if (label) {
        map.set(label, opt.value || "");
      }
    });

    const results = document.querySelectorAll(".select2-results__option");
    results.forEach((item) => {
      if (item.querySelector(`.${OPTION_ICON_CLASS}`)) return;
      const label = item.textContent ? item.textContent.trim() : "";
      const value = map.get(label);
      const slug = sanitizeSlug(value || "");
      if (!slug) return;

      const icon = document.createElement("span");
      icon.className = `${OPTION_ICON_CLASS} bi bi-${slug}`;
      icon.setAttribute("aria-hidden", "true");
      icon.style.display = "inline-flex";
      icon.style.alignItems = "center";
      icon.style.marginRight = "8px";
      icon.style.fontSize = "1.4em";
      icon.style.lineHeight = "1";

      item.prepend(icon);
    });
  };

  document.addEventListener("click", () => {
    setTimeout(decorateSelect2Options, 0);
  });

  document.addEventListener("keyup", () => {
    setTimeout(decorateSelect2Options, 0);
  });

  const observer = new MutationObserver((mutations) => {
    mutations.forEach((m) => {
      m.addedNodes.forEach((node) => {
        if (node.nodeType === 1) {
          initIn(node);
          setTimeout(decorateSelect2Options, 0);
        }
      });
    });
  });

  observer.observe(document.documentElement, { childList: true, subtree: true });
})();
