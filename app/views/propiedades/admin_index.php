<?php require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="page-header">
  <div>
    <h2>🏠 Gestión de Propiedades</h2>
    <p><?= count($propiedades) ?> propiedad<?= count($propiedades) !== 1 ? 'es' : '' ?> registrada<?= count($propiedades) !== 1 ? 's' : '' ?></p>
  </div>
  <a href="<?= BASE_URL ?>/propiedad/crear" class="btn btn-primary">+ Nueva Propiedad</a>
</div>

<div class="admin-card">
  <table class="data-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Imagen</th>
        <th>Propiedad</th>
        <th>Tipo</th>
        <th>Precio</th>
        <th>Características</th>
        <th>Estado</th>
        <th>Aprobación</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($propiedades)): ?>
        <tr>
          <td colspan="8" style="text-align:center;padding:3rem;color:var(--text-3)">
            No hay propiedades. <a href="<?= BASE_URL ?>/propiedad/crear" style="color:var(--accent)">Crear primera propiedad →</a>
          </td>
        </tr>
      <?php else: ?>
        <?php 
          $zonaActual = null;
          foreach ($propiedades as $p): 
            if (in_array($_SESSION['usuario_rol'] ?? '', ['admin', 'supervisor'])) {
                $zonaProp = $p->zona_nombre ?? 'Sin Zona Asignada';
                if ($zonaActual !== $zonaProp):
                  $zonaActual = $zonaProp;
        ?>
            <tr>
              <td colspan="9" style="background:var(--bg-2);font-weight:bold;padding:1rem;color:var(--text)">
                📍 Zona: <?= htmlspecialchars($zonaActual) ?>
              </td>
            </tr>
        <?php 
                endif;
            }
        ?>
        <tr>
          <td style="color:var(--text-3);font-size:.8rem">#<?= $p->id ?></td>
          <td>
            <img src="<?= $p->imagen !== 'no-imagen.jpg' ? UPLOAD_URL . htmlspecialchars($p->imagen) : '' ?>"
                 onerror="this.src='https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=100&q=50'"
                 alt="" style="width:60px;height:45px;object-fit:cover;border-radius:6px">
          </td>
          <td>
            <strong><?= htmlspecialchars($p->titulo) ?></strong>
            <?php if ($p->direccion): ?>
              <div style="font-size:.75rem;color:var(--text-3)">📍 <?= htmlspecialchars(substr($p->direccion, 0, 45)) ?></div>
            <?php endif; ?>
            <?php if (!empty($p->zona_nombre)): ?>
              <div style="font-size:.75rem;color:var(--primary);margin-top:.2rem">📍 Asignada a: <?= htmlspecialchars($p->zona_nombre) ?></div>
            <?php elseif (in_array($_SESSION['usuario_rol'] ?? '', ['admin', 'supervisor'])): ?>
              <div style="font-size:.75rem;color:var(--text-3);margin-top:.2rem">📍 Sin zona asignada</div>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-blue"><?= ucfirst($p->tipo) ?></span></td>
          <td><strong><?= Propiedad::formatearPrecio((float)$p->precio) ?></strong></td>
          <td style="font-size:.8rem;color:var(--text-2)">
            <?php if ($p->habitaciones):  ?> 🛏 <?= $p->habitaciones ?>  <?php endif; ?>
            <?php if ($p->banos):         ?> 🚿 <?= $p->banos ?>        <?php endif; ?>
            <?php if ($p->metros2):       ?> 📐 <?= (int)$p->metros2 ?>m² <?php endif; ?>
          </td>
          <td>
            <?php if (($p->estado ?? 'Disponible') === 'Vendida'): ?>
                <span class="badge badge-gold" style="background:#fcd34d;color:#92400e;">Vendida</span>
            <?php elseif (($p->estado ?? 'Disponible') === 'Alquilada'): ?>
                <span class="badge badge-gold" style="background:#fcd34d;color:#92400e;">Alquilada</span>
            <?php else: ?>
                <?= $p->activo ? '<span class="badge badge-green">Activa</span>' : '<span class="badge badge-gray">Inactiva</span>' ?>
            <?php endif; ?>
          </td>
          <td>
            <?php if (($p->estado_aprobacion ?? 'Aprobado') === 'Aprobado'): ?>
                <span class="badge badge-green">Aprobado</span>
            <?php elseif (($p->estado_aprobacion ?? 'Aprobado') === 'Rechazado'): ?>
                <span class="badge badge-gray" style="background:#fecaca;color:#991b1b">Rechazado</span>
            <?php else: ?>
                <span class="badge badge-gold" style="background:#fef08a;color:#854d0e">Pendiente</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= BASE_URL ?>/propiedad/detalle/<?= $p->id ?>" class="btn btn-sm" style="background:rgba(27,43,94,.08);color:var(--text)" target="_blank" title="Ver en portal">👁️</a>
            <?php if (in_array($_SESSION['usuario_rol'] ?? '', ['admin', 'supervisor']) && ($p->estado_aprobacion ?? 'Aprobado') === 'Pendiente'): ?>
                <a href="<?= BASE_URL ?>/propiedad/aprobar/<?= $p->id ?>" class="btn btn-sm" style="background:#22c55e;color:white" title="Aprobar">✅</a>
                <a href="<?= BASE_URL ?>/propiedad/rechazar/<?= $p->id ?>" class="btn btn-sm" style="background:#ef4444;color:white" title="Rechazar">❌</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/propiedad/editar/<?= $p->id ?>" class="btn btn-sm btn-dark" title="Editar">✏️</a>
            <a href="<?= BASE_URL ?>/propiedad/eliminar/<?= $p->id ?>" class="btn btn-sm btn-danger btn-delete" title="Eliminar">🗑️</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
