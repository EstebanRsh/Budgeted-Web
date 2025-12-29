<?php
/** @var array $pendientes */
/** @var array $activos */
/** @var array $paginacionPend */
/** @var array $paginacionAct */
?>

<style>
.htmx-indicator {
    display: none;
}
.htmx-request .htmx-indicator {
    display: inline-block;
}
</style>

<div class="row mb-4">
    <div class="col-12 col-lg-8">
        <h1 class="h4 mb-1">Gestión de Usuarios</h1>
        <p class="text-muted small mb-0">
            Aprobá o desactivá usuarios. Los usuarios crean sus propias empresas.
        </p>
    </div>
    <div class="col-12 col-lg-4 text-lg-end mt-2 mt-lg-0">
        <a href="<?= BASE_URL ?>admin/logs/mail" class="btn btn-sm btn-outline-secondary">
            📧 Ver Logs de Correo
        </a>
    </div>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success small alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-warning small">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">Solicitudes pendientes</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab">Usuarios registrados</button>
    </li>
</ul>

<div class="tab-content mt-3">
    <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
        <div class="row g-2 align-items-end mb-3 justify-content-center">
            <div class="col-md-6 col-lg-5">
                <label for="busqueda-pendientes" class="form-label small text-muted mb-1">Buscar solicitudes pendientes</label>
                <div class="d-flex gap-2">
                    <div class="position-relative flex-grow-1">
                        <input
                            type="text"
                            id="busqueda-pendientes"
                            class="form-control form-control-sm"
                            placeholder="Nombre o email..."
                            hx-get="<?= BASE_URL ?>admin/usuarios/buscar"
                            hx-target="#tabla-pendientes"
                            hx-trigger="keyup changed delay:400ms"
                            hx-vals='{"tab": "pendientes"}'
                            hx-push-url="false"
                            hx-indicator="#spinner-pendientes"
                            name="q"
                        >
                        <div id="spinner-pendientes" class="htmx-indicator position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('busqueda-pendientes').dispatchEvent(new Event('keyup'))">Buscar</button>
                </div>
            </div>
        </div>

        <div class="card app-card border-0">
            <div class="card-body" style="min-height: 400px;">
                <div id="tabla-pendientes">
                    <?php if (empty($pendientes)): ?>
                        <div class="alert alert-info text-center mb-0" style="display: flex; align-items: center; justify-content: center; min-height: 350px;">
                            <div>
                                <p class="mb-2"><strong>No hay solicitudes pendientes de aprobación.</strong></p>
                                <p class="small text-muted mb-0">Invita a otros usuarios para que se registren y gestiona sus solicitudes aquí.</p>
                            </div>
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendientes as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($u['created_at'] ?? '') ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <form method="POST" action="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/toggle" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-outline-success" title="Aprobar solicitud">Aprobar</button>
                                                </form>
                                                <form method="POST" action="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/eliminar" class="d-inline eliminar-usuario-form">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-outline-danger" title="Eliminar solicitud">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    <div class="mt-3" style="min-height: 60px;">
                        <?php if ($paginacionPend['total_paginas'] > 1): ?>
                            <small class="text-muted d-block mb-2">Mostrando <?= $paginacionPend['inicio_rango'] ?>–<?= $paginacionPend['fin_rango'] ?> de <?= $paginacionPend['total_registros'] ?> solicitudes</small>
                            <?php echo renderizar_paginacion($paginacionPend, BASE_URL . 'admin/usuarios/buscar', ['tab' => 'pendientes', 'q' => $busquedaPend ?? ''], 'page', ['target' => '#tabla-pendientes', 'indicator' => '#spinner-pendientes', 'swap' => 'innerHTML']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="activos" role="tabpanel">
        <div class="row g-2 align-items-end mb-3 justify-content-center">
            <div class="col-md-6 col-lg-5">
                <label for="busqueda-activos" class="form-label small text-muted mb-1">Buscar usuarios</label>
                <div class="d-flex gap-2">
                    <div class="position-relative flex-grow-1">
                        <input
                            type="text"
                            id="busqueda-activos"
                            class="form-control form-control-sm"
                            placeholder="Nombre o email..."
                            hx-get="<?= BASE_URL ?>admin/usuarios/buscar"
                            hx-target="#tabla-activos"
                            hx-trigger="keyup changed delay:400ms"
                            hx-vals='{"tab": "activos"}'
                            hx-push-url="false"
                            hx-indicator="#spinner-activos"
                            name="q"
                        >
                        <div id="spinner-activos" class="htmx-indicator position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('busqueda-activos').dispatchEvent(new Event('keyup'))">Buscar</button>
                </div>
            </div>
        </div>

        <div class="card app-card border-0">
            <div class="card-body" style="min-height: 400px;">
                <div id="tabla-activos">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activos)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted small">No hay usuarios registrados</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activos as $u): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td>
                                                <?php if ($u['estado'] === 'activo'): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php elseif ($u['estado'] === 'desactivado'): ?>
                                                    <span class="badge bg-secondary">Desactivado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">En espera</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($u['created_at'] ?? '') ?>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/editar" class="btn btn-outline-primary" title="Editar usuario">Editar</a>
                                                    <form method="POST" action="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/toggle" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <button class="btn btn-outline-<?= $u['estado'] === 'activo' ? 'warning' : 'success' ?>" title="<?= $u['estado'] === 'activo' ? 'Desactivar usuario' : 'Activar usuario' ?>">
                                                            <?= $u['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="<?= BASE_URL ?>admin/usuarios/<?= (int)$u['id'] ?>/eliminar" class="d-inline eliminar-usuario-form">
                                                        <?= csrf_field() ?>
                                                        <button class="btn btn-outline-danger" title="Eliminar usuario">Eliminar</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3" style="min-height: 60px;">
                        <?php if ($paginacionAct['total_paginas'] > 1): ?>
                            <small class="text-muted d-block mb-2">Mostrando <?= $paginacionAct['inicio_rango'] ?>–<?= $paginacionAct['fin_rango'] ?> de <?= $paginacionAct['total_registros'] ?> usuarios</small>
                            <?php echo renderizar_paginacion($paginacionAct, BASE_URL . 'admin/usuarios/buscar', ['tab' => 'activos', 'q' => $busquedaAct ?? ''], 'page', ['target' => '#tabla-activos', 'indicator' => '#spinner-activos', 'swap' => 'innerHTML']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1" aria-labelledby="modalConfirmarEliminacionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalConfirmarEliminacionLabel">Confirmar eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Seguro que querés eliminar este usuario? Esta acción no se puede deshacer.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarEliminarUsuario">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>
let formAEliminar = null;

document.querySelectorAll('.eliminar-usuario-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        formAEliminar = this;
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminacion'));
        modal.show();
    });
});

document.getElementById('btnConfirmarEliminarUsuario').addEventListener('click', function() {
    if (formAEliminar) {
        formAEliminar.submit();
        formAEliminar = null;
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminacion'));
        if (modal) modal.hide();
    }
});

// Guardar pestaña activa en localStorage
document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(tab) {
    tab.addEventListener('shown.bs.tab', function(e) {
        localStorage.setItem('adminUsuariosTab', e.target.getAttribute('data-bs-target'));
    });
});

// Restaurar pestaña activa al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    var activeTab = localStorage.getItem('adminUsuariosTab');
    if (activeTab) {
        var tabTrigger = document.querySelector('button[data-bs-target="' + activeTab + '"]');
        if (tabTrigger) {
            var tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }
});
</script>
