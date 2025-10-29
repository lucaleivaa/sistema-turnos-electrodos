<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Seleccionar Motivo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css?v=offset-1">
</head>
<body class="bg-light text-center">
  <!-- Logo fijo arriba a la derecha -->
  <img src="https://electrodos.com.ar/electrodos/images/logo.png" alt="Logo de Electrodos" class="logo" />

  <!-- Contenido ligeramente más abajo -->
  <main class="kiosk-offset">
    <div class="container">
      <h2 class="visita mb-5">¿Cuál es el motivo de su visita?</h2>

      <div class="row g-4 justify-content-center">
        <div class="col-6">
          <a href="generar_turno.php?motivo=compra" class="btn-custom2">Compra</a>
        </div>
        <div class="col-6">
          <a href="generar_turno.php?motivo=retiro" class="btn-custom2">Retiro</a>
        </div>
        <div class="col-6">
          <a href="generar_turno.php?motivo=asistencia" class="btn-custom2">Asistencia técnica</a>
        </div>
        <div class="col-6">
          <a href="generar_turno.php?motivo=presupuesto" class="btn-custom2">Presupuesto</a>
        </div>
      </div>

      <div class="mt-5">
        <a href="inicio.html" class="btn-volver">← Volver</a>
      </div>
    </div>
  </main>
</body>
</html>
