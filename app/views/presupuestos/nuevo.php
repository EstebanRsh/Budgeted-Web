<?php
/** @var array $clientes */
/** @var array $productos */
/** @var array $form */
/** @var array $errores */

$clienteId    = $form['cliente_id'] ?? '';
$fechaEmision = $form['fecha_emision'] ?? date('Y-m-d');
$estado       = $form['estado'] ?? 'Pendiente';
$validezDias  = $form['validez_dias'] ?? 15;
$observaciones= $form['observaciones'] ?? '';
$itemsForm    = $form['items'] ?? [];

// Al menos algunas filas visibles al inicio
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
        <h1 class="h5 h4-md mb-2">Nuevo presupuesto</h1>
        <p class="text-muted small mb-0">
            Completá los datos del cliente y los ítems para generar un nuevo presupuesto.
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

        <form method="post" action="<?= BASE_URL ?>presupuestos/nuevo" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Cliente <span class="text-danger">*</span></label>
                <select
                    name="cliente_id"
                    class="form-select form-select-sm"
                    tabindex="1"
                    required
                >
                    <option value="">Seleccioná un cliente...</option>
                    <?php foreach ($clientes as $c): ?>
                        <?php
                        $id   = (int)$c['id'];
                        $nome = $c['nombre'] ?? '';
                        ?>
                        <option value="<?= $id ?>" <?= ($id === (int)$clienteId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>
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
                    tabindex="2"
                    value="<?= htmlspecialchars($fechaEmision, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label small fw-semibold">Estado</label>
                <select
                    name="estado"
                    class="form-select form-select-sm"
                    tabindex="3"
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
                        <p class="text-muted small mb-0 d-none d-md-block">Agregá productos o servicios. Usá Tab para avanzar rápido.</p>
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        id="btn-agregar-item"
                        tabindex="100"
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
                            $desc       = $row['descripcion'] ?? '';
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
                                        value="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Escribí el nombre del producto o servicio"
                                    >
                                </td>
                                <td class="item-cant-cell text-center">
                                    <label class="form-label small fw-semibold mb-1 d-md-none">Cantidad</label>
                                    <input
                                        type="text"
                                        name="items[<?= $index ?>][cantidad]"
                                        class="form-control form-control-sm text-end item-cantidad"
                                        value="<?= htmlspecialchars($cant, ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="0"
                                        inputmode="numeric"
                                    >
                                </td>
                                <td class="item-precio-cell text-end">
                                    <label class="form-label small fw-semibold mb-1 d-md-none">Precio unitario</label>
                                    <input
                                        type="text"
                                        name="items[<?= $index ?>][precio_unitario]"
                                        class="form-control form-control-sm text-end item-precio"
                                        value="<?= htmlspecialchars($precio, ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="0,00"
                                        inputmode="decimal"
                                    >
                                </td>
                                <td class="item-total-cell text-end">
                                    <label class="form-label small fw-semibold mb-1 d-md-none">Total</label>
                                    <span class="item-total fw-semibold">—</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="row g-3 align-items-start mt-2">
                    <div class="col-12 col-md-8">
                        <label class="form-label small fw-semibold">Comentarios / Condiciones</label>
                        <textarea
                            name="observaciones"
                            class="form-control form-control-sm"
                            rows="3"
                            placeholder="Plazos de entrega, forma de pago, notas adicionales..."
                        ><?= htmlspecialchars($observaciones, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label small fw-semibold">Validez (días)</label>
                        <input
                            type="number"
                            name="validez_dias"
                            class="form-control form-control-sm text-end"
                            min="1"
                            max="90"
                            value="<?= htmlspecialchars((string)$validezDias, ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                    <div class="col-12 col-sm-6 col-md-2 text-end">
                        <span class="small text-muted me-2">Total general:</span><br>
                        <strong id="presupuesto-total-general">$ 0,00</strong>
                    </div>
                </div>

                <p class="text-muted small mt-2 mb-0">
                    Los totales se calculan automáticamente. Los productos nuevos se crearán en tu catálogo al guardar; los existentes actualizarán su precio.
                </p>
            </div>

            <div class="col-12 d-flex flex-column flex-sm-row justify-content-between gap-2 mt-4">
                <a href="<?= BASE_URL ?>presupuestos" class="btn btn-outline-secondary btn-sm order-2 order-sm-1" tabindex="102">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary btn-sm order-1 order-sm-2" tabindex="101">
                    <span class="d-none d-sm-inline">Guardar presupuesto</span>
                    <span class="d-inline d-sm-none">Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .confirm-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1300;
        padding: 16px;
    }
    .confirm-overlay.d-none { display: none; }
    .confirm-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        max-width: 420px;
        width: 100%;
        padding: 18px 20px;
    }
    .confirm-card h6 { margin: 0 0 8px 0; }
    .confirm-card p { margin: 0 0 14px 0; color: #555; }
    .confirm-actions { display: flex; gap: 10px; justify-content: flex-end; }
    
    /* Tabla de ítems - estilo desktop */
    .items-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .items-table thead th {
        border-bottom: 2px solid #dee2e6;
        padding: 8px 12px;
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
    }
    
    .items-table tbody tr {
        border-bottom: 1px solid #e9ecef;
    }
    
    .items-table tbody tr:last-child {
        border-bottom: none;
    }
    
    .items-table tbody td {
        padding: 10px 12px;
        vertical-align: middle;
    }
    
    .item-total {
        font-weight: 600;
        color: #374151;
    }
    
    /* Mejoras móviles - convertir tabla en cards */
    @media (max-width: 767.98px) {
        /* Inputs más grandes en móvil */
        .item-descripcion,
        .item-cantidad,
        .item-precio {
            font-size: 16px !important; /* Evita zoom en iOS */
            padding: 10px 12px !important;
            min-height: 42px;
        }
        
        /* Convertir tabla en cards */
        .items-table,
        .items-table tbody {
            display: block;
        }
        
        .items-table tbody tr {
            display: block;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 12px;
        }
        
        .items-table tbody td {
            display: block;
            padding: 8px 0;
            border: none;
            text-align: left !important;
        }
        
        .item-total {
            display: block;
            padding: 10px 12px;
            background: #f8f9fa;
            border-radius: 4px;
            text-align: center;
            font-size: 1rem;
        }
        
        /* Card body padding reducido */
        .card-body {
            padding: 16px !important;
        }
        
        /* Botones más grandes */
        .btn-sm {
            padding: 10px 16px !important;
            font-size: 15px !important;
        }
        
        /* Total general destacado */
        #presupuesto-total-general {
            font-size: 1.25rem !important;
        }
        
        /* Modal más grande en móvil */
        .confirm-card {
            padding: 20px;
        }
        
        .confirm-actions {
            flex-direction: column-reverse;
            gap: 8px;
        }
        
        .confirm-actions .btn {
            width: 100%;
            padding: 12px;
        }
        
        /* Labels más visibles */
        .form-label {
            font-size: 13px;
            margin-bottom: 6px;
        }
    }
    
    /* Mejoras tablet */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .item-card {
            padding: 18px;
        }
    }
    
    /* Desktop */
    @media (min-width: 992px) {
        .item-card {
            padding: 18px 20px;
        }
        
        .items-container {
            gap: 14px;
        }
    }
    
    /* Autocomplete responsivo */
    @media (max-width: 767.98px) {
        .autocomplete-menu {
            max-height: 50vh !important;
            font-size: 15px;
        }
        
        .autocomplete-menu button {
            padding: 12px 16px !important;
            min-height: 48px;
        }
    }
</style>

<div id="confirm-save" class="confirm-overlay d-none" role="dialog" aria-modal="true" aria-labelledby="confirm-save-title">
    <div class="confirm-card">
        <h6 id="confirm-save-title" class="fw-semibold mb-1">¿Guardar este presupuesto?</h6>
        <p class="small mb-3">Revisá que los datos estén correctos. Podés seguir editando si elegís "No".</p>
        <div class="confirm-actions">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="confirm-save-no">No, seguir editando</button>
            <button type="button" class="btn btn-primary btn-sm" id="confirm-save-yes">Sí, guardar</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Catálogo de productos para sugerencias (id, nombre, precio)
    var productosCatalogo = <?php
        $productosJs = array_map(function ($p) {
            return [
                'id'     => (int)$p['id'],
                'nombre' => $p['nombre'] ?? '',
                'precio' => (float)($p['precio_unitario'] ?? 0),
            ];
        }, $productos);
        echo json_encode($productosJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ?>;

    var btnAgregar        = document.getElementById('btn-agregar-item');
    var tbody             = document.getElementById('items-rows');
    var totalGeneral      = document.getElementById('presupuesto-total-general');
    var currentMenu       = null;
    var currentMenuItems  = [];
    var currentMenuActive = -1;
    var skipSuggestOnce   = false; // evita reabrir sugerencias justo después de seleccionar
    var suggestTimer      = null;
    var SUGGEST_MIN_CHARS = 3;   // evita mostrar demasiados resultados con textos cortos
    var SUGGEST_LIMIT     = 10;  // máximo de sugerencias visibles
    var confirmOverlay    = document.getElementById('confirm-save');
    var confirmYesBtn     = document.getElementById('confirm-save-yes');
    var confirmNoBtn      = document.getElementById('confirm-save-no');

    function cerrarMenu() {
        if (currentMenu && currentMenu.parentNode) {
            currentMenu.parentNode.removeChild(currentMenu);
        }
        currentMenu = null;
        currentMenuItems = [];
        currentMenuActive = -1;
    }

    function abrirConfirmacion(onConfirm) {
        if (!confirmOverlay) return onConfirm();
        confirmOverlay.classList.remove('d-none');
        if (confirmNoBtn) {
            confirmNoBtn.focus();
        }

        var handleYes = function () {
            cerrarConfirmacion();
            onConfirm();
        };

        var handleNo = function () {
            cerrarConfirmacion();
        };

        function cerrarConfirmacion() {
            confirmOverlay.classList.add('d-none');
            confirmYesBtn && confirmYesBtn.removeEventListener('click', handleYes);
            confirmNoBtn && confirmNoBtn.removeEventListener('click', handleNo);
            document.removeEventListener('keydown', escHandler);
        }

        function escHandler(e) {
            if (e.key === 'Escape') {
                cerrarConfirmacion();
            }
        }

        confirmYesBtn && confirmYesBtn.addEventListener('click', handleYes);
        confirmNoBtn && confirmNoBtn.addEventListener('click', handleNo);
        document.addEventListener('keydown', escHandler);
    }

    // Parse decimal similar al backend (acepta 32.000,00 / 32000,00 / 32000.50, etc.)
    function parseDecimal(value) {
        if (value === null || value === undefined) return null;
        var v = String(value).trim().replace(/[\s$]/g, '');
        if (v === '') return null;

        var tieneComa  = v.indexOf(',') !== -1;
        var tienePunto = v.indexOf('.') !== -1;

        if (tieneComa && tienePunto) {
            // 32.000,00 -> 32000.00
            v = v.replace(/\./g, '');
            v = v.replace(',', '.');
        } else if (tieneComa && !tienePunto) {
            // 32000,00 -> 32000.00
            v = v.replace(',', '.');
        }
        // si solo tiene punto o solo dígitos, se deja tal cual

        var num = parseFloat(v);
        if (isNaN(num)) return null;
        return num;
    }

    function formatMoney(value) {
        if (value === null || isNaN(value)) return '0,00';
        try {
            return value.toLocaleString('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } catch (e) {
            return value.toFixed(2);
        }
    }

    function setFieldError(input, message, type) {
        if (!input) return;
        input.classList.remove('border-success', 'border-warning', 'border-secondary', 'is-valid');
        input.classList.add('border-danger', 'is-invalid');
        input.style.borderWidth = '2px';
        input.setAttribute('title', message);
        input.dataset.errorType = type || '';
    }

    function clearFieldError(input, type) {
        if (!input) return;
        input.classList.remove('border-danger', 'is-invalid');
        input.style.borderWidth = '';
        input.removeAttribute('title');
        input.dataset.errorType = '';

        // Para la descripción volvemos a pintar su estado (catálogo / nuevo)
        if (type === 'desc') {
            var row = input.closest('tr');
            if (row) {
                actualizarEstadoProducto(row);
            }
        }
    }

    // Calcula el total de UNA fila y devuelve el número
    function recalcRow(row) {
        var cantidadInput = row.querySelector('.item-cantidad');
        var precioInput   = row.querySelector('.item-precio');
        var totalSpan     = row.querySelector('.item-total');

        if (!cantidadInput || !precioInput || !totalSpan) return 0;

        var cant   = parseDecimal(cantidadInput.value);
        var precio = parseDecimal(precioInput.value);

        if (cant === null || precio === null) {
            totalSpan.textContent = '—';
            return 0;
        }

        var total = cant * precio;
        totalSpan.textContent = '$ ' + formatMoney(total);
        return total;
    }

    // Recalcula el total general sumando todas las filas
    function recalcTotalGeneral() {
        if (!tbody) return;
        var total = 0;
        Array.prototype.forEach.call(tbody.rows, function (row) {
            var rowTotal = recalcRow(row);
            if (!isNaN(rowTotal)) {
                total += rowTotal;
            }
        });
        if (totalGeneral) {
            totalGeneral.textContent = '$ ' + formatMoney(total);
        }
    }

    function actualizarEstadoProducto(row) {
        var idInput   = row.querySelector('.item-producto-id');
        var descInput = row.querySelector('.item-descripcion');
        if (!descInput) return;

        var tieneId = idInput && idInput.value;
        var desc    = descInput.value.trim();

        // Limpiar clases previas
        descInput.classList.remove('border-success', 'border-warning', 'border-secondary');
        descInput.style.borderWidth = '';
        descInput.removeAttribute('title');
        
        if (tieneId) {
            // Producto del catálogo - verde
            descInput.classList.add('border-success');
            descInput.style.borderWidth = '2px';
            descInput.title = 'Producto del catálogo';
        } else if (desc.length > 0) {
            // Producto nuevo - amarillo/naranja
            descInput.classList.add('border-warning');
            descInput.style.borderWidth = '2px';
            descInput.title = 'Este producto no existe en tu catálogo. Se guardará automáticamente al guardar el presupuesto.';
        } else {
            // Vacío - gris
            descInput.classList.add('border-secondary');
            descInput.style.borderWidth = '1px';
        }
    }

    function buscarCoincidencias(query) {
        var q = query.trim().toLowerCase();
        if (q.length < SUGGEST_MIN_CHARS) return [];

        // Ranking simple: primero los que empiezan con el texto, luego el resto
        var starts = [];
        var contains = [];
        productosCatalogo.forEach(function (p) {
            var name = p.nombre.toLowerCase();
            if (name.indexOf(q) === 0) {
                starts.push(p);
            } else if (name.indexOf(q) !== -1) {
                contains.push(p);
            }
        });

        return starts.concat(contains).slice(0, SUGGEST_LIMIT);
    }

    function highlightMatch(text, query) {
        var q = query.trim();
        if (!q) return text;
        var escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var regex = new RegExp('(' + escaped + ')', 'ig');
        return text.replace(regex, '<mark class="px-0 py-0 bg-warning-subtle">$1</mark>');
    }

    function renderSugerencias(descInput) {
        cerrarMenu();

        var row  = descInput.closest('tr');
        var td   = descInput.closest('td');
        if (!row || !td) return;

        var query = descInput.value || '';
        var coincidencias = buscarCoincidencias(query);

        var idInput  = row.querySelector('.item-producto-id');

        if (coincidencias.length === 0) {
            actualizarEstadoProducto(row);
            return;
        }

        var menu = document.createElement('div');
        menu.className = 'autocomplete-menu position-absolute w-100 bg-white border rounded-3 shadow-sm';
        menu.style.zIndex = '1050';
        menu.style.top = '100%';
        menu.style.left = '0';
        menu.style.maxHeight = '260px';
        menu.style.overflowY = 'auto';
        menu.style.marginTop = '4px';
        menu.style.minWidth = '280px';

        coincidencias.forEach(function (p) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 small border-0 border-bottom px-3 py-2';
            btn.dataset.id = String(p.id);
            btn.dataset.nombre = p.nombre;
            btn.dataset.precio = String(p.precio);

            var titleSpan = document.createElement('span');
            titleSpan.innerHTML = highlightMatch(p.nombre, query);

            var priceSpan = document.createElement('span');
            priceSpan.className = 'text-muted fw-semibold';
            priceSpan.textContent = '$ ' + formatMoney(p.precio);

            btn.appendChild(titleSpan);
            btn.appendChild(priceSpan);

            btn.addEventListener('click', function () {
                if (idInput) {
                    idInput.value = btn.dataset.id;
                }
                descInput.value = btn.dataset.nombre;

                var precioInput = row.querySelector('.item-precio');
                if (precioInput) {
                    var num = parseFloat(btn.dataset.precio);
                    if (!isNaN(num)) {
                        precioInput.value = formatMoney(num);
                    }
                }

                actualizarEstadoProducto(row);
                cerrarMenu();
                recalcTotalGeneral();

                // Cadena de foco: cantidad -> precio
                var cantidadInput = row.querySelector('.item-cantidad');
                if (cantidadInput) {
                    skipSuggestOnce = true;
                    cantidadInput.focus();
                    cantidadInput.select();
                }
            });

            menu.appendChild(btn);
        });

        // Truco para que se vea como lista limpia
        var lastChild = menu.lastElementChild;
        if (lastChild) {
            lastChild.classList.add('border-0');
        }

        // Indicador de límite
        if (coincidencias.length === SUGGEST_LIMIT) {
            var info = document.createElement('div');
            info.className = 'small text-muted px-3 py-2 border-top bg-light';
            info.textContent = 'Mostrando las ' + SUGGEST_LIMIT + ' mejores coincidencias';
            menu.appendChild(info);
        }

        td.appendChild(menu);
        currentMenu = menu;
        currentMenuItems = Array.prototype.slice.call(menu.querySelectorAll('button.list-group-item'));
        currentMenuActive = -1;
    }

    function attachRowEvents(row) {
        var descInput     = row.querySelector('.item-descripcion');
        var cantidadInput = row.querySelector('.item-cantidad');
        var precioInput   = row.querySelector('.item-precio');

        if (descInput) {
            var scheduleSuggest = function () {
                if (suggestTimer) {
                    clearTimeout(suggestTimer);
                }
                suggestTimer = setTimeout(function () {
                    if (skipSuggestOnce) {
                        skipSuggestOnce = false;
                        return;
                    }
                    renderSugerencias(descInput);
                }, 120); // debounce corto para no spamear
            };

            var moveActive = function (delta) {
                if (!currentMenuItems.length) return;
                currentMenuActive = (currentMenuActive + delta + currentMenuItems.length) % currentMenuItems.length;
                currentMenuItems.forEach(function (el, idx) {
                    if (idx === currentMenuActive) {
                        el.classList.add('active');
                        el.classList.add('bg-primary', 'text-white');
                        el.scrollIntoView({ block: 'nearest' });
                    } else {
                        el.classList.remove('active', 'bg-primary', 'text-white');
                    }
                });
            };

            var selectActive = function () {
                if (currentMenuActive < 0 || currentMenuActive >= currentMenuItems.length) return;
                currentMenuItems[currentMenuActive].click();
            };

            descInput.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') {
                    if (!currentMenu) {
                        renderSugerencias(descInput);
                    }
                    moveActive(1);
                    e.preventDefault();
                } else if (e.key === 'ArrowUp') {
                    if (!currentMenu) {
                        renderSugerencias(descInput);
                    }
                    moveActive(-1);
                    e.preventDefault();
                } else if (e.key === 'Enter') {
                    if (currentMenu) {
                        selectActive();
                        e.preventDefault();
                    }
                } else if (e.key === 'Escape') {
                    cerrarMenu();
                } else if (e.key === 'Tab') {
                    // Al tabular, no volver a abrir sugerencias y forzar cadena foco -> cantidad
                    skipSuggestOnce = true;
                    cerrarMenu();
                    var cantidadInput = row.querySelector('.item-cantidad');
                    if (cantidadInput) {
                        setTimeout(function () { cantidadInput.focus(); cantidadInput.select(); }, 0);
                        e.preventDefault();
                    }
                }
            });

            descInput.addEventListener('input', function () {
                var idInput = row.querySelector('.item-producto-id');
                if (idInput) {
                    // Si el usuario empieza a escribir de nuevo, desvinculamos del catálogo
                    idInput.value = '';
                }
                clearFieldError(descInput, 'desc');
                scheduleSuggest();
                actualizarEstadoProducto(row);
            });

            descInput.addEventListener('focus', function () {
                scheduleSuggest();
            });

            descInput.addEventListener('blur', function () {
                // pequeño delay para permitir click en el menú
                setTimeout(cerrarMenu, 200);
            });
        }

        if (cantidadInput) {
            var recalcHandler = function () {
                clearFieldError(cantidadInput);
                recalcRow(row);
                recalcTotalGeneral();
            };
            cantidadInput.addEventListener('input', recalcHandler);
            cantidadInput.addEventListener('change', recalcHandler);
            cantidadInput.addEventListener('keyup', recalcHandler);
        }

        if (precioInput) {
            var recalcHandler = function () {
                clearFieldError(precioInput);
                recalcRow(row);
                recalcTotalGeneral();
            };
            precioInput.addEventListener('input', recalcHandler);
            precioInput.addEventListener('change', recalcHandler);
            precioInput.addEventListener('keyup', recalcHandler);
        }

            // Cadena: Enter/Tab en cantidad -> precio
            if (cantidadInput && precioInput) {
                cantidadInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === 'Tab') {
                        precioInput.focus();
                        precioInput.select();
                        e.preventDefault();
                    }
                });

                // Cadena: Enter en precio -> siguiente campo natural (submit navegable)
                precioInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        // dejar que el navegador avance al siguiente foco (submit)
                        var btnSubmit = document.querySelector('button[type="submit"]');
                        if (btnSubmit) {
                            btnSubmit.focus();
                        }
                        e.preventDefault();
                    }
                });
            }

        // Estado inicial (producto nuevo / catálogo) y totales
        actualizarEstadoProducto(row);
    }

    // Inicializar filas existentes
    if (tbody) {
        Array.prototype.forEach.call(tbody.rows, function (row) {
            attachRowEvents(row);
            // Calcular total de la fila al inicializar
            recalcRow(row);
        });
        recalcTotalGeneral();
    }

    // Botón "Agregar ítem"
    if (btnAgregar && tbody) {
        btnAgregar.addEventListener('click', function (e) {
            e.preventDefault();

            var nextIndex = parseInt(tbody.getAttribute('data-next-index') || tbody.rows.length || 0, 10);
            if (!tbody.rows.length) return;

            var templateRow = tbody.rows[tbody.rows.length - 1];
            var newRow = templateRow.cloneNode(true);

            // Limpiar valores y actualizar names
            var inputs = newRow.querySelectorAll('input');
            inputs.forEach(function (input) {
                if (input.classList.contains('item-producto-id')) {
                    input.value = '';
                } else {
                    input.value = '';
                }
                var name = input.getAttribute('name') || '';
                name = name.replace(/\[\d+]/, '[' + nextIndex + ']');
                input.setAttribute('name', name);
            });

            var totalSpan = newRow.querySelector('.item-total');
            if (totalSpan) {
                totalSpan.textContent = '—';
            }

            tbody.appendChild(newRow);
            tbody.setAttribute('data-next-index', String(nextIndex + 1));

            attachRowEvents(newRow);
            recalcTotalGeneral();

            var firstInput = newRow.querySelector('.item-descripcion');
            if (firstInput) {
                firstInput.focus();
            }
        });
    }

    // Validación al enviar el formulario (inline, sin modal)
    var form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            var firstErrorInput = null;
            // Si ya estamos confirmando, no volver a interceptar
            if (form.dataset.submitting === '1') {
                return;
            }

            // Limpiar estados previos
            if (tbody) {
                Array.prototype.forEach.call(tbody.rows, function (row) {
                    clearFieldError(row.querySelector('.item-descripcion'), 'desc');
                    clearFieldError(row.querySelector('.item-cantidad'));
                    clearFieldError(row.querySelector('.item-precio'));
                });
            }

            var itemsValidos = 0;

            if (tbody) {
                Array.prototype.forEach.call(tbody.rows, function (row) {
                    var descInput     = row.querySelector('.item-descripcion');
                    var cantidadInput = row.querySelector('.item-cantidad');
                    var precioInput   = row.querySelector('.item-precio');

                    var desc   = descInput ? descInput.value.trim() : '';
                    var cant   = cantidadInput ? cantidadInput.value.trim() : '';
                    var precio = precioInput ? precioInput.value.trim() : '';

                    // Fila completamente vacía -> ignorar
                    if (desc === '' && cant === '' && precio === '') {
                        return;
                    }

                    // Si hay cantidad o precio sin descripción, pedir descripción
                    if (desc === '' && (cant !== '' || precio !== '')) {
                        setFieldError(descInput, 'Ingresá una descripción para este ítem.', 'desc');
                        if (!firstErrorInput) firstErrorInput = descInput;
                    }

                    // Requerir cantidad
                    if (desc !== '' && cant === '') {
                        setFieldError(cantidadInput, 'Ingresá una cantidad mayor a 0.');
                        if (!firstErrorInput) firstErrorInput = cantidadInput;
                    }

                    // Requerir precio
                    if (desc !== '' && precio === '') {
                        setFieldError(precioInput, 'Ingresá un precio unitario mayor a 0.');
                        if (!firstErrorInput) firstErrorInput = precioInput;
                    }

                    // Validar cantidad > 0
                    if (cant !== '') {
                        var cantParsed = parseDecimal(cant);
                        if (cantParsed === null || cantParsed <= 0) {
                            setFieldError(cantidadInput, 'La cantidad debe ser un número mayor a 0.');
                            if (!firstErrorInput) firstErrorInput = cantidadInput;
                        }
                    }

                    // Validar precio > 0
                    if (precio !== '') {
                        var precioParsed = parseDecimal(precio);
                        if (precioParsed === null || precioParsed <= 0) {
                            setFieldError(precioInput, 'El precio unitario debe ser un número mayor a 0.');
                            if (!firstErrorInput) firstErrorInput = precioInput;
                        }
                    }

                    if (desc !== '' && cant !== '' && precio !== '') {
                        var cantParsedOk = parseDecimal(cant);
                        var precioParsedOk = parseDecimal(precio);
                        if (cantParsedOk !== null && cantParsedOk > 0 && precioParsedOk !== null && precioParsedOk > 0) {
                            itemsValidos++;
                        }
                    }
                });
            }

            if (itemsValidos === 0) {
                e.preventDefault();
                if (tbody && tbody.rows.length) {
                    var firstRow = tbody.rows[0];
                    var descInputFirst  = firstRow.querySelector('.item-descripcion');
                    var cantidadInputFirst = firstRow.querySelector('.item-cantidad');
                    var precioInputFirst   = firstRow.querySelector('.item-precio');

                    var descFirst   = descInputFirst ? descInputFirst.value.trim() : '';
                    var cantFirst   = cantidadInputFirst ? cantidadInputFirst.value.trim() : '';
                    var precioFirst = precioInputFirst ? precioInputFirst.value.trim() : '';

                    if (descFirst === '') {
                        setFieldError(descInputFirst, 'Agregá una descripción.', 'desc');
                        if (!firstErrorInput) firstErrorInput = descInputFirst;
                    } else if (cantFirst === '' || parseDecimal(cantFirst) === null || parseDecimal(cantFirst) <= 0) {
                        setFieldError(cantidadInputFirst, 'Ingresá una cantidad mayor a 0.');
                        if (!firstErrorInput) firstErrorInput = cantidadInputFirst;
                    } else {
                        setFieldError(precioInputFirst, 'Ingresá un precio unitario mayor a 0.');
                        if (!firstErrorInput) firstErrorInput = precioInputFirst;
                    }
                }
                if (firstErrorInput) {
                    firstErrorInput.focus();
                }
                return false;
            }

            // Si hubo errores, impedir submit
            var inputsConError = form.querySelectorAll('.is-invalid');
            if (inputsConError.length > 0) {
                e.preventDefault();
                if (firstErrorInput) {
                    firstErrorInput.focus();
                }
                return false;
            }

            // Si no hay errores, pedimos confirmación
            e.preventDefault();
            abrirConfirmacion(function onConfirm() {
                form.dataset.submitting = '1';
                form.submit();
            });
            return false;
        });
    }

    // Cerrar menú al hacer click fuera
    document.addEventListener('click', function (e) {
        if (currentMenu && !currentMenu.contains(e.target)) {
            cerrarMenu();
        }
    });

    // Recalcular totales si el usuario vuelve del servidor con datos cargados
    recalcTotalGeneral();
});
</script>
