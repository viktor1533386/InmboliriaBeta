<?php
// ============================================================
//  CONTROLLER: Usuario – Gestión de usuarios del sistema
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/core/Mailer.php';
require_once APP_ROOT . '/app/models/Usuario.php';

class UsuarioController extends Controller {

    private Usuario $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    // GET /usuario
    public function index(): void {
        Middleware::requireRole(['admin']);
        $usuarios = $this->usuario->findAll('created_at DESC');
        $this->render('usuarios/index', [
            'titulo'   => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
        ]);
    }

    // GET/POST /usuario/crear
    public function crear(): void {
        Middleware::requireRole(['admin']);
        $errores = [];

        if ($this->isPost()) {
            $datos = $this->recogerDatos($_POST);
            $errores = $this->validar($datos, false);

            if (empty($errores)) {
                $passwordPlano = $this->generarPasswordTemporal();
                $datos['password'] = password_hash($passwordPlano, PASSWORD_BCRYPT);
                $datos['password_reset_required'] = 1;
                try {
                    $this->usuario->insert($datos);
                    
                    // Enviar correo de bienvenida
                    $asunto = "Bienvenido a Hogar Ideal Perú - Tus Credenciales";
                    $cuerpo = "
                        <h2>¡Hola {$datos['nombre']}!</h2>
                        <p>Has sido registrado en la plataforma de <strong>Hogar Ideal Perú</strong> con el rol de <strong>{$datos['rol']}</strong>.</p>
                        <p>Tus credenciales temporales de acceso son:</p>
                        <ul>
                            <li><strong>Email:</strong> {$datos['email']}</li>
                            <li><strong>Contraseña:</strong> $passwordPlano</li>
                        </ul>
                        <p>Por seguridad, el sistema te pedirá cambiar esta contraseña la primera vez que ingreses al panel.</p>
                        <p>Puedes iniciar sesión aquí: <a href='" . BASE_URL . "/auth/login'>" . BASE_URL . "/auth/login</a></p>
                    ";
                    
                    $enviado = Mailer::send($datos['email'], $asunto, $cuerpo);
                    
                    if ($enviado) {
                        $this->flash('success', 'Usuario creado. Se enviaron las credenciales por correo.');
                    } else {
                        $this->flash('success', 'Usuario creado, pero hubo un error al enviar el correo. Pass temporal: ' . $passwordPlano);
                    }
                    
                    $_SESSION['temp_password'] = $passwordPlano;
                    $_SESSION['temp_user_email'] = $datos['email'];
                    $this->redirect('usuario');
                } catch (Exception $e) {
                    $errores[] = 'No se pudo guardar el usuario. Verifica que el email no exista.';
                }
            }
        }

        $this->render('usuarios/crear', [
            'titulo'  => 'Nuevo Usuario',
            'errores' => $errores,
        ]);
    }

    // GET/POST /usuario/editar/{id}
    public function editar(string $id = '0'): void {
        Middleware::requireRole(['admin']);
        $usuario = $this->usuario->findById((int)$id);
        if (!$usuario) $this->redirect('usuario');

        $errores = [];

        if ($this->isPost()) {
            $datos = $this->recogerDatos($_POST, false);
            $errores = $this->validar($datos, false);

            if (empty($errores)) {
                $update = [
                    'nombre' => $datos['nombre'],
                    'email'  => $datos['email'],
                    'password_reset_required' => $datos['password_reset_required'],
                ];

                // No permitir auto-degradarse de rol ni auto-desactivarse
                if ((int)$id !== (int)$_SESSION['usuario_id']) {
                    $update['rol'] = $datos['rol'];
                    $update['estado'] = $datos['estado'];
                }

                if (!empty($datos['password'])) {
                    $update['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);
                }

                try {
                    $this->usuario->update((int)$id, $update);
                    $this->flash('success', 'Usuario actualizado correctamente.');
                    $this->redirect('usuario');
                } catch (Exception $e) {
                    $errores[] = 'No se pudo actualizar el usuario. Verifica el email.';
                }
            }
        }

        $this->render('usuarios/editar', [
            'titulo'  => 'Editar Usuario',
            'usuario' => $usuario,
            'errores' => $errores,
        ]);
    }

    // GET /usuario/eliminar/{id}
    public function eliminar(string $id = '0'): void {
        Middleware::requireRole(['admin']);
        if ((int)$id === (int)$_SESSION['usuario_id']) {
            $this->flash('error', 'No puedes eliminar tu propia cuenta.');
            $this->redirect('usuario');
        }
        if ((int)$id === 1) {
            $this->flash('error', 'No se puede eliminar al administrador principal.');
            $this->redirect('usuario');
        }
        $this->usuario->delete((int)$id);
        $this->flash('success', 'Usuario eliminado.');
        $this->redirect('usuario');
    }

    // GET /usuario/reset/{id}
    public function reset(string $id = '0'): void {
        Middleware::requireRole(['admin']);
        $usuario = $this->usuario->findById((int)$id);
        if (!$usuario || ($usuario->rol ?? '') === 'admin') {
            $this->redirect('usuario');
        }

        $passwordPlano = $this->generarPasswordTemporal();
        $this->usuario->update((int)$id, [
            'password' => password_hash($passwordPlano, PASSWORD_BCRYPT),
            'password_reset_required' => 1,
        ]);

        $_SESSION['temp_password'] = $passwordPlano;
        $_SESSION['temp_user_email'] = $usuario->email;
        $this->flash('success', 'Contraseña temporal generada.');
        $this->redirect('usuario');
    }

    private function recogerDatos(array $input, bool $requirePassword = true): array {
        return [
            'nombre'   => $this->sanitize($input['nombre'] ?? ''),
            'email'    => $this->sanitize($input['email'] ?? ''),
            'password' => trim($input['password'] ?? ''),
            'rol'      => $this->sanitize($input['rol'] ?? 'supervisor'),
            'estado'   => isset($input['estado']) ? 1 : 0,
            'password_reset_required' => isset($input['password_reset_required']) ? 1 : 0,
        ];
    }

    private function validar(array $datos, bool $requirePassword): array {
        $errores = [];
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es obligatorio.';
        }
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido.';
        }
        if (!in_array($datos['rol'], ['admin', 'supervisor', 'vendedor'], true)) {
            $errores[] = 'El rol no es válido.';
        }
        if ($requirePassword && strlen($datos['password']) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        return $errores;
    }

    private function generarPasswordTemporal(int $length = 10): string {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $password = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        return $password;
    }
}
