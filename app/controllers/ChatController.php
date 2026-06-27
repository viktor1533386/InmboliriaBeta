<?php
// ============================================================
//  CONTROLLER: ChatController
// ============================================================
require_once APP_ROOT . '/core/Controller.php';

class ChatController extends Controller {

    // GET /chat/contactos
    public function contactos(): void {
        header('Content-Type: application/json');
        if (empty($_SESSION['usuario_id'])) {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        require_once APP_ROOT . '/app/models/Usuario.php';
        $usuarioModel = new Usuario();
        
        $sql = "SELECT id, nombre, rol FROM usuarios WHERE id != ? AND estado = 1 ORDER BY nombre ASC";
        $contactos = $usuarioModel->raw($sql, [$_SESSION['usuario_id']]);
        
        // Count unread (total messages received from each contact)
        require_once APP_ROOT . '/app/models/MensajeChat.php';
        $chatModel = new MensajeChat();
        $mensajesTotales = $chatModel->getUnreadCounts($_SESSION['usuario_id']);
        
        foreach ($contactos as $c) {
            $c->mensajes_recibidos = $mensajesTotales[$c->id] ?? 0;
        }

        echo json_encode(['contactos' => $contactos]);
    }

    // GET /chat/obtener?dest=ID
    public function obtener(): void {
        header('Content-Type: application/json');
        if (empty($_SESSION['usuario_id'])) {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $destinatario_id = (int)($_GET['dest'] ?? 0);
        if ($destinatario_id <= 0) {
            echo json_encode(['mensajes' => []]);
            return;
        }

        require_once APP_ROOT . '/app/models/MensajeChat.php';
        $chatModel = new MensajeChat();
        $mensajes = $chatModel->getMessagesBetween($_SESSION['usuario_id'], $destinatario_id, 50);
        
        echo json_encode(['mensajes' => $mensajes]);
    }

    // POST /chat/enviar
    public function enviar(): void {
        header('Content-Type: application/json');
        if (empty($_SESSION['usuario_id'])) {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        if ($this->isPost()) {
            $input = json_decode(file_get_contents('php://input'), true);
            $mensaje = $input['mensaje'] ?? ($_POST['mensaje'] ?? '');
            $destinatario_id = (int)($input['destinatario_id'] ?? ($_POST['destinatario_id'] ?? 0));
            $mensaje = trim(htmlspecialchars(strip_tags($mensaje)));

            if (!empty($mensaje) && $destinatario_id > 0) {
                require_once APP_ROOT . '/app/models/MensajeChat.php';
                $chatModel = new MensajeChat();
                
                $id_insertado = $chatModel->insert([
                    'remitente_id' => $_SESSION['usuario_id'],
                    'destinatario_id' => $destinatario_id,
                    'mensaje' => $mensaje
                ]);
                
                echo json_encode(['success' => true, 'id' => $id_insertado]);
                return;
            }
        }
        
        echo json_encode(['error' => 'Mensaje vacío o destinatario inválido']);
    }
}
