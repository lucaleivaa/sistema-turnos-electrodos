<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="../interfaz/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">
    <img src="https://electrodos.com.ar/electrodos/images/logo.png" alt="Logo de Electrodos" class="logo" />
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow">
                    <div class="card-header text-center">
                        <h4>Iniciar sesión</h4>
                    </div>
                    <div class="card-body">
                        <form action="verificar_login.php" method="POST">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" name="usuario" id="usuario" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="login_usuario" class="btn btn-primary">Ingresar</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        ¿No tenés cuenta? <a href="registro.php">Registrate acá</a>
                        ¿Olvidaste tu contraseña? <a href="recuperar_clave_form.php">Recuperar acá</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
