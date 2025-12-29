<?php
declare(strict_types=1);

class Producto
{
    public static function listarPorEmpresa(int $empresaId, ?string $busqueda = null): array
    {
        $db = db();

        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';

            $sql = "SELECT id, empresa_id, nombre, descripcion, precio_unitario, created_at
                    FROM productos
                    WHERE empresa_id = :empresa_id
                      AND (nombre LIKE :busqueda_nombre OR descripcion LIKE :busqueda_desc)
                    ORDER BY nombre ASC";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':busqueda_nombre', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_desc', $like, PDO::PARAM_STR);
        } else {
            $sql = "SELECT id, empresa_id, nombre, descripcion, precio_unitario, created_at
                    FROM productos
                    WHERE empresa_id = :empresa_id
                    ORDER BY nombre ASC";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll();

        return $rows ?: [];
    }

    public static function actualizarPrecio(int $id, int $empresaId, float $precio): ?array
    {
        $db = db();

        $sql = "UPDATE productos
                SET precio_unitario = :precio
                WHERE id = :id AND empresa_id = :empresa_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':precio', $precio);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $sqlSel = "SELECT id, empresa_id, nombre, descripcion, precio_unitario, created_at
                   FROM productos
                   WHERE id = :id AND empresa_id = :empresa_id";
        $stmtSel = $db->prepare($sqlSel);
        $stmtSel->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtSel->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmtSel->execute();

        $row = $stmtSel->fetch();

        return $row ?: null;
    }

    public static function crear(int $empresaId, string $nombre, ?string $descripcion, float $precio): int
    {
        $db = db();

        $sql = "INSERT INTO productos (empresa_id, nombre, descripcion, precio_unitario, created_at)
                VALUES (:empresa_id, :nombre, :descripcion, :precio, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':precio', $precio);
        $stmt->execute();

        return (int)$db->lastInsertId();
    }

    public static function obtenerPorId(int $id, int $empresaId): ?array
    {
        $db = db();

        $sql = "SELECT id, empresa_id, nombre, descripcion, precio_unitario, created_at
                FROM productos
                WHERE id = :id AND empresa_id = :empresa_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function actualizar(int $id, int $empresaId, string $nombre, ?string $descripcion, float $precio): bool
    {
        $db = db();

        $sql = "UPDATE productos
                SET nombre = :nombre,
                    descripcion = :descripcion,
                    precio_unitario = :precio
                WHERE id = :id AND empresa_id = :empresa_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':precio', $precio);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public static function eliminar(int $id, int $empresaId): bool
    {
        $db = db();

        $sql = "DELETE FROM productos
                WHERE id = :id AND empresa_id = :empresa_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Cuenta total de productos con búsqueda opcional.
     */
    public static function contarPorEmpresa(int $empresaId, ?string $busqueda = null): int
    {
        $db = db();

        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT COUNT(*) as total FROM productos
                    WHERE empresa_id = :empresa_id
                      AND (nombre LIKE :busqueda_nombre OR descripcion LIKE :busqueda_desc)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':busqueda_nombre', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_desc', $like, PDO::PARAM_STR);
        } else {
            $sql = "SELECT COUNT(*) as total FROM productos WHERE empresa_id = :empresa_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Lista productos con paginación.
     */
    public static function listarPorEmpresaPaginado(
        int $empresaId,
        int $offset,
        int $limit,
        ?string $busqueda = null
    ): array {
        $db = db();

        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT id, empresa_id, nombre, descripcion, precio_unitario, created_at
                    FROM productos
                    WHERE empresa_id = :empresa_id
                      AND (nombre LIKE :busqueda_nombre OR descripcion LIKE :busqueda_desc)
                    ORDER BY nombre ASC
                    LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':busqueda_nombre', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_desc', $like, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        } else {
            $sql = "SELECT id, empresa_id, nombre, descripcion, precio_unitario, created_at
                    FROM productos
                    WHERE empresa_id = :empresa_id
                    ORDER BY nombre ASC
                    LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $rows ?: [];
    }
}

