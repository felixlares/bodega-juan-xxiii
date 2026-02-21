<?php
$og_title = "Inicio - Bodega Juan XXIII";
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Bienvenidos a
            <?php echo htmlspecialchars($configuracion['nombre_tienda']); ?>
        </h1>
        <p>Encuentra todo lo que necesitas en un solo lugar: ropa, accesorios, víveres, ferretería y más. Lo mejor al
            mejor precio.</p>
        <a href="/productos.php" class="btn btn-primary"
            style="font-size: 1.1rem; padding: 1rem 2.5rem; border-radius: 50px;">🛍️ Ver Catálogo Completo</a>
    </div>
</section>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <h2 style="font-size: 2rem; font-weight: 800; color: #212529;">Categorías</h2>
    </div>

    <div class="product-grid"
        style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 4rem;">
        <a href="/productos.php?categoria=ropa" class="product-card"
            style="text-align: center; padding: 2rem 1rem; border: none; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <i class="fas fa-tshirt" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
            <h3 style="font-size: 1rem; font-weight: 700;">Ropa</h3>
        </a>
        <a href="/productos.php?categoria=viveres" class="product-card"
            style="text-align: center; padding: 2rem 1rem; border: none; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <i class="fas fa-shopping-basket" style="font-size: 2.5rem; color: #198754; margin-bottom: 1rem;"></i>
            <h3 style="font-size: 1rem; font-weight: 700;">Víveres</h3>
        </a>
        <a href="/productos.php?categoria=ferreteria" class="product-card"
            style="text-align: center; padding: 2rem 1rem; border: none; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <i class="fas fa-hammer" style="font-size: 2.5rem; color: #6c757d; margin-bottom: 1rem;"></i>
            <h3 style="font-size: 1rem; font-weight: 700;">Ferretería</h3>
        </a>
        <a href="/productos.php?categoria=electricidad" class="product-card"
            style="text-align: center; padding: 2rem 1rem; border: none; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <i class="fas fa-plug" style="font-size: 2.5rem; color: #ffc107; margin-bottom: 1rem;"></i>
            <h3 style="font-size: 1rem; font-weight: 700;">Electricidad</h3>
        </a>
        <a href="/productos.php?categoria=hogar" class="product-card"
            style="text-align: center; padding: 2rem 1rem; border: none; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <i class="fas fa-home" style="font-size: 2.5rem; color: #fd7e14; margin-bottom: 1rem;"></i>
            <h3 style="font-size: 1rem; font-weight: 700;">Hogar</h3>
        </a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <h2 style="font-size: 2rem; font-weight: 800; color: #212529;">🔥 Destacados</h2>
        <a href="/productos.php" style="color: var(--primary-color); font-weight: 600;">Ver todos &rarr;</a>
    </div>

    <div class="product-grid" id="destacadosContainer">
        <!-- Renderizado desde script -->
        <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #6c757d;">Cargando productos...</div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        fetch('/api/get_products.php')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('destacadosContainer');
                if (data.status === 'success') {
                    const limitados = data.data.slice(0, 10); // Display top 10 as highlights

                    if (limitados.length === 0) {
                        container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #6c757d;">No hay productos disponibles por ahora.</div>';
                        return;
                    }

                    container.innerHTML = limitados.map(p => {
                        const imagenSrc = p.imagen_principal ? `/${p.imagen_principal}` : '/assets/images/placeholder.jpg';
                        const oldPrice = p.precio_anterior ? `<span class="product-price-old">$${p.precio_anterior}</span>` : '';
                        const badge = p.destacado ? `<div class="product-badge">🔥 Nuevo</div>` : '';

                        const pTitleJSON = JSON.stringify(p.titulo).replace(/"/g, '&quot;');

                        return `
                    <div class="product-card">
                        ${badge}
                        <a href="/producto-detalle.php?slug=${p.slug}" class="product-image-container">
                            <img src="${imagenSrc}" alt="${p.titulo}" class="product-image" loading="lazy">
                        </a>
                        <div class="product-info">
                            <div class="product-category">${p.categoria}</div>
                            <a href="/producto-detalle.php?slug=${p.slug}" class="product-title">${p.titulo}</a>
                            <div class="product-price-wrapper">
                                <span class="product-price">$${parseFloat(p.precio).toFixed(2)}</span>
                                ${oldPrice}
                            </div>
                            <button class="add-to-cart-btn" onclick="addToCart({id: ${p.id}, titulo: ${pTitleJSON}, precio: ${p.precio}, imagen: '${imagenSrc}'})">
                                <i class="fas fa-cart-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                `;
                    }).join('');
                } else {
                    container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: #dc3545;">Error cargando catálogo.</div>`;
                }
            });
    });
</script>

<?php require_once 'includes/footer.php'; ?>