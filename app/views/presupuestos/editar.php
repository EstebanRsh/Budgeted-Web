<?php
/** @var array $presupuesto */
/** @var array $clientes */
/** @var array $productos */
/** @var array $form */
/** @var array $errores */

$id          = (int)($presupuesto['id'] ?? 0);
$numero      = htmlspecialchars($presupuesto['numero'] ?? '', ENT_QUOTES, 'UTF-8');

$clienteId    = $form['cliente_id'] ?? '';
$fechaEmision = $form['fecha_emision'] ?? date('Y-m-d');
$estado       = $form['estado'] ?? 'Pendiente';
$validezDias  = $form['validez_dias'] ?? 15;
$observaciones= $form['observaciones'] ?? '';
$itemsForm    = $form['items'] ?? [];

// Al menos 5 filas visibles
$minFilas = 5;
if (count($itemsForm) < $minFilas) {
    for ($i = count($itemsForm); $i < $minFilas; $i++) {
        $itemsForm[] = [
            'producto_id'     => '',
            'descripcion'     => '',
            'cantidad'        => '',
            'precio_unitario' => '',
        ];
    }
}

$nextIndex = count($itemsForm);
?>

<div class="row mb-3 mb-md-4">
    <div class="col-12">
        <h1 class="h5 h4-md mb-2">Editar presupuesto</h1>
        <p class="text-muted small mb-0">
            Presupuesto <code><?= $numero ?></code> (ID: <?= $id ?>)
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

        <form method="post" action="<?= BASE_URL ?>presupuestos/<?= $id ?>/editar" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Cliente <span class="text-danger">*</span></label>
                <select
                    name="cliente_id"
                    class="form-select form-select-sm"
                    required
                >
                    <option value="">Seleccioná un cliente...</option>
                    <?php foreach ($clientes as $c): ?>
                        <?php
                        $id_cli = (int)$c['id'];
                        $nome   = htmlspecialchars($c['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
                        ?>
                        <option value="<?= $id_cli ?>" <?= ($id_cli === (int)$clienteId) ? 'selected' : '' ?>>
                            <?= $nome ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold">Fecha de emisión <span class="text-danger">*</span></label>
                <input
                    type="date"
                    name="fecha_emision"
                    class="form-control form-control-sm"
                    value="<?= htmlspecialchars($fechaEmision, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold">Estado</label>
                <select
                    name="estado"
                    class="form-select form-select-sm"
                >
                    <?php
                    $estados = ['Pendiente', 'Aprobado', 'Rechazado', 'Cancelado'];
                    ?>
                    <?php foreach ($estados as $e): ?>
                        <option value="<?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($estado === $e) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <hr class="mt-3 mt-md-2 mb-3">
                <div class="d-flex justify-content-between align-items-start align-items-md-center mb-3 flex-wrap gap-2">
                    <div>
                        <h2 class="h6 mb-1 fw-semibold">Ítems del presupuesto</h2>
                        <p class="text-muted small mb-0 d-none d-md-block">Modificá los productos o servicios.</p>
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        id="btn-agregar-item"
                    >
                        <span class="d-none d-sm-inline">Agregar ítem</span>
                        <span class="d-inline d-sm-none">+ Ítem</span>
                    </button>
                </div>

                <table class="table table-sm items-table mb-0">
                    <thead class="d-none d-md-table-header-group">
                        <tr>
                            <th style="width: 50%;">Producto / Servicio</th>
                            <th style="width: 10%;" class="text-center">Cantidad</th>
                            <th style="width: 20%;" class="text-end">Precio unitario</th>
                            <th style="width: 20%;" class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody id="items-rows" data-next-index="<?= (int)$nextIndex ?>">
                        <?php foreach ($itemsForm as $index => $row): ?>
                            <?php
                            $productoId = $row['producto_id'] ?? '';
                            $desc       = htmlspecialchars($row['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
                            $cant       = $row['cantidad'] ?? '';
                            $precio     = $row['precio_unitario'] ?? '';
                            ?>
                            <tr class="item-card">
                                <td class="item-desc-cell position-relative">
                                    <label class="form-label small fw-semibold mb-1 d-md-none">Producto / Servicio</label>
                                    <input
                                        type="hidden"
                                        name="items[<?= $index ?>][producto_id]"
                                        class="item-producto-id"
                                        value="<?= htmlspecialchars((string)$productoId, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                    <input
                                        type="text"
                                        name="items[<?= $index ?>][descripcion]"
                                        class="form-control form-control-sm item-descripcion"
                                        value="<?= $desc ?>"
                                        placeholder="Descripción (producto/servicio)"
                                        autocomplete="off"
                                    >
                                    <div class="position-absolute top-100 start-0 w-100 border border-top-0 rounded-bottom bg-white autocomplete-list" style="max-height: 150px; overflow-y: auto; display: none; z-index: 1000;">
                                    </div>
                                </td>

                                <td class="text-center">
                                    <label class="form-label small fw-semibold mb-1 d-md-none">Cantidad</label>
                                    <input
                                        type="text"
                                        name="items[<?= $index ?>][cantidad]"
                                        class="form-control form-control-sm text-center item-cantidad"
                                        value="<?= htmlspecialchars((string)$cant, ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Cant."
                                        inputmode="decimal"
                                    >
                                </td>

                                <td class="text-end">
                                    <label class="form-label small fw-semibold mb-1 d-md-none">Precio unitario</label>
                                    <input
                                        type="text"
                                        name="items[<?= $index ?>][precio_unitario]"
                                        class="form-control form-control-sm text-end item-precio"
                                        value="<?= htmlspecialchars((string)$precio, ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="0,00"
                                        inputmode="decimal"
                                    >
                                </td>

                                <td class="text-end">
                                    <label class="form-label small fw-semibold mb-1 d-md-none">Total</label>
                                    <div class="item-total">$ 0,00</div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="col-12">
                <hr class="mt-3 mb-3">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Validez del presupuesto (días)</label>
                        <input
                            type="number"
                            name="validez_dias"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars((string)$validezDias, ENT_QUOTES, 'UTF-8') ?>"
                            min="1"
                            max="365"
                        >
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">Observaciones</label>
                        <input
                            type="text"
                            name="observaciones"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($observaciones, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Notas adicionales (opcional)"
                        >
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Guardar cambios
                        </button>
                    </div>
                    <div class="col-12 col-md-auto">
                        <a href="<?= BASE_URL ?>presupuestos/<?= (int)$presupuesto['id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const itemsRows = document.getElementById('items-rows');
    const btnAgregarItem = document.getElementById('btn-agregar-item');
    const productosData = <?php echo json_encode($productos ?? []); ?>;

    // Calcula total de un ítem
    function calcularItem(row) {
        const cantInput = row.querySelector('.item-cantidad');
        const precioInput = row.querySelector('.item-precio');
        const totalDiv = row.querySelector('.item-total');

        const cantidad = parseFloat(cantInput.value.replace(',', '.')) || 0;
        const precio = parseFloat(precioInput.value.replace(',', '.')) || 0;

        const total = cantidad * precio;
        totalDiv.textContent = '$ ' + total.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Autocomplete para productos
    function setupAutocomplete(descInput) {
        descInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const autocompleteList = this.parentElement.querySelector('.autocomplete-list');

            if (query === '') {
                autocompleteList.style.display = 'none';
                return;
            }

            const matching = productosData.filter(p => 
                p.descripcion.toLowerCase().includes(query)
            );

            if (matching.length === 0) {
                autocompleteList.style.display = 'none';
                return;
            }

            autocompleteList.innerHTML = '';
            matching.slice(0, 5).forEach(p => {
                const item = document.createElement('div');
                item.className = 'list-group-item list-group-item-action small';
                item.style.cursor = 'pointer';
                item.textContent = p.descripcion + ' ($ ' + parseFloat(p.precio_unitario).toLocaleString('es-AR', {minimumFractionDigits: 2}) + ')';
                
                item.addEventListener('click', function() {
                    const row = descInput.closest('tr');
                    descInput.value = p.descripcion;
                    row.querySelector('.item-producto-id').value = p.id;
                    row.querySelector('.item-precio').value = p.precio_unitario;
                    autocompleteList.style.display = 'none';
                    calcularItem(row);
                });

                autocompleteList.appendChild(item);
            });

            autocompleteList.style.display = 'block';
        });

        document.addEventListener('click', function(e) {
            if (!descInput.parentElement.contains(e.target)) {
                descInput.parentElement.querySelector('.autocomplete-list').style.display = 'none';
            }
        });
    }

    // Agregar nueva fila
    function agregarFila() {
        const index = parseInt(itemsRows.dataset.nextIndex);
        const row = document.createElement('tr');
        row.className = 'item-card';
        row.innerHTML = `
            <td class="item-desc-cell position-relative">
                <label class="form-label small fw-semibold mb-1 d-md-none">Producto / Servicio</label>
                <input type="hidden" name="items[${index}][producto_id]" class="item-producto-id" value="">
                <input type="text" name="items[${index}][descripcion]" class="form-control form-control-sm item-descripcion" placeholder="Descripción" autocomplete="off">
                <div class="position-absolute top-100 start-0 w-100 border border-top-0 rounded-bottom bg-white autocomplete-list" style="max-height: 150px; overflow-y: auto; display: none; z-index: 1000;"></div>
            </td>
            <td class="text-center">
                <label class="form-label small fw-semibold mb-1 d-md-none">Cantidad</label>
                <input type="text" name="items[${index}][cantidad]" class="form-control form-control-sm text-center item-cantidad" placeholder="Cant." inputmode="decimal">
            </td>
            <td class="text-end">
                <label class="form-label small fw-semibold mb-1 d-md-none">Precio unitario</label>
                <input type="text" name="items[${index}][precio_unitario]" class="form-control form-control-sm text-end item-precio" placeholder="0,00" inputmode="decimal">
            </td>
            <td class="text-end">
                <label class="form-label small fw-semibold mb-1 d-md-none">Total</label>
                <div class="item-total">$ 0,00</div>
            </td>
        `;

        itemsRows.appendChild(row);

        const descInput = row.querySelector('.item-descripcion');
        setupAutocomplete(descInput);

        const cantInput = row.querySelector('.item-cantidad');
        const precioInput = row.querySelector('.item-precio');

        cantInput.addEventListener('input', () => calcularItem(row));
        precioInput.addEventListener('input', () => calcularItem(row));

        itemsRows.dataset.nextIndex = index + 1;
    }

    // Inicializar filas existentes
    document.querySelectorAll('.item-descripcion').forEach(descInput => {
        setupAutocomplete(descInput);
        calcularItem(descInput.closest('tr'));
    });

    // Agregar listeners a inputs existentes
    document.querySelectorAll('.item-cantidad, .item-precio').forEach(input => {
        input.addEventListener('input', function() {
            calcularItem(this.closest('tr'));
        });
    });

    btnAgregarItem.addEventListener('click', agregarFila);
})();
</script>
