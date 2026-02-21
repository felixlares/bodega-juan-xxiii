document.addEventListener('DOMContentLoaded', () => {
    // Cart Elements
    const cartToggle = document.getElementById('cartToggle');
    const cartSidebar = document.getElementById('cartSidebar');
    const closeCartBtn = document.getElementById('closeCart');
    const cartOverlay = document.getElementById('cartOverlay');
    const cartItemsContainer = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const cartTotalPrice = document.getElementById('cartTotalPrice');
    const checkoutBtn = document.getElementById('checkoutBtn');

    // Iniciar Estado
    let cart = JSON.parse(localStorage.getItem('bodega_cart')) || [];

    // Funciones
    function saveCart() {
        localStorage.setItem('bodega_cart', JSON.stringify(cart));
        updateCartUI();
    }

    function toggleCart() {
        cartSidebar.classList.toggle('open');
        cartOverlay.classList.toggle('show');
    }

    // Global func para llamar desde PHP/HTML
    window.addToCart = function (producto) {
        const existingItem = cart.find(item => item.id === producto.id);
        if (existingItem) {
            existingItem.cantidad += (producto.cantidad || 1);
        } else {
            cart.push({
                id: producto.id,
                titulo: producto.titulo,
                precio: parseFloat(producto.precio),
                imagen: producto.imagen || '/assets/images/placeholder.jpg',
                cantidad: producto.cantidad || 1
            });
        }
        saveCart();

        // Efecto visual al añadir (abrir sidebar o hacer shake count)
        cartSidebar.classList.add('open');
        cartOverlay.classList.add('show');
    }

    window.removeFromCart = function (id) {
        cart = cart.filter(item => item.id !== id);
        saveCart();
    }

    window.updateQuantity = function (id, change) {
        const item = cart.find(item => item.id === id);
        if (item) {
            item.cantidad += change;
            if (item.cantidad <= 0) {
                removeFromCart(id);
            } else {
                saveCart();
            }
        }
    }

    function updateCartUI() {
        // Count Items
        const totalItems = cart.reduce((sum, item) => sum + item.cantidad, 0);
        if (cartCount) cartCount.textContent = totalItems;

        // Total Price
        const total = cart.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        if (cartTotalPrice) cartTotalPrice.textContent = `$${total.toFixed(2)}`;

        // Render HTML
        if (cartItemsContainer) {
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = `
                    <div class="cart-empty">
                        <i class="fas fa-shopping-basket"></i>
                        <p>Tu carrito está vacío</p>
                    </div>`;
                return;
            }

            cartItemsContainer.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <img src="${item.imagen}" alt="${item.titulo}" class="cart-item-img">
                    <div class="cart-item-details">
                        <div class="cart-item-title">${item.titulo}</div>
                        <div class="cart-item-price">$${item.precio.toFixed(2)} c/u</div>
                        <div class="cart-item-controls">
                            <div class="qty-btn-group">
                                <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                                <input type="text" class="qty-input" value="${item.cantidad}" readonly>
                                <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                            </div>
                            <button class="remove-item" onclick="removeFromCart(${item.id})"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    }

    // Checkout Func
    function actWhatsApp() {
        if (cart.length === 0) return;

        const telefono = window.TIENDA_CONFIG?.whatsapp || '';
        if (!telefono) {
            alert("El número de WhatsApp no está configurado.");
            return;
        }

        let msg = `*🛍️ PEDIDO - ${window.TIENDA_CONFIG?.nombre || 'BODEGA'}*\n\n`;
        let total = 0;

        cart.forEach(item => {
            let subtotal = item.precio * item.cantidad;
            total += subtotal;
            msg += `▪️ ${item.cantidad}x ${item.titulo} - *$${subtotal.toFixed(2)}*\n`;
        });

        msg += `\n*🧾 TOTAL A PAGAR: $${total.toFixed(2)}*\n\n`;
        msg += `¡Hola! Me gustaría hacer este pedido.`;

        const url = `https://wa.me/${telefono}?text=${encodeURIComponent(msg)}`;
        window.open(url, '_blank');
    }

    // Eventos
    if (cartToggle) cartToggle.addEventListener('click', toggleCart);
    if (closeCartBtn) closeCartBtn.addEventListener('click', toggleCart);
    if (cartOverlay) cartOverlay.addEventListener('click', toggleCart);
    if (checkoutBtn) checkoutBtn.addEventListener('click', actWhatsApp);

    // Bootstrap
    updateCartUI();
});
