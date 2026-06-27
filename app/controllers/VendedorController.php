<?php
// ============================================================
//  CONTROLLER: Vendedor – CRUD
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/core/Mailer.php';
require_once APP_ROOT . '/app/models/Vendedor.php';
require_once APP_ROOT . '/app/models/Usuario.php';

class VendedorController extends Controller {

    private Vendedor $vendedor;
    private Usuario $usuario;

    public function __construct() {
        $this->vendedor = new Vendedor();
        $this->usuario = new Usuario();
    }

    // GET /vendedor/dashboard
    public function dashboard(): void {
        Middleware::requireRole(['vendedor']);
        
        require_once APP_ROOT . '/app/models/Prospecto.php';
        require_once APP_ROOT . '/app/models/Propiedad.php';
        
        $prospectoModel = new Prospecto();
        $propiedadModel = new Propiedad();
        
        $usuario_id = $_SESSION['usuario_id'];
        $vendedorData = clone $this->vendedor;
        $vendedorObj = $vendedorData->findOneWhere('usuario_id', $usuario_id);
        
        if (!$vendedorObj) {
            $this->flash('error', 'Su cuenta no tiene un perfil de vendedor asociado.');
            $this->redirect('auth/login');
            return;
        }

        $vendedor_id = $vendedorObj->id;
        
        // Métricas
        $misPropiedades = $propiedadModel->count("vendedor_id = $vendedor_id AND activo = 1");
        $prospectosEnGestion = $prospectoModel->count("vendedor_id = $vendedor_id AND estado NOT IN ('Nuevo', 'Cerrado_Ganado', 'Cerrado_Perdedor')");
        $prospectosGanados = $prospectoModel->count("vendedor_id = $vendedor_id AND estado = 'Cerrado_Ganado'");
        
        // Asignaciones recientes
        $asignacionesRecientes = $prospectoModel->findAll("created_at DESC", "vendedor_id = $vendedor_id AND estado = 'Asignado'");
        
        // Últimos prospectos
        $ultimosProspectos = $prospectoModel->raw("
            SELECT p.*, pr.titulo as propiedad_titulo 
            FROM prospectos p 
            LEFT JOIN propiedades pr ON p.propiedad_id = pr.id 
            WHERE p.vendedor_id = ? 
            ORDER BY p.created_at DESC 
            LIMIT 5
        ", [$vendedor_id]);

        $this->render('vendedor/dashboard', [
            'titulo' => 'Dashboard – Agente Inmobiliario',
            'misPropiedades' => $misPropiedades,
            'prospectosEnGestion' => $prospectosEnGestion,
            'prospectosGanados' => $prospectosGanados,
            'asignacionesRecientes' => count($asignacionesRecientes),
            'ultimosProspectos' => $ultimosProspectos
        ]);
    }

    // GET /vendedor
    public function index(): void {
        Middleware::requireRole(['admin', 'supervisor', 'scrum_master', 'especialista_ti']);
        $vendedores = $this->vendedor->findAll('nombre ASC');
        $this->render('vendedores/index', [
            'titulo'     => 'Gestión de Vendedores',
            'vendedores' => $vendedores,
        ]);
    }

    // GET/POST /vendedor/crear
    public function crear(): void {
        Middleware::requireRole(['admin', 'supervisor', 'scrum_master', 'especialista_ti']);
        $errores = [];

        if ($this->isPost()) {
            $datos = [
                'nombre'       => $this->sanitize($_POST['nombre']   ?? ''),
                'apellido'     => $this->sanitize($_POST['apellido'] ?? ''),
                'email'        => $this->sanitize($_POST['email']    ?? ''),
                'telefono'     => preg_replace('/[^0-9+]/', '', $_POST['telefono'] ?? ''),
                'dni'          => preg_replace('/[^0-9]/', '', $_POST['dni'] ?? ''),
            ];

            if (empty($datos['nombre']))   $errores[] = 'El nombre es obligatorio.';
            if (empty($datos['apellido'])) $errores[] = 'El apellido es obligatorio.';
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido.';
            }
            if (strlen($datos['dni']) !== 8) {
                $errores[] = 'El DNI debe tener exactamente 8 dígitos.';
            }
            
            // Auto-formatear teléfono si tiene 9 dígitos sin prefijo
            $telefonoPuro = preg_replace('/[^0-9]/', '', $datos['telefono']);
            if (strlen($telefonoPuro) === 9) {
                $datos['telefono'] = '+51 ' . $telefonoPuro;
            } elseif (strlen($telefonoPuro) !== 11) { // 11 considerando 51999...
                $errores[] = 'El teléfono debe tener 9 dígitos.';
            }

            if (empty($errores)) {
                // Generar contraseña aleatoria
                $randomPass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$'), 0, 8);
                
                // 1. Crear el Usuario de acceso para el vendedor
                try {
                    $usuarioId = $this->usuario->insert([
                        'nombre'   => $datos['nombre'] . ' ' . $datos['apellido'],
                        'email'    => $datos['email'],
                        'password' => password_hash($randomPass, PASSWORD_BCRYPT),
                        'rol'      => 'vendedor',
                        'estado'   => 1,
                        'password_reset_required' => 1
                    ]);

                    // 2. Crear el Vendedor vinculándolo al usuario
                    $datos['usuario_id'] = (int)$usuarioId;
                    $this->vendedor->insert($datos);
                } catch (Exception $e) {
                    $errores[] = 'El correo electrónico ya está en uso por otro usuario.';
                }
                
                if (empty($errores)) {
                
                // Enviar correo real usando PHPMailer
                $asunto = "Bienvenido a Hogar Ideal Perú - Tus Credenciales";
                $cuerpo = "
                    <h2>¡Hola {$datos['nombre']}!</h2>
                    <p>Has sido registrado como agente inmobiliario en <strong>Hogar Ideal Perú</strong>.</p>
                    <p>Tus credenciales temporales de acceso son:</p>
                    <ul>
                        <li><strong>Email:</strong> {$datos['email']}</li>
                        <li><strong>Contraseña:</strong> $randomPass</li>
                    </ul>
                    <p>Por seguridad, el sistema te pedirá cambiar esta contraseña la primera vez que ingreses al panel.</p>
                    <p>Puedes iniciar sesión aquí: <a href='" . BASE_URL . "/auth/login'>" . BASE_URL . "/auth/login</a></p>
                ";
                
                $enviado = Mailer::send($datos['email'], $asunto, $cuerpo);
                
                if ($enviado) {
                    $this->flash('success', "Vendedor registrado. Se envió el correo con credenciales a {$datos['email']}.");
                } else {
                    $this->flash('success', "Vendedor registrado, pero ocurrió un error al enviar el correo. Contraseña temporal: $randomPass");
                }
                
                $this->redirect('vendedor');
                }
            }
        }

        $this->render('vendedores/crear', [
            'titulo'  => 'Nuevo Vendedor',
            'errores' => $errores,
        ]);
    }

    // GET/POST /vendedor/editar/{id}
    public function editar(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor', 'scrum_master', 'especialista_ti']);
        $vendedor = $this->vendedor->findById((int)$id);
        if (!$vendedor) $this->redirect('vendedor');

        $errores = [];

        if ($this->isPost()) {
            $datos = [
                'nombre'       => $this->sanitize($_POST['nombre']   ?? ''),
                'apellido'     => $this->sanitize($_POST['apellido'] ?? ''),
                'email'        => $this->sanitize($_POST['email']    ?? ''),
                'telefono'     => preg_replace('/[^0-9+]/', '', $_POST['telefono'] ?? ''),
                'dni'          => preg_replace('/[^0-9]/', '', $_POST['dni'] ?? ''),
            ];

            if (empty($datos['nombre']))   $errores[] = 'El nombre es obligatorio.';
            if (empty($datos['apellido'])) $errores[] = 'El apellido es obligatorio.';
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido.';
            }
            if (strlen($datos['dni']) !== 8) {
                $errores[] = 'El DNI debe tener exactamente 8 dígitos.';
            }

            // Auto-formatear teléfono si tiene 9 dígitos sin prefijo
            $telefonoPuro = preg_replace('/[^0-9]/', '', $datos['telefono']);
            if (strlen($telefonoPuro) === 9) {
                $datos['telefono'] = '+51 ' . $telefonoPuro;
            } elseif (strlen($telefonoPuro) !== 11) { // 11 considerando 51999...
                $errores[] = 'El teléfono debe tener 9 dígitos.';
            }

            if (empty($errores)) {
                $this->vendedor->update((int)$id, $datos);
                $this->flash('success', 'Vendedor actualizado correctamente.');
                $this->redirect('vendedor');
            }
        }

        $this->render('vendedores/editar', [
            'titulo'   => 'Editar Vendedor',
            'vendedor' => $vendedor,
            'errores'  => $errores,
        ]);
    }

    // GET /vendedor/eliminar/{id}
    public function eliminar(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor', 'scrum_master', 'especialista_ti']);
        $vendedor = $this->vendedor->findById((int)$id);
        if ($vendedor && $vendedor->usuario_id) {
            $this->usuario->delete((int)$vendedor->usuario_id);
        }
        $this->vendedor->delete((int)$id);
        $this->flash('success', 'Vendedor eliminado.');
        $this->redirect('vendedor');
    }

    // GET /vendedor/reset/{id}
    public function reset(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor', 'scrum_master', 'especialista_ti']);
        $vendedor = $this->vendedor->findById((int)$id);
        if (!$vendedor || !$vendedor->usuario_id) {
            $this->redirect('vendedor');
            return;
        }

        $passwordPlano = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $this->usuario->update((int)$vendedor->usuario_id, [
            'password' => password_hash($passwordPlano, PASSWORD_BCRYPT),
            'password_reset_required' => 1,
        ]);

        // Send email to the vendedor
        require_once APP_ROOT . '/core/Mailer.php';
        $asunto = "Restablecimiento de Contraseña - Hogar Ideal Perú";
        $loginUrl = BASE_URL . "/auth/login";
        $cuerpo = "
            <h2>¡Hola {$vendedor->nombre}!</h2>
            <p>Tu supervisor ha restablecido tu contraseña de acceso al sistema.</p>
            <p>Tu nueva contraseña temporal es: <strong>{$passwordPlano}</strong></p>
            <p>Por motivos de seguridad, el sistema te pedirá cambiar esta contraseña inmediatamente después de iniciar sesión.</p>
            <p>Puedes acceder aquí: <a href='{$loginUrl}'>{$loginUrl}</a></p>
        ";
        Mailer::send($vendedor->email, $asunto, $cuerpo);

        $this->flash('success', "Se generó la contraseña temporal: <strong>{$passwordPlano}</strong> para el usuario {$vendedor->email} y se envió por correo. Debe cambiarla al iniciar sesión.");
        $this->redirect('vendedor');
    }
    // GET /vendedor/prospectos
    public function prospectos(): void {
        Middleware::requireRole(['vendedor', 'supervisor', 'admin']);
        
        require_once APP_ROOT . '/app/models/Prospecto.php';
        $prospectoModel = new Prospecto();
        
        // Obtener ID del vendedor vinculado al usuario logueado
        // Ojo: $_SESSION['usuario_id'] es el usuario. Hay que buscar su vendedor_id.
        $usuario_id = $_SESSION['usuario_id'];
        $vendedor = clone $this->vendedor;
        $vendedorData = $vendedor->findOneWhere('usuario_id', $usuario_id);
        
        if (!$vendedorData && $_SESSION['usuario_rol'] === 'vendedor') {
            $this->flash('error', 'Su cuenta no tiene un perfil de vendedor asociado.');
            $this->redirect('auth/login');
            return;
        }

        $vendedor_id = $vendedorData ? $vendedorData->id : 0;
        
        $sql = "SELECT p.*, pr.titulo as propiedad_titulo, pr.precio as propiedad_precio, pr.direccion as propiedad_direccion 
                FROM prospectos p 
                LEFT JOIN propiedades pr ON p.propiedad_id = pr.id 
                WHERE p.estado != 'Nuevo'";
        
        if ($_SESSION['usuario_rol'] === 'vendedor') {
            $sql .= " AND p.vendedor_id = " . (int)$vendedor_id;
        }
        $sql .= " ORDER BY p.created_at DESC";
        
        $prospectos = $prospectoModel->raw($sql);

        $this->render('vendedor/prospectos', [
            'titulo'     => 'Mis Prospectos',
            'prospectos' => $prospectos,
            'rol'        => $_SESSION['usuario_rol']
        ]);
    }

    // GET /vendedor/detalle/{id}
    public function detalle(string $id = '0'): void {
        Middleware::requireRole(['vendedor', 'supervisor', 'admin']);
        
        require_once APP_ROOT . '/app/models/Prospecto.php';
        require_once APP_ROOT . '/app/models/ActividadProspecto.php';
        
        $prospectoModel = new Prospecto();
        $prospecto = $prospectoModel->findById((int)$id);
        
        if (!$prospecto) {
            $this->redirect('vendedor/prospectos');
        }

        $actividadModel = new ActividadProspecto();
        $actividades = $actividadModel->findByProspecto((int)$id);
        
        $propiedad = null;
        if (!empty($prospecto->propiedad_id)) {
            require_once APP_ROOT . '/app/models/Propiedad.php';
            $propiedadModel = new Propiedad();
            $propiedad = $propiedadModel->findById((int)$prospecto->propiedad_id);
        }
        
        $estadosPermitidos = ['Asignado', 'Contactado', 'Visita_Agendada', 'En_Negociacion', 'Cerrado_Perdedor', 'Post_Venta'];

        $solicitudPendiente = null;
        if ($prospecto->estado === 'Pendiente_Cierre') {
            require_once APP_ROOT . '/app/models/SolicitudCierre.php';
            $solicitudModel = new SolicitudCierre();
            $solicitudes = $solicitudModel->raw("SELECT * FROM solicitudes_cierre WHERE prospecto_id = ? AND estado = 'Pendiente' LIMIT 1", [(int)$id]);
            if (!empty($solicitudes)) {
                $solicitudPendiente = $solicitudes[0];
            }
        }

        $this->render('vendedor/prospecto_detalle', [
            'titulo'             => 'Detalle del Prospecto',
            'prospecto'          => $prospecto,
            'actividades'        => $actividades,
            'propiedad'          => $propiedad,
            'estados'            => $estadosPermitidos,
            'rol'                => $_SESSION['usuario_rol'],
            'solicitudPendiente' => $solicitudPendiente
        ]);
    }

    // POST /vendedor/reabrir_prospecto/{id}
    public function reabrir_prospecto(string $id = '0'): void {
        Middleware::requireRole(['supervisor', 'admin']);
        if ($this->isPost()) {
            $estado_forzado = $this->sanitize($_POST['estado_forzado'] ?? '');
            
            require_once APP_ROOT . '/app/models/Prospecto.php';
            $prospectoModel = new Prospecto();
            $prospecto = $prospectoModel->findById((int)$id);
            
            if ($prospecto && !empty($estado_forzado)) {
                $prospectoModel->update((int)$id, ['estado' => $estado_forzado]);
                
                // Add an activity note
                require_once APP_ROOT . '/app/models/ActividadProspecto.php';
                $actividad = new ActividadProspecto();
                $actividad->insert([
                    'prospecto_id' => (int)$id,
                    'tipo'         => 'Reunion',
                    'comentario'   => 'Supervisión: Estado forzado a ' . $estado_forzado,
                    'nuevo_estado' => $estado_forzado,
                    'creado_por'   => $_SESSION['usuario_id']
                ]);
                
                $this->flash('success', 'El estado del prospecto ha sido forzado a ' . $estado_forzado);
            }
        }
        $this->redirect('vendedor/detalle/' . $id);
    }

    // POST /vendedor/actividades/{id}
    public function actividades(string $id = '0'): void {
        Middleware::requireRole(['vendedor']);
        
        if ($this->isPost()) {
            $tipo         = $this->sanitize($_POST['tipo'] ?? '');
            $comentario   = $this->sanitize($_POST['comentario'] ?? '');
            $nuevo_estado = $this->sanitize($_POST['nuevo_estado'] ?? '');
            
            require_once APP_ROOT . '/app/models/Prospecto.php';
            require_once APP_ROOT . '/app/models/ActividadProspecto.php';
            
            $prospectoModel = new Prospecto();
            $prospecto = $prospectoModel->findById((int)$id);
            
            if ($prospecto) {
                // Validación para evitar actividades si está cerrado
                if (in_array($prospecto->estado, ['Cerrado_Ganado', 'Cerrado_Perdedor', 'Pendiente_Cierre'])) {
                    $this->flash('error', 'No se pueden registrar actividades en un prospecto cerrado o pendiente de cierre.');
                    $this->redirect('vendedor/detalle/' . $id);
                    return;
                }
                
                // Registrar actividad
                $actividad = new ActividadProspecto();
                $actividad->insert([
                    'prospecto_id' => (int)$id,
                    'tipo'         => $tipo,
                    'comentario'   => $comentario,
                    'nuevo_estado' => !empty($nuevo_estado) ? $nuevo_estado : $prospecto->estado,
                    'creado_por'   => $_SESSION['usuario_id']
                ]);
                
                // Actualizar estado si se seleccionó uno diferente
                if (!empty($nuevo_estado) && $nuevo_estado !== $prospecto->estado) {
                    $prospectoModel->update((int)$id, ['estado' => $nuevo_estado]);
                    
                    // Notificar al visitante del cambio de estado
                    $vendedorClon = clone $this->vendedor;
                    $vData = $vendedorClon->findById((int)$prospecto->vendedor_id);
                    $vendedorNombre = $vData ? "{$vData->nombre} {$vData->apellido}" : "tu agente";
                    
                    $asunto = "Actualización de tu solicitud";
                    $cuerpo = match($nuevo_estado) {
                        'Contactado' => "El vendedor $vendedorNombre ha intentado contactarte o ha registrado una comunicación inicial. Pronto recibirás más detalles.",
                        'Visita_Agendada' => "Se ha programado una visita o reunión contigo. El vendedor $vendedorNombre se pondrá en contacto para confirmar los detalles.",
                        'En_Negociacion' => "¡Excelentes noticias! Tu solicitud ha avanzado a la fase de negociación con $vendedorNombre.",
                        'Cerrado_Ganado' => "¡Felicidades! Se ha cerrado con éxito el proceso con $vendedorNombre. Gracias por confiar en Hogar Ideal Perú.",
                        'Cerrado_Perdedor' => "Lamentamos que esta vez no hayamos podido concretar la operación. Si cambias de opinión, estaremos aquí para ayudarte.",
                        default => ''
                    };
                    
                    if (!empty($cuerpo)) {
                        $urlSeguimiento = BASE_URL . "/seguimiento";
                        
                        $htmlCorreo = "
                            <h2>¡Hola {$prospecto->nombre}!</h2>
                            <p>$cuerpo</p>
                            <p>Puedes hacer seguimiento con tu código <strong>{$prospecto->codigo}</strong> en: <a href='$urlSeguimiento'>$urlSeguimiento</a></p>
                        ";
                        Mailer::send($prospecto->email, $asunto, $htmlCorreo);
                    }
                }
                
                $this->flash('success', 'Actividad registrada correctamente.');
            }
        }
        $this->redirect('vendedor/detalle/' . $id);
    }

    // POST /vendedor/solicitar_cierre/{id}
    public function solicitar_cierre(string $id = '0'): void {
        Middleware::requireRole(['vendedor']);
        
        if ($this->isPost()) {
            require_once APP_ROOT . '/app/models/Prospecto.php';
            require_once APP_ROOT . '/app/models/SolicitudCierre.php';
            require_once APP_ROOT . '/app/models/ActividadProspecto.php';
            
            $prospectoModel = new Prospecto();
            $prospecto = $prospectoModel->findById((int)$id);
            
            $vendedorData = $this->vendedor->findOneWhere('usuario_id', $_SESSION['usuario_id']);
            $vendedor_id = $vendedorData ? $vendedorData->id : 0;

            if ($prospecto && $prospecto->vendedor_id == $vendedor_id) {
                $tipo_cierre = $this->sanitize($_POST['tipo_cierre'] ?? 'Venta');
                $monto_final = (float)($_POST['monto_final'] ?? 0);
                $comentarios = $this->sanitize($_POST['comentarios'] ?? '');

                $solicitudModel = new SolicitudCierre();
                $solicitudModel->insert([
                    'prospecto_id' => $prospecto->id,
                    'propiedad_id' => $prospecto->propiedad_id,
                    'vendedor_id' => $vendedor_id,
                    'tipo_cierre' => $tipo_cierre,
                    'monto_final' => $monto_final,
                    'comentarios_vendedor' => $comentarios,
                    'estado' => 'Pendiente'
                ]);

                // Actualizar estado del prospecto
                $prospectoModel->update($prospecto->id, ['estado' => 'Pendiente_Cierre']);

                // Registrar actividad
                $actividad = new ActividadProspecto();
                $actividad->insert([
                    'prospecto_id' => $prospecto->id,
                    'tipo' => 'Solicitud de Cierre',
                    'comentario' => "Se ha solicitado la aprobación del supervisor para el cierre de la operación ($tipo_cierre) por S/ " . number_format($monto_final, 2) . ". Comentarios: $comentarios",
                    'nuevo_estado' => 'Pendiente_Cierre',
                    'creado_por' => $_SESSION['usuario_id']
                ]);

                // Notify supervisors
                require_once APP_ROOT . '/app/models/Usuario.php';
                $usuarioModel = new Usuario();
                $supervisores = $usuarioModel->findWhere('rol', 'supervisor');
                if (!empty($supervisores)) {
                    require_once APP_ROOT . '/core/Mailer.php';
                    $asuntoSupervisor = "Nueva solicitud de cierre de venta: {$prospecto->nombre}";
                    $cuerpoSupervisor = "
                        <h2>Solicitud de Cierre de Operación</h2>
                        <p>El vendedor ha solicitado la validación y cierre de una venta/alquiler.</p>
                        <ul>
                            <li><strong>Prospecto:</strong> {$prospecto->nombre}</li>
                            <li><strong>Tipo de Cierre:</strong> {$tipo_cierre}</li>
                            <li><strong>Monto Final:</strong> S/ " . number_format($monto_final, 2) . "</li>
                            <li><strong>Comentarios del Vendedor:</strong> {$comentarios}</li>
                        </ul>
                        <p>Por favor, revisa la bandeja de cierres en el panel de administración para validar y actualizar el estado de la propiedad a Vendida.</p>
                    ";
                    foreach ($supervisores as $sup) {
                        if (!empty($sup->email)) {
                            Mailer::send($sup->email, $asuntoSupervisor, $cuerpoSupervisor);
                        }
                    }
                }

                $this->flash('success', 'Solicitud de cierre enviada al supervisor correctamente. Se ha notificado al supervisor para su validación.');
            } else {
                $this->flash('error', 'No tienes permiso para realizar esta acción.');
            }
        }
        $this->redirect('vendedor/detalle/' . $id);
    }
}
