<?php

// === Fehleranzeige (nur im Dev-Modus empfohlen)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === Abhängigkeiten & Konfiguration
$config = require_once __DIR__ . '/../private/config.php';
require_once __DIR__ . '/dompdf/autoload.inc.php';

// PHPMailer laden
$phpmailerPath = __DIR__ . '/PHPMailer/src/';
require_once $phpmailerPath . 'PHPMailer.php';
require_once $phpmailerPath . 'SMTP.php';
require_once $phpmailerPath . 'Exception.php';

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// === JSON-Helfer
function send_json($success, $message = '') {
  header('Content-Type: application/json');
  echo json_encode(['success' => $success, 'message' => $message]);
  exit;
}

// === Formulardaten prüfen
$name         = trim($_POST['name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$auswahlInput = $_POST['menge'] ?? [];
$nachricht    = trim($_POST['nachricht'] ?? '');

if (!$name || !$email || empty($auswahlInput) || !is_array($auswahlInput)) {
  send_json(false, "Pflichtfelder fehlen.");
}

// === Preisliste definieren (zentral steuerbar)
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

// === Auswertung: Auswahl & Preis
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
  send_json(false, "Keine gültige Produktauswahl.");
}

// === PDF erzeugen
$filename = 'bundle-anfrage_' . strtolower(str_replace(' ', '-', $name)) . '_' . date('Y-m-d_H-i-s') . '.pdf';
$pdfFolder = __DIR__ . '/../pdf/bundle/';
$pdfPath = $pdfFolder . $filename;

if (!is_dir($pdfFolder)) {
  if (!mkdir($pdfFolder, 0775, true)) {
    send_json(false, "PDF-Verzeichnis konnte nicht erstellt werden.");
  }
}

$pdfHtml = "
  <h2>Bundle-Anfrage</h2>
  <p><strong>Name:</strong> {$name}</p>
  <p><strong>E-Mail:</strong> {$email}</p>
  <p><strong>Auswahl:</strong><br>" . nl2br(implode("\n", array_map('htmlspecialchars', $ausgabeZeilen))) . "</p>
  <p><strong>Gesamtpreis:</strong> {$gesamtpreis} €</p>
  " . ($nachricht ? "<p><strong>Nachricht:</strong><br>" . nl2br(htmlspecialchars($nachricht)) . "</p>" : "") . "
  <p><small>Gesendet am " . date('d.m.Y H:i:s') . "</small></p>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($pdfHtml);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
file_put_contents($pdfPath, $dompdf->output());

// === E-Mail versenden
$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  $mail->Host       = $config['smtp_host'];
  $mail->Port       = $config['smtp_port'];
  $mail->SMTPSecure = $config['smtp_secure'];
  $mail->SMTPAuth   = true;
  $mail->Username   = $config['smtp_user'];
  $mail->Password   = $config['smtp_pass'];

  $mail->setFrom($config['smtp_user'], 'Equipped! Eventtechnik');
  $mail->addAddress($email, $name);
  $mail->addBCC('hello@equipped-eventtechnik.de', 'Equipped! Eventtechnik');
  $mail->addReplyTo($config['smtp_user'], 'Equipped! Eventtechnik');

  $mail->isHTML(true);
  $mail->Subject = "Deine Bundle-Anfrage bei Equipped!";

  $mail->Body = "
    <h2>Danke für deine Anfrage, {$name}!</h2>
    <p>Hier ist die Zusammenfassung deiner Konfiguration:</p>
    <ul><li>" . implode("</li><li>", array_map('htmlspecialchars', $ausgabeZeilen)) . "</li></ul>
    <p><strong>Gesamtpreis:</strong> {$gesamtpreis} €</p>
    " . ($nachricht ? "<p><strong>Deine Nachricht:</strong><br>" . nl2br(htmlspecialchars($nachricht)) . "</p>" : "") . "
    <p>Deine Konfiguration findest du im PDF-Anhang.</p>
    <p style='margin-top:20px;'>– Dein Equipped!-Team</p>
  ";

  $mail->addAttachment($pdfPath, 'Bundle-Anfrage.pdf');
  $mail->send();

  send_json(true);
} catch (Exception $e) {
  send_json(false, "E-Mail Fehler: " . $mail->ErrorInfo);
}
