<?php
/** @var array $productoFila */

$id          = (int)$productoFila['id'];
$nombre      = $productoFila['nombre'] ?? '';
$descripcion = $productoFila['descripcion'] ?? '';
$precio      = (float)($productoFila['precio_unitario'] ?? 0);
$creadoRaw   = $productoFila['created_at'] ?? null;
$creado      = $creadoRaw ? date('d/m/Y', strtotime($creadoRaw)) : '-';
?>
<tr id="fila-producto-<?= $id ?>">
    <td class="small">
        <?= $id ?>
    </td>
    <td class="small">
        <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
    </td>
    <td class="small">
        <span class="text-muted">
            <?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>
        </span>
    </td>
    <td class="small text-end" style="width: 140px;">
        <input
            type="text"
            name="precio_unitario"
            class="form-control form-control-sm text-end"
            value="<?= number_format($precio, 2, ',', '.') ?>"
            hx-post="<?= BASE_URL ?>productos/<?= $id ?>/precio"
            hx-target="#fila-producto-<?= $id ?>"
            hx-swap="outerHTML"
            hx-trigger="change, keyup[event.key === 'Enter']"
        >
    </td>
    <td class="small">
        <span class="text-muted">
            <?= $creado ?>
        </span>
    </td>
        <td class="small text-end">
            <a href="<?= BASE_URL ?>productos/<?= $id ?>/editar" class="btn btn-outline-light btn-sm me-1">
                Editar
            </a>
            <form method="POST" action="<?= BASE_URL ?>productos/<?= $id ?>/eliminar" class="d-inline eliminar-producto-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar producto">Eliminar</button>
            </form>
        </td>
</tr>
