<?php require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2>Mesa de Ayuda (Tickets) 🛠️</h2>
        <p>Reporta incidencias o solicita configuraciones especiales al equipo de soporte.</p>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert success" style="padding:15px; background:#d4edda; color:#155724; border-radius:8px; margin-bottom:20px;">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert error" style="padding:15px; background:#f8d7da; color:#721c24; border-radius:8px; margin-bottom:20px;">
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="card" style="background:#fff; border-radius:12px; padding:2rem; box-shadow:0 4px 6px rgba(0,0,0,0.05); max-width: 600px;">
    <form action="<?= BASE_URL ?>/ticket/store" method="POST">
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="servicio_id" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#333;">Tipo de Servicio <span style="color:red;">*</span></label>
            <select name="servicio_id" id="servicio_id" class="form-control" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                <option value="">-- Seleccione un servicio del catálogo --</option>
                <?php foreach($servicios as $code => $name): ?>
                    <option value="<?= $code ?>"><?= $code ?> - <?= htmlspecialchars($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="descripcion" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#333;">Descripción detallada <span style="color:red;">*</span></label>
            <textarea name="descripcion" id="descripcion" rows="5" class="form-control" required placeholder="Describe el problema o solicitud con el mayor detalle posible..." style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; resize:vertical;"></textarea>
            <small style="color:#666; display:block; margin-top:5px;">El ticket será enviado al Scrum Master y atendido por el equipo TI según SLA.</small>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn btn-primary" style="background-color: var(--primary); color:#111; padding:12px 24px; border:none; border-radius:6px; cursor:pointer; font-weight:bold;">
                Generar Ticket
            </button>
        </div>
    </form>
</div>

<div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid var(--primary);">
    <h4 style="margin-top: 0;">¿Qué sucede después de enviar el ticket?</h4>
    <p style="margin-bottom: 0; color: #555;">
        El sistema enviará automáticamente tu solicitud a <strong>soporte@hogarideal.pe</strong>. 
        Recibirás en pantalla un código de seguimiento con formato <code>TKT-YYMMDD-NNN</code>.
        El especialista de TI encargado realizará los cambios directamente en el sistema y se te notificará por canales internos cuando esté resuelto.
    </p>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
