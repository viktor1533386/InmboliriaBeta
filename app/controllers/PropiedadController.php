<?php
// ============================================================
//  CONTROLLER: Propiedad – CRUD completo
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/app/models/Propiedad.php';
require_once APP_ROOT . '/app/models/Vendedor.php';
require_once APP_ROOT . '/app/models/Zona.php';
require_once APP_ROOT . '/app/models/Usuario.php';
require_once APP_ROOT . '/app/models/Notificacion.php';
require_once APP_ROOT . '/core/Mailer.php';

class PropiedadController extends Controller {

    private Propiedad $propiedad;
    private Vendedor  $vendedor;
    private Zona      $zona;

    public function __construct() {
        $this->propiedad = new Propiedad();
        $this->vendedor  = new Vendedor();
        $this->zona      = new Zona();
    }

    // ── PÚBLICO ──────────────────────────────────────────────

    // GET /propiedad  → catálogo público
    public function index(): void {
        $tipo        = $_GET['tipo'] ?? '';
        $propiedades = $tipo
            ? $this->propiedad->porTipo($tipo)
            : $this->propiedad->todasActivas();

        $this->render('propiedades/index', [
            'titulo'      => 'Propiedades – ' . APP_NAME,
            'propiedades' => $propiedades,
            'tipoActivo'  => $tipo,
        ]);
    }

    // GET /propiedad/detalle/{id}
    public function detalle(string $id = '0'): void {
        $propiedad = $this->propiedad->detalleConVendedor((int)$id);
        if (!$propiedad) {
            $this->redirect('propiedad');
        }
        $this->render('propiedades/detalle', [
            'titulo'    => $propiedad->titulo . ' – ' . APP_NAME,
            'propiedad' => $propiedad,
        ]);
    }

    // ── ADMIN (protegidas con Middleware) ─────────────────────

    // GET /propiedad/admin  → lista admin
    public function admin(): void {
        Middleware::requireRole(['admin', 'supervisor', 'vendedor', 'scrum_master', 'especialista_ti']);
        
        if (($_SESSION['usuario_rol'] ?? '') === 'vendedor') {
            // Un vendedor solo ve sus propiedades
            $vData = $this->vendedor->findOneWhere('usuario_id', $_SESSION['usuario_id']);
            $vendedor_id = $vData ? $vData->id : 0;
            $propiedades = $this->propiedad->todasConVendedor($vendedor_id);
        } else {
            $propiedades = $this->propiedad->todasConVendedor();
            // Ordenar por zona
            usort($propiedades, function($a, $b) {
                $zonaA = $a->zona_nombre ?? 'ZZZ_Sin Zona';
                $zonaB = $b->zona_nombre ?? 'ZZZ_Sin Zona';
                if ($zonaA === $zonaB) {
                    return $b->id <=> $a->id; // ID desc if same zone
                }
                return strcmp($zonaA, $zonaB);
            });
        }
        
        $this->render('propiedades/admin_index', [
            'titulo'      => 'Gestión de Propiedades',
            'propiedades' => $propiedades,
        ]);
    }

    // GET/POST /propiedad/crear
    public function crear(): void {
        Middleware::requireRole(['admin', 'supervisor', 'vendedor', 'scrum_master', 'especialista_ti']);
        $errores = [];

        if ($this->isPost()) {
            $datos = $this->recogerDatos();
            $errores = $this->validar($datos);

            if (empty($errores)) {
                // Subir imagen
                if (!empty($_FILES['imagen']['name'])) {
                    $nombreImg = $this->propiedad->subirImagen($_FILES['imagen']);
                    if ($nombreImg) {
                        $datos['imagen'] = $nombreImg;
                    } else {
                        $errores[] = 'La imagen no es válida. Solo JPG, PNG o WEBP hasta 5MB.';
                    }
                }

                if (empty($errores)) {
                    // Si es vendedor, forzar que sea el dueño de la propiedad y quede pendiente
                    if (($_SESSION['usuario_rol'] ?? '') === 'vendedor') {
                        $vData = $this->vendedor->findOneWhere('usuario_id', $_SESSION['usuario_id']);
                        $datos['vendedor_id'] = $vData ? $vData->id : 0;
                        $datos['estado_aprobacion'] = 'Pendiente';
                    } else {
                        $datos['estado_aprobacion'] = 'Aprobado';
                    }

                    $this->propiedad->insert($datos);
                    
                    if (($_SESSION['usuario_rol'] ?? '') === 'vendedor') {
                        $this->notificarSupervisores($datos['titulo'], 'creada');
                        $this->flash('success', 'Propiedad creada correctamente. Pendiente de aprobación por un supervisor.');
                    } else {
                        $this->flash('success', 'Propiedad creada correctamente.');
                    }
                    
                    $this->redirect('propiedad/admin');
                }
            }
        }

        $zonas = $this->zona->findAll();
        $this->render('propiedades/crear', [
            'titulo'  => 'Nueva Propiedad',
            'zonas'   => $zonas,
            'errores' => $errores,
        ]);
    }

    // GET/POST /propiedad/editar/{id}
    public function editar(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor', 'vendedor', 'scrum_master', 'especialista_ti']);
        $propiedad = $this->propiedad->findById((int)$id);
        if (!$propiedad) $this->redirect('propiedad/admin');

        if (($_SESSION['usuario_rol'] ?? '') === 'vendedor' && $propiedad->vendedor_id != $_SESSION['usuario_id']) {
            $this->flash('error', 'No tienes permiso para editar esta propiedad.');
            $this->redirect('propiedad/admin');
        }

        $errores = [];

        if ($this->isPost()) {
            $datos = $this->recogerDatos();
            $errores = $this->validar($datos);

            if (empty($errores)) {
                if (!empty($_FILES['imagen']['name'])) {
                    $nombreImg = $this->propiedad->subirImagen($_FILES['imagen']);
                    if ($nombreImg) {
                        // Eliminar imagen anterior si no es la default
                        if ($propiedad->imagen !== 'no-imagen.jpg') {
                            @unlink(UPLOAD_DIR . $propiedad->imagen);
                        }
                        $datos['imagen'] = $nombreImg;
                    } else {
                        $errores[] = 'La imagen no es válida. Solo JPG, PNG o WEBP hasta 5MB.';
                    }
                }

                if (empty($errores)) {
                    if (($_SESSION['usuario_rol'] ?? '') === 'vendedor') {
                        $vData = $this->vendedor->findOneWhere('usuario_id', $_SESSION['usuario_id']);
                        $datos['vendedor_id'] = $vData ? $vData->id : 0; // Forzar el ID original
                        $datos['estado_aprobacion'] = 'Pendiente';
                    }

                    $this->propiedad->update((int)$id, $datos);
                    
                    if (($_SESSION['usuario_rol'] ?? '') === 'vendedor') {
                        $this->notificarSupervisores($datos['titulo'], 'editada');
                        $this->flash('success', 'Propiedad actualizada correctamente. Pendiente de aprobación por un supervisor.');
                    } else {
                        $this->flash('success', 'Propiedad actualizada correctamente.');
                    }
                    
                    $this->redirect('propiedad/admin');
                }
            }
        }

        $zonas = $this->zona->findAll();
        $this->render('propiedades/editar', [
            'titulo'    => 'Editar Propiedad',
            'propiedad' => $propiedad,
            'zonas'     => $zonas,
            'errores'   => $errores,
        ]);
    }

    // GET /propiedad/eliminar/{id}
    public function eliminar(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor', 'scrum_master', 'especialista_ti']);
        $propiedad = $this->propiedad->findById((int)$id);
        
        if ($propiedad) {
            if (($_SESSION['usuario_rol'] ?? '') === 'vendedor' && $propiedad->vendedor_id != $_SESSION['usuario_id']) {
                $this->flash('error', 'No tienes permiso para eliminar esta propiedad.');
                $this->redirect('propiedad/admin');
                return;
            }

            if ($propiedad->imagen !== 'no-imagen.jpg') {
                @unlink(UPLOAD_DIR . $propiedad->imagen);
            }
            $this->propiedad->delete((int)$id);
            $this->flash('success', 'Propiedad eliminada.');
        }
        $this->redirect('propiedad/admin');
    }

    // GET /propiedad/aprobar/{id}
    public function aprobar(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor']);
        $this->propiedad->update((int)$id, ['estado_aprobacion' => 'Aprobado', 'activo' => 1]);
        $this->flash('success', 'Propiedad aprobada exitosamente y ahora es visible en el catálogo.');
        $this->redirect('propiedad/admin');
    }

    // GET /propiedad/rechazar/{id}
    public function rechazar(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor']);
        $this->propiedad->update((int)$id, ['estado_aprobacion' => 'Rechazado', 'activo' => 0]);
        $this->flash('success', 'Propiedad rechazada. No será visible en el catálogo.');
        $this->redirect('propiedad/admin');
    }

    // ── HELPERS PRIVADOS ─────────────────────────────────────

    private function recogerDatos(): array {
        return [
            'titulo'           => $this->sanitize($_POST['titulo']           ?? ''),
            'descripcion'      => $this->sanitize($_POST['descripcion']      ?? ''),
            'precio'           => (float) ($_POST['precio']                  ?? 0),
            'tipo'             => $this->sanitize($_POST['tipo']             ?? 'casa'),
            'habitaciones'     => (int) ($_POST['habitaciones']              ?? 0),
            'banos'            => (int) ($_POST['banos']                     ?? 0),
            'estacionamientos' => (int) ($_POST['estacionamientos']          ?? 0),
            'metros2'          => (float) ($_POST['metros2']                 ?? 0),
            'direccion'        => $this->sanitize($_POST['direccion']        ?? ''),
            'vendedor_id'      => empty($_POST['vendedor_id']) ? null : (int) $_POST['vendedor_id'],
            'zona_id'          => empty($_POST['zona_id']) ? null : (int) $_POST['zona_id'],
            'activo'           => isset($_POST['activo']) ? 1 : 0,
            'estado'           => $this->sanitize($_POST['estado']           ?? 'Disponible'),
        ];
    }

    private function validar(array $datos): array {
        $errores = [];
        if (empty($datos['titulo']))  $errores[] = 'El título es obligatorio.';
        if ($datos['precio'] <= 0)    $errores[] = 'El precio debe ser mayor a 0.';
        if (empty($datos['tipo']))    $errores[] = 'El tipo de propiedad es obligatorio.';
        return $errores;
    }

    private function notificarSupervisores(string $tituloPropiedad, string $accion): void {
        $usuarioModel = new Usuario();
        $notificacionModel = new Notificacion();
        
        $supervisores = $usuarioModel->findWhere('rol', 'supervisor');
        
        $vendedorNombre = $_SESSION['usuario_nombre'] ?? 'Un vendedor';
        $asunto = "Propiedad pendiente de aprobación: {$tituloPropiedad}";
        $mensajePanel = "El agente {$vendedorNombre} ha {$accion} la propiedad '{$tituloPropiedad}'. Requiere tu aprobación para ser publicada.";
        
        $cuerpoCorreo = "
            <h2>Nueva Propiedad Pendiente de Aprobación</h2>
            <p>Hola,</p>
            <p>El agente inmobiliario <strong>{$vendedorNombre}</strong> ha {$accion} la propiedad <strong>{$tituloPropiedad}</strong>.</p>
            <p>Por favor ingresa al panel de administración para revisarla, y decidir si se aprueba para su publicación en el catálogo.</p>
            <p><a href='" . BASE_URL . "/propiedad/admin'>Ir a Gestión de Propiedades</a></p>
        ";
        
        foreach ($supervisores as $sup) {
            // Notificación en panel
            $notificacionModel->insert([
                'usuario_id' => $sup->id,
                'titulo'     => "Propiedad pendiente: {$tituloPropiedad}",
                'mensaje'    => $mensajePanel,
                'enlace'     => '/propiedad/admin'
            ]);
            
            // Correo electrónico
            if (!empty($sup->email)) {
                Mailer::send($sup->email, $asunto, $cuerpoCorreo);
            }
        }
    }
}
