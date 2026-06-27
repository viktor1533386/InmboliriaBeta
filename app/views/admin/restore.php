<?php require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div style="max-width:600px; margin:0 auto; padding: 2rem 0;">
    <div style="text-align:center; margin-bottom: 2rem;">
        <h2 style="margin-bottom:0.5rem">Restaurar Sistema</h2>
        <p style="color:var(--text-3)">Sube un archivo .sql para restaurar toda la base de datos.</p>
    </div>

    <div style="background:#fee; border:1px solid #fcc; border-radius:8px; padding:1.5rem; margin-bottom: 2rem; color:#c00;">
        <h3 style="margin-top:0; display:flex; align-items:center; gap:0.5rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4M12 17h.01"/></svg>
            PELIGRO DE PÉRDIDA DE DATOS
        </h3>
        <p style="margin-bottom:0; font-size:0.95rem; line-height:1.5;">
            Esta acción <strong>eliminará por completo</strong> todas las propiedades, usuarios y mensajes que existen actualmente en el sistema y los reemplazará con los del archivo que subas. Asegúrate de que estás subiendo el archivo correcto. Esta acción <strong>no se puede deshacer</strong>.
        </p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/admin/restore" method="POST" enctype="multipart/form-data" class="form-container" style="background:#fff; border:1px solid var(--border); box-shadow:var(--shadow-sm);">
        
        <div class="form-group" style="margin-bottom:2rem;">
            <label style="font-weight:600; margin-bottom:0.5rem; display:block;">Archivo de Backup (.sql) *</label>
            <input type="file" name="backup_file" accept=".sql" required 
                   style="display:block; width:100%; padding:0.75rem; border:1px dashed var(--border); border-radius:4px; background:var(--bg-alt);">
            <small style="color:var(--text-3); display:block; margin-top:0.5rem;">Solo se admiten archivos generados por este sistema o formato equivalente.</small>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center;">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-danger" onclick="return confirm('⚠️ ¿Estás COMPLETAMENTE SEGURO de que deseas formatear y restaurar la base de datos? ESTO BORRARÁ LA INFORMACIÓN ACTUAL.')">
                Comenzar Restauración
            </button>
        </div>
    </form>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
