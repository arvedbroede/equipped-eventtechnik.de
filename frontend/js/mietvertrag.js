document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("mietvertrag-form"); // Formular-ID
  const danke = document.getElementById("mietvertrag-danke"); // Dankes-Box ID

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

    const formData = new FormData(form);

    fetch("/backend/api/mietvertrag.php", { // Ziel-API-Endpunkt für den Mietvertrag
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
        showToast("Ihr Mietvertrag wurde erfolgreich versendet. Vielen Dank!", 'success'); // Zeigt Erfolgs-Toast an
      } else {
        // console.error("API Fehler:", data.message); // Entfernt für Produktion
        showToast("Fehler: " + (data.message || "Bitte versuche es erneut."), 'error'); // Ersetzt alert()
      }
    })
    .catch(error => {
      // console.error("Fehler beim Senden des Formulars:", error); // Entfernt für Produktion
      showToast("Es gab ein Problem beim Senden des Mietvertrags. Bitte versuchen Sie es später erneut.", 'error'); // Ersetzt alert()
    });
  });
});