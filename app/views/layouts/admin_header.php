<?php
require_once APP_ROOT . '/app/models/Notificacion.php';
$notifModel = new Notificacion();
$notificacionesNoLeidas = $notifModel->findNoLeidas($_SESSION['usuario_id']);
$numNotif = count($notificacionesNoLeidas);
?>
<!-- ============================================================
     ADMIN LAYOUT COMPONENT
     Inclúyelo al inicio de cada vista de admin así:
     require_once APP_ROOT . '/app/views/layouts/admin_header.php';
============================================================ -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($titulo ?? 'Admin') ?> – Panel</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="admin-layout">

  <?php $rolUsuario = $_SESSION['usuario_rol'] ?? 'supervisor'; ?>

  <!-- ── SIDEBAR ─────────────────────────── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar__brand">
      <div class="sidebar__brand-name">🏠 <span>Hogar Ideal</span> Perú</div>
      <div class="sidebar__sub"><?= $rolUsuario === 'admin' ? 'Panel TI' : ($rolUsuario === 'vendedor' ? 'Panel Agente' : 'Panel Supervisor') ?></div>
    </div>

    <nav class="sidebar__nav">
      <?php if (in_array($rolUsuario, ['admin', 'supervisor', 'vendedor'])): ?>
        <p class="sidebar__nav-title">Principal</p>
        <a href="<?= BASE_URL ?><?= $rolUsuario === 'vendedor' ? '/vendedor/dashboard' : '/admin/dashboard' ?>"
           class="<?= strpos($titulo ?? '', 'Dashboard') !== false ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Dashboard
        </a>
      <?php endif; ?>

        <p class="sidebar__nav-title">Gestión</p>
        <?php if (in_array($rolUsuario, ['admin', 'supervisor', 'vendedor', 'especialista_ti'])): ?>
        <a href="<?= BASE_URL ?>/propiedad/admin"
           class="<?= strpos($titulo ?? '', 'Propiedad') !== false && strpos($titulo ?? '', 'Dashboard') === false ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          Propiedades
        </a>
        <?php endif; ?>
        
        <?php if (in_array($rolUsuario, ['admin', 'supervisor'])): ?>
        <a href="<?= BASE_URL ?>/admin/prospectos"
           class="<?= strpos($titulo ?? '', 'Bandeja de Prospectos') !== false ? 'active' : '' ?>" style="margin-top: 5px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Bandeja de Prospectos
        </a>
        <?php endif; ?>

        <?php if (in_array($rolUsuario, ['vendedor', 'supervisor', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/vendedor/prospectos"
           class="<?= strpos($titulo ?? '', 'Mis Prospectos') !== false || strpos($titulo ?? '', 'Detalle del Prospecto') !== false ? 'active' : '' ?>" style="margin-top: 5px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          <?= $rolUsuario === 'vendedor' ? 'Mis Prospectos' : 'Todos los Prospectos' ?>
        </a>
        <?php endif; ?>

      <?php 
        $ticketMenuName = '';
        if ($rolUsuario === 'scrum_master') $ticketMenuName = 'Control Mesa Ayuda';
        elseif ($rolUsuario === 'especialista_ti') $ticketMenuName = 'Tickets Asignados';
        elseif ($rolUsuario === 'seguridad') $ticketMenuName = 'Auditoría Tickets';
        else $ticketMenuName = 'Mis Tickets Soporte';
      ?>
        <a href="<?= BASE_URL ?>/ticket/index"
           class="<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/ticket/index') !== false ? 'active' : '' ?>" style="margin-top: 5px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <?= $ticketMenuName ?>
        </a>

      <?php if (in_array($rolUsuario, ['admin', 'supervisor', 'vendedor'])): ?>
        <a href="<?= BASE_URL ?>/ticket/create"
           class="<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/ticket/create') !== false ? 'active' : '' ?>" style="margin-top: 5px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          Crear Ticket
        </a>
      <?php endif; ?>

        <?php if (in_array($rolUsuario, ['admin', 'supervisor', 'scrum_master', 'especialista_ti', 'seguridad'])): ?>
        <a href="<?= BASE_URL ?>/vendedor"
           class="<?= strpos($titulo ?? '', 'Vendedor') !== false ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          Vendedores
        </a>
        <?php endif; ?>

      <?php if (in_array($rolUsuario, ['admin', 'supervisor'])): ?>
        <a href="<?= BASE_URL ?>/admin/backup" target="_blank"
           class="sidebar__nav-link" style="margin-top: 5px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
          Respaldar BD
        </a>
      <?php endif; ?>

      <?php if ($rolUsuario === 'admin'): ?>
        <p class="sidebar__nav-title">TI</p>
        <a href="<?= BASE_URL ?>/usuario"
           class="<?= strpos($titulo ?? '', 'Usuario') !== false ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          Usuarios
        </a>
        <a href="<?= BASE_URL ?>/admin/restore"
           class="<?= strpos($titulo ?? '', 'Restore') !== false ? 'active' : '' ?>" style="margin-top: 5px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4M12 17h.01"/></svg>
          Restaurar BD
        </a>
      <?php endif; ?>
    </nav>

<?php
$displayRole = 'Supervisor';
if ($rolUsuario === 'admin') $displayRole = 'Admin (TI)';
elseif ($rolUsuario === 'vendedor') $displayRole = 'Agente Inmobiliario';
elseif ($rolUsuario === 'scrum_master') $displayRole = 'Scrum Master';
elseif ($rolUsuario === 'especialista_ti') $displayRole = 'Especialista TI';
elseif ($rolUsuario === 'seguridad') $displayRole = 'Seguridad';

$displayName = $_SESSION['usuario_nombre'] ?? 'Admin';
if (in_array($rolUsuario, ['scrum_master', 'especialista_ti', 'seguridad'])) {
    $displayName = $displayRole;
}
?>
    <div class="sidebar__user">
      <div class="sidebar__user-av">
        <?= strtoupper(substr($displayName, 0, 1)) ?>
      </div>
      <div>
        <div class="sidebar__user-name"><?= htmlspecialchars($displayName) ?></div>
        <div class="sidebar__user-role"><?= htmlspecialchars($displayRole) ?></div>
      </div>
      <a href="<?= BASE_URL ?>/auth/logout" class="sidebar__logout" title="Cerrar sesión">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      </a>
    </div>
  </aside>

  <!-- ── MAIN ───────────────────────────── -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div>
        <h1><?= htmlspecialchars($titulo ?? 'Admin') ?></h1>
      </div>
      <div style="display:flex;align-items:center;gap:.75rem">
        <!-- Notificaciones Dropdown -->
        <div style="position: relative; margin-right: 1rem;">
          <button id="btn-notificaciones" style="background:none;border:none;font-size:1.5rem;cursor:pointer;position:relative;">
            🔔
            <?php if ($numNotif > 0): ?>
              <span style="position:absolute;top:-5px;right:-5px;background:#ef4444;color:white;border-radius:50%;width:18px;height:18px;font-size:11px;font-weight:bold;line-height:18px;text-align:center;"><?= $numNotif ?></span>
            <?php endif; ?>
          </button>
          
          <div id="dropdown-notificaciones" style="display:none;position:absolute;right:0;top:35px;width:320px;background:#fff;border:1px solid var(--border);border-radius:var(--radius);box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);z-index:100;max-height:400px;overflow-y:auto;">
            <div style="padding:1rem;border-bottom:1px solid var(--border);font-weight:bold;display:flex;justify-content:space-between;">
                <span>Notificaciones</span>
                <?php if ($numNotif > 0): ?>
                    <span style="font-size:0.8rem;color:var(--primary);cursor:pointer;" onclick="window.location='<?= BASE_URL ?>/admin/leer_todas_notificaciones'">Marcar leídas</span>
                <?php endif; ?>
            </div>
            <?php if ($numNotif === 0): ?>
              <div style="padding:1.5rem 1rem;text-align:center;color:var(--text-3);font-size:0.9rem;">No tienes notificaciones nuevas.</div>
            <?php else: ?>
              <?php foreach ($notificacionesNoLeidas as $n): ?>
                <a href="<?= BASE_URL ?>/admin/leer_notificacion/<?= $n->id ?>" style="display:block;padding:1rem;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                  <div style="font-weight:600;font-size:0.9rem;margin-bottom:0.2rem;color:var(--text);"><?= htmlspecialchars($n->titulo) ?></div>
                  <div style="font-size:0.85rem;color:var(--text-2);line-height:1.4;"><?= htmlspecialchars($n->mensaje) ?></div>
                  <div style="font-size:0.75rem;color:var(--text-3);margin-top:0.4rem;"><?= date('d/m/Y H:i', strtotime($n->created_at)) ?></div>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <a href="<?= BASE_URL ?>/auth/cambiar" class="btn btn-sm btn-dark">Cambiar clave</a>
        <a href="<?= BASE_URL ?>/auth/logout" class="btn btn-sm btn-danger">Salir</a>
      </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const btn = document.getElementById('btn-notificaciones');
      const dropdown = document.getElementById('dropdown-notificaciones');
      if (btn) {
        btn.addEventListener('click', function(e) {
          e.stopPropagation();
          dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        });
      }
      document.addEventListener('click', function(e) {
        if (dropdown && !dropdown.contains(e.target) && e.target !== btn) {
          dropdown.style.display = 'none';
        }
      });
    });
    </script>

    <!-- Flash messages are now handled by SweetAlert in the footer -->
    <div class="admin-content">
