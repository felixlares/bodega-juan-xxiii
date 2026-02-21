<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error de seguridad (CSRF). Por favor recargue la página.");
    }

    $pass_actual = $_POST['pass_actual'] ?? '';
    $pass_nueva = $_POST['pass_nueva'] ?? '';
    $pass_confirmacion = $_POST['pass_confirmacion'] ?? '';
    $user_id = $_SESSION['admin_user_id'];

    if (empty($pass_actual) || empty($pass_nueva) || empty($pass_confirmacion)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($pass_nueva !== $pass_confirmacion) {
        $error = "Las contraseñas nuevas no coinciden.";
    } elseif (strlen($pass_nueva) < 6) {
        $error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } else {
        try {
            $pdo->exec("USE `$dbname`");
            $stmt = $pdo->prepare("SELECT password_hash FROM usuarios_admin WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass_actual, $user['password_hash'])) {
                // Contraseña actual correcta, actualizar a la nueva
                $nuevo_hash = password_hash($pass_nueva, PASSWORD_BCRYPT);
                $update_stmt = $pdo->prepare("UPDATE usuarios_admin SET password_hash = ? WHERE id = ?");
                $update_stmt->execute([$nuevo_hash, $user_id]);
                $success = "Contraseña actualizada exitosamente.";
            } else {
                $error = "La contraseña actual es incorrecta.";
            }
        } catch (PDOException $e) {
            $error = "Error de conexión a la base de datos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Panel de Administración</title>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            color: #333;
        }

        .header {
            background: #212529;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
            font-size: 1.25rem;
        }

        .header a {
            color: white;
            text-decoration: none;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background 0.2s;
        }

        .header a:hover {
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .container {
            padding: 2rem;
            max-width: 500px;
            margin: 0 auto;
            background: white;
            margin-top: 2rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: #0d6efd;
            outline: none;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            display: inline-block;
            width: 100%;
        }

        .btn-primary {
            background: #0d6efd;
        }

        .btn-primary:hover {
            background: #0b5ed7;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        .alert-error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Bodega Juan XXIII - Admin</h2>
        <div>
            <a href="index.php" style="background: transparent; border: 1px solid white;">Productos</a>
            <a href="configuracion.php"
                style="background: transparent; border: 1px solid white; margin-left: 0.5rem;">Configuración</a>
            <a href="cambiar_password.php"
                style="background: rgba(255,255,255,0.2); border: 1px solid white; margin-left: 0.5rem;">Cambiar
                Contraseña</a>
            <a href="index.php?logout=1" style="margin-left: 1rem; background: #dc3545; border: none;">Cerrar Sesión</a>
        </div>
    </div>
    <div class="container">
        <h3 style="margin-top: 0; margin-bottom: 1.5rem; text-align: center;">Cambiar Contraseña</h3>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="cambiar_password.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label>Contraseña Actual</label>
                <input type="password" name="pass_actual" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Nueva Contraseña</label>
                <input type="password" name="pass_nueva" class="form-control" required minlength="6">
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label>Confirmar Nueva Contraseña</label>
                <input type="password" name="pass_confirmacion" class="form-control" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
        </form>
    </div>
</body>

</html>