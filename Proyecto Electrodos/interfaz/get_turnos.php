<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'trabajador') {
    http_response_code(403);
    exit(json_encode(['error' => 'No autorizado']));
}

$conexion = new mysqli("192.168.101.93", "AG04", "St2025#QUcwNA", "ag04");
if ($conexion->connect_error) {
    http_response_code(500);
    exit(json_encode(['error' => 'Error de conexión']));
}
$conexion->set_charset('utf8mb4');

function obtenerTurnos($conexion, $motivo) {
    // ✅ Filtrar por fecha actual
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
    $stmt->bind_param('ss', $motivo, $fecha_actual);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $turnos = [];
    while ($fila = $result->fetch_assoc()) {
        $turnos[] = $fila;
    }
    
    return $turnos;
}

$resultado = [
    'compra' => obtenerTurnos($conexion, 'compra'),
    'retiro' => obtenerTurnos($conexion, 'retiro'),
    'asistencia' => obtenerTurnos($conexion, 'asistencia'),
    'presupuesto' => obtenerTurnos($conexion, 'presupuesto'),
    'timestamp' => time()
];

echo json_encode($resultado);
$conexion->close();
?>
