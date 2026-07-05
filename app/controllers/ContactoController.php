<?php
// ============================================================
//  CONTROLLER: Contacto
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Mailer.php';
require_once APP_ROOT . '/app/models/Prospecto.php';
require_once APP_ROOT . '/app/models/ActividadProspecto.php';
require_once APP_ROOT . '/app/models/Propiedad.php';
require_once APP_ROOT . '/app/models/Zona.php';

class ContactoController extends Controller {

    private Prospecto $prospecto;
    private ActividadProspecto $actividad;
    private Propiedad $propiedadModel;
    private Zona $zona;

    public function __construct() {
        $this->prospecto = new Prospecto();
        $this->actividad = new ActividadProspecto();
        $this->propiedadModel = new Propiedad();
        $this->zona = new Zona();
    }

    // GET/POST /contacto
    public function index(): void {
        $exito = false;
        $error = '';
        $codigoGenerado = '';

        if ($this->isPost()) {
            $nombre   = $this->sanitize($_POST['nombre']   ?? '');
            $email    = $this->sanitize($_POST['email']    ?? '');
            $telefono = $this->sanitize($_POST['telefono'] ?? '');
            $mensaje  = $this->sanitize($_POST['mensaje']  ?? '');
            // Si el formulario enviara propiedad_id, podríamos recibirlo. Por defecto NULL.
            $propiedad_id = !empty($_POST['propiedad_id']) ? (int)$_POST['propiedad_id'] : null;

            if (!$nombre || !$email || !$mensaje) {
                $error = 'Por favor completa los campos requeridos.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'El correo electrónico no es válido.';
            } else {
                // 1. Generar código único: PROS-YYMMDD-XXXX
                $fecha = date('ymd');
                do {
                    $random = strtoupper(bin2hex(random_bytes(2)));
                    $codigoGenerado = "PROS-{$fecha}-{$random}";
                    $existe = $this->prospecto->rawOne("SELECT id FROM prospectos WHERE codigo = ?", [$codigoGenerado]);
                } while ($existe);
                // Determinar si hay un vendedor a asignar por zona
                $vendedor_asignado_id = null;
                $estado_inicial = 'Nuevo';
                if ($propiedad_id) {
                    $propiedadInfo = $this->propiedadModel->findById($propiedad_id);
                    if ($propiedadInfo && !empty($propiedadInfo->zona_id)) {
                        $vendedor_asignado_id = $this->zona->obtenerVendedorMenorCarga((int)$propiedadInfo->zona_id);
                        if ($vendedor_asignado_id) {
                            $estado_inicial = 'Asignado';
                        }
                    }
                }

                // 2. Insertar prospecto
                $prospectoId = $this->prospecto->insert([
                    'codigo'       => $codigoGenerado,
                    'nombre'       => $nombre,
                    'email'        => $email,
                    'telefono'     => $telefono,
                    'mensaje'      => $mensaje,
                    'propiedad_id' => $propiedad_id,
                    'vendedor_id'  => $vendedor_asignado_id,
                    'estado'       => $estado_inicial
                ]);

                // 3. Crear actividad inicial
                $comentarioExtra = '';
                if ($vendedor_asignado_id) {
                    $comentarioExtra = ' y fue asignado automáticamente por zona.';
                } else if ($propiedad_id) {
                    $comentarioExtra = ' (Nota: La zona de la propiedad no tiene vendedores asignados, por lo que quedó pendiente de asignación).';
                }

                $this->actividad->insert([
                    'prospecto_id' => $prospectoId,
                    'tipo'         => 'Nuevo',
                    'comentario'   => 'El visitante completó el formulario de contacto' . $comentarioExtra,
                    'nuevo_estado' => $estado_inicial
                ]);

                // 4. Enviar correo al visitante con el código de seguimiento
                $asunto = "Hemos recibido tu consulta - $codigoGenerado";
                $urlSeguimiento = BASE_URL . "/seguimiento";
                
                $mensajePersonalizado = "Hemos recibido tu mensaje y un supervisor lo revisará pronto para asignarte un agente inmobiliario.";
                if ($vendedor_asignado_id) {
                    $mensajePersonalizado = "Tu solicitud ha sido pre-asignada a uno de nuestros agentes en la zona, quien se comunicará contigo a la brevedad.";
                }

                $cuerpo = "
                    <h2>¡Hola $nombre!</h2>
                    <p>Gracias por contactar con Hogar Ideal Perú. $mensajePersonalizado</p>
                    <p>Para tu tranquilidad, puedes hacer seguimiento del estado de tu solicitud usando el siguiente código:</p>
                    <div style='background:#f3f4f6;padding:15px;text-align:center;font-size:20px;font-weight:bold;letter-spacing:2px;'>$codigoGenerado</div>
                    <p>Ingresa tu código y tu correo en nuestro portal de seguimiento:</p>
                    <p><a href='$urlSeguimiento'>$urlSeguimiento</a></p>
                ";
                Mailer::send($email, $asunto, $cuerpo);

                $exito = true;
            }
        }

        $this->render('contacto/index', [
            'titulo' => 'Contacto – ' . APP_NAME,
            'exito'  => $exito,
            'error'  => $error,
            'codigo' => $codigoGenerado
        ]);
    }
}
