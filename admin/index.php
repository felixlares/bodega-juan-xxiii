<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

try {
    $pdo->exec("USE `$dbname`");
    $stmt = $pdo->query("SELECT * FROM productos ORDER BY id DESC");
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "No se pudo obtener la lista de productos.";
    $productos = [];
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
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
            background: #dc3545;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background 0.2s;
        }

        .header a:hover {
            background: #c82333;
        }

        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .toolbar h3 {
            margin: 0;
            font-size: 1.5rem;
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge-danger {
            background: #f8d7da;
            color: #842029;
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
            <a href="?logout=1" style="margin-left: 1rem;">Cerrar Sesión</a>
        </div>
    </div>
    <div class="container">
        <div class="toolbar">
            <h3>Productos</h3>
            <a href="producto.php" class="btn btn-primary">+ Añadir Producto</a>
        </div>

        <?php if (isset($error)): ?>
            <div style="color: red; margin-bottom: 1rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td>
                            <?php echo $p['id']; ?>
                        </td>
                        <td>
                            <div style="font-weight: 500;">
                                <?php echo htmlspecialchars($p['titulo']); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #6c757d;">SKU:
                                <?php echo htmlspecialchars($p['sku']); ?>
                            </div>
                        </td>
                        <td><span style="text-transform: capitalize;">
                                <?php echo htmlspecialchars($p['categoria']); ?>
                            </span></td>
                        <td>$
                            <?php echo number_format($p['precio'], 2); ?>
                        </td>
                        <td>
                            <?php echo $p['stock']; ?>
                        </td>
                        <td>
                            <?php if ($p['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="producto.php?id=<?php echo $p['id']; ?>"
                                style="color: #0d6efd; text-decoration: none; font-size: 0.9rem; margin-right: 0.5rem;">Editar</a>
                            <a href="eliminar_producto.php?id=<?php echo $p['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                                style="color: #dc3545; text-decoration: none; font-size: 0.9rem;"
                                onclick="return confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.');">Borrar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($productos) === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #6c757d; padding: 2rem;">No hay productos
                            registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>