<?php
/** @var array       $presupuestos */
/** @var string|null $busqueda */
?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Presupuestos</h1>
        <p class="text-muted small mb-0">
            Listado de presupuestos emitidos por tu empresa.
        </p>
    </div>
</div>

<!-- Modal de confirmación de eliminación de presupuesto -->
<div class="modal fade" id="modalConfirmarEliminarPresupuesto" tabindex="-1" aria-labelledby="modalConfirmarEliminarPresupuestoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarEliminarPresupuestoLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                ¿Seguro que querés eliminar este presupuesto? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarPresupuesto">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let formAEliminarPresupuesto = null;
document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.eliminar-presupuesto-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        formAEliminarPresupuesto = this;
                        const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminarPresupuesto'));
                        modal.show();
                });
        });
        document.getElementById('btnConfirmarEliminarPresupuesto').addEventListener('click', function() {
                if (formAEliminarPresupuesto) {
                        formAEliminarPresupuesto.submit();
                        formAEliminarPresupuesto = null;
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminarPresupuesto'));
                        if (modal) modal.hide();
                }
        });
});
</script>

<div class="card app-card border-0">
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-6">
                <label for="busqueda-presupuestos" class="form-label small text-muted mb-1">Buscar</label>
                <div class="position-relative">
                    <input
                        type="text"
                        name="q"
                        id="busqueda-presupuestos"
                        class="form-control form-control-sm"
                        placeholder="Buscar por número, cliente o estado..."
                        value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        hx-get="<?= BASE_URL ?>presupuestos/buscar"
                        hx-target="#presupuestos-tabla"
                        hx-trigger="keyup changed delay:400ms"
                        hx-push-url="false"
                        hx-indicator="#busqueda-spinner"
                    >
                    <div id="busqueda-spinner" class="htmx-indicator position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                        <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?= BASE_URL ?>presupuestos/export/excel<?= isset($busqueda) && $busqueda ? '?q=' . urlencode($busqueda) : '' ?>" class="btn btn-sm btn-success me-2">
                    Exportar Excel
                </a>
                <a href="<?= BASE_URL ?>presupuestos/nuevo" class="btn btn-sm btn-primary">
                    Nuevo presupuesto
                </a>
            </div>
        </div>

        <div id="presupuestos-tabla">
            <?php require __DIR__ . '/_tabla.php'; ?>
        </div>
    </div>
</div>
