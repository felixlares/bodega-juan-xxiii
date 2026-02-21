<?php
$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: /productos.php');
    exit;
}

require_once 'includes/db.php';
try {
    $pdo->exec("USE `$dbname`");
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE slug = ?");
    $stmt->execute([$slug]);
    $prod = $stmt->fetch();
} catch (Exception $e) {
    $prod = null;
}

if (!$prod) {
    header("HTTP/1.0 404 Not Found");
    echo "<div style='text-align:center; padding: 5rem; font-family: sans-serif;'><h1>Producto no encontrado</h1><a href='/productos.php'>Volver al catálogo</a></div>";
    exit;
}

// Meta Tags SEO Dinámicos
$og_title = $prod['titulo'] . " | Bodega Juan XXIII";
$og_description = $prod['descripcion'] ? mb_substr(strip_tags($prod['descripcion']), 0, 150) . "..." : "Consigue este producto al mejor precio en Bodega Juan XXIII.";
$og_type = "product";
$product_price = $prod['precio'];
if ($prod['imagen_principal']) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $og_image = $protocol . $_SERVER['HTTP_HOST'] . "/" . $prod['imagen_principal'];
}

require_once 'includes/header.php';
$galeria = json_decode($prod['galeria'], true) ?: [];
$imagenSrc = $prod['imagen_principal'] ? "/{$prod['imagen_principal']}" : "/assets/images/placeholder.jpg";
?>
<div class="container" style="padding-top: 3rem; padding-bottom: 4rem;">
    <!-- Breadcrumb -->
    <div style="margin-bottom: 2rem; color: #6c757d; font-size: 0.9rem;">
        <a href="/" style="color: var(--primary-color);">Inicio</a> &rsaquo;
        <a href="/productos.php?categoria=<?php echo urlencode($prod['categoria']); ?>"
            style="color: var(--primary-color); text-transform: capitalize;">
            <?php echo htmlspecialchars($prod['categoria']); ?>
        </a> &rsaquo;
        <span>
            <?php echo htmlspecialchars($prod['titulo']); ?>
        </span>
    </div>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 4rem; align-items: start;">

        <!-- Columna Izquierda: Galería -->
        <div>
            <div
                style="background: white; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); margin-bottom: 1rem; position: relative;">
                <?php if ($prod['precio_anterior']): ?>
                    <div
                        style="position: absolute; top: 15px; left: 15px; background: #dc3545; color: white; padding: 0.4rem 1rem; border-radius: 50px; font-weight: bold; font-size: 0.85rem; z-index: 10;">
                        ¡OFERTA!</div>
                <?php endif; ?>
                <img src="<?php echo htmlspecialchars($imagenSrc); ?>"
                    alt="<?php echo htmlspecialchars($prod['titulo']); ?>" id="mainImage"
                    style="width: 100%; height: auto; object-fit: contain; aspect-ratio: 1/1;">
            </div>

            <?php if (count($galeria) > 0): ?>
                <div style="display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 0.5rem;">
                    <img src="<?php echo htmlspecialchars($imagenSrc); ?>"
                        onclick="document.getElementById('mainImage').src=this.src"
                        style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid var(--primary-color); transition: 0.2s;">
                    <?php foreach ($galeria as $img): ?>
                        <img src="/<?php echo htmlspecialchars($img); ?>"
                            onclick="document.getElementById('mainImage').src=this.src; document.querySelectorAll('.gal-img').forEach(el=>el.style.borderColor='var(--border-color)'); this.style.borderColor='var(--primary-color)';"
                            class="gal-img"
                            style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid var(--border-color); transition: 0.2s;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Columna Derecha: Información del Producto -->
        <div>
            <div
                style="text-transform: uppercase; color: var(--primary-color); font-weight: 700; font-size: 0.85rem; letter-spacing: 1px; margin-bottom: 0.5rem;">
                <?php echo htmlspecialchars($prod['categoria']); ?>
            </div>
            <h1 style="font-size: 2.2rem; color: #212529; margin-bottom: 0.5rem; line-height: 1.2; font-weight: 800;">
                <?php echo htmlspecialchars($prod['titulo']); ?>
            </h1>
            <div style="color: #adb5bd; font-size: 0.95rem; margin-bottom: 2rem;">SKU:
                <?php echo htmlspecialchars($prod['sku'] ?: 'N/A'); ?>
            </div>

            <div
                style="background: #f8f9fa; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid var(--border-color);">
                <div style="color: #6c757d; font-size: 0.9rem; margin-bottom: 0.25rem;">Precio</div>
                <div style="display: flex; align-items: baseline; gap: 1rem;">
                    <span style="font-size: 2.5rem; font-weight: 800; color: #212529;">$
                        <?php echo number_format($prod['precio'], 2); ?>
                    </span>
                    <?php if ($prod['precio_anterior']): ?>
                        <span style="font-size: 1.2rem; color: #adb5bd; text-decoration: line-through;">$
                            <?php echo number_format($prod['precio_anterior'], 2); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <hr style="border: none; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">

                <div style="display: flex; align-items: center; gap: 1rem;">
                    <?php if ($prod['stock'] > 0): ?>
                        <div style="color: #198754; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-check-circle"></i> En stock (
                            <?php echo $prod['stock']; ?> disponibles)
                        </div>
                    <?php else: ?>
                        <div style="color: #dc3545; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-times-circle"></i> Agotado por el momento
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2.5rem;">
                <button class="btn btn-primary" onclick='addToCart(<?php echo json_encode([
                    "id" => $prod["id"],
                    "titulo" => $prod["titulo"],
                    "precio" => (float) $prod["precio"],
                    "imagen" => $imagenSrc
                ]); ?>)' style="font-size: 1.15rem; padding: 1.2rem; width: 100%; border-radius: 50px;">
                    <i class="fas fa-cart-plus" style="margin-right: 0.5rem;"></i> Agregar al Carrito
                </button>

                <button onclick="shareProduct(window.location.href, '<?php echo addslashes($prod["titulo"]); ?>', 'Mira
                    este excelente producto en Bodega Juan XXIII')"
                    class="btn" style="background: white; border: 1px solid var(--border-color); color: #495057; width:
                    100%; padding: 1rem; border-radius: 50px;">
                    <i class="fas fa-share-alt" style="margin-right: 0.5rem;"></i> Compartir Producto
                </button>
            </div>

            <!-- Tabs de Info -->
            <div>
                <div style="display: flex; border-bottom: 1px solid var(--border-color); margin-bottom: 1.5rem;">
                    <div
                        style="padding: 1rem 1.5rem; font-weight: 600; color: #212529; border-bottom: 2px solid var(--primary-color);">
                        Descripción</div>
                </div>
                <div style="color: #495057; line-height: 1.8; font-size: 1.05rem;">
                    <?php echo nl2br(htmlspecialchars($prod['descripcion'] ?: 'No hay descripción detallada disponible para este producto.')); ?>
                </div>

                <?php if ($prod['peso'] || $prod['dimensiones_alto']): ?>
                    <div style="margin-top: 2rem;">
                        <h4 style="margin-bottom: 1rem;">Especificaciones Físicas</h4>
                        <ul style="color: #495057; line-height: 1.8; list-style-position: inside;">
                            <?php if ($prod['peso'])
                                echo "<li>Peso: {$prod['peso']} kg</li>"; ?>
                            <?php if ($prod['dimensiones_alto'])
                                echo "<li>Dimensiones (AxAxP): {$prod['dimensiones_alto']} x {$prod['dimensiones_ancho']} x {$prod['dimensiones_profundidad']} cm</li>"; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>