<?php
/** @var array|null $usuario */
/** @var array $empresas */

$esNuevo = $usuario === null;
$titulo = $esNuevo ? 'Nuevo Usuario' : 'Editar Usuario';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1"><?= $titulo ?></h1>
        <p class="text-muted small mb-0">
            <?= $esNuevo ? 'Crea un nuevo usuario del sistema.' : 'Modifica los datos del usuario.' ?>
        </p>
    </div>
</div>

<div class="card app-card border-0">
    <div class="card-body">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-warning small">
                <ul class="mb-0">
                    <li><?= htmlspecialchars($_SESSION['error']) ?></li>
                </ul>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" 
              action="<?= BASE_URL ?>admin/usuarios/<?= $esNuevo ? 'guardar' : $usuario['id'] . '/actualizar' ?>" 
              class="row g-3">
            
            <!-- Nombre -->
            <div class="col-md-6">
                <label for="nombre" class="form-label small">Nombre completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" 
                       value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label for="email" class="form-label small">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control form-control-sm" id="email" name="email" 
                       value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
            </div>

            <!-- Contraseña -->
            <div class="col-md-6">
                <label for="password" class="form-label small">
                    Contraseña <?= $esNuevo ? '<span class="text-danger">*</span>' : '(dejar vacío para mantener)' ?>
                </label>
                <input type="password" class="form-control form-control-sm" id="password" name="password" 
                       minlength="6" placeholder="Mínimo 6 caracteres"
                       <?= $esNuevo ? 'required' : '' ?>>
                <?php if (!$esNuevo): ?>
                    <small class="text-muted">Solo completar si deseas cambiar la contraseña.</small>
                <?php endif; ?>
            </div>

            <!-- Empresa -->
            <div class="col-md-6">
                <label for="empresa_id" class="form-label small">Empresa asociada</label>
                <select class="form-select form-select-sm" id="empresa_id" name="empresa_id">
                    <option value="">-- Sin asignar --</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= $empresa['id'] ?>" 
                                <?= (isset($usuario['empresa_id']) && (int)$usuario['empresa_id'] === (int)$empresa['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($empresa['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Si no asignas empresa, el usuario no podrá acceder al sistema.</small>
            </div>

            <!-- Estado activo -->
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                           <?= (!isset($usuario['activo']) || (int)$usuario['activo'] === 1) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="activo">
                        Usuario activo (puede iniciar sesión)
                    </label>
                </div>
            </div>

            <!-- Botones -->
            <div class="col-12 d-flex justify-content-between">
                <a href="<?= BASE_URL ?>admin/usuarios" class="btn btn-outline-light btn-sm">
                    Volver
                </a>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save"></i> <?= $esNuevo ? 'Crear usuario' : 'Guardar cambios' ?>
                </button>
            </div>
        </form>
    </div>
</div>
