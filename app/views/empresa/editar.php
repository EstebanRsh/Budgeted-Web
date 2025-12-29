<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1"><?= $esNueva ?? false ? 'Crear Mi Empresa' : 'Editar Mi Empresa' ?></h1>
        <p class="text-muted small mb-0">
            <?= $esNueva ?? false 
                ? 'Completá los datos de tu empresa para empezar a generar presupuestos.' 
                : 'Modificá los datos de tu empresa. Estos se reflejarán en los presupuestos que generes.' 
            ?>
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

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success small">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>empresa/editar" enctype="multipart/form-data" class="row g-3">
            <?= csrf_field() ?>
            
            <!-- Nombre -->
            <div class="col-md-8">
                <label for="nombre" class="form-label small">Nombre de la Empresa <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" 
                       value="<?= htmlspecialchars($empresa['nombre']) ?>" required>
            </div>

            <!-- Logo -->
            <div class="col-md-4">
                <label for="logo" class="form-label small">Logo</label>
                <?php if (!empty($empresa['logo_path']) && file_exists(APP_ROOT . '/public/' . $empresa['logo_path'])): ?>
                    <div class="mb-2 text-center">
                        <img src="<?= BASE_URL . $empresa['logo_path'] ?>" alt="Logo" style="max-height: 50px;">
                        <div><small class="text-muted d-block mt-1">Logo actual</small></div>
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control form-control-sm" id="logo" name="logo" accept="image/png,image/jpeg,image/svg+xml">
                <small class="text-muted">PNG, JPG, SVG (máx 2MB)</small>
            </div>

            <!-- CUIT -->
            <div class="col-md-4">
                <label for="cuit" class="form-label small">CUIT</label>
                <input type="text" class="form-control form-control-sm" id="cuit" name="cuit" 
                       placeholder="XX-XXXXXXXX-X"
                       value="<?= htmlspecialchars($empresa['cuit'] ?? '') ?>">
            </div>

            <!-- Email -->
            <div class="col-md-4">
                <label for="email" class="form-label small">Email</label>
                <input type="email" class="form-control form-control-sm" id="email" name="email" 
                       value="<?= htmlspecialchars($empresa['email'] ?? '') ?>">
            </div>

            <!-- Teléfono -->
            <div class="col-md-4">
                <label for="telefono" class="form-label small">Teléfono</label>
                <input type="tel" class="form-control form-control-sm" id="telefono" name="telefono" 
                       value="<?= htmlspecialchars($empresa['telefono'] ?? '') ?>">
            </div>

            <!-- Domicilio -->
            <div class="col-12">
                <label for="domicilio" class="form-label small">Domicilio</label>
                <input type="text" class="form-control form-control-sm" id="domicilio" name="domicilio" 
                       value="<?= htmlspecialchars($empresa['domicilio'] ?? '') ?>">
            </div>

            <!-- Web -->
            <div class="col-md-6">
                <label for="web" class="form-label small">Sitio Web</label>
                <input type="text" class="form-control form-control-sm" id="web" name="web" 
                       placeholder="www.ejemplo.com"
                       value="<?= htmlspecialchars($empresa['web'] ?? '') ?>">
                <small class="text-muted">Opcional: con o sin https://</small>
            </div>

            <!-- Condición IVA -->
            <div class="col-md-6">
                <label for="condicion_iva" class="form-label small">Condición IVA</label>
                <select class="form-select form-select-sm" id="condicion_iva" name="condicion_iva">
                    <option value="">-- Seleccionar --</option>
                    <option value="Responsable Inscripto" <?= ($empresa['condicion_iva'] === 'Responsable Inscripto') ? 'selected' : '' ?>>Responsable Inscripto</option>
                    <option value="Monotributista" <?= ($empresa['condicion_iva'] === 'Monotributista') ? 'selected' : '' ?>>Monotributista</option>
                    <option value="Exento" <?= ($empresa['condicion_iva'] === 'Exento') ? 'selected' : '' ?>>Exento</option>
                    <option value="No Responsable" <?= ($empresa['condicion_iva'] === 'No Responsable') ? 'selected' : '' ?>>No Responsable</option>
                </select>
            </div>

            <!-- Inicio de Actividades -->
            <div class="col-md-6">
                <label for="inicio_actividades" class="form-label small">Inicio de Actividades</label>
                <input type="date" class="form-control form-control-sm" id="inicio_actividades" name="inicio_actividades" 
                       value="<?= htmlspecialchars($empresa['inicio_actividades'] ?? '') ?>">
            </div>

            <!-- Ingresos Brutos -->
            <div class="col-md-6">
                <label for="ingresos_brutos" class="form-label small">Ingresos Brutos</label>
                <input type="text" class="form-control form-control-sm" id="ingresos_brutos" name="ingresos_brutos" 
                       placeholder="Ej: 901-283910-2"
                       value="<?= htmlspecialchars($empresa['ingresos_brutos'] ?? '') ?>">
            </div>

            <!-- Botones -->
            <div class="col-12 d-flex justify-content-between">
                <a href="<?= BASE_URL ?>empresa" class="btn btn-outline-light btn-sm">
                    Volver
                </a>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
