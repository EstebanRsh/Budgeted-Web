<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';

function rand_string(int $len): string {
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $out = '';
    for ($i=0;$i<$len;$i++) $out .= $chars[random_int(0, strlen($chars)-1)];
    return $out;
}

function seed_clientes(int $empresaId, int $cantidad = 50): void {
    $db = db();
    $sql = "INSERT INTO clientes (empresa_id, nombre, cuit_dni, condicion_iva, domicilio, telefono, email, observaciones, activo, created_at)
            VALUES (:empresa_id, :nombre, :cuit_dni, :condicion_iva, :domicilio, :telefono, :email, :observaciones, 1, NOW())";
    $stmt = $db->prepare($sql);
    for ($i=1; $i<=$cantidad; $i++) {
        $nombre = 'Cliente ' . rand_string(6) . ' ' . $i;
        $cuit = str_pad((string)random_int(20000000, 30999999), 8, '0', STR_PAD_LEFT);
        $condIva = ['Responsable Inscripto', 'Monotributo', 'Consumidor Final'][array_rand([0,1,2])];
        $dom = 'Calle ' . rand_string(5) . ' ' . random_int(100,999);
        $tel = '+54 11 ' . random_int(1000,9999) . '-' . random_int(1000,9999);
        $email = strtolower(str_replace(' ', '', $nombre)) . '@example.com';
        $obs = 'Observación auto ' . rand_string(10);
        $stmt->execute([
            ':empresa_id' => $empresaId,
            ':nombre' => $nombre,
            ':cuit_dni' => $cuit,
            ':condicion_iva' => $condIva,
            ':domicilio' => $dom,
            ':telefono' => $tel,
            ':email' => $email,
            ':observaciones' => $obs,
        ]);
    }
}

function seed_productos(int $empresaId, int $cantidad = 100): void {
    $db = db();
    $sql = "INSERT INTO productos (empresa_id, nombre, descripcion, precio_unitario, created_at)
            VALUES (:empresa_id, :nombre, :descripcion, :precio_unitario, NOW())";
    $stmt = $db->prepare($sql);
    for ($i=1; $i<=$cantidad; $i++) {
        $nombre = 'Producto ' . rand_string(6) . ' ' . $i;
        $desc = 'Descripción de ' . $nombre;
        $precio = random_int(500, 50000) / 100.0;
        $stmt->execute([
            ':empresa_id' => $empresaId,
            ':nombre' => $nombre,
            ':descripcion' => $desc,
            ':precio_unitario' => $precio,
        ]);
    }
}

function seed_presupuestos(int $empresaId, int $cantidad = 30): void {
    $db = db();

    // Obtener algunos clientes y productos existentes
    $clientes = $db->query("SELECT id FROM clientes WHERE empresa_id = " . (int)$empresaId . " LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
    $productos = $db->query("SELECT id, precio_unitario FROM productos WHERE empresa_id = " . (int)$empresaId . " LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($clientes) || empty($productos)) {
        echo "Sembrá clientes y productos primero.\n";
        return;
    }

    $sqlPres = "INSERT INTO presupuestos (empresa_id, numero, fecha_emision, cliente_id, estado, validez_dias, observaciones, total_general, created_at)
                VALUES (:empresa_id, :numero, :fecha_emision, :cliente_id, :estado, :validez_dias, :observaciones, :total_general, NOW())";
    $stmtPres = $db->prepare($sqlPres);

    $sqlItem = "INSERT INTO presupuesto_items (presupuesto_id, producto_id, descripcion, cantidad, precio_unitario, total)
                VALUES (:presupuesto_id, :producto_id, :descripcion, :cantidad, :precio_unitario, :total)";
    $stmtItem = $db->prepare($sqlItem);

    for ($i=1; $i<=$cantidad; $i++) {
        $numero = 'P-' . date('Y') . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
        $fecha = date('Y-m-d', strtotime('-' . random_int(0, 90) . ' days'));
        $clienteId = (int)$clientes[array_rand($clientes)];
        // Ponderar más presupuestos pendientes para probar bandejas de aprobación
        $estadoPool = ['Pendiente', 'Pendiente', 'Pendiente', 'Aceptado', 'Rechazado'];
        $estado = $estadoPool[array_rand($estadoPool)];
        $validez = [7, 15, 30][array_rand([0,1,2])];
        $obs = 'Observación presupuesto ' . rand_string(10);

        // Componer 2-5 ítems
        $itemsCount = random_int(2,5);
        $subtotal = 0.0;
        $items = [];
        for ($j=0; $j<$itemsCount; $j++) {
            $prod = $productos[array_rand($productos)];
            $cantidad = random_int(1,10);
            $precio = (float)$prod['precio_unitario'];
            $total = $cantidad * $precio;
            $subtotal += $total;
            $items[] = [
                'producto_id' => (int)$prod['id'],
                'descripcion' => 'Item auto ' . rand_string(8),
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'total' => $total,
            ];
        }
        $totalGeneral = $subtotal;

        $stmtPres->execute([
            ':empresa_id' => $empresaId,
            ':numero' => $numero,
            ':fecha_emision' => $fecha,
            ':cliente_id' => $clienteId,
            ':estado' => $estado,
            ':validez_dias' => $validez,
            ':observaciones' => $obs,
            ':total_general' => $totalGeneral,
        ]);
        $presupuestoId = (int)$db->lastInsertId();

        foreach ($items as $it) {
            $stmtItem->execute([
                ':presupuesto_id' => $presupuestoId,
                ':producto_id' => $it['producto_id'],
                ':descripcion' => $it['descripcion'],
                ':cantidad' => $it['cantidad'],
                ':precio_unitario' => $it['precio_unitario'],
                ':total' => $it['total'],
            ]);
        }
    }
}

// Detectar empresa actual (si hay sesión) o usar 1; en CLI permite pasar empresaId como primer argumento
$empresaId = current_empresa_id();
if (PHP_SAPI === 'cli' && isset($argv[1])) {
    $empresaId = (int)$argv[1];
}
if (!$empresaId) {
    $empresaId = 1;
}

// Parámetros opcionales desde CLI: empresaId clientes productos presupuestos
$clientesCant = 60;
$productosCant = 120;
$presupuestosCant = 40;

if (PHP_SAPI === 'cli') {
    $clientesCant = isset($argv[2]) ? (int)$argv[2] : $clientesCant;
    $productosCant = isset($argv[3]) ? (int)$argv[3] : $productosCant;
    $presupuestosCant = isset($argv[4]) ? (int)$argv[4] : $presupuestosCant;
}

seed_clientes($empresaId, max(1, $clientesCant));
seed_productos($empresaId, max(1, $productosCant));
seed_presupuestos($empresaId, max(1, $presupuestosCant));

echo "Datos demo sembrados para empresa {$empresaId}.\n";
