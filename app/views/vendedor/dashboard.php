<?php require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="page-header" style="margin-bottom: 2rem;">
  <div>
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Agente') ?> 👋</h2>
    <p>Aquí tienes un resumen de tu actividad y prospectos asignados.</p>
  </div>
</div>

<!-- Stats cards -->
<div class="stats-grid">
  <!-- 1. Nuevas Asignaciones -->
  <div class="stat-card" style="<?= $asignacionesRecientes > 0 ? 'border-color:var(--accent)' : '' ?>">
    <div class="stat-card__icon green">🎯</div>
    <div>
      <div class="stat-card__num"><?= $asignacionesRecientes ?></div>
      <div class="stat-card__lbl">Nuevas Asignaciones</div>
    </div>
  </div>
  
  <!-- 2. En Gestión -->
  <div class="stat-card">
    <div class="stat-card__icon purple">📈</div>
    <div>
      <div class="stat-card__num"><?= $prospectosEnGestion ?></div>
      <div class="stat-card__lbl">En Seguimiento</div>
    </div>
  </div>

  <!-- 3. Cierres Ganados -->
  <div class="stat-card">
    <div class="stat-card__icon gold">🏆</div>
    <div>
      <div class="stat-card__num"><?= $prospectosGanados ?></div>
      <div class="stat-card__lbl">Ventas Cerradas</div>
    </div>
  </div>
  
  <!-- 4. Propiedades -->
  <div class="stat-card">
    <div class="stat-card__icon blue">🏠</div>
    <div>
      <div class="stat-card__num"><?= $misPropiedades ?></div>
      <div class="stat-card__lbl">Mis Propiedades Activas</div>
    </div>
  </div>
</div>

<!-- Últimos Prospectos -->
<?php if (!empty($ultimosProspectos)): ?>
<div class="admin-card" style="margin-top:1.5rem">
  <div class="admin-card__header">
    <span class="admin-card__title">👥 Mis Últimos Prospectos Asignados</span>
    <a href="<?= BASE_URL ?>/vendedor/prospectos" class="btn btn-sm btn-dark">Ver todos</a>
  </div>
  <table class="data-table">
    <thead>
      <tr><th>Visitante</th><th>Código</th><th>Estado</th><th>Fecha de Asignación</th><th>Acción</th></tr>
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
          <?php if ($p->estado === 'Asignado'): ?>
            <span class="badge badge-gold">Nuevo/Asignado</span>
          <?php elseif ($p->estado === 'En_Negociacion'): ?>
            <span class="badge badge-purple">Negociación</span>
          <?php elseif ($p->estado === 'Cerrado_Ganado'): ?>
            <span class="badge badge-green">Ganado</span>
          <?php else: ?>
            <span class="badge badge-blue"><?= htmlspecialchars(str_replace('_', ' ', $p->estado)) ?></span>
          <?php endif; ?>
        </td>
        <td style="font-size:.8rem"><?= date('d/m/Y H:i', strtotime($p->fecha_asignacion ?? $p->created_at)) ?></td>
        <td>
          <a href="<?= BASE_URL ?>/vendedor/detalle/<?= $p->id ?>" class="btn btn-sm btn-outline" style="padding:.2rem .5rem;border-color:var(--border);color:var(--text);font-size:.75rem">Gestionar</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
  <div class="admin-card" style="margin-top:1.5rem;text-align:center;padding:3rem 1rem">
    <h3 style="color:var(--text-2);margin-bottom:.5rem">No tienes prospectos asignados</h3>
    <p style="color:var(--text-3)">Tus prospectos aparecerán aquí una vez que el supervisor te los asigne.</p>
  </div>
<?php endif; ?>

<script>
<?php if (isset($asignacionesRecientes) && $asignacionesRecientes > 0): ?>
document.addEventListener('DOMContentLoaded', function() {
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'info',
    title: 'Tienes <?= $asignacionesRecientes ?> prospecto(s) recién asignado(s).',
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true
  });
});
<?php endif; ?>
</script>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
