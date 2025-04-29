<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Equipped! Eventtechnik GbR – Mietvertrag erstellen</title>
  
  <meta property="og:title" content="Equipped! Eventtechnik – Bekomme hier direkt deinen Mietvertrag"/>
  <meta property="og:description" content="Egal ob Party oder Hochzeit – wir liefern dir professionelle Eventtechnik zum fairen Preis. Jetzt anfragen!" />
  <meta property="og:image" content="https://equipped-eventtechnik.de/img/social-preview.png" />
  <meta property="og:url" content="https://equipped-eventtechnik.de/mietvertrag" />
  <meta property="og:type" content="website" />
  <meta property="og:locale" content="de_DE" />
  <meta property="og:site_name" content="Equipped! Eventtechnik" />

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Equipped! Eventtechnik – Miete Sound & Licht für dein Event" />
  <meta name="twitter:description" content="Jetzt Licht- & Tontechnik für deine Veranstaltung in Stuttgart und Umgebung mieten – fair, flexibel & einfach." />
  <meta name="twitter:image" content="https://equipped-eventtechnik.de/img/social-preview.png" />

  <meta name="description" content="Miete Licht- & Tontechnik für dein Event – einfach, flexibel & bezahlbar. Equipped! Eventtechnik macht deine Veranstaltung unvergesslich." />
  <link rel="icon" href="frontend/img/logos/EQ! Icon (neu) - trans color.svg" type="image/svg" />
  <link rel="stylesheet" href="frontend/css/style.css" />
  <link rel="stylesheet" href="frontend/css/mietvertrag.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="frontend/js/mietvertrag.js" defer></script>
  <script src="frontend/js/script.js" defer></script>
</head>

<body>

  <?php include('frontend/includes/header.php'); ?>

  <section class="anfrage" id="mietvertrag">
    <div class="container">
      <h3>Mietvertragsanfrage</h3>
      <p class="anfrage-sub">Erstelle deinen Vertrag – schnell, einfach & digital.</p>

      <div id="mietvertrag-danke" style="display: none;">
        <p><strong>Vielen Dank!</strong> Dein Mietvertrag wurde erfolgreich erstellt und versendet.</p>
      </div>

      <form id="mietvertrag-form" class="anfrage-form">
        <div class="form-group">
          <label for="name">Name *</label>
          <input type="text" id="name" name="name" required placeholder="Max Mustermann" />
        </div>

        <div class="form-group">
          <label for="email">E-Mail *</label>
          <input type="email" id="email" name="email" required placeholder="max@example.com" />
        </div>

        <div class="form-group">
          <label for="adresse">Adresse *</label>
          <input type="text" id="adresse" name="adresse" required placeholder="Musterstraße 1, 12345 Musterstadt" />
        </div>

        <div class="form-group">
          <label for="telefon">Telefonnummer *</label>
          <input type="tel" id="telefon" name="telefon" required placeholder="0157 12345678" />
        </div>

        <div class="form-group">
          <label for="bundle">Gewünschtes Bundle *</label>
          <select id="bundle" name="bundle" required>
            <option value="">Bitte wählen</option>
            <option value="partybundle">Party Bundle</option>
            <option value="djset">DJ-Set</option>
            <option value="lichtset">Lichtset</option>
          </select>
        </div>

        <div class="form-group">
          <label for="mietbeginn">Mietbeginn *</label>
          <input type="datetime-local" id="mietbeginn" name="mietbeginn" required />
        </div>

        <div class="form-group">
          <label for="mietende">Mietende *</label>
          <input type="datetime-local" id="mietende" name="mietende" required />
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="nachricht">Zusätzliche Anmerkungen</label>
          <textarea id="nachricht" name="nachricht" rows="4" placeholder="Sonstiges, z. B. Lieferwunsch oder Bemerkungen"></textarea>
        </div>

        <button type="submit" class="btn">Mietvertrag erstellen</button>
      </form>
    </div>
  </section>

  <?php include('frontend/includes/footer.php'); ?>

</body>
</html>
