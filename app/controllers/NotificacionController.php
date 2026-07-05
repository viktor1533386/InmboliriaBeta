<?php
// ============================================================
//  CONTROLLER: Notificacion
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/app/models/Notificacion.php';

class NotificacionController extends Controller {

    // Endpoint AJAX para chequear notificaciones nuevas
    public function check(): void {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            return;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];
        $notifModel = new Notificacion();
        $noLeidas = $notifModel->findNoLeidas($usuario_id);

        echo json_encode([
            'status' => 'success',
            'count'  => count($noLeidas),
            'data'   => $noLeidas
        ]);
    }
}
