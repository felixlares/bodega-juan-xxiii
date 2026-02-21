</main>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-col" style="grid-column: span 2;">
            <h4>
                <i class="fas fa-wine-bottle" style="margin-right: 5px;"></i>
                <?php echo htmlspecialchars($configuracion['nombre_tienda']); ?>
            </h4>
            <p><i class="fas fa-map-marker-alt" style="width: 20px;"></i>
                <?php echo htmlspecialchars($configuracion['direccion']); ?>
            </p>
            <p><i class="fab fa-whatsapp" style="width: 20px;"></i>
                <?php echo htmlspecialchars($configuracion['whatsapp']); ?>
            </p>
            <p><i class="far fa-envelope" style="width: 20px;"></i>
                <?php echo htmlspecialchars($configuracion['email']); ?>
            </p>
            <p><i class="far fa-clock" style="width: 20px;"></i>
                <?php echo htmlspecialchars($configuracion['horarios']); ?>
            </p>
        </div>
        <div class="footer-col">
            <h4>Categorías</h4>
            <a href="/productos.php?categoria=ropa">Ropa</a>
            <a href="/productos.php?categoria=accesorios">Accesorios</a>
            <a href="/productos.php?categoria=hogar">Hogar</a>
            <a href="/productos.php?categoria=viveres">Víveres</a>
            <a href="/productos.php?categoria=electricidad">Electricidad</a>
            <a href="/productos.php?categoria=ferreteria">Ferretería</a>
        </div>
        <div class="footer-col">
            <h4>Enlaces Útiles</h4>
            <a href="/">Inicio</a>
            <a href="/productos.php">Todos los Productos</a>
            <a href="/contacto.php">Contáctanos</a>
            <a href="" style="margin-top: 1rem; color: #6c757d; font-size: 0.8rem;">Acceso
                Administrativo</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy;
            <?php echo date('Y'); ?>
            <?php echo htmlspecialchars($configuracion['nombre_tienda']); ?>. Todos los derechos reservados.
        </p>
    </div>
</footer>

<!-- Declarar configuracion para JS -->
<script>
    window.TIENDA_CONFIG = {
        whatsapp: "<?php echo preg_replace('/[^0-9]/', '', $configuracion['whatsapp']); ?>",
        nombre: "<?php echo htmlspecialchars($configuracion['nombre_tienda'], ENT_QUOTES); ?>"
    };
</script>
<script src="/assets/js/main.js?v=<?php echo APP_VERSION; ?>"></script>
<script src="/assets/js/cart.js?v=<?php echo APP_VERSION; ?>"></script>
</body>

</html>