<?php 
session_start();
include ("con_db.php");

$email = $_GET['e'] ?? '';
$token = $_GET['t'] ?? '';

// Validar que existan email y token
if (!$email || !$token) {
    $_SESSION['error'] = 'Solicitud no válida.';
    header("Location: http://192.168.101.92:8084/tp/login/login.php");
    exit();
}

$consulta = "SELECT clavenueva FROM recuperar WHERE email='$email' AND token='$token' LIMIT 1";
$resultado = mysqli_query($conex, $consulta);

$a = mysqli_fetch_assoc($resultado);

if (!$a) {
    $_SESSION['error'] = 'Solicitud no encontrada';
    header("Location: http://192.168.101.92:8084/tp/login/login.php");
    exit();
}

$clave = $a['clavenueva'];
$clave_ = password_hash($clave, PASSWORD_DEFAULT, ["cost" => 10]);

$consulta2 = "UPDATE usuarios SET password_cifrada='$clave_' WHERE email='$email' LIMIT 1";
mysqli_query($conex, $consulta2);

$consulta3 = "DELETE FROM recuperar WHERE email='$email' LIMIT 1";
mysqli_query($conex, $consulta3);

$_SESSION['rta'] = 'Contraseña actualizada satisfactoriamente, ya se puede loguear';
header("Location: http://192.168.101.92:8084/tp/login/login.php");
exit();
?>
