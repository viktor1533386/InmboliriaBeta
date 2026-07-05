<?php
// ============================================================
//  CONTROLLER: Zona (Gestión para Supervisores)
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/app/models/Zona.php';
require_once APP_ROOT . '/app/models/Vendedor.php';

class ZonaController extends Controller {

    private Zona $zona;
    private Vendedor $vendedor;

    public function __construct() {
        $this->zona = new Zona();
        $this->vendedor = new Vendedor();
        // Solo acceso para admin y supervisor
        Middleware::requireRole(['admin', 'supervisor']);
    }

    // GET /zona  → lista admin
    public function index(): void {
        $zonas = $this->zona->findAll();
        // Obtener vendedores por zona para mostrarlos en la lista
        foreach ($zonas as $z) {
            $z->vendedores = $this->zona->obtenerVendedores($z->id);
        }

        $this->render('zonas/admin_index', [
            'titulo' => 'Gestión de Zonas',
            'zonas'  => $zonas,
        ]);
    }

    // GET/POST /zona/crear
    public function crear(): void {
        $errores = [];

        if ($this->isPost()) {
            $nombre = $this->sanitize($_POST['nombre'] ?? '');
            if (empty($nombre)) {
                $errores[] = 'El nombre de la zona es obligatorio.';
            }

            if (empty($errores)) {
                $zonaId = $this->zona->insert(['nombre' => $nombre]);
                
                // Asignar vendedores si se seleccionaron
                if (!empty($_POST['vendedores_ids'])) {
                    $this->zona->asignarVendedores($zonaId, $_POST['vendedores_ids']);
                }

                $this->flash('success', 'Zona creada correctamente.');
                $this->redirect('zona');
            }
        }

        $vendedores = $this->vendedor->listaParaSelect();
        $this->render('zonas/crear', [
            'titulo'     => 'Nueva Zona',
            'vendedores' => $vendedores,
            'errores'    => $errores,
        ]);
    }

    // GET/POST /zona/editar/{id}
    public function editar(string $id = '0'): void {
        $zonaInfo = $this->zona->findById((int)$id);
        if (!$zonaInfo) $this->redirect('zona');

        $errores = [];

        if ($this->isPost()) {
            $nombre = $this->sanitize($_POST['nombre'] ?? '');
            if (empty($nombre)) {
                $errores[] = 'El nombre de la zona es obligatorio.';
            }

            if (empty($errores)) {
                $this->zona->update((int)$id, ['nombre' => $nombre]);
                
                // Asignar vendedores
                $vendedoresIds = $_POST['vendedores_ids'] ?? [];
                $this->zona->asignarVendedores((int)$id, $vendedoresIds);

                $this->flash('success', 'Zona actualizada correctamente.');
                $this->redirect('zona');
            }
        }

        $vendedores = $this->vendedor->listaParaSelect();
        $vendedoresAsignados = array_column($this->zona->obtenerVendedores((int)$id), 'id');

        $this->render('zonas/editar', [
            'titulo'              => 'Editar Zona',
            'zona'                => $zonaInfo,
            'vendedores'          => $vendedores,
            'vendedoresAsignados' => $vendedoresAsignados,
            'errores'             => $errores,
        ]);
    }

    // GET /zona/eliminar/{id}
    public function eliminar(string $id = '0'): void {
        $zonaInfo = $this->zona->findById((int)$id);
        if ($zonaInfo) {
            $this->zona->delete((int)$id);
            $this->flash('success', 'Zona eliminada.');
        }
        $this->redirect('zona');
    }
}
