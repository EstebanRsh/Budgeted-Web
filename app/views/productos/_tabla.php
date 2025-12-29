<?php
/** @var array $productos */
/** @var array|null $paginacion */
?>
<?php if (empty($productos)): ?>
    <p class="text-muted small mb-0">
        Todavía no cargaste productos. Más adelante acá vas a poder dar de alta tu catálogo.
    </p>
<?php else: ?>
    <div class="table-responsive dashboard-table mt-2">
        <table class="table table-sm align-middle mb-0">
            <thead>
            <tr>
                <th class="small">ID</th>
                <th class="small">Nombre</th>
                <th class="small">Descripción</th>
                <th class="small text-end">Precio unitario</th>
                <th class="small">Creado</th>
                    <th class="small text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $productoFila): ?>
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
                BASE_URL . 'productos/buscar',
                $parametros,
                'pagina',
                ['target' => '#productos-tabla', 'swap' => 'innerHTML']
            );
            ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
