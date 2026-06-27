<?php $activePage = 'mis_prospectos'; require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div style="margin-bottom:2rem">
  <a href="<?= BASE_URL ?>/vendedor/prospectos" style="color:var(--text-2);text-decoration:none">← Volver a prospectos</a>
  <h1 style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--text);margin-top:1rem">
    Detalle y Seguimiento
  </h1>
</div>

<div class="grid" style="display:grid;grid-template-columns:1fr 2fr;gap:2rem">

  <!-- Columna 1: Info del Prospecto -->
  <div>
    <div class="card" style="padding:1.5rem;margin-bottom:1.5rem">
      <h3 style="margin-bottom:1rem;color:var(--text);border-bottom:1px solid var(--border);padding-bottom:.5rem">Datos del Visitante</h3>
      <p><strong>Nombre:</strong> <?= htmlspecialchars($prospecto->nombre) ?></p>
      <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($prospecto->email) ?>"><?= htmlspecialchars($prospecto->email) ?></a></p>
      <p><strong>Teléfono:</strong> <?= htmlspecialchars($prospecto->telefono ?? 'N/A') ?></p>
      <p><strong>Código:</strong> <?= htmlspecialchars($prospecto->codigo) ?></p>
      <p><strong>Estado Actual:</strong> <span style="background:var(--primary);color:white;padding:.2rem .6rem;border-radius:1rem;font-size:.85rem"><?= htmlspecialchars($prospecto->estado) ?></span></p>
      <?php if (!empty($propiedad)): ?>
        <p style="margin-top:.5rem"><strong>Interesado en:</strong> <a href="<?= BASE_URL ?>/propiedad/detalle/<?= $propiedad->id ?>" target="_blank" style="color:var(--primary);text-decoration:none;font-weight:500">🏠 <?= htmlspecialchars($propiedad->titulo) ?></a></p>
        <?php if ($rol === 'vendedor' && ($propiedad->estado ?? 'Disponible') === 'Disponible' && !in_array($prospecto->estado, ['Cerrado_Ganado', 'Cerrado_Perdedor', 'Pendiente_Cierre'])): ?>
          <div style="margin-top: 1rem;">
            <button type="button" class="btn btn-primary" style="width:100%; justify-content:center; background-color:#10b981; border-color:#10b981;" onclick="document.getElementById('modal-cierre').style.display='flex'">🏆 Solicitar Cierre de Operación</button>
          </div>
        <?php elseif ($prospecto->estado === 'Pendiente_Cierre'): ?>
          <div style="margin-top: 1rem; padding: 0.5rem; background: #fffbeb; border: 1px solid #fcd34d; border-radius: 6px; color: #b45309; text-align: center; font-size: 0.9rem;">
            ⏳ Solicitud de Cierre en revisión por Supervisor
          </div>
          <?php if (in_array($rol, ['supervisor', 'admin']) && !empty($solicitudPendiente)): ?>
            <div style="margin-top: 1rem; padding: 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;">
              <h4 style="margin-top:0; color:#166534; font-size:1rem; margin-bottom:0.5rem;">✅ Aprobar Venta</h4>
              <p style="font-size:0.85rem; color:#15803d; margin-bottom:1rem; line-height:1.4;">
                Monto final: <strong>S/ <?= number_format($solicitudPendiente->monto_final, 2) ?></strong><br>
                Nota: <?= htmlspecialchars($solicitudPendiente->comentarios_vendedor) ?>
              </p>
              <div style="display:flex; gap:0.5rem;">
                <form method="POST" action="<?= BASE_URL ?>/admin/aprobar_cierre/<?= $solicitudPendiente->id ?>" style="flex:1">
                  <button type="submit" class="btn" style="background:#22c55e; color:white; width:100%; border:none; padding:8px; border-radius:4px; cursor:pointer; font-weight:600; justify-content:center;">Aprobar</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>/admin/rechazar_cierre/<?= $solicitudPendiente->id ?>" style="flex:1" onsubmit="return confirm('¿Seguro que deseas rechazar este cierre? El prospecto volverá a En Negociación.');">
                  <button type="submit" class="btn" style="background:#ef4444; color:white; width:100%; border:none; padding:8px; border-radius:4px; cursor:pointer; font-weight:600; justify-content:center;">Rechazar</button>
                </form>
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      <?php endif; ?>
      
      <h3 style="margin-top:1.5rem;margin-bottom:1rem;color:var(--text);border-bottom:1px solid var(--border);padding-bottom:.5rem">Mensaje Original</h3>
      <p style="color:var(--text-2);font-size:.95rem;line-height:1.5;background:#f9fafb;padding:1rem;border-radius:var(--radius)">
        <?= nl2br(htmlspecialchars($prospecto->mensaje)) ?>
      </p>
    </div>

    <?php if ($rol === 'vendedor'): ?>
      <?php if (in_array($prospecto->estado, ['Cerrado_Ganado', 'Cerrado_Perdedor', 'Pendiente_Cierre'])): ?>
        <div class="card" style="padding:1.5rem; background:#f9fafb; border:1px solid var(--border); text-align:center;">
          <h3 style="color:var(--text-2); margin-bottom:0.5rem;">🔒 Interacciones Bloqueadas</h3>
          <p style="color:var(--text-3); font-size:0.9rem; margin:0;">Este prospecto está cerrado o en proceso de cierre. El historial es de solo lectura. Si necesitas agregar notas (ej. seguimiento post-venta), solicítale a tu supervisor que reabra el prospecto.</p>
        </div>
      <?php else: ?>
        <!-- Formulario para nueva actividad -->
        <div class="card" style="padding:1.5rem">
          <h3 style="margin-bottom:1rem">Registrar Interacción</h3>
          <form method="POST" action="<?= BASE_URL ?>/vendedor/actividades/<?= $prospecto->id ?>">
            
            <div style="margin-bottom:1rem">
              <label style="display:block;margin-bottom:.3rem;font-size:.9rem">Tipo de Interacción</label>
              <select name="tipo" required style="width:100%;padding:.5rem;border:1px solid var(--border);border-radius:var(--radius)">
                <option value="Llamada">Llamada telefónica</option>
                <option value="Email">Envío de Email</option>
                <option value="WhatsApp">Mensaje por WhatsApp</option>
                <option value="Reunion">Reunión presencial / virtual</option>
                <option value="Visita">Visita a propiedad</option>
              </select>
            </div>

            <div style="margin-bottom:1rem">
              <label style="display:block;margin-bottom:.3rem;font-size:.9rem">¿Cambiar Estado?</label>
              <select name="nuevo_estado" style="width:100%;padding:.5rem;border:1px solid var(--border);border-radius:var(--radius)">
                <option value="">Mantener estado actual (<?= htmlspecialchars($prospecto->estado) ?>)</option>
                <?php foreach ($estados as $est): ?>
                  <?php if ($est !== $prospecto->estado): ?>
                    <option value="<?= $est ?>"><?= htmlspecialchars($est) ?></option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
              <small style="color:var(--text-2)">Si cambias el estado, el visitante recibirá un email automático.</small>
            </div>

            <div style="margin-bottom:1rem">
              <label style="display:block;margin-bottom:.3rem;font-size:.9rem">Comentario / Notas</label>
              <textarea name="comentario" required style="width:100%;padding:.5rem;border:1px solid var(--border);border-radius:var(--radius);min-height:80px" placeholder="Resumen de lo conversado..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Guardar Actividad</button>
          </form>
        </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (in_array($rol, ['supervisor', 'admin'])): ?>
      <div class="card" style="padding:1.5rem; margin-top:1.5rem; background:#fff8f1; border:1px solid #fdba74;">
        <h3 style="color:#c2410c; margin-bottom:1rem;">🔧 Acciones de Supervisor</h3>
        <p style="font-size:0.9rem; color:#9a3412; margin-bottom:1rem;">Puedes forzar el cambio de estado de este prospecto (por ejemplo, pasarlo a Post-Venta o reabrirlo).</p>
        <form method="POST" action="<?= BASE_URL ?>/vendedor/reabrir_prospecto/<?= $prospecto->id ?>">
          <div style="margin-bottom:1rem;">
            <label style="display:block;margin-bottom:.3rem;font-size:.9rem; color:#9a3412;">Forzar Estado:</label>
            <select name="estado_forzado" required style="width:100%;padding:.5rem;border:1px solid #fdba74;border-radius:var(--radius);background:#fff;">
              <option value="">Seleccione un estado...</option>
              <option value="Post_Venta">Post-Venta (Reabrir)</option>
              <option value="En_Negociacion">En Negociación (Reabrir)</option>
              <?php foreach ($estados as $est): ?>
                <?php if ($est !== $prospecto->estado): ?>
                  <option value="<?= $est ?>"><?= htmlspecialchars($est) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn" style="background:#ea580c; color:#fff; width:100%; justify-content:center;">Cambiar Estado</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <!-- Columna 2: Timeline -->
  <div>
    <div class="card" style="padding:1.5rem">
      <h3 style="margin-bottom:1.5rem">Línea de Tiempo (Timeline)</h3>
      
      <!-- Filtros -->
      <div style="margin-bottom:1.5rem;display:flex;gap:.5rem;flex-wrap:wrap">
        <button type="button" class="btn btn-sm btn-dark" onclick="filterTimeline('all')">Todos</button>
        <button type="button" class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--text)" onclick="filterTimeline('Llamada')">Llamadas</button>
        <button type="button" class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--text)" onclick="filterTimeline('Email')">Emails</button>
        <button type="button" class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--text)" onclick="filterTimeline('WhatsApp')">WhatsApp</button>
        <button type="button" class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--text)" onclick="filterTimeline('Reunion')">Reunión</button>
        <button type="button" class="btn btn-sm btn-outline" style="border-color:var(--border);color:var(--text)" onclick="filterTimeline('Visita')">Visitas</button>
      </div>

      <div style="position:relative;padding-left:1.5rem;border-left:2px solid var(--border)">
        <?php foreach ($actividades as $act): ?>
          <div class="timeline-item" data-type="<?= htmlspecialchars($act->tipo) ?>" style="position:relative;margin-bottom:1.5rem">
            <!-- Punto del timeline -->
            <div style="position:absolute;left:-1.85rem;top:.2rem;width:12px;height:12px;border-radius:50%;background:var(--primary)"></div>
            
            <div style="background:#f9fafb;padding:1rem;border-radius:var(--radius);border:1px solid var(--border)">
              <div style="display:flex;justify-content:space-between;margin-bottom:.5rem">
                <div>
                  <strong style="color:var(--text)"><?= htmlspecialchars($act->tipo) ?></strong>
                  <?php if (!empty($act->autor_nombre)): ?>
                    <span style="font-size:.8rem;color:var(--text-3);margin-left:.5rem">por <?= htmlspecialchars($act->autor_nombre) ?></span>
                  <?php endif; ?>
                </div>
                <span style="color:var(--text-2);font-size:.85rem"><?= date('d/m/Y H:i', strtotime($act->created_at)) ?></span>
              </div>
              <p style="margin:0;color:var(--text-2);font-size:.95rem">
                <?= nl2br(htmlspecialchars($act->comentario)) ?>
              </p>
              <?php if (!empty($act->nuevo_estado) && $act->nuevo_estado !== $prospecto->estado): ?>
                <div style="margin-top:.5rem;font-size:.85rem;color:var(--primary)">
                  → Cambió el estado a <strong><?= htmlspecialchars($act->nuevo_estado) ?></strong>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>

</div>

<script>
function filterTimeline(type) {
  document.querySelectorAll('.timeline-item').forEach(item => {
    if (type === 'all' || item.getAttribute('data-type') === type) {
      item.style.display = 'block';
    } else {
      item.style.display = 'none';
    }
  });
}
</script>

<?php if ($rol === 'vendedor'): ?>
<!-- Modal Solicitar Cierre -->
<div id="modal-cierre" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
  <div class="card" style="padding:2rem; width:100%; max-width:500px; background:#fff; margin:1rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
      <h3 style="margin:0; font-family:'Playfair Display',serif;">🏆 Solicitar Cierre</h3>
      <button type="button" style="background:none; border:none; font-size:1.5rem; cursor:pointer;" onclick="document.getElementById('modal-cierre').style.display='none'">&times;</button>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/vendedor/solicitar_cierre/<?= $prospecto->id ?>">
      <div style="margin-bottom:1rem">
        <label style="display:block;margin-bottom:.3rem">Tipo de Cierre *</label>
        <select name="tipo_cierre" required style="width:100%;padding:.5rem;border:1px solid var(--border);border-radius:var(--radius)">
          <option value="Venta">Venta</option>
          <option value="Alquiler">Alquiler</option>
        </select>
      </div>
      <div style="margin-bottom:1rem">
        <label style="display:block;margin-bottom:.3rem">Monto Final Acordado (S/) *</label>
        <input type="number" step="0.01" min="0" name="monto_final" required style="width:100%;padding:.5rem;border:1px solid var(--border);border-radius:var(--radius)">
      </div>
      <div style="margin-bottom:1rem">
        <label style="display:block;margin-bottom:.3rem">Comentarios para el Supervisor</label>
        <textarea name="comentarios" style="width:100%;padding:.5rem;border:1px solid var(--border);border-radius:var(--radius);min-height:80px" placeholder="Detalles de la negociación, forma de pago, etc."></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;background-color:#10b981;border-color:#10b981;">Enviar Solicitud al Supervisor</button>
    </form>
  </div>
</div>
<?php endif; ?>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
