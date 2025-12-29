<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';

function rand_string_user(int $len): string {
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $out;
}

function seed_usuarios(int $empresaId, int $pendientes = 10, int $activos = 15, int $desactivados = 5): void {
    $db = db();

    $insert = $db->prepare("INSERT INTO usuarios (empresa_id, nombre, email, password_hash, is_superadmin, estado, created_at)
        VALUES (:empresa_id, :nombre, :email, :password_hash, 0, :estado, NOW())");

    $password = password_hash('demo1234', PASSWORD_BCRYPT);

    $crear = function (int $cant, string $estado) use ($insert, $empresaId, $password): void {
        for ($i = 0; $i < $cant; $i++) {
            $nombre = ucfirst(rand_string_user(6)) . ' ' . ucfirst(rand_string_user(6));
            $email = strtolower(str_replace(' ', '', $nombre)) . random_int(100, 9999) . '@example.com';
            $insert->execute([
                ':empresa_id'    => $empresaId,
                ':nombre'        => $nombre,
                ':email'         => $email,
                ':password_hash' => $password,
                ':estado'        => $estado,
            ]);
        }
    };

    $crear($pendientes, 'en_espera');
    $crear($activos, 'activo');
    $crear($desactivados, 'desactivado');
}

// CLI args: empresaId pendientes activos desactivados
$empresaId = 1;
$pend = 10;
$act = 15;
$des = 5;

if (PHP_SAPI === 'cli') {
    $empresaId = isset($argv[1]) ? (int)$argv[1] : $empresaId;
    $pend = isset($argv[2]) ? (int)$argv[2] : $pend;
    $act = isset($argv[3]) ? (int)$argv[3] : $act;
    $des = isset($argv[4]) ? (int)$argv[4] : $des;
}

seed_usuarios(max(1, $empresaId), max(0, $pend), max(0, $act), max(0, $des));

echo "Usuarios demo creados para empresa {$empresaId}: pendientes={$pend}, activos={$act}, desactivados={$des}\n";
