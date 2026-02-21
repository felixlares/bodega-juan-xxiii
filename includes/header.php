<?php
require_once __DIR__ . '/db.php';
try {
    $pdo->exec("USE `$dbname`");
    // Obtener configuración global
    $stmtConfig = $pdo->query("SELECT * FROM configuracion LIMIT 1");
    $configuracion = $stmtConfig->fetch(PDO::FETCH_ASSOC) ?: [
        'nombre_tienda' => 'Bodega Juan XXIII',
        'whatsapp' => '0412-1614868',
        'email' => 'felixlares@gmail.com',
        'direccion' => 'Las 40 Calle 9 frente AGEL',
        'horarios' => '24 horas'
    ];
} catch (Exception $e) {
    // Valores por defecto si la BD falla
    $configuracion = [
        'nombre_tienda' => 'Bodega Juan XXIII',
        'whatsapp' => '0412-1614868',
        'email' => 'felixlares@gmail.com',
        'direccion' => 'Las 40 Calle 9 frente AGEL',
        'horarios' => '24 horas'
    ];
}

// Valores SEO por defecto
$og_title = $og_title ?? $configuracion['nombre_tienda'];
$og_description = $og_description ?? "La mejor tienda de ropa, accesorios, hogar, ferretería y víveres.";
$og_image = $og_image ?? (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/assets/images/logo.svg";
$og_url = $og_url ?? ((isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$og_type = $og_type ?? "website";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($og_title); ?>
    </title>

    <!-- Meta Tags Básicos -->
    <meta name="description" content="<?php echo htmlspecialchars($og_description); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo.svg">

    <!-- Open Graph SEO -->
    <meta property="og:type" content="<?php echo htmlspecialchars($og_type); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($og_url); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">

    <?php if ($og_type === 'product' && isset($product_price)): ?>
        <meta property="product:price:amount" content="<?php echo htmlspecialchars($product_price); ?>">
        <meta property="product:price:currency" content="USD">
    <?php endif; ?>

    <!-- Fuentes y Estilos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="/" class="logo">
                <i class="fas fa-wine-bottle" style="color: var(--primary-color); margin-right: 5px;"></i>
                <?php echo htmlspecialchars($configuracion['nombre_tienda']); ?>
            </a>

            <div class="search-bar">
                <form action="/productos.php" method="GET">
                    <input type="text" name="q" placeholder="Buscar productos..." id="searchInput" required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="nav-links" id="navLinks">
                <a href="/">Inicio</a>
                <a href="/productos.php">Catálogo</a>
                <a href="/contacto.php">Contacto</a>
            </div>

            <div class="nav-actions">
                <button class="cart-btn" id="cartToggle">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </button>
                <button class="menu-btn" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <!-- Search bar mobile -->
        <div class="search-mobile">
            <form action="/productos.php" method="GET" style="position: relative;">
                <input type="text" name="q" placeholder="Buscar productos..." required>
                <button type="submit"
                    style="position: absolute; right: 15px; top: 12px; background: none; border: none; color: #6c757d;"><i
                        class="fas fa-search"></i></button>
            </form>
        </div>
    </nav>

    <!-- Floating Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3><i class="fas fa-shopping-cart"></i> Tu Carrito</h3>
            <button id="closeCart">&times;</button>
        </div>
        <div class="cart-items" id="cartItems">
            <!-- Renderizado dinámicamente -->
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span id="cartTotalPrice">$0.00</span>
            </div>
            <button class="btn btn-whatsapp" id="checkoutBtn"><i class="fab fa-whatsapp"></i> Pedir por
                WhatsApp</button>
        </div>
    </div>
    <div class="cart-overlay" id="cartOverlay"></div>

    <main class="main-content">