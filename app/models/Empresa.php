<?php
declare(strict_types=1);

class Empresa
{
    /**
     * Obtiene los datos de una empresa por ID.
     */
    public static function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM empresas WHERE id = :id LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $empresa = $stmt->fetch();

        return $empresa ?: null;
    }

    /**
     * Crea una nueva empresa.
     */
    public static function crear(int $usuarioId, array $datos): ?int
    {
        $permitidos = [
            'nombre',
            'cuit',
            'email',
            'domicilio',
            'telefono',
            'web',
            'condicion_iva',
            'inicio_actividades',
            'ingresos_brutos',
            'logo_path'
        ];

        $campos = ['usuario_id'];
        $placeholders = [':usuario_id'];
        $valores = [':usuario_id' => $usuarioId];

        foreach ($datos as $key => $value) {
            if (in_array($key, $permitidos, true)) {
                $campos[] = $key;
                $placeholders[] = ":$key";
                $valores[":$key"] = $value;
            }
        }

        $sql = 'INSERT INTO empresas (' . implode(', ', $campos) . ') 
                VALUES (' . implode(', ', $placeholders) . ')';
        
        $stmt = db()->prepare($sql);
        
        if ($stmt->execute($valores)) {
            return (int) db()->lastInsertId();
        }
        
        return null;
    }

    /**
     * Actualiza los datos de una empresa.
     */
    public static function actualizar(int $id, array $datos): bool
    {
        $permitidos = [
            'nombre',
            'cuit',
            'email',
            'domicilio',
            'telefono',
            'web',
            'condicion_iva',
            'inicio_actividades',
            'ingresos_brutos',
            'logo_path'
        ];

        $campos = [];
        $valores = [':id' => $id];

        foreach ($datos as $key => $value) {
            if (in_array($key, $permitidos, true)) {
                $campos[] = "$key = :$key";
                $valores[":$key"] = $value;
            }
        }

        if (empty($campos)) {
            return false;
        }

        $sql = 'UPDATE empresas SET ' . implode(', ', $campos) . ' WHERE id = :id';
        $stmt = db()->prepare($sql);

        return $stmt->execute($valores);
    }

    /**
     * Obtiene todas las empresas (para superadmin).
     */
    public static function obtenerTodas(): array
    {
        $sql = 'SELECT * FROM empresas ORDER BY nombre ASC';
        $stmt = db()->query($sql);

        return $stmt->fetchAll() ?: [];
    }
}
