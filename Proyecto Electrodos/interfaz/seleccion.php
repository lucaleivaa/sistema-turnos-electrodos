<?php
session_start();

// ✅ VERIFICAR SESIÓN TABLET
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'tablet') {
    header("Location: ../login/login.php");
    exit;
}

// ✅ EVITAR CACHÉ
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Seleccionar Motivo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light text-center">
  
  <img src="https://electrodos.com.ar/electrodos/images/logo.png" 
       alt="Logo de Electrodos" 
       class="logo" 
       id="logo-logout"
       style="cursor: default;">

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
        <a href="inicio_tablet.php" class="btn-volver">← Volver</a>
      </div>
    </div>
  </main>

  <script>
    (function() {
        const logo = document.getElementById('logo-logout');
        let clickCount = 0;
        let resetTimer = null;

        logo.addEventListener('click', function() {
            clickCount++;
            
            if (resetTimer) {
                clearTimeout(resetTimer);
            }
            
            if (clickCount >= 5) {
                if (confirm('¿Cerrar sesión del kiosco?')) {
                    window.location.href = 'logout_tablet.php';
                }
                clickCount = 0;
                return;
            }
            
            resetTimer = setTimeout(function() {
                clickCount = 0;
            }, 3000);
        });
    })();

    window.addEventListener('pageshow', function(event) {
      if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
      }
    });
  </script>

</body>
</html>
