<?php
include('db.php');
session_start();

$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';

$mensaje = '';
if (!empty($_SESSION['msg_error'])) {
    $mensaje = $_SESSION['msg_error'];
    unset($_SESSION['msg_error']);
}

if ($user !== '' && $pass !== '') {
    $user_esc = mysqli_real_escape_string($conn, $user);
    $pass_esc = mysqli_real_escape_string($conn, $pass);
    $res = mysqli_query($conn, "SELECT id FROM usuarios WHERE usuario = '$user_esc' AND password = '$pass_esc' LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['user'] = $user;
        $uid = (int)$row['id'];
        $est = mysqli_query($conn, "SELECT id, codigo, nombres, apellidos FROM estudiantes WHERE usuario_id = $uid AND estado = 'activo' LIMIT 1");
        if ($est && $est_row = mysqli_fetch_assoc($est)) {
            $_SESSION['estudiante_id'] = (int)$est_row['id'];
            $_SESSION['estudiante_codigo'] = $est_row['codigo'];
            $_SESSION['estudiante_nombre'] = trim($est_row['nombres'] . ' ' . $est_row['apellidos']);
        } else {
            $_SESSION['estudiante_id'] = null;
        }
        header('Location: dashboard.php');
        exit;
    }
    $mensaje = 'Error de credenciales';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - IUPB</title>
    <link rel="icon" type="image/png" href="cropped-favicon-192x192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #083e65 0%, #052a47 100%); min-height: 100vh; }
        .login-card { max-width: 380px; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.2); }
        .brand-title { color: #083e65; font-weight: 700; }
        /* HU-06: Verde #00843D, texto blanco. Bug 1: inyectado rojo #FF0000 */
        .btn-iacceso { background-color: #FF0000; color: white; border: none; padding: 0.6rem 1rem; }
        .btn-iacceso:hover { background-color: #cc0000; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-4">
    <div class="card login-card rounded-3">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <img src="cropped-Imagotipo-horizontal_acreditados.png" alt="IUPB" class="img-fluid" style="max-height: 60px;">
            </div>
            <h3 class="card-title text-center mb-4 brand-title">Portal de Matrícula IUPB</h3>
            <?php if ($mensaje !== '') { ?>
                <div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php } ?>
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="user" class="form-label">Usuario</label>
                    <input type="text" class="form-control form-control-lg" id="user" name="user" placeholder="Usuario" value="<?php echo htmlspecialchars($user); ?>" required autocomplete="username">
                </div>
                <div class="mb-4">
                    <label for="pass" class="form-label">Clave</label>
                    <input type="password" class="form-control form-control-lg" id="pass" name="pass" placeholder="Clave" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-iacceso w-100 btn-lg">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>
