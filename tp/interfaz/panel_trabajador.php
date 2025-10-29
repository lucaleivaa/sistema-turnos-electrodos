<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'trabajador') {
    header("Location: ../login/login.php");
    exit;
}

/* Evitar caché */
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/* Conexión */
$conexion = new mysqli("192.168.101.93", "AG04", "St2025#QUcwNA", "ag04");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

/* Acciones: iniciar (pendiente -> atendiendo) / finalizar (-> atendido)
   Con confirmación del lado servidor (checkbox 'confirmar') */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['turno_id'], $_POST['accion'])) {

    if (empty($_POST['confirmar'])) {
        // Si no marcaron el check, no procesamos
        header("Location: panel_trabajador.php");
        exit;
    }

    $id = (int)$_POST['turno_id'];

    if ($_POST['accion'] === 'iniciar') {
        $stmt = $conexion->prepare("
          UPDATE turnos
             SET estado = 'atendiendo',
                 inicio_atencion = IFNULL(inicio_atencion, NOW())
           WHERE id = ? AND estado = 'pendiente'
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

    } elseif ($_POST['accion'] === 'finalizar') {
        $stmt = $conexion->prepare("
          UPDATE turnos
             SET estado = 'atendido',
                 fin_atencion = NOW()
           WHERE id = ? AND estado IN ('pendiente','atendiendo')
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: panel_trabajador.php");
    exit;
}

/* Obtener turnos por motivo
   Usamos ts = COALESCE(creado_en, fecha 00:00) para mostrar fecha+hora */
function obtenerTurnos($conexion, $motivo) {
    $stmt = $conexion->prepare("
        SELECT
            id,
            numero,
            motivo,
            estado,
            COALESCE(
              creado_en,
              CAST(CONCAT(fecha,' 00:00:00') AS DATETIME)
            ) AS ts
        FROM turnos
        WHERE estado IN ('pendiente','atendiendo')
          AND LOWER(REPLACE(motivo, 'á', 'a')) LIKE LOWER(REPLACE(?, 'á', 'a'))
        ORDER BY
          CASE estado WHEN 'atendiendo' THEN 0 ELSE 1 END,
          ts ASC
    ");
    $stmt->bind_param("s", $motivo);
    $stmt->execute();
    return $stmt->get_result();
}

/* Obtener datasets */
$compras      = obtenerTurnos($conexion, "compra");
$retiros      = obtenerTurnos($conexion, "retiro");
$asistencia   = obtenerTurnos($conexion, "asistencia");
$presupuestos = obtenerTurnos($conexion, "presupuesto");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- Si más adelante usás realtime, quitá este refresh -->
  <meta http-equiv="refresh" content="10">
  <meta charset="UTF-8">
  <title>Panel de Turnos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css">
  <style>
    .tabla-container { max-height: 80vh; overflow-y: auto; }
    .titulo-tabla { text-align: center; font-weight: bold; margin-bottom: 10px; color: #013761; }
  </style>
</head>
<body class="bg-light py-3">

<div class="d-flex justify-content-end px-4 py-2">
  <a href="../interfaz/logout_trabajador.php" class="btn-cerrar-sesion">Cerrar sesión</a>
</div>

<div class="container-fluid">
  <p class="turnos_pendientes">Turnos Pendientes</p>

  <div class="row">
    <?php
    function mostrarTabla($titulo, $resultado) {
        echo "<div class='col-md-3 tabla-container'>";
        echo "<p class='titulo-tabla'>" . htmlspecialchars($titulo) . "</p>";
        if ($resultado && $resultado->num_rows > 0) {
            echo "<table class='tabla_turnos'>";
            echo "<thead><tr><th>Turno</th><th>Fecha y hora</th><th>Acción</th></tr></thead><tbody>";
            while ($fila = $resultado->fetch_assoc()) {
                $tsRaw  = $fila['ts'] ?? null;
                $fecha_formateada = $tsRaw ? date("d/m/Y H:i", strtotime($tsRaw)) . " hs" : "-";

                $id      = (int)$fila['id'];
                $numero  = htmlspecialchars($fila['numero']);
                $estado  = $fila['estado'];

                // Acción con checkbox de confirmación (required) + validación servidor
                if ($estado === 'pendiente') {
                    $accionHtml = '
                        <form method="post" class="acciones-form" style="margin:0;">
                          <input type="hidden" name="turno_id" value="'.$id.'">
                          <input type="hidden" name="accion" value="iniciar">
                          <label class="confirm-check">
                            <input type="checkbox" name="confirmar" required>
                            Confirmo iniciar la atención
                          </label>
                          <button type="submit" class="btn-custom" style="margin-top:6px;">Atender</button>
                        </form>';
                } elseif ($estado === 'atendiendo') {
                    $accionHtml = '
                        <form method="post" class="acciones-form" style="margin:0;">
                          <input type="hidden" name="turno_id" value="'.$id.'">
                          <input type="hidden" name="accion" value="finalizar">
                          <label class="confirm-check">
                            <input type="checkbox" name="confirmar" required>
                            Confirmo finalizar el turno
                          </label>
                          <button type="submit" class="btn-custom btn-finalizar" style="margin-top:6px;">Atendido</button>
                        </form>';
                } else {
                    $accionHtml = '<span class="badge-estado">Atendido</span>';
                }

                echo "<tr>
                        <td><strong>{$numero}</strong></td>
                        <td>{$fecha_formateada}</td>
                        <td>{$accionHtml}</td>
                      </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p class='text-muted text-center'>Sin turnos</p>";
        }
        echo "</div>";
    }

    mostrarTabla("Compras", $compras);
    mostrarTabla("Retiros", $retiros);
    mostrarTabla("Asistencia Técnica", $asistencia);
    mostrarTabla("Presupuestos", $presupuestos);
    ?>
  </div>
</div>

<script>
/* Maneja volver desde cache del navegador */
window.addEventListener('pageshow', function(event) {
  if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
    window.location.reload();
  }
});
</script>
</body>
</html>
