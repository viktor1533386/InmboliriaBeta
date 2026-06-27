<?php require_once APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h2>
            <?php
            if ($userRole === 'scrum_master') echo 'Control Mesa de Ayuda 🛠️';
            elseif ($userRole === 'especialista_ti') echo 'Mis Tickets Asignados 👨‍💻';
            elseif ($userRole === 'seguridad') echo 'Auditoría de Tickets 🛡️';
            else echo 'Mis Tickets de Soporte 🎫';
            ?>
        </h2>
        <p>Sistema de gestión de incidentes y servicios técnicos.</p>
    </div>
</div>

<?php
$countNuevos = 0;
$countEnCurso = 0;
$countResueltos = 0;
foreach ($tickets as $t) {
    if ($t['estado'] === 'Abierto') $countNuevos++;
    elseif ($t['estado'] === 'En Progreso') $countEnCurso++;
    elseif ($t['estado'] === 'Resuelto' || $t['estado'] === 'Cerrado') $countResueltos++;
}
?>
<div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
    <div style="flex: 1; background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; border-left: 4px solid #dc3545;">
        <h3 style="margin: 0; color: #666; font-size: 0.9rem;">Nuevos (Abiertos)</h3>
        <p style="margin: 0.5rem 0 0 0; font-size: 1.5rem; font-weight: bold; color: #dc3545;"><?= $countNuevos ?></p>
    </div>
    <div style="flex: 1; background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; border-left: 4px solid #ffc107;">
        <h3 style="margin: 0; color: #666; font-size: 0.9rem;">En Curso</h3>
        <p style="margin: 0.5rem 0 0 0; font-size: 1.5rem; font-weight: bold; color: #ffc107;"><?= $countEnCurso ?></p>
    </div>
    <div style="flex: 1; background: #fff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; border-left: 4px solid #17a2b8;">
        <h3 style="margin: 0; color: #666; font-size: 0.9rem;">Resueltos/Cerrados</h3>
        <p style="margin: 0.5rem 0 0 0; font-size: 1.5rem; font-weight: bold; color: #17a2b8;"><?= $countResueltos ?></p>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert success" style="padding:15px; background:#d4edda; color:#155724; border-radius:8px; margin-bottom:20px;">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="card" style="background:#fff; border-radius:12px; padding:2rem; box-shadow:0 4px 6px rgba(0,0,0,0.05); overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #eee;">
                <th style="padding: 12px;">Código</th>
                <?php if ($userRole === 'scrum_master' || $userRole === 'seguridad'): ?>
                    <th style="padding: 12px;">Solicitante</th>
                <?php endif; ?>
                <th style="padding: 12px;">Servicio</th>
                <th style="padding: 12px;">Prioridad</th>
                <th style="padding: 12px;">Estado</th>
                <?php if ($userRole !== 'especialista_ti'): ?>
                    <th style="padding: 12px;">Asignado A</th>
                <?php endif; ?>
                <th style="padding: 12px;">Fecha</th>
                <th style="padding: 12px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">No hay tickets registrados en esta bandeja.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <?php 
                        $badgeColor = '#6c757d'; // Default
                        if ($ticket['estado'] === 'Abierto') $badgeColor = '#dc3545';
                        elseif ($ticket['estado'] === 'En Progreso') $badgeColor = '#ffc107';
                        elseif ($ticket['estado'] === 'Resuelto') $badgeColor = '#17a2b8';
                        elseif ($ticket['estado'] === 'Cerrado') $badgeColor = '#28a745';

                        $prioColor = $ticket['prioridad'] === 'Alta' ? 'red' : ($ticket['prioridad'] === 'Media' ? 'orange' : 'green');
                    ?>
                    <tr style="border-bottom: 1px solid #f5f5f5;">
                        <td style="padding: 12px;"><strong><?= htmlspecialchars($ticket['codigo']) ?></strong></td>
                        
                        <?php if ($userRole === 'scrum_master' || $userRole === 'seguridad'): ?>
                            <td style="padding: 12px;"><?= htmlspecialchars($ticket['solicitante_nombre'] ?? 'Desconocido') ?></td>
                        <?php endif; ?>
                        
                        <td style="padding: 12px;">
                            <strong><?= htmlspecialchars($ticket['servicio_id']) ?></strong><br>
                            <small style="color:#666;"><?= htmlspecialchars($serviciosList[$ticket['servicio_id']] ?? 'Servicio') ?></small>
                        </td>
                        <td style="padding: 12px; color: <?= $prioColor ?>; font-weight: bold;"><?= htmlspecialchars($ticket['prioridad']) ?></td>
                        <td style="padding: 12px;">
                            <span style="background: <?= $badgeColor ?>; color: <?= $ticket['estado'] === 'En Progreso' ? '#111' : '#fff' ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold;">
                                <?= htmlspecialchars($ticket['estado']) ?>
                            </span>
                        </td>
                        
                        <?php if ($userRole !== 'especialista_ti'): ?>
                            <td style="padding: 12px;">
                                <?= $ticket['asignado_nombre'] ? htmlspecialchars($ticket['asignado_nombre']) : '<span style="color:#999;">Sin asignar</span>' ?>
                            </td>
                        <?php endif; ?>

                        <td style="padding: 12px;"><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></td>
                        <td style="padding: 12px; display: flex; gap: 5px;">
                            <!-- Botón universal: Ver Detalles -->
                            <button onclick="openDetalleModal(<?= htmlspecialchars(json_encode($ticket), ENT_QUOTES, 'UTF-8') ?>)" class="btn btn-sm" style="background:#f8f9fa; color:#333; border:1px solid #ddd; padding:5px 10px; border-radius:4px; cursor:pointer;" title="Ver Detalle">👁️ Ver</button>

                            <!-- Acciones Scrum Master -->
                            <?php if ($userRole === 'scrum_master'): ?>
                                <?php if ($ticket['estado'] === 'Abierto' || $ticket['estado'] === 'En Progreso'): ?>
                                    <button onclick="openAssignModal(<?= $ticket['id'] ?>, '<?= $ticket['prioridad'] ?>', <?= $ticket['asignado_a'] ?? 0 ?>)" class="btn btn-sm" style="background:#007bff; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;" title="Derivar/Asignar">👤 Asignar</button>
                                <?php endif; ?>
                                <?php if ($ticket['estado'] === 'Resuelto'): ?>
                                    <button onclick="openCloseModal(<?= $ticket['id'] ?>)" class="btn btn-sm" style="background:#28a745; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;" title="Cerrar Ticket">✅ Cerrar</button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Acciones Especialista TI -->
                            <?php if ($userRole === 'especialista_ti'): ?>
                                <?php if (in_array($ticket['estado'], ['Asignado', 'En Progreso', 'Pendiente'])): ?>
                                    <button onclick="openStatusModal(<?= $ticket['id'] ?>, '<?= $ticket['estado'] ?>')" class="btn btn-sm" style="background:#ffc107; color:#111; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;" title="Cambiar Estado">🔄 Estado</button>
                                <?php endif; ?>
                                <?php if ($ticket['estado'] === 'En Progreso'): ?>
                                    <button onclick="openTechResolveModal(<?= $ticket['id'] ?>)" class="btn btn-sm" style="background:#17a2b8; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;" title="Registrar Acción">🔧 Resolver</button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Acciones Solicitante -->
                            <?php if ($userRole === 'vendedor' || $userRole === 'admin' || $userRole === 'supervisor'): ?>
                                <?php if ($ticket['estado'] === 'Resuelto'): ?>
                                    <button onclick="openCloseModal(<?= $ticket['id'] ?>)" class="btn btn-sm" style="background:#28a745; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;" title="Confirmar Resolución">✅ Confirmar Solución</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Detalle -->
<div id="detalleModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
    <div style="background:#fff; padding:2rem; border-radius:8px; width:500px; max-width:90%;">
        <h3>Detalle de Ticket <span id="det_codigo"></span></h3>
        <hr style="margin:10px 0; border:0; border-top:1px solid #eee;">
        <p><strong>Descripción original:</strong></p>
        <div id="det_descripcion" style="background:#f8f9fa; padding:10px; border-radius:4px; margin-bottom:10px; white-space: pre-wrap;"></div>
        
        <p><strong>Acción Técnica (TI):</strong></p>
        <div id="det_accion" style="background:#e9ecef; padding:10px; border-radius:4px; margin-bottom:10px; white-space: pre-wrap;"></div>

        <p><strong>Resolución Final:</strong></p>
        <div id="det_resolucion" style="background:#d4edda; padding:10px; border-radius:4px; margin-bottom:10px; white-space: pre-wrap;"></div>

        <div style="display:flex; justify-content:flex-end;">
            <button type="button" onclick="document.getElementById('detalleModal').style.display='none'" style="padding:8px 16px; border:none; border-radius:4px; cursor:pointer; background:#6c757d; color:#fff;">Cerrar Vista</button>
        </div>
    </div>
</div>

<?php if ($userRole === 'scrum_master'): ?>
<!-- Modal Asignar (Solo SM) -->
<div id="assignModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
    <div style="background:#fff; padding:2rem; border-radius:8px; width:400px; max-width:90%;">
        <h3>Derivar Ticket</h3>
        <form action="<?= BASE_URL ?>/ticket/assign" method="POST">
            <input type="hidden" name="ticket_id" id="assign_ticket_id">
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem;">Asignar a especialista:</label>
                <select name="asignado_a" id="assign_user" class="form-control" required style="width:100%; padding:8px;">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($tiUsers as $user): ?>
                        <option value="<?= $user->id ?>"><?= htmlspecialchars($user->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem;">Prioridad:</label>
                <select name="prioridad" id="assign_prio" class="form-control" required style="width:100%; padding:8px;">
                    <option value="Baja">Baja</option>
                    <option value="Media">Media</option>
                    <option value="Alta">Alta</option>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('assignModal').style.display='none'" style="padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Cancelar</button>
                <button type="submit" style="background:#007bff; color:#fff; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Asignar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($userRole === 'especialista_ti'): ?>
<!-- Modal Actualizar Estado (Solo TI) -->
<div id="statusModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
    <div style="background:#fff; padding:2rem; border-radius:8px; width:400px; max-width:90%;">
        <h3>Actualizar Estado</h3>
        <form action="<?= BASE_URL ?>/ticket/updateStatus" method="POST">
            <input type="hidden" name="ticket_id" id="status_ticket_id">
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem;">Nuevo Estado:</label>
                <select name="estado" id="status_select" class="form-control" required style="width:100%; padding:8px;">
                    <option value="En Progreso">En Progreso</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('statusModal').style.display='none'" style="padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Cancelar</button>
                <button type="submit" style="background:#ffc107; color:#111; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registrar Acción Técnica (Solo TI) -->
<div id="techResolveModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
    <div style="background:#fff; padding:2rem; border-radius:8px; width:450px; max-width:90%;">
        <h3>Registrar Acción Técnica</h3>
        <p style="font-size:0.9em; color:#666; margin-bottom:15px;">Describe los cambios en código, logs revisados o consultas SQL ejecutadas para resolver el problema.</p>
        <form action="<?= BASE_URL ?>/ticket/technical_resolve" method="POST">
            <input type="hidden" name="ticket_id" id="tech_ticket_id">
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem;">Acción Técnica Realizada:</label>
                <textarea name="accion_tecnica" rows="5" class="form-control" required style="width:100%; padding:8px;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('techResolveModal').style.display='none'" style="padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Cancelar</button>
                <button type="submit" style="background:#17a2b8; color:#fff; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Guardar y Marcar Resuelto</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Confirmar/Cerrar (Solicitante y SM) -->
<div id="closeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
    <div style="background:#fff; padding:2rem; border-radius:8px; width:400px; max-width:90%;">
        <h3>Cerrar Ticket</h3>
        <p style="font-size:0.9em; color:#666; margin-bottom:15px;">Al cerrar el ticket, estás confirmando que el problema fue solucionado satisfactoriamente.</p>
        <form action="<?= BASE_URL ?>/ticket/confirm_close" method="POST">
            <input type="hidden" name="ticket_id" id="close_ticket_id">
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem;">Notas de Confirmación / Resolución:</label>
                <textarea name="resolucion" rows="3" class="form-control" required style="width:100%; padding:8px;" placeholder="Ej: Confirmado, todo funciona correctamente."></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('closeModal').style.display='none'" style="padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Cancelar</button>
                <button type="submit" style="background:#28a745; color:#fff; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Cerrar Ticket</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDetalleModal(ticket) {
    document.getElementById('det_codigo').innerText = ticket.codigo;
    document.getElementById('det_descripcion').innerText = ticket.descripcion || 'Sin descripción';
    document.getElementById('det_accion').innerText = ticket.accion_tecnica || 'Pendiente';
    document.getElementById('det_resolucion').innerText = ticket.resolucion || 'Pendiente';
    document.getElementById('detalleModal').style.display = 'flex';
}

function openAssignModal(ticketId, prioridad, userId) {
    document.getElementById('assign_ticket_id').value = ticketId;
    document.getElementById('assign_prio').value = prioridad;
    if (userId) document.getElementById('assign_user').value = userId;
    document.getElementById('assignModal').style.display = 'flex';
}

function openTechResolveModal(ticketId) {
    document.getElementById('tech_ticket_id').value = ticketId;
    document.getElementById('techResolveModal').style.display = 'flex';
}

function openStatusModal(ticketId, currentState) {
    document.getElementById('status_ticket_id').value = ticketId;
    if (currentState === 'Asignado' || currentState === 'En Progreso' || currentState === 'Pendiente' || currentState === 'Cancelado') {
        let select = document.getElementById('status_select');
        for (let i=0; i<select.options.length; i++) {
            if (select.options[i].value === currentState) {
                select.selectedIndex = i;
                break;
            }
        }
    }
    document.getElementById('statusModal').style.display = 'flex';
}

function openCloseModal(ticketId) {
    document.getElementById('close_ticket_id').value = ticketId;
    document.getElementById('closeModal').style.display = 'flex';
}
</script>

<?php require_once APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
