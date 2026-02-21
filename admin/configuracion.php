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

try {
    $pdo->exec("USE `$dbname`");
} catch (PDOException $e) {
    die("Error conectando a BD.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error de seguridad (CSRF). Por favor recargue la página.");
    }

    $nombre_tienda = trim($_POST['nombre_tienda'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $google_maps_url = trim($_POST['google_maps_url'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $horarios = trim($_POST['horarios'] ?? '');

    $redes = [
        'facebook' => trim($_POST['facebook'] ?? ''),
        'instagram' => trim($_POST['instagram'] ?? ''),
        'tiktok' => trim($_POST['tiktok'] ?? '')
    ];
    $redes_json = json_encode($redes);

    if (empty($nombre_tienda)) {
        $error = "El nombre de la tienda es obligatorio.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE configuracion SET nombre_tienda=?, direccion=?, google_maps_url=?, whatsapp=?, email=?, horarios=?, redes_sociales=?");
            $stmt->execute([$nombre_tienda, $direccion, $google_maps_url, $whatsapp, $email, $horarios, $redes_json]);
            $success = "Configuración actualizada correctamente.";
        } catch (PDOException $e) {
            $error = "Error al guardar en la base de datos: " . $e->getMessage();
        }
    }
}

// Cargar configuración actual
$stmt = $pdo->query("SELECT * FROM configuracion LIMIT 1");
$config = $stmt->fetch();

if (!$config) {
    // Valores por defecto si no existen
    $config = [
        'nombre_tienda' => 'Bodega Juan XXIII',
        'direccion' => '',
        'google_maps_url' => '',
        'whatsapp' => '',
        'email' => '',
        'horarios' => '',
        'redes_sociales' => '{"facebook": "", "instagram": "", "tiktok": ""}'
    ];
}

$redes = json_decode($config['redes_sociales'], true) ?: ['facebook' => '', 'instagram' => '', 'tiktok' => ''];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Panel de Administración</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            margin-top: 2rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            display: inline-block;
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
            margin-bottom: 1rem;
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

        .section-title {
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #dee2e6;
            color: #495057;
        }

        .flex-row {
            display: flex;
            gap: 1rem;
        }

        .flex-col {
            flex: 1;
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
                style="background: transparent; border: 1px solid white; margin-left: 0.5rem;">Cambiar Contraseña</a>
            <a href="index.php?logout=1" style="margin-left: 1rem; background: #dc3545; border: none;">Cerrar Sesión</a>
        </div>
    </div>
    <div class="container">
        <h3>Ajustes Generales</h3>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="configuracion.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <h4 class="section-title">Información de la Tienda</h4>

            <div class="form-group">
                <label>Nombre de la Tienda *</label>
                <input type="text" name="nombre_tienda" class="form-control"
                    value="<?php echo htmlspecialchars($config['nombre_tienda']); ?>" required>
            </div>

            <div class="flex-row">
                <div class="form-group flex-col">
                    <label>WhatsApp (ej. 0412-1614868)</label>
                    <input type="text" name="whatsapp" class="form-control"
                        value="<?php echo htmlspecialchars($config['whatsapp']); ?>">
                </div>
                <div class="form-group flex-col">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($config['email']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Dirección Física</label>
                <input type="text" name="direccion" class="form-control"
                    value="<?php echo htmlspecialchars($config['direccion']); ?>">
            </div>

            <div class="form-group">
                <label>Enlace de Google Maps</label>
                <input type="text" name="google_maps_url" class="form-control"
                    value="<?php echo htmlspecialchars($config['google_maps_url']); ?>">
            </div>

            <div class="form-group">
                <label>Horarios de Atención</label>
                <input type="text" name="horarios" class="form-control"
                    value="<?php echo htmlspecialchars($config['horarios']); ?>"
                    placeholder="ej. Lunes a Sábado 8am - 6pm">
            </div>

            <h4 class="section-title">Redes Sociales</h4>

            <div class="form-group">
                <label>URL de Instagram</label>
                <input type="text" name="instagram" class="form-control"
                    value="<?php echo htmlspecialchars($redes['instagram'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>URL de Facebook</label>
                <input type="text" name="facebook" class="form-control"
                    value="<?php echo htmlspecialchars($redes['facebook'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>URL de TikTok</label>
                <input type="text" name="tiktok" class="form-control"
                    value="<?php echo htmlspecialchars($redes['tiktok'] ?? ''); ?>">
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Guardar Configuración</button>
            </div>
        </form>
    </div>
</body>

</html>