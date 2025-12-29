<?php
declare(strict_types=1);

require_once APP_ROOT . '/app/models/Usuario.php';
require_once APP_ROOT . '/app/models/Empresa.php';
require_once APP_ROOT . '/app/services/MailService.php';

class AdminController
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

        $title = $params['title'] ?? 'Administración · Presupuestador';

        require APP_ROOT . '/app/views/layouts/main.php';
    }

    /**
     * Verifica que el usuario sea superadmin.
     */
    private function requireSuperadmin(): void
    {
        require_login();
        
        if (!is_superadmin()) {
            http_response_code(403);
            die('Acceso denegado. Solo superadministradores.');
        }
    }

    /**
     * Dashboard de administración.
     */
    public function dashboard(): void
    {
        $this->requireSuperadmin();

        // Métricas generales
        $db = db();

        // Total usuarios
        $stmtUsuarios = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE is_superadmin = 0");
        $totalUsuarios = $stmtUsuarios->fetch()['total'] ?? 0;

        // Usuarios activos
        $stmtActivos = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE is_superadmin = 0 AND estado = 'activo'");
        $usuariosActivos = $stmtActivos->fetch()['total'] ?? 0;

        // Total presupuestos
        $stmtPresupuestos = $db->query("SELECT COUNT(*) as total FROM presupuestos");
        $totalPresupuestos = $stmtPresupuestos->fetch()['total'] ?? 0;

        // Total empresas
        $stmtEmpresas = $db->query("SELECT COUNT(*) as total FROM empresas");
        $totalEmpresas = $stmtEmpresas->fetch()['total'] ?? 0;

        // Actividad reciente (últimos 30 días)
        $stmt30dias = $db->query("
            SELECT COUNT(*) as total 
            FROM presupuestos 
            WHERE fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $presupuestos30dias = $stmt30dias->fetch()['total'] ?? 0;

        // Top usuarios por presupuestos
        $stmtTop = $db->query("
            SELECT u.nombre, u.email, COUNT(p.id) as total_presupuestos
            FROM usuarios u
            LEFT JOIN empresas e ON u.empresa_id = e.id
            LEFT JOIN presupuestos p ON e.id = p.empresa_id
            WHERE u.is_superadmin = 0
            GROUP BY u.id, u.nombre, u.email
            ORDER BY total_presupuestos DESC
            LIMIT 10
        ");
        $topUsuarios = $stmtTop->fetchAll();

        $this->render('admin/dashboard', [
            'title' => 'Panel de Administración · Presupuestador',
            'totalUsuarios' => $totalUsuarios,
            'usuariosActivos' => $usuariosActivos,
            'totalPresupuestos' => $totalPresupuestos,
            'totalEmpresas' => $totalEmpresas,
            'presupuestos30dias' => $presupuestos30dias,
            'topUsuarios' => $topUsuarios,
        ]);
    }

    /**
     * Listado de usuarios.
     */
    public function usuarios(): void
    {
        $this->requireSuperadmin();

        $pendPage = isset($_GET['pend_page']) ? max(1, (int)$_GET['pend_page']) : 1;
        $actPage  = isset($_GET['act_page']) ? max(1, (int)$_GET['act_page']) : 1;
        // Búsquedas separadas por pestaña
        $busquedaPend = isset($_GET['q_pend']) ? trim((string)$_GET['q_pend']) : '';
        $busquedaAct  = isset($_GET['q_act']) ? trim((string)$_GET['q_act']) : '';
        $perPage  = 10;

        // Pendientes
        $totalPend = Usuario::contarPendientes($busquedaPend);
        $paginacionPend = calcular_paginacion($totalPend, $pendPage, $perPage);
        $pendientes = Usuario::listarPendientesPaginado($paginacionPend['offset'], $perPage, $busquedaPend);

        // Activos / desactivados
        $totalAct = Usuario::contarActivos($busquedaAct);
        $paginacionAct = calcular_paginacion($totalAct, $actPage, $perPage);
        $activos = Usuario::listarActivosPaginado($paginacionAct['offset'], $perPage, $busquedaAct);

        $this->render('admin/usuarios', [
            'title' => 'Gestión de Usuarios · Administración',
            'pendientes' => $pendientes,
            'activos' => $activos,
            'paginacionPend' => $paginacionPend,
            'paginacionAct' => $paginacionAct,
            'busquedaPend' => $busquedaPend,
            'busquedaAct' => $busquedaAct,
        ]);
    }

    /**
     * Editar datos del usuario.
     */
    public function editarUsuario(int $id): void
    {
        $this->requireSuperadmin();
        $usuario = Usuario::obtenerPorId($id);
        if (!$usuario) {
            http_response_code(404);
            die('Usuario no encontrado.');
        }

        // Solo se pueden editar usuarios que no estén en espera
        if ($usuario['estado'] === 'en_espera') {
            flash('error', 'No se pueden editar usuarios pendientes de aprobación.');
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $this->render('admin/usuario_editar', [
            'title' => 'Editar Usuario · Administración',
            'usuario' => $usuario,
        ]);
    }

    /**
     * Actualiza datos del usuario.
     */
    public function actualizarUsuario(int $id): void
    {
        $this->requireSuperadmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método no permitido.');
        }

        $usuario = Usuario::obtenerPorId($id);
        if (!$usuario) {
            http_response_code(404);
            die('Usuario no encontrado.');
        }

        // Solo se pueden editar usuarios que no estén en espera
        if ($usuario['estado'] === 'en_espera') {
            flash('error', 'No se pueden editar usuarios pendientes de aprobación.');
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($nombre === '' || $email === '') {
            flash('error', 'Nombre y email son obligatorios.');
            header('Location: ' . BASE_URL . 'admin/usuarios/' . $id . '/editar');
            exit;
        }

        if (Usuario::existeEmail($email, $id)) {
            flash('error', 'El email ya está en uso por otro usuario.');
            header('Location: ' . BASE_URL . 'admin/usuarios/' . $id . '/editar');
            exit;
        }

        if (Usuario::actualizar($id, ['nombre' => $nombre, 'email' => $email])) {
            flash('success', 'Usuario actualizado correctamente.');
        } else {
            flash('error', 'No se pudo actualizar el usuario.');
        }
        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }

    /**
     * Cambiar estado de usuario.
     * - en_espera -> activo (aprobar)
     * - activo -> desactivado
     * - desactivado -> activo
     */
    public function toggleEstado(int $id): void
    {
        $this->requireSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método no permitido.');
        }

        if (!verify_csrf_token()) {
            http_response_code(403);
            die('Token de seguridad inválido. Intenta de nuevo.');
        }

        $usuario = Usuario::obtenerPorId($id);
        if (!$usuario) {
            http_response_code(404);
            die('Usuario no encontrado.');
        }

        // Determinar nuevo estado
        $estadoActual = $usuario['estado'];
        $nuevoEstado = match($estadoActual) {
            'en_espera' => 'activo',
            'activo' => 'desactivado',
            'desactivado' => 'activo',
            default => 'activo'
        };

        $mensaje = match($nuevoEstado) {
            'activo' => 'Usuario activado correctamente.',
            'desactivado' => 'Usuario desactivado correctamente.',
            default => 'Estado actualizado.'
        };

        try {
            if (Usuario::actualizar($id, ['estado' => $nuevoEstado])) {
                flash('success', $mensaje);
                
                // Enviar email de notificación (no bloquear si falla)
                try {
                    if ($nuevoEstado === 'activo' && $estadoActual === 'en_espera') {
                        // Primera activación desde en_espera
                        MailService::sendCuentaActivada($usuario);
                    } elseif ($nuevoEstado === 'activo' && $estadoActual === 'desactivado') {
                        // Reactivación
                        MailService::sendCuentaActivada($usuario);
                    } elseif ($nuevoEstado === 'desactivado') {
                        // Desactivación
                        MailService::sendCuentaDesactivada($usuario);
                    }
                } catch (Exception $mailError) {
                    // Log del error pero no detener el flujo
                    error_log("Error al enviar email: " . $mailError->getMessage());
                }
            } else {
                flash('error', 'Error al cambiar el estado del usuario.');
            }
        } catch (Exception $e) {
            flash('error', 'Error del sistema: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }

    /**
     * Eliminar usuario (baja física - usar con precaución).
     */
    public function eliminarUsuario(int $id): void
    {
        $this->requireSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método no permitido.');
        }

        if (!verify_csrf_token()) {
            http_response_code(403);
            die('Token de seguridad inválido. Intenta de nuevo.');
        }

        try {
            if (Usuario::eliminar($id)) {
                flash('success', 'Usuario eliminado correctamente.');
            } else {
                flash('error', 'Error al eliminar el usuario.');
            }
        } catch (Exception $e) {
            flash('error', 'Error del sistema: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }

    /**
     * Búsqueda AJAX para pendientes y activos.
     */
    public function buscarUsuarios(): void
    {
        $this->requireSuperadmin();

        $tab = $_GET['tab'] ?? 'pendientes'; // 'pendientes' o 'activos'
        $q = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);

        $usuario = new Usuario();

        if ($tab === 'pendientes') {
            $total = $usuario->contarPendientes($q);
            $paginacion = calcular_paginacion($total, $page, 10);
            $items = $usuario->listarPendientesPaginado(
                $page,
                10,
                $q
            );
        } else {
            $total = $usuario->contarActivos($q);
            $paginacion = calcular_paginacion($total, $page, 10);
            $items = $usuario->listarActivosPaginado(
                $page,
                10,
                $q
            );
        }

        $paginacion['total_registros'] = $total;

        // Renderizar tabla directamente sin layout
        if ($tab === 'pendientes') {
            echo $this->renderTabla($items, $paginacion + ['q' => $q], 'pendientes');
        } else {
            echo $this->renderTabla($items, $paginacion + ['q' => $q], 'activos');
        }
    }

    /**
     * Renderizar tabla de usuarios
     */
    private function renderTabla(array $items, array $paginacion, string $tab): string
    {
        ob_start();
        ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <?php if ($tab === 'activos'): ?><th>Estado</th><?php endif; ?>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="<?= $tab === 'activos' ? '5' : '4' ?>" class="text-center text-muted small">
                                <?= $tab === 'pendientes' ? 'No hay solicitudes pendientes' : 'No hay usuarios registrados' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['nombre']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <?php if ($tab === 'activos'): ?>
                                    <td>
                                        <?php if ($u['estado'] === 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php elseif ($u['estado'] === 'desactivado'): ?>
                                            <span class="badge bg-secondary">Desactivado</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">En espera</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($u['created_at'] ?? '') ?>
                                    </small>
                                </td>
                                <td class="text-end">
                                    <?php if ($tab === 'pendientes'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/toggle" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-success">Aprobar</button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/eliminar" class="d-inline" onsubmit="return confirmarEliminacion()">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/editar" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form method="POST" action="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/toggle" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm <?= $u['estado'] === 'activo' ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                                <?= $u['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/eliminar" class="d-inline" onsubmit="return confirmarEliminacion()">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($paginacion['total_paginas'] > 1): ?>
            <div class="mt-3">
                <small class="text-muted d-block mb-2">
                    Mostrando <?= $paginacion['inicio_rango'] ?>–<?= $paginacion['fin_rango'] ?> de <?= $paginacion['total_registros'] ?> 
                    <?= $tab === 'pendientes' ? 'solicitudes' : 'usuarios' ?>
                </small>
                <?php 
                $target = $tab === 'pendientes' ? '#tabla-pendientes' : '#tabla-activos';
                $indicator = $tab === 'pendientes' ? '#spinner-pendientes' : '#spinner-activos';
                echo renderizar_paginacion(
                    $paginacion,
                    BASE_URL . 'admin/usuarios/buscar',
                    ['tab' => $tab, 'q' => $paginacion['q'] ?? ''],
                    'page',
                    ['target' => $target, 'indicator' => $indicator, 'swap' => 'innerHTML']
                );
                ?>
            </div>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }

    /**
     * Ver logs de correo
     */
    public function mailLogs(): void
    {
        $this->requireSuperadmin();
        
        $logFile = APP_ROOT . '/logs/mail.log';
        $logs = [];
        
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            // Obtener últimas 200 líneas
            $logs = array_reverse(array_slice($lines, -200));
        }
        
        $this->render('admin/mail_logs', [
            'title' => 'Logs de Correo · Administración',
            'logs' => $logs,
            'logFile' => $logFile,
        ]);
    }
}
