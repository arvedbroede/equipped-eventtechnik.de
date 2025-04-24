<?php
$config = require __DIR__ . '/../../backend/private/config.php';
$url = $config['base_url'];
?>

<footer class="site-footer" role="contentinfo">
  <div class="footer-container">
    <div class="footer-column">
      <h4>Equipped! Eventtechnik GbR</h4>
      <p>Miete Sound und Licht.</p>
      <div class="social-icons" role="complementary" aria-label="Social Media Links">
        <a href="https://facebook.com/equippedeventtechnik" title="Folge uns auf Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/equipped_eventtechnik" title="Folge uns auf Instagram"><i class="fab fa-instagram"></i></a>
        <a href="https://www.google.com/search?q=equipped+eventtechnik" title="Erfahre mehr auf Google"><i class="fab fa-google"></i></a>
      </div>
    </div>

    <div class="footer-column">
      <h4>Rechtliches</h4>
      <ul>
        <li><a href="<?= $url ?>/impressum" title="Impressum">Impressum</a></li>
        <li><a href="<?= $url ?>/datenschutz" title="Datenschutz">Datenschutz</a></li>
        <li><a href="<?= $url ?>/agb" title="Allgemeine Geschäftsbedingungen">AGB</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h4>Support</h4>
      <ul>
        <li><a href="<?= $url ?>/kontakt" title="Kontaktiere uns">Kontakt</a></li>
        <li><a href="<?= $url ?>/faq">FAQ</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h4>Zahlungsmethoden bei Lieferung</h4>
      <div class="payment-logos" aria-label="Zahlungsmethoden">
        <i class="fab fa-cc-mastercard" aria-hidden="true"></i>
        <i class="fab fa-google-pay" aria-hidden="true"></i>
        <i class="fab fa-apple-pay" aria-hidden="true"></i>
        <i class="fab fa-paypal" aria-hidden="true"></i>
        <i class="fab fa-cc-visa" aria-hidden="true"></i>
        <span class="bar-zahlung">Bar</span>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>Copyright ©2025 | <span itemprop="name">Equipped! Eventtechnik GbR</span></p>
  </div>
</footer>
