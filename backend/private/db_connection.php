<?php
  $host_name = 'db5017786223.hosting-data.io';
  $database = 'dbs14196064';
  $user_name = 'dbu1809875';
  $password = 'L$C_xrGv7LJu3S@';

  $link = new mysqli($host_name, $user_name, $password, $database);

  if ($link->connect_error) {
    error_log('Verbindung zum MySQL Server fehlgeschlagen: ' . $link->connect_error);
    die('<p>Ein Datenbankfehler ist aufgetreten. Bitte versuchen Sie es später erneut.</p>');
  }
?>