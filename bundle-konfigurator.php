<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bundle-Konfigurator – Equipped! Eventtechnik GbR</title>
  <meta name="description" content="Konfiguriere dein eigenes Licht- & Tontechnik-Bundle online – flexibel, einfach und direkt anfragbar. Perfekt für Partys, Hochzeiten & Events.">
  <meta property="og:title" content="Bundle-Konfigurator – Stelle dir dein Wunschpaket zusammen">
  <meta property="og:description" content="Stell dir dein individuelles Bundle aus Licht- und Tontechnik zusammen. Jetzt anfragen bei Equipped! Eventtechnik.">
  <meta property="og:image" content="https://equipped-eventtechnik.de/img/social-preview.png">
  <meta property="og:url" content="https://equipped-eventtechnik.de/bundle-konfigurator">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="de_DE">
  <meta property="og:site_name" content="Equipped! Eventtechnik">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Bundle-Konfigurator – Stelle dir dein Wunschpaket zusammen">
  <meta name="twitter:description" content="Jetzt Bundle aus Licht- & Tontechnik individuell zusammenstellen – mit Echtzeit-Preisvorschau & direkter Anfrage.">
  <meta name="twitter:image" content="https://equipped-eventtechnik.de/img/social-preview.png">
  <link rel="icon" href="frontend/img/logos/EQ! Icon (neu) - trans color.svg" type="image/svg+xml">
  <link rel="stylesheet" href="frontend/css/style.css">
  <link rel="stylesheet" href="frontend/css/bundle-konfigurator.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <script src="frontend/js/bundle-konfigurator.js" defer></script>
  <script src="frontend/js/script.js" defer></script>
  <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-61ZH3W30V8"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-61ZH3W30V8');
</script>
</head>
<body>
  <?php include_once 'frontend/includes/header.php'; ?>
  <main class="bundle-konfigurator container">
    <h1>Bundle-Konfigurator</h1>
    <p class="subline">Stell dir dein individuelles Eventtechnik-Bundle aus unseren Kategorien zusammen.</p>
    <form id="bundleForm" class="bundle-form">
      <fieldset>
        <legend>🎤 Ton</legend>
        <label>Sony GTK-XB72 <input type="number" name="menge[Sony GTK-XB72]" min="0" max="2" value="0" data-name="Sony GTK-XB72" data-preis="15"></label>
        <label>Hughes & Kettner C152 Lautsprecher <input type="number" name="menge[Hughes & Kettner C152 Lautsprecher]" min="0" max="2" value="0" data-name="Hughes & Kettner C152 Lautsprecher" data-preis="15"></label>
        <label>Magnum Standard Subwoofer <input type="number" name="menge[Magnum Standard Subwoofer]" min="0" max="2" value="0" data-name="Magnum Standard Subwoofer" data-preis="20"></label>
        <label>Mixars Primo DJ-Controller <input type="number" name="menge[Mixars Primo]" min="0" max="1" value="0" data-name="Mixars Primo DJ-Controller" data-preis="20"></label>
        <label>the t.mix xmix 1202 FXMP USB <input type="number" name="menge[the t.mix xmix 1202 FXMP USB]" min="0" max="1" value="0" data-name="the t.mix xmix 1202 FXMP USB" data-preis="10"></label>
        <label>Aokeo Mikrofon <input type="number" name="menge[Aokeo Mikrofon]" min="0" max="3" value="0" data-name="Aokeo Mikrofon" data-preis="5"></label>
      </fieldset>
      <fieldset>
        <legend>💡 Licht</legend>
        <label>Stairville CLB4 RGB Compact LED Bar 4 <input type="number" name="menge[Stairville CLB4 RGB Compact LED Bar 4]" min="0" max="1" value="0" data-name="Stairville CLB4 RGB Compact LED Bar 4" data-preis="25"></label>
      </fieldset>
      <fieldset>
        <legend>🔧 Zubehör</legend>
        <label>Superlux MS-108E Mikrofonstativ <input type="number" name="menge[Superlux MS-108E Mikrofonstativ]" min="0" max="2" value="0" data-name="Superlux MS-108E Mikrofonstativ" data-preis="3"></label>
        <label>Accu Stand Pro Event Table 2 <input type="number" name="menge[Accu Stand Pro Event Table 2]" min="0" max="1" value="0" data-name="Accu Stand Pro Event Table 2" data-preis="10"></label>
        <label>Millenium Laptopstand White <input type="number" name="menge[Millenium Laptopstand White]" min="0" max="1" value="0" data-name="Millenium Laptopstand White" data-preis="2"></label>
      </fieldset>
      <div id="zusammenfassung" class="bundle-summary">
        <h3>Deine Auswahl</h3>
        <ul id="auswahlListe"></ul>
        <p><strong>Gesamtpreis:</strong> <span id="gesamtPreis">0</span> €</p>
      </div>
      <fieldset class="form-block">
        <legend>📧 Deine Angaben</legend>
        <label for="name">Name *</label>
        <input type="text" name="name" id="name" required placeholder="Max Mustermann">
        <label for="email">E-Mail *</label>
        <input type="email" name="email" id="email" required placeholder="max@example.com">
        <label for="nachricht">Nachricht / Anmerkungen</label>
        <textarea name="nachricht" id="nachricht" rows="4" placeholder="Hast du besondere Wünsche?"></textarea>
      </fieldset>
      <button type="submit" class="btn">Anfrage absenden</button>
    </form>
    <div id="danke-nachricht" style="display:none;">
      <p><strong>Vielen Dank!</strong> Deine Anfrage wurde erfolgreich versendet.</p>
    </div>
  </main>
  <?php include_once 'frontend/includes/footer.php'; ?>
  <?php include('frontend/includes/live-chat.php'); ?>
</body>
</html>