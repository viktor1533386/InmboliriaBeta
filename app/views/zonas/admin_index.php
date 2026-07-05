<?php require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="page-header">
  <div>
    <h2>📍 Gestión de Zonas</h2>
    <p><?= count($zonas) ?> zona<?= count($zonas) !== 1 ? 's' : '' ?> registrada<?= count($zonas) !== 1 ? 's' : '' ?></p>
  </div>
  <a href="<?= BASE_URL ?>/zona/crear" class="btn btn-primary">+ Nueva Zona</a>
</div>

<div class="admin-card">
  <table class="data-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Nombre de Zona</th>
        <th>Vendedores Asignados</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($zonas)): ?>
        <tr>
          <td colspan="4" style="text-align:center;padding:3rem;color:var(--text-3)">
            No hay zonas. <a href="<?= BASE_URL ?>/zona/crear" style="color:var(--accent)">Crear primera zona →</a>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($zonas as $z): ?>
        <tr>
          <td style="color:var(--text-3);font-size:.8rem">#<?= $z->id ?></td>
          <td>
            <strong><?= htmlspecialchars($z->nombre) ?></strong>
          </td>
          <td>
            <?php if (!empty($z->vendedores)): ?>
              <?php foreach ($z->vendedores as $v): ?>
                <span class="badge badge-blue"><?= htmlspecialchars($v->nombre . ' ' . $v->apellido) ?></span>
              <?php endforeach; ?>
            <?php else: ?>
              <span class="badge badge-gray">Sin asignar</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= BASE_URL ?>/zona/editar/<?= $z->id ?>" class="btn btn-sm btn-dark" title="Editar">✏️</a>
            <a href="<?= BASE_URL ?>/zona/eliminar/<?= $z->id ?>" class="btn btn-sm btn-danger btn-delete" title="Eliminar">🗑️</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
