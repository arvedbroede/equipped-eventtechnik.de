<?php
// === PHP Fehleranzeige für PRODUKTION deaktivieren ===
// Fehler NICHT im Browser anzeigen, aber zur Protokollierung aktivieren
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL); // Alle Fehler zur Protokollierung aktivieren

// Optional: Fehler in eine eigene Log-Datei schreiben (für die Produktion sehr empfohlen!)
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log'); // Pfad zu deiner Log-Datei anpassen
// Stelle sicher, dass der 'logs'-Ordner existiert und vom Webserver beschreibbar ist
// und NICHT öffentlich über den Browser erreichbar ist!

header('Content-Type: application/json');

// === Globale Fehler- und Exception-Handler für JSON-Antworten ===
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

// === Abhängigkeiten & Konfiguration ===
// Die config.php definiert Konstanten, daher kein 'require_once' mit Zuweisung
require_once __DIR__ . '/../private/config.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';

// PHPMailer laden
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';


// === JSON-Helfer ===
function send_json($success, $message = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// === Formulardaten prüfen ===
$name         = trim($_POST['name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$auswahlInput = $_POST['menge'] ?? []; // Bleibt ein Array von Strings/Zahlen
$nachricht    = trim($_POST['nachricht'] ?? '');

// E-Mail-Validierung hinzufügen
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(false, "Ungültiges E-Mail-Format. Bitte überprüfe deine E-Mail-Adresse.");
}

if (!$name || empty($auswahlInput) || !is_array($auswahlInput)) {
    send_json(false, "Pflichtfelder fehlen: Name, E-Mail oder Produktauswahl.");
}

// === Preisliste definieren (zentral steuerbar) ===
// Diese Preisliste sollte idealerweise aus einer Datenbank oder einer zentralen,
// sicheren Konfigurationsdatei geladen werden, um Manipulationen zu vermeiden.
$preise = [
    "Sony GTK-XB72"         => 15,
    "Mikrofon"              => 20,
    "Mischpult"             => 50,
    "Lichtstativ mit LED"   => 40,
    "Nebelmaschine"         => 25,
    "Laser-Effekt"          => 35,
    "Kabelset (10m)"        => 10,
    "Verlängerungskabel"    => 5,
    "Mehrfachsteckdose"     => 5,
];

// === Auswertung: Auswahl & Preis ===
$ausgabeZeilen = [];
$gesamtpreis = 0;

foreach ($auswahlInput as $produkt => $anzahl) {
    $anzahl = (int) $anzahl;
    if ($anzahl <= 0 || !isset($preise[$produkt])) continue;

    $einzelpreis = $preise[$produkt];
    $gesamt      = $anzahl * $einzelpreis;
    $gesamtpreis += $gesamt;

    $ausgabeZeilen[] = "{$produkt} × {$anzahl} ({$gesamt} €)";
}

// Wenn keine gültige Auswahl → Fehler
if (empty($ausgabeZeilen)) {
    send_json(false, "Es wurde keine gültige Produktauswahl getroffen.");
}

// === PDF erzeugen ===
// HINWEIS: Dieser Ordner (backend/pdf/bundle/) speichert PDF-Dateien.
// Überlege eine Strategie für die Bereinigung alter Dateien,
// um eine unbegrenzte Akkumulation von Daten zu vermeiden.
// Stelle sicher, dass dieser Ordner vom Webserver beschreibbar ist (CHMOD 0775).
$filename = 'bundle-anfrage_' . strtolower(str_replace(' ', '-', $name)) . '_' . date('Y-m-d_H-i-s') . '.pdf';
$pdfFolder = __DIR__ . '/../pdf/bundle/';
$pdfPath = $pdfFolder . $filename;

if (!is_dir($pdfFolder)) {
    if (!mkdir($pdfFolder, 0775, true)) { // 0775 ist typischerweise ein guter Wert für Ordner
        send_json(false, "PDF-Verzeichnis konnte nicht erstellt werden.");
    }
}

// HTML-Inhalt für das PDF
$pdfHtml = "
    <h2>Bundle-Anfrage</h2>
    <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
    <p><strong>E-Mail:</strong> " . htmlspecialchars($email) . "</p>
    <p><strong>Auswahl:</strong><br>" . nl2br(implode("\n", array_map('htmlspecialchars', $ausgabeZeilen))) . "</p>
    <p><strong>Gesamtpreis:</strong> " . htmlspecialchars(number_format($gesamtpreis, 2, ',', '.')) . " €</p>
    " . ($nachricht ? "<p><strong>Nachricht:</strong><br>" . nl2br(htmlspecialchars($nachricht)) . "</p>" : "") . "
    <p><small>Gesendet am " . date('d.m.Y H:i:s') . "</small></p>
";

$dompdf = new Dompdf\Dompdf(); // Korrekte Instanziierung
$dompdf->loadHtml($pdfHtml);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Sicherstellen, dass der Inhalt geschrieben werden kann
if (!file_put_contents($pdfPath, $dompdf->output())) {
    send_json(false, "PDF konnte nicht gespeichert werden.");
}

// === E-Mail versenden (PHPMailer) ===
$mail = new PHPMailer(true); // true = Exceptions aktivieren
try {
    $mail->isSMTP();
    // SMTP-Konfiguration aus config.php (Konstanten verwenden, nicht Array-Zugriff)
    $mail->Host       = SMTP_HOST;
    $mail->Port       = SMTP_PORT;
    $mail->SMTPSecure = SMTP_SECURE; // Verwende die Konstante
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;

    // Absender und Empfänger
    $mail->setFrom(SMTP_USER, 'Equipped! Eventtechnik'); // Absender-E-Mail und -Name
    $mail->addAddress($email, $name); // E-Mail des Kunden
    $mail->addBCC(SMTP_USER, 'Equipped! Eventtechnik'); // Blindkopie an eigene E-Mail (hello@equipped-eventtechnik.de)
    $mail->addReplyTo($email, $name); // Antwortadresse des Kunden

    $mail->CharSet = PHPMailer::CHARSET_UTF8; // UTF-8 für Umlaute
    $mail->isHTML(true); // HTML-Inhalt für die E-Mail

    $mail->Subject = "Deine Bundle-Anfrage bei Equipped!";

    $mail->Body = "
        <h2>Vielen Dank für deine Anfrage, " . htmlspecialchars($name) . "!</h2>
        <p>Hier ist die Zusammenfassung deiner Konfiguration:</p>
        <ul><li>" . implode("</li><li>", array_map('htmlspecialchars', $ausgabeZeilen)) . "</li></ul>
        <p><strong>Gesamtpreis:</strong> " . htmlspecialchars(number_format($gesamtpreis, 2, ',', '.')) . " €</p>
        " . ($nachricht ? "<p><strong>Deine Nachricht:</strong><br>" . nl2br(htmlspecialchars($nachricht)) . "</p>" : "") . "
        <p>Deine Konfiguration findest du im PDF-Anhang.</p>
        <p style='margin-top:20px;'>– Dein Equipped!-Team</p>
    ";
    $mail->AltBody = "Vielen Dank für deine Anfrage, $name!\n\nHier ist die Zusammenfassung deiner Konfiguration:\n" . implode("\n", $ausgabeZeilen) . "\nGesamtpreis: $gesamtpreis €\n" . ($nachricht ? "Deine Nachricht:\n$nachricht\n" : "") . "\nDeine Konfiguration findest du im PDF-Anhang.\n\n– Dein Equipped!-Team";

    $mail->addAttachment($pdfPath, 'Bundle-Anfrage.pdf'); // PDF als Anhang hinzufügen
    $mail->send();

    send_json(true, "Anfrage erfolgreich gesendet!"); // Erfolgsmeldung mit Nachricht
} catch (Exception $e) {
    // Fehler protokollieren
    error_log("E-Mail Fehler beim Senden der Bundle-Anfrage: " . $e->getMessage() . " / Debug-Output: " . $mail->ErrorInfo);
    send_json(false, "E-Mail konnte nicht versendet werden. Bitte versuche es erneut.");
}

?>