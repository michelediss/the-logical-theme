(function ($) {
  function triggerCompile(event) {
    event.preventDefault();

    if (!window.ldsTw || !window.ldsTw.ajaxUrl || !window.ldsTw.nonce) {
      window.alert('LDS Tailwind config missing.');
      return;
    }

    const data = {
      action: 'lds_tw_compile',
      security: window.ldsTw.nonce
    };

    $.post(window.ldsTw.ajaxUrl, data)
      .done(function (response) {
        if (response && response.success) {
          window.alert(response.data || 'LDS Tailwind compiled successfully.');
          return;
        }
        window.alert((response && response.data) || 'LDS Tailwind compilation failed.');
      })
      .fail(function () {
        window.alert('LDS Tailwind AJAX request failed.');
      });
  }

  $(document).on('click', '#wp-admin-bar-compile_lds_tw > a', triggerCompile);
})(jQuery);
