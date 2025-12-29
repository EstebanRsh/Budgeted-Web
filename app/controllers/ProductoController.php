<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/models/Producto.php';

class ProductoController
{
    /**
     * Renderiza una vista dentro del layout principal.
     */
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

        $title = $params['title'] ?? 'Productos · Presupuestador';

        require APP_ROOT . '/app/views/layouts/main.php';
    }

    /**
     * Normaliza un precio en formato "humano" a float.
     *
     * Soporta:
     * - "1.500,50"  -> 1500.50
     * - "1500,50"   -> 1500.50
     * - "1500.50"   -> 1500.50
     * - "1500"      -> 1500.00
     *
     * Devuelve null si no es un número válido.
     */
    private function normalizarPrecio(string $input): ?float
    {
        // Quitamos símbolos comunes
        $valor = trim(str_replace(['$', ' '], '', $input));

        if ($valor === '') {
            return null;
        }

        $tieneComa  = strpos($valor, ',') !== false;
        $tienePunto = strpos($valor, '.') !== false;

        if ($tieneComa && $tienePunto) {
            // Asumimos: puntos como miles, coma como decimal -> 1.500,50
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif ($tieneComa && !$tienePunto) {
            // Solo coma -> decimal latino -> 1500,50
            $valor = str_replace(',', '.', $valor);
        } else {
            // Solo punto o solo dígitos -> dejamos como está (1500.50 o 1500)
        }

        if (!is_numeric($valor)) {
            return null;
        }

        return (float)$valor;
    }

    /**
     * Listado principal de productos.
     */
    public function index(): void
    {
        require_login();

        // Por ahora, sólo usuarios estándar gestionan productos
        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar productos.';
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
        $totalProductos = Producto::contarPorEmpresa($empresaId, $busqueda);
        $paginacion = calcular_paginacion($totalProductos, $paginaActual, $registrosPorPagina);

        // Obtener registros de la página actual
        $productos = Producto::listarPorEmpresaPaginado(
            $empresaId,
            $paginacion['offset'],
            $registrosPorPagina,
            $busqueda
        );

        $this->render('productos/index', [
            'title'      => 'Productos · Presupuestador',
            'productos'  => $productos,
            'busqueda'   => $busqueda,
            'paginacion' => $paginacion,
        ]);
    }

    /**
     * Búsqueda dinámica con htmx (actualiza sólo la tabla).
     */
    public function buscar(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar productos.';
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

        $totalProductos = Producto::contarPorEmpresa($empresaId, $busqueda);
        $paginacion = calcular_paginacion($totalProductos, $paginaActual, $registrosPorPagina);

        $productos = Producto::listarPorEmpresaPaginado(
            $empresaId,
            $paginacion['offset'],
            $registrosPorPagina,
            $busqueda
        );

        $viewFile = APP_ROOT . '/app/views/productos/_tabla.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'Vista no encontrada.';
            return;
        }

        require $viewFile;
    }

    /**
     * Endpoint htmx para actualizar sólo el precio de un producto (inline).
     */
    public function actualizarPrecio(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden editar precios.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        // Aceptamos "precio_unitario" o "precio_base"
        $valor = $_POST['precio_unitario'] ?? $_POST['precio_base'] ?? null;

        if ($valor === null || trim((string)$valor) === '') {
            http_response_code(400);
            echo 'El precio es obligatorio.';
            return;
        }

        $precio = $this->normalizarPrecio((string)$valor);

        if ($precio === null) {
            http_response_code(400);
            echo 'El precio no es válido.';
            return;
        }

        $producto = Producto::actualizarPrecio($id, $empresaId, $precio);

        if (!$producto) {
            http_response_code(404);
            echo 'Producto no encontrado.';
            return;
        }

        $viewFile = APP_ROOT . '/app/views/productos/_fila.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'Vista no encontrada.';
            return;
        }

        $productoFila = $producto;
        require $viewFile;
    }

    /**
     * Mostrar formulario de creación.
     */
    public function crear(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar productos.';
            return;
        }

        $errores  = [];
        $producto = [
            'nombre'          => '',
            'descripcion'     => '',
            'precio_unitario' => '',
        ];

        $this->render('productos/nuevo', [
            'title'    => 'Nuevo producto · Presupuestador',
            'producto' => $producto,
            'errores'  => $errores,
        ]);
    }

    /**
     * Guardar nuevo producto.
     */
    public function guardar(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar productos.';
            return;
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . BASE_URL . 'productos/nuevo');
            exit;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precioInput = $_POST['precio_unitario'] ?? '';

        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        $precio = $this->normalizarPrecio((string)$precioInput);

        if ($precio === null) {
            $errores[] = 'El precio debe ser un número válido.';
        }

        if (!empty($errores)) {
            $producto = [
                'nombre'          => $nombre,
                'descripcion'     => $descripcion,
                'precio_unitario' => $precioInput,
            ];

            $this->render('productos/nuevo', [
                'title'    => 'Nuevo producto · Presupuestador',
                'producto' => $producto,
                'errores'  => $errores,
            ]);
            return;
        }

        Producto::crear($empresaId, $nombre, $descripcion ?: null, $precio);

        header('Location: ' . BASE_URL . 'productos');
        exit;
    }

    /**
     * Mostrar formulario de edición.
     */
    public function editar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar productos.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $producto = Producto::obtenerPorId($id, $empresaId);
        if (!$producto) {
            http_response_code(404);
            echo 'Producto no encontrado.';
            return;
        }

        $errores = [];

        $this->render('productos/editar', [
            'title'    => 'Editar producto · Presupuestador',
            'producto' => $producto,
            'errores'  => $errores,
        ]);
    }

    /**
     * Actualizar producto existente.
     */
    public function actualizar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar productos.';
            return;
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . BASE_URL . 'productos/' . $id . '/editar');
            exit;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precioInput = $_POST['precio_unitario'] ?? '';

        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        $precio = $this->normalizarPrecio((string)$precioInput);

        if ($precio === null) {
            $errores[] = 'El precio debe ser un número válido.';
        }

        if (!empty($errores)) {
            $producto = [
                'id'              => $id,
                'nombre'          => $nombre,
                'descripcion'     => $descripcion,
                'precio_unitario' => $precioInput,
            ];

            $this->render('productos/editar', [
                'title'    => 'Editar producto · Presupuestador',
                'producto' => $producto,
                'errores'  => $errores,
            ]);
            return;
        }

        Producto::actualizar($id, $empresaId, $nombre, $descripcion ?: null, $precio);

        header('Location: ' . BASE_URL . 'productos');
        exit;
    }

    /**
     * Eliminar producto.
     */
    public function eliminar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar productos.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        Producto::eliminar($id, $empresaId);

        header('Location: ' . BASE_URL . 'productos');
        exit;
    }
}
