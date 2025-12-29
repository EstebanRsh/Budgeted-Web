<?php
/** @var array $clientes */
/** @var array|null $paginacion */
?>
<?php if (empty($clientes)): ?>
    <p class="text-muted small mb-0">
        Todavía no cargaste clientes. Desde aquí vas a poder administrar tu agenda de clientes.
    </p>
<?php else: ?>
    <div class="table-responsive dashboard-table mt-2">
        <table class="table table-sm align-middle mb-0">
            <thead>
            <tr>
                <th class="small">Nombre</th>
                <th class="small">CUIT / DNI</th>
                <th class="small">Cond. IVA</th>
                <th class="small">Teléfono</th>
                <th class="small">Email</th>
                <th class="small">Estado</th>
                <th class="small text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($clientes as $clienteFila): ?>
                <?php require __DIR__ . '/_fila.php'; ?>
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
            echo renderizar_paginacion(
                $paginacion,
                BASE_URL . 'clientes/buscar',
                $parametros,
                'pagina',
                ['target' => '#clientes-tabla', 'swap' => 'innerHTML']
            );
            ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
