// Search form functionality - compatible with Barba.js
(function() {
    'use strict';
  
    // Store event handlers to prevent duplicates
    let searchHandlers = new WeakMap();
  
    // Initialize search forms
    function initSearchForms() {
      // Seleziona tutti i contenitori dei moduli di ricerca
      const searchForms = document.querySelectorAll('.search-form');
  
      searchForms.forEach(function(searchForm) {
        // Skip if already initialized
        if (searchHandlers.has(searchForm)) {
          return;
        }
  
        // Seleziona gli elementi all'interno di questo modulo di ricerca
        const inputGroup = searchForm.querySelector('.input-group');
        const inputSearch = searchForm.querySelector('.input-search');
        const buttonSearch = searchForm.querySelector('.button-search');
        const overlaySearch = searchForm.querySelector('.overlay-search');
  
        // Funzione per aggiungere la classe .active a questo modulo
        function addActive() {
          if (inputSearch) {
            inputSearch.classList.add('active');
          }
          if (buttonSearch) {
            buttonSearch.classList.add('active');
          }
          if (overlaySearch) {
            overlaySearch.classList.add('active');
          }
        }
  
        // Funzione per rimuovere la classe .active da questo modulo
        function removeActive() {
          if (inputSearch) {
            inputSearch.classList.remove('active');
          }
          if (buttonSearch) {
            buttonSearch.classList.remove('active');
          }
          if (overlaySearch) {
            overlaySearch.classList.remove('active');
          }
        }
  
        // Handler per il click sull'input group
        function inputGroupClickHandler(event) {
          event.stopPropagation();
          // Prima, rimuovi .active da tutti i moduli tranne questo
          const allForms = document.querySelectorAll('.search-form');
          allForms.forEach(function(otherForm) {
            if (otherForm !== searchForm) {
              const otherInputSearch = otherForm.querySelector('.input-search');
              const otherButtonSearch = otherForm.querySelector('.button-search');
              const otherOverlaySearch = otherForm.querySelector('.overlay-search');
              if (otherInputSearch) otherInputSearch.classList.remove('active');
              if (otherButtonSearch) otherButtonSearch.classList.remove('active');
              if (otherOverlaySearch) otherOverlaySearch.classList.remove('active');
            }
          });
          // Aggiungi .active a questo modulo
          addActive();
        }
  
        // Handler per ESC key
        function escKeyHandler(event) {
          if (event.key === 'Escape' || event.key === 'Esc') {
            removeActive();
          }
        }
  
        // Handler per click fuori dal form
        function outsideClickHandler(event) {
          if (!searchForm.contains(event.target)) {
            removeActive();
          }
        }
  
        // Aggiungi event listeners
        if (inputGroup) {
          inputGroup.addEventListener('click', inputGroupClickHandler);
        }
        document.addEventListener('keydown', escKeyHandler);
        document.addEventListener('click', outsideClickHandler);
  
        // Store handlers for cleanup
        searchHandlers.set(searchForm, {
          inputGroup: inputGroup,
          inputGroupClickHandler: inputGroupClickHandler,
          escKeyHandler: escKeyHandler,
          outsideClickHandler: outsideClickHandler
        });
      });
  
    }
  
    // Cleanup function to remove event listeners
    function cleanupSearchForms() {
      const searchForms = document.querySelectorAll('.search-form');
      searchForms.forEach(function(searchForm) {
        const handlers = searchHandlers.get(searchForm);
        if (handlers) {
          if (handlers.inputGroup) {
            handlers.inputGroup.removeEventListener('click', handlers.inputGroupClickHandler);
          }
          document.removeEventListener('keydown', handlers.escKeyHandler);
          document.removeEventListener('click', handlers.outsideClickHandler);
          searchHandlers.delete(searchForm);
        }
      });
    }
  
    // Initialize on DOMContentLoaded
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initSearchForms);
    } else {
      initSearchForms();
    }
  
    // Reinitialize after Barba.js transitions
    document.addEventListener('barba:transition:complete', function() {
      cleanupSearchForms();
      initSearchForms();
    });
  
    // Expose cleanup function globally for manual cleanup if needed
    window.searchFormsCleanup = cleanupSearchForms;
    window.searchFormsInit = initSearchForms;
  
  })();
  