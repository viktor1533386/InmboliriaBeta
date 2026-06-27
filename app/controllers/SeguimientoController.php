<?php
// ============================================================
//  CONTROLLER: Seguimiento (Público)
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/app/models/Prospecto.php';
require_once APP_ROOT . '/app/models/MensajeEstadoTemplate.php';

class SeguimientoController extends Controller {

    private Prospecto $prospecto;
    private MensajeEstadoTemplate $templateModel;

    public function __construct() {
        $this->prospecto = new Prospecto();
        $this->templateModel = new MensajeEstadoTemplate();
    }

    // GET /seguimiento
    public function index(): void {
        $codigo = $this->sanitize($_GET['codigo'] ?? '');
        $email  = $this->sanitize($_GET['email'] ?? '');
        
        // Si no hay código o email, mostramos el formulario
        if (empty($codigo) || empty($email)) {
            $this->render('publico/seguimiento_form', [
                'titulo' => 'Seguimiento de Solicitud – ' . APP_NAME,
                'codigo' => $codigo,
                'email'  => $email,
                'error'  => (!empty($_GET) && (empty($codigo) || empty($email))) ? 'Debe ingresar su código de seguimiento y su correo electrónico.' : ''
            ]);
            return;
        }

        // Si hay datos, buscamos el prospecto
        $prospecto = $this->prospecto->findByCodigo($codigo);

        if (!$prospecto || $prospecto->email !== $email) {
            $this->render('publico/seguimiento_form', [
                'titulo' => 'Seguimiento de Solicitud – ' . APP_NAME,
                'codigo' => $codigo,
                'email'  => $email,
                'error'  => 'No se encontró ninguna solicitud con ese código y correo electrónico.'
            ]);
            return;
        }

        // Cargar template del estado
        $template = $this->templateModel->findByEstado($prospecto->estado);
        
        // Obtener nombre del vendedor si está asignado
        $vendedorNombre = 'un agente';
        if ($prospecto->vendedor_id) {
            require_once APP_ROOT . '/app/models/Vendedor.php';
            $vendedorModel = new Vendedor();
            $vendedor = $vendedorModel->findById((int)$prospecto->vendedor_id);
            if ($vendedor) {
                $vendedorNombre = $vendedor->nombre . ' ' . $vendedor->apellido;
            }
        }

        // Reemplazar variables en el cuerpo del template
        $cuerpoHtml = $template ? $template->cuerpo_template : 'Estamos procesando tu solicitud.';
        $cuerpoHtml = str_replace('{vendedor_nombre}', $vendedorNombre, $cuerpoHtml);

        $this->render('publico/seguimiento_resultado', [
            'titulo'    => 'Estado de tu Solicitud – ' . APP_NAME,
            'prospecto' => $prospecto,
            'template'  => $template,
            'cuerpoHtml'=> $cuerpoHtml,
            'vendedor'  => $vendedorNombre
        ]);
    }
}
