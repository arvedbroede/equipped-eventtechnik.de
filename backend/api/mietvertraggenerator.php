<?php
// mietvertraggenerator.php
// Ersetzt die alte Logik und fügt automatische Berechnungen hinzu.

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
    echo json_encode(['success' => false, 'message' => 'Ein interner Fehler ist aufgetreten (PHP-Fehler: Zeile ' . $errline . ' in ' . basename($errfile) . '): ' . $errstr]);
    exit();
});

set_exception_handler(function ($exception) {
    http_response_code(500); // Interner Serverfehler
    echo json_encode(['success' => false, 'message' => 'Eine unerwartete Ausnahme ist aufgetreten: ' . $exception->getMessage()]);
    exit();
});

// --- Bibliotheken laden ---
// 🔐 Konfigurationsdaten laden (definiert Konstanten)
require_once __DIR__ . '/../private/config.php'; // Deine Konfigurationsdatei mit SMTP-Daten und API-Schlüssel

require_once __DIR__ . '/dompdf/autoload.inc.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
// NEU: Externen Entfernungsrechner laden
require_once __DIR__ . '/distance_calculator.php'; // Stelle sicher, dass diese Datei existiert und korrekt ist.

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Für SMTP-Konstanten

// --- EINSTELLUNGEN FÜR BERECHNUNGEN (Konstanten aus config.php verwenden) ---
// Falls Maps_API_KEY in config.php definiert ist, wird es verwendet, ansonsten hier Fallback
// Optimal wäre, Maps_API_KEY immer in config.php zu definieren.
if (!defined('Maps_API_KEY')) {
    define('Maps_API_KEY', 'YOUR_API_KEY_FALLBACK'); // Fallback, falls nicht in config.php
}
const FIRMEN_ADRESSE = 'Danziger Straße 28, 71679 Asperg';
const PREIS_PRO_KM_NACH_GRENZE = 0.20; // 20ct pro km
const FREI_KM_GRENZE = 20; // Die ersten 20 km sind kostenfrei
const KAUTION_PROZENTSATZ = 0.20;
const MINDEST_KAUTION = 50.00;


// === JSON-Helfer (mit HTTP-Statuscode) ===
function send_json($success, $message = '', $httpStatusCode = 200) {
    http_response_code($httpStatusCode);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// --- FUNKTIONEN FÜR BERECHNUNGEN ---

/**
 * Berechnet die Anfahrtskosten basierend auf der Entfernung.
 * Berechnet 20ct pro km, aber erst ab 20km Entfernung.
 * Die Entfernung wird durch die Funktion in distance_calculator.php ermittelt.
 */
function calculateShippingCost(string $destination_address): float
{
    try {
        // NEU: Berechne die Entfernung anhand der Adresse mit der externen Funktion
        $distanceKm = calculateDistanceByGoogleAPI(FIRMEN_ADRESSE, $destination_address, Maps_API_KEY); // Nutze die Konstante
    } catch (Exception $e) {
        // Bei einem Fehler der API-Abfrage, z.B. ungültige Adresse, Kosten als 0 annehmen
        // In der Produktion sollte dies geloggt werden, aber nicht die gesamte Anfrage abbrechen.
        error_log("Fehler bei Google Maps API: " . $e->getMessage() . " für Adresse: " . $destination_address);
        return 0.00;
    }
    
    // Die Kosten werden nur für die Entfernung berechnet, die über der Freigrenze liegt
    $distanz_ueber_grenze = $distanceKm - FREI_KM_GRENZE;
    
    // Wenn die Entfernung 20 km oder weniger ist, sind die Kosten 0
    if ($distanz_ueber_grenze <= 0) {
        return 0.00;
    }
    
    // Kosten für Hin- und Rückfahrt
    $cost = $distanz_ueber_grenze * PREIS_PRO_KM_NACH_GRENZE * 2;
    
    // Runde auf 2 Dezimalstellen
    return round($cost, 2);
}

/**
 * Berechnet die Mietdauer basierend auf Datum UND Uhrzeit.
 * Rechnet die Dauer in vollen Tagen (24-Stunden-Blöcken) ab.
 */
function calculateRentalPeriod(string $start, string $end): string
{
    try {
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);
        
        // Stellen Sie sicher, dass Enddatum nicht vor Startdatum liegt
        if ($endDate < $startDate) {
            return "Ungültige Mietdauer";
        }

        $interval = $startDate->diff($endDate);
        
        // Berechne die Gesamtzahl der Stunden
        $totalHours = $interval->days * 24 + $interval->h + ($interval->i / 60); // Minuten auch berücksichtigen für genauere Stunden
        
        // Zähle jeden angebrochenen Tag als ganzen Tag
        $days = ceil($totalHours / 24);

        if ($days < 1) {
            return "1 Tag"; // Mindestmietdauer 1 Tag
        }
        
        return $days . ' Tage';
    } catch (Exception $e) {
        error_log("Fehler bei calculateRentalPeriod: " . $e->getMessage());
        return "Ungültiges Datum/Uhrzeit";
    }
}

/**
 * Berechnet die Kaution basierend auf dem Gesamtmietpreis.
 */
function calculateDeposit(float $totalPrice): float
{
    $deposit = $totalPrice * KAUTION_PROZENTSATZ;
    return max($deposit, MINDEST_KAUTION);
}

// === Formulardaten prüfen und erfassen ===
// Die Pflichtfelder werden jetzt getrennt abgefragt.
$required = ['name', 'email', 'strasse', 'plz', 'ort', 'mietbeginn', 'mietende', 'gesamtmietpreis', 'bundle_items_html'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        send_json(false, "Feld '$field' fehlt.", 400); // Bad Request
    }
}

// 🧾 Daten erfassen
$name            = htmlspecialchars(trim($_POST['name']));
$email           = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL); // trim() bereits hier anwenden
$strasse         = htmlspecialchars(trim($_POST['strasse']));
$plz             = htmlspecialchars(trim($_POST['plz']));
$ort             = htmlspecialchars(trim($_POST['ort']));
$telefon         = htmlspecialchars(trim($_POST['telefon'] ?? ''));
$mietbeginn      = trim($_POST['mietbeginn']); // Format:YYYY-MM-DDTHH:MM
$mietende        = trim($_POST['mietende']);   // Format:YYYY-MM-DDTHH:MM
$nachricht       = htmlspecialchars(trim($_POST['nachricht'] ?? ''));
$gesamtmietpreis = (float)$_POST['gesamtmietpreis'];
$bundleItemsHtml = $_POST['bundle_items_html']; // HTML kann Tags enthalten, htmlspecialchars() vor Ausgabe im PDF/E-Mail


// Zusätzliche Überprüfung für valide E-Mail nach filter_var
if (!$email) {
    send_json(false, "Ungültige E-Mail-Adresse.", 400);
}

// --- Adress-String für die API und den Vertrag zusammensetzen ---
$destination_address_for_api = $strasse . ', ' . $plz . ' ' . $ort;
$adresse_fuer_vertrag        = htmlspecialchars($destination_address_for_api);


// --- FÜHRE DIE BERECHNUNGEN DURCH ---
$berechnete_anfahrtskosten = calculateShippingCost($destination_address_for_api);
$berechnete_kaution        = calculateDeposit($gesamtmietpreis);
$berechnete_mietzeit       = calculateRentalPeriod($mietbeginn, $mietende);


// Überprüfe, ob die Mietzeitberechnung gültig ist
if ($berechnete_mietzeit === "Ungültiges Datum/Uhrzeit" || $berechnete_mietzeit === "Ungültige Mietdauer") {
    send_json(false, "Ungültige Mietbeginn- oder Mietende-Angaben.", 400);
}


// 📄 Template laden
// Entferne das '@' für bessere Fehlerbehandlung. Fehler werden vom globalen Handler erfasst.
$templatePath = __DIR__ . '/../../frontend/templates/mietvertrag_template_final.html'; // Aktualisierter Dateiname
$template = file_get_contents($templatePath);
if ($template === false) { // Prüfung auf false statt nur !template
    error_log("Mietvertrag-Template konnte nicht geladen werden: " . $templatePath);
    send_json(false, 'Das Mietvertrag-Template konnte nicht geladen werden.', 500);
}

if (trim($template) === '') {
    send_json(false, 'Mietvertrag-Template ist leer.', 500);
}


// 📋 Platzhalter ersetzen
$replacements = [
    '{{mieter_name}}'                 => $name, // $name ist bereits htmlspecialcharsed
    '{{adresse}}'                     => $adresse_fuer_vertrag, // $adresse_fuer_vertrag ist bereits htmlspecialcharsed
    '{{telefon}}'                     => $telefon, // $telefon ist bereits htmlspecialcharsed
    '{{email}}'                       => $email, // $email ist bereits filter_var und trim
    '{{mietbeginn}}'                  => date('d.m.Y H:i', strtotime($mietbeginn)), // Konvertiere Format für die Ausgabe
    '{{mietende}}'                    => date('d.m.Y H:i', strtotime($mietende)),   // Konvertiere Format für die Ausgabe
    '{{bundle_items}}'                => $bundleItemsHtml, // Ist HTML, muss evtl. vor Ausgabe im PDF/Email nochmals gesichert werden
    '{{nachricht}}'                   => nl2br($nachricht), // $nachricht ist bereits htmlspecialcharsed
    '{{erstell_datum}}'               => date('d.m.Y'),
    '{{berechnete_mietzeit}}'         => $berechnete_mietzeit,
    '{{berechneter_mietpreis_summe}}' => number_format($gesamtmietpreis, 2, ',', '.') . ' EUR',
    '{{kautionssumme}}'               => number_format($berechnete_kaution, 2, ',', '.') . ' EUR',
    '{{berechnete_anfahrtskosten}}'   => number_format($berechnete_anfahrtskosten, 2, ',', '.') . ' EUR',
    '{{datum_ort}}'                   => date('d.m.Y') . ', ' . $ort // $ort ist bereits htmlspecialcharsed
];

$html = str_replace(array_keys($replacements), array_values($replacements), $template);

// 🛠 Vorschau speichern (Debug) - Nur für Entwicklung!
// file_put_contents(__DIR__ . '/debug_preview.html', $html); // DIESE ZEILE ENTFERNEN ODER AUSKOMMENTIEREN FÜR PRODUKTION!

if (trim($html) === '') {
    send_json(false, 'HTML für PDF ist leer – PDF konnte nicht erzeugt werden.', 500);
}

// 📄 PDF erzeugen (Dompdf)
$options = new Options();
$options->set('isRemoteEnabled', true); // Erlaubt das Laden von Remote-Ressourcen (Bilder, CSS)
$options->set('defaultFont', 'DejaVuSans'); // Beispiel: Setze einen Standardfont für bessere UTF-8 Unterstützung

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4'); // Setzt das Papierformat und Ausrichtung
$dompdf->render();

$pdfOutput = $dompdf->output();
$pdfFilePath = sys_get_temp_dir() . "/mietvertrag_" . time() . ".pdf";

// Sicherstellen, dass der Inhalt geschrieben werden kann
if (!file_put_contents($pdfFilePath, $pdfOutput)) {
    error_log("PDF konnte nicht in temporärem Verzeichnis gespeichert werden: " . $pdfFilePath);
    send_json(false, "PDF konnte nicht gespeichert werden.", 500);
}

// 📧 Mail versenden mit IONOS SMTP (PHPMailer)
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    // SMTP-Konfiguration aus config.php (Konstanten verwenden!)
    $mail->Host       = SMTP_HOST;
    $mail->Port       = SMTP_PORT;
    $mail->SMTPSecure = SMTP_SECURE; // Wichtig für TLS/SSL (z.B. 'ssl' oder 'tls')
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->CharSet    = PHPMailer::CHARSET_UTF8; // Wichtig für Umlaute

    // Absender und Empfänger
    $mail->setFrom(SMTP_USER, 'Equipped! Eventtechnik'); // Absender-E-Mail und -Name
    $mail->addAddress($email, $name); // E-Mail des Kunden
    $mail->addAddress(SMTP_USER); // interne Kopie an hello@equipped-eventtechnik.de (oft SMTP_USER)
    $mail->addReplyTo($email, $name); // Antwortadresse des Kunden

    $mail->isHTML(true);
    $mail->Subject = "Dein Mietvertrag – Equipped! Eventtechnik";
    $mail->Body = "
        Hallo " . htmlspecialchars($name) . ",<br><br>
        anbei findest du deinen Mietvertrag als PDF.<br><br>
        Viele Grüße<br>
        Dein Team von <strong>Equipped! Eventtechnik GbR</strong>
    ";
    // Alternativtext für E-Mail-Clients, die kein HTML unterstützen
    $mail->AltBody = "Hallo $name,\n\nanbei findest du deinen Mietvertrag als PDF.\n\nViele Grüße\nDein Team von Equipped! Eventtechnik GbR";

    $mail->addAttachment($pdfFilePath, "Mietvertrag.pdf"); // PDF als Anhang hinzufügen
    $mail->send();

    unlink($pdfFilePath); // Temporäre Datei löschen nach erfolgreichem Versand

    send_json(true, "Mietvertrag erfolgreich gesendet!");
} catch (Exception $e) {
    // Detaillierte Fehlermeldung für das Log
    error_log("Mailer Fehler beim Mietvertrag: " . $e->getMessage() . " / Debug-Output: " . $mail->ErrorInfo);
    // Generische Fehlermeldung an das Frontend
    send_json(false, "E-Mail konnte nicht versendet werden. Bitte versuche es erneut.", 500);
}
?>