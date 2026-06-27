<?php
require_once APP_ROOT . '/core/Model.php';

class Ticket extends Model {

    protected string $table = 'tickets';


    /**
     * Crea un nuevo ticket y devuelve su ID
     * Genera automáticamente el código TKT-YYMMDD-NNN
     */
    public function createTicket(int $usuario_id, string $servicio_id, string $descripcion): ?int {
        // Generar prefijo fecha YYMMDD
        $fecha = date('ymd');
        
        // Buscar el último ticket de hoy para el correlativo
        $sql = "SELECT codigo FROM {$this->table} WHERE codigo LIKE 'TKT-{$fecha}-%' ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $lastTicket = $stmt->fetch(PDO::FETCH_ASSOC);

        $correlativo = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket['codigo']);
            if (count($parts) === 3) {
                $correlativo = (int)$parts[2] + 1;
            }
        }

        $codigo = sprintf("TKT-%s-%03d", $fecha, $correlativo);

        $data = [
            'codigo' => $codigo,
            'usuario_id' => $usuario_id,
            'servicio_id' => $servicio_id,
            'descripcion' => $descripcion,
            'estado' => 'Abierto'
        ];

        return $this->insert($data);
    }

    /**
     * Obtiene todos los tickets (Para Scrum Master y Seguridad)
     */
    public function getAllTickets(): array {
        $sql = "
            SELECT t.*, 
                   u1.nombre as solicitante_nombre,
                   u2.nombre as asignado_nombre
            FROM {$this->table} t
            LEFT JOIN usuarios u1 ON t.usuario_id = u1.id
            LEFT JOIN usuarios u2 ON t.asignado_a = u2.id
            ORDER BY t.id DESC
        ";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los tickets de un solicitante
     */
    public function getTicketsByUsuario(int $usuario_id): array {
        $sql = "
            SELECT t.*, 
                   u1.nombre as solicitante_nombre,
                   u2.nombre as asignado_nombre
            FROM {$this->table} t
            LEFT JOIN usuarios u1 ON t.usuario_id = u1.id
            LEFT JOIN usuarios u2 ON t.asignado_a = u2.id
            WHERE t.usuario_id = ?
            ORDER BY t.id DESC
        ";
        return $this->db->query($sql, [$usuario_id])->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los tickets asignados a un Especialista TI
     */
    public function getTicketsAsignados(int $asignado_a): array {
        $sql = "
            SELECT t.*, 
                   u1.nombre as solicitante_nombre,
                   u2.nombre as asignado_nombre
            FROM {$this->table} t
            LEFT JOIN usuarios u1 ON t.usuario_id = u1.id
            LEFT JOIN usuarios u2 ON t.asignado_a = u2.id
            WHERE t.asignado_a = ?
            ORDER BY t.id DESC
        ";
        return $this->db->query($sql, [$asignado_a])->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Asigna un especialista y prioridad (Scrum Master)
     */
    public function assignTicket(int $id, int $asignado_a, string $prioridad): bool {
        return $this->update($id, [
            'asignado_a' => $asignado_a,
            'prioridad' => $prioridad,
            'estado' => 'Asignado'
        ]);
    }

    /**
     * Registra la acción técnica y marca como Resuelto (Especialista TI)
     */
    public function technicalResolve(int $id, string $accion_tecnica): bool {
        return $this->update($id, [
            'accion_tecnica' => $accion_tecnica,
            'estado' => 'Resuelto'
        ]);
    }

    /**
     * Cierra el ticket y guarda la resolución (Scrum Master o Solicitante)
     */
    public function closeTicket(int $id, string $resolucion): bool {
        return $this->update($id, [
            'resolucion' => $resolucion,
            'estado' => 'Cerrado',
            'fecha_cierre' => date('Y-m-d H:i:s')
        ]);
    }
}
