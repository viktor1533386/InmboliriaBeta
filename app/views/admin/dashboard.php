<?php require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="page-header" style="margin-bottom: 2rem;">
  <div>
    <h2>Bienvenido de vuelta, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin') ?> 👋</h2>
    <p>Aquí tienes un resumen del estado de tu agencia inmobiliaria.</p>
  </div>
</div>

<!-- Stats cards -->
<div class="stats-grid">
  <!-- 1. Nuevos Prospectos -->
  <div class="stat-card" style="<?= $prospectosNuevos > 0 ? 'border-color:var(--accent)' : '' ?>">
    <div class="stat-card__icon green">🎯</div>
    <div>
      <div class="stat-card__num"><?= $prospectosNuevos ?></div>
      <div class="stat-card__lbl">Nuevos Prospectos</div>
    </div>
  </div>
  
  <!-- 2. Ventas / Cierres -->
  <div class="stat-card">
    <div class="stat-card__icon gold">🏆</div>
    <div>
      <div class="stat-card__num"><?= $prospectosCierres ?></div>
      <div class="stat-card__lbl">Cierres Ganados</div>
    </div>
  </div>
  
  <!-- 3. Propiedades Activas -->
  <div class="stat-card">
    <div class="stat-card__icon blue">🏠</div>
    <div>
      <div class="stat-card__num"><?= $totalActivas ?></div>
      <div class="stat-card__lbl">Propiedades Activas</div>
    </div>
  </div>
  
  <!-- 4. Prospectos en Gestión -->
  <div class="stat-card">
    <div class="stat-card__icon purple">📈</div>
    <div>
      <div class="stat-card__num"><?= $prospectosEnGestion ?></div>
      <div class="stat-card__lbl">En Seguimiento</div>
    </div>
  </div>
</div>

<!-- Acciones rápidas -->
<div style="display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap">
  <a href="<?= BASE_URL ?>/propiedad/crear" class="btn btn-primary">+ Nueva Propiedad</a>
  <a href="<?= BASE_URL ?>/vendedor/crear" class="btn btn-dark">+ Nuevo Vendedor</a>
</div>

<!-- Solicitudes de Cierre Pendientes -->
<?php if (!empty($solicitudesCierre)): ?>
<div class="admin-card" style="margin-top:1.5rem; border-left: 4px solid #f59e0b;">
  <div class="admin-card__header">
    <span class="admin-card__title">⏳ Solicitudes de Cierre Pendientes de Aprobación</span>
  </div>
  <table class="data-table">
    <thead>
      <tr>
        <th>Vendedor</th>
        <th>Propiedad</th>
        <th>Prospecto</th>
        <th>Tipo y Monto</th>
        <th>Comentarios</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($solicitudesCierre as $sol): ?>
      <tr>
        <td><strong><?= htmlspecialchars($sol->vendedor_nombre . ' ' . $sol->vendedor_apellido) ?></strong></td>
        <td><?= htmlspecialchars($sol->propiedad_titulo) ?></td>
        <td><?= htmlspecialchars($sol->prospecto_nombre) ?> <br><small><?= htmlspecialchars($sol->prospecto_codigo) ?></small></td>
        <td>
            <span class="badge badge-gold"><?= $sol->tipo_cierre ?></span><br>
            <strong>S/ <?= number_format((float)$sol->monto_final, 2) ?></strong>
        </td>
        <td style="font-size: 0.85rem; max-width: 200px;"><?= htmlspecialchars($sol->comentarios_vendedor) ?></td>
        <td>
          <form method="POST" action="<?= BASE_URL ?>/admin/aprobar_cierre/<?= $sol->id ?>" style="display:inline;">
             <button class="btn btn-sm" style="background:#10b981;color:#fff;" title="Aprobar" onclick="return confirm('¿Seguro que deseas aprobar este cierre? La propiedad se retirará del catálogo.')">✔️ Aprobar</button>
          </form>
          <form method="POST" action="<?= BASE_URL ?>/admin/rechazar_cierre/<?= $sol->id ?>" style="display:inline;">
             <button class="btn btn-sm btn-danger" title="Rechazar" onclick="return confirm('¿Rechazar esta solicitud?')">❌ Rechazar</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- Últimas propiedades -->
<div class="admin-card">
  <div class="admin-card__header">
    <span class="admin-card__title">📋 Últimas Propiedades Agregadas</span>
    <a href="<?= BASE_URL ?>/propiedad/admin" class="btn btn-sm btn-dark">Ver todas</a>
  </div>
  <table class="data-table">
    <thead>
      <tr>
        <th>Propiedad</th>
        <th>Tipo</th>
        <th>Precio</th>
        <th>Estado</th>
        <th>Fecha</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($ultimasProp)): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:2rem">Sin propiedades aún.</td></tr>
      <?php else: ?>
        <?php foreach (array_slice($ultimasProp, 0, 3) as $p): ?>
        <tr>
          <td><strong><?= htmlspecialchars($p->titulo) ?></strong>
              <div style="font-size:.75rem;color:var(--text-3)">📍 <?= htmlspecialchars(substr($p->direccion ?? 'Lima', 0, 40)) ?></div>
          </td>
          <td><span class="badge badge-blue"><?= ucfirst($p->tipo) ?></span></td>
          <td><strong><?= Propiedad::formatearPrecio((float)$p->precio) ?></strong></td>
          <td>
            <?php if ($p->activo): ?>
              <span class="badge badge-green">Activa</span>
            <?php else: ?>
              <span class="badge badge-gray">Inactiva</span>
            <?php endif; ?>
          </td>
          <td style="font-size:.8rem"><?= date('d/m/Y', strtotime($p->created_at)) ?></td>
          <td>
            <a href="<?= BASE_URL ?>/propiedad/editar/<?= $p->id ?>" class="btn btn-sm btn-dark" style="margin-right:.3rem">✏️</a>
            <a href="<?= BASE_URL ?>/propiedad/eliminar/<?= $p->id ?>" class="btn btn-sm btn-danger btn-delete">🗑️</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Últimos Prospectos -->
<?php if (!empty($ultimosProspectos)): ?>
<div class="admin-card" style="margin-top:1.5rem">
  <div class="admin-card__header">
    <span class="admin-card__title">🎯 Últimos Prospectos Ingresados</span>
    <a href="<?= BASE_URL ?>/admin/prospectos" class="btn btn-sm btn-dark">Ir a Bandeja</a>
  </div>
  <table class="data-table">
    <thead>
      <tr><th>Visitante</th><th>Código</th><th>Estado</th><th>Fecha</th></tr>
    </thead>
    <tbody>
      <?php foreach ($ultimosProspectos as $p): ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars($p->nombre) ?></strong>
          <div style="font-size:.75rem;color:var(--text-3)"><?= htmlspecialchars($p->email) ?></div>
          <?php if (!empty($p->propiedad_titulo)): ?>
            <div style="font-size:.75rem;color:var(--primary);margin-top:.2rem">🏠 <?= htmlspecialchars($p->propiedad_titulo) ?></div>
          <?php endif; ?>
        </td>
        <td style="font-size:.85rem"><strong><?= htmlspecialchars($p->codigo) ?></strong></td>
        <td>
          <?php if ($p->estado === 'Nuevo'): ?>
            <span class="badge badge-gold">Nuevo</span>
          <?php elseif ($p->estado === 'Asignado'): ?>
            <span class="badge badge-blue">Asignado</span>
          <?php else: ?>
            <span class="badge badge-gray"><?= htmlspecialchars($p->estado) ?></span>
          <?php endif; ?>
        </td>
        <td style="font-size:.8rem"><?= date('d/m/Y H:i', strtotime($p->created_at)) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<script>
<?php if (isset($prospectosNuevos) && $prospectosNuevos > 0): ?>
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'info',
    title: 'Tienes <?= $prospectosNuevos ?> prospecto(s) nuevo(s) por asignar.',
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true
  });
});
<?php endif; ?>
</script>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
