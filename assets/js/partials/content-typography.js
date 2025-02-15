document.addEventListener("DOMContentLoaded", function () {
  // Verifica la presenza del selettore .post-content
  if (!document.querySelector(".post-content")) {
    return; // Interrompi l'esecuzione se .post-content non è presente
  }

  const contentArea = document.querySelector(".post-content");

  contentArea.querySelectorAll("h1").forEach((el) => {
    el.classList.add("heading", "text-3xl", "mt-4"); 
  });

  contentArea.querySelectorAll("h2").forEach((el) => {
    el.classList.add("heading", "text-2xl", "mt-4");
  });

  contentArea.querySelectorAll("h3").forEach((el) => {
    el.classList.add("heading", "text-xl", "mt-4");
  });

  contentArea.querySelectorAll("h4").forEach((el) => {
    el.classList.add("heading", "text-lg", "mt-4");
  });

  contentArea.querySelectorAll("p").forEach((el) => {
    el.classList.add("paragraph", "text-base", "text-2xl-lg");
  });

  contentArea.querySelectorAll("ul").forEach((el) => {
    el.classList.add("paragraph", "text-base", "text-2xl-lg");
  });

  contentArea.querySelectorAll("ol").forEach((el) => {
    el.classList.add("paragraph", "text-base", "text-2xl-lg");
  });

  contentArea.querySelectorAll("strong").forEach((el) => {
    el.classList.add("paragraph", "bold");
  });

});
