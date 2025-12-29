<?php
/** @var string|null $error */
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card app-card border-0">
            <div class="card-body">
                <h1 class="h5 mb-3 text-center">Crear cuenta</h1>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>registro" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label small">Nombre completo</label>
                        <input type="text" name="nombre" id="nombre" class="form-control form-control-sm" autocomplete="name" autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label small">Email</label>
                        <input type="email" name="email" id="email" class="form-control form-control-sm" autocomplete="email">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control form-control-sm" autocomplete="new-password">
                        <div class="form-text small">Mínimo 6 caracteres</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label small">Confirmar contraseña</label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control form-control-sm" autocomplete="new-password">
                    </div>

                    <div class="alert alert-info py-2 small mb-3">
                        <strong>Nota:</strong> Tu cuenta quedará pendiente de aprobación por un administrador.
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Registrarse
                        </button>
                    </div>

                    <p class="text-muted small mt-3 mb-0 text-center">
                        ¿Ya tenés cuenta? <a href="<?= BASE_URL ?>login">Iniciar sesión</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
