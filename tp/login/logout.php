<?php
session_start();

// ✅ Ruta correcta y verificación
$config_path = 'config.php';
if (file_exists($config_path)) {
    include_once($config_path);

    if (isset($google_client)) {
        $google_client->revokeToken(); // Cierra sesión de Google
    }
}

// 🔒 Cierra sesión local
session_destroy();

// 🔁 Redirige al login (o home)
header('Location: index.php');
exit;
?>
