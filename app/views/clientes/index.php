<?php
/** @var array       $clientes */
/** @var string|null $busqueda */
?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Clientes</h1>
        <p class="text-muted small mb-0">
            Administrá tus clientes para poder generar presupuestos rápidamente.
        </p>
    </div>
</div>

<!-- Modal de confirmación de eliminación de cliente -->
<div class="modal fade" id="modalConfirmarEliminarCliente" tabindex="-1" aria-labelledby="modalConfirmarEliminarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarEliminarClienteLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                ¿Seguro que querés eliminar este cliente? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarCliente">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let formAEliminarCliente = null;
document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.eliminar-cliente-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        formAEliminarCliente = this;
                        const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminarCliente'));
                        modal.show();
                });
        });
        document.getElementById('btnConfirmarEliminarCliente').addEventListener('click', function() {
                if (formAEliminarCliente) {
                        formAEliminarCliente.submit();
                        formAEliminarCliente = null;
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminarCliente'));
                        if (modal) modal.hide();
                }
        });
});
</script>

<div class="card app-card border-0">
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-6">
                <label for="busqueda-clientes" class="form-label small text-muted mb-1">Buscar</label>
                <input
                    type="text"
                    name="q"
                    id="busqueda-clientes"
                    class="form-control form-control-sm"
                    placeholder="Buscar por nombre, CUIT/DNI o email..."
                    value="<?= htmlspecialchars($busqueda ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    hx-get="<?= BASE_URL ?>clientes/buscar"
                    hx-target="#clientes-tabla"
                    hx-trigger="keyup changed delay:400ms"
                    hx-push-url="false"
                >
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?= BASE_URL ?>clientes/nuevo" class="btn btn-sm btn-primary">
                    Nuevo cliente
                </a>
            </div>
        </div>

        <div id="clientes-tabla">
            <?php require __DIR__ . '/_tabla.php'; ?>
        </div>
    </div>
</div>
