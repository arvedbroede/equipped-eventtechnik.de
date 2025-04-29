<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kontakt – Equipped! Eventtechnik</title>

  <meta property="og:title" content="Kontakt – Equipped! Eventtechnik" />
  <meta property="og:description" content="Fragen? Wünsche? Jetzt schnell & einfach Kontakt aufnehmen – wir melden uns innerhalb von 24h." />
  <meta property="og:image" content="https://equipped-eventtechnik.de/img/social-preview.png" />
  <meta property="og:url" content="https://equipped-eventtechnik.de/kontakt" />
  <meta property="og:type" content="website" />
  <meta property="og:locale" content="de_DE" />
  <meta property="og:site_name" content="Equipped! Eventtechnik" />

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Kontakt – Equipped! Eventtechnik" />
  <meta name="twitter:description" content="Einfach das Formular ausfüllen oder direkt per Mail schreiben – wir freuen uns auf deine Anfrage." />
  <meta name="twitter:image" content="https://equipped-eventtechnik.de/img/social-preview.png" />

  <meta name="description" content="Nimm Kontakt mit uns auf – unkompliziert & schnell. Wir melden uns zügig zurück und klären alle offenen Fragen zu deinem Event." />
  <link rel="icon" href="frontend/img/logos/EQ! Icon (neu) - trans color.svg" type="image/svg" />
  <link rel="stylesheet" href="frontend/css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script src="frontend/js/kontakt.js" defer></script>
  <script src="frontend/js/script.js" defer></script>
</head>

<body>

  <?php include('frontend/includes/header.php'); ?>

  <main role="main">
  <section class="anfrage" id="anfrage" aria-labelledby="kontakt-heading">
  <div class="container">
    <h3 id="kontakt-heading">Kontakt</h3>
    <p class="anfrage-sub">
      Schreiben Sie uns gerne eine Nachricht über das Kontaktformular oder einfach via Mail an
      <a href="mailto:hello@equipped-eventtechnik.de">
        <span itemprop="email" style="color: #00ffc2; text-decoration: none;">hello@equipped-eventtechnik.de</span>
      </a><br>
      Wir melden uns schnellstmöglichst bei dir – meistens innerhalb von 24 Stunden.
    </p>

    <div id="anfrage-danke" style="display: none;">
      <p><strong>Vielen Dank!</strong> Wir haben deine Nachricht erhalten und melden uns bald bei dir.</p>
    </div>

    <form id="anfrage-form" class="anfrage-form" method="POST" role="form" aria-label="Kontaktformular">
      <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" required placeholder="Max Mustermann" />
      </div>

      <div class="form-group">
        <label for="email">E-Mail *</label>
        <input type="email" id="email" name="email" required placeholder="max@example.com" />
      </div>

      <div class="form-group" style="grid-column: 1 / -1;">
        <label for="nachricht">Nachricht / Anmerkungen</label>
        <textarea id="nachricht" name="nachricht" rows="4" placeholder="Wie können wir dir helfen?"></textarea>
      </div>

      <div class="form-group" style="grid-column: 1 / -1;">
        <label for="datenschutz-checkbox" class="checkbox-label">
          <input type="checkbox" id="datenschutz-checkbox" name="datenschutz" required aria-describedby="ds-hinweis" />
          Ich habe die <a href="datenschutz.php" target="_blank">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zu.
        </label>
        <small id="ds-hinweis" style="display: block; margin-top: 0.25rem; font-size: 0.85rem;">
          Deine Angaben werden verschlüsselt übermittelt und nur zur Bearbeitung deiner Anfrage verwendet.
        </small>
      </div>

      <button type="submit" class="btn">Nachricht senden</button>
    </form>
  </div>
</section>

  </main>

  <?php include('frontend/includes/footer.php'); ?>

</body>
</html>
