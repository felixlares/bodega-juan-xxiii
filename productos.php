<?php
$og_title = "Catálogo - Bodega Juan XXIII";
require_once 'includes/header.php';
$catActiva = htmlspecialchars($_GET['categoria'] ?? '');
$qActiva = htmlspecialchars($_GET['q'] ?? '');
?>
<div class="container" style="display: flex; gap: 2.5rem; flex-wrap: wrap; padding-top: 2rem;">
    <!-- Sidebar Filtros -->
    <aside style="width: 260px; flex-shrink: 0;" class="sidebar-filters">
        <div
            style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color); position: sticky; top: 100px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0; font-size: 1.1rem; flex: 1;"><i class="fas fa-filter"
                        style="margin-right: 0.5rem; color: var(--text-muted);"></i> Filtros</h3>
            </div>

            <form id="filterForm">
                <div style="margin-bottom: 1.5rem;">
                    <label
                        style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 0.75rem;">Búsqueda</label>
                    <input type="text" name="q" value="<?php echo $qActiva; ?>" placeholder="¿Qué buscas?"
                        style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit;">
                </div>

                <div style="margin-bottom: 2rem;">
                    <label
                        style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 0.75rem;">Categoría</label>
                    <select name="categoria"
                        style="width: 100%; padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; background: transparent; cursor: pointer;">
                        <option value="">Todas las Categorías</option>
                        <option value="ropa" <?php echo $catActiva === 'ropa' ? 'selected' : ''; ?>>Ropa</option>
                        <option value="accesorios" <?php echo $catActiva === 'accesorios' ? 'selected' : ''; ?>>Accesorios
                        </option>
                        <option value="hogar" <?php echo $catActiva === 'hogar' ? 'selected' : ''; ?>>Hogar</option>
                        <option value="electricidad" <?php echo $catActiva === 'electricidad' ? 'selected' : ''; ?>
                            >Electricidad</option>
                        <option value="ferreteria" <?php echo $catActiva === 'ferreteria' ? 'selected' : ''; ?>>Ferretería
                        </option>
                        <option value="viveres" <?php echo $catActiva === 'viveres' ? 'selected' : ''; ?>>Víveres</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">Aplicar
                    Filtros</button>
            </form>
        </div>
    </aside>

    <!-- Content -->
    <div style="flex: 1; min-width: 0;">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <h2 style="margin: 0; font-size: 1.5rem;">Catálogo de Productos</h2>
            <div id="resultsCount" style="color: var(--text-muted); font-weight: 500;">Cargando...</div>
        </div>

        <div class="product-grid" id="productsContainer"
            style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
            <!-- Renderizado por JS -->
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('filterForm');
        const container = document.getElementById('productsContainer');
        const countLabel = document.getElementById('resultsCount');

        function loadProducts() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 4rem; color: #adb5bd;"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><br>Buscando productos...</div>';

            fetch(`/api/get_products.php?${params}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        countLabel.innerHTML = `<b>${data.data.length}</b> Productos encontrados`;
                        if (data.data.length === 0) {
                            container.innerHTML = `
                        <div style="grid-column: 1/-1; text-align: center; padding: 4rem; background: white; border-radius: 12px; border: 1px solid var(--border-color);">
                            <i class="fas fa-search" style="font-size: 3rem; color: #dee2e6; margin-bottom: 1rem;"></i>
                            <h3 style="margin-bottom: 0.5rem; color: #495057;">No se encontraron resultados</h3>
                            <p style="color: var(--text-muted);">Intenta ajustar los filtros o buscar con otro término.</p>
                        </div>`;
                            return;
                        }

                        container.innerHTML = data.data.map(p => {
                            const imagenSrc = p.imagen_principal ? `/${p.imagen_principal}` : '/assets/images/placeholder.jpg';
                            const oldPrice = p.precio_anterior ? `<span class="product-price-old">$${p.precio_anterior}</span>` : '';
                            const badge = p.destacado ? `<div class="product-badge">Top</div>` : '';
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
                                    <i class="fas fa-cart-plus"></i> Añadir
                                </button>
                            </div>
                        </div>`;
                        }).join('');
                    }
                });
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            loadProducts();
            const params = new URLSearchParams(new FormData(form));
            window.history.replaceState({}, '', `${window.location.pathname}?${params}`);
        });

        loadProducts();
    });
</script>

<style>
    @media (max-width: 900px) {
        .sidebar-filters {
            width: 100% !important;
        }

        aside>div {
            position: static !important;
        }
    }
</style>
<?php require_once 'includes/footer.php'; ?>