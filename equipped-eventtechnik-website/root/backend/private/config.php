<?php
// 🔍 Basis-URL dynamisch ermitteln
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl  = $protocol . '://' . $host;

// 🔐 .env-Datei einlesen
$envPath = __DIR__ . '/.env';
$envVars = [];

if (file_exists($envPath)) {
  $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos($line, '=') !== false) {
      [$key, $value] = explode('=', $line, 2);
      $envVars[trim($key)] = trim($value);
    }
  }
}

return [

  // 📨 SMTP-Einstellungen
  'smtp_host'   => 'smtp.ionos.de',
  'smtp_port'   => 465,
  'smtp_secure' => 'ssl',
  'smtp_user'   => 'hello@equipped-eventtechnik.de',
  'smtp_pass'   => $envVars['SMTP_PASS'] ?? '',

  // 🌐 Base URL
  'base_url'    => $baseUrl,

  // 📦 DB (optional)
  'db_host'     => 'localhost',
  'db_user'     => 'db_user',
  'db_pass'     => 'db_passwort',
  'db_name'     => 'equipped_eventtechnik',

  // 🔄 Umgebung
  'env'         => 'production'
];
