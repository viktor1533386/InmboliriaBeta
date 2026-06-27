<?php $activePage = 'seguimiento'; require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="seguimiento-page" style="min-height:70vh;padding:4rem 1rem;">
  <div class="container" style="max-width:800px;margin:0 auto">
    
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem">
      <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;color:var(--text)">
        Estado de Solicitud
      </h1>
      <a href="<?= BASE_URL ?>/seguimiento" class="btn btn-outline" style="font-size:.9rem;padding:.5rem 1rem">
        ← Volver
      </a>
    </div>

    <!-- Tarjeta de Resultado -->
    <div style="background:var(--card);border-radius:var(--radius-lg);padding:2.5rem;border:1px solid var(--border);box-shadow:var(--shadow);text-align:center">
      
      <div style="font-family:monospace;font-size:1.2rem;color:var(--text-2);margin-bottom:1rem">
        <?= htmlspecialchars($prospecto->codigo) ?>
      </div>
      
      <h2 style="font-size:1.8rem;color:<?= $template ? htmlspecialchars($template->color_hex) : 'var(--primary)' ?>;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:center;gap:.5rem">
        <span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:currentColor"></span>
        <?= htmlspecialchars($prospecto->estado) ?>
      </h2>

      <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:var(--radius);padding:2rem;text-align:left;color:var(--text-2);font-size:1.1rem;line-height:1.6">
        <h3 style="color:var(--text);margin-bottom:1rem;font-size:1.25rem">
          <?= $template ? htmlspecialchars($template->titulo_template) : 'Actualización' ?>
        </h3>
        <div>
          <?= $cuerpoHtml // Ya se han limpiado/reemplazado variables en el controller, pero ideal usar purificador en producción ?>
        </div>
      </div>

      <div style="margin-top:2rem;padding-top:2rem;border-top:1px solid var(--border);color:var(--text-2);font-size:.95rem">
        Fecha de creación: <?= date('d/m/Y H:i', strtotime($prospecto->created_at)) ?><br>
        Última actualización: <?= date('d/m/Y H:i', strtotime($prospecto->updated_at)) ?>
      </div>

    </div>

  </div>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
