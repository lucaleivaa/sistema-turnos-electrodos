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

  <!-- Logo -->
  <img src="https://electrodos.com.ar/electrodos/images/logo.png" alt="Logo de Electrodos" class="logo" />

  <main class="rows-center">
    <div class="rows-card">
      <div class="rows-body">
        <h1 class="rows-title" style="color: #013761;">Recuperar Contraseña</h1>
        <p class="rows-msg">Ingresá tu email y te enviaremos una nueva contraseña.</p>

        <form action="recuperar_clave.php" method="POST">
          <div class="mb-3">
            <label for="email" class="form-label-custom">Correo Electrónico</label>
            <input 
              type="email" 
              name="email" 
              id="email"
              class="form-control recuperar-input" 
              placeholder="tu@email.com"
              required>
          </div>

          <div class="rows-actions">
            <button type="submit" class="btn-turno">Recuperar Contraseña</button>
            <a href="login.php" class="btn-outline-turno">Volver al Login</a>
          </div>
        </form>
      </div>
    </div>

    <div class="rows-footer">Rows · Electrodos</div>
  </main>

</body>
</html>
