<?php
/** @var array $clienteFila */

$id            = (int)$clienteFila['id'];
$nombre        = $clienteFila['nombre'] ?? '';
$cuitDni       = $clienteFila['cuit_dni'] ?? '';
$condicionIva  = $clienteFila['condicion_iva'] ?? '';
$telefono      = $clienteFila['telefono'] ?? '';
$email         = $clienteFila['email'] ?? '';
$activo        = (int)($clienteFila['activo'] ?? 1) === 1;

$badgeClass = $activo ? 'badge bg-success-subtle text-success' : 'badge bg-secondary-subtle text-muted';
$badgeText  = $activo ? 'Activo' : 'Inactivo';
?>
<tr>
    <td class="small">
        <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
    </td>
    <td class="small">
        <span class="text-muted">
            <?= htmlspecialchars($cuitDni, ENT_QUOTES, 'UTF-8') ?>
        </span>
    </td>
    <td class="small">
        <span class="text-muted">
            <?= htmlspecialchars($condicionIva, ENT_QUOTES, 'UTF-8') ?>
        </span>
    </td>
    <td class="small">
        <span class="text-muted">
            <?= htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8') ?>
        </span>
    </td>
    <td class="small">
        <span class="text-muted">
            <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
        </span>
    </td>
    <td class="small">
        <span class="<?= $badgeClass ?>">
            <?= $badgeText ?>
        </span>
    </td>
    <td class="small text-end">
        <a href="<?= BASE_URL ?>clientes/<?= $id ?>/editar" class="btn btn-outline-light btn-sm me-1">
            Editar
        </a>
        <form method="POST" action="<?= BASE_URL ?>clientes/<?= $id ?>/eliminar" class="d-inline eliminar-cliente-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar cliente">Eliminar</button>
        </form>
    </td>
</tr>
