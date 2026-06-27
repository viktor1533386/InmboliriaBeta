<?php $activePage = 'mis_prospectos'; require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem">
  <h1 style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--text)">
    <?= $rol === 'vendedor' ? 'Mis Prospectos' : 'Todos los Prospectos (Seguimiento)' ?>
  </h1>
</div>

<div class="card" style="padding:1.5rem">
  <?php if (empty($prospectos)): ?>
    <div style="text-align:center;padding:3rem 0;color:var(--text-2)">
      <div style="font-size:3rem;margin-bottom:1rem"> inbox_zero </div>
      <h3>No tienes prospectos asignados</h3>
      <p>Cuando un supervisor te asigne un visitante, aparecerá aquí.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Código / Fecha Asignación</th>
            <th>Visitante</th>
            <th>Contacto</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($prospectos as $p): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($p->codigo) ?></strong><br>
                <small style="color:var(--text-2)"><?= date('d/m/Y H:i', strtotime($p->fecha_asignacion ?? $p->created_at)) ?></small>
              </td>
              <td>
                <div style="font-weight:500"><?= htmlspecialchars($p->nombre) ?></div>
                <?php if (!empty($p->propiedad_titulo)): ?>
                  <div style="margin-top:.5rem;font-size:.8rem;padding:.5rem;background:#f9fafb;border:1px solid var(--border);border-radius:4px;width:100%;max-width:250px">
                    <div style="color:var(--primary);font-weight:600;margin-bottom:.2rem">🏠 <?= htmlspecialchars(substr($p->propiedad_titulo, 0, 30)) ?><?= strlen($p->propiedad_titulo) > 30 ? '...' : '' ?></div>
                    <div style="color:var(--text-3);display:flex;justify-content:space-between;margin-top:.2rem">
                      <span>💰 S/ <?= number_format((float)$p->propiedad_precio, 0, '.', ',') ?></span>
                    </div>
                    <a href="<?= BASE_URL ?>/propiedad/detalle/<?= $p->propiedad_id ?>" target="_blank" class="btn btn-sm btn-outline" style="width:100%;justify-content:center;margin-top:.5rem;padding:.2rem;font-size:.7rem;border-color:var(--border);color:var(--text)">Ver Detalle Propiedad</a>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <a href="mailto:<?= htmlspecialchars($p->email) ?>" style="color:var(--primary);text-decoration:none"><?= htmlspecialchars($p->email) ?></a><br>
                <small><?= htmlspecialchars($p->telefono ?? 'Sin teléfono') ?></small>
              </td>
              <td>
                <span style="display:inline-block;padding:.2rem .6rem;border-radius:1rem;font-size:.85rem;background:#f3f4f6;color:#374151">
                  <?= htmlspecialchars($p->estado) ?>
                </span>
              </td>
              <td>
                <a href="<?= BASE_URL ?>/vendedor/detalle/<?= $p->id ?>" class="btn btn-outline" style="font-size:.85rem;padding:.4rem .8rem">
                  Ver Timeline →
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
