<?php
/** @var array       $productos */
/** @var string|null $busqueda */
?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Productos y servicios</h1>
        <p class="text-muted small mb-0">
            Administrá tu catálogo y actualizá los precios rápido desde esta pantalla.
        </p>
    </div>
</div>

<!-- Modal de confirmación de eliminación de producto -->
<div class="modal fade" id="modalConfirmarEliminarProducto" tabindex="-1" aria-labelledby="modalConfirmarEliminarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarEliminarProductoLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                ¿Seguro que querés eliminar este producto? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarProducto">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let formAEliminarProducto = null;
document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.eliminar-producto-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        formAEliminarProducto = this;
                        const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminarProducto'));
                        modal.show();
                });
        });
        document.getElementById('btnConfirmarEliminarProducto').addEventListener('click', function() {
                if (formAEliminarProducto) {
                        formAEliminarProducto.submit();
                        formAEliminarProducto = null;
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminarProducto'));
                        if (modal) modal.hide();
                }
        });
});
</script>

<div class="card app-card border-0">
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-6">
                <label for="busqueda" class="form-label small text-muted mb-1">Buscar</label>
                <input
                    type="text"
                    name="q"
                    id="busqueda"
                    class="form-control form-control-sm"
                    placeholder="Buscar por nombre o descripción..."
                    value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    hx-get="<?= BASE_URL ?>productos/buscar"
                    hx-target="#productos-tabla"
                    hx-trigger="keyup changed delay:400ms"
                    hx-push-url="false"
                >
            </div>
            <div class="col-md-6 text-md-end">
<a href="<?= BASE_URL ?>productos/nuevo" class="btn btn-sm btn-primary">
    Nuevo producto
</a>

            </div>
        </div>

        <div id="productos-tabla">
            <?php
            // Reutilizamos la tabla en index y en /productos/buscar
            require __DIR__ . '/_tabla.php';
            ?>
        </div>
    </div>
</div>
