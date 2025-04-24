<header role="banner">

  <?php
  // Konfigurationsdaten laden
  $config = require __DIR__ . '/../../backend/private/config.php';
  $url = $config['base_url']; // Basis-URL (Root-Ordner)
  ?>

  <div class="container">
    <a href="<?= $url ?>" class="logo-link">
      <img src="<?= $url ?>/frontend/img/logos/EQ! Logo (neu) - trans color (3000 x 800 px).svg" alt="Equipped! Eventtechnik Logo" class="logo" title="Equipped! Eventtechnik GbR" />
    </a>

    <nav role="navigation" aria-label="Hauptnavigation">
      <ul>
        <li><a href="<?= $url ?>/index">Start</a></li>
        <li><a href="<?= $url ?>/bundles">Bundles</a></li>
        <li><a href="<?= $url ?>/equipment">Equipment</a></li>
        <li><a href="<?= $url ?>/über-uns">Über Uns</a></li>
        <li><a href="<?= $url ?>/faq">FAQ</a></li>
        <li><a href="<?= $url ?>/kontakt">Kontakt</a></li>
      </ul>
    </nav>

    <div class="burger" id="burger" aria-hidden="true">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>

  <div class="mobile-nav" id="mobileNav" role="navigation" aria-label="Mobile Navigation">
    <div class="close-btn" id="closeBtn" aria-label="Navigation schließen">&times;</div>
    <ul>
      <li><a href="<?= $url ?>/index">Start</a></li>
      <li><a href="<?= $url ?>/bundles">Bundles</a></li>
      <li><a href="<?= $url ?>/equipment">Equipment</a></li>
      <li><a href="<?= $url ?>/über-uns">Über Uns</a></li>
      <li><a href="<?= $url ?>/faq">FAQ</a></li>
      <li><a href="<?= $url ?>/kontakt">Kontakt</a></li>
    </ul>
  </div>

  <div class="mobile-overlay" id="mobileOverlay" aria-hidden="true"></div>
</header>
