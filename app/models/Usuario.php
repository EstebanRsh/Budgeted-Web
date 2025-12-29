<?php
declare(strict_types=1);

class Usuario
{
    public static function findByEmail(string $email): ?array
    {
        $sql = 'SELECT * FROM usuarios WHERE email = :email LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function verificarCredenciales(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if (!$user) return null;
        if (!password_verify($password, $user['password_hash'])) return null;
        return $user;
    }

    public static function obtenerTodos(): array
    {
        $sql = 'SELECT u.*, e.nombre as empresa_nombre 
                FROM usuarios u
                LEFT JOIN empresas e ON u.empresa_id = e.id
                ORDER BY u.created_at DESC';
        $stmt = db()->query($sql);
        return $stmt->fetchAll() ?: [];
    }

    public static function obtenerPendientes(?string $busqueda = null): array
    {
        $db = db();
        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT * FROM usuarios 
                    WHERE estado = 'en_espera' AND is_superadmin = 0 
                    AND (nombre LIKE :b1 OR email LIKE :b2)
                    ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':b1', $like, PDO::PARAM_STR);
            $stmt->bindValue(':b2', $like, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = $db->query("SELECT * FROM usuarios WHERE estado = 'en_espera' AND is_superadmin = 0 ORDER BY created_at DESC");
        }
        return $stmt->fetchAll() ?: [];
    }

    public static function contarPendientes(?string $busqueda = null): int
    {
        $db = db();
        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT COUNT(*) AS total FROM usuarios 
                    WHERE estado = 'en_espera' AND is_superadmin = 0 
                    AND (nombre LIKE :b1 OR email LIKE :b2)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':b1', $like, PDO::PARAM_STR);
            $stmt->bindValue(':b2', $like, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = $db->query("SELECT COUNT(*) AS total FROM usuarios WHERE estado = 'en_espera' AND is_superadmin = 0");
        }
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public static function listarPendientesPaginado(int $offset, int $limit, ?string $busqueda = null): array
    {
        $db = db();
        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT * FROM usuarios 
                    WHERE estado = 'en_espera' AND is_superadmin = 0 
                    AND (nombre LIKE :b1 OR email LIKE :b2)
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':b1', $like, PDO::PARAM_STR);
            $stmt->bindValue(':b2', $like, PDO::PARAM_STR);
        } else {
            $sql = "SELECT * FROM usuarios 
                    WHERE estado = 'en_espera' AND is_superadmin = 0 
                    ORDER BY created_at DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public static function contarActivos(?string $busqueda = null): int
    {
        $db = db();
        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT COUNT(*) AS total FROM usuarios 
                    WHERE estado IN ('activo','desactivado') AND is_superadmin = 0 
                    AND (nombre LIKE :b1 OR email LIKE :b2)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':b1', $like, PDO::PARAM_STR);
            $stmt->bindValue(':b2', $like, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $stmt = $db->query("SELECT COUNT(*) AS total FROM usuarios WHERE estado IN ('activo','desactivado') AND is_superadmin = 0");
        }
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public static function listarActivosPaginado(int $offset, int $limit, ?string $busqueda = null): array
    {
        $db = db();
        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT * FROM usuarios 
                    WHERE estado IN ('activo', 'desactivado') AND is_superadmin = 0 
                    AND (nombre LIKE :b1 OR email LIKE :b2)
                    ORDER BY CASE estado WHEN 'activo' THEN 1 WHEN 'desactivado' THEN 2 END, created_at DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':b1', $like, PDO::PARAM_STR);
            $stmt->bindValue(':b2', $like, PDO::PARAM_STR);
        } else {
            $sql = "SELECT * FROM usuarios 
                    WHERE estado IN ('activo', 'desactivado') AND is_superadmin = 0 
                    ORDER BY CASE estado WHEN 'activo' THEN 1 WHEN 'desactivado' THEN 2 END, created_at DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public static function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM usuarios WHERE id = :id LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function existeEmail(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $sql = 'SELECT COUNT(*) as total FROM usuarios WHERE email = :email AND id != :id';
            $stmt = db()->prepare($sql);
            $stmt->execute([':email' => $email, ':id' => $excludeId]);
        } else {
            $sql = 'SELECT COUNT(*) as total FROM usuarios WHERE email = :email';
            $stmt = db()->prepare($sql);
            $stmt->execute([':email' => $email]);
        }
        $result = $stmt->fetch();
        return ($result['total'] ?? 0) > 0;
    }

    public static function crear(array $datos): ?int
    {
        $sql = 'INSERT INTO usuarios (nombre, email, password_hash, empresa_id, estado, is_superadmin, created_at)
                VALUES (:nombre, :email, :password_hash, :empresa_id, :estado, 0, NOW())';

        $stmt = db()->prepare($sql);
        $result = $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':email' => $datos['email'],
            ':password_hash' => password_hash($datos['password'], PASSWORD_BCRYPT),
            ':empresa_id' => $datos['empresa_id'] ?? null,
            ':estado' => $datos['estado'] ?? 'en_espera',
        ]);

        return $result ? (int)db()->lastInsertId() : null;
    }

    public static function actualizar(int $id, array $datos): bool
    {
        $permitidos = ['nombre', 'email', 'empresa_id', 'estado'];
        
        $campos = [];
        $valores = [':id' => $id];

        foreach ($datos as $key => $value) {
            if (in_array($key, $permitidos, true)) {
                $campos[] = "$key = :$key";
                $valores[":$key"] = $value;
            }
        }

        if (isset($datos['password']) && !empty($datos['password'])) {
            $campos[] = "password_hash = :password_hash";
            $valores[':password_hash'] = password_hash($datos['password'], PASSWORD_BCRYPT);
        }

        if (empty($campos)) return false;

        $sql = 'UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = :id';
        $stmt = db()->prepare($sql);
        return $stmt->execute($valores);
    }

    public static function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM usuarios WHERE id = :id';
        $stmt = db()->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
