// main.js
if (typeof partialFiles !== 'undefined') {
    // Funzione per caricare uno script e restituire una Promise
    function loadScript(url) {
        return new Promise((resolve) => {
            const script = document.createElement("script");
            script.type = "text/javascript";
            script.src = url;
            script.onload = resolve;
            document.head.appendChild(script);
        });
    }

    // Carica tutti i file in sequenza
    partialFiles.reduce((promise, file) => {
        return promise.then(() => loadScript(file));
    }, Promise.resolve());
} else {
    // Il codice minificato in main.min.js include già tutti i file
    console.log('main.min.js caricato, nessun file aggiuntivo da importare.');
}
