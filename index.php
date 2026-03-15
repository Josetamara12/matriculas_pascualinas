<?php include('db.php'); session_start(); ?>
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
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="user" class="form-label">Usuario</label>
                    <input type="text" class="form-control form-control-lg" id="user" name="user" placeholder="Usuario" required autocomplete="username">
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
