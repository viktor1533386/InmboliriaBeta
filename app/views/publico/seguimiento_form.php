<?php $activePage = 'seguimiento'; require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="seguimiento-page" style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:4rem 1rem;">
  <div style="background:var(--card);border-radius:var(--radius-lg);padding:2.5rem;border:1px solid var(--border);box-shadow:var(--shadow);max-width:500px;width:100%">
    
    <div style="text-align:center;margin-bottom:2rem">
      <h1 style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--text);margin-bottom:.5rem">Seguimiento</h1>
      <p style="color:var(--text-2)">Ingresa tu código y correo para ver el estado de tu solicitud.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error" style="margin-bottom:1.5rem">
        ❌ <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="GET" action="<?= BASE_URL ?>/seguimiento" class="form-grid" style="gap:1rem">
      <div class="form-full">
        <label>Código de Seguimiento *</label>
        <input type="text" name="codigo" value="<?= htmlspecialchars($codigo ?? '') ?>" placeholder="PROS-231015-ABC" required style="font-family:monospace;font-size:1.1rem;letter-spacing:1px;text-transform:uppercase">
      </div>
      
      <div class="form-full">
        <label>Correo Electrónico *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" placeholder="tu@correo.com" required>
      </div>

      <div class="form-full" style="margin-top:1rem">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
          Consultar Estado 🔍
        </button>
      </div>
    </form>

  </div>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
