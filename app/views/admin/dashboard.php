<?php
/** @var int $totalUsuarios */
/** @var int $usuariosActivos */
/** @var int $totalPresupuestos */
/** @var int $totalEmpresas */
/** @var int $presupuestos30dias */
/** @var array $topUsuarios */
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4 mb-1">Panel de Administración</h1>
        <p class="text-muted small mb-0">
            Vista global del sistema y métricas de actividad.
        </p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Total Usuarios</p>
                <p class="metric-value mb-0"><?= $totalUsuarios ?></p>
                <small class="text-muted"><?= $usuariosActivos ?> activos</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Total Empresas</p>
                <p class="metric-value mb-0"><?= $totalEmpresas ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Total Presupuestos</p>
                <p class="metric-value mb-0"><?= $totalPresupuestos ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card app-card metric-card border-0">
            <div class="card-body">
                <p class="metric-label mb-1">Últimos 30 días</p>
                <p class="metric-value mb-0"><?= $presupuestos30dias ?></p>
                <small class="text-muted">presupuestos</small>
            </div>
        </div>
    </div>
</div>

<div class="card app-card border-0">
    <div class="card-body">
        <h2 class="h6 mb-3">Top Usuarios por Actividad</h2>
        
        <?php if (empty($topUsuarios)): ?>
            <p class="text-muted small mb-0">No hay actividad registrada aún.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="small">Usuario</th>
                            <th class="small">Email</th>
                            <th class="small text-end">Presupuestos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topUsuarios as $usuario): ?>
                            <tr>
                                <td class="small"><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td class="small"><?= htmlspecialchars($usuario['email']) ?></td>
                                <td class="small text-end">
                                    <span class="badge bg-primary"><?= $usuario['total_presupuestos'] ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
