<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/models/Empresa.php';

class EmpresaController
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

        $title = $params['title'] ?? 'Mi Empresa · Presupuestador';

        require APP_ROOT . '/app/views/layouts/main.php';
    }

    /**
     * Ver datos de la empresa del usuario logueado.
     */
    public function index(): void
    {
        require_login();

        $empresaId = current_empresa_id();
        
        // Si no tiene empresa, redirigir a crear
        if (!$empresaId) {
            header('Location: ' . BASE_URL . 'empresa/editar');
            exit;
        }

        $empresa = Empresa::obtenerPorId($empresaId);
        if (!$empresa) {
            http_response_code(404);
            die('Empresa no encontrada.');
        }

        $this->render('empresa/index', [
            'title'   => 'Mi Empresa · Presupuestador',
            'empresa' => $empresa,
        ]);
    }

    /**
     * Muestra el formulario de edición/creación.
     */
    public function editar(): void
    {
        require_login();

        $empresaId = current_empresa_id();
        
        // Si no tiene empresa, mostrar formulario vacío
        if (!$empresaId) {
            $empresa = [
                'nombre' => '',
                'cuit' => '',
                'email' => $_SESSION['user_email'] ?? '',
                'domicilio' => '',
                'telefono' => '',
                'web' => '',
                'condicion_iva' => '',
                'inicio_actividades' => '',
                'ingresos_brutos' => '',
                'logo_path' => null,
            ];
        } else {
            $empresa = Empresa::obtenerPorId($empresaId);
            if (!$empresa) {
                http_response_code(404);
                die('Empresa no encontrada.');
            }
        }

        $this->render('empresa/editar', [
            'title'   => $empresaId ? 'Editar Mi Empresa · Presupuestador' : 'Crear Mi Empresa · Presupuestador',
            'empresa' => $empresa,
            'esNueva' => !$empresaId,
        ]);
    }

    /**
     * Procesa la actualización/creación de los datos de la empresa.
     */
    public function actualizar(): void
    {
        require_login();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método no permitido.');
        }

        // Verificar token CSRF
        if (!verify_csrf_token()) {
            flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            header('Location: ' . BASE_URL . 'empresa/editar');
            exit;
        }

        $empresaId = current_empresa_id();
        $esNueva = !$empresaId;
        
        // Si ya tiene empresa, verificar que existe
        if (!$esNueva) {
            $empresa = Empresa::obtenerPorId($empresaId);
            if (!$empresa) {
                http_response_code(404);
                die('Empresa no encontrada.');
            }
        }

        // Recopilar datos
        $datos = [
            'nombre'               => trim($_POST['nombre'] ?? ''),
            'cuit'                 => trim($_POST['cuit'] ?? ''),
            'email'                => trim($_POST['email'] ?? ''),
            'domicilio'            => trim($_POST['domicilio'] ?? ''),
            'telefono'             => trim($_POST['telefono'] ?? ''),
            'web'                  => trim($_POST['web'] ?? ''),
            'condicion_iva'        => trim($_POST['condicion_iva'] ?? ''),
            'inicio_actividades'   => !empty($_POST['inicio_actividades']) ? $_POST['inicio_actividades'] : null,
            'ingresos_brutos'      => trim($_POST['ingresos_brutos'] ?? ''),
        ];

        // Validar campos obligatorios
        if (empty($datos['nombre'])) {
            flash('error', 'El nombre de la empresa es obligatorio.');
            header('Location: ' . BASE_URL . 'empresa/editar');
            exit;
        }

        // Procesar logo si se subió
        if (!empty($_FILES['logo']['tmp_name'])) {
            // Si es nueva, usar ID temporal 0, luego se actualizará
            $logoPath = $this->procesarLogo($_FILES['logo'], $esNueva ? 0 : $empresaId);
            if ($logoPath) {
                $datos['logo_path'] = $logoPath;
            } else {
                // Si hubo error en el logo, redirigir con el mensaje de error
                header('Location: ' . BASE_URL . 'empresa/editar');
                exit;
            }
        }

        // Crear o actualizar
        try {
            if ($esNueva) {
                // Crear nueva empresa para el usuario
                $nuevaEmpresaId = Empresa::crear($_SESSION['user_id'], $datos);
                if ($nuevaEmpresaId) {
                    // Si se subió logo con ID temporal, renombrarlo
                    if (!empty($datos['logo_path'])) {
                        $this->renombrarLogoTemporal($datos['logo_path'], $nuevaEmpresaId);
                    }
                    flash('success', 'Empresa creada correctamente.');
                    header('Location: ' . BASE_URL . 'empresa');
                    exit;
                } else {
                    flash('error', 'No se pudo crear la empresa. Intenta nuevamente.');
                    header('Location: ' . BASE_URL . 'empresa/editar');
                    exit;
                }
            } else {
                // Actualizar empresa existente
                if (Empresa::actualizar($empresaId, $datos)) {
                    flash('success', 'Datos de la empresa actualizados correctamente.');
                    header('Location: ' . BASE_URL . 'empresa');
                    exit;
                } else {
                    flash('error', 'No se pudo actualizar la empresa. Intenta nuevamente.');
                    header('Location: ' . BASE_URL . 'empresa/editar');
                    exit;
                }
            }
        } catch (Exception $e) {
            flash('error', 'Error del sistema: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'empresa/editar');
            exit;
        }
    }

    /**
     * Renombra el logo con ID temporal al ID real de la empresa.
     */
    private function renombrarLogoTemporal(string $logoPath, int $empresaId): void
    {
        $logoDir = APP_ROOT . '/public/uploads/logos';
        $oldPath = APP_ROOT . '/public/' . $logoPath;
        
        if (file_exists($oldPath)) {
            $ext = pathinfo($oldPath, PATHINFO_EXTENSION);
            $newFilename = 'logo_' . $empresaId . '_' . time() . '.' . $ext;
            $newPath = $logoDir . '/' . $newFilename;
            
            if (rename($oldPath, $newPath)) {
                // Actualizar el path en la BD
                Empresa::actualizar($empresaId, ['logo_path' => 'uploads/logos/' . $newFilename]);
            }
        }
    }

    /**
     * Procesa y guarda el logo de la empresa.
     */
    private function procesarLogo(array $file, int $empresaId): ?string
    {
        $permitidos = ['image/png', 'image/jpeg', 'image/svg+xml', 'image/x-icon'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        // Validaciones
        if ($file['size'] > $maxSize) {
            flash('error', 'El logo no puede pesar más de 2MB.');
            return null;
        }

        // Validar por extensión en lugar de MIME type (más confiable)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['png', 'jpg', 'jpeg', 'svg', 'gif'];

        if (!in_array($ext, $extensionesPermitidas)) {
            flash('error', 'Formato de logo no permitido. Use PNG, JPG, SVG o GIF.');
            return null;
        }

        // Crear carpeta si no existe
        $logoDir = APP_ROOT . '/public/uploads/logos';
        if (!is_dir($logoDir)) {
            mkdir($logoDir, 0755, true);
        }

        // Generar nombre único
        $filename = 'logo_' . $empresaId . '_' . time() . '.' . $ext;
        $filepath = $logoDir . '/' . $filename;

        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'uploads/logos/' . $filename;
        }

        flash('error', 'Error al guardar el logo. Verifica los permisos de la carpeta.');
        return null;
    }
}
