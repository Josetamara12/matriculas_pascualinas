<?php
include('db.php');
session_start();
if (!isset($_SESSION['user']) || empty($_SESSION['estudiante_id'])) {
    header('Location: index.php');
    exit;
}
if (empty($_POST['materias']) || !is_array($_POST['materias'])) {
    header('Location: dashboard.php?msg=Seleccione al menos una materia');
    exit;
}

$periodo_id = isset($_POST['periodo_id']) ? (int)$_POST['periodo_id'] : 0;
$estudiante_id = (int)$_SESSION['estudiante_id'];

if ($periodo_id < 1) {
    header('Location: dashboard.php?msg=Periodo no válido.');
    exit;
}

// Verificar que el periodo esté abierto
$p = mysqli_query($conn, "SELECT id FROM periodos_academicos WHERE id = $periodo_id AND estado = 'abierto' LIMIT 1");
if (!($p && mysqli_num_rows($p) > 0)) {
    header('Location: dashboard.php?msg=El periodo no está abierto para matrícula.');
    exit;
}

// Una sola matrícula por estudiante por periodo
$ya = mysqli_query($conn, "SELECT id FROM matriculas WHERE estudiante_id = $estudiante_id AND periodo_id = $periodo_id AND estado = 'confirmada' LIMIT 1");
if ($ya && mysqli_num_rows($ya) > 0) {
    header('Location: dashboard.php?msg=Ya tiene una matrícula registrada en este periodo.');
    exit;
}

$ids = array_map('intval', $_POST['materias']);
$ids = array_filter($ids);
if (empty($ids)) {
    header('Location: dashboard.php?msg=Seleccione al menos una materia');
    exit;
}

$ids_str = implode(',', $ids);
$res = mysqli_query($conn, "SELECT id, creditos, cupos FROM materias WHERE id IN ($ids_str)");
if (!$res || mysqli_num_rows($res) < 1) {
    header('Location: dashboard.php?msg=Datos de materias no válidos.');
    exit;
}

$total = 0;
$cupos_ok = true;
while ($m = mysqli_fetch_assoc($res)) {
    $total += (int)$m['creditos'];
    if ((int)$m['cupos'] < 1) $cupos_ok = false;
}
// HU-03 CA-02 (Crítico): Bloquear si suma > 20. Bug inyectado: límite en 35 permite matricular 35 cr. (Bug 2)
if ($total > 35) {
    header('Location: dashboard.php?msg=Has superado el límite de créditos.');
    exit;
}
if (!$cupos_ok) {
    header('Location: dashboard.php?msg=Una o más materias no tienen cupos disponibles.');
    exit;
}

// Insertar matrícula
$r = mysqli_query($conn, "INSERT INTO matriculas (estudiante_id, periodo_id, estado, total_creditos) VALUES ($estudiante_id, $periodo_id, 'confirmada', $total)");
if (!$r) {
    header('Location: dashboard.php?msg=Error al registrar la matrícula.');
    exit;
}
$matricula_id = (int)mysqli_insert_id($conn);

// Insertar detalle y descontar cupos
foreach ($ids as $mid) {
    mysqli_query($conn, "INSERT INTO detalle_matricula (matricula_id, materia_id, estado) VALUES ($matricula_id, $mid, 'inscrito')");
    mysqli_query($conn, "UPDATE materias SET cupos = cupos - 1 WHERE id = $mid AND cupos > 0");
}

header('Location: comprobante.php?id=' . $matricula_id);
exit;
