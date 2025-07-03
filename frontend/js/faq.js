document.addEventListener("DOMContentLoaded", () => {
  const items = document.querySelectorAll(".faq-item");

  items.forEach(item => {
    const question = item.querySelector(".faq-question");
    question.addEventListener("click", () => {
      // Schließt alle anderen FAQ-Items
      items.forEach(i => {
        if (i !== item) {
          i.classList.remove("open");
        }
      });
      // Öffnet/schließt das geklickte FAQ-Item
      item.classList.toggle("open");
    });
  });
});