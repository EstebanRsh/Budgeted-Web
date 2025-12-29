<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/models/Presupuesto.php';
require_once APP_ROOT . '/app/models/Cliente.php';
require_once APP_ROOT . '/app/models/Producto.php';
require_once APP_ROOT . '/app/models/Empresa.php';

class PresupuestoController
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

        $title = $params['title'] ?? 'Presupuestos · Presupuestador';

        require APP_ROOT . '/app/views/layouts/main.php';
    }

    /**
     * Normaliza un número decimal (cantidades, precios) en formato humano a float.
     * Soporta:
     * - "1.500,50" -> 1500.50
     * - "1500,50"  -> 1500.50
     * - "1500.50"  -> 1500.50
     * - "1500"     -> 1500.00
     */
    private function normalizarDecimal(string $input): ?float
    {
        $valor = trim(str_replace(['$', ' '], '', $input));

        if ($valor === '') {
            return null;
        }

        $tieneComa  = strpos($valor, ',') !== false;
        $tienePunto = strpos($valor, '.') !== false;

        if ($tieneComa && $tienePunto) {
            // Puntos como miles, coma como decimal
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif ($tieneComa && !$tienePunto) {
            // Solo coma -> decimal latino
            $valor = str_replace(',', '.', $valor);
        } else {
            // Solo punto o solo dígitos -> se deja
        }

        if (!is_numeric($valor)) {
            return null;
        }

        return (float)$valor;
    }

    public function index(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar presupuestos por ahora.';
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
        $totalPresupuestos = Presupuesto::contarPorEmpresa($empresaId, $busqueda);
        $paginacion = calcular_paginacion($totalPresupuestos, $paginaActual, $registrosPorPagina);

        // Obtener registros de la página actual
        $presupuestos = Presupuesto::listarPorEmpresaPaginado(
            $empresaId,
            $paginacion['offset'],
            $registrosPorPagina,
            $busqueda
        );

        $this->render('presupuestos/index', [
            'title'        => 'Presupuestos · Presupuestador',
            'presupuestos' => $presupuestos,
            'busqueda'     => $busqueda,
            'paginacion'   => $paginacion,
        ]);
    }

    public function buscar(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar presupuestos por ahora.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $busqueda     = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
        $presupuestos = Presupuesto::listarPorEmpresa($empresaId, $busqueda);
        $empresa = Empresa::obtenerPorId($empresaId);

        $viewFile = APP_ROOT . '/app/views/presupuestos/_tabla.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'Vista no encontrada.';
            return;
        }

        require $viewFile;
    }

    public function ver(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden ver presupuestos por ahora.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $presupuesto = Presupuesto::obtenerConItems($id, $empresaId);
        if (!$presupuesto) {
            http_response_code(404);
            echo 'Presupuesto no encontrado.';
            return;
        }

        $this->render('presupuestos/ver', [
            'title'       => 'Presupuesto ' . ($presupuesto['numero'] ?? '') . ' · Presupuestador',
            'presupuesto' => $presupuesto,
        ]);
    }

    /**
     * Vista print-friendly del presupuesto (formato PDF en HTML para impresión).
     */
    public function print(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden imprimir presupuestos por ahora.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $presupuesto = Presupuesto::obtenerConItems($id, $empresaId);
        if (!$presupuesto) {
            http_response_code(404);
            echo 'Presupuesto no encontrado.';
            return;
        }

        // Renderizar la vista print.php SIN el layout principal (para impresión limpia)
        extract(['presupuesto' => $presupuesto]);
        require APP_ROOT . '/app/views/presupuestos/print.php';
    }

    /**
     * Exporta un presupuesto a PDF usando Dompdf.
     * Requiere tener instalado dompdf (composer require dompdf/dompdf).
     */
    public function pdf(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden exportar presupuestos por ahora.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $presupuesto = Presupuesto::obtenerConItems($id, $empresaId);
        if (!$presupuesto) {
            http_response_code(404);
            echo 'Presupuesto no encontrado.';
            return;
        }

        // Cargar dompdf si está instalado
        if (!class_exists('\Dompdf\Dompdf')) {
            $autoload = APP_ROOT . '/vendor/autoload.php';
            if (file_exists($autoload)) {
                require_once $autoload;
            }
        }

        if (!class_exists('\Dompdf\Dompdf')) {
            http_response_code(501);
            echo 'Dompdf no está instalado. Ejecuta "composer require dompdf/dompdf" en la raíz del proyecto.';
            return;
        }

        $items = $presupuesto['items'] ?? [];

        ob_start();
        require APP_ROOT . '/app/views/presupuestos/pdf.php';
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf([
            'defaultPaperSize'   => 'a4',
            'defaultPaperOrientation' => 'portrait',
            'isRemoteEnabled'    => false,
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $filename = 'presupuesto-' . ($presupuesto['numero'] ?? $presupuesto['id']) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    /**
     * Formulario de creación de presupuesto.
     */
    public function crear(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar presupuestos por ahora.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $clientes  = Cliente::listarPorEmpresa($empresaId, null);
        $productos = Producto::listarPorEmpresa($empresaId, null);

        $errores = [];
        $form = [
            'cliente_id'    => '',
            'fecha_emision' => date('Y-m-d'),
            'estado'        => 'Pendiente',
            'validez_dias'  => 15,
            'observaciones' => '',
            'items'         => [],
        ];

        $this->render('presupuestos/nuevo', [
            'title'     => 'Nuevo presupuesto · Presupuestador',
            'clientes'  => $clientes,
            'productos' => $productos,
            'form'      => $form,
            'errores'   => $errores,
        ]);
    }

    /**
     * Guardar presupuesto nuevo.
     *
     * - Valida que precio > 0.
     * - Si el ítem está vinculado a un producto existente -> actualiza su precio en catálogo.
     * - Si el ítem corresponde a un producto nuevo (sin producto_id) -> crea el producto en catálogo.
     */
    public function guardar(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden gestionar presupuestos por ahora.';
            return;
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . BASE_URL . 'presupuestos/nuevo');
            exit;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $clienteId     = (int)($_POST['cliente_id'] ?? 0);
        $fechaEmision  = trim($_POST['fecha_emision'] ?? '');
        $estado        = trim($_POST['estado'] ?? 'Pendiente');
        $validezDias   = (int)($_POST['validez_dias'] ?? 15);
        $observaciones = trim($_POST['observaciones'] ?? '');
        $itemsInput    = $_POST['items'] ?? [];

        $errores = [];

        if ($clienteId <= 0) {
            $errores[] = 'Debés seleccionar un cliente.';
        }

        if ($fechaEmision === '') {
            $errores[] = 'La fecha de emisión es obligatoria.';
        }

        $itemsPresupuesto = [];
        $productosCreados = []; // nombre normalizado => id

        foreach ($itemsInput as $row) {
            $productoIdRaw = $row['producto_id'] ?? '';
            $productoId    = $productoIdRaw !== '' ? (int)$productoIdRaw : 0;

            $descInput   = trim($row['descripcion'] ?? '');
            $cantInput   = trim($row['cantidad'] ?? '');
            $precioInput = trim($row['precio_unitario'] ?? '');

            // Fila completamente vacía -> ignorar
            if (
                $productoId === 0
                && $descInput === ''
                && $cantInput === ''
                && $precioInput === ''
            ) {
                continue;
            }

            if ($descInput === '') {
                $errores[] = 'Cada ítem debe tener una descripción.';
                continue;
            }

            if ($cantInput === '') {
                $errores[] = 'La cantidad es obligatoria para cada ítem.';
                continue;
            }

            $cantidad = $this->normalizarDecimal($cantInput);
            if ($cantidad === null) {
                $errores[] = 'La cantidad debe ser un número válido.';
                continue;
            }
            if ($cantidad <= 0) {
                $errores[] = 'La cantidad debe ser mayor a 0.';
                continue;
            }

            if ($precioInput === '') {
                $errores[] = 'El precio unitario es obligatorio para cada ítem.';
                continue;
            }

            $precio = $this->normalizarDecimal($precioInput);
            if ($precio === null) {
                $errores[] = 'El precio unitario debe ser un número válido.';
                continue;
            }
            if ($precio <= 0) {
                $errores[] = 'El precio unitario debe ser mayor a 0.';
                continue;
            }

            // Nombre para el catálogo (para productos nuevos)
            $nombreCatalogo = $descInput;

            // Si el ítem está asociado a un producto existente -> actualizar precio en catálogo
            if ($productoId > 0) {
                Producto::actualizarPrecio($productoId, $empresaId, $precio);
            } else {
                // Producto nuevo -> dar de alta en catálogo usando la descripción
                if ($nombreCatalogo !== '') {
                    $key = function_exists('mb_strtolower')
                        ? mb_strtolower($nombreCatalogo, 'UTF-8')
                        : strtolower($nombreCatalogo);

                    if (isset($productosCreados[$key])) {
                        $productoId = $productosCreados[$key];
                    } else {
                        $productoId = Producto::crear(
                            $empresaId,
                            $nombreCatalogo,
                            null,
                            $precio
                        );
                        $productosCreados[$key] = $productoId;
                    }
                }
            }

            $itemsPresupuesto[] = [
                'producto_id'     => $productoId > 0 ? $productoId : null,
                'descripcion'     => $descInput,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precio,
            ];
        }

        if (empty($itemsPresupuesto)) {
            $errores[] = 'Debés cargar al menos un ítem con descripción, cantidad y precio.';
        }

        // Si hay errores -> volvemos al formulario con los datos
        if (!empty($errores)) {
            $clientes  = Cliente::listarPorEmpresa($empresaId, null);
            $productos = Producto::listarPorEmpresa($empresaId, null);

            $form = [
                'cliente_id'    => $clienteId,
                'fecha_emision' => $fechaEmision !== '' ? $fechaEmision : date('Y-m-d'),
                'estado'        => $estado !== '' ? $estado : 'Pendiente',
                'validez_dias'  => $validezDias > 0 ? $validezDias : 15,
                'observaciones' => $observaciones,
                'items'         => $itemsInput,
            ];

            $this->render('presupuestos/nuevo', [
                'title'     => 'Nuevo presupuesto · Presupuestador',
                'clientes'  => $clientes,
                'productos' => $productos,
                'form'      => $form,
                'errores'   => $errores,
            ]);
            return;
        }

        // Creamos el presupuesto y sus ítems
        $presupuestoId = Presupuesto::crearConItems(
            $empresaId,
            $clienteId,
            $fechaEmision,
            $estado !== '' ? $estado : 'Pendiente',
            $validezDias > 0 ? $validezDias : 15,
            $observaciones !== '' ? $observaciones : null,
            $itemsPresupuesto
        );

        audit_log('create', 'Presupuesto', $presupuestoId);
        flash('success', 'Presupuesto creado correctamente.');

        header('Location: ' . BASE_URL . 'presupuestos/' . $presupuestoId);
        exit;
    }

    /**
     * Formulario para editar un presupuesto.
     */
    public function editar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden editar presupuestos.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $presupuesto = Presupuesto::obtenerConItems($id, $empresaId);
        if (!$presupuesto) {
            http_response_code(404);
            echo 'Presupuesto no encontrado.';
            return;
        }

        $clientes  = Cliente::listarPorEmpresa($empresaId, null);
        $productos = Producto::listarPorEmpresa($empresaId, null);

        $form = [
            'cliente_id'    => $presupuesto['cliente_id'],
            'fecha_emision' => $presupuesto['fecha_emision'],
            'estado'        => $presupuesto['estado'],
            'validez_dias'  => $presupuesto['validez_dias'],
            'observaciones' => $presupuesto['observaciones'],
            'items'         => [],
        ];

        // Llenar items del formulario desde presupuesto actual
        foreach ($presupuesto['items'] as $item) {
            $form['items'][] = [
                'producto_id'     => $item['producto_id'] ?? '',
                'descripcion'     => $item['descripcion'],
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
            ];
        }

        $this->render('presupuestos/editar', [
            'title'       => 'Editar presupuesto · Presupuestador',
            'presupuesto' => $presupuesto,
            'clientes'    => $clientes,
            'productos'   => $productos,
            'form'        => $form,
            'errores'     => [],
        ]);
    }

    /**
     * Actualizar presupuesto.
     */
    public function actualizar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden editar presupuestos.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método no permitido.';
            return;
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . BASE_URL . 'presupuestos/' . $id . '/editar');
            exit;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $presupuesto = Presupuesto::obtenerPorId($id, $empresaId);
        if (!$presupuesto) {
            http_response_code(404);
            echo 'Presupuesto no encontrado.';
            return;
        }

        $clienteId     = (int)($_POST['cliente_id'] ?? 0);
        $fechaEmision  = trim($_POST['fecha_emision'] ?? '');
        $estado        = trim($_POST['estado'] ?? 'Pendiente');
        $validezDias   = (int)($_POST['validez_dias'] ?? 15);
        $observaciones = trim($_POST['observaciones'] ?? '');
        $itemsInput    = $_POST['items'] ?? [];

        $errores = [];

        if ($clienteId <= 0) {
            $errores[] = 'Debés seleccionar un cliente.';
        }

        if ($fechaEmision === '') {
            $errores[] = 'La fecha de emisión es obligatoria.';
        }

        $itemsPresupuesto = [];

        foreach ($itemsInput as $row) {
            $productoIdRaw = $row['producto_id'] ?? '';
            $productoId    = $productoIdRaw !== '' ? (int)$productoIdRaw : 0;

            $descInput   = trim($row['descripcion'] ?? '');
            $cantInput   = trim($row['cantidad'] ?? '');
            $precioInput = trim($row['precio_unitario'] ?? '');

            // Fila completamente vacía -> ignorar
            if (
                $productoId === 0
                && $descInput === ''
                && $cantInput === ''
                && $precioInput === ''
            ) {
                continue;
            }

            if ($descInput === '') {
                $errores[] = 'Cada ítem debe tener una descripción.';
                continue;
            }

            if ($cantInput === '') {
                $errores[] = 'La cantidad es obligatoria para cada ítem.';
                continue;
            }

            $cantidad = $this->normalizarDecimal($cantInput);
            if ($cantidad === null || $cantidad <= 0) {
                $errores[] = 'La cantidad debe ser un número válido y mayor a 0.';
                continue;
            }

            if ($precioInput === '') {
                $errores[] = 'El precio unitario es obligatorio para cada ítem.';
                continue;
            }

            $precio = $this->normalizarDecimal($precioInput);
            if ($precio === null || $precio <= 0) {
                $errores[] = 'El precio unitario debe ser un número válido y mayor a 0.';
                continue;
            }

            $itemsPresupuesto[] = [
                'producto_id'     => $productoId > 0 ? $productoId : null,
                'descripcion'     => $descInput,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precio,
            ];
        }

        if (empty($itemsPresupuesto)) {
            $errores[] = 'Debés cargar al menos un ítem con descripción, cantidad y precio.';
        }

        // Si hay errores -> volver al formulario
        if (!empty($errores)) {
            $clientes  = Cliente::listarPorEmpresa($empresaId, null);
            $productos = Producto::listarPorEmpresa($empresaId, null);

            $presupuestoConItems = Presupuesto::obtenerConItems($id, $empresaId);

            $form = [
                'cliente_id'    => $clienteId,
                'fecha_emision' => $fechaEmision !== '' ? $fechaEmision : date('Y-m-d'),
                'estado'        => $estado !== '' ? $estado : 'Pendiente',
                'validez_dias'  => $validezDias > 0 ? $validezDias : 15,
                'observaciones' => $observaciones,
                'items'         => $itemsInput,
            ];

            $this->render('presupuestos/editar', [
                'title'       => 'Editar presupuesto · Presupuestador',
                'presupuesto' => $presupuestoConItems,
                'clientes'    => $clientes,
                'productos'   => $productos,
                'form'        => $form,
                'errores'     => $errores,
            ]);
            return;
        }

        // Actualizar datos del presupuesto
        try {
            Presupuesto::actualizar($id, $empresaId, [
                'cliente_id'    => $clienteId,
                'fecha_emision' => $fechaEmision,
                'estado'        => $estado !== '' ? $estado : 'Pendiente',
                'validez_dias'  => $validezDias > 0 ? $validezDias : 15,
                'observaciones' => $observaciones !== '' ? $observaciones : null,
            ]);

            // Actualizar ítems
            Presupuesto::actualizarItems($id, $itemsPresupuesto);

            audit_log('update', 'Presupuesto', $id);
            flash('success', 'Presupuesto actualizado correctamente.');
            header('Location: ' . BASE_URL . 'presupuestos/' . $id);
            exit;
        } catch (Exception $e) {
            flash('error', 'Error al actualizar el presupuesto: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'presupuestos/' . $id . '/editar');
            exit;
        }
    }

    /**
     * Duplicar presupuesto.
     */
    public function duplicar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden duplicar presupuestos.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método no permitido.';
            return;
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . BASE_URL . 'presupuestos/' . $id);
            exit;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $presupuesto = Presupuesto::obtenerPorId($id, $empresaId);
        if (!$presupuesto) {
            http_response_code(404);
            echo 'Presupuesto no encontrado.';
            return;
        }

        try {
            $presupuestoNuevoId = Presupuesto::duplicar($id, $empresaId);
            audit_log('duplicate', 'Presupuesto', $presupuestoNuevoId);
            flash('success', 'Presupuesto duplicado correctamente.');
            header('Location: ' . BASE_URL . 'presupuestos/' . $presupuestoNuevoId);
            exit;
        } catch (Exception $e) {
            flash('error', 'Error al duplicar el presupuesto: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'presupuestos/' . $id);
            exit;
        }
    }

    /**
     * Eliminar presupuesto.
     */
    public function eliminar(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden eliminar presupuestos.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método no permitido.';
            return;
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . BASE_URL . 'presupuestos/' . $id);
            exit;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $presupuesto = Presupuesto::obtenerPorId($id, $empresaId);
        if (!$presupuesto) {
            http_response_code(404);
            echo 'Presupuesto no encontrado.';
            return;
        }

        try {
            Presupuesto::eliminar($id, $empresaId);
            audit_log('delete', 'Presupuesto', $id);
            flash('success', 'Presupuesto eliminado correctamente.');
            header('Location: ' . BASE_URL . 'presupuestos');
            exit;
        } catch (Exception $e) {
            flash('error', 'Error al eliminar el presupuesto: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'presupuestos/' . $id);
            exit;
        }
    }

    /**
     * Exporta presupuestos a Excel.
     */
    public function exportarExcel(): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden exportar presupuestos.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $busqueda = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
        $presupuestos = Presupuesto::listarPorEmpresa($empresaId, $busqueda);

        // Crear el Excel
        require_once APP_ROOT . '/vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Presupuestos');

        $empresaNombre = $empresa['nombre'] ?? 'Mi Empresa';
        $empresaCuit    = $empresa['cuit'] ?? '';
        $empresaEmail   = $empresa['email'] ?? '';

        // Cabecera tipo reporte
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');
        $sheet->setCellValue('A1', $empresaNombre);
        $sheet->setCellValue('A2', 'Listado de Presupuestos');
        $sheet->setCellValue('A3', 'Generado: ' . date('d/m/Y H:i') . ($empresaCuit ? ' · CUIT: ' . $empresaCuit : '') . ($empresaEmail ? ' · ' . $empresaEmail : ''));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Encabezados tabla
        $headers = ['N° Presupuesto', 'Cliente', 'Fecha emisión', 'Estado', 'Validez (días)', 'Total', 'Observaciones'];
        $headerRow = 5;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $col++;
        }

        $headerRange = 'A' . $headerRow . ':G' . $headerRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0D6EFD');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($headerRow)->setRowHeight(20);

        // Datos
        $row = $headerRow + 1;
        foreach ($presupuestos as $p) {
            $fecha = $p['fecha_emision'] ?? '';
            $fechaFormateada = $fecha ? date('d/m/Y', strtotime((string)$fecha)) : '';

            $sheet->setCellValue('A' . $row, $p['numero'] ?? '');
            $sheet->setCellValue('B' . $row, $p['cliente_nombre'] ?? '');
            $sheet->setCellValue('C' . $row, $fechaFormateada);
            $sheet->setCellValue('D' . $row, $p['estado'] ?? '');
            $sheet->setCellValue('E' . $row, (int)($p['validez_dias'] ?? 0));
            $sheet->setCellValue('F' . $row, (float)($p['total_general'] ?? 0));
            $sheet->setCellValue('G' . $row, $p['observaciones'] ?? '');

            // Formato de moneda y alineaciones
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Zebra
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF2F5F9');
            }

            $row++;
        }

        $lastDataRow = $row - 1;

        // Resumen
        $summaryRow = $lastDataRow + 1;
        $sheet->mergeCells('A' . $summaryRow . ':E' . $summaryRow);
        $sheet->setCellValue('A' . $summaryRow, 'Total presupuestos');
        $sheet->setCellValue('F' . $summaryRow, '=SUM(F' . ($headerRow + 1) . ':F' . $lastDataRow . ')');
        $sheet->getStyle('A' . $summaryRow . ':G' . $summaryRow)->getFont()->setBold(true);
        $sheet->getStyle('F' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0.00');

        // Bordes
        $sheet->getStyle('A' . $headerRow . ':G' . $summaryRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Autoajustar ancho de columnas
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Filtros y freeze
        $sheet->setAutoFilter('A' . $headerRow . ':G' . $headerRow);
        $sheet->freezePane('A' . ($headerRow + 1));

        // Enviar archivo
        $filename = 'presupuestos_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Exporta un presupuesto individual a Excel.
     */
    public function excel(int $id): void
    {
        require_login();

        if (is_superadmin()) {
            http_response_code(403);
            echo 'Sólo los usuarios estándar pueden exportar presupuestos.';
            return;
        }

        $empresaId = current_empresa_id();
        if ($empresaId === null) {
            http_response_code(400);
            echo 'No hay empresa asociada al usuario actual.';
            return;
        }

        $presupuesto = Presupuesto::obtenerConItems($id, $empresaId);
        if (!$presupuesto) {
            http_response_code(404);
            echo 'Presupuesto no encontrado.';
            return;
        }

        // Crear el Excel
        require_once APP_ROOT . '/vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Presupuesto');

        $numero      = (string)($presupuesto['numero'] ?? '');
        $cliente     = (string)($presupuesto['cliente_nombre'] ?? '');
        $fecha       = $presupuesto['fecha_emision'] ?? '';
        $fechaFmt    = $fecha ? date('d/m/Y', strtotime((string)$fecha)) : '';
        $estado      = (string)($presupuesto['estado'] ?? '');
        $validez     = (int)($presupuesto['validez_dias'] ?? 0);
        $totalGen    = (float)($presupuesto['total_general'] ?? 0);
        $obs         = (string)($presupuesto['observaciones'] ?? '');
        $empresaNom  = (string)($presupuesto['empresa_nombre'] ?? 'Mi Empresa');
        $empresaCuit = (string)($presupuesto['empresa_cuit'] ?? '');
        $empresaMail = (string)($presupuesto['empresa_email'] ?? '');

        // Cabecera tipo documento
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A1', $empresaNom . ($empresaCuit ? ' · CUIT: ' . $empresaCuit : ''));
        $sheet->setCellValue('A2', 'Presupuesto ' . $numero);
        $sheet->setCellValue('D3', 'Generado: ' . date('d/m/Y H:i'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1:D2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Bloque de datos
        $infoRowStart = 4;
        $sheet->setCellValue('A' . $infoRowStart, 'Cliente');
        $sheet->setCellValue('B' . $infoRowStart, $cliente);
        $sheet->setCellValue('C' . $infoRowStart, 'Fecha emisión');
        $sheet->setCellValue('D' . $infoRowStart, $fechaFmt);

        $sheet->setCellValue('A' . ($infoRowStart + 1), 'Estado');
        $sheet->setCellValue('B' . ($infoRowStart + 1), $estado);
        $sheet->setCellValue('C' . ($infoRowStart + 1), 'Validez (días)');
        $sheet->setCellValue('D' . ($infoRowStart + 1), $validez > 0 ? $validez : '');

        $sheet->setCellValue('A' . ($infoRowStart + 2), 'Email empresa');
        $sheet->setCellValue('B' . ($infoRowStart + 2), $empresaMail);
        $sheet->setCellValue('C' . ($infoRowStart + 2), 'Total general');
        $sheet->setCellValue('D' . ($infoRowStart + 2), $totalGen);
        $sheet->getStyle('D' . ($infoRowStart + 2))->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $infoRowStart . ':D' . ($infoRowStart + 2))->getFont()->setBold(false);
        $sheet->getStyle('A' . $infoRowStart . ':D' . ($infoRowStart + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Observaciones
        $sheet->mergeCells('A' . ($infoRowStart + 3) . ':D' . ($infoRowStart + 3));
        $sheet->setCellValue('A' . ($infoRowStart + 3), 'Observaciones: ' . ($obs ?: ''));

        // Encabezado de ítems
        $headerRow = $infoRowStart + 5;
        $sheet->setCellValue('A' . $headerRow, 'Descripción');
        $sheet->setCellValue('B' . $headerRow, 'Cantidad');
        $sheet->setCellValue('C' . $headerRow, 'Precio unitario');
        $sheet->setCellValue('D' . $headerRow, 'Total');

        $headerRange = 'A' . $headerRow . ':D' . $headerRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0D6EFD');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($headerRow)->setRowHeight(20);

        // Ítems
        $row = $headerRow + 1;
        foreach (($presupuesto['items'] ?? []) as $item) {
            $sheet->setCellValue('A' . $row, (string)($item['descripcion'] ?? ''));
            $sheet->setCellValue('B' . $row, (float)($item['cantidad'] ?? 0));
            $sheet->setCellValue('C' . $row, (float)($item['precio_unitario'] ?? 0));
            $sheet->setCellValue('D' . $row, (float)($item['total'] ?? 0));

            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':D' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF2F5F9');
            }

            $row++;
        }

        // Total general
        $sheet->setCellValue('C' . $row, 'Total general');
        $sheet->setCellValue('D' . $row, $totalGen);
        $sheet->getStyle('C' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        // Bordes
        $lastRow = $row;
        $sheet->getStyle('A' . $headerRow . ':D' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Auto ancho
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze & filtros
        $sheet->freezePane('A' . ($headerRow + 1));
        $sheet->setAutoFilter('A' . $headerRow . ':D' . $headerRow);

        $filename = 'presupuesto_' . ($presupuesto['numero'] ?? $id) . '_' . date('Y-m-d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
