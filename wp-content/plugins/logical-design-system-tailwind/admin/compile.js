(function ($) {
  function runAction(action, okMessage, failMessage) {
    if (!window.ldsTw || !window.ldsTw.ajaxUrl || !window.ldsTw.nonce) {
      window.alert('LDS Tailwind config missing.');
      return;
    }

    const data = {
      action,
      security: window.ldsTw.nonce
    };

    $.post(window.ldsTw.ajaxUrl, data)
      .done(function (response) {
        if (response && response.success) {
          window.alert(response.data || okMessage);
          return;
        }
        window.alert((response && response.data) || failMessage);
      })
      .fail(function () {
        window.alert('LDS Tailwind AJAX request failed.');
      });
  }

  function triggerCompile(event) {
    event.preventDefault();
    runAction('lds_tw_compile', 'LDS Tailwind compiled successfully.', 'LDS Tailwind compilation failed.');
  }

  function triggerBuildJs(event) {
    event.preventDefault();
    runAction('lds_tw_build_theme_js', 'Theme JS build completed.', 'Theme JS build failed.');
  }

  $(document).on('click', '#wp-admin-bar-compile_lds_tw > a', triggerCompile);
  $(document).on('click', '#wp-admin-bar-build_theme_js > a', triggerBuildJs);
})(jQuery);
