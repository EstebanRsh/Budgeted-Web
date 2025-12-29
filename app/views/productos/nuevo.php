<?php
/** @var array $producto */
/** @var array $errores */

$nombre      = $producto['nombre'] ?? '';
$descripcion = $producto['descripcion'] ?? '';
$precio      = $producto['precio_unitario'] ?? '';
?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Nuevo producto</h1>
        <p class="text-muted small mb-0">
            Cargá un producto o servicio para usar en tus presupuestos.
        </p>
    </div>
</div>

<div class="card app-card border-0">
    <div class="card-body">
        <?php if (!empty($errores)): ?>
            <div class="alert alert-warning small">
                <ul class="mb-0">
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>productos/nuevo" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-6">
                <label class="form-label small">Nombre <span class="text-danger">*</span></label>
                <input type="text"
                       name="nombre"
                       class="form-control form-control-sm"
                       value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label small">Precio unitario <span class="text-danger">*</span></label>
                <input type="text"
                       name="precio_unitario"
                       class="form-control form-control-sm text-end"
                       placeholder="0,00"
                       value="<?= htmlspecialchars($precio, ENT_QUOTES, 'UTF-8') ?>"
                       required>
                <div class="form-text small text-muted">
                    Podés usar coma o punto para los decimales.
                </div>
            </div>

            <div class="col-12">
                <label class="form-label small">Descripción</label>
                <textarea
                    name="descripcion"
                    class="form-control form-control-sm"
                    rows="3"
                ><?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="col-12 d-flex justify-content-between">
                <a href="<?= BASE_URL ?>productos" class="btn btn-outline-light btn-sm">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary btn-sm">
                    Guardar producto
                </button>
            </div>
        </form>
    </div>
</div>
