<?php
/** @var string $content */
/** @var string $title */

// Estado de sesión y rol
$isLoggedIn   = !empty($_SESSION['user_id'] ?? null);
$isSuperadmin = !empty($_SESSION['is_superadmin'] ?? null);

// Ruta actual normalizada (sin BASE_URL)
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$baseUrl     = BASE_URL;

if ($baseUrl !== '/' && $baseUrl !== '') {
    if (strpos($requestPath, $baseUrl) === 0) {
        $requestPath = substr($requestPath, strlen($baseUrl));
        if ($requestPath === '') {
            $requestPath = '/';
        }
    }
}

/**
 * Devuelve ' active' si la ruta actual coincide con alguna de las pasadas.
 *
 * Uso:
 *   <a class="nav-link<?= $navIsActive(['/dashboard']) ?>" ...>Dashboard</a>
 */
$navIsActive = function (array $paths) use ($requestPath): string {
    return in_array($requestPath, $paths, true) ? ' active' : '';
};
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'Presupuestador', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema de gestión de presupuestos para empresas">
    <meta name="theme-color" content="#4472C4">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>assets/icons/icon-192x192.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/icons/icon-192x192.png">

    <!-- Aplica el tema lo antes posible para evitar parpadeos -->
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var theme = (stored === 'dark' || stored === 'light') ? stored : 'light';
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css?v=10">
</head>
<body>

<!-- Contenedor de Toasts Bootstrap -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container" style="z-index: 1080"></div>

<?= render_flash_messages() ?>

<header class="app-header">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a href="<?= BASE_URL ?>dashboard" class="app-header-brand">
                <div class="app-header-logo">
                    <span>P</span>
                </div>
                <div class="app-header-title">
                    Presupuestador
                </div>
            </a>

            <?php if ($isLoggedIn): ?>
    <ul class="navbar-nav ms-4 me-auto d-none d-md-flex">
        <li class="nav-item">
            <a class="nav-link<?= $navIsActive(['/', '/dashboard']) ?>" href="<?= BASE_URL ?>dashboard">
                Dashboard
            </a>
        </li>

        <?php if (!$isSuperadmin): ?>
            <!-- Menú de usuario estándar -->
            <li class="nav-item">
                <a class="nav-link<?= $navIsActive(['/empresa']) ?>" href="<?= BASE_URL ?>empresa">
                    Mi empresa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $navIsActive(['/clientes']) ?>" href="<?= BASE_URL ?>clientes">
                    Clientes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $navIsActive(['/productos']) ?>" href="<?= BASE_URL ?>productos">
                    Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $navIsActive(['/presupuestos']) ?>" href="<?= BASE_URL ?>presupuestos">
                    Presupuestos
                </a>
            </li>
        <?php else: ?>
            <!-- Menú del superadmin -->
            <li class="nav-item">
                <a class="nav-link<?= $navIsActive(['/admin', '/admin/dashboard']) ?>" href="<?= BASE_URL ?>admin">
                    Panel admin
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= $navIsActive(['/admin/usuarios']) ?>" href="<?= BASE_URL ?>admin/usuarios">
                    Usuarios
                </a>
            </li>
        <?php endif; ?>
    </ul>
<?php endif; ?>


            <div class="ms-auto d-flex align-items-center gap-3">
                <button id="theme-toggle" class="theme-toggle btn btn-sm" type="button" aria-label="Cambiar tema">
                    <span id="theme-label">Claro</span>
                </button>

                <?php if ($isLoggedIn): ?>
                    <span class="text-muted small d-none d-md-inline">
                        <?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        <?= $isSuperadmin ? ' · Super Admin' : '' ?>
                    </span>
                    <a href="<?= BASE_URL ?>logout" class="btn btn-outline-light btn-sm">
                        Cerrar sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<main class="app-main">
    <div class="container">
        <?= $content ?>
    </div>
</main>

<footer class="app-footer">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="text-muted small">
            © <?= date('Y') ?> Presupuestador · ImperialSoft
        </span>
        <span class="text-muted small">
            Demo funcional · PHP + MySQL · PWA
        </span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<script src="https://unpkg.com/htmx.org@1.9.10"></script>
<script src="<?= BASE_URL ?>assets/js/app.js?v=3"></script>

<!-- Toasts dinámicos para notificaciones -->
<script>
function showToast(message, type = 'info', delay = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 shadow mb-2`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    `;
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}
</script>

<!-- Registro del Service Worker para PWA -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('<?= BASE_URL ?>service-worker.js')
            .then(registration => {
                console.log('Service Worker registrado con éxito:', registration.scope);
            })
            .catch(error => {
                console.log('Error al registrar Service Worker:', error);
            });
    });
}
</script>
</body>
</html>
