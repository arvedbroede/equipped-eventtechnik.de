<?php
// === PHP Fehleranzeige für PRODUKTION deaktivieren ===
// Fehler NICHT im Browser anzeigen, aber zur Protokollierung aktivieren
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0); // Auch Startfehler nicht anzeigen
error_reporting(E_ALL); // Alle Fehler zur Protokollierung aktivieren

// Optional: Fehler in eine eigene Log-Datei schreiben (für die Produktion sehr empfohlen!)
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log'); // Pfad zu deiner Log-Datei anpassen
// Stelle sicher, dass der 'logs'-Ordner existiert und vom Webserver beschreibbar ist
// und NICHT öffentlich über den Browser erreichbar ist!

header("Content-Type: application/json");

// === Globale Fehler- und Exception-Handler für JSON-Antworten ===
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // Dieser Fehler-Typ ist nicht im error_reporting enthalten, also ignorieren
        return false;
    }
    http_response_code(500); // Interner Serverfehler
    echo json_encode([
        "success" => false,
        "message" => "Ein interner Fehler ist aufgetreten (PHP-Fehler: Zeile $errline): $errstr"
    ]);
    exit;
});

set_exception_handler(function ($e) {
    http_response_code(500); // Interner Serverfehler
    echo json_encode([
        "success" => false,
        "message" => "Eine unerwartete Ausnahme ist aufgetreten: " . $e->getMessage()
    ]);
    exit;
});

// === Abhängigkeiten & Konfiguration ===
require_once __DIR__ . '/../private/config.php'; // Deine Konfigurationsdatei mit SMTP-Daten

// PHPMailer laden
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';


// === Felder holen und validieren ===
$name      = trim($_POST["name"] ?? '');
$email     = trim($_POST["email"] ?? '');
$nachricht = trim($_POST["nachricht"] ?? '');

// E-Mail-Validierung hinzufügen
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "success" => false,
        "message" => "Ungültiges E-Mail-Format. Bitte überprüfe deine E-Mail-Adresse."
    ]);
    exit;
}

if ($name === '' || $email === '' || $nachricht === '') {
    http_response_code(400); // Bad Request
    echo json_encode([
        "success" => false,
        "message" => "Bitte fülle alle Pflichtfelder aus."
    ]);
    exit;
}

// ==========================
// Mail an Equipped! Eventtechnik senden (PHPMailer)
// ==========================
$mail = new PHPMailer(true); // true = Exceptions aktivieren
try {
    // SMTP-Konfiguration aus config.php
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE; // Verwende die Konstante
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_USER, 'Kontaktformular'); // Absender der Mail (oft dieselbe wie SMTP_USER)
    $mail->addAddress(SMTP_USER, 'Equipped! Eventtechnik'); // Empfänger ist hello@equipped-eventtechnik.de
    $mail->addReplyTo($email, $name); // Antwortadresse des Kunden

    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    $mail->isHTML(false); // Text-E-Mail

    $mail->Subject = "Neue Kontaktanfrage von " . $name;
    $mail->Body    = "
Neue Kontaktanfrage:

Name: $name
E-Mail: $email
Nachricht:
$nachricht
    ";

    $mail->send();

    echo json_encode(["success" => true, "message" => "Nachricht erfolgreich gesendet!"]);
    exit;

} catch (Exception $e) {
    // Fehler beim Senden der E-Mail protokollieren
    error_log("Fehler beim Senden der Kontakt-E-Mail: " . $e->getMessage() . " / Debug-Output: " . $mail->ErrorInfo);
    http_response_code(500); // Interner Serverfehler
    echo json_encode([
        "success" => false,
        "message" => "Es gab ein Problem beim Senden deiner Nachricht. Bitte versuche es später erneut."
    ]);
    exit;
}
?>