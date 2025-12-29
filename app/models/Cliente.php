<?php
declare(strict_types=1);

class Cliente
{
    /**
     * Lista clientes de una empresa, opcionalmente filtrados por búsqueda.
     *
     * Permite buscar por nombre, CUIT/DNI o email del cliente.
     * Los resultados se ordenan alfabéticamente por nombre.
     *
     * @param int $empresaId ID de la empresa propietaria
     * @param string|null $busqueda Término de búsqueda opcional (nombre, CUIT/DNI, email)
     * @return array Lista de clientes que coinciden con los criterios
     */
    public static function listarPorEmpresa(int $empresaId, ?string $busqueda = null): array
    {
        $db = db();

        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';

            $sql = "SELECT id, empresa_id, nombre, cuit_dni, condicion_iva, domicilio,
                           telefono, email, observaciones, activo, created_at
                    FROM clientes
                    WHERE empresa_id = :empresa_id
                      AND (
                            nombre   LIKE :busqueda_nombre
                         OR cuit_dni LIKE :busqueda_cuit
                         OR email    LIKE :busqueda_email
                      )
                    ORDER BY nombre ASC";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':busqueda_nombre', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_cuit', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_email', $like, PDO::PARAM_STR);
        } else {
            $sql = "SELECT id, empresa_id, nombre, cuit_dni, condicion_iva, domicilio,
                           telefono, email, observaciones, activo, created_at
                    FROM clientes
                    WHERE empresa_id = :empresa_id
                    ORDER BY nombre ASC";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll();

        return $rows ?: [];
    }

    /**
     * Crea un nuevo cliente para una empresa.
     *
     * Inserta un cliente en la base de datos con todos sus datos fiscales
     * y de contacto. La fecha de creación se establece automáticamente.
     *
     * @param int $empresaId ID de la empresa propietaria
     * @param string $nombre Razón social o nombre del cliente
     * @param string|null $cuitDni CUIT o DNI (formato validado externamente)
     * @param string|null $condicionIva Condición ante IVA (ej: Responsable Inscripto)
     * @param string|null $domicilio Dirección física del cliente
     * @param string|null $telefono Teléfono de contacto
     * @param string|null $email Correo electrónico
     * @param string|null $observaciones Notas adicionales sobre el cliente
     * @param bool $activo Estado del cliente (true = activo, false = inactivo)
     * @return int ID del cliente recién creado
     */
    public static function crear(
        int $empresaId,
        string $nombre,
        ?string $cuitDni,
        ?string $condicionIva,
        ?string $domicilio,
        ?string $telefono,
        ?string $email,
        ?string $observaciones,
        bool $activo
    ): int {
        $db = db();

        $sql = "INSERT INTO clientes
                    (empresa_id, nombre, cuit_dni, condicion_iva, domicilio,
                     telefono, email, observaciones, activo, created_at)
                VALUES
                    (:empresa_id, :nombre, :cuit_dni, :condicion_iva, :domicilio,
                     :telefono, :email, :observaciones, :activo, NOW())";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindValue(':cuit_dni', $cuitDni, PDO::PARAM_STR);
        $stmt->bindValue(':condicion_iva', $condicionIva, PDO::PARAM_STR);
        $stmt->bindValue(':domicilio', $domicilio, PDO::PARAM_STR);
        $stmt->bindValue(':telefono', $telefono, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':observaciones', $observaciones, PDO::PARAM_STR);
        $stmt->bindValue(':activo', $activo ? 1 : 0, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$db->lastInsertId();
    }

    /**
     * Obtiene un cliente por su ID verificando pertenencia a la empresa.
     *
     * @param int $id ID del cliente a buscar
     * @param int $empresaId ID de la empresa (validación de pertenencia)
     * @return array|null Datos del cliente o null si no existe/no pertenece
     */
    public static function obtenerPorId(int $id, int $empresaId): ?array
    {
        $db = db();

        $sql = "SELECT id, empresa_id, nombre, cuit_dni, condicion_iva, domicilio,
                       telefono, email, observaciones, activo, created_at
                FROM clientes
                WHERE id = :id AND empresa_id = :empresa_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Actualiza los datos de un cliente existente.
     *
     * Modifica todos los campos del cliente excepto ID, empresa_id y created_at.
     * Verifica que el cliente pertenezca a la empresa antes de actualizar.
     *
     * @param int $id ID del cliente a actualizar
     * @param int $empresaId ID de la empresa (validación de pertenencia)
     * @param string $nombre Nuevo nombre del cliente
     * @param string|null $cuitDni Nuevo CUIT/DNI
     * @param string|null $condicionIva Nueva condición IVA
     * @param string|null $domicilio Nuevo domicilio
     * @param string|null $telefono Nuevo teléfono
     * @param string|null $email Nuevo email
     * @param string|null $observaciones Nuevas observaciones
     * @param bool $activo Nuevo estado (activo/inactivo)
     * @return bool True si se actualizó al menos un registro
     */
    public static function actualizar(
        int $id,
        int $empresaId,
        string $nombre,
        ?string $cuitDni,
        ?string $condicionIva,
        ?string $domicilio,
        ?string $telefono,
        ?string $email,
        ?string $observaciones,
        bool $activo
    ): bool {
        $db = db();

        $sql = "UPDATE clientes
                SET nombre        = :nombre,
                    cuit_dni      = :cuit_dni,
                    condicion_iva = :condicion_iva,
                    domicilio     = :domicilio,
                    telefono      = :telefono,
                    email         = :email,
                    observaciones = :observaciones,
                    activo        = :activo
                WHERE id = :id AND empresa_id = :empresa_id";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindValue(':cuit_dni', $cuitDni, PDO::PARAM_STR);
        $stmt->bindValue(':condicion_iva', $condicionIva, PDO::PARAM_STR);
        $stmt->bindValue(':domicilio', $domicilio, PDO::PARAM_STR);
        $stmt->bindValue(':telefono', $telefono, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':observaciones', $observaciones, PDO::PARAM_STR);
        $stmt->bindValue(':activo', $activo ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Elimina permanentemente un cliente de la base de datos.
     *
     * ADVERTENCIA: Baja física. El cliente será eliminado completamente.
     * Verifica pertenencia a la empresa antes de eliminar.
     *
     * @param int $id ID del cliente a eliminar
     * @param int $empresaId ID de la empresa (validación de pertenencia)
     * @return bool True si se eliminó correctamente
     */
    public static function eliminar(int $id, int $empresaId): bool
    {
        $db = db();

        $sql = "DELETE FROM clientes
                WHERE id = :id AND empresa_id = :empresa_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Cuenta total de clientes con búsqueda opcional.
     */
    public static function contarPorEmpresa(int $empresaId, ?string $busqueda = null): int
    {
        $db = db();

        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT COUNT(*) as total FROM clientes
                    WHERE empresa_id = :empresa_id
                      AND (nombre LIKE :busqueda_nombre
                        OR cuit_dni LIKE :busqueda_cuit
                        OR email LIKE :busqueda_email)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':busqueda_nombre', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_cuit', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_email', $like, PDO::PARAM_STR);
        } else {
            $sql = "SELECT COUNT(*) as total FROM clientes WHERE empresa_id = :empresa_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Lista clientes con paginación.
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
            $sql = "SELECT id, empresa_id, nombre, cuit_dni, condicion_iva, domicilio,
                           telefono, email, observaciones, activo, created_at
                    FROM clientes
                    WHERE empresa_id = :empresa_id
                      AND (nombre LIKE :busqueda_nombre
                        OR cuit_dni LIKE :busqueda_cuit
                        OR email LIKE :busqueda_email)
                    ORDER BY nombre ASC
                    LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':busqueda_nombre', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_cuit', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_email', $like, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        } else {
            $sql = "SELECT id, empresa_id, nombre, cuit_dni, condicion_iva, domicilio,
                           telefono, email, observaciones, activo, created_at
                    FROM clientes
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
