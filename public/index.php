<?php
/**
 * Front Controller - Punto de entrada principal de la aplicación
 *
 * Este archivo maneja todas las peticiones HTTP mediante un sistema de ruteo simple
 * basado en URLs amigables. Implementa el patrón Front Controller para centralizar
 * el manejo de solicitudes y delegar la lógica a los controladores apropiados.
 *
 * Estructura de ruteo:
 * - Rutas estáticas: /login, /dashboard, /productos
 * - Rutas dinámicas: /presupuestos/{id}, /clientes/{id}/editar
 * - Rutas de API: /api/check-email
 *
 * PHP Version 8.0+
 */
declare(strict_types=1);

// ERRORES: Desactivar en producción
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

require_once dirname(__DIR__) . '/config/app.php';

// Procesar la URI solicitada
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = str_replace('\\', '/', $uri);

// Quitamos el "base path" (subcarpeta) de la URL
// Permite que la app funcione tanto en raíz como en subdirectorios
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($basePath !== '' && $basePath !== '/') {
    if (strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
    }
}

// Normalizar URI
$uri = rtrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ============================================================================
// RUTAS DE PRODUCTOS
// ============================================================================

// Actualización rápida de precio con HTMX (sin recarga de página)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && preg_match('#^/productos/(\d+)/precio$#', $uri, $matches)
) {
    require_once APP_ROOT . '/app/controllers/ProductoController.php';
    $controller = new ProductoController();
    $controller->actualizarPrecio((int)$matches[1]);
    exit;
}

// Rutas dinámicas para edición y eliminación
if (preg_match('#^/productos/(\d+)/editar$#', $uri, $matches)) {
    require_once APP_ROOT . '/app/controllers/ProductoController.php';
    $controller = new ProductoController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->actualizar((int)$matches[1]);
    } else {
        $controller->editar((int)$matches[1]);
    }
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && preg_match('#^/productos/(\d+)/eliminar$#', $uri, $matches)
) {
    require_once APP_ROOT . '/app/controllers/ProductoController.php';
    $controller = new ProductoController();
    $controller->eliminar((int)$matches[1]);
    exit;
}

// Ruta para crear producto (GET muestra form, POST guarda)
if ($uri === '/productos/nuevo') {
    require_once APP_ROOT . '/app/controllers/ProductoController.php';
    $controller = new ProductoController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->guardar();
    } else {
        $controller->crear();
    }
    exit;
}

// ============================================================================
// RUTAS DE CLIENTES
// ============================================================================

// Rutas dinámicas para clientes (editar / actualizar / eliminar)
if (preg_match('#^/clientes/(\d+)/editar$#', $uri, $matches)) {
    require_once APP_ROOT . '/app/controllers/ClienteController.php';
    $controller = new ClienteController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->actualizar((int)$matches[1]);
    } else {
        $controller->editar((int)$matches[1]);
    }
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && preg_match('#^/clientes/(\d+)/eliminar$#', $uri, $matches)
) {
    require_once APP_ROOT . '/app/controllers/ClienteController.php';
    $controller = new ClienteController();
    $controller->eliminar((int)$matches[1]);
    exit;
}

// Ruta para crear cliente (GET muestra form, POST guarda)
if ($uri === '/clientes/nuevo') {
    require_once APP_ROOT . '/app/controllers/ClienteController.php';
    $controller = new ClienteController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->guardar();
    } else {
        $controller->crear();
    }
    exit;
}

// Ruta dinámica para ver un presupuesto: /presupuestos/{id}
if (preg_match('#^/presupuestos/(\d+)$#', $uri, $matches)) {
    require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
    $controller = new PresupuestoController();
    $controller->ver((int)$matches[1]);
    exit;
}


// Ruta para imprimir presupuesto: /presupuestos/{id}/print
if (preg_match('#^/presupuestos/(\d+)/print$#', $uri, $matches)) {
    require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
    $controller = new PresupuestoController();
    $controller->print((int)$matches[1]);
    exit;
}

// Ruta para exportar presupuesto a PDF: /presupuestos/{id}/pdf
if (preg_match('#^/presupuestos/(\d+)/pdf$#', $uri, $matches)) {
    require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
    $controller = new PresupuestoController();
    $controller->pdf((int)$matches[1]);
    exit;
}

// Exportar presupuesto individual a Excel: /presupuestos/{id}/excel
if (preg_match('#^/presupuestos/(\d+)/excel$#', $uri, $matches) && $method === 'GET') {
    require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
    $controller = new PresupuestoController();
    $controller->excel((int)$matches[1]);
    exit;
}

// Ruta para editar presupuesto: /presupuestos/{id}/editar
if (preg_match('#^/presupuestos/(\d+)/editar$#', $uri, $matches)) {
    require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
    $controller = new PresupuestoController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->actualizar((int)$matches[1]);
    } else {
        $controller->editar((int)$matches[1]);
    }
    exit;
}

// Ruta para duplicar presupuesto: /presupuestos/{id}/duplicar
if (preg_match('#^/presupuestos/(\d+)/duplicar$#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
    $controller = new PresupuestoController();
    $controller->duplicar((int)$matches[1]);
    exit;
}

// Ruta para eliminar presupuesto: /presupuestos/{id}/eliminar
if (preg_match('#^/presupuestos/(\d+)/eliminar$#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
    $controller = new PresupuestoController();
    $controller->eliminar((int)$matches[1]);
    exit;
}

// Ruta para crear presupuesto (GET muestra form, POST guarda)
if ($uri === '/presupuestos/nuevo') {
    require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
    $controller = new PresupuestoController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->guardar();
    } else {
        $controller->crear();
    }
    exit;
}

// Exportar presupuestos a Excel
if ($uri === '/presupuestos/export/excel' && $method === 'GET') {
    require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
    $controller = new PresupuestoController();
    $controller->exportarExcel();
    exit;
}

// ============================================================================
// RUTAS DE EMPRESA
// ============================================================================

// Ruta para empresa (GET) y actualizar (POST)
if (preg_match('#^/empresa(/editar)?$#', $uri)) {
    require_once APP_ROOT . '/app/controllers/EmpresaController.php';
    $controller = new EmpresaController();

    if ($uri === '/empresa/editar') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->actualizar();
        } else {
            $controller->editar();
        }
    } else {
        $controller->index();
    }
    exit;
}

// ============================================================================
// PANEL DE ADMINISTRACIÓN (SuperAdmin)
// ============================================================================

// Dashboard admin
if ($uri === '/admin' || $uri === '/admin/dashboard') {
    require_once APP_ROOT . '/app/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->dashboard();
    exit;
}

// Gestión de usuarios
if ($uri === '/admin/usuarios') {
    require_once APP_ROOT . '/app/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->usuarios();
    exit;
}

// Editar usuario (GET)
if (preg_match('#^/admin/usuarios/(\d+)/editar$#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once APP_ROOT . '/app/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->editarUsuario((int)$matches[1]);
    exit;
}

// Actualizar usuario (POST)
if (preg_match('#^/admin/usuarios/(\d+)/actualizar$#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once APP_ROOT . '/app/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->actualizarUsuario((int)$matches[1]);
    exit;
}

// Toggle estado usuario (POST)
if (preg_match('#^/admin/usuarios/(\d+)/toggle$#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once APP_ROOT . '/app/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->toggleEstado((int)$matches[1]);
    exit;
}

// Buscar usuarios (GET - AJAX)
if ($uri === '/admin/usuarios/buscar' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once APP_ROOT . '/app/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->buscarUsuarios();
    exit;
}

// Eliminar usuario (POST)
if (preg_match('#^/admin/usuarios/(\d+)/eliminar$#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once APP_ROOT . '/app/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->eliminarUsuario((int)$matches[1]);
    exit;
}

// Logs de correo
if ($uri === '/admin/logs/mail' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once APP_ROOT . '/app/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->mailLogs();
    exit;
}

// ============================================================================
// RUTAS DE API (Endpoints AJAX)
// ============================================================================

// API: Verificar email disponible (AJAX)
if ($uri === '/api/check-email' && $method === 'GET') {
    require_once APP_ROOT . '/app/controllers/Api/ApiController.php';
    $controller = new ApiController();
    $controller->checkEmail();
    exit;
}

// ============================================================================
// RUTAS ESTÁTICAS (Switch principal)
// ============================================================================

switch ($uri) {
    // Autenticación
    case '/login':
        require_once APP_ROOT . '/app/controllers/AuthController.php';
        $controller = new AuthController();

        if ($method === 'GET') {
            $controller->loginForm();
        } elseif ($method === 'POST') {
            $controller->login();
        } else {
            http_response_code(405);
            echo 'Método no permitido';
        }
        break;

    case '/registro':
        require_once APP_ROOT . '/app/controllers/AuthController.php';
        $controller = new AuthController();

        if ($method === 'GET') {
            $controller->registroForm();
        } elseif ($method === 'POST') {
            $controller->registro();
        } else {
            http_response_code(405);
            echo 'Método no permitido';
        }
        break;

    case '/logout':
        require_once APP_ROOT . '/app/controllers/AuthController.php';
        $controller = new AuthController();

        if ($method === 'GET') {
            $controller->logout();
        } else {
            http_response_code(405);
            echo 'Método no permitido';
        }
        break;

    case '/':
    case '/dashboard':
        require_once APP_ROOT . '/app/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;

    case '/empresa':
        require_once APP_ROOT . '/app/controllers/EmpresaController.php';
        $controller = new EmpresaController();
        $controller->index();
        break;

    case '/productos':
        require_once APP_ROOT . '/app/controllers/ProductoController.php';
        $controller = new ProductoController();
        $controller->index();
        break;

    case '/productos/buscar':
        require_once APP_ROOT . '/app/controllers/ProductoController.php';
        $controller = new ProductoController();
        $controller->buscar();
        break;
        case '/clientes':
        require_once APP_ROOT . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->index();
        break;

    case '/clientes/buscar':
        require_once APP_ROOT . '/app/controllers/ClienteController.php';
        $controller = new ClienteController();
        $controller->buscar();
        break;
    
        case '/presupuestos':
        require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
        $controller = new PresupuestoController();
        $controller->index();
        break;

    case '/presupuestos/buscar':
        require_once APP_ROOT . '/app/controllers/PresupuestoController.php';
        $controller = new PresupuestoController();
        $controller->buscar();
        break;


    default:
        http_response_code(404);
        echo '404 · Página no encontrada';
        break;
}
