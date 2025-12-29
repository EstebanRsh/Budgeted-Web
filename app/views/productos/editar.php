<?php
/** @var array $producto */
/** @var array $errores */

$id          = (int)($producto['id'] ?? 0);
$nombre      = $producto['nombre'] ?? '';
$descripcion = $producto['descripcion'] ?? '';

// Formateamos el precio para mostrarlo amigable
$precioRaw = $producto['precio_unitario'] ?? '';
if ($precioRaw !== '' && is_numeric($precioRaw)) {
    $precio = number_format((float)$precioRaw, 2, ',', '.');
} else {
    $precio = (string)$precioRaw;
}
?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Editar producto</h1>
        <p class="text-muted small mb-0">
            Modificá los datos del producto seleccionado.
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

        <!-- Formulario de edición -->
        <form method="post" action="<?= BASE_URL ?>productos/<?= $id ?>/editar" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-6">
                <label class="form-label small">Nombre <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="nombre"
                    class="form-control form-control-sm"
                    value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label small">Precio unitario <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="precio_unitario"
                    class="form-control form-control-sm text-end"
                    placeholder="0,00"
                    value="<?= htmlspecialchars($precio, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
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
                    Volver al listado
                </a>
                <button type="submit" class="btn btn-primary btn-sm">
                    Guardar cambios
                </button>
            </div>
        </form>

        <!-- Formulario separado para eliminar (sin anidarlo) -->
        <form method="post"
              action="<?= BASE_URL ?>productos/<?= $id ?>/eliminar"
              onsubmit="return confirm('¿Seguro que querés eliminar este producto?');"
              class="mt-3">
            <button type="submit" class="btn btn-outline-danger btn-sm">
                Eliminar producto
            </button>
        </form>
    </div>
</div>
