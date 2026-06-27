<?php
// ============================================================
//  MODEL: Prospecto
// ============================================================
require_once APP_ROOT . '/core/Model.php';

class Prospecto extends Model {
    protected string $table = 'prospectos';

    public function findByCodigo(string $codigo): object|false {
        return $this->findOneWhere('codigo', $codigo);
    }
    
    public function findByVendedor(int $vendedor_id): array {
        return $this->findAll("created_at DESC", "vendedor_id = $vendedor_id");
    }

    public function findNuevos(): array {
        return $this->findAll("created_at DESC", "estado = 'Nuevo'");
    }
}
