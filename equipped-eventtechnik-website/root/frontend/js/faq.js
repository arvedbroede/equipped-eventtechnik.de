document.addEventListener("DOMContentLoaded", () => {
    const items = document.querySelectorAll(".faq-item");
  
    items.forEach(item => {
      const question = item.querySelector(".faq-question");
  
      question.addEventListener("click", () => {
        // Alle Antworten schließen
        items.forEach(i => {
          if (i !== item) {
            i.classList.remove("open");
          }
        });
  
        // Toggle aktuelle Antwort
        item.classList.toggle("open");
      });
    });
  });
  