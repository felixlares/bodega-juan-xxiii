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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$accion = $id > 0 ? 'Editar' : 'Añadir';

$producto = [
    'titulo' => '',
    'slug' => '',
    'descripcion' => '',
    'precio' => '',
    'precio_anterior' => '',
    'categoria' => 'ropa',
    'imagen_principal' => '',
    'stock' => 0,
    'sku' => '',
    'activo' => 1
];

try {
    $pdo->exec("USE `$dbname`");
} catch (PDOException $e) {
    die("Error conectando a BD.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error de seguridad (CSRF). Por favor recargue la página.");
    }

    function generarSlug($text)
    {
        // Reemplazar caracteres especiales y acentos
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        // Convertir a minúsculas
        $text = strtolower($text);
        // Eliminar caracteres no alfanuméricos
        $text = preg_replace('/[^a-z0-9-]+/', '-', $text);
        // Eliminar guiones duplicados y limpiar extremos
        $text = trim(preg_replace('/-+/', '-', $text), '-');
        return $text;
    }

    // Basic CSRF check could be added here later

    $titulo = trim($_POST['titulo'] ?? '');
    $slug = trim($_POST['slug'] ?? generarSlug($titulo));
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $precio_anterior = isset($_POST['precio_anterior']) && $_POST['precio_anterior'] !== '' ? floatval($_POST['precio_anterior']) : null;
    $categoria = $_POST['categoria'] ?? 'ropa';
    $imagen = trim($_POST['imagen_principal'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $sku = trim($_POST['sku'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (empty($titulo) || $precio <= 0 || empty($sku)) {
        $error = "Por favor, completa los campos obligatorios: Título, Precio y SKU.";
    } else {
        try {
            if ($id > 0) {
                // Actualizar
                $stmt = $pdo->prepare("UPDATE productos SET titulo=?, slug=?, descripcion=?, precio=?, precio_anterior=?, categoria=?, imagen_principal=?, stock=?, sku=?, activo=? WHERE id=?");
                $stmt->execute([$titulo, $slug, $descripcion, $precio, $precio_anterior, $categoria, $imagen, $stock, $sku, $activo, $id]);
                $success = "Producto actualizado correctamente.";
            } else {
                // Insertar
                $stmt = $pdo->prepare("INSERT INTO productos (titulo, slug, descripcion, precio, precio_anterior, categoria, imagen_principal, stock, sku, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titulo, $slug, $descripcion, $precio, $precio_anterior, $categoria, $imagen, $stock, $sku, $activo]);
                $id = $pdo->lastInsertId();
                $accion = 'Editar';
                $success = "Producto creado correctamente.";
            }
        } catch (PDOException $e) {
            // Verificar si es error de SKU duplicado
            if ($e->getCode() == 23000) {
                $error = "Error: El SKU ingresado ya existe.";
            } else {
                $error = "Error al guardar en la base de datos: " . $e->getMessage();
            }
        }
    }

    // Actualizar producto local para el formulario
    $producto = [
        'titulo' => $titulo,
        'slug' => $slug,
        'descripcion' => $descripcion,
        'precio' => $precio,
        'precio_anterior' => $precio_anterior,
        'categoria' => $categoria,
        'imagen_principal' => $imagen,
        'stock' => $stock,
        'sku' => $sku,
        'activo' => $activo
    ];
} elseif ($id > 0) {
    // Cargar producto existente
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $prod_db = $stmt->fetch();
    if ($prod_db) {
        $producto = $prod_db;
    } else {
        $error = "Producto no encontrado.";
        $id = 0;
        $accion = 'Añadir';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $accion; ?> Producto - Panel de Administración
    </title>
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
            background: #6c757d;
            border-radius: 4px;
            font-size: 0.9rem;
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

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
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

        .btn-secondary {
            background: #6c757d;
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
        <h3>
            <?php echo $accion; ?> Producto
        </h3>

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

        <form method="POST" action="producto.php<?php echo $id > 0 ? '?id=' . $id : ''; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label>Título del Producto *</label>
                <input type="text" name="titulo" class="form-control"
                    value="<?php echo htmlspecialchars($producto['titulo']); ?>" required>
            </div>

            <div class="flex-row">
                <div class="form-group flex-col">
                    <label>SKU (Código único) *</label>
                    <input type="text" name="sku" class="form-control"
                        value="<?php echo htmlspecialchars($producto['sku']); ?>" required>
                </div>
                <div class="form-group flex-col">
                    <label>Categoría</label>
                    <select name="categoria" class="form-control">
                        <option value="ropa" <?php echo $producto['categoria'] === 'ropa' ? 'selected' : ''; ?>>Ropa
                        </option>
                        <option value="accesorios" <?php echo $producto['categoria'] === 'accesorios' ? 'selected' : ''; ?>>Accesorios</option>
                        <option value="hogar" <?php echo $producto['categoria'] === 'hogar' ? 'selected' : ''; ?>>Hogar
                        </option>
                        <option value="viveres" <?php echo $producto['categoria'] === 'viveres' ? 'selected' : ''; ?>>
                            Víveres</option>
                        <option value="electricidad" <?php echo $producto['categoria'] === 'electricidad' ? 'selected' : ''; ?>>Electricidad</option>
                        <option value="ferreteria" <?php echo $producto['categoria'] === 'ferreteria' ? 'selected' : ''; ?>>Ferretería</option>
                    </select>
                </div>
            </div>

            <div class="flex-row">
                <div class="form-group flex-col">
                    <label>Precio Final ($) *</label>
                    <input type="number" step="0.01" name="precio" class="form-control"
                        value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
                </div>
                <div class="form-group flex-col">
                    <label>Precio Anterior ($) (Opcional, para tachado)</label>
                    <input type="number" step="0.01" name="precio_anterior" class="form-control"
                        value="<?php echo htmlspecialchars($producto['precio_anterior'] ?? ''); ?>">
                </div>
                <div class="form-group flex-col">
                    <label>Stock Disponible</label>
                    <input type="number" name="stock" class="form-control"
                        value="<?php echo htmlspecialchars($producto['stock']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>URL de la Imagen</label>
                <input type="text" name="imagen_principal" class="form-control"
                    placeholder="/assets/images/productos/tu-imagen.jpg"
                    value="<?php echo htmlspecialchars($producto['imagen_principal']); ?>">
                <small style="color: #6c757d;">Para una mejor carga, sube la imagen a /assets/images/productos/ y
                    escribe la ruta aquí.</small>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion"
                    class="form-control"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="activo" id="activo" value="1" <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                <label for="activo" style="margin: 0; cursor: pointer;">Producto Activo (Visible en la tienda)</label>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Guardar Producto</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>

</html>