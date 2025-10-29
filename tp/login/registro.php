<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';


// Configurar cliente de Google
$google_client = new Google_Client();
$google_client->setClientId('544867149677-gq4up2001pihk4nqmgae94bibsdjm9u8.apps.googleusercontent.com');
$google_client->setClientSecret('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');
$google_client->setRedirectUri('http://192.168.101.92:8084/tp/login/index.php');
$google_client->addScope('email');
$google_client->addScope('profile');

// Si Google devuelve el código de autorización
if (isset($_GET["code"])) {
    $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);

    if (!isset($token["error"])) {
        $google_client->setAccessToken($token['access_token']);
        $_SESSION['access_token'] = $token['access_token'];

        $google_service = new Google_Service_Oauth2($google_client);
        $data = $google_service->userinfo->get();

        $_SESSION['user_email'] = $data['email'];
        $_SESSION['user_name'] = $data['name'];
        $_SESSION['user_picture'] = $data['picture'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Nuevos Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4">Registro de Usuario</h3>

        <?php if (isset($_SESSION['user_email'])): ?>
            <div class="text-center">
                <img src="<?php echo $_SESSION['user_picture']; ?>" width="100" class="rounded-circle mb-3">
                <h5><?php echo $_SESSION['user_name']; ?></h5>
                <p><?php echo $_SESSION['user_email']; ?></p>
                <a href="logout.php" class="btn btn-danger w-100">Cerrar sesión</a>
            </div>
        <?php else: ?>
            <form action="procesar_registro.php" method="POST">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Nombre de Usuario</label>
                    <input type="text" name="usuario" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100" name="registro_usuario">Registrarse</button>
            </form>

            <hr>
            <a href="<?= $google_client->createAuthUrl(); ?>" class="btn btn-danger w-100">
                Iniciar sesión con Google
            </a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>