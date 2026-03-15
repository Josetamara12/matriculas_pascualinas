<?php 
include('db.php'); 
session_start();
// HU-05 CA-02: impedir re-ingreso con Atrás del navegador
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }
if (empty($_SESSION['estudiante_id'])) {
    $_SESSION['msg_error'] = 'Su usuario no tiene perfil de estudiante activo.';
    header('Location: login.php');
    exit;
}

$estudiante_id = (int)$_SESSION['estudiante_id'];
$programa = null;
$programa_id = null;
$res_prog = mysqli_query($conn, "SELECT p.id, p.codigo, p.nombre FROM programas p INNER JOIN estudiantes e ON e.programa_id = p.id WHERE e.id = $estudiante_id LIMIT 1");
if ($res_prog && $row_prog = mysqli_fetch_assoc($res_prog)) {
    $programa = $row_prog;
    $programa_id = (int)$row_prog['id'];
}

$periodo = null;
$res_periodo = mysqli_query($conn, "SELECT id, codigo, anio, semestre FROM periodos_academicos WHERE estado = 'abierto' LIMIT 1");
if ($res_periodo && $row_p = mysqli_fetch_assoc($res_periodo)) {
    $periodo = $row_p;
}
// HU-08: Carga en menos de 2.0 s. Bug inyectado: retraso forzado de 5 s (Bug 4)
sleep(5);
// Solo materias del programa del estudiante o transversales (programa_id NULL)
$programa_cond = $programa_id ? "(materias.programa_id = $programa_id OR materias.programa_id IS NULL)" : "1=1";
$res = mysqli_query($conn, "SELECT * FROM materias WHERE cupos > 0 AND $programa_cond ORDER BY materias.codigo");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oferta Académica - IUPB</title>
    <link rel="icon" type="image/png" href="cropped-favicon-192x192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-iacceso { background-color: #083e65 !important; }
        .navbar-iacceso .navbar-brand, .navbar-iacceso .nav-link { color: rgba(255,255,255,0.95) !important; }
        .navbar-iacceso .nav-link:hover { color: #fff !important; }
        .btn-iacceso { background-color: #083e65; color: white; border: none; }
        .btn-iacceso:hover { background-color: #062d4a; color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-iacceso">
        <div class="container">
            <span class="navbar-brand mb-0">IUPB - Matrículas</span>
            <span class="navbar-text text-white me-3">Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['estudiante_nombre'] ?? $_SESSION['user']); ?></strong> <span class="opacity-75">(<?php echo htmlspecialchars($_SESSION['estudiante_codigo'] ?? ''); ?>)</span></span>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-light btn-sm" href="dashboard.php">Menú Principal</a>
                <a class="btn btn-outline-light btn-sm" href="logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <main class="container my-4">
        <?php 
        if (!empty($_SESSION['msg_error'])) { 
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['msg_error']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            unset($_SESSION['msg_error']);
        }
        if (!empty($_GET['msg'])) { ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php } ?>

        <?php if (!$periodo) { ?>
            <div class="alert alert-info">No hay periodo académico abierto para matrícula en este momento.</div>
        <?php } else { ?>
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Oferta Académica</h5>
                <span class="badge bg-secondary">Periodo <?php echo htmlspecialchars($periodo['codigo']); ?> (<?php echo (int)$periodo['anio']; ?>-<?php echo (int)$periodo['semestre']; ?>)</span>
            </div>
            <div class="card-body">
                <?php if ($programa) { ?>
                <p class="mb-3"><strong>Programa al que se matricula:</strong> <span class="text-primary"><?php echo htmlspecialchars($programa['nombre']); ?></span> <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($programa['codigo']); ?></span></p>
                <?php } ?>
                <p class="text-muted mb-4">Seleccione las materias a matricular (máximo 20 créditos).</p>
                <form action="matricular.php" method="POST">
                    <input type="hidden" name="periodo_id" value="<?php echo (int)($periodo['id'] ?? 0); ?>">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 3rem;">Sel.</th>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Créditos</th>
                                    <th>Cupos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($m = mysqli_fetch_assoc($res)) { ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" name="materias[]" value="<?php echo (int)$m['id']; ?>">
                                    </td>
                                    <td><?php echo (int)$m['id']; ?></td>
                                    <td><?php echo htmlspecialchars($m['nombre']); ?></td>
                                    <td><?php echo (int)$m['creditos']; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo (int)$m['cupos']; ?></span></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <button type="submit" class="btn btn-iacceso btn-lg">Confirmar Matrícula</button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">Menú Principal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php } ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
