<?php
// ============================================================
//  MODEL: MensajeChat
// ============================================================
require_once APP_ROOT . '/core/Model.php';

class MensajeChat extends Model {
    protected string $table = 'mensajes_chat';

    public function getMessagesBetween(int $user1, int $user2, int $limit = 50): array {
        $sql = "SELECT m.*, u.nombre as remitente_nombre, u.rol as remitente_rol 
                FROM {$this->table} m 
                JOIN usuarios u ON m.remitente_id = u.id 
                WHERE (m.remitente_id = ? AND m.destinatario_id = ?) 
                   OR (m.remitente_id = ? AND m.destinatario_id = ?)
                ORDER BY m.created_at DESC 
                LIMIT {$limit}";
        
        $messages = $this->raw($sql, [$user1, $user2, $user2, $user1]);
        return array_reverse($messages);
    }
    
    // Check if there are any unread messages (simple check if we had read_at, but we don't, so just get count of messages where user is destinatario)
    public function getUnreadCounts(int $userId): array {
        // Since we don't have read_at, we just count all messages sent TO this user BY each remitente
        $sql = "SELECT remitente_id, COUNT(*) as total 
                FROM {$this->table} 
                WHERE destinatario_id = ? 
                GROUP BY remitente_id";
        $results = $this->raw($sql, [$userId]);
        $counts = [];
        foreach ($results as $r) {
            $counts[$r->remitente_id] = $r->total;
        }
        return $counts;
    }
}
