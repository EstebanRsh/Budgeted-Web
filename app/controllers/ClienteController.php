<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/models/Cliente.php';

class ClienteController
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

        $title = $params['title'] ?? 'Clientes · Presupuestador';

        require APP_ROOT . '/app/views/layouts/main.php';
    }

    public function index(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar clientes.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $busqueda = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
        $paginaActual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $registrosPorPagina = 10;

        // Obtener total y calcular paginación
        $totalClientes = Cliente::contarPorEmpresa($empresaId, $busqueda);
        $paginacion = calcular_paginacion($totalClientes, $paginaActual, $registrosPorPagina);

        // Obtener registros de la página actual
        $clientes = Cliente::listarPorEmpresaPaginado(
            $empresaId,
            $paginacion['offset'],
            $registrosPorPagina,
            $busqueda
        );

        $this->render('clientes/index', [
            'title'      => 'Clientes · Presupuestador',
            'clientes'   => $clientes,
            'busqueda'   => $busqueda,
            'paginacion' => $paginacion,
        ]);
    }

    public function buscar(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar clientes.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $busqueda = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
        $paginaActual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
        $registrosPorPagina = 10;

        $totalClientes = Cliente::contarPorEmpresa($empresaId, $busqueda);
        $paginacion = calcular_paginacion($totalClientes, $paginaActual, $registrosPorPagina);

        $clientes = Cliente::listarPorEmpresaPaginado(
            $empresaId,
            $paginacion['offset'],
            $registrosPorPagina,
            $busqueda
        );

        $viewFile = APP_ROOT . '/app/views/clientes/_tabla.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'Vista no encontrada.';
            return;
        }

        require $viewFile;
    }

    public function crear(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar clientes.';
            return;
        }

        $errores = [];
        $cliente = [
            'nombre'        => '',
            'cuit_dni'      => '',
            'condicion_iva' => '',
            'domicilio'     => '',
            'telefono'      => '',
            'email'         => '',
            'observaciones' => '',
            'activo'        => 1,
        ];

        $this->render('clientes/nuevo', [
            'title'   => 'Nuevo cliente · Presupuestador',
            'cliente' => $cliente,
            'errores' => $errores,
        ]);
    }

    public function guardar(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar clientes.';
            return;
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . BASE_URL . 'clientes/nuevo');
            exit;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $nombre        = trim($_POST['nombre'] ?? '');
        $cuitDni       = trim($_POST['cuit_dni'] ?? '');
        $condicionIva  = trim($_POST['condicion_iva'] ?? '');
        $domicilio     = trim($_POST['domicilio'] ?? '');
        $telefono      = trim($_POST['telefono'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');
        $activo        = isset($_POST['activo']) ? true : false;

        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        if ($cuitDni !== '' && !validar_cuit_dni($cuitDni)) {
            $errores[] = 'El CUIT/DNI no tiene un formato válido. Ingresá 8 dígitos (DNI) o 11 dígitos (CUIT).';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no tiene un formato válido.';
        }

        if (!empty($errores)) {
            $cliente = [
                'nombre'        => $nombre,
                'cuit_dni'      => $cuitDni,
                'condicion_iva' => $condicionIva,
                'domicilio'     => $domicilio,
                'telefono'      => $telefono,
                'email'         => $email,
                'observaciones' => $observaciones,
                'activo'        => $activo ? 1 : 0,
            ];

            $this->render('clientes/nuevo', [
                'title'   => 'Nuevo cliente · Presupuestador',
                'cliente' => $cliente,
                'errores' => $errores,
            ]);
            return;
        }

        Cliente::crear(
            $empresaId,
            $nombre,
            $cuitDni ?: null,
            $condicionIva ?: null,
            $domicilio ?: null,
            $telefono ?: null,
            $email ?: null,
            $observaciones ?: null,
            $activo
        );

        header('Location: ' . BASE_URL . 'clientes');
        exit;
    }

    public function editar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar clientes.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $cliente = Cliente::obtenerPorId($id, $empresaId);
        if (!$cliente) {
            http_response_code(404);
            echo 'Cliente no encontrado.';
            return;
        }

        $errores = [];

        $this->render('clientes/editar', [
            'title'   => 'Editar cliente · Presupuestador',
            'cliente' => $cliente,
            'errores' => $errores,
        ]);
    }

    public function actualizar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar clientes.';
            return;
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . BASE_URL . 'clientes/' . $id . '/editar');
            exit;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $nombre        = trim($_POST['nombre'] ?? '');
        $cuitDni       = trim($_POST['cuit_dni'] ?? '');
        $condicionIva  = trim($_POST['condicion_iva'] ?? '');
        $domicilio     = trim($_POST['domicilio'] ?? '');
        $telefono      = trim($_POST['telefono'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');
        $activo        = isset($_POST['activo']) ? true : false;

        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        if ($cuitDni !== '' && !validar_cuit_dni($cuitDni)) {
            $errores[] = 'El CUIT/DNI no tiene un formato válido. Ingresá 8 dígitos (DNI) o 11 dígitos (CUIT).';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no tiene un formato válido.';
        }

        if (!empty($errores)) {
            $cliente = [
                'id'            => $id,
                'nombre'        => $nombre,
                'cuit_dni'      => $cuitDni,
                'condicion_iva' => $condicionIva,
                'domicilio'     => $domicilio,
                'telefono'      => $telefono,
                'email'         => $email,
                'observaciones' => $observaciones,
                'activo'        => $activo ? 1 : 0,
            ];

            $this->render('clientes/editar', [
                'title'   => 'Editar cliente · Presupuestador',
                'cliente' => $cliente,
                'errores' => $errores,
            ]);
            return;
        }

        Cliente::actualizar(
            $id,
            $empresaId,
            $nombre,
            $cuitDni ?: null,
            $condicionIva ?: null,
            $domicilio ?: null,
            $telefono ?: null,
            $email ?: null,
            $observaciones ?: null,
            $activo
        );

        header('Location: ' . BASE_URL . 'clientes');
        exit;
    }

    public function eliminar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar clientes.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        Cliente::eliminar($id, $empresaId);

        header('Location: ' . BASE_URL . 'clientes');
        exit;
    }
}
