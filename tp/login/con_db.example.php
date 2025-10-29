<?php
// ARCHIVO DE EJEMPLO - Renombrar a con_db.php y completar con tus datos reales

$conex = mysqli_connect(
    "TU_HOST",              // Ejemplo: dirección ip olocalhost
    "TU_USUARIO",           // Ejemplo: prueba
    "TU_CONTRASEÑA",        // Tu contraseña de MySQL
    "TU_BASE_DATOS"         // Ejemplo: prueba_db
);

if (!$conex) {
    die("Error de conexión: " . mysqli_connect_error());
}

$conex->set_charset('utf8mb4');
?>
