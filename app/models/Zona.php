<?php
// ============================================================
//  MODEL: Zona
// ============================================================
require_once APP_ROOT . '/core/Model.php';

class Zona extends Model {
    protected string $table = 'zonas';

    // Obtener los vendedores asignados a una zona
    public function obtenerVendedores(int $zona_id): array {
        $sql = "SELECT v.* 
                FROM vendedores v
                JOIN zona_vendedor zv ON v.id = zv.vendedor_id
                WHERE zv.zona_id = ?";
        return $this->raw($sql, [$zona_id]);
    }

    // Asignar vendedores a una zona (reemplaza los existentes)
    public function asignarVendedores(int $zona_id, array $vendedor_ids): void {
        // Primero eliminar las asignaciones actuales
        $sqlDelete = "DELETE FROM zona_vendedor WHERE zona_id = ?";
        $this->raw($sqlDelete, [$zona_id]);

        // Luego insertar las nuevas
        if (!empty($vendedor_ids)) {
            $sqlInsert = "INSERT INTO zona_vendedor (zona_id, vendedor_id) VALUES (?, ?)";
            foreach ($vendedor_ids as $vid) {
                $this->raw($sqlInsert, [$zona_id, (int)$vid]);
            }
        }
    }

    // Obtener el vendedor con MENOR carga de trabajo en una zona
    // Se cuentan los prospectos activos asignados al vendedor
    public function obtenerVendedorMenorCarga(int $zona_id): ?int {
        $sql = "SELECT v.id, COUNT(p.id) as num_prospectos
                FROM zona_vendedor zv
                JOIN vendedores v ON zv.vendedor_id = v.id
                LEFT JOIN prospectos p ON p.vendedor_id = v.id AND p.estado NOT IN ('Cerrado_Ganado', 'Cerrado_Perdedor')
                WHERE zv.zona_id = ?
                GROUP BY v.id
                ORDER BY num_prospectos ASC, v.id ASC
                LIMIT 1";
        
        $resultado = $this->rawOne($sql, [$zona_id]);
        return $resultado ? (int)$resultado->id : null;
    }
}
