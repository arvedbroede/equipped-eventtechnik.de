<?php
header('Content-Type: application/json');

// === Daten auslesen und validieren ===
$name       = htmlspecialchars($_POST['name'] ?? '');
$email      = htmlspecialchars($_POST['email'] ?? '');
$telefon    = htmlspecialchars($_POST['telefon'] ?? '');
$plz        = htmlspecialchars($_POST['plz'] ?? '');
$termin     = htmlspecialchars($_POST['termin'] ?? '');
$bundle     = htmlspecialchars($_POST['bundle'] ?? 'Nicht angegeben');
$einzelteil = htmlspecialchars($_POST['einzelteile'] ?? 'Keine');
$nachrichtKunde = htmlspecialchars($_POST['nachricht'] ?? '');

if (!$name || !$email || !$termin) {
  echo json_encode(['success' => false, 'message' => 'Fehlende Pflichtfelder']);
  exit;
}

// === E-Mail an Equipped senden ===
$empfaenger = "hello@equipped-eventtechnik.de";
$betreff = "Neue Anfrage von $name";

$nachrichtIntern = "
Neue Anfrage von Equipped Eventtechnik:

Name: $name
E-Mail: $email
Telefon: $telefon
PLZ: $plz
Wunschtermin: $termin
Bundle: $bundle
Einzelteil(e): $einzelteil

Nachricht:
$nachrichtKunde
";

$header  = "From: Anfrage <hello@equipped-eventtechnik.de>\r\n";
$header .= "Reply-To: $email\r\n";

mail($empfaenger, $betreff, $nachrichtIntern, $header);

// === Autoresponder an den Kunden (HTML-Version) ===
$antwortBetreff = "Deine Anfrage bei Equipped! Eventtechnik";
$antwortHeader  = "From: Equipped! Eventtechnik <hello@equipped-eventtechnik.de>\r\n";
$antwortHeader .= "Reply-To: hello@equipped-eventtechnik.de\r\n";
$antwortHeader .= "MIME-Version: 1.0\r\n";
$antwortHeader .= "Content-Type: text/html; charset=UTF-8\r\n";

$antwortText = "
<html>
  <body style='font-family: Arial, sans-serif; color: #333;'>
    <h2 style='color: #ff6b00;'>Vielen Dank für deine Anfrage, $name!</h2>
    <p>Wir melden uns schnellstmöglich bei dir – in der Regel innerhalb von 24 Stunden.</p>
    <hr />
    <h3>Deine Angaben:</h3>
    <ul>
      <li><strong>Wunschtermin:</strong> $termin</li>
      <li><strong>Bundle:</strong> $bundle</li>
      <li><strong>Einzelteil(e):</strong> $einzelteil</li>
      <li><strong>Nachricht:</strong><br>" . nl2br($nachrichtKunde) . "</li>
    </ul>
    <hr />
    <p>Falls du Fragen hast oder etwas ergänzen möchtest, antworte einfach direkt auf diese E-Mail.</p>
    <p style='margin-top: 40px;'>Viele Grüße<br>Dein Team von <strong>Equipped! Eventtechnik GbR</strong></p>
  </body>
</html>
";

mail($email, $antwortBetreff, $antwortText, $antwortHeader);

// === Erfolg zurückgeben ===
echo json_encode(['success' => true]);
exit;
?>
