document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("anfrage-form"); // Beachte: ID hier ist auch "anfrage-form", nicht "kontakt-form"
  const danke = document.getElementById("anfrage-danke"); // Beachte: ID hier ist auch "anfrage-danke"
  const checkbox = document.getElementById("datenschutz-checkbox");

  // --- Funktion: Benutzerfreundliche Toast-Nachrichten anzeigen (Konsistenz) ---
  // Füge dieses CSS in deine frontend/css/style.css hinzu, falls noch nicht geschehen:
  /*
  .custom-toast {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(100px); // Startposition unterhalb des Bildschirms
    opacity: 0;
    visibility: hidden;
    padding: 15px 25px;
    border-radius: 8px;
    color: #fff;
    font-weight: 600;
    z-index: 10000;
    transition: all 0.5s ease-out; // Sanfte Animation
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  }

  .custom-toast.show {
    transform: translateX(-50%) translateY(0); // Endposition
    opacity: 1;
    visibility: visible;
  }

  .toast-success {
    background-color: #00ffc2; // Dein vorhandenes success-Grün
    color: #0a0c27; // Passende Textfarbe
  }

  .toast-error {
    background-color: #ff4d4d; // Ein Beispiel-Rot für Fehler
    color: #ffffff;
  }
  */
  function showToast(message, type = 'success', duration = 5000) {
    const existingToast = document.querySelector('.custom-toast');
    if (existingToast) {
      existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.className = `custom-toast toast-${type}`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('show');
    }, 10);

    setTimeout(() => {
      toast.classList.remove('show');
      toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    }, duration);
  }
  // --- Ende showToast Funktion ---


  form.addEventListener("submit", function(e) {
    e.preventDefault();

    if (!checkbox.checked) {
      checkbox.focus();
      checkbox.parentElement.style.outline = "2px solid #ff0000";
      checkbox.parentElement.style.padding = "0.5rem"; // Behält den Padding-Stil bei
      showToast("Bitte stimme der Datenschutzerklärung zu, um fortzufahren.", 'error'); // Ersetzt alert()
      return;
    } else {
      checkbox.parentElement.style.outline = "none";
      checkbox.parentElement.style.padding = "0"; // Setzt Padding zurück, wenn Outline entfernt wird
    }

    const formData = new FormData(form);

    fetch("/backend/api/kontakt.php", { // Ziel-API-Endpunkt für das Kontaktformular
      method: "POST",
      body: formData
    })
    .then(response => {
      if (!response.ok) {
        // Bei einem HTTP-Fehler (z.B. 404, 500) direkt Fehler werfen
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        form.style.display = "none";
        danke.style.display = "block";
        showToast("Ihre Nachricht wurde erfolgreich gesendet. Vielen Dank!", 'success'); // Zeigt Erfolgs-Toast an
      } else {
        // console.error("API Fehler:", data.message); // Entfernt für Produktion
        showToast("Fehler: " + (data.message || "Bitte versuchen Sie es erneut."), 'error'); // Ersetzt alert()
      }
    })
    .catch(error => {
      // console.error("Fehler beim Senden der Anfrage:", error); // Entfernt für Produktion
      showToast("Es gab ein Problem beim Senden Ihrer Nachricht. Bitte versuchen Sie es später erneut.", 'error'); // Ersetzt alert()
    });
  });
});