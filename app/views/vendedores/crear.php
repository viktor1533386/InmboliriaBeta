<?php require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="page-header">
  <div><h2>➕ Nuevo Vendedor</h2></div>
  <a href="<?= BASE_URL ?>/vendedor" class="btn btn-outline" style="border-color:var(--border);color:var(--text)">← Volver</a>
</div>

<?php if (!empty($errores)): ?>
<div class="alert alert-error" style="max-width:600px;margin-bottom:1.5rem">
  <?php foreach ($errores as $e): ?><div>⚠️ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="form-card" style="max-width:600px">
  <form method="POST" data-validate>
    <div class="form-grid">
      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" required placeholder="Gabriel"
               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Apellido *</label>
        <input type="text" name="apellido" required placeholder="Gamero"
               value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
      </div>
      <div class="form-group form-full">
        <label>Email *</label>
        <input type="email" name="email" required placeholder="gabriel@hogarideal.pe"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group form-full">
        <label>Teléfono * <small>(9 dígitos)</small></label>
        <input type="tel" name="telefono" placeholder="Ej. 999888777" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
               value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
      </div>
      <div class="form-group form-full">
        <label>DNI * <small>(8 dígitos)</small></label>
        <input type="text" name="dni" placeholder="12345678" required maxlength="8" pattern="\d{8}" title="Debe contener exactamente 8 dígitos" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
               value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>">
      </div>
    </div>
    <div style="margin-top:1.5rem;display:flex;gap:1rem">
      <button type="submit" class="btn btn-primary">💾 Guardar Vendedor</button>
      <a href="<?= BASE_URL ?>/vendedor" class="btn btn-outline" style="border-color:var(--border);color:var(--text)">Cancelar</a>
    </div>
  </form>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
