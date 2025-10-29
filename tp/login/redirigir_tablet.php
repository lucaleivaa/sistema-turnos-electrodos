<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'tablet') {
    header("Location: ../login/login.php");
    exit;
}
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Redirigiendo</title>
  <meta http-equiv="refresh" content="0; URL=../interfaz/inicio.html">
</head>
<body>
  <p>Redirigiendo a la interfaz...</p>
</body>
</html>
