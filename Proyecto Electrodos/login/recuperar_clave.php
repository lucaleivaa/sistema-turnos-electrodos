<?php 
session_start();
include("con_db.php");

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$status = null;
$title = '';
$message = '';
$datos_recuperacion = [];

if (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = mysqli_real_escape_string($conex, $_POST['email']);
    
    // Buscar usuario
    $stmt = $conex->prepare("SELECT id, username, email FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    
    if (!$usuario) {
        $status = 'error';
        $title = 'Email no encontrado';
        $message = 'El email <strong>' . h($email) . '</strong> no está registrado en el sistema.';
    } else {
        // Generar token y contraseña nueva
        $token = md5($usuario['email'] . time() . rand(1000, 9999));
        $clave_nueva = rand(10000000, 99999999);
        
        // Insertar o actualizar en tabla recuperar
        $stmt = $conex->prepare("INSERT INTO recuperar (email, token, fechaalta, clavenueva) 
                                 VALUES (?, ?, NOW(), ?)
                                 ON DUPLICATE KEY UPDATE token = ?, clavenueva = ?, fechaalta = NOW()");
        $stmt->bind_param("ssiss", $email, $token, $clave_nueva, $token, $clave_nueva);
        $stmt->execute();
        
        // Link de confirmación
        $link = "http://192.168.101.92:8084/tp/login/recuperar_clave_confirmar.php?e=" . urlencode($email) . "&t=$token";
        
        $status = 'ok';
        $title = '¡Solicitud Generada!';
        $message = 'Hola <strong>' . h($usuario['username']) . '</strong>, tu nueva contraseña temporal es:';
        $datos_recuperacion = [
            'clave' => $clave_nueva,
            'link' => $link
        ];
    }
    
    $stmt->close();
} else {
    $status = 'error';
    $title = 'Error';
    $message = 'Debes ingresar un email válido.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar Contraseña</title>
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
          <p class="rows-msg"><?= $message ?></p>

          <div class="codigo-box">
            <p style="margin: 0; color: #666; font-size: 14px;">Tu contraseña temporal es:</p>
            <div class="clave-temporal"><?= h($datos_recuperacion['clave']) ?></div>
            <p class="codigo-box-advertencia">⚠️ Guarda este número antes de continuar</p>
          </div>

          <p class="instruccion-texto">
            <strong>Paso siguiente:</strong><br>
            Para activar tu nueva contraseña, debes hacer clic en el siguiente enlace:
          </p>

          <div class="rows-actions">
            <a href="<?= h($datos_recuperacion['link']) ?>" class="btn-turno">Activar Nueva Contraseña</a>
          </div>

          <div class="link-box">
            <strong>O copia este enlace en tu navegador:</strong>
            <?= h($datos_recuperacion['link']) ?>
          </div>

          <p class="rows-muted rows-muted-spacing">
            Si tú no solicitaste esto, ignora este mensaje. La contraseña solo se cambiará si haces clic en el enlace.
          </p>

        <?php else: ?>
          <div class="rows-icon rows-icon--err" aria-hidden="true">✗</div>
          <h1 class="rows-title rows-title--error"><?= h($title) ?></h1>
          <p class="rows-msg"><?= $message ?></p>

          <div class="rows-actions">
            <a href="recuperar_clave_form.php" class="btn-turno">Intentar de nuevo</a>
            <a href="login.php" class="btn-outline-turno">Volver al Login</a>
          </div>

          <p class="rows-muted">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
        <?php endif; ?>

      </div>
    </div>

    <div class="rows-footer">Rows · Electrodos</div>
  </main>

</body>
</html>
