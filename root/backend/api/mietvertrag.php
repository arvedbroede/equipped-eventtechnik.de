<?php
require_once __DIR__ . '/dompdf/autoload.inc.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// 🔐 Konfigurationsdaten laden
$config = require __DIR__ . '/../private/config.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Formulardaten prüfen
$required = ['name', 'email', 'adresse', 'telefon', 'bundle', 'mietbeginn', 'mietende'];
foreach ($required as $field) {
  if (empty($_POST[$field])) {
    echo json_encode(['success' => false, 'message' => "Feld '$field' fehlt."]);
    exit;
  }
}

// 🧾 Daten erfassen
$name        = htmlspecialchars($_POST['name']);
$email       = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$adresse     = htmlspecialchars($_POST['adresse']);
$telefon     = htmlspecialchars($_POST['telefon']);
$bundleKey   = strtolower(trim($_POST['bundle']));
$mietbeginn  = $_POST['mietbeginn'];
$mietende    = $_POST['mietende'];
$nachricht   = htmlspecialchars($_POST['nachricht'] ?? '');

// 📦 Bundle laden
$bundlesPath = __DIR__ . '/../bundles.json';
if (!file_exists($bundlesPath)) {
  echo json_encode(['success' => false, 'message' => 'Bundles-Datei fehlt.']);
  exit;
}
$bundles = json_decode(file_get_contents($bundlesPath), true);
if (!isset($bundles[$bundleKey])) {
  echo json_encode(['success' => false, 'message' => 'Ungültiges Bundle.']);
  exit;
}
$bundleItemsHtml = "<ul><li>" . implode("</li><li>", $bundles[$bundleKey]) . "</li></ul>";

// 📄 Template laden
$templatePath = __DIR__ . '/../../frontend/template/vertrag_template_final.html';
$template = file_get_contents($templatePath);
if (!$template) {
  echo json_encode(['success' => false, 'message' => 'Template konnte nicht geladen werden.']);
  exit;
}

// 📋 Platzhalter ersetzen
$replacements = [
  '{{mieter_name}}'   => $name,
  '{{adresse}}'       => $adresse,
  '{{telefon}}'       => $telefon,
  '{{email}}'         => $email,
  '{{mietbeginn}}'    => date('d.m.Y H:i', strtotime($mietbeginn)),
  '{{mietende}}'      => date('d.m.Y H:i', strtotime($mietende)),
  '{{bundle}}'        => ucfirst($bundleKey),
  '{{bundle_items}}'  => $bundleItemsHtml,
  '{{nachricht}}'     => nl2br($nachricht),
  '{{erstell_datum}}' => date('d.m.Y'),
];

$html = str_replace(array_keys($replacements), array_values($replacements), $template);

// 🛠 Vorschau speichern (Debug)
file_put_contents(__DIR__ . '/debug_preview.html', $html);

if (trim($html) === '') {
  echo json_encode(['success' => false, 'message' => 'HTML ist leer – PDF konnte nicht erzeugt werden.']);
  exit;
}

// 📄 PDF erzeugen
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

$pdfOutput = $dompdf->output();
$pdfFilePath = sys_get_temp_dir() . "/mietvertrag_" . time() . ".pdf";
file_put_contents($pdfFilePath, $pdfOutput);

// 📧 Mail versenden mit IONOS SMTP
try {
  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host = $config['smtp_host'] ?? 'smtp.ionos.de';
  $mail->SMTPAuth = true;
  $mail->Username = $config['smtp_user'];
  $mail->Password = $config['smtp_pass'];
  $mail->SMTPSecure = $config['smtp_secure'] ?? 'ssl';
  $mail->Port = $config['smtp_port'] ?? 465;

  $mail->setFrom($config['smtp_user'], 'Equipped! Eventtechnik');
  $mail->addAddress($email, $name);
  $mail->addAddress($config['smtp_user']); // interne Kopie
  $mail->addAttachment($pdfFilePath, "Mietvertrag.pdf");

  $mail->isHTML(true);
  $mail->Subject = "Dein Mietvertrag – Equipped! Eventtechnik";
  $mail->Body = "Hallo $name,<br><br>anbei findest du deinen Mietvertrag als PDF.<br><br>Viele Grüße<br>Equipped! Eventtechnik";

  $mail->send();
  unlink($pdfFilePath);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => "Mailer Fehler: {$mail->ErrorInfo}"]);
}
