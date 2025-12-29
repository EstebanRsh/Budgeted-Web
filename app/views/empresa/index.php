<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Mi Empresa</h1>
        <p class="text-muted small mb-0">
            Visualizá y administrá los datos de tu empresa. Esta información aparecerá en tus presupuestos.
        </p>
    </div>
</div>

<div class="card app-card border-0">
    <div class="card-body">
        <?php if (!empty($empresa['logo_path']) && file_exists(APP_ROOT . '/public/' . $empresa['logo_path'])): ?>
            <div class="text-center mb-4">
                <img src="<?= BASE_URL . $empresa['logo_path'] ?>" alt="Logo" style="max-height: 100px;">
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Nombre</label>
                    <p class="mb-0 fw-semibold"><?= htmlspecialchars($empresa['nombre']) ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">CUIT</label>
                    <p class="mb-0 fw-semibold"><?= htmlspecialchars($empresa['cuit'] ?? '-') ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Email</label>
                    <p class="mb-0"><?= htmlspecialchars($empresa['email'] ?? '-') ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Teléfono</label>
                    <p class="mb-0"><?= htmlspecialchars($empresa['telefono'] ?? '-') ?></p>
                </div>
            </div>
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Domicilio</label>
                    <p class="mb-0"><?= htmlspecialchars($empresa['domicilio'] ?? '-') ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Sitio Web</label>
                    <p class="mb-0"><?= htmlspecialchars($empresa['web'] ?? '-') ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Condición IVA</label>
                    <p class="mb-0"><?= htmlspecialchars($empresa['condicion_iva'] ?? '-') ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Inicio de Actividades</label>
                    <p class="mb-0"><?= $empresa['inicio_actividades'] ? date('d/m/Y', strtotime($empresa['inicio_actividades'])) : '-' ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label small text-muted mb-1">Ingresos Brutos</label>
                    <p class="mb-0"><?= htmlspecialchars($empresa['ingresos_brutos'] ?? '-') ?></p>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Actualizada: <?= date('d/m/Y H:i', strtotime($empresa['created_at'])) ?></small>
            <a href="<?= BASE_URL ?>empresa/editar" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Editar datos
            </a>
        </div>
    </div>
</div>
