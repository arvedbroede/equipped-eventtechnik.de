document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("anfrage-form");
  const danke = document.getElementById("anfrage-danke");
  const checkbox = document.getElementById("datenschutz-checkbox");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    // DSGVO-Zustimmung prüfen
    if (!checkbox.checked) {
      checkbox.focus();
      checkbox.parentElement.style.outline = "2px solid #ff0000";
      checkbox.parentElement.style.padding = "0.5rem";
      alert("Bitte stimme der Datenschutzerklärung zu, um fortzufahren.");
      return;
    } else {
      checkbox.parentElement.style.outline = "none";
    }

    const formData = new FormData(form);

    fetch("/backend/api/anfrage.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        form.style.display = "none";
        danke.style.display = "block";
      } else {
        alert("Fehler: " + (data.message || "Bitte versuche es erneut."));
      }
    })
    .catch(error => {
      console.error("Fehler beim Senden:", error);
      alert("Fehler beim Senden der Anfrage.");
    });
  });
});
