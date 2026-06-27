<?php
// ============================================================
//  MODEL: MensajeEstadoTemplate
// ============================================================
require_once APP_ROOT . '/core/Model.php';

class MensajeEstadoTemplate extends Model {
    protected string $table = 'mensajes_estado_templates';

    public function findByEstado(string $estado): object|false {
        return $this->findOneWhere('estado', $estado);
    }
}
