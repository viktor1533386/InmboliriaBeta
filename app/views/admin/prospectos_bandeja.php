<?php $activePage = 'prospectos'; require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem">
  <h1 style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--text)">
    Bandeja de Prospectos Nuevos
  </h1>
</div>

<div class="card" style="padding:1.5rem">
  
  <?php if (empty($prospectos)): ?>
    <div style="text-align:center;padding:3rem 0;color:var(--text-2)">
      <div style="font-size:3rem;margin-bottom:1rem"> inbox_zero </div>
      <h3>No hay prospectos nuevos</h3>
      <p>Todos los visitantes han sido asignados.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Código / Fecha</th>
            <th>Visitante</th>
            <th>Contacto</th>
            <th>Mensaje</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($prospectos as $p): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($p->codigo) ?></strong><br>
                <small style="color:var(--text-2)"><?= date('d/m/Y H:i', strtotime($p->created_at)) ?></small>
              </td>
              <td>
                <div style="font-weight:500"><?= htmlspecialchars($p->nombre) ?></div>
              </td>
              <td>
                <a href="mailto:<?= htmlspecialchars($p->email) ?>" style="color:var(--primary);text-decoration:none"><?= htmlspecialchars($p->email) ?></a><br>
                <small><?= htmlspecialchars($p->telefono ?? 'Sin teléfono') ?></small>
              </td>
              <td style="max-width:200px">
                <div style="display:flex;align-items:center;gap:.5rem">
                  <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.9rem;flex:1" title="<?= htmlspecialchars($p->mensaje) ?>">
                    <?= htmlspecialchars($p->mensaje) ?>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline" style="padding:.2rem .5rem;font-size:.75rem;border-color:var(--border);color:var(--text)" onclick="viewMessage(this)" data-nombre="<?= htmlspecialchars($p->nombre) ?>" data-email="<?= htmlspecialchars($p->email) ?>" data-fecha="<?= date('d/m/Y H:i', strtotime($p->created_at)) ?>" data-mensaje="<?= htmlspecialchars($p->mensaje) ?>">Ver</button>
                </div>
              </td>
              <td>
                <form method="POST" action="<?= BASE_URL ?>/admin/asignar/<?= $p->id ?>" style="display:flex;gap:.5rem">
                  <select name="vendedor_id" required style="padding:.4rem;border:1px solid var(--border);border-radius:var(--radius);font-size:.9rem;background:white">
                    <option value="">-- Seleccionar Vendedor --</option>
                    <?php foreach ($vendedores as $v): ?>
                      <option value="<?= $v->id ?>"><?= htmlspecialchars($v->nombre . ' ' . $v->apellido) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="btn btn-primary" style="padding:.4rem .8rem;font-size:.9rem">
                    Asignar
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>

<script>
function viewMessage(btn) {
  const nombre = btn.getAttribute('data-nombre');
  const email = btn.getAttribute('data-email');
  const fecha = btn.getAttribute('data-fecha');
  const mensaje = btn.getAttribute('data-mensaje');
  
  Swal.fire({
    title: 'Mensaje de ' + nombre,
    html: `
      <div style="text-align:left;font-size:.9rem;color:var(--text-2);margin-bottom:1rem">
        <strong>Email:</strong> <a href="mailto:${email}">${email}</a><br>
        <strong>Fecha:</strong> ${fecha}
      </div>
      <div style="text-align:left;background:var(--bg-alt);padding:1rem;border-radius:8px;border:1px solid var(--border);color:var(--text);white-space:pre-wrap;font-size:.95rem">${mensaje}</div>
    `,
    width: 600,
    confirmButtonText: 'Cerrar',
    confirmButtonColor: 'var(--primary)'
  });
}
</script>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
