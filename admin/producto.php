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

    // Basic CSRF check could be added here later

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

    /**
     * Redimensiona y comprime una imagen usando GD
     */
    function optimizarImagen($sourcePath, $destPath, $maxWidth = 1200, $quality = 80)
    {
        list($width, $height, $type) = getimagesize($sourcePath);

        // Calcular nuevas dimensiones manteniendo proporción
        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));
        }

        // Crear recurso de imagen según el tipo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                // Preservar transparencia
                imagealphablending($source, true);
                imagesavealpha($source, true);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        // Crear imagen de destino
        $dest = imagecreatetruecolor($newWidth, $newHeight);

        // Manejar transparencia para el destino (especialmente PNG/WEBP)
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
            imagefilledrectangle($dest, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Redimensionar
        imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Guardar comprimida
        $result = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($dest, $destPath, $quality);
                break;
            case IMAGETYPE_PNG:
                // PNG quality en GD es 0-9 (compresión), no 0-100
                $pngQuality = floor((100 - $quality) / 10);
                $result = imagepng($dest, $destPath, $pngQuality);
                break;
            case IMAGETYPE_WEBP:
                $result = imagewebp($dest, $destPath, $quality);
                break;
        }

        // Liberar memoria
        imagedestroy($source);
        imagedestroy($dest);

        return $result;
    }

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

    // Procesar carga de imagen
    if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen_archivo']['tmp_name'];
        $fileName = $_FILES['imagen_archivo']['name'];
        $fileSize = $_FILES['imagen_archivo']['size'];
        $fileType = $_FILES['imagen_archivo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $uploadFileDir = '../assets/images/productos/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            // Nombre del archivo: slug-p.extensión
            $newFileName = $slug . '-p.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            // Optimizar antes de guardar definitivamente
            if (optimizarImagen($fileTmpPath, $dest_path)) {
                $imagen = './assets/images/productos/' . $newFileName;
            } else {
                // Si la optimización falla (ej. formato no soportado), intentar mover directo
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $imagen = './assets/images/productos/' . $newFileName;
                } else {
                    $error = "Hubo un error al procesar o guardar la imagen.";
                }
            }
        } else {
            $error = "Extensión de archivo no permitida. Solo JPG, PNG, GIF y WEBP.";
        }
    }

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
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header h2 {
            margin: 0;
            font-size: 1.25rem;
        }

        .nav-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 0.4rem 0.8rem;
            background: transparent;
            border: 1px solid white;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .nav-menu a.logout {
            background: #dc3545;
            border-color: #dc3545;
            margin-left: 0.5rem;
        }

        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-menu a.logout:hover {
            background: #c82333;
        }

        .container {
            padding: 1.5rem;
            max-width: 800px;
            margin: 0 auto;
            background: white;
            margin-top: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.25rem;
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

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
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

        @media (max-width: 768px) {
            .header {
                padding: 0.8rem 1rem;
            }

            .nav-toggle {
                display: block;
            }

            .nav-menu {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #212529;
                padding: 1rem;
                border-top: 1px solid #343a40;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .nav-menu.active {
                display: flex;
            }

            .nav-menu a {
                width: 100%;
                box-sizing: border-box;
                text-align: center;
                margin: 0.25rem 0 !important;
            }

            .container {
                padding: 1rem;
                margin-top: 0;
                border-radius: 0;
            }

            .flex-row {
                flex-direction: column;
                gap: 0;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Bodega Juan XXIII - Admin</h2>
        <button class="nav-toggle" id="navToggle">☰</button>
        <div class="nav-menu" id="navMenu">
            <a href="index.php">Productos</a>
            <a href="configuracion.php">Configuración</a>
            <a href="cambiar_password.php">Cambiar Contraseña</a>
            <a href="index.php?logout=1" class="logout">Cerrar Sesión</a>
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

        <form method="POST" action="producto.php<?php echo $id > 0 ? '?id=' . $id : ''; ?>"
            enctype="multipart/form-data">
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
                    <label>Precio Anterior ($) (Opcional)</label>
                    <input type="number" step="0.01" name="precio_anterior" class="form-control"
                        value="<?php echo htmlspecialchars($producto['precio_anterior'] ?? ''); ?>">
                </div>
                <div class="form-group flex-col">
                    <label>Stock Disponible</label>
                    <input type="number" name="stock" class="form-control"
                        value="<?php echo htmlspecialchars($producto['stock']); ?>">
                </div>
            </div>

            <div class="form-group" style="background: #f1f3f5; padding: 1rem; border-radius: 8px;">
                <label style="margin-bottom: 1rem; color: #495057;">Imagen del Producto</label>

                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.85rem; color: #6c757d;">Cargar desde dispositivo (Foto o Galería)</label>
                    <input type="file" name="imagen_archivo" class="form-control" accept="image/*" capture="environment"
                        style="background: white;">
                </div>

                <div>
                    <label style="font-size: 0.85rem; color: #6c757d;">O ingresar URL de imagen actual</label>
                    <input type="text" name="imagen_principal" class="form-control"
                        placeholder="./assets/images/productos/tu-imagen.jpg"
                        value="<?php echo htmlspecialchars($producto['imagen_principal']); ?>"
                        style="background: white;">
                </div>

                <?php if ($producto['imagen_principal']): ?>
                    <div
                        style="margin-top: 1rem; border-top: 1px solid #dee2e6; padding-top: 1rem; display: flex; align-items: center; gap: 1rem;">
                        <img src="<?php echo htmlspecialchars('../' . $producto['imagen_principal']); ?>" alt="Vista previa"
                            style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;">
                        <span style="font-size: 0.8rem; color: #6c757d;">Imagen actual</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion"
                    class="form-control"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <input type="checkbox" name="activo" id="activo" value="1" <?php echo $producto['activo'] ? 'checked' : ''; ?> style="width: 20px; height: 20px;">
                <label for="activo" style="margin: 0; cursor: pointer;">Producto Activo (Visible en la tienda)</label>
            </div>

            <div
                style="margin-top: 2rem; border-top: 1px solid #dee2e6; padding-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary" style="flex: 2; min-width: 200px;">Guardar
                    Producto</button>
                <a href="index.php" class="btn btn-secondary" style="flex: 1; min-width: 120px;">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('navToggle').addEventListener('click', function () {
            document.getElementById('navMenu').classList.toggle('active');
        });
    </script>
</body>

</html>