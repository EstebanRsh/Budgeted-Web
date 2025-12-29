<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro · Presupuestador</title>
    
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css?v=10">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <span>P</span>
                </div>
                <h1 class="login-title">Presupuestador</h1>
                <p class="login-subtitle">Crear cuenta nueva</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-warning small mb-3">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>registro" class="login-form">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="nombre" class="form-label small">Nombre completo</label>
                    <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" 
                           placeholder="Tu nombre" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small">Email</label>
                    <input type="email" class="form-control form-control-sm" id="email" name="email" 
                           placeholder="tu@email.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small">Contraseña</label>
                    <input type="password" class="form-control form-control-sm" id="password" name="password" 
                           placeholder="Mínimo 6 caracteres" minlength="6" required>
                </div>

                <div class="mb-3">
                    <label for="password_confirm" class="form-label small">Confirmar contraseña</label>
                    <input type="password" class="form-control form-control-sm" id="password_confirm" name="password_confirm" 
                           placeholder="Repetí tu contraseña" minlength="6" required>
                </div>

                <div class="alert alert-info small mb-3">
                    <strong>Nota:</strong> Tu cuenta quedará pendiente de aprobación por un administrador.
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Registrarse
                </button>
            </form>

            <div class="login-footer">
                <p class="small mb-0">
                    ¿Ya tenés cuenta? <a href="<?= BASE_URL ?>login">Iniciar sesión</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
