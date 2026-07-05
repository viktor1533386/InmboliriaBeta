<?php require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="page-header">
  <div>
    <h2>✏️ Editar Zona</h2>
    <p>Actualiza los datos y asignaciones de la zona.</p>
  </div>
  <a href="<?= BASE_URL ?>/zona" class="btn btn-outline" style="border-color:var(--border);color:var(--text)">← Volver</a>
</div>

<?php if (!empty($errores)): ?>
<div class="alert alert-error" style="max-width:800px;margin-bottom:1.5rem">
  <?php foreach ($errores as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="form-card">
  <form method="POST" data-validate>

    <div class="form-grid">

      <div class="form-group form-full">
        <label>Nombre de la zona *</label>
        <input type="text" name="nombre" placeholder="Ej: Zona Norte" required
               value="<?= htmlspecialchars($_POST['nombre'] ?? $zona->nombre) ?>">
      </div>

      <div class="form-group form-full">
        <label>Vendedores asignados a esta zona</label>
        <div style="display:flex; flex-direction:column; gap:0.5rem; margin-top:0.5rem;">
          <?php foreach ($vendedores as $v): ?>
            <?php 
              // Verificar si fue enviado por POST o si está en la BD
              $checked = false;
              if (isset($_POST['vendedores_ids'])) {
                  $checked = in_array($v->id, $_POST['vendedores_ids']);
              } else {
                  $checked = in_array($v->id, $vendedoresAsignados);
              }
            ?>
            <label style="font-weight:normal; cursor:pointer; display:flex; align-items:center; gap:0.5rem;">
              <input type="checkbox" name="vendedores_ids[]" value="<?= $v->id ?>" <?= $checked ? 'checked' : '' ?> style="width:auto; margin:0;">
              <?= htmlspecialchars($v->nombre . ' ' . $v->apellido) ?>
            </label>
          <?php endforeach; ?>
        </div>
        <small style="color:var(--text-3); display:block; margin-top:0.5rem;">Puedes seleccionar varios vendedores para esta zona.</small>
      </div>
      
    </div>

    <div class="form-actions" style="margin-top:2rem">
      <button type="submit" class="btn btn-primary" style="width:100%;font-size:1.1rem;padding:12px">Actualizar Zona</button>
    </div>

  </form>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
