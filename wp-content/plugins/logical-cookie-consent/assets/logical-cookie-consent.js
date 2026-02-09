(function () {
  const DEFAULT_CATS = ["preferences", "statistics-anonymous", "statistics", "marketing"];
  const ESCAPE_MAP = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;"
  };
  let consentState = null;

  function hasFn(name) {
    return typeof window[name] === "function";
  }

  function escapeHTML(str) {
    return String(str == null ? "" : str).replace(/[&<>"']/g, (match) => ESCAPE_MAP[match] || match);
  }

  function decodeEmbedHtml(encoded) {
    if (!encoded || !window.atob) return "";
    try {
      if (window.TextDecoder) {
        const binary = window.atob(encoded);
        const len = binary.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i += 1) {
          bytes[i] = binary.charCodeAt(i);
        }
        return new TextDecoder().decode(bytes);
      }
      return window.atob(encoded);
    } catch (err) {
      try {
        return decodeURIComponent(escape(window.atob(encoded)));
      } catch (_) {
        return "";
      }
    }
  }

  function executeScripts(root) {
    if (!root) return;
    const scripts = root.querySelectorAll("script");
    scripts.forEach((script) => {
      const clone = document.createElement("script");
      for (let i = 0; i < script.attributes.length; i += 1) {
        const attr = script.attributes[i];
        if (!attr) continue;
        clone.setAttribute(attr.name, attr.value);
      }
      clone.text = script.text || script.textContent || "";
      script.parentNode.replaceChild(clone, script);
    });
  }

  function defineConsentTypeOptin() {
    window.wp_consent_type = "optin";
    document.dispatchEvent(new CustomEvent("wp_consent_type_defined"));
  }

  function canRun(category) {
    if (category === "functional") return true;
    if (hasFn("wp_has_consent")) {
      return !!window.wp_has_consent(category);
    }
    if (consentState && consentState.categories) {
      const key = String(category || "").replace(/_/g, "-");
      return !!consentState.categories[key];
    }
    return false;
  }

  function shouldBlockEmbeds() {
    if (!consentState) return true;
    return !canRun("marketing");
  }

  function restoreBlockedEmbeds() {
    const blocked = document.querySelectorAll("iframe[data-lcc-blocked='1']");
    blocked.forEach((iframe) => {
      const originalSrc = iframe.getAttribute("data-lcc-original-src");
      const originalSrcdoc = iframe.getAttribute("data-lcc-original-srcdoc");

      if (originalSrc) {
        iframe.setAttribute("src", originalSrc);
      } else {
        iframe.removeAttribute("src");
      }

      if (originalSrcdoc) {
        iframe.setAttribute("srcdoc", originalSrcdoc);
      } else {
        iframe.removeAttribute("srcdoc");
      }

      iframe.removeAttribute("data-lcc-blocked");
      iframe.removeAttribute("data-lcc-original-src");
      iframe.removeAttribute("data-lcc-original-srcdoc");
      iframe.style.display = "";

      const wrapper = iframe.parentElement;
      if (wrapper && wrapper.getAttribute("data-lcc-embed-wrapper") === "1") {
        wrapper.parentNode.insertBefore(iframe, wrapper);
        wrapper.remove();
      }
    });
  }

  function blockEmbedsIfNeeded() {
    if (!shouldBlockEmbeds()) {
      restoreBlockedEmbeds();
      return;
    }

    const iframes = document.querySelectorAll("iframe");
    iframes.forEach((iframe) => {
      if (!iframe || iframe.getAttribute("data-lcc-blocked") === "1") return;
      if (iframe.closest(".pap-consent-embed")) return;

      const src = iframe.getAttribute("src");
      const srcdoc = iframe.getAttribute("srcdoc");
      if (!src && !srcdoc) return;

      iframe.setAttribute("data-lcc-original-src", src || "");
      if (srcdoc) iframe.setAttribute("data-lcc-original-srcdoc", srcdoc);

      iframe.setAttribute("src", "about:blank");
      iframe.removeAttribute("srcdoc");
      iframe.setAttribute("data-lcc-blocked", "1");
      iframe.style.display = "none";

      const wrapper = document.createElement("div");
      wrapper.className = "lcc-embed-blocked";
      wrapper.setAttribute("data-lcc-embed-wrapper", "1");

      const placeholder = document.createElement("div");
      placeholder.className = "lcc-embed-blocked__placeholder";
      const message = (window.LCC && LCC.texts && LCC.texts.embedBlocked)
        || "Per visualizzare questo contenuto devi accettare i cookie.";
      const buttonLabel = (window.LCC && LCC.texts && LCC.texts.settings)
        || "Impostazioni";
      placeholder.innerHTML = `
        <p class="paragraph regular text-black text-base">${escapeHTML(message)}</p>
        <button type="button" class="button heading text-uppercase rounded-pill text-decoration-none px-4 py-2 text-sm text-white bg-primary border-primary hover-text-primary hover-bg-white hover-border-primary" data-lcc-open-settings="1">${escapeHTML(buttonLabel)}</button>
      `;

      const parent = iframe.parentNode;
      if (!parent) return;
      parent.insertBefore(wrapper, iframe);
      wrapper.appendChild(iframe);
      wrapper.appendChild(placeholder);
    });
  }

  function activateConsentEmbeds() {
    if (!canRun("marketing")) return;
    const wraps = document.querySelectorAll(".pap-consent-embed[data-pap-embed-html]");
    wraps.forEach((wrap) => {
      if (!wrap || wrap.dataset.papEmbedLoaded === "1") return;
      const encoded = wrap.getAttribute("data-pap-embed-html");
      const body = wrap.querySelector(".pap-consent-embed__body");
      if (!encoded || !body) return;
      const html = decodeEmbedHtml(encoded);
      if (!html) return;
      body.innerHTML = html;
      executeScripts(body);
      body.removeAttribute("hidden");
      const placeholder = wrap.querySelector(".pap-consent-embed__placeholder");
      if (placeholder) placeholder.remove();
      wrap.classList.add("pap-consent-embed--active");
      wrap.dataset.papEmbedLoaded = "1";
    });
  }

  function runBlockedScripts() {
    const scripts = document.querySelectorAll('script[type="text/plain"][data-wp-consent-category]');
    scripts.forEach((s) => {
      if (s.dataset.lccExecuted === "1") return;

      const cat = s.getAttribute("data-wp-consent-category") || "functional";
      if (!canRun(cat)) return;

      const real = document.createElement("script");
      const attrs = s.attributes;
      for (let i = 0; i < attrs.length; i += 1) {
        const attr = attrs[i];
        if (!attr) continue;
        if (attr.name === "type" || attr.name === "data-wp-consent-category") continue;
        if (attr.name === "data-src") {
          real.src = attr.value;
          continue;
        }
        real.setAttribute(attr.name, attr.value);
      }

      if (!real.src) {
        const src = s.getAttribute("data-src") || s.getAttribute("src");
        if (src) {
          real.src = src;
        }
      }

      if (!real.src) {
        real.text = s.text || s.textContent || "";
      }

      if (!s.parentNode) return;

      s.dataset.lccExecuted = "1";
      s.parentNode.insertBefore(real, s);
      s.remove();
    });

    activateConsentEmbeds();
    blockEmbedsIfNeeded();
  }

  function dispatchConsentChange(consentMap) {
    // Evento standard: detail contiene mappa categoria -> allow|deny :contentReference[oaicite:10]{index=10}
    document.dispatchEvent(new CustomEvent("wp_listen_for_consent_change", { detail: consentMap }));
  }

  async function saveConsent(map) {
    const body = new URLSearchParams();
    body.set("action", "lcc_set_consent");
    body.set("nonce", LCC.nonce);

    body.set("preferences", map.preferences ? "1" : "0");
    body.set("statistics_anonymous", map["statistics-anonymous"] ? "1" : "0");
    body.set("statistics", map.statistics ? "1" : "0");
    body.set("marketing", map.marketing ? "1" : "0");

    const res = await fetch(LCC.ajaxUrl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: body.toString(),
      credentials: "same-origin"
    });

    const json = await res.json();
    if (!json || !json.success) throw new Error("consent_save_failed");
    return json.data.consent;
  }

  function lockScroll() {
    const body = document.body;
    const docEl = document.documentElement;
    if (!body) return;
    if (!body.classList.contains("lcc-scroll-locked")) {
      body.classList.add("lcc-scroll-locked");
      if (docEl) {
        docEl.classList.add("lcc-scroll-locked");
      }
    }
  }

  function unlockScroll() {
    const body = document.body;
    const docEl = document.documentElement;
    if (!body) return;
    body.classList.remove("lcc-scroll-locked");
    if (docEl) {
      docEl.classList.remove("lcc-scroll-locked");
    }
  }

  function showBanner(openSettings) {
    const banner = document.getElementById("lcc-banner");
    if (!banner) return;

    const settings = document.getElementById("lcc-settings");
    syncToggles();
    banner.classList.remove("lcc-hidden");
    if (openSettings && settings) {
      settings.classList.remove("lcc-hidden");
      banner.classList.add("lcc-settings-open");
    } else {
      banner.classList.remove("lcc-settings-open");
    }
    lockScroll();
    banner.focus();
  }

  function hideBanner() {
    const banner = document.getElementById("lcc-banner");
    if (!banner) return;
    banner.classList.add("lcc-hidden");
    banner.classList.remove("lcc-settings-open");
    unlockScroll();
    const settings = document.getElementById("lcc-settings");
    if (settings) settings.classList.add("lcc-hidden");
  }

  function getInitialConsentState() {
    const localized = (window.LCC && window.LCC.initialConsent) || {};
    const categories = Object.assign(
      Object.create(null),
      {
        preferences: false,
        "statistics-anonymous": false,
        statistics: false,
        marketing: false
      },
      localized.categories || {}
    );

    if (categories.statistics) {
      categories["statistics-anonymous"] = true;
    }

    return {
      categories,
      decisionMade: !!localized.decisionMade
    };
  }

  consentState = getInitialConsentState();

  function syncToggles() {
    const banner = document.getElementById("lcc-banner");
    if (!banner) return;
    const toggles = {
      preferences: banner.querySelector('input[data-lcc-toggle="preferences"]'),
      "statistics-anonymous": banner.querySelector('input[data-lcc-toggle="statistics-anonymous"]'),
      statistics: banner.querySelector('input[data-lcc-toggle="statistics"]'),
      marketing: banner.querySelector('input[data-lcc-toggle="marketing"]')
    };

    Object.keys(toggles).forEach((key) => {
      if (toggles[key]) {
        toggles[key].checked = !!consentState.categories[key];
      }
    });
  }

  function updateLocalConsent(consentMap) {
    DEFAULT_CATS.forEach((key) => {
      if (consentMap && Object.prototype.hasOwnProperty.call(consentMap, key)) {
        consentState.categories[key] = consentMap[key] === "allow";
      }
    });
    consentState.decisionMade = true;
    if (consentState.categories.statistics) {
      consentState.categories["statistics-anonymous"] = true;
    }
    syncToggles();
    blockEmbedsIfNeeded();
  }

  function init() {
    defineConsentTypeOptin();

    // Prova subito ad attivare eventuali script già consentiti
    runBlockedScripts();

    // Link footer “Rivedi consenso”
    const manage = document.getElementById("lcc-manage-consent");
    if (manage) {
      manage.addEventListener("click", function () {
        showBanner(true);
      });
    }

    const banner = document.getElementById("lcc-banner");
    if (!banner) return;

    const settings = document.getElementById("lcc-settings");

    document.addEventListener("click", function (event) {
      const trigger = event.target.closest("[data-lcc-open-settings]");
      if (!trigger) return;
      event.preventDefault();
      showBanner(true);
    });

    if (window.LCC && window.LCC.showBannerOnLoad) {
      showBanner(false);
    }

    syncToggles();
    renderCookieLists();
    activateConsentEmbeds();
    blockEmbedsIfNeeded();
    setupCookieToggles();

    function getToggleStates() {
      const findToggle = (key) => banner.querySelector(`input[data-lcc-toggle="${key}"]`);
      const pref = findToggle("preferences");
      const anon = findToggle("statistics-anonymous");
      const stats = findToggle("statistics");
      const marketing = findToggle("marketing");

      return {
        preferences: !!(pref && pref.checked),
        "statistics-anonymous": !!(anon && anon.checked),
        statistics: !!(stats && stats.checked),
        marketing: !!(marketing && marketing.checked)
      };
    }

    banner.addEventListener("click", async (e) => {
      const btn = e.target.closest("[data-lcc-action]");
      if (!btn) return;
      e.preventDefault();
      const action = btn.getAttribute("data-lcc-action");

      if (action === "openSettings") {
        if (settings) {
          const isHidden = settings.classList.contains("lcc-hidden");
          settings.classList.toggle("lcc-hidden", !isHidden);
          banner.classList.toggle("lcc-settings-open", isHidden);
        }
        return;
      }

      if (action === "close") {
        hideBanner();
        return;
      }

      try {
        if (action === "acceptAll") {
          const consent = await saveConsent({
            preferences: true,
            "statistics-anonymous": true,
            statistics: true,
            marketing: true
          });
          dispatchConsentChange(consent);
          updateLocalConsent(consent);
          runBlockedScripts();
          hideBanner();
          return;
        }

        if (action === "rejectAll") {
          const consent = await saveConsent({
            preferences: false,
            "statistics-anonymous": false,
            statistics: false,
            marketing: false
          });
          dispatchConsentChange(consent);
          updateLocalConsent(consent);
          runBlockedScripts();
          hideBanner();
          return;
        }

        if (action === "save") {
          const consent = await saveConsent(getToggleStates());
          dispatchConsentChange(consent);
          updateLocalConsent(consent);
          runBlockedScripts();
          hideBanner();
          return;
        }
      } catch (_) {
        // Se fallisce, lascia tutto visibile
      }
    });

    // Quando altri CMP cambiano consenso, riattiva
    document.addEventListener("wp_listen_for_consent_change", function () {
      runBlockedScripts();
    });
  }

  document.addEventListener("DOMContentLoaded", init);

  function setCookiePanelState(category, open) {
    const panel = document.querySelector(`[data-lcc-cookie-panel="${category}"]`);
    const toggle = document.querySelector(`[data-lcc-cookie-toggle="${category}"]`);
    if (panel) {
      panel.classList.toggle("lcc-hidden", !open);
    }
    if (toggle) {
      toggle.classList.toggle("lcc-open", open);
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    }
  }

  function toggleCookiePanel(category) {
    const panel = document.querySelector(`[data-lcc-cookie-panel="${category}"]`);
    if (!panel) return;
    const isOpen = !panel.classList.contains("lcc-hidden");
    setCookiePanelState(category, !isOpen);
  }

  function setupCookieToggles() {
    const rows = document.querySelectorAll("[data-lcc-cookie-row]");
    rows.forEach((row) => {
      row.addEventListener("click", (event) => {
        if (event.target.closest("input") || event.target.closest("[data-lcc-cookie-toggle]")) {
          return;
        }
        const cat = row.getAttribute("data-lcc-cookie-row");
        if (!cat) return;
        event.preventDefault();
        toggleCookiePanel(cat);
      });
    });

    const toggles = document.querySelectorAll("[data-lcc-cookie-toggle]");
    toggles.forEach((btn) => {
      btn.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        const cat = btn.getAttribute("data-lcc-cookie-toggle");
        if (!cat) return;
        toggleCookiePanel(cat);
      });
    });
  }

  function renderCookieLists() {
    if (!window.LCC || !LCC.cookieDetails) return;
    const details = LCC.cookieDetails || {};
    const lists = document.querySelectorAll("[data-lcc-cookie-list]");
    const serviceLabel = escapeHTML((LCC.texts && LCC.texts.serviceLabel) || "Servizio");
    const durationLabel = escapeHTML((LCC.texts && LCC.texts.durationLabel) || "Durata");
    const descriptionLabel = escapeHTML((LCC.texts && LCC.texts.descriptionLabel) || "Descrizione");
    const emptyLabel = escapeHTML((LCC.texts && LCC.texts.noCookies) || "");

    lists.forEach((list) => {
      const cat = list.getAttribute("data-lcc-cookie-list");
      const items = (details && details[cat]) || [];
      const row = document.querySelector(`[data-lcc-cookie-row="${cat}"]`);
      const panel = document.querySelector(`[data-lcc-cookie-panel="${cat}"]`);
      if (!items.length) {
        if (row) row.classList.add("lcc-hidden");
        if (panel) panel.classList.add("lcc-hidden");
        list.innerHTML = "";
        return;
      }

      if (row) row.classList.remove("lcc-hidden");
      if (panel) panel.classList.remove("lcc-hidden");
      setCookiePanelState(cat, false);

      const html = items
        .map((item) => {
          const name = escapeHTML(item.name || "");
          const service = escapeHTML(item.service || "");
          const duration = escapeHTML(item.duration || "");
          const desc = escapeHTML(item.description || "");
          return `
            <div class="lcc-cookie-item">
              <div class="lcc-cookie-item-name text-base bold text-secondary paragraph">${name || "&ndash;"}</div>
              <div class="lcc-cookie-item-meta text-sm text-gray paragraph">
                ${service ? `<div><span class="text-sm text-gray paragraph">${serviceLabel}</span><strong class="bold text-secondary paragraph">${service}</strong></div>` : ""}
                ${duration ? `<div><span class="text-sm text-gray paragraph">${durationLabel}</span><strong class="bold text-secondary paragraph">${duration}</strong></div>` : ""}
              </div>
              ${desc ? `<p class="lcc-cookie-item-desc text-sm text-gray paragraph"><span class="bold text-secondary paragraph">${descriptionLabel}:</span> ${desc}</p>` : ""}
            </div>
          `;
        })
        .join("");
      list.innerHTML = html;
    });
  }
})();
