(function (window, document, wp) {
  const i18n = wp && wp.i18n ? wp.i18n : null;
  const __ = i18n ? i18n.__ : function (text) { return text; };
  const config = window.SimpleCookieConsent || {};
  const defaultCategories = ["preferences", "statistics-anonymous", "statistics", "marketing"];
  const escapeMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;"
  };

  let consentState = getInitialConsentState();

  function escapeHTML(value) {
    return String(value == null ? "" : value).replace(/[&<>"']/g, function (match) {
      return escapeMap[match] || match;
    });
  }

  function decodeEmbedHtml(encoded) {
    if (!encoded || !window.atob) {
      return "";
    }

    try {
      if (window.TextDecoder) {
        const binary = window.atob(encoded);
        const bytes = new Uint8Array(binary.length);

        for (let index = 0; index < binary.length; index += 1) {
          bytes[index] = binary.charCodeAt(index);
        }

        return new TextDecoder().decode(bytes);
      }

      return window.atob(encoded);
    } catch (error) {
      return "";
    }
  }

  function executeScripts(root) {
    if (!root) {
      return;
    }

    root.querySelectorAll("script").forEach(function (scriptNode) {
      const clone = document.createElement("script");

      Array.prototype.forEach.call(scriptNode.attributes, function (attribute) {
        clone.setAttribute(attribute.name, attribute.value);
      });

      clone.text = scriptNode.text || scriptNode.textContent || "";
      scriptNode.parentNode.replaceChild(clone, scriptNode);
    });
  }

  function getInitialConsentState() {
    const localized = config.initialConsent || {};
    const categories = Object.assign(
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
      categories: categories,
      decisionMade: !!localized.decisionMade
    };
  }

  function hasConsent(category) {
    if (category === "functional") {
      return true;
    }

    if (window.wp_has_consent) {
      return !!window.wp_has_consent(category);
    }

    return !!(consentState.categories && consentState.categories[category]);
  }

  function getBanner() {
    return document.getElementById("simple-cookie-consent-banner");
  }

  function getSettingsPanel() {
    return document.getElementById("simple-cookie-consent-settings");
  }

  function dispatchBannerEvent(name, detail, cancelable) {
    const event = new CustomEvent(name, {
      detail: detail || {},
      cancelable: !!cancelable
    });

    document.dispatchEvent(event);
    return event;
  }

  function setSettingsState(open) {
    const banner = getBanner();
    const settings = getSettingsPanel();

    if (!banner || !settings) {
      return;
    }

    settings.classList.toggle("scc-hidden", !open);
    banner.classList.toggle("scc-settings-open", !!open);

    dispatchBannerEvent("simple-cookie-consent:settings-change", {
      banner: banner,
      settings: settings,
      open: !!open
    });
  }

  function showBanner(openSettings) {
    const banner = getBanner();

    if (!banner) {
      return;
    }

    syncToggles();
    banner.classList.remove("scc-hidden");
    document.documentElement.classList.add("scc-scroll-locked");
    document.body.classList.add("scc-scroll-locked");
    setSettingsState(!!openSettings);

    banner.focus();
    dispatchBannerEvent("simple-cookie-consent:show", {
      banner: banner,
      settings: getSettingsPanel(),
      openSettings: !!openSettings
    });
  }

  function hideBanner(reason) {
    const banner = getBanner();
    const settings = getSettingsPanel();

    if (!banner) {
      return;
    }

    const finish = function () {
      banner.classList.add("scc-hidden");
      banner.classList.remove("scc-settings-open");

      if (settings) {
        settings.classList.add("scc-hidden");
      }

      document.documentElement.classList.remove("scc-scroll-locked");
      document.body.classList.remove("scc-scroll-locked");
    };

    const event = dispatchBannerEvent("simple-cookie-consent:before-hide", {
      banner: banner,
      settings: settings,
      reason: reason || "dismiss",
      finish: finish
    }, true);

    if (event.defaultPrevented) {
      return;
    }

    finish();
  }

  function syncToggles() {
    const banner = getBanner();

    if (!banner) {
      return;
    }

    defaultCategories.forEach(function (category) {
      const input = banner.querySelector('[data-simple-cookie-consent-toggle="' + category + '"]');
      if (input) {
        input.checked = !!consentState.categories[category];
      }
    });
  }

  function setCookiePanelState(category, open) {
    const panel = document.querySelector('[data-simple-cookie-consent-cookie-panel="' + category + '"]');
    const toggle = document.querySelector('[data-simple-cookie-consent-cookie-toggle="' + category + '"]');

    if (panel) {
      panel.classList.toggle("scc-hidden", !open);
    }

    if (toggle) {
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      toggle.classList.toggle("is-open", open);
    }
  }

  function renderCookieLists() {
    const details = config.cookieDetails || {};
    const lists = document.querySelectorAll("[data-simple-cookie-consent-cookie-list]");
    const serviceLabel = escapeHTML(__("Service", "simple-cookie-consent"));
    const durationLabel = escapeHTML(__("Duration", "simple-cookie-consent"));
    const descriptionLabel = escapeHTML(__("Description", "simple-cookie-consent"));

    lists.forEach(function (list) {
      const category = list.getAttribute("data-simple-cookie-consent-cookie-list");
      const items = details[category] || [];
      const row = document.querySelector('[data-simple-cookie-consent-cookie-row="' + category + '"]');
      const panel = document.querySelector('[data-simple-cookie-consent-cookie-panel="' + category + '"]');

      if (!items.length) {
        if (row) {
          row.classList.add("scc-hidden");
        }
        if (panel) {
          panel.classList.add("scc-hidden");
        }
        list.innerHTML = "";
        return;
      }

      if (row) {
        row.classList.remove("scc-hidden");
      }
      if (panel) {
        panel.classList.remove("scc-hidden");
      }

      setCookiePanelState(category, false);

      list.innerHTML = items.map(function (item) {
        const name = escapeHTML(item.name || "");
        const service = escapeHTML(item.service || "");
        const duration = escapeHTML(item.duration || "");
        const description = escapeHTML(item.description || "");

        return [
          '<div class="scc-cookie-detail">',
          '<div class="scc-cookie-detail__name">' + (name || "&ndash;") + "</div>",
          '<div class="scc-cookie-detail__meta">',
          service ? '<span><strong>' + serviceLabel + ":</strong> " + service + "</span>" : "",
          duration ? '<span><strong>' + durationLabel + ":</strong> " + duration + "</span>" : "",
          "</div>",
          description ? '<p><strong>' + descriptionLabel + ":</strong> " + description + "</p>" : "",
          "</div>"
        ].join("");
      }).join("");
    });
  }

  function activateConsentEmbeds() {
    if (!hasConsent("marketing")) {
      return;
    }

    document.querySelectorAll(".simple-cookie-consent-embed[data-simple-cookie-consent-embed-html]").forEach(function (wrap) {
      if (wrap.dataset.simpleCookieConsentLoaded === "1") {
        return;
      }

      const body = wrap.querySelector(".simple-cookie-consent-embed__body");
      const encoded = wrap.getAttribute("data-simple-cookie-consent-embed-html");
      const placeholder = wrap.querySelector(".simple-cookie-consent-embed__placeholder");
      const html = decodeEmbedHtml(encoded);

      if (!body || !html) {
        return;
      }

      body.innerHTML = html;
      body.hidden = false;
      if (placeholder) {
        placeholder.remove();
      }

      executeScripts(body);
      wrap.dataset.simpleCookieConsentLoaded = "1";
    });
  }

  function blockEmbedsIfNeeded() {
    if (hasConsent("marketing")) {
      document.querySelectorAll("iframe[data-simple-cookie-consent-blocked='1']").forEach(function (iframe) {
        const originalSrc = iframe.getAttribute("data-simple-cookie-consent-original-src");
        const placeholder = iframe.nextElementSibling;
        if (originalSrc) {
          iframe.setAttribute("src", originalSrc);
        }

        iframe.style.display = "";
        iframe.removeAttribute("data-simple-cookie-consent-blocked");
        iframe.removeAttribute("data-simple-cookie-consent-original-src");
        if (placeholder && placeholder.classList.contains("simple-cookie-consent-iframe-placeholder")) {
          placeholder.remove();
        }
      });
      return;
    }

    document.querySelectorAll("iframe").forEach(function (iframe) {
      if (iframe.getAttribute("data-simple-cookie-consent-blocked") === "1") {
        return;
      }
      if (iframe.closest(".simple-cookie-consent-embed")) {
        return;
      }

      const src = iframe.getAttribute("src");
      if (!src) {
        return;
      }

      iframe.setAttribute("data-simple-cookie-consent-original-src", src);
      iframe.setAttribute("data-simple-cookie-consent-blocked", "1");
      iframe.setAttribute("src", "about:blank");
      iframe.style.display = "none";

      const wrapper = document.createElement("div");
      wrapper.className = "simple-cookie-consent-iframe-placeholder";
      wrapper.innerHTML = [
        "<p>" + escapeHTML(__("To view this content you need to accept marketing cookies.", "simple-cookie-consent")) + "</p>",
        '<button type="button" class="scc-button scc-button--secondary" data-simple-cookie-consent-open="1">' + escapeHTML(__("Settings", "simple-cookie-consent")) + "</button>"
      ].join("");

      iframe.parentNode.insertBefore(wrapper, iframe.nextSibling);
    });
  }

  function runBlockedScripts() {
    document.querySelectorAll('script[type="text/plain"][data-wp-consent-category]').forEach(function (scriptNode) {
      if (scriptNode.dataset.simpleCookieConsentExecuted === "1") {
        return;
      }

      const category = scriptNode.getAttribute("data-wp-consent-category") || "functional";
      if (!hasConsent(category)) {
        return;
      }

      const replacement = document.createElement("script");
      Array.prototype.forEach.call(scriptNode.attributes, function (attribute) {
        if (attribute.name === "type" || attribute.name === "data-wp-consent-category") {
          return;
        }

        if (attribute.name === "data-src") {
          replacement.src = attribute.value;
          return;
        }

        replacement.setAttribute(attribute.name, attribute.value);
      });

      if (!replacement.src) {
        replacement.text = scriptNode.text || scriptNode.textContent || "";
      }

      scriptNode.dataset.simpleCookieConsentExecuted = "1";
      scriptNode.parentNode.insertBefore(replacement, scriptNode);
      scriptNode.remove();
    });

    activateConsentEmbeds();
    blockEmbedsIfNeeded();
  }

  function updateLocalConsent(consentMap) {
    defaultCategories.forEach(function (category) {
      if (Object.prototype.hasOwnProperty.call(consentMap, category)) {
        consentState.categories[category] = consentMap[category] === "allow";
      }
    });

    if (consentState.categories.statistics) {
      consentState.categories["statistics-anonymous"] = true;
    }

    consentState.decisionMade = true;
    syncToggles();
  }

  function dispatchConsentChange(consentMap) {
    document.dispatchEvent(new CustomEvent("wp_listen_for_consent_change", { detail: consentMap }));
  }

  function saveConsent(map) {
    return window.fetch(config.restUrl, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": config.restNonce || ""
      },
      body: JSON.stringify({
        preferences: !!map.preferences,
        statisticsAnonymous: !!map["statistics-anonymous"],
        statistics: !!map.statistics,
        marketing: !!map.marketing
      })
    }).then(function (response) {
      return response.json().then(function (json) {
        if (!response.ok || !json || !json.consent) {
          throw new Error("consent_save_failed");
        }

        return json.consent;
      });
    });
  }

  function getToggleStates() {
    const banner = getBanner();
    const state = {};

    defaultCategories.forEach(function (category) {
      const input = banner ? banner.querySelector('[data-simple-cookie-consent-toggle="' + category + '"]') : null;
      state[category] = !!(input && input.checked);
    });

    return state;
  }

  function setupCookiePanels() {
    document.querySelectorAll("[data-simple-cookie-consent-cookie-row]").forEach(function (row) {
      row.addEventListener("click", function (event) {
        if (event.target.closest("input") || event.target.closest("[data-simple-cookie-consent-cookie-toggle]")) {
          return;
        }

        const category = row.getAttribute("data-simple-cookie-consent-cookie-row");
        const panel = document.querySelector('[data-simple-cookie-consent-cookie-panel="' + category + '"]');
        const isOpen = panel && !panel.classList.contains("scc-hidden");
        setCookiePanelState(category, !isOpen);
      });
    });

    document.querySelectorAll("[data-simple-cookie-consent-cookie-toggle]").forEach(function (button) {
      button.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        const category = button.getAttribute("data-simple-cookie-consent-cookie-toggle");
        const panel = document.querySelector('[data-simple-cookie-consent-cookie-panel="' + category + '"]');
        const isOpen = panel && !panel.classList.contains("scc-hidden");
        setCookiePanelState(category, !isOpen);
      });
    });
  }

  function bindBannerActions() {
    const banner = getBanner();
    const settings = getSettingsPanel();

    if (!banner) {
      return;
    }

    banner.addEventListener("click", function (event) {
      const actionButton = event.target.closest("[data-simple-cookie-consent-action]");

      if (!actionButton) {
        return;
      }

      event.preventDefault();

      const action = actionButton.getAttribute("data-simple-cookie-consent-action");

      if (action === "openSettings") {
        if (settings) {
          setSettingsState(settings.classList.contains("scc-hidden"));
        }
        return;
      }

      let payload = null;

      if (action === "acceptAll") {
        payload = {
          preferences: true,
          "statistics-anonymous": true,
          statistics: true,
          marketing: true
        };
      } else if (action === "rejectAll") {
        payload = {
          preferences: false,
          "statistics-anonymous": false,
          statistics: false,
          marketing: false
        };
      } else if (action === "save") {
        payload = getToggleStates();
      }

      if (!payload) {
        return;
      }

      saveConsent(payload)
        .then(function (consentMap) {
          updateLocalConsent(consentMap);
          dispatchConsentChange(consentMap);
          runBlockedScripts();
          hideBanner(action);
        })
        .catch(function () {
          showBanner(true);
        });
    });
  }

  function bindOpenTriggers() {
    document.addEventListener("click", function (event) {
      const trigger = event.target.closest("[data-simple-cookie-consent-open]");
      if (!trigger) {
        return;
      }

      event.preventDefault();
      showBanner(true);
    });
  }

  function init() {
    window.wp_consent_type = "optin";
    document.dispatchEvent(new CustomEvent("wp_consent_type_defined"));

    renderCookieLists();
    setupCookiePanels();
    bindBannerActions();
    bindOpenTriggers();
    runBlockedScripts();

    if (!consentState.decisionMade && getBanner()) {
      showBanner(false);
    }

    document.addEventListener("wp_listen_for_consent_change", function () {
      runBlockedScripts();
    });
  }

  document.addEventListener("DOMContentLoaded", init);
})(window, document, window.wp);
