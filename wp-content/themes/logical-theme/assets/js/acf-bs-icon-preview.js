(function ($) {
  "use strict";

  const FIELD_NAME = "icon";
  const PREVIEW_CLASS = "acf-bs-icon-preview";

  const sanitizeSlug = (value) => {
    if (!value) return "";
    let slug = String(value).replace(/\.svg$/i, "");
    slug = slug.toLowerCase().replace(/[^a-z0-9\-]/g, "");
    if (slug.endsWith("svg") && !slug.endsWith("-svg")) {
      slug = slug.slice(0, -3);
    }
    return slug;
  };

  const ensureInlinePreview = (selectEl) => {
    if (!selectEl) return null;

    const inputWrap = selectEl.closest(".acf-input") || selectEl.parentElement;
    if (!inputWrap) return null;

    let preview = inputWrap.querySelector(`.${PREVIEW_CLASS}`);
    if (!preview) {
      preview = document.createElement("span");
      preview.className = PREVIEW_CLASS;
      preview.style.display = "inline-flex";
      preview.style.alignItems = "center";
      preview.style.justifyContent = "center";
      preview.style.marginLeft = "10px";
      preview.style.fontSize = "22px";
      preview.style.minWidth = "28px";
      selectEl.insertAdjacentElement("afterend", preview);
    }

    return preview;
  };

  const updateInlinePreview = (selectEl) => {
    const preview = ensureInlinePreview(selectEl);
    if (!preview) return;

    const slug = sanitizeSlug(selectEl.value || "");
    if (!slug) {
      preview.innerHTML = "";
      return;
    }

    preview.innerHTML = `<i class="bi bi-${slug}" aria-hidden="true"></i>`;
  };

  const initField = (selectEl) => {
    if (!selectEl || selectEl.dataset.bsIconPreviewBound === "1") return;

    updateInlinePreview(selectEl);

    selectEl.addEventListener("change", () => updateInlinePreview(selectEl));

    if (window.jQuery) {
      window.jQuery(selectEl).on("select2:select select2:clear", () => {
        updateInlinePreview(selectEl);
      });
    }

    selectEl.dataset.bsIconPreviewBound = "1";
  };

  const initIn = (root) => {
    const scope = root && root.querySelector ? root : document;
    const selects = scope.querySelectorAll(`.acf-field[data-name="${FIELD_NAME}"] select`);
    selects.forEach(initField);
  };

  // Render icon inside Select2 list and selected value.
  const renderSelect2Option = (state) => {
    if (!state) return "";

    const id = state.id || "";
    const text = state.text || "";

    if (!id) {
      return text;
    }

    const slug = sanitizeSlug(id);
    if (!slug) {
      return text;
    }

    return $(
      `<span><i class="bi bi-${slug}" aria-hidden="true" style="margin-right:8px;"></i>${text}</span>`
    );
  };

  if (window.acf && typeof window.acf.addFilter === "function") {
    window.acf.addFilter("select2_args", function (args, $select) {
      const $field = $select && $select.closest ? $select.closest(".acf-field") : null;
      if (!$field || !$field.length || $field.data("name") !== FIELD_NAME) {
        return args;
      }

      args.templateResult = renderSelect2Option;
      args.templateSelection = renderSelect2Option;
      args.escapeMarkup = function (markup) {
        return markup;
      };

      return args;
    });

    window.acf.addAction("ready", (el) => initIn(el && el[0] ? el[0] : document));
    window.acf.addAction("append", (el) => initIn(el && el[0] ? el[0] : document));
  } else {
    document.addEventListener("DOMContentLoaded", () => initIn(document));
  }

  const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      mutation.addedNodes.forEach((node) => {
        if (node && node.nodeType === 1) {
          initIn(node);
        }
      });
    }
  });

  observer.observe(document.documentElement, { childList: true, subtree: true });
})(window.jQuery || function () {});
