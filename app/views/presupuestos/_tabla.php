<?php
/** @var array $presupuestos */
/** @var array|null $paginacion */
?>
<?php if (empty($presupuestos)): ?>
    <p class="text-muted small mb-0">
        Todavía no hay presupuestos cargados. Más adelante, desde aquí vas a poder crear nuevos.
    </p>
<?php else: ?>
    <div class="table-responsive dashboard-table mt-2">
        <table class="table table-sm align-middle mb-0">
            <thead>
            <tr>
                <th class="small">N°</th>
                <th class="small">Cliente</th>
                <th class="small">Fecha</th>
                <th class="small">Estado</th>
                <th class="small text-end">Total</th>
                <th class="small text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($presupuestos as $p): ?>
                <?php
                $id        = (int)$p['id'];
                $numero    = $p['numero'] ?? '';
                $cliente   = $p['cliente_nombre'] ?? '';
                $estado    = $p['estado'] ?? '';
                $total     = (float)($p['total_general'] ?? 0);
                $fechaRaw  = $p['fecha_emision'] ?? null;
                $fechaForm = $fechaRaw ? date('d/m/Y', strtotime($fechaRaw)) : '-';

                $estadoSlug  = strtolower(str_replace(' ', '-', $estado));
                $badgeClass  = 'badge bg-secondary-subtle text-muted';
                if ($estadoSlug === 'pendiente') {
                    $badgeClass = 'badge bg-warning-subtle text-warning';
                } elseif (in_array($estadoSlug, ['aceptado', 'aprobado'], true)) {
                    $badgeClass = 'badge bg-success-subtle text-success';
                } elseif (in_array($estadoSlug, ['rechazado', 'cancelado'], true)) {
                    $badgeClass = 'badge bg-danger-subtle text-danger';
                }
                ?>
                <tr>
                    <td class="small">
                        <?= htmlspecialchars($numero, ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td class="small">
                        <?= htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td class="small">
                        <?= $fechaForm ?>
                    </td>
                    <td class="small">
                        <span class="<?= $badgeClass ?>">
                            <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td class="small text-end">
                        $ <?= number_format($total, 2, ',', '.') ?>
                    </td>
                    <td class="small text-end">
                        <a href="<?= BASE_URL ?>presupuestos/<?= $id ?>" class="btn btn-outline-light btn-sm me-1">
                            Ver detalle
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>presupuestos/<?= $id ?>/eliminar" class="d-inline eliminar-presupuesto-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar presupuesto">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($paginacion) && $paginacion['total_paginas'] > 1): ?>
        <div class="mt-3">
            <?php
            $parametros = [];
            if (isset($busqueda) && $busqueda !== null && $busqueda !== '') {
                $parametros['q'] = $busqueda;
            }
            echo renderizar_paginacion($paginacion, BASE_URL . 'presupuestos', $parametros);
            ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
