<?php
// mietvertraggenerator.php
// Ersetzt die alte Logik und fügt automatische Berechnungen hinzu.

// --- Wichtiger Hinweis zur Reihenfolge:
// Lade die Konfiguration, bevor du auf Werte aus ihr zugreifst.
// 🔐 Konfigurationsdaten laden
// Der Pfad ist relativ zum Speicherort dieser Datei (__DIR__).
$config = require __DIR__ . '/../private/config.php';

// --- Bibliotheken laden ---
require_once __DIR__ . '/dompdf/autoload.inc.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
// NEU: Externen Entfernungsrechner laden
require_once __DIR__ . '/distance_calculator.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- EINSTELLUNGEN FÜR BERECHNUNGEN ---
// Google Maps API-Schlüssel aus der Konfiguration laden (Korrigierte Logik)
const Maps_API_KEY_LOADED = 'YOUR_API_KEY_FALLBACK';
if (isset($config['Maps_api_key']) && !empty($config['Maps_api_key'])) {
    define('Maps_API_KEY_LOADED', $config['Maps_api_key']);
}

// Firmenadresse als Startpunkt für die Entfernungsmessung
const FIRMEN_ADRESSE = 'Danziger Straße 28, 71679 Asperg';
// Preis pro km für die Anfahrtskosten (Hin- und Rückfahrt)
const PREIS_PRO_KM_NACH_GRENZE = 0.20; // 20ct pro km
const FREI_KM_GRENZE = 20; // Die ersten 20 km sind kostenfrei
// Prozentsatz und Mindestkaution
const KAUTION_PROZENTSATZ = 0.20;
const MINDEST_KAUTION = 50.00;


// --- FUNKTIONEN FÜR BERECHNUNGEN ---

/**
 * Berechnet die Anfahrtskosten basierend auf der Entfernung.
 * Berechnet 20ct pro km, aber erst ab 20km Entfernung.
 * Die Entfernung wird durch die Funktion in distance_calculator.php ermittelt.
 */
function calculateShippingCost(string $destination_address): float
{
    // NEU: Berechne die Entfernung anhand der Adresse mit der externen Funktion
    $distanceKm = calculateDistanceByGoogleAPI(FIRMEN_ADRESSE, $destination_address, Maps_API_KEY_LOADED);
    
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
        
        $interval = $startDate->diff($endDate);
        
        // Berechne die Gesamtzahl der Stunden
        $totalHours = $interval->days * 24 + $interval->h;
        
        // Zähle jeden angebrochenen Tag als ganzen Tag
        $days = ceil($totalHours / 24);

        if ($days < 1) {
            return "1 Tag";
        }
        
        return $days . ' Tage';
    } catch (Exception $e) {
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

// ✅ Formulardaten prüfen
// Die Pflichtfelder werden jetzt getrennt abgefragt.
$required = ['name', 'email', 'strasse', 'plz', 'ort', 'mietbeginn', 'mietende', 'gesamtmietpreis', 'bundle_items_html'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Feld '$field' fehlt."]);
        exit;
    }
}

// 🧾 Daten erfassen
$name            = htmlspecialchars($_POST['name']);
$email           = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
// Neue Adressvariablen
$strasse         = htmlspecialchars($_POST['strasse']);
$plz             = htmlspecialchars($_POST['plz']);
$ort             = htmlspecialchars($_POST['ort']);
$telefon         = htmlspecialchars($_POST['telefon'] ?? '');
$mietbeginn      = $_POST['mietbeginn']; // Format:YYYY-MM-DDTHH:MM
$mietende        = $_POST['mietende']; // Format:YYYY-MM-DDTHH:MM
$nachricht       = htmlspecialchars($_POST['nachricht'] ?? '');
$gesamtmietpreis = (float)$_POST['gesamtmietpreis'];
$bundleItemsHtml = $_POST['bundle_items_html'];

// --- Adress-String für die API und den Vertrag zusammensetzen ---
$adresse_fuer_vertrag = $strasse . ', ' . $plz . ' ' . $ort;

// --- FÜHRE DIE BERECHNUNGEN DURCH ---
// Übergib die PLZ an die neue Funktion
$berechnete_kaution = calculateDeposit($gesamtmietpreis);
$berechnete_mietzeit = calculateRentalPeriod($mietbeginn, $mietende);


// 📄 Template laden
// Stelle sicher, dass der Pfad korrekt ist
$templatePath = __DIR__ . '/../../frontend/templates/mietvertrag_template.html';
$template = @file_get_contents($templatePath);
if (!$template) {
    echo json_encode(['success' => false, 'message' => 'Template konnte nicht geladen werden. Pfad: ' . $templatePath]);
    exit;
}

// 📋 Platzhalter ersetzen
$replacements = [
    '{{mieter_name}}'                 => $name,
    '{{adresse}}'                     => $adresse_fuer_vertrag, // Füge den neuen, zusammengesetzten Adress-String ein
    '{{telefon}}'                     => $telefon,
    '{{email}}'                       => $email,
    '{{mietbeginn}}'                  => date('d.m.Y H:i', strtotime($mietbeginn)), // Konvertiere Format für die Ausgabe
    '{{mietende}}'                    => date('d.m.Y H:i', strtotime($mietende)),   // Konvertiere Format für die Ausgabe
    '{{bundle_items}}'                => $bundleItemsHtml,
    '{{nachricht}}'                   => nl2br($nachricht),
    '{{erstell_datum}}'               => date('d.m.Y'),
    '{{berechnete_mietzeit}}'         => $berechnete_mietzeit,
    '{{berechneter_mietpreis_summe}}' => number_format($gesamtmietpreis, 2, ',', '.') . ' EUR',
    '{{kautionssumme}}'               => number_format($berechnete_kaution, 2, ',', '.') . ' EUR',
    '{{berechnete_anfahrtskosten}}'   => number_format($berechnete_anfahrtskosten, 2, ',', '.') . ' EUR',
    '{{datum_ort}}'                   => date('d.m.Y') . ', ' . $ort // Verwende den Ort aus dem Formular
];

$html = str_replace(array_keys($replacements), array_values($replacements), $template);

// 🛠 Vorschau speichern (Debug)
file_put_contents(__DIR__ . '/debug_preview.html', $html);

if (trim($html) === '') {
    echo json_encode(['success' => false, 'message' => 'HTML ist leer – PDF konnte nicht erzeugt werden.']);
    exit;
}

// 📄 PDF erzeugen (Dompdf)
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

$pdfOutput = $dompdf->output();
$pdfFilePath = sys_get_temp_dir() . "/mietvertrag_" . time() . ".pdf";
file_put_contents($pdfFilePath, $pdfOutput);

// 📧 Mail versenden mit IONOS SMTP (PHPMailer)
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'] ?? 'smtp.ionos.de';
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->SMTPSecure = $config['smtp_secure'] ?? 'ssl';
    $mail->Port = $config['smtp_port'] ?? 465;
    $mail->CharSet = 'UTF-8'; // Wichtig für Umlaute

    $mail->setFrom($config['smtp_user'], 'Equipped! Eventtechnik');
    $mail->addAddress($email, $name);
    $mail->addAddress($config['smtp_user']); // interne Kopie
    $mail->addAttachment($pdfFilePath, "Mietvertrag.pdf");

    $mail->isHTML(true);
    $mail->Subject = "Dein Mietvertrag – Equipped! Eventtechnik";
    $mail->Body = "Hallo $name,<br><br>anbei findest du deinen Mietvertrag als PDF.<br><br>Viele Grüße<br>Equipped! Eventtechnik";

    $mail->send();
    unlink($pdfFilePath); // Temporäre Datei löschen

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Mailer Fehler: {$mail->ErrorInfo}");
    echo json_encode(['success' => false, 'message' => "Mailer Fehler: {$mail->ErrorInfo}"]);
}