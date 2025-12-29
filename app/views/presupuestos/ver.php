<?php
/** @var array $presupuesto */

$numero       = $presupuesto['numero'] ?? '';
$fechaRaw     = $presupuesto['fecha_emision'] ?? null;
$fechaForm    = $fechaRaw ? date('d/m/Y', strtotime($fechaRaw)) : '-';
$estado       = $presupuesto['estado'] ?? '';
$totalGeneral = (float)($presupuesto['total_general'] ?? 0);
$validezDias  = (int)($presupuesto['validez_dias'] ?? 15);
$observaciones = $presupuesto['observaciones'] ?? '';

$clienteNombre   = $presupuesto['cliente_nombre'] ?? '';
$clienteCuitDni  = $presupuesto['cliente_cuit_dni'] ?? '';
$clienteCondIva  = $presupuesto['cliente_condicion_iva'] ?? '';
$clienteDom      = $presupuesto['cliente_domicilio'] ?? '';
$clienteEmail    = $presupuesto['cliente_email'] ?? '';
$clienteTelefono = $presupuesto['cliente_telefono'] ?? '';

$items = $presupuesto['items'] ?? [];

$estadoSlug = strtolower(str_replace(' ', '-', $estado));
$badgeClass = 'badge bg-secondary-subtle text-muted';
if ($estadoSlug === 'pendiente') {
    $badgeClass = 'badge bg-warning-subtle text-warning';
} elseif (in_array($estadoSlug, ['aceptado', 'aprobado'], true)) {
    $badgeClass = 'badge bg-success-subtle text-success';
} elseif (in_array($estadoSlug, ['rechazado', 'cancelado'], true)) {
    $badgeClass = 'badge bg-danger-subtle text-danger';
}
?>
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-1">
                Presupuesto <?= htmlspecialchars($numero, ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="text-muted small mb-0">
                Fecha de emisión: <?= $fechaForm ?>
            </p>
        </div>
        <div class="text-end">
            <span class="<?= $badgeClass ?>">
                <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
            </span>
            <p class="small text-muted mb-0 mt-2">
                Total general:
                <strong>
                    $ <?= number_format($totalGeneral, 2, ',', '.') ?>
                </strong>
            </p>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="card app-card border-0">
            <div class="card-body">
                <h2 class="h6 mb-2">Cliente</h2>
                <p class="small mb-1">
                    <strong><?= htmlspecialchars($clienteNombre, ENT_QUOTES, 'UTF-8') ?></strong>
                </p>
                <?php if ($clienteCuitDni): ?>
                    <p class="small mb-1">
                        CUIT / DNI:
                        <span class="text-muted">
                            <?= htmlspecialchars($clienteCuitDni, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </p>
                <?php endif; ?>
                <?php if ($clienteCondIva): ?>
                    <p class="small mb-1">
                        Condición IVA:
                        <span class="text-muted">
                            <?= htmlspecialchars($clienteCondIva, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </p>
                <?php endif; ?>
                <?php if ($clienteDom): ?>
                    <p class="small mb-1">
                        Domicilio:
                        <span class="text-muted">
                            <?= htmlspecialchars($clienteDom, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </p>
                <?php endif; ?>
                <?php if ($clienteEmail): ?>
                    <p class="small mb-1">
                        Email:
                        <span class="text-muted">
                            <?= htmlspecialchars($clienteEmail, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </p>
                <?php endif; ?>
                <?php if ($clienteTelefono): ?>
                    <p class="small mb-0">
                        Teléfono:
                        <span class="text-muted">
                            <?= htmlspecialchars($clienteTelefono, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card app-card border-0">
            <div class="card-body">
                <h2 class="h6 mb-2">Detalle de ítems</h2>

                <?php if (empty($items)): ?>
                    <p class="text-muted small mb-0">
                        Este presupuesto aún no tiene ítems asociados.
                    </p>
                <?php else: ?>
                    <div class="table-responsive dashboard-table mt-2">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                            <tr>
                                <th class="small">Descripción</th>
                                <th class="small text-end">Cant.</th>
                                <th class="small text-end">Precio unitario</th>
                                <th class="small text-end">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php
                                $desc    = $item['descripcion'] ?? '';
                                $cant    = (float)($item['cantidad'] ?? 0);
                                $pu      = (float)($item['precio_unitario'] ?? 0);
                                $totalIt = (float)($item['total'] ?? 0);
                                ?>
                                <tr>
                                    <td class="small">
                                        <?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="small text-end">
                                        <?= number_format($cant, 2, ',', '.') ?>
                                    </td>
                                    <td class="small text-end">
                                        $ <?= number_format($pu, 2, ',', '.') ?>
                                    </td>
                                    <td class="small text-end">
                                        $ <?= number_format($totalIt, 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 align-items-start mb-3">
    <div class="col-md-8">
        <div class="card app-card border-0 h-100">
            <div class="card-body">
                <h2 class="h6 mb-2">Comentarios / Condiciones</h2>
                <p class="small mb-0 text-muted" style="white-space: pre-line;">
                    <?= htmlspecialchars($observaciones ?: 'Sin comentarios.', ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card app-card border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Validez</span>
                    <strong class="small"><?= (int)$validezDias ?> días</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Total general</span>
                    <strong>$ <?= number_format($totalGeneral, 2, ',', '.') ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>presupuestos" class="btn btn-outline-light btn-sm">Volver al listado</a>
            <a href="<?= BASE_URL ?>presupuestos/<?= (int)($presupuesto['id'] ?? 0) ?>/editar" class="btn btn-outline-primary btn-sm">Editar</a>
            <form method="POST" action="<?= BASE_URL ?>presupuestos/<?= (int)($presupuesto['id'] ?? 0) ?>/duplicar" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-outline-success btn-sm">Duplicar</button>
            </form>
            <form method="POST" action="<?= BASE_URL ?>presupuestos/<?= (int)($presupuesto['id'] ?? 0) ?>/eliminar" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este presupuesto?');">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger btn-sm">Eliminar</button>
            </form>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>presupuestos/<?= (int)($presupuesto['id'] ?? 0) ?>/print" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">Imprimir</a>
            <a href="<?= BASE_URL ?>presupuestos/<?= (int)($presupuesto['id'] ?? 0) ?>/pdf" class="btn btn-outline-light btn-sm" target="_blank" rel="noopener">PDF</a>
            <a href="<?= BASE_URL ?>presupuestos/<?= (int)($presupuesto['id'] ?? 0) ?>/excel" class="btn btn-outline-light btn-sm">Excel</a>
        </div>
    </div>
    
</div>
