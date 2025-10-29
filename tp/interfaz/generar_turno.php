<?php
session_start();

/* Prefijos por motivo */
$prefijos = [
  'compra'      => 'C',
  'retiro'      => 'R',
  'asistencia'  => 'A',
  'presupuesto' => 'P'
];

/* Validar motivo */
$motivo = isset($_GET['motivo']) ? strtolower(trim($_GET['motivo'])) : '';
if (!isset($prefijos[$motivo])) {
  header("Location: seleccion.php"); exit;
}
$prefijo = $prefijos[$motivo];

/* Conexión (podés mover estos datos a login/con_db.php) */
$conexion = new mysqli("192.168.101.93", "AG04", "St2025#QUcwNA", "ag04");
if ($conexion->connect_error) { die("Error de conexión: ".$conexion->connect_error); }
$conexion->set_charset('utf8mb4');

/* Transacción + lock por motivo (evita números duplicados si dos tablets disparan a la vez) */
$conexion->begin_transaction();
try {
  $lockName = "ag04_num_".$prefijo;
  $conexion->query("SELECT GET_LOCK('$lockName', 5)"); // espera hasta 5s

  // Buscar último número con ese prefijo
  $sql = "SELECT numero FROM turnos WHERE numero LIKE CONCAT(?, '%') ORDER BY id DESC LIMIT 1";
  $st = $conexion->prepare($sql);
  $st->bind_param('s', $prefijo);
  $st->execute();
  $st->bind_result($ultimo);
  $st->fetch();
  $st->close();

  $n = 1;
  if ($ultimo) {
    $dig = preg_replace('/\D/', '', $ultimo); // toma solo dígitos
    $n = (int)$dig + 1;
  }
  $nuevo_numero = $prefijo . str_pad($n, 3, '0', STR_PAD_LEFT);

  // Insertar turno
  $st = $conexion->prepare("INSERT INTO turnos (numero, motivo) VALUES (?, ?)");
  $st->bind_param("ss", $nuevo_numero, $motivo);
  $st->execute();
  $turno_id = $st->insert_id;
  $st->close();

  // Encolar impresión (para que el worker la tome)
  $conexion->query("INSERT INTO print_queue (turno_id) VALUES ($turno_id)");

  // Liberar lock y cerrar
  $conexion->query("SELECT RELEASE_LOCK('$lockName')");
  $conexion->commit();

header("Location: confirmacion.php?turno=" . urlencode($nuevo_numero) . "&motivo=" . urlencode($motivo));

  exit;

} catch (Throwable $e) {
  $conexion->rollback();
  @$conexion->query("SELECT RELEASE_LOCK('$lockName')");
  die("Error generando turno: ".$e->getMessage());
}
