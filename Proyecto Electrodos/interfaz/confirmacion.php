<?php
session_start();

// ✅ VERIFICAR SESIÓN TABLET
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'tablet') {
    header("Location: ../login/login.php");
    exit;
}

include("plantilla_turno.php");

// ✅ OBTENER DATOS DESDE SESIÓN en lugar de GET
if (!isset($_SESSION['ultimo_turno'])) {
    // Si no hay turno en sesión, redirigir al inicio
    header("Location: inicio_tablet.php");
    exit;
}

$turno = $_SESSION['ultimo_turno']['numero'];
$motivo = $_SESSION['ultimo_turno']['motivo'];
$fecha = $_SESSION['ultimo_turno']['fecha'];

// Generar código ZPL
$zpl = generarZPL($turno, $motivo, $fecha);
$zplEscapado = json_encode($zpl);

// ✅ LIMPIAR SESIÓN después de obtener los datos (para evitar reimpresiones)
// Comentar esta línea si quieres que puedan reimprimir presionando F5
// unset($_SESSION['ultimo_turno']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Confirmación</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css">
  <script src="js/BrowserPrint-3.1.250.min.js"></script>
</head>
<body class="bg-light text-center py-5">
  
  <img src="https://electrodos.com.ar/electrodos/images/logo.png" 
       alt="Logo de Electrodos" 
       class="logo" 
       id="logo-logout"
       style="cursor: default;">
  
  <div class="container">
    <p class="titulo-confirmacion">¡Listo!</p>
    <p class="texto-turno">Tu número es:</p>
    <p class="numero-turno"><?= htmlspecialchars($turno) ?></p>

    <div class="mt-4">
      <a href="inicio_tablet.php" class="btn-volver-inicio">Volver al inicio</a>
    </div>
  </div>

  <script>
    function imprimirTurnoAutomatico() {
        BrowserPrint.getDefaultDevice("printer", function(device) {
            if (device) {
                var zpl = <?php echo $zplEscapado; ?>;
                
                device.send(zpl, 
                    function() {
                        console.log("✓ Turno <?= htmlspecialchars($turno) ?> impreso correctamente");
                    }, 
                    function(error) {
                        console.error("✗ Error al imprimir:", error);
                    }
                );
            } else {
                console.warn("⚠ No se detectó impresora Zebra");
            }
        });
    }

    window.onload = function() {
        setTimeout(imprimirTurnoAutomatico, 800);
    };

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
