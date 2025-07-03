document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("bundleForm");
  const auswahlListe = document.getElementById("auswahlListe");
  const gesamtPreis = document.getElementById("gesamtPreis");
  const dankeBox = document.getElementById("danke-nachricht");

  // --- Neue Funktion: Benutzerfreundliche Toast-Nachrichten anzeigen (Konsistenz mit anfrage.js) ---
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


  function updateZusammenfassung() {
    const inputs = form.querySelectorAll('input[type="number"][data-name]');
    let sum = 0;
    auswahlListe.innerHTML = "";
    inputs.forEach(input => {
      const menge = parseInt(input.value) || 0;
      const name = input.dataset.name;
      const preis = parseFloat(input.dataset.preis);
      if (menge > 0) {
        const gesamt = menge * preis;
        sum += gesamt;
        const li = document.createElement("li");
        li.textContent = `${name} × ${menge} (${gesamt.toFixed(2)} €)`;
        auswahlListe.appendChild(li);
      }
    });
    gesamtPreis.textContent = sum.toFixed(2);
  }

  form.addEventListener("input", updateZusammenfassung);
  updateZusammenfassung(); // Initialer Aufruf beim Laden der Seite

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const inputs = form.querySelectorAll('input[type="number"][data-name]');
    const auswahlArray = [];
    let summe = 0;

    inputs.forEach(input => {
      const menge = parseInt(input.value) || 0;
      const name = input.dataset.name;
      const preis = parseFloat(input.dataset.preis);
      if (menge > 0) {
        auswahlArray.push(`${name} × ${menge}`);
        summe += menge * preis;
      }
    });

    formData.append("auswahl", auswahlArray.join(", "));
    formData.append("gesamtpreis", summe.toFixed(2));

    try {
      const res = await fetch("/backend/api/bundle-request.php", {
        method: "POST",
        body: formData
      });

      const text = await res.text();
      // console.log("Raw response:", text); // Entfernt für Produktion

      let data = null;
      try {
        data = JSON.parse(text);
        // console.log("Parsed JSON:", data); // Entfernt für Produktion
      } catch (parseErr) {
        // console.error("Fehler beim Parsen der Antwort:", parseErr); // Entfernt für Produktion
        showToast("Die Server-Antwort konnte nicht verarbeitet werden.", 'error');
        return; // Wichtig: Hier abbrechen, da die Antwort nicht valide ist
      }

      if (data && data.success) {
        form.style.display = "none";
        dankeBox.innerHTML = `
          <p><strong>Vielen Dank!</strong> Deine Konfiguration wurde erfolgreich übermittelt.</p>
          <p>Du bekommst in Kürze eine E-Mail mit deiner Auswahl als PDF.</p>
          <p style="margin-top: 1rem;">
            <button id="neustartBtn" class="btn">Neue Anfrage starten</button>
          </p>
        `;
        dankeBox.style.display = "block";

        showToast("Anfrage erfolgreich versendet! Du erhältst in Kürze eine E-Mail mit deiner Auswahl als PDF.", 'success'); // Nutzt die showToast Funktion

        document.getElementById("neustartBtn").addEventListener("click", () => {
          form.reset();
          auswahlListe.innerHTML = "";
          gesamtPreis.textContent = "0.00";
          form.style.display = "block";
          dankeBox.style.display = "none";
          // Optional: Wenn du das Toast wieder einblenden möchtest, wenn das Formular neu gestartet wird,
          // oder einfach um sicherzustellen, dass keine alten Toasts mehr da sind:
          const currentToast = document.querySelector('.custom-toast');
          if (currentToast) currentToast.remove();
        });
      } else {
        showToast("Fehler: " + (data?.message || "Bitte erneut versuchen."), 'error'); // Ersetzt alert()
      }
    } catch (err) {
      // console.error(err); // Entfernt für Produktion
      showToast("Ein unerwarteter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.", 'error'); // Ersetzt alert()
    }
  });
});