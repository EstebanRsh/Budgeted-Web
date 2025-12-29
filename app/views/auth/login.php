<?php
/** @var string|null $error */
?>
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card app-card border-0">
            <div class="card-body">
                <h1 class="h5 mb-3 text-center">Iniciar sesión</h1>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['registro_success'])): ?>
                    <div class="alert alert-success py-2 small mb-3">
                        <?= htmlspecialchars($_SESSION['registro_success'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <?php unset($_SESSION['registro_success']); ?>
                <?php endif; ?>

                <form method="post" action="<?= BASE_URL ?>login" autocomplete="off">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label small">Email</label>
                        <input type="text" name="email" id="email" class="form-control form-control-sm" autocomplete="username">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control form-control-sm" autocomplete="current-password">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Entrar
                        </button>
                    </div>

                    <p class="text-muted small mt-3 mb-0 text-center">
                        ¿No tenés cuenta? <a href="<?= BASE_URL ?>registro">Registrate aquí</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
