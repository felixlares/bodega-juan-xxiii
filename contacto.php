<?php
$og_title = "Contacto - Bodega Juan XXIII";
require_once 'includes/header.php';
?>
<div class="container" style="padding-top: 4rem; padding-bottom: 5rem;">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 4rem;">
        <h1 style="font-size: 2.8rem; font-weight: 800; color: #212529; margin-bottom: 1rem;">Estamos para Ayudarte</h1>
        <p style="color: #6c757d; font-size: 1.15rem; line-height: 1.6;">
            ¿Tienes alguna duda sobre nuestros productos? ¿Necesitas cotizar materiales al mayor?
            Visítanos o escríbenos a través de nuestros canales de atención.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 3rem;">

        <!-- Info Side -->
        <div
            style="background: white; padding: 3rem 2.5rem; border-radius: 16px; border: 1px solid var(--border-color);">
            <h3
                style="margin-bottom: 2rem; font-size: 1.5rem; font-weight: 700; border-bottom: 2px solid #f1f3f5; padding-bottom: 1rem;">
                Información de Contacto</h3>

            <div style="display: flex; gap: 1.2rem; margin-bottom: 2rem; align-items: flex-start;">
                <div
                    style="width: 45px; height: 45px; border-radius: 10px; background: rgba(13, 110, 253, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.3rem; flex-shrink: 0;">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.35rem; color: #212529; font-size: 1.1rem;">Ubicación Física</h4>
                    <p style="color: #6c757d; line-height: 1.5;">
                        <?php echo htmlspecialchars($configuracion['direccion']); ?>
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: 1.2rem; margin-bottom: 2rem; align-items: flex-start;">
                <div
                    style="width: 45px; height: 45px; border-radius: 10px; background: rgba(37, 211, 102, 0.1); display: flex; align-items: center; justify-content: center; color: var(--whatsapp-color); font-size: 1.5rem; flex-shrink: 0;">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.35rem; color: #212529; font-size: 1.1rem;">Atención vía WhatsApp</h4>
                    <p style="color: #6c757d; line-height: 1.5;">
                        <?php echo htmlspecialchars($configuracion['whatsapp']); ?>
                    </p>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $configuracion['whatsapp']); ?>"
                        target="_blank"
                        style="display: inline-block; margin-top: 0.5rem; color: var(--whatsapp-color); font-weight: 600;">Escríbenos
                        ahora &rarr;</a>
                </div>
            </div>

            <div style="display: flex; gap: 1.2rem; margin-bottom: 2rem; align-items: flex-start;">
                <div
                    style="width: 45px; height: 45px; border-radius: 10px; background: rgba(13, 110, 253, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.3rem; flex-shrink: 0;">
                    <i class="far fa-envelope"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.35rem; color: #212529; font-size: 1.1rem;">Correo de Negocios</h4>
                    <p style="color: #6c757d; line-height: 1.5;">
                        <?php echo htmlspecialchars($configuracion['email']); ?>
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: 1.2rem; align-items: flex-start;">
                <div
                    style="width: 45px; height: 45px; border-radius: 10px; background: rgba(253, 126, 20, 0.1); display: flex; align-items: center; justify-content: center; color: #fd7e14; font-size: 1.3rem; flex-shrink: 0;">
                    <i class="far fa-clock"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.35rem; color: #212529; font-size: 1.1rem;">Horario Comercial</h4>
                    <p style="color: #6c757d; line-height: 1.5;">
                        <?php echo htmlspecialchars($configuracion['horarios']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Side -->
        <div
            style="background: white; padding: 3rem 2.5rem; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
            <h3 style="margin-bottom: 2rem; font-size: 1.5rem; font-weight: 700;">Envíanos un Mensaje Consultivo</h3>
            <form onsubmit="sendContactMsg(event)">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #495057;">Nombre o
                        Empresa</label>
                    <input type="text" id="cNombre" required
                        style="width: 100%; padding: 0.9rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; background: #f8f9fa;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #495057;">Motivo de
                        Contacto</label>
                    <select id="cAsunto" required
                        style="width: 100%; padding: 0.9rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; background: #f8f9fa;">
                        <option value="Duda General">Duda General</option>
                        <option value="Cotización">Solicitud de Cotización</option>
                        <option value="Reclamo/Sugerencia">Reclamo o Sugerencia</option>
                        <option value="Proveedor">Soy Proveedor</option>
                    </select>
                </div>
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #495057;">Mensaje
                        Detallado</label>
                    <textarea id="cMensaje" rows="5" required
                        placeholder="Escribe aquí los productos que deseas cotizar o tu consulta..."
                        style="width: 100%; padding: 0.9rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; resize: vertical; background: #f8f9fa;"></textarea>
                </div>
                <button type="submit" class="btn btn-whatsapp"
                    style="width: 100%; justify-content: center; gap: 0.5rem; border-radius: 50px;">
                    <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i> Iniciar Chat en WhatsApp
                </button>
            </form>
            <script>
                function sendContactMsg(e) {
                    e.preventDefault();
                    const n = document.getElementById('cNombre').value;
                    const a = document.getElementById('cAsunto').value;
                    const m = document.getElementById('cMensaje').value;
                    const t = window.TIENDA_CONFIG?.whatsapp || '';
                    if (!t) return alert('WhatsApp de la tienda no configurado.');
                    const text = `*Nueva Consulta desde Sitio Web*\n\n*👤 De:* ${n}\n*📝 Asunto:* ${a}\n\n*💬 Mensaje:* ${m}`;
                    window.open(`https://wa.me/${t}?text=${encodeURIComponent(text)}`, '_blank');
                }
            </script>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>