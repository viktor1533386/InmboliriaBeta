<?php
// ============================================================
//  CONTROLLER: Admin – Dashboard
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/app/models/Propiedad.php';
require_once APP_ROOT . '/app/models/Vendedor.php';
require_once APP_ROOT . '/app/models/Mensaje.php';

class AdminController extends Controller {

    // GET /admin/dashboard
    public function dashboard(): void {
        Middleware::requireRole(['admin', 'supervisor', 'scrum_master', 'especialista_ti', 'seguridad']);

        $propiedad = new Propiedad();
        $vendedor  = new Vendedor();
        
        require_once APP_ROOT . '/app/models/Prospecto.php';
        require_once APP_ROOT . '/app/models/SolicitudCierre.php';
        $prospecto = new Prospecto();
        $solicitudModel = new SolicitudCierre();
        
        $prospectosEnGestion = $prospecto->count("estado NOT IN ('Nuevo', 'Cerrado_Ganado', 'Cerrado_Perdedor')");

        $this->render('admin/dashboard', [
            'titulo'          => 'Dashboard – Panel Admin',
            'prospectosNuevos'=> $prospecto->count("estado = 'Nuevo'"),
            'prospectosCierres'=> $prospecto->count("estado = 'Cerrado_Ganado'"),
            'totalActivas'    => $propiedad->count("estado = 'Disponible'"),
            'prospectosEnGestion' => $prospectosEnGestion,
            'ultimasProp'     => $propiedad->ultimas(5),
            'ultimosProspectos' => $prospecto->raw("
                SELECT p.*, pr.titulo as propiedad_titulo 
                FROM prospectos p 
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id 
                ORDER BY p.created_at DESC 
                LIMIT 5
            "),
            'solicitudesCierre' => $solicitudModel->findPendientes(),
        ]);
    }

    // GET /admin/backup
    public function backup(): void {
        Middleware::requireRole(['admin', 'supervisor']);
        
        $db = Database::getInstance();
        $tables = [];
        
        // Obtener tablas
        $stmt = $db->query("SHOW TABLES");
        $results = $stmt->fetchAll(PDO::FETCH_NUM);
        foreach ($results as $row) {
            $tables[] = $row[0];
        }

        $sqlScript = "-- ==========================================\n";
        $sqlScript .= "-- Backup de Base de Datos Hogar Ideal Perú\n";
        $sqlScript .= "-- Generado el: " . date('Y-m-d H:i:s') . "\n";
        $sqlScript .= "-- ==========================================\n\n";

        foreach ($tables as $table) {
            // Estructura
            $stmt = $db->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            
            $sqlScript .= "-- --------------------------------------------------------\n";
            $sqlScript .= "-- Estructura de la tabla `{$table}`\n";
            $sqlScript .= "-- --------------------------------------------------------\n\n";
            $sqlScript .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlScript .= $row[1] . ";\n\n";

            // Datos
            $stmt = $db->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sqlScript .= "-- Volcado de datos para la tabla `{$table}`\n\n";
                foreach ($rows as $rowData) {
                    $cols = array_keys($rowData);
                    $vals = array_values($rowData);
                    
                    $escapedVals = array_map(function($val) {
                        if ($val === null) return 'NULL';
                        return "'" . addslashes((string)$val) . "'";
                    }, $vals);

                    $sqlScript .= "INSERT INTO `{$table}` (`" . implode("`, `", $cols) . "`) VALUES (" . implode(", ", $escapedVals) . ");\n";
                }
                $sqlScript .= "\n\n";
            }
        }

        $filename = 'backup_hogarideal_' . date('Y_m_d_His') . '.sql';

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $sqlScript;
        exit;
    }

    // GET/POST /admin/restore
    public function restore(): void {
        Middleware::requireRole(['admin']);

        $error = '';
        if ($this->isPost()) {
            if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['backup_file']['tmp_name'];
                $fileName = $_FILES['backup_file']['name'];
                
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if ($ext !== 'sql') {
                    $error = 'El archivo debe tener la extensión .sql';
                } else {
                    $sqlContent = file_get_contents($fileTmp);
                    if (empty(trim($sqlContent))) {
                        $error = 'El archivo está vacío.';
                    } else {
                        try {
                            $db = Database::getInstance();
                            // Desactivar checks de claves foráneas para poder hacer DROP de tablas
                            $db->exec('SET FOREIGN_KEY_CHECKS=0;');
                            $db->exec($sqlContent);
                            $db->exec('SET FOREIGN_KEY_CHECKS=1;');
                            
                            $this->flash('success', 'Base de datos restaurada correctamente a partir del respaldo.');
                            $this->redirect('admin/dashboard');
                            return;
                        } catch (Exception $e) {
                            // Intentar reactivar checks
                            @$db->exec('SET FOREIGN_KEY_CHECKS=1;');
                            $error = 'Error crítico al ejecutar el script SQL: ' . $e->getMessage();
                        }
                    }
                }
            } else {
                $error = 'No se ha subido ningún archivo válido.';
            }
        }

        $this->render('admin/restore', [
            'titulo' => 'Restaurar Sistema (Restore)',
            'error'  => $error
        ]);
    }
    // GET /admin/prospectos
    public function prospectos(): void {
        Middleware::requireRole(['admin', 'supervisor']);
        
        require_once APP_ROOT . '/app/models/Prospecto.php';
        $prospectoModel = new Prospecto();
        
        // Obtener vendedores para el dropdown de asignación
        $vendedorModel = new Vendedor();
        $vendedores = $vendedorModel->findAll('nombre ASC');
        
        // Mostrar todos los prospectos en estado "Nuevo"
        $prospectos = $prospectoModel->findNuevos();
        
        $this->render('admin/prospectos_bandeja', [
            'titulo'     => 'Bandeja de Prospectos – Sin Asignar',
            'prospectos' => $prospectos,
            'vendedores' => $vendedores
        ]);
    }

    // POST /admin/asignar/{id}
    public function asignar(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor']);
        
        if ($this->isPost()) {
            $vendedor_id = (int)($_POST['vendedor_id'] ?? 0);
            
            require_once APP_ROOT . '/app/models/Prospecto.php';
            require_once APP_ROOT . '/app/models/ActividadProspecto.php';
            
            $prospectoModel = new Prospecto();
            $prospecto = $prospectoModel->findById((int)$id);
            
            if ($prospecto && $vendedor_id > 0) {
                // Actualizar estado del prospecto
                $prospectoModel->update((int)$id, [
                    'vendedor_id'      => $vendedor_id,
                    'estado'           => 'Asignado',
                    'asignado_por'     => $_SESSION['usuario_id'],
                    'fecha_asignacion' => date('Y-m-d H:i:s')
                ]);

                // Actualizar la propiedad vinculada al vendedor
                if (!empty($prospecto->propiedad_id)) {
                    $propModel = new Propiedad();
                    $propModel->update((int)$prospecto->propiedad_id, [
                        'vendedor_id' => $vendedor_id
                    ]);
                }
                
                // Obtener nombre del vendedor para el comentario
                $vendedorModel = new Vendedor();
                $vendedor = $vendedorModel->findById($vendedor_id);
                $nombreVendedor = $vendedor ? "{$vendedor->nombre} {$vendedor->apellido}" : "Desconocido";

                // Crear actividad
                $actividad = new ActividadProspecto();
                $actividad->insert([
                    'prospecto_id' => (int)$id,
                    'tipo'         => 'Asignacion',
                    'comentario'   => "Prospecto asignado a $nombreVendedor",
                    'nuevo_estado' => 'Asignado',
                    'creado_por'   => $_SESSION['usuario_id']
                ]);
                
                // Enviar email al vendedor
                if ($vendedor && !empty($vendedor->email)) {
                    $asunto = "Nuevo Prospecto Asignado: {$prospecto->nombre}";
                    $urlPanel = BASE_URL . "/vendedor/prospectos";
                    $cuerpo = "
                        <h2>¡Hola $nombreVendedor!</h2>
                        <p>Se te ha asignado un nuevo prospecto.</p>
                        <ul>
                            <li><strong>Nombre:</strong> {$prospecto->nombre}</li>
                            <li><strong>Email:</strong> {$prospecto->email}</li>
                            <li><strong>Teléfono:</strong> {$prospecto->telefono}</li>
                        </ul>
                        <p>Puedes revisar los detalles en tu panel: <a href='$urlPanel'>$urlPanel</a></p>
                    ";
                    require_once APP_ROOT . '/core/Mailer.php';
                    Mailer::send($vendedor->email, $asunto, $cuerpo);
                }

                $this->flash('success', "Prospecto asignado correctamente a $nombreVendedor.");
            } else {
                $this->flash('error', "No se pudo asignar el prospecto. Verifique los datos.");
            }
        }
        
        $this->redirect('admin/prospectos');
    }

    // POST /admin/aprobar_cierre/{id}
    public function aprobar_cierre(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor']);
        
        if ($this->isPost()) {
            require_once APP_ROOT . '/app/models/SolicitudCierre.php';
            require_once APP_ROOT . '/app/models/Prospecto.php';
            require_once APP_ROOT . '/app/models/ActividadProspecto.php';
            require_once APP_ROOT . '/app/models/Propiedad.php';
            require_once APP_ROOT . '/app/models/Notificacion.php';
            require_once APP_ROOT . '/core/Mailer.php';
            
            $solicitudModel = new SolicitudCierre();
            $prospectoModel = new Prospecto();
            $propiedadModel = new Propiedad();
            $actividadModel = new ActividadProspecto();
            $notifModel = new Notificacion();
            
            $solicitud = $solicitudModel->getDetails((int)$id);
            
            if ($solicitud && $solicitud->estado === 'Pendiente') {
                // 1. Marcar solicitud como aprobada
                $solicitudModel->update((int)$id, [
                    'estado' => 'Aprobado',
                    'supervisor_id' => $_SESSION['usuario_id']
                ]);
                
                // 2. Marcar prospecto como ganado
                $prospectoModel->update($solicitud->prospecto_id, [
                    'estado' => 'Cerrado_Ganado'
                ]);
                
                // Actividad para el ganador
                $actividadModel->insert([
                    'prospecto_id' => $solicitud->prospecto_id,
                    'tipo' => 'Cierre Aprobado',
                    'comentario' => "SISTEMA: El supervisor ha aprobado el cierre de esta operación.",
                    'nuevo_estado' => 'Cerrado_Ganado',
                    'creado_por' => $_SESSION['usuario_id']
                ]);

                // 3. Actualizar la Propiedad
                $nuevoEstadoPropiedad = ($solicitud->tipo_cierre === 'Venta') ? 'Vendida' : 'Alquilada';
                $propiedadModel->update($solicitud->propiedad_id, [
                    'estado' => $nuevoEstadoPropiedad,
                    'activo' => 0 // Retirar del catálogo
                ]);

                // 4. MOTOR DE NOTIFICACIONES: Buscar otros prospectos activos para esta propiedad
                $sqlOtros = "SELECT p.*, v.usuario_id as vendedor_usuario_id, v.email as vendedor_email, v.nombre as vendedor_nombre 
                             FROM prospectos p 
                             JOIN vendedores v ON p.vendedor_id = v.id 
                             WHERE p.propiedad_id = ? AND p.id != ? AND p.estado NOT IN ('Cerrado_Ganado', 'Cerrado_Perdedor', 'Pendiente_Cierre')";
                $otrosProspectos = $prospectoModel->raw($sqlOtros, [$solicitud->propiedad_id, $solicitud->prospecto_id]);

                foreach ($otrosProspectos as $otro) {
                    // a) Actividad del prospecto (para que el vendedor la vea)
                    $actividadModel->insert([
                        'prospecto_id' => $otro->id,
                        'tipo' => 'Notificación Sistema',
                        'comentario' => "SISTEMA: La propiedad vinculada ($solicitud->propiedad_titulo) ha sido marcada como $nuevoEstadoPropiedad. Por favor, reorientar al prospecto hacia alternativas disponibles.",
                        'creado_por' => $_SESSION['usuario_id'] // o null si el sistema lo permite
                    ]);

                    // b) Notificación en el Dashboard
                    if ($otro->vendedor_usuario_id) {
                        $notifModel->insert([
                            'usuario_id' => $otro->vendedor_usuario_id,
                            'titulo' => "Propiedad no disponible",
                            'mensaje' => "La propiedad '{$solicitud->propiedad_titulo}' por la que negociabas con {$otro->nombre} ha sido {$nuevoEstadoPropiedad}.",
                            'enlace' => BASE_URL . "/vendedor/detalle/" . $otro->id
                        ]);
                    }

                    // c) Correo electrónico al vendedor
                    if (!empty($otro->vendedor_email)) {
                        $asunto = "Atención: Propiedad no disponible - {$solicitud->propiedad_titulo}";
                        $cuerpo = "
                            <h2>Hola {$otro->vendedor_nombre},</h2>
                            <p>Te informamos que la propiedad <strong>{$solicitud->propiedad_titulo}</strong> ha sido {$nuevoEstadoPropiedad}.</p>
                            <p>El prospecto <strong>{$otro->nombre}</strong> estaba interesado en esta propiedad. Te sugerimos reorientarlo hacia otras alternativas de nuestro catálogo.</p>
                            <p>Ingresa al CRM para gestionar a tu cliente.</p>
                        ";
                        Mailer::send($otro->vendedor_email, $asunto, $cuerpo);
                    }
                }

                $this->flash('success', 'Solicitud aprobada. La propiedad fue retirada y se notificó a los demás vendedores involucrados.');
            }
        }
        $this->redirect('admin/dashboard');
    }

    // POST /admin/rechazar_cierre/{id}
    public function rechazar_cierre(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor']);
        
        if ($this->isPost()) {
            require_once APP_ROOT . '/app/models/SolicitudCierre.php';
            require_once APP_ROOT . '/app/models/Prospecto.php';
            require_once APP_ROOT . '/app/models/ActividadProspecto.php';
            
            $solicitudModel = new SolicitudCierre();
            $prospectoModel = new Prospecto();
            $actividadModel = new ActividadProspecto();
            
            $solicitud = $solicitudModel->getDetails((int)$id);
            
            if ($solicitud && $solicitud->estado === 'Pendiente') {
                $solicitudModel->update((int)$id, [
                    'estado' => 'Rechazado',
                    'supervisor_id' => $_SESSION['usuario_id']
                ]);
                
                $prospectoModel->update($solicitud->prospecto_id, [
                    'estado' => 'En_Negociacion'
                ]);
                
                $actividadModel->insert([
                    'prospecto_id' => $solicitud->prospecto_id,
                    'tipo' => 'Cierre Rechazado',
                    'comentario' => "SISTEMA: El supervisor ha rechazado la solicitud de cierre.",
                    'nuevo_estado' => 'En_Negociacion',
                    'creado_por' => $_SESSION['usuario_id']
                ]);

                $this->flash('success', 'Solicitud rechazada. El prospecto volvió a estado de negociación.');
            }
        }
        $this->redirect('admin/dashboard');
    }

    // GET /admin/leer_notificacion/{id}
    public function leer_notificacion(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor', 'vendedor', 'scrum_master', 'especialista_ti', 'seguridad']);
        require_once APP_ROOT . '/app/models/Notificacion.php';
        
        $notifModel = new Notificacion();
        $notif = $notifModel->findById((int)$id);
        
        if ($notif && $notif->usuario_id == $_SESSION['usuario_id']) {
            $notifModel->marcarLeida((int)$id, $_SESSION['usuario_id']);
            if (!empty($notif->enlace)) {
                header("Location: " . $notif->enlace);
                exit;
            }
        }
        $rol = $_SESSION['usuario_rol'] ?? '';
        if (in_array($rol, ['scrum_master', 'especialista_ti', 'seguridad'])) {
            $this->redirect('ticket/index');
        } else {
            $this->redirect('admin/dashboard');
        }
    }

    // GET /admin/leer_todas_notificaciones
    public function leer_todas_notificaciones(): void {
        Middleware::requireRole(['admin', 'supervisor', 'vendedor', 'scrum_master', 'especialista_ti', 'seguridad']);
        require_once APP_ROOT . '/app/models/Notificacion.php';
        
        $notifModel = new Notificacion();
        $notificaciones = $notifModel->findNoLeidas($_SESSION['usuario_id']);
        
        foreach ($notificaciones as $n) {
            $notifModel->marcarLeida($n->id, $_SESSION['usuario_id']);
        }
        
        $rol = $_SESSION['usuario_rol'] ?? '';
        if (in_array($rol, ['scrum_master', 'especialista_ti', 'seguridad'])) {
            $this->redirect('ticket/index');
        } else {
            $this->redirect('admin/dashboard');
        }
    }
}
