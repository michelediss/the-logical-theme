// Registra il plugin ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

// Assicuriamoci che il DOM sia caricato
window.addEventListener("DOMContentLoaded", () => {
  // Iteriamo su ogni `.row`
  document.querySelectorAll(".row").forEach((row, index) => {
    // Crea il trigger individuale per ogni riga con `once: true`
    ScrollTrigger.create({
      trigger: row,
      start: "top 80%",
      once: true, // L'animazione si attiverà solo la prima volta
      onEnter: () => {
        gsap.fromTo(
          row.querySelectorAll(":scope > *"),
          { opacity: 0, y: 20 }, // Iniziale
          { opacity: 1, y: 0, duration: 0.6, stagger: 0.2 } // Finale
        );
      },
    });
  });
});


// Esegui il codice dopo che il DOM è caricato
document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.querySelector("#navbar-scroll");

  // Crea un ScrollTrigger per gestire la visibilità della navbar
  ScrollTrigger.create({
    trigger: "body",
    start: "top -100", // Inizia quando si scrolla di 100px
    onEnter: () => navbar.classList.add("visible"),
    onLeaveBack: () => navbar.classList.remove("visible"),
  });
});
