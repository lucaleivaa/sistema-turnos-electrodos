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

/* Obtener turnos por motivo */
function obtenerTurnos($conexion, $motivo) {
    // ✅ Agregar filtro por fecha actual
    $fecha_actual = date('Y-m-d');
    
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
          AND fecha = ?
        ORDER BY
          CASE estado WHEN 'atendiendo' THEN 0 ELSE 1 END,
          ts ASC
    ");
    $stmt->bind_param("ss", $motivo, $fecha_actual);
    $stmt->execute();
    return $stmt->get_result();
}


/* Obtener datasets SOLO DEL DÍA ACTUAL */
$compras      = obtenerTurnos($conexion, "compra");
$retiros      = obtenerTurnos($conexion, "retiro");
$asistencia   = obtenerTurnos($conexion, "asistencia");
$presupuestos = obtenerTurnos($conexion, "presupuesto");

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- ❌ QUITAMOS EL REFRESH MANUAL -->
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
    function mostrarTabla($titulo, $resultado, $motivo) {
        echo "<div class='col-md-3 tabla-container'>";
        echo "<p class='titulo-tabla'>" . htmlspecialchars($titulo) . "</p>";
        
        // ✅ AGREGAMOS data-motivo para identificar la tabla
        echo "<table class='tabla_turnos' data-motivo='{$motivo}'>";
        echo "<thead><tr><th>Turno</th><th>Fecha y hora</th><th>Acción</th></tr></thead>";
        echo "<tbody>";
        
        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $tsRaw  = $fila['ts'] ?? null;
                $fecha_formateada = $tsRaw ? date("d/m/Y H:i", strtotime($tsRaw)) . " hs" : "-";

                $id      = (int)$fila['id'];
                $numero  = htmlspecialchars($fila['numero']);
                $estado  = $fila['estado'];

                // Acción con checkbox de confirmación
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
        }
        
        echo "</tbody></table>";
        
        if (!$resultado || $resultado->num_rows === 0) {
            echo "<p class='text-muted text-center'>Sin turnos</p>";
        }
        
        echo "</div>";
    }

    mostrarTabla("Compras", $compras, "compra");
    mostrarTabla("Retiros", $retiros, "retiro");
    mostrarTabla("Asistencia Técnica", $asistencia, "asistencia");
    mostrarTabla("Presupuestos", $presupuestos, "presupuesto");
    ?>
  </div>
</div>

<!-- ✅ JAVASCRIPT PARA ACTUALIZACIÓN AUTOMÁTICA CON GET -->
<script>
// Función para actualizar turnos usando GET (método por defecto de fetch)
function actualizarTurnos() {
    fetch('get_turnos.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Turnos actualizados:', new Date().toLocaleTimeString());
            
            // Actualizar cada columna
            actualizarColumna('compra', data.compra);
            actualizarColumna('retiro', data.retiro);
            actualizarColumna('asistencia', data.asistencia);
            actualizarColumna('presupuesto', data.presupuesto);
        })
        .catch(error => {
            console.error('❌ Error al actualizar:', error);
        });
}

// Función para actualizar una columna específica
function actualizarColumna(motivo, turnos) {
    const tabla = document.querySelector(`table[data-motivo="${motivo}"] tbody`);
    if (!tabla) {
        console.warn(`⚠ No se encontró tabla para motivo: ${motivo}`);
        return;
    }
    
    // Si hay checkboxes marcados, NO actualizar (el usuario está interactuando)
    const checkboxesMarcados = tabla.querySelectorAll('input[type="checkbox"]:checked').length;
    if (checkboxesMarcados > 0) {
        console.log(`⏸ Omitiendo actualización de ${motivo} - usuario confirmando acción`);
        return;
    }
    
    // Limpiar tabla
    tabla.innerHTML = '';
    
    // Si no hay turnos, no agregar filas
    if (turnos.length === 0) {
        return;
    }
    
    // Agregar filas
    turnos.forEach(turno => {
        const fila = document.createElement('tr');
        
        // Columna Turno
        const celdaTurno = document.createElement('td');
        celdaTurno.innerHTML = `<strong>${turno.numero}</strong>`;
        fila.appendChild(celdaTurno);
        
        // Columna Fecha y hora
        const celdaFecha = document.createElement('td');
        const fecha = new Date(turno.ts);
        celdaFecha.textContent = fecha.toLocaleDateString('es-AR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        }) + ' ' + fecha.toLocaleTimeString('es-AR', {
            hour: '2-digit',
            minute: '2-digit'
        }) + ' hs';
        fila.appendChild(celdaFecha);
        
        // Columna Acción
        const celdaAccion = document.createElement('td');
        
        if (turno.estado === 'pendiente') {
            celdaAccion.innerHTML = `
                <form method="post" class="acciones-form" style="margin:0;">
                  <input type="hidden" name="turno_id" value="${turno.id}">
                  <input type="hidden" name="accion" value="iniciar">
                  <label class="confirm-check">
                    <input type="checkbox" name="confirmar" required>
                    Confirmo iniciar la atención
                  </label>
                  <button type="submit" class="btn-custom" style="margin-top:6px;">Atender</button>
                </form>
            `;
        } else if (turno.estado === 'atendiendo') {
            celdaAccion.innerHTML = `
                <form method="post" class="acciones-form" style="margin:0;">
                  <input type="hidden" name="turno_id" value="${turno.id}">
                  <input type="hidden" name="accion" value="finalizar">
                  <label class="confirm-check">
                    <input type="checkbox" name="confirmar" required>
                    Confirmo finalizar el turno
                  </label>
                  <button type="submit" class="btn-custom btn-finalizar" style="margin-top:6px;">Atendido</button>
                </form>
            `;
        } else {
            celdaAccion.innerHTML = '<span class="badge-estado">Atendido</span>';
        }
        
        fila.appendChild(celdaAccion);
        tabla.appendChild(fila);
    });
}

// ✅ Ejecutar actualización cada 3 segundos (3000 milisegundos)
setInterval(actualizarTurnos, 3000);

// ✅ Ejecutar una vez al cargar la página (opcional, ya se carga con PHP)
// actualizarTurnos();

/* Maneja volver desde cache del navegador */
window.addEventListener('pageshow', function(event) {
  if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
    window.location.reload();
  }
});
</script>
</body>
</html>
