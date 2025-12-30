<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/models/Usuario.php';
require_once APP_ROOT . '/app/services/MailService.php';

class AuthController
{
    private function render(string $view, array $params = []): void
    {
        extract($params);
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("Vista no encontrada: {$viewFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $title = $params['title'] ?? 'Iniciar sesión · Presupuestador';

        require APP_ROOT . '/app/views/layouts/main.php';
    }

    public function loginForm(): void
    {
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $this->render('auth/login', [
            'title' => 'Iniciar sesión · Presupuestador',
            'error' => $_SESSION['login_error'] ?? null,
        ]);

        unset($_SESSION['login_error']);
    }

    /**
     * Formulario de registro.
     */
    public function registroForm(): void
    {
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $this->render('auth/registro', [
            'title' => 'Registro · Presupuestador',
            'error' => $_SESSION['registro_error'] ?? null,
        ]);

        unset($_SESSION['registro_error']);
    }

    /**
     * Procesar registro de usuario.
     */
    public function registro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método no permitido.');
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            $_SESSION['registro_error'] = 'Token de seguridad inválido. Intenta de nuevo.';
            header('Location: ' . BASE_URL . 'registro');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $passwordConfirm = trim($_POST['password_confirm'] ?? '');

        // Validaciones
        if (empty($nombre) || empty($email) || empty($password)) {
            $_SESSION['registro_error'] = 'Todos los campos son obligatorios.';
            header('Location: ' . BASE_URL . 'registro');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['registro_error'] = 'Email inválido.';
            header('Location: ' . BASE_URL . 'registro');
            exit;
        }

        // VALIDACIÓN DESACTIVADA TEMPORALMENTE
        // if (strlen($password) < 6) {
        //     $_SESSION['registro_error'] = 'La contraseña debe tener al menos 6 caracteres.';
        //     header('Location: ' . BASE_URL . 'registro');
        //     exit;
        // }

        if ($password !== $passwordConfirm) {
            $_SESSION['registro_error'] = 'Las contraseñas no coinciden.';
            header('Location: ' . BASE_URL . 'registro');
            exit;
        }

        // Verificar si el email ya existe
        if (Usuario::existeEmail($email)) {
            $_SESSION['registro_error'] = 'El email ya está registrado.';
            header('Location: ' . BASE_URL . 'registro');
            exit;
        }

        try {
            // Crear usuario en espera (pendiente de aprobación)
            $usuarioId = Usuario::crear([
                'nombre' => $nombre,
                'email' => $email,
                'password' => $password,
                'empresa_id' => null,
                'estado' => 'en_espera',
            ]);

            if ($usuarioId) {
                // Enviar email de bienvenida (no bloquear si falla)
                try {
                    $usuario = Usuario::obtenerPorId($usuarioId);
                    if ($usuario) {
                        MailService::sendBienvenida($usuario);
                    }
                } catch (Exception $mailError) {
                    error_log("Error al enviar email de bienvenida: " . $mailError->getMessage());
                }
                
                $_SESSION['registro_success'] = 'Registro exitoso. Tu cuenta está pendiente de aprobación por un administrador.';
                header('Location: ' . BASE_URL . 'login');
                exit;
            } else {
                $_SESSION['registro_error'] = 'Error al crear el usuario.';
                header('Location: ' . BASE_URL . 'registro');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['registro_error'] = 'Error del sistema: ' . $e->getMessage();
            header('Location: ' . BASE_URL . 'registro');
            exit;
        }
    }

    public function login(): void
    {
        // Verificar token CSRF
        if (!verify_csrf_token()) {
            $_SESSION['login_error'] = 'Token de seguridad inválido. Intenta de nuevo.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['login_error'] = 'Debes ingresar email y contraseña.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Rate limiting básico de login
        $maxIntentos = 5;
        $ventanaSegundos = 900; // 15 minutos
        $bloqueoSegundos = 300; // 5 minutos de cooldown

        $ahora = time();
        $intentos = $_SESSION['login_attempts'] ?? [];
        // Limpiar intentos antiguos
        $intentos = array_filter($intentos, fn($ts) => ($ahora - $ts) <= $ventanaSegundos);

        if (count($intentos) >= $maxIntentos) {
            $ultimo = max($intentos);
            $restante = max(0, $bloqueoSegundos - ($ahora - $ultimo));
            if ($restante > 0) {
                $_SESSION['login_error'] = 'Demasiados intentos. Esperá ' . ceil($restante / 60) . ' minutos e intentá de nuevo.';
                $_SESSION['login_attempts'] = $intentos;
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
        }

        $user = Usuario::verificarCredenciales($email, $password);

        if (!$user) {
            $_SESSION['login_error'] = 'Email o contraseña incorrectos.';
            $intentos[] = $ahora;
            $_SESSION['login_attempts'] = $intentos;
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Login exitoso, reiniciar contador
        unset($_SESSION['login_attempts']);

        // Validar estado del usuario (solo usuarios no-superadmin)
        if (!$user['is_superadmin']) {
            if ($user['estado'] === 'en_espera') {
                $_SESSION['login_error'] = 'Tu cuenta está pendiente de aprobación.';
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            if ($user['estado'] === 'desactivado') {
                $_SESSION['login_error'] = 'Tu cuenta ha sido desactivada. Contacta al administrador.';
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
        }

        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);

        // Seteamos datos mínimos de sesión
        $_SESSION['user_id']       = (int)$user['id'];
        $_SESSION['user_name']     = $user['nombre'] ?? '';
        $_SESSION['is_superadmin'] = (bool)$user['is_superadmin'];
        $_SESSION['empresa_id']    = $user['empresa_id'] !== null ? (int)$user['empresa_id'] : null;

        header('Location: ' . BASE_URL);
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}
