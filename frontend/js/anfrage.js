document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("anfrage-form");
  const danke = document.getElementById("anfrage-danke");
  const checkbox = document.getElementById("datenschutz-checkbox");
  const liveChat = document.querySelector(".live-chat"); // Nehme an, dass du einen Live-Chat hast, der ausgeblendet werden soll
  const footer = document.querySelector("footer"); // Und den Footer

  // --- Neue Funktion: Benutzerfreundliche Toast-Nachrichten anzeigen ---
  function showToast(message, type = 'success', duration = 5000) {
    // Wenn schon ein Toast existiert, entfernen wir ihn zuerst
    const existingToast = document.querySelector('.custom-toast');
    if (existingToast) {
      existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.className = `custom-toast toast-${type}`; // Erstelle Klassen wie 'custom-toast toast-success' oder 'custom-toast toast-error'
    toast.textContent = message;

    // Positioniere den Toast (z.B. oben rechts oder zentriert unten)
    // Für dieses Beispiel fügen wir es direkt in den Body ein
    document.body.appendChild(toast);

    // Füge eine Klasse für die Animation hinzu, damit es sichtbar wird
    setTimeout(() => {
      toast.classList.add('show');
    }, 10); // Kleine Verzögerung, um die CSS-Animation auszulösen

    // Entferne den Toast nach einer bestimmten Zeit
    setTimeout(() => {
      toast.classList.remove('show');
      toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    }, duration);
  }

  // --- CSS für den Custom Toast (füge dies zu deiner style.css hinzu) ---
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
  // --- Ende der CSS-Ergänzung ---


  form.addEventListener("submit", function(e) {
    e.preventDefault();

    if (!checkbox.checked) {
      checkbox.focus();
      checkbox.parentElement.style.outline = "2px solid #ff0000";
      checkbox.parentElement.style.padding = "0.5rem";
      showToast("Bitte stimme der Datenschutzerklärung zu, um fortzufahren.", 'error'); // Ersetzt alert()
      return;
    } else {
      checkbox.parentElement.style.outline = "none";
    }

    const formData = new FormData(form);

    fetch("/backend/api/anfrage.php", {
      method: "POST",
      body: formData
    })
    .then(response => {
      if (!response.ok) {
        // Bei einem HTTP-Fehler (z.B. 404, 500) direkt Fehler werfen
        // console.error wird für Produktionscode entfernt, aber hier nützlich für die Fehlerquelle
        // console.error("HTTP-Fehler:", response.status, response.statusText);
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        form.style.display = "none";
        danke.style.display = "block";
        // Blende Live-Chat und Footer aus, falls gewünscht
        if (liveChat) liveChat.style.display = "none";
        if (footer) footer.style.display = "none";
        showToast("Ihre Anfrage wurde erfolgreich gesendet. Vielen Dank!", 'success'); // Ersetzt alert()
      } else {
        // console.error("API Fehler:", data.message); // Entfernt für Produktion
        showToast("Fehler: " + (data.message || "Bitte versuche es erneut."), 'error'); // Ersetzt alert()
      }
    })
    .catch(error => {
      // console.error("Fehler beim Senden der Anfrage:", error); // Entfernt für Produktion
      showToast("Es gab ein Problem beim Senden der Anfrage. Bitte versuchen Sie es später erneut.", 'error'); // Ersetzt alert()
    });
  });
});