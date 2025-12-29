<?php
/** @var array $resumen */
/** @var array $ultimos */

$totalPresupuestos = $resumen['total_presupuestos'] ?? 0;
$totalClientes     = $resumen['total_clientes'] ?? 0;
$sumaPresupuestos  = $resumen['suma_presupuestos'] ?? 0.0;
?>

<div class="dashboard-header row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Panel global de la plataforma</h1>
        <p class="text-muted small mb-0">
            Estás en modo <strong>superadmin</strong>. Acá ves métricas agregadas de todas las empresas y usuarios.
        </p>
    </div>
</div>

<div class="dashboard-metrics row g-3 mb-4">
    <div class="col-md-4">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Presupuestos totales</p>
                <p class="metric-value mb-0"><?= (int)$totalPresupuestos ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Clientes totales</p>
                <p class="metric-value mb-0"><?= (int)$totalClientes ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Total presupuestado (global)</p>
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
                        <h2 class="h6 mb-1">Últimos presupuestos generados (global)</h2>
                        <p class="text-muted small mb-0">
                            Listado de los presupuestos más recientes de todas las empresas.
                        </p>
                    </div>
                </div>

                <?php if (empty($ultimos)): ?>
                    <p class="text-muted small mb-0">
                        Todavía no hay presupuestos registrados en el sistema.
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
                <h2 class="h6 mb-2">Administración (roadmap)</h2>
                <p class="text-muted small mb-3">
                    Este panel global está pensado para centralizar la administración de la plataforma.
                    Próximas funcionalidades a incorporar:
                </p>
                <ul class="text-muted small mb-3">
                    <li>Listado y alta de empresas.</li>
                    <li>Gestión de usuarios por empresa.</li>
                    <li>Métricas de uso (presupuestos por período, actividad, etc.).</li>
                </ul>
                <p class="text-muted small mb-0">
                    Por ahora es un dashboard sólo de lectura, con métricas globales.
                </p>
            </div>
        </div>
    </div>
</div>
