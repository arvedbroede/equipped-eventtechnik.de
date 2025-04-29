document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("bundleForm");
  const auswahlListe = document.getElementById("auswahlListe");
  const gesamtPreis = document.getElementById("gesamtPreis");
  const dankeBox = document.getElementById("danke-nachricht");

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
  updateZusammenfassung();

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    // Auswahl als Textform für PDF zusammenbauen
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
      console.log("Raw response:", text);

      let data = null;
      try {
        data = JSON.parse(text);
        console.log("Parsed JSON:", data);
      } catch (parseErr) {
        console.error("Fehler beim Parsen der Antwort:", parseErr);
      }

      if (data && data.success) {
        form.style.display = "none";
        dankeBox.innerHTML = `
          <div class="toast-success">🎉 Anfrage erfolgreich versendet!</div>
          <p><strong>Vielen Dank!</strong> Deine Konfiguration wurde erfolgreich übermittelt.</p>
          <p>Du bekommst in Kürze eine E-Mail mit deiner Auswahl als PDF.</p>
          <p style="margin-top: 1rem;">
            <button id="neustartBtn" class="btn">Neue Anfrage starten</button>
          </p>
        `;
        dankeBox.style.display = "block";

        setTimeout(() => {
          const toast = document.querySelector('.toast-success');
          if (toast) toast.remove();
        }, 5000);

        document.getElementById("neustartBtn").addEventListener("click", () => {
          form.reset();
          auswahlListe.innerHTML = "";
          gesamtPreis.textContent = "0.00";
          form.style.display = "block";
          dankeBox.style.display = "none";
        });

      } else {
        alert("Fehler: " + (data?.message || "Bitte erneut versuchen."));
      }

    } catch (err) {
      console.error(err);
      alert("Fehler beim Absenden.");
    }
  });
});
