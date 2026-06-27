<?php
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/core/Mailer.php';
require_once APP_ROOT . '/app/models/Ticket.php';

class TicketController extends Controller {

    public function __construct() {
        // Todos los métodos de Ticket requieren estar logueado
        Middleware::auth();
    }

    public static $serviciosList = [
        'SRV-01' => 'Crear cuenta de vendedor',
        'SRV-02' => 'Publicar propiedad (soporte)',
        'SRV-03' => 'Reset de contraseña',
        'SRV-04' => 'Respaldo manual de BD',
        'SRV-05' => 'Restaurar información',
        'SRV-06' => 'Reporte de disponibilidad',
        'SRV-07' => 'Modificación de datos maestros'
    ];

    public function create() {
        // Solicitantes válidos: Vendedor o Admin
        Middleware::requireRole(['admin', 'supervisor', 'vendedor']);

        $this->render('tickets/create', ['servicios' => self::$serviciosList]);
    }

    public function store() {
        Middleware::requireRole(['admin', 'supervisor', 'vendedor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $servicio_id = $_POST['servicio_id'] ?? '';
            $descripcion = trim($_POST['descripcion'] ?? '');
            
            if (empty($servicio_id) || empty($descripcion)) {
                $_SESSION['flash_error'] = 'Todos los campos son obligatorios.';
                header('Location: ' . BASE_URL . '/ticket/create');
                exit;
            }

            $ticketModel = new Ticket();
            $usuario_id = $_SESSION['usuario_id'];
            
            $ticket_id = $ticketModel->createTicket($usuario_id, $servicio_id, $descripcion);
            
            if ($ticket_id) {
                // Recuperar info del ticket para el correo
                $ticket = $ticketModel->findById($ticket_id);
                $codigo = $ticket->codigo ?? $ticket['codigo'] ?? 'Desconocido';
                
                // Enviar correo al Scrum Master
                $to = 'soporte@hogarideal.pe';
                $subject = "Nuevo Ticket Generado: " . $codigo;
                $body = "
                    <h2>Nuevo Ticket en Mesa de Ayuda</h2>
                    <p><strong>Código:</strong> {$codigo}</p>
                    <p><strong>Servicio Solicitado:</strong> {$servicio_id}</p>
                    <p><strong>Solicitante (User ID):</strong> {$usuario_id}</p>
                    <p><strong>Descripción:</strong><br/>" . nl2br(htmlspecialchars($descripcion)) . "</p>
                    <hr>
                    <p><em>Hogar Ideal Perú - Sistema de Tickets</em></p>
                ";
                
                Mailer::send($to, $subject, $body);

                $_SESSION['flash_success'] = 'Ticket generado correctamente. Código de seguimiento: ' . $codigo;
                header('Location: ' . BASE_URL . '/ticket/create');
                exit;
            } else {
                $_SESSION['flash_error'] = 'Error al registrar el ticket. Intente nuevamente.';
                header('Location: ' . BASE_URL . '/ticket/create');
                exit;
            }
        }
        
        header('Location: ' . BASE_URL . '/ticket/create');
        exit;
    }

    public function index() {
        // Todos pueden ver una bandeja (diferenciada)
        $rol = $_SESSION['usuario_rol'] ?? '';

        $ticketModel = new Ticket();
        require_once APP_ROOT . '/app/models/Usuario.php';
        $usuarioModel = new Usuario();

        $tickets = [];
        if ($rol === 'scrum_master' || $rol === 'seguridad') {
            $tickets = $ticketModel->getAllTickets();
        } elseif ($rol === 'especialista_ti') {
            $tickets = $ticketModel->getTicketsAsignados($_SESSION['usuario_id']);
        } else {
            // Solicitantes (admin, supervisor, vendedor)
            $tickets = $ticketModel->getTicketsByUsuario($_SESSION['usuario_id']);
        }

        $tiUsers = $usuarioModel->findWhere('rol', 'especialista_ti');

        $this->render('tickets/index', [
            'tickets' => $tickets,
            'tiUsers' => $tiUsers,
            'userRole' => $rol,
            'serviciosList' => self::$serviciosList
        ]);
    }

    public function assign() {
        Middleware::requireRole(['scrum_master']);

        if ($this->isPost()) {
            $ticket_id = (int)$_POST['ticket_id'];
            $asignado_a = (int)$_POST['asignado_a'];
            $prioridad = $_POST['prioridad'];

            $ticketModel = new Ticket();
            $ticketModel->assignTicket($ticket_id, $asignado_a, $prioridad);

            require_once APP_ROOT . '/app/models/Usuario.php';
            $usuarioModel = new Usuario();
            $userAsignado = $usuarioModel->findById($asignado_a);
            $ticket = $ticketModel->findById($ticket_id);

            if ($userAsignado) {
                $emailAsignado = $userAsignado->email ?? $userAsignado['email'];
                $codigo = $ticket->codigo ?? $ticket['codigo'];
                Mailer::send(
                    $emailAsignado,
                    "Ticket Asignado: {$codigo} (Prioridad {$prioridad})",
                    "Se te ha asignado el ticket {$codigo} con prioridad {$prioridad}. Por favor revísalo en tu bandeja."
                );
            }

            $_SESSION['flash_success'] = 'Ticket asignado correctamente.';
        }
        $this->redirect('ticket/index');
    }

    public function updateStatus() {
        Middleware::requireRole(['especialista_ti']);
        if ($this->isPost()) {
            $ticket_id = (int)$_POST['ticket_id'];
            $estado = $_POST['estado'] ?? '';
            
            if (in_array($estado, ['En Progreso', 'Pendiente', 'Cancelado'])) {
                $ticketModel = new Ticket();
                $ticketModel->update($ticket_id, ['estado' => $estado]);
                $_SESSION['flash_success'] = "El estado del ticket se actualizó a: $estado.";
            } else {
                $_SESSION['flash_error'] = "Estado no válido.";
            }
        }
        $this->redirect('ticket/index');
    }

    public function technical_resolve() {
        Middleware::requireRole(['especialista_ti']);

        if ($this->isPost()) {
            $ticket_id = (int)$_POST['ticket_id'];
            $accion_tecnica = trim($_POST['accion_tecnica'] ?? '');

            $ticketModel = new Ticket();
            $ticketModel->technicalResolve($ticket_id, $accion_tecnica);

            $_SESSION['flash_success'] = 'Acción técnica registrada y ticket marcado como Resuelto.';
        }
        $this->redirect('ticket/index');
    }

    public function confirm_close() {
        // Scrum master puede cerrarlo, o el Solicitante (vendedor/admin/supervisor)
        Middleware::requireRole(['scrum_master', 'admin', 'supervisor', 'vendedor']);

        if ($this->isPost()) {
            $ticket_id = (int)$_POST['ticket_id'];
            $resolucion = trim($_POST['resolucion'] ?? '');

            $ticketModel = new Ticket();
            $ticketModel->closeTicket($ticket_id, $resolucion);

            $_SESSION['flash_success'] = 'Ticket cerrado correctamente.';
        }
        $this->redirect('ticket/index');
    }
}
