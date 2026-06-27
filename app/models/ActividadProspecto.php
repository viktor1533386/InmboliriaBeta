<?php
// ============================================================
//  MODEL: ActividadProspecto
// ============================================================
require_once APP_ROOT . '/core/Model.php';

class ActividadProspecto extends Model {
    protected string $table = 'actividades_prospecto';

    public function findByProspecto(int $prospecto_id): array {
        $sql = "SELECT a.*, u.nombre AS autor_nombre, u.rol AS autor_rol 
                FROM `{$this->table}` a
                LEFT JOIN usuarios u ON a.creado_por = u.id
                WHERE a.prospecto_id = ?
                ORDER BY a.created_at DESC";
        return $this->raw($sql, [$prospecto_id]);
    }
}
