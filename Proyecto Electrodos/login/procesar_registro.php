<?php
// tp/login/procesar_registro.php
declare(strict_types=1);
require_once __DIR__ . '/con_db.php'; // debe exponer $conex (mysqli)

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$status  = null;   // 'ok' | 'error'
$title   = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro_usuario'])) {
    $usuario  = trim($_POST['usuario'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validaciones básicas
    if ($usuario === '' || $email === '' || $password === '') {
        $status  = 'error';
        $title   = 'Faltan datos';
        $message = 'Por favor, completá todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $status  = 'error';
        $title   = 'Email inválido';
        $message = 'Ingresá un email válido.';
    } elseif (mb_strlen($usuario) < 3 || mb_strlen($usuario) > 50) {
        $status  = 'error';
        $title   = 'Usuario inválido';
        $message = 'El usuario debe tener entre 3 y 50 caracteres.';
    } elseif (mb_strlen($password) < 6) {
        $status  = 'error';
        $title   = 'Contraseña muy corta';
        $message = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        // ✅ VERIFICAR SI EL USUARIO YA EXISTE (antes de intentar insertar)
        $stmt = $conex->prepare("SELECT id FROM usuarios WHERE username = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $status  = 'error';
                $title   = 'Usuario ya registrado';
                $message = 'El nombre de usuario <strong>' . h($usuario) . '</strong> ya está en uso. Por favor, elegí otro.';
                $stmt->close();
            } else {
                $stmt->close();
                
                // ✅ VERIFICAR SI EL EMAIL YA EXISTE
                $stmt = $conex->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->store_result();
                    
                    if ($stmt->num_rows > 0) {
                        $status  = 'error';
                        $title   = 'Email ya registrado';
                        $message = 'El email <strong>' . h($email) . '</strong> ya está registrado. ¿Olvidaste tu contraseña?';
                        $stmt->close();
                    } else {
                        $stmt->close();
                        
                        // ✅ TODO OK - PROCEDER A INSERTAR
                        $password_cifrada = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
                        $rol = 'trabajador';

                        $stmt = $conex->prepare("INSERT INTO usuarios (rol, username, email, password_cifrada) VALUES (?, ?, ?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("ssss", $rol, $usuario, $email, $password_cifrada);
                            $ok = $stmt->execute();

                            if ($ok) {
                                $status  = 'ok';
                                $title   = '¡Usuario registrado!';
                                $message = 'Tu cuenta fue creada correctamente. Ya podés iniciar sesión.';
                            } else {
                                $status  = 'error';
                                $title   = 'No se pudo registrar';
                                
                                // Por si acaso falla a nivel de BD (duplicado que no detectamos antes)
                                if ($stmt->errno === 1062 || $conex->errno === 1062) {
                                    $message = 'El usuario o el email ya están registrados. Probá con otro.';
                                } else {
                                    $message = 'Ocurrió un error inesperado. Intentá nuevamente.';
                                }
                            }
                            $stmt->close();
                        } else {
                            $status  = 'error';
                            $title   = 'Error interno';
                            $message = 'No se pudo preparar la consulta de inserción.';
                        }
                    }
                } else {
                    $status  = 'error';
                    $title   = 'Error interno';
                    $message = 'No se pudo verificar el email.';
                }
            }
        } else {
            $status  = 'error';
            $title   = 'Error interno';
            $message = 'No se pudo verificar el usuario.';
        }
    }
} else {
    $status  = 'error';
    $title   = 'Acceso inválido';
    $message = 'Ingresá desde el formulario de registro.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Registro de usuario – Rows</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- ÚNICO CSS: tu estilo global -->
  <link rel="stylesheet" href="../interfaz/style.css">
</head>
<body class="rows-page">
  <!-- Logo corporativo -->
  <img src="https://electrodos.com.ar/electrodos/images/logo.png" alt="Logo de Electrodos" class="logo" />

  <main class="rows-center">
    <div class="rows-card">
      <div class="rows-body">
        <?php if ($status === 'ok'): ?>
          <div class="rows-icon rows-icon--ok" aria-hidden="true">✓</div>
          <h1 class="rows-title"><?= h($title) ?></h1>
          <p class="rows-msg"><?= $message ?></p>

          <div class="rows-actions">
            <a href="login.php" class="btn-turno">Ir a iniciar sesión</a>
          </div>

          <p class="rows-muted">Si ya tenés usuario creado, podés ingresar ahora.</p>

        <?php else: ?>
          <div class="rows-icon rows-icon--err" aria-hidden="true">✗</div>
          <h1 class="rows-title rows-title--error"><?= h($title) ?></h1>
          <p class="rows-msg"><?= $message ?></p>

          <div class="rows-actions">
            <a href="registro.php" class="btn-turno">Volver al registro</a>
            <?php if (strpos($message, 'email') !== false && strpos($message, 'registrado') !== false): ?>
              <a href="recuperar_clave_form.php" class="btn-outline-turno">Recuperar contraseña</a>
            <?php else: ?>
              <a href="login.php" class="btn-outline-turno">Ir al login</a>
            <?php endif; ?>
          </div>

          <p class="rows-muted">Comprobá los datos e intentá nuevamente.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="rows-footer">Rows · Electrodos</div>
  </main>
</body>
</html>
