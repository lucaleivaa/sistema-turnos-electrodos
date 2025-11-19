<?php
session_start();

// ✅ VERIFICAR SESIÓN TABLET
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'tablet') {
    header("Location: ../login/login.php");
    exit;
}

// Conexión
$conexion = new mysqli("192.168.101.93", "AG04", "St2025#QUcwNA", "ag04");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset('utf8mb4');

// Obtener motivo
$motivo = $_GET['motivo'] ?? '';
$motivos_validos = ['compra', 'retiro', 'asistencia', 'presupuesto'];

if (!in_array($motivo, $motivos_validos)) {
    header("Location: inicio_tablet.php");
    exit;
}

// Prefijos por motivo
$prefijos = [
    'compra'      => 'C',
    'retiro'      => 'R',
    'asistencia'  => 'A',
    'presupuesto' => 'P'
];

$prefijo = $prefijos[$motivo];
$lock_name = "generar_turno_{$motivo}";

// Obtener lock
$conexion->query("SELECT GET_LOCK('$lock_name', 10)");

// ✅ OBTENER ÚLTIMO NÚMERO DEL DÍA ACTUAL SOLAMENTE
$fecha_actual = date('Y-m-d');
$sql = "SELECT numero FROM turnos 
        WHERE numero LIKE ? 
        AND fecha = ?
        ORDER BY id DESC LIMIT 1";
$stmt = $conexion->prepare($sql);
$patron = $prefijo . '%';
$stmt->bind_param('ss', $patron, $fecha_actual);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Hay turnos hoy, incrementar
    $ultimo_numero = (int)substr($row['numero'], 1);
    $nuevo_numero = $ultimo_numero + 1;
} else {
    // No hay turnos hoy, empezar en 1
    $nuevo_numero = 1;
}

$numero_turno = $prefijo . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);

// Insertar turno
$stmt = $conexion->prepare("INSERT INTO turnos (numero, motivo, estado, fecha) VALUES (?, ?, 'pendiente', ?)");
$stmt->bind_param('sss', $numero_turno, $motivo, $fecha_actual);
$stmt->execute();

// Liberar lock
$conexion->query("SELECT RELEASE_LOCK('$lock_name')");

$stmt->close();
$conexion->close();

// Guardar en sesión
$_SESSION['ultimo_turno'] = [
    'numero' => $numero_turno,
    'motivo' => $motivo,
    'fecha' => date('d/m/Y H:i') . ' hs'
];

// Redirigir
header("Location: confirmacion.php");
exit;
?>
