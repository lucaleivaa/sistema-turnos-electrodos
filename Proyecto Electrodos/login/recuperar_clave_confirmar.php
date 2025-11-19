<?php 
session_start();
include("con_db.php");

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$status = null;
$title = '';
$message = '';

$email = $_GET['e'] ?? '';
$token = $_GET['t'] ?? '';

// Validar que existan email y token
if (!$email || !$token) {
    $status = 'error';
    $title = 'Solicitud no válida';
    $message = 'El enlace de recuperación no es válido o está incompleto.';
} else {
    // Buscar en tabla recuperar
    $stmt = $conex->prepare("SELECT clavenueva FROM recuperar WHERE email = ? AND token = ? LIMIT 1");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $recuperar = $resultado->fetch_assoc();
    
    if (!$recuperar) {
        $status = 'error';
        $title = 'Solicitud no encontrada';
        $message = 'El enlace de recuperación ha expirado, ya fue usado o no existe. Solicita uno nuevo.';
    } else {
        // Actualizar contraseña
        $clave_nueva = $recuperar['clavenueva'];
        $clave_hash = password_hash($clave_nueva, PASSWORD_DEFAULT, ["cost" => 10]);
        
        $stmt = $conex->prepare("UPDATE usuarios SET password_cifrada = ? WHERE email = ? LIMIT 1");
        $stmt->bind_param("ss", $clave_hash, $email);
        $stmt->execute();
        
        // Eliminar solicitud de recuperación
        $stmt = $conex->prepare("DELETE FROM recuperar WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $status = 'ok';
        $title = '¡Contraseña Actualizada!';
        $message = 'Tu contraseña se ha cambiado correctamente. Ya puedes iniciar sesión con tu nueva contraseña temporal.';
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Confirmación - Recuperar Contraseña</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../interfaz/style.css">
</head>
<body class="rows-page">

  <img src="https://electrodos.com.ar/electrodos/images/logo.png" alt="Logo de Electrodos" class="logo" />

  <main class="rows-center">
    <div class="rows-card">
      <div class="rows-body">
        
        <?php if ($status === 'ok'): ?>
          <div class="rows-icon rows-icon--ok" aria-hidden="true">✓</div>
          <h1 class="rows-title"><?= h($title) ?></h1>
          <p class="rows-msg"><?= h($message) ?></p>

          <div class="rows-actions">
            <a href="login.php" class="btn-turno">Ir a Iniciar Sesión</a>
          </div>

          <p class="rows-muted">
            Recuerda usar la contraseña temporal que se te mostró en el paso anterior. 
            Te recomendamos cambiarla por una personalizada una vez que ingreses.
          </p>

        <?php else: ?>
          <div class="rows-icon rows-icon--err" aria-hidden="true">✗</div>
          <h1 class="rows-title rows-title--error"><?= h($title) ?></h1>
          <p class="rows-msg"><?= h($message) ?></p>

          <div class="rows-actions">
            <a href="recuperar_clave_form.php" class="btn-turno">Solicitar Nuevo Enlace</a>
            <a href="login.php" class="btn-outline-turno">Volver al Login</a>
          </div>

          <p class="rows-muted">
            Si sigues teniendo problemas, contacta al administrador del sistema.
          </p>
        <?php endif; ?>

      </div>
    </div>

    <div class="rows-footer">Rows · Electrodos</div>
  </main>

</body>
</html>
