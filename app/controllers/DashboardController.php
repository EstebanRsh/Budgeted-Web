<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/models/Presupuesto.php';

class DashboardController
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

        $title = $params['title'] ?? 'Dashboard · Presupuestador';

        require APP_ROOT . '/app/views/layouts/main.php';
    }

    public function index(): void
    {
        // Siempre exigir que haya sesión
        require_login();

        // Rol
        $esAdmin   = is_superadmin();
        
        // Si es superadmin, redirigir al panel de administración
        if ($esAdmin) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }
        
        // Para usuario normal filtramos por su empresa
        $empresaId = current_empresa_id();

        $resumen = Presupuesto::obtenerResumen($empresaId);
        $ultimos = Presupuesto::ultimosPresupuestos($empresaId, 5);

        // Dashboard de usuario (por empresa)
        $this->render('dashboard/index', [
            'title'   => 'Dashboard · Presupuestador',
            'resumen' => $resumen,
            'ultimos' => $ultimos,
        ]);
    }
}
