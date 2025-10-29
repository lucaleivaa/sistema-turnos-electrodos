<?php
include("plantilla_turno.php");

// Verificar que se haya recibido el número de turno
$turno = $_GET['turno'] ?? '';
$motivo = $_GET['motivo'] ?? '';

if ($turno === '') {
    header("Location: inicio.html");
    exit;
}

// Generar código ZPL para la impresión
$fecha = date('d/m/Y H:i') . ' hs';
$zpl = generarZPL($turno, $motivo, $fecha);
$zplEscapado = json_encode($zpl);
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
<img src="https://electrodos.com.ar/electrodos/images/logo.png" alt="Logo de Electrodos" class="logo" />
  <div class="container">
    <p class="titulo-confirmacion">¡Listo!</p>
    <p class="texto-turno">Tu número es:</p>
    <p class="numero-turno"><?= htmlspecialchars($turno) ?></p>

    <div class="mt-4">
      <a href="inicio.html" class="btn-volver-inicio">Volver al inicio</a>
    </div>
  </div>

  <script>
    // Función para imprimir automáticamente
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
                        // No mostrar alerta para no interrumpir la UX del kiosco
                    }
                );
            } else {
                console.warn("⚠ No se detectó impresora Zebra (Browser Print no disponible)");
            }
        });
    }

    // Ejecutar automáticamente al cargar la página
    window.onload = function() {
        // Espera 800ms para que cargue Browser Print
        setTimeout(imprimirTurnoAutomatico, 800);
    };
  </script>

</body>
</html>
