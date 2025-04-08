document.addEventListener("DOMContentLoaded", function () {
  // Verifica la presenza del selettore .post-content
  if (!document.querySelector(".post-content")) {
    return; // Interrompi l'esecuzione se .post-content non è presente
  }

  const contentArea = document.querySelector(".post-content");

  contentArea.querySelectorAll("h1").forEach((el) => {
    el.classList.add("heading", "text-3xl", "my-4"); 
  });

  contentArea.querySelectorAll("h2").forEach((el) => {
    el.classList.add("heading", "text-2xl", "my-4");
  });

  contentArea.querySelectorAll("h3").forEach((el) => {
    el.classList.add("heading", "text-xl", "my-3");
  });

  contentArea.querySelectorAll("h4").forEach((el) => {
    el.classList.add("heading", "text-lg", "my-3");
  });

  contentArea.querySelectorAll("p").forEach((el) => {
    el.classList.add("paragraph", "text-base");
  });

  contentArea.querySelectorAll("ul").forEach((el) => {
    el.classList.add("paragraph", "text-base");
  });

  contentArea.querySelectorAll("ol").forEach((el) => {
    el.classList.add("paragraph", "text-base");
  });

  contentArea.querySelectorAll("strong").forEach((el) => {
    el.classList.add("paragraph", "text-base", "bold");
  });

});
