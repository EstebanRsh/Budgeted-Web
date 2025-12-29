<?php
/** @var array $cliente */
/** @var array $errores */

$nombre        = $cliente['nombre'] ?? '';
$cuitDni       = $cliente['cuit_dni'] ?? '';
$condicionIva  = $cliente['condicion_iva'] ?? '';
$domicilio     = $cliente['domicilio'] ?? '';
$telefono      = $cliente['telefono'] ?? '';
$email         = $cliente['email'] ?? '';
$observaciones = $cliente['observaciones'] ?? '';
$activo        = (int)($cliente['activo'] ?? 1) === 1;
?>
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Nuevo cliente</h1>
        <p class="text-muted small mb-0">
            Cargá un cliente para poder usarlo en tus presupuestos.
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

        <form method="post" action="<?= BASE_URL ?>clientes/nuevo" class="row g-3">
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

            <div class="col-md-3">
                <label class="form-label small">CUIT / DNI</label>
                <input
                    type="text"
                    name="cuit_dni"
                    class="form-control form-control-sm"
                    value="<?= htmlspecialchars($cuitDni, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label small">Condición IVA</label>
                <input
                    type="text"
                    name="condicion_iva"
                    class="form-control form-control-sm"
                    placeholder="Responsable Inscripto, Monotributo..."
                    value="<?= htmlspecialchars($condicionIva, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="col-12">
                <label class="form-label small">Domicilio</label>
                <input
                    type="text"
                    name="domicilio"
                    class="form-control form-control-sm"
                    value="<?= htmlspecialchars($domicilio, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="col-md-4">
                <label class="form-label small">Teléfono</label>
                <input
                    type="text"
                    name="telefono"
                    class="form-control form-control-sm"
                    value="<?= htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="col-md-4">
                <label class="form-label small">Email</label>
                <input
                    type="email"
                    name="email"
                    class="form-control form-control-sm"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="col-md-4 d-flex align-items-center">
                <div class="form-check mt-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="activo"
                        id="cliente-activo"
                        value="1"
                        <?= $activo ? 'checked' : '' ?>
                    >
                    <label class="form-check-label small" for="cliente-activo">
                        Cliente activo
                    </label>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label small">Observaciones</label>
                <textarea
                    name="observaciones"
                    class="form-control form-control-sm"
                    rows="3"
                ><?= htmlspecialchars($observaciones, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="col-12 d-flex justify-content-between">
                <a href="<?= BASE_URL ?>clientes" class="btn btn-outline-light btn-sm">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary btn-sm">
                    Guardar cliente
                </button>
            </div>
        </form>
    </div>
</div>
