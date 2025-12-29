<?php
/** @var array $resumen */
/** @var array $ultimos */

$totalPresupuestos = $resumen['total_presupuestos'] ?? 0;
$totalClientes     = $resumen['total_clientes'] ?? 0;
$sumaPresupuestos  = $resumen['suma_presupuestos'] ?? 0.0;
?>

<div class="dashboard-header row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Tu actividad reciente</h1>
        <p class="text-muted small mb-0">
            Este panel muestra un resumen de los presupuestos y clientes de tu empresa.
        </p>
    </div>
</div>

<div class="dashboard-metrics row g-3 mb-4">
    <div class="col-md-4">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Presupuestos emitidos</p>
                <p class="metric-value mb-0"><?= (int)$totalPresupuestos ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Clientes activos</p>
                <p class="metric-value mb-0"><?= (int)$totalClientes ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Total presupuestado (aprox.)</p>
                <p class="metric-value mb-0">
                    $ <?= number_format((float)$sumaPresupuestos, 2, ',', '.') ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card app-card border-0 dashboard-panel">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h6 mb-1">Últimos presupuestos</h2>
                        <p class="text-muted small mb-0">
                            Los presupuestos más recientes generados por tu empresa.
                        </p>
                    </div>
                </div>

                <?php if (empty($ultimos)): ?>
                    <p class="text-muted small mb-0">
                        Todavía no generaste presupuestos. Desde el menú vas a poder crear el primero
                        una vez que el módulo de presupuestos esté disponible.
                    </p>
                <?php else: ?>
                    <div class="table-responsive dashboard-table mt-2">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                            <tr>
                                <th class="small">N°</th>
                                <th class="small">Cliente</th>
                                <th class="small">Fecha</th>
                                <th class="small text-end">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($ultimos as $p): ?>
                                <?php
                                $estado      = $p['estado'] ?? '';
                                $estadoSlug  = strtolower(str_replace(' ', '-', $estado));
                                $fecha       = $p['fecha_emision'] ?? null;
                                $fechaForm   = $fecha ? date('d/m/Y', strtotime($fecha)) : '-';
                                ?>
                                <tr>
                                    <td class="small">
                                        <?= htmlspecialchars($p['numero'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="small">
                                        <?= htmlspecialchars($p['cliente_nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="small">
                                        <?= $fechaForm ?>
                                    </td>
                                    <td class="small text-end">
                                        $ <?= number_format((float)($p['total_general'] ?? 0), 2, ',', '.') ?>
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

    <div class="col-lg-4">
        <div class="card app-card border-0 dashboard-panel mb-3">
            <div class="card-body">
                <h2 class="h6 mb-2">Accesos rápidos</h2>
                <p class="text-muted small mb-3">
                    Atajos pensados para el uso diario de la app. Por ahora son placeholders
                    hasta que estén implementados los módulos correspondientes.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#" class="btn btn-primary btn-sm disabled" tabindex="-1" aria-disabled="true">
                        Nuevo presupuesto
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm disabled" tabindex="-1" aria-disabled="true">
                        Ver clientes
                    </a>
                    <a href="#" class="btn btn-outline-light btn-sm disabled" tabindex="-1" aria-disabled="true">
                        Ver productos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
