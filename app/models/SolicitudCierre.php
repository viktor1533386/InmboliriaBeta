<?php
// ============================================================
//  MODEL: SolicitudCierre
// ============================================================
require_once APP_ROOT . '/core/Model.php';

class SolicitudCierre extends Model {
    protected string $table = 'solicitudes_cierre';

    public function findPendientes(): array {
        $sql = "SELECT s.*, p.codigo as prospecto_codigo, p.nombre as prospecto_nombre,
                       prop.titulo as propiedad_titulo, v.nombre as vendedor_nombre, v.apellido as vendedor_apellido
                FROM {$this->table} s
                JOIN prospectos p ON s.prospecto_id = p.id
                JOIN propiedades prop ON s.propiedad_id = prop.id
                JOIN vendedores v ON s.vendedor_id = v.id
                WHERE s.estado = 'Pendiente'
                ORDER BY s.created_at ASC";
        return $this->raw($sql);
    }
    
    public function getDetails(int $id): object|false {
        $sql = "SELECT s.*, p.codigo as prospecto_codigo, p.nombre as prospecto_nombre, p.email as prospecto_email, p.telefono as prospecto_telefono,
                       prop.titulo as propiedad_titulo, prop.precio as propiedad_precio, prop.id as prop_id,
                       v.nombre as vendedor_nombre, v.apellido as vendedor_apellido, v.usuario_id as vendedor_usuario_id
                FROM {$this->table} s
                JOIN prospectos p ON s.prospecto_id = p.id
                JOIN propiedades prop ON s.propiedad_id = prop.id
                JOIN vendedores v ON s.vendedor_id = v.id
                WHERE s.id = ?
                LIMIT 1";
        return $this->rawOne($sql, [$id]);
    }
}
