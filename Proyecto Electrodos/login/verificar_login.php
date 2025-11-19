<?php
// tp/login/verificar_login.php
declare(strict_types=1);

// ⚠️ Asegurate de que NO haya BOM ni espacios antes de "<?php" en ESTE archivo ni en con_db.php
require_once __DIR__ . '/con_db.php'; // debe exponer $conex (mysqli)
session_start();

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// Base URL absoluta (host + puerto), ajustá "/tp" si tu raíz cambia
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';   // incluye puerto si corresponde (ej: 192.168.101.92:8084)
$BASE   = $scheme . '://' . $host . '/tp';

function redirect_by_role(string $rol, string $BASE): void {
    if ($rol === 'trabajador') {
        header('Location: ' . $BASE . '/interfaz/panel_trabajador.php'); exit;
    } elseif ($rol === 'tablet') {
        header('Location: ' . $BASE . '/interfaz/inicio_tablet.php'); exit;
    } elseif ($rol === 'admin') {
        header('Location: ' . $BASE . '/interfaz/admin.php'); exit;
    }
    // Rol desconocido -> no redirigimos, se muestra UI de error
}

$status  = 'error';
$title   = 'Acceso inválido';
$message = 'Ingresá desde el formulario de inicio de sesión.';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_usuario'])) {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($usuario === '' || $password === '') {
        $title   = 'Faltan datos';
        $message = 'Por favor, completá usuario y contraseña.';
    } else {
        // Consulta segura
        $stmt = $conex->prepare("SELECT id, username, rol, password_cifrada FROM usuarios WHERE username = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $u = $res->fetch_assoc();
                if (password_verify($password, $u['password_cifrada'])) {
                    // Login OK → sesión y redirección
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $u['id'] ?? $u['username'];  // fallback si no hay columna id
                    $_SESSION['usuario'] = $u['username'];
                    $_SESSION['rol']     = strtolower(trim((string)$u['rol']));

                    redirect_by_role($_SESSION['rol'], $BASE); // sale por header + exit
                    // Si no salió, rol inválido:
                    $title   = 'Rol no reconocido';
                    $message = 'Consultá con el administrador para asignarte un rol válido.';
                } else {
                    $title   = 'Contraseña incorrecta';
                    $message = 'La contraseña no coincide con el usuario ingresado.';
                }
            } else {
                $title   = 'Usuario no encontrado';
                $message = 'Verificá el nombre de usuario o registrate para crear una cuenta.';
            }
            $stmt->close();
        } else {
            $title   = 'Error interno';
            $message = 'No se pudo preparar la consulta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Verificación de acceso – Rows</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Tu CSS (agrego ?v para romper caché si venías probando) -->
  <link rel="stylesheet" href="../interfaz/style.css?v=login-fix-1">
</head>
<body class="rows-page">
  <!-- Vista SOLO para errores (si el login fue OK, ya redirigió y no se renderiza esto) -->
  <img src="https://electrodos.com.ar/electrodos/images/logo.png" alt="Logo de Electrodos" class="logo" />

  <main class="rows-center">
    <div class="rows-card">
      <div class="rows-body">
        <div class="rows-icon rows-icon--err" aria-hidden="true">!</div>
        <h1 class="rows-title rows-title--error"><?= h($title) ?></h1>
        <p class="rows-msg"><?= h($message) ?></p>

        <div class="rows-actions">
          <a href="login.php" class="btn-turno">Volver al login</a>
        </div>

        <?php if ($title === 'Usuario no encontrado'): ?>
          <p class="rows-muted">¿No tenés cuenta? <a class="rows-link" href="registro.php">Registrate acá</a>.</p>
        <?php else: ?>
          <p class="rows-muted">Si el problema persiste, contactá al administrador.</p>
        <?php endif; ?>
      </div>
    </div>
    <div class="rows-footer">Rows · Electrodos</div>
  </main>
</body>
</html>
