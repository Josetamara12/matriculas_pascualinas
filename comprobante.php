<?php 
include('db.php'); 
session_start();
// HU-05 CA-02: no cachear para impedir re-ingreso con Atrás
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
if (!isset($_SESSION['user']) || empty($_SESSION['estudiante_id'])) {
    header('Location: index.php');
    exit;
}

$matricula_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($matricula_id < 1) {
    header('Location: dashboard.php?msg=Comprobante no válido.');
    exit;
}

$estudiante_id = (int)$_SESSION['estudiante_id'];
$mat = mysqli_query($conn, "SELECT m.id, m.fecha_matricula, m.total_creditos, m.estado, p.codigo AS periodo_codigo, p.anio, p.semestre 
    FROM matriculas m 
    JOIN periodos_academicos p ON p.id = m.periodo_id 
    WHERE m.id = $matricula_id AND m.estudiante_id = $estudiante_id AND m.estado = 'confirmada' LIMIT 1");
if (!($mat && $matricula = mysqli_fetch_assoc($mat))) {
    header('Location: dashboard.php?msg=Comprobante no encontrado.');
    exit;
}

$res = mysqli_query($conn, "SELECT ma.codigo, ma.nombre, ma.creditos FROM detalle_matricula d 
    JOIN materias ma ON ma.id = d.materia_id 
    WHERE d.matricula_id = $matricula_id AND d.estado = 'inscrito' ORDER BY ma.codigo");

$programa = null;
$res_prog = mysqli_query($conn, "SELECT p.codigo, p.nombre FROM programas p INNER JOIN estudiantes e ON e.programa_id = p.id WHERE e.id = $estudiante_id LIMIT 1");
if ($res_prog && $row_prog = mysqli_fetch_assoc($res_prog)) {
    $programa = $row_prog;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobante - IUPB</title>
    <link rel="icon" type="image/png" href="cropped-favicon-192x192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-comprobante { max-width: 520px; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1); }
        .header-success { background: linear-gradient(135deg, #083e65 0%, #062d4a 100%); color: white; }
        .btn-iacceso { background-color: #083e65; color: white; border: none; }
        .btn-iacceso:hover { background-color: #062d4a; color: white; }
        .badge-iacceso { background-color: #083e65; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="card card-comprobante rounded-3 overflow-hidden">
        <div class="card-header header-success py-3 text-center">
            <h4 class="mb-0">Matrícula Exitosa</h4>
            <p class="mb-0 small opacity-90">IUPB - Institución Universitaria Pascual Bravo</p>
            <p class="mb-0 mt-1 small opacity-75">Periodo <?php echo htmlspecialchars($matricula['periodo_codigo']); ?> (<?php echo (int)$matricula['anio']; ?>-<?php echo (int)$matricula['semestre']; ?>)</p>
        </div>
        <div class="card-body p-4">
            <p class="text-muted small mb-1">Matrícula N.º <?php echo (int)$matricula['id']; ?> — <?php echo date('d/m/Y H:i', strtotime($matricula['fecha_matricula'])); ?></p>
            <?php if ($programa) { ?><p class="small mb-2"><strong>Programa:</strong> <?php echo htmlspecialchars($programa['nombre']); ?> (<?php echo htmlspecialchars($programa['codigo']); ?>)</p><?php } ?>
            <p class="text-muted mb-2">Materias inscritas:</p>
            <ul class="list-group list-group-flush mb-4">
                <?php while($m = mysqli_fetch_assoc($res)) { ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars($m['codigo']); ?> — <?php echo htmlspecialchars($m['nombre']); ?></span>
                        <span class="badge bg-light text-dark"><?php echo (int)$m['creditos']; ?> cr.</span>
                    </li>
                <?php } ?>
            </ul>
            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <h5 class="mb-0">Total Créditos: <span class="badge badge-iacceso fs-6"><?php echo (int)$matricula['total_creditos']; ?></span></h5>
                <!-- HU-07 CA-01: Bug inyectado: falta botón Regresar/Menú Principal (Bug 3) -->
                <a href="logout.php" class="btn btn-iacceso">Salir</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
