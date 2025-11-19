<?php
session_start();

// ✅ VERIFICAR SESIÓN (igual que panel_trabajador)
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
  <meta charset="UTF-8">
  <title>Inicio - Turnos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

  <img src="https://electrodos.com.ar/electrodos/images/logo.png" 
       alt="Logo de Electrodos" 
       class="logo" 
       id="logo-logout"
       style="cursor: default;">

  <main class="center-screen">
    <div class="center-container">
      <h1 class="mb-4">Bienvenido a <span style="color:#013761;">Electrodos</span></h1>
      <a href="seleccion.php" class="btn-turno">Sacar turno</a>
    </div>
  </main>

  <!-- ✅ LOGOUT OCULTO: 5 clics en el logo -->
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

    // ✅ BLOQUEAR "VOLVER" DESDE CACHE
    window.addEventListener('pageshow', function(event) {
      if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
      }
    });
  </script>

</body>
</html>
