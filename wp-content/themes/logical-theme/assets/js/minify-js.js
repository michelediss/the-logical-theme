document.addEventListener('DOMContentLoaded', function() {
    window.triggerMinifyJS = function(e) {
        e.preventDefault(); // Previene l'azione predefinita del link

        // Visualizza un messaggio di caricamento
        document.querySelector('#wp-admin-bar-minify_js_button .ab-item').textContent = 'Minifying...';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxurl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Messaggio di successo
                    document.querySelector('#wp-admin-bar-minify_js_button .ab-item').textContent = 'Minify JS';
                    alert(response.data); // Opzionalmente mostra un alert di successo
                } else {
                    // Messaggio di errore
                    document.querySelector('#wp-admin-bar-minify_js_button .ab-item').textContent = 'Minify JS';
                    alert(response.data); // Opzionalmente mostra un alert di errore
                }
            } else {
                // Messaggio di errore generico
                document.querySelector('#wp-admin-bar-minify_js_button .ab-item').textContent = 'Minify JS';
                alert('Errore durante l\'elaborazione della richiesta.');
            }
        };

        xhr.onerror = function() {
            document.querySelector('#wp-admin-bar-minify_js_button .ab-item').textContent = 'Minify JS';
            alert('Errore durante l\'elaborazione della richiesta.');
        };

        var params = 'action=minify_js&security=' + encodeURIComponent(minify_js_data.nonce);
        xhr.send(params);
    };
});
