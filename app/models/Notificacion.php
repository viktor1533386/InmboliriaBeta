<?php
// ============================================================
//  MODEL: Notificacion
// ============================================================
require_once APP_ROOT . '/core/Model.php';

class Notificacion extends Model {
    protected string $table = 'notificaciones';

    public function findByUsuario(int $usuario_id): array {
        return $this->findAll("created_at DESC", "usuario_id = $usuario_id");
    }

    public function findNoLeidas(int $usuario_id): array {
        return $this->findAll("created_at DESC", "usuario_id = $usuario_id AND leido = 0");
    }

    public function marcarLeida(int $id, int $usuario_id): void {
        $sql = "UPDATE {$this->table} SET leido = 1 WHERE id = ? AND usuario_id = ?";
        $this->raw($sql, [$id, $usuario_id]);
    }
}
