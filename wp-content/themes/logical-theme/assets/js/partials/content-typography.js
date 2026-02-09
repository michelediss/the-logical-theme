document.addEventListener("DOMContentLoaded", function () {
  const contentArea = document.querySelector(".post-content");
  if (!contentArea) {
    return;
  }

  const rules = [
    { selector: "h2", classes: ["heading", "regular", "text-xl", "my-4", "text-secondary"] },
    { selector: "h3", classes: ["heading", "regular", "text-lg", "my-3", "text-secondary"] },
    { selector: "p", classes: ["paragraph", "regular", "text-base", "text-gray"] },
    { selector: "ul", classes: ["paragraph", "regular", "text-base", "text-gray"] },
    { selector: "ol", classes: ["paragraph", "regular", "text-base", "text-gray"] },
    { selector: "strong", classes: ["paragraph", "bold", "text-gray"] },
    { selector: "em", classes: ["paragraph", "italic", "text-gray"] },
    { selector: "a", classes: ["paragraph", "bold", "text-primary"] }
  ];

  rules.forEach((rule) => {
    contentArea.querySelectorAll(rule.selector).forEach((element) => {
      element.classList.add(...rule.classes);
    });
  });
});
