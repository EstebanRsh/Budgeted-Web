<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';

/**
 * Script de limpieza y reseteo de datos demo
 * Elimina clientes, productos, presupuestos, ítems, usuarios no-superadmin de una empresa
 * y luego resembla datos frescos.
 *
 * Uso: php app/scripts/reset_demo.php [empresa_id] [clientes] [productos] [presupuestos] [usuarios_pendientes] [usuarios_activos]
 * Ej:  php app/scripts/reset_demo.php 2 80 160 120 12 20
 */

function reset_empresa(int $empresaId): void {
    $db = db();
    
    echo "Limpiando empresa {$empresaId}...\n";
    
    try {
        $db->beginTransaction();
        
        // Eliminar ítems de presupuestos
        $db->query("DELETE FROM presupuesto_items 
                   WHERE presupuesto_id IN (SELECT id FROM presupuestos WHERE empresa_id = {$empresaId})");
        
        // Eliminar presupuestos
        $db->query("DELETE FROM presupuestos WHERE empresa_id = {$empresaId}");
        
        // Eliminar clientes
        $db->query("DELETE FROM clientes WHERE empresa_id = {$empresaId}");
        
        // Eliminar productos
        $db->query("DELETE FROM productos WHERE empresa_id = {$empresaId}");
        
        // Eliminar usuarios no-superadmin
        $db->query("DELETE FROM usuarios WHERE empresa_id = {$empresaId} AND is_superadmin = 0");
        
        $db->commit();
        echo "✓ Datos limpiados\n";
    } catch (Exception $e) {
        $db->rollBack();
        die("Error durante limpieza: " . $e->getMessage() . "\n");
    }
}

// Parsear args
$empresaId = 2;
$clientesCant = 80;
$productosCant = 160;
$presupuestosCant = 120;
$usuariosPend = 12;
$usuariosAct = 20;

if (PHP_SAPI === 'cli' && isset($argv[1])) {
    $empresaId = (int)$argv[1];
    $clientesCant = isset($argv[2]) ? (int)$argv[2] : $clientesCant;
    $productosCant = isset($argv[3]) ? (int)$argv[3] : $productosCant;
    $presupuestosCant = isset($argv[4]) ? (int)$argv[4] : $presupuestosCant;
    $usuariosPend = isset($argv[5]) ? (int)$argv[5] : $usuariosPend;
    $usuariosAct = isset($argv[6]) ? (int)$argv[6] : $usuariosAct;
}

// Limpiar
reset_empresa($empresaId);

// Resembrar
echo "\nResembrando datos...\n";
$seedDemoPath = dirname(__FILE__) . '/seed_demo.php';
$seedUsersPath = dirname(__FILE__) . '/seed_users.php';

if (file_exists($seedDemoPath)) {
    shell_exec("php {$seedDemoPath} {$empresaId} {$clientesCant} {$productosCant} {$presupuestosCant}");
    echo "✓ Datos demo resembrados\n";
}

if (file_exists($seedUsersPath)) {
    shell_exec("php {$seedUsersPath} {$empresaId} {$usuariosPend} {$usuariosAct} 5");
    echo "✓ Usuarios demo resembrados\n";
}

echo "\n✓ Reset completado para empresa {$empresaId}\n";
