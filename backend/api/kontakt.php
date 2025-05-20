<?php
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fehler als JSON zurückgeben
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
  echo json_encode([
    "success" => false,
    "message" => "PHP-Fehler (Zeile $errline): $errstr"
  ]);
  exit;
});
set_exception_handler(function ($e) {
  echo json_encode([
    "success" => false,
    "message" => "Exception: " . $e->getMessage()
  ]);
  exit;
});

// Felder holen
$name     = trim($_POST["name"] ?? '');
$email    = trim($_POST["email"] ?? '');
$nachricht = trim($_POST["nachricht"] ?? '');

if ($name === '' || $email === '') {
  echo json_encode([
    "success" => false,
    "message" => "Bitte fülle alle Pflichtfelder aus."
  ]);
  exit;
}

// ==========================
// Mail an Equipped
// ==========================
$to = "hello@equipped-eventtechnik.de";
$subject = "Neue Kontaktanfrage über das Formular";
$headers = "From: Equipped! Eventtechnik <hello@equipped-eventtechnik.de>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

$message = "Neue Kontaktanfrage:\n\n";
$message .= "Name: $name\n";
$message .= "E-Mail: $email\n";
$message .= "Nachricht:\n$nachricht\n";

$sent = mail($to, $subject, $message, $headers);

// Antwort an JS
if ($sent) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "Mailversand fehlgeschlagen."
  ]);
}
exit;
