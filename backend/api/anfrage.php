<?php
// PHP-Fehlerbehandlung für den Produktionseinsatz
// Fehler NICHT im Browser anzeigen, aber zur Protokollierung aktivieren
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Optional: Fehler in eine eigene Log-Datei schreiben (für die Produktion sehr empfohlen!)
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log'); // Pfad zu deiner Log-Datei anpassen
// Stelle sicher, dass der 'logs'-Ordner existiert und vom Webserver beschreibbar ist
// und NICHT öffentlich über den Browser erreichbar ist!

header('Content-Type: application/json');

// Standardmäßige Fehler- und Exception-Handler, die JSON zurückgeben
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // Dieser Fehler-Typ ist nicht im error_reporting enthalten, also ignorieren
        return false;
    }
    http_response_code(500); // Interner Serverfehler
    echo json_encode(['success' => false, 'message' => 'Ein interner Fehler ist aufgetreten (E_ERROR): ' . $errstr]);
    exit();
});

set_exception_handler(function ($exception) {
    http_response_code(500); // Interner Serverfehler
    echo json_encode(['success' => false, 'message' => 'Eine unerwartete Ausnahme ist aufgetreten: ' . $exception->getMessage()]);
    exit();
});

// PHPMailer und Konfigurationsdateien einbinden
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Wird für SMTP-Verbindung benötigt

// Pfade zu den PHPMailer-Klassen und zur Konfiguration anpassen
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php'; // Für SMTP-Verbindung
require_once __DIR__ . '/../private/config.php'; // Deine Konfigurationsdatei mit SMTP-Daten

// === Daten auslesen und validieren ===
$name        = htmlspecialchars($_POST['name'] ?? '');
$email       = htmlspecialchars($_POST['email'] ?? '');
$telefon     = htmlspecialchars($_POST['telefon'] ?? '');
$plz         = htmlspecialchars($_POST['plz'] ?? '');
$termin      = htmlspecialchars($_POST['termin'] ?? '');
$bundle      = htmlspecialchars($_POST['bundle'] ?? 'Nicht angegeben');
$einzelteil  = htmlspecialchars($_POST['einzelteile'] ?? 'Keine');
$nachrichtKunde = htmlspecialchars($_POST['nachricht'] ?? '');

// Zusätzliche Validierung für E-Mail-Format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Ungültiges E-Mail-Format.']);
    exit;
}

if (!$name || !$email || !$termin) {
    echo json_encode(['success' => false, 'message' => 'Fehlende Pflichtfelder: Name, E-Mail und Wunschtermin sind erforderlich.']);
    exit;
}

// === E-Mail an Equipped! Eventtechnik senden (PHPMailer) ===
$mailIntern = new PHPMailer(true); // true = Exceptions aktivieren
try {
    // SMTP-Konfiguration aus config.php
    $mailIntern->isSMTP();
    $mailIntern->Host       = SMTP_HOST;
    $mailIntern->SMTPAuth   = true;
    $mailIntern->Username   = SMTP_USER;
    $mailIntern->Password   = SMTP_PASS;
    $mailIntern->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Oder PHPMailer::ENCRYPTION_STARTTLS
    $mailIntern->Port       = SMTP_PORT;

    $mailIntern->setFrom('hello@equipped-eventtechnik.de', 'Anfrage'); // Absender der internen Mail
    $mailIntern->addAddress('hello@equipped-eventtechnik.de'); // Empfänger der internen Mail
    $mailIntern->addReplyTo($email, $name); // Antwortadresse des Kunden

    $mailIntern->CharSet = PHPMailer::CHARSET_UTF8;
    $mailIntern->isHTML(false); // Kein HTML für die interne Nachricht

    $mailIntern->Subject = "Neue Anfrage von $name (Equipped! Eventtechnik)";
    $mailIntern->Body    = "
Neue Anfrage von Equipped! Eventtechnik:

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
    $mailIntern->send();

} catch (Exception $e) {
    // Fehler beim Senden der internen E-Mail protokollieren
    error_log("Fehler beim Senden der internen E-Mail (Anfrage.php): " . $mailIntern->ErrorInfo);
    http_response_code(500); // Interner Serverfehler
    echo json_encode(['success' => false, 'message' => 'Ein Fehler ist beim Senden der internen E-Mail aufgetreten.']);
    exit;
}

// === Autoresponder an den Kunden senden (PHPMailer) ===
$mailKunde = new PHPMailer(true);
try {
    // SMTP-Konfiguration (oft dieselbe wie für interne Mails)
    $mailKunde->isSMTP();
    $mailKunde->Host       = SMTP_HOST;
    $mailKunde->SMTPAuth   = true;
    $mailKunde->Username   = SMTP_USER;
    $mailKunde->Password   = SMTP_PASS;
    $mailKunde->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mailKunde->Port       = SMTP_PORT;

    $mailKunde->setFrom('hello@equipped-eventtechnik.de', 'Equipped! Eventtechnik'); // Absender des Autoresponders
    $mailKunde->addAddress($email, $name); // Empfänger ist der Kunde
    $mailKunde->addReplyTo('hello@equipped-eventtechnik.de', 'Equipped! Eventtechnik'); // Antwortadresse des Autoresponders

    $mailKunde->CharSet = PHPMailer::CHARSET_UTF8;
    $mailKunde->isHTML(true); // HTML-Inhalt für den Autoresponder

    $mailKunde->Subject = "Deine Anfrage bei Equipped! Eventtechnik";
    $mailKunde->Body    = "
<html>
    <body style='font-family: Arial, sans-serif; color: #333;'>
      <h2 style='color: #ff6b00;'>Vielen Dank für deine Anfrage, " . htmlspecialchars($name) . "!</h2>
      <p>Wir melden uns schnellstmöglich bei dir – in der Regel innerhalb von 24 Stunden.</p>
      <hr />
      <h3>Deine Angaben:</h3>
      <ul>
        <li><strong>Wunschtermin:</strong> " . htmlspecialchars($termin) . "</li>
        <li><strong>Bundle:</strong> " . htmlspecialchars($bundle) . "</li>
        <li><strong>Einzelteil(e):</strong> " . htmlspecialchars($einzelteil) . "</li>
        <li><strong>Nachricht:</strong><br>" . nl2br(htmlspecialchars($nachrichtKunde)) . "</li>
      </ul>
      <hr />
      <p>Falls du Fragen hast oder etwas ergänzen möchtest, antworte einfach direkt auf diese E-Mail.</p>
      <p style='margin-top: 40px;'>Viele Grüße<br>Dein Team von <strong>Equipped! Eventtechnik GbR</strong></p>
    </body>
</html>
    ";
    $mailKunde->AltBody = "Vielen Dank für deine Anfrage, $name!\nWir melden uns schnellstmöglich bei dir – in der Regel innerhalb von 24 Stunden.\n\nDeine Angaben:\nWunschtermin: $termin\nBundle: $bundle\nEinzelteil(e): $einzelteil\nNachricht:\n$nachrichtKunde\n\nFalls du Fragen hast oder etwas ergänzen möchtest, antworte einfach direkt auf diese E-Mail.\n\nViele Grüße\nDein Team von Equipped! Eventtechnik GbR";

    $mailKunde->send();

} catch (Exception $e) {
    // Fehler beim Senden des Autoresponders protokollieren
    error_log("Fehler beim Senden des Autoresponders (Anfrage.php): " . $mailKunde->ErrorInfo);
    // Hier geben wir KEINE Fehlermeldung an den Frontend zurück, da die interne Mail bereits erfolgreich war.
    // Der Kunde muss nicht wissen, dass der Autoresponder fehlgeschlagen ist.
}

// === Erfolg zurückgeben ===
echo json_encode(['success' => true]);
exit;
?>