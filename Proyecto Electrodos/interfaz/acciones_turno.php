<?php
// tp/interfaz/acciones_turno.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../login/con_db.php'; // $conex (mysqli)

// Permitir solo trabajador o admin
if (empty($_SESSION['rol']) || !in_array($_SESSION['rol'], ['trabajador','admin'], true)) {
  header('Location: ../login/login.php'); exit;
}

$accion = $_POST['accion'] ?? '';
$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) { header('Location: panel_trabajador.php'); exit; }

if ($accion === 'iniciar') {
  // De pendiente -> atendiendo. Marca inicio solo si aún no estaba.
  $stmt = $conex->prepare("
    UPDATE turnos
       SET estado = 'atendiendo',
           inicio_atencion = IFNULL(inicio_atencion, NOW())
     WHERE id = ? AND estado = 'pendiente'
  ");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();

} elseif ($accion === 'finalizar') {
  // A 'atendido' desde pendiente/atendiendo. Marca fin ahora.
  $stmt = $conex->prepare("
    UPDATE turnos
       SET estado = 'atendido',
           fin_atencion = NOW()
     WHERE id = ? AND estado IN ('pendiente','atendiendo')
  ");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
}

// Volver al panel
header('Location: panel_trabajador.php');
exit;
