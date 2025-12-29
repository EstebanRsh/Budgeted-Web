<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Editar Usuario</h1>
        <p class="text-muted small mb-0">Modificar datos del usuario activo.</p>
    </div>
</div>

<div class="card app-card border-0">
    <div class="card-body">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-warning small"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success small"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>admin/usuarios/<?= (int)$usuario['id'] ?>/actualizar" class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label small">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control form-control-sm" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label small">Email</label>
                <input type="email" id="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>
            <div class="col-12 text-end">
                <a href="<?= BASE_URL ?>admin/usuarios" class="btn btn-sm btn-outline-secondary">Cancelar</a>
                <button class="btn btn-sm btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
