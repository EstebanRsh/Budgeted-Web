<?php
declare(strict_types=1);

class Presupuesto
{
    /**
     * Devuelve datos de resumen para el dashboard.
     */
    public static function obtenerResumen(?int $empresaId = null): array
    {
        $db = db();

        // Total de presupuestos + suma
        if ($empresaId !== null) {
            $sqlPres = "SELECT COUNT(*) AS total_presupuestos,
                               COALESCE(SUM(total_general), 0) AS suma_presupuestos
                        FROM presupuestos
                        WHERE empresa_id = :empresa_id";
            $stmtPres = $db->prepare($sqlPres);
            $stmtPres->execute([':empresa_id' => $empresaId]);
        } else {
            $sqlPres = "SELECT COUNT(*) AS total_presupuestos,
                               COALESCE(SUM(total_general), 0) AS suma_presupuestos
                        FROM presupuestos";
            $stmtPres = $db->query($sqlPres);
        }

        $rowPres = $stmtPres->fetch() ?: [
            'total_presupuestos' => 0,
            'suma_presupuestos'  => 0,
        ];

        // Total de clientes
        if ($empresaId !== null) {
            $sqlCli = "SELECT COUNT(*) AS total_clientes
                       FROM clientes
                       WHERE empresa_id = :empresa_id";
            $stmtCli = $db->prepare($sqlCli);
            $stmtCli->execute([':empresa_id' => $empresaId]);
        } else {
            $sqlCli = "SELECT COUNT(*) AS total_clientes
                       FROM clientes";
            $stmtCli = $db->query($sqlCli);
        }

        $rowCli = $stmtCli->fetch() ?: ['total_clientes' => 0];

        return [
            'total_presupuestos' => (int)$rowPres['total_presupuestos'],
            'suma_presupuestos'  => (float)$rowPres['suma_presupuestos'],
            'total_clientes'     => (int)$rowCli['total_clientes'],
        ];
    }

    /**
     * Devuelve los últimos N presupuestos.
     */
    public static function ultimosPresupuestos(?int $empresaId = null, int $limite = 5): array
    {
        $db = db();

        if ($empresaId !== null) {
            $sql = "SELECT p.id,
                           p.numero,
                           p.fecha_emision,
                           p.estado,
                           p.total_general,
                           c.nombre AS cliente_nombre
                    FROM presupuestos p
                    INNER JOIN clientes c ON c.id = p.cliente_id
                    WHERE p.empresa_id = :empresa_id
                    ORDER BY p.fecha_emision DESC, p.id DESC
                    LIMIT :limite";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "SELECT p.id,
                           p.numero,
                           p.fecha_emision,
                           p.estado,
                           p.total_general,
                           c.nombre AS cliente_nombre
                    FROM presupuestos p
                    INNER JOIN clientes c ON c.id = p.cliente_id
                    ORDER BY p.fecha_emision DESC, p.id DESC
                    LIMIT :limite";
            $stmt = db()->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
        }

        $rows = $stmt->fetchAll();

        return $rows ?: [];
    }

    /**
     * Lista todos los presupuestos de una empresa con datos del cliente.
     *
     * Soporta búsqueda por número de presupuesto, nombre del cliente o estado.
     * Los resultados se ordenan por fecha de emisión descendente.
     *
     * @param int $empresaId ID de la empresa propietaria
     * @param string|null $busqueda Término de búsqueda opcional (busca en número, cliente, estado)
     * @return array Lista de presupuestos con datos relacionados del cliente
     */
    public static function listarPorEmpresa(int $empresaId, ?string $busqueda = null): array
{
    $db = db();

    if ($busqueda !== null && $busqueda !== '') {
        $like = '%' . $busqueda . '%';

        $sql = "SELECT p.id,
                       p.empresa_id,
                       p.cliente_id,
                       p.numero,
                       p.fecha_emision,
                      p.estado,
                      p.validez_dias,
                      p.observaciones,
                      p.total_general,
                      p.created_at,
                       c.nombre AS cliente_nombre
                FROM presupuestos p
                INNER JOIN clientes c ON c.id = p.cliente_id
                WHERE p.empresa_id = :empresa_id
                  AND (
                        p.numero LIKE :busqueda_num
                     OR c.nombre LIKE :busqueda_cliente
                     OR p.estado LIKE :busqueda_estado
                  )
                ORDER BY p.fecha_emision DESC, p.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':busqueda_num', $like, PDO::PARAM_STR);
        $stmt->bindValue(':busqueda_cliente', $like, PDO::PARAM_STR);
        $stmt->bindValue(':busqueda_estado', $like, PDO::PARAM_STR);
    } else {
        $sql = "SELECT p.id,
                       p.empresa_id,
                       p.cliente_id,
                       p.numero,
                       p.fecha_emision,
                      p.estado,
                      p.validez_dias,
                      p.total_general,
                      p.created_at,
                       c.nombre AS cliente_nombre
                FROM presupuestos p
                INNER JOIN clientes c ON c.id = p.cliente_id
                WHERE p.empresa_id = :empresa_id
                ORDER BY p.fecha_emision DESC, p.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll();

    return $rows ?: [];
}

/**
 * Obtiene un presupuesto completo con toda la información relacionada.
 *
 * Incluye datos del presupuesto, cliente, empresa y todos los ítems asociados.
 * Realiza un JOIN con las tablas clientes y empresas para obtener información completa.
 * Los ítems se ordenan por ID ascendente (orden de inserción).
 *
 * @param int $id ID del presupuesto a obtener
 * @param int $empresaId ID de la empresa (validación de pertenencia)
 * @return array|null Presupuesto completo con ítems, o null si no existe
 */
public static function obtenerConItems(int $id, int $empresaId): ?array
{
    $db = db();

    $sqlPres = "SELECT p.id,
                       p.empresa_id,
                       p.cliente_id,
                       p.numero,
                       p.fecha_emision,
                       p.estado,
                       p.total_general,
                       p.validez_dias,
                       p.observaciones,
                       p.created_at,
                       p.updated_at,
                       c.nombre        AS cliente_nombre,
                       c.cuit_dni      AS cliente_cuit_dni,
                       c.condicion_iva AS cliente_condicion_iva,
                       c.domicilio     AS cliente_domicilio,
                       c.email         AS cliente_email,
                       c.telefono      AS cliente_telefono,
                       e.nombre        AS empresa_nombre,
                       e.cuit          AS empresa_cuit,
                       e.domicilio     AS empresa_domicilio,
                       e.telefono      AS empresa_telefono,
                       e.email         AS empresa_email,
                       e.web           AS empresa_web,
                       e.condicion_iva AS empresa_condicion_iva,
                       e.inicio_actividades AS empresa_inicio_actividades,
                       e.ingresos_brutos AS empresa_iibb,
                       e.logo_path     AS empresa_logo_path
                FROM presupuestos p
                INNER JOIN clientes c ON c.id = p.cliente_id
                INNER JOIN empresas e ON e.id = p.empresa_id
                WHERE p.id = :id AND p.empresa_id = :empresa_id";

    $stmt = $db->prepare($sqlPres);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
    $stmt->execute();

    $presupuesto = $stmt->fetch();
    if (!$presupuesto) {
        return null;
    }

    $sqlItems = "SELECT i.id,
                        i.presupuesto_id,
                        i.producto_id,
                        i.descripcion,
                        i.cantidad,
                        i.precio_unitario,
                        i.total
                 FROM presupuesto_items i
                 WHERE i.presupuesto_id = :presupuesto_id
                 ORDER BY i.id ASC";

    $stmtItems = $db->prepare($sqlItems);
    $stmtItems->bindValue(':presupuesto_id', $presupuesto['id'], PDO::PARAM_INT);
    $stmtItems->execute();

    $items = $stmtItems->fetchAll() ?: [];

    $presupuesto['items'] = $items;

    return $presupuesto;
}

    /**
     * Genera el próximo número de presupuesto para la empresa.
     * Formato: P-0001, P-0002, etc.
     */
    private static function generarNumero(int $empresaId): string
    {
        $db = db();

        $sql = "SELECT numero
                FROM presupuestos
                WHERE empresa_id = :empresa_id
                ORDER BY id DESC
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();

        $ultimo = $stmt->fetchColumn();

        $siguiente = 1;
        if ($ultimo && preg_match('/(\d+)$/', (string)$ultimo, $m)) {
            $siguiente = (int)$m[1] + 1;
        }

        return sprintf('P-%04d', $siguiente);
    }

    /**
     * Crea un presupuesto con sus ítems en una transacción.
     *
     * $items: array de arrays:
     * [
     *   'producto_id'     => int|null,
     *   'descripcion'     => string,
     *   'cantidad'        => float,
     *   'precio_unitario' => float,
     * ]
     */
    public static function crearConItems(
        int $empresaId,
        int $clienteId,
        string $fechaEmision,
        ?string $estado,
        int $validezDias,
        ?string $observaciones,
        array $items
    ): int {
        $db = db();

        $numero = self::generarNumero($empresaId);
        $estado = $estado !== null && $estado !== '' ? $estado : 'Pendiente';

        try {
            $db->beginTransaction();

            $sqlPres = "INSERT INTO presupuestos
                        (empresa_id, cliente_id, numero, fecha_emision, estado, validez_dias, observaciones, total_general, created_at)
                        VALUES
                        (:empresa_id, :cliente_id, :numero, :fecha_emision, :estado, :validez_dias, :observaciones, 0, NOW())";
            $stmtPres = $db->prepare($sqlPres);
            $stmtPres->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmtPres->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmtPres->bindValue(':numero', $numero, PDO::PARAM_STR);
            $stmtPres->bindValue(':fecha_emision', $fechaEmision, PDO::PARAM_STR);
            $stmtPres->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmtPres->bindValue(':validez_dias', $validezDias, PDO::PARAM_INT);
            $stmtPres->bindValue(':observaciones', $observaciones, PDO::PARAM_STR);
            $stmtPres->execute();

            $presupuestoId = (int)$db->lastInsertId();

            $totalGeneral = 0.0;

            $sqlItem = "INSERT INTO presupuesto_items
                        (presupuesto_id, producto_id, descripcion, cantidad, precio_unitario, total)
                        VALUES
                        (:presupuesto_id, :producto_id, :descripcion, :cantidad, :precio_unitario, :total)";
            $stmtItem = $db->prepare($sqlItem);

            foreach ($items as $item) {
                $productoId     = isset($item['producto_id']) ? (int)$item['producto_id'] : 0;
                $descripcion    = $item['descripcion'] ?? '';
                $cantidad       = (float)($item['cantidad'] ?? 0);
                $precioUnitario = (float)($item['precio_unitario'] ?? 0);

                if ($descripcion === '' || $cantidad <= 0 || $precioUnitario <= 0) {
                    continue;
                }

                $totalLinea = $cantidad * $precioUnitario;

                $stmtItem->bindValue(':presupuesto_id', $presupuestoId, PDO::PARAM_INT);

                if ($productoId > 0) {
                    $stmtItem->bindValue(':producto_id', $productoId, PDO::PARAM_INT);
                } else {
                    $stmtItem->bindValue(':producto_id', null, PDO::PARAM_NULL);
                }

                $stmtItem->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                $stmtItem->bindValue(':cantidad', $cantidad);
                $stmtItem->bindValue(':precio_unitario', $precioUnitario);
                $stmtItem->bindValue(':total', $totalLinea);
                $stmtItem->execute();

                $totalGeneral += $totalLinea;
            }

            $sqlUpdateTotal = "UPDATE presupuestos
                               SET total_general = :total_general
                               WHERE id = :id";
            $stmtTotal = $db->prepare($sqlUpdateTotal);
            $stmtTotal->bindValue(':total_general', $totalGeneral);
            $stmtTotal->bindValue(':id', $presupuestoId, PDO::PARAM_INT);
            $stmtTotal->execute();

            $db->commit();

            return $presupuestoId;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Obtiene un presupuesto simple por ID.
     */
    public static function obtenerPorId(int $id, int $empresaId): ?array
    {
        $sql = "SELECT p.* FROM presupuestos p
                WHERE p.id = :id AND p.empresa_id = :empresa_id";
        $stmt = db()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch() ?: null;
    }

    /**
     * Actualiza un presupuesto.
     */
    public static function actualizar(int $id, int $empresaId, array $datos): bool
    {
        $permitidos = ['cliente_id', 'fecha_emision', 'estado', 'validez_dias', 'observaciones'];
        
        $campos = [];
        $valores = [':id' => $id, ':empresa_id' => $empresaId];

        foreach ($datos as $key => $value) {
            if (in_array($key, $permitidos, true)) {
                $campos[] = "$key = :$key";
                $valores[":$key"] = $value;
            }
        }

        if (empty($campos)) {
            return false;
        }

        $sql = 'UPDATE presupuestos SET ' . implode(', ', $campos) . ', updated_at = NOW() WHERE id = :id AND empresa_id = :empresa_id';
        $stmt = db()->prepare($sql);

        return $stmt->execute($valores);
    }

    /**
     * Actualiza los ítems de un presupuesto en una transacción.
     */
    public static function actualizarItems(int $presupuestoId, array $items): bool
    {
        $db = db();

        try {
            $db->beginTransaction();

            // Eliminar ítems existentes
            $sqlDelete = "DELETE FROM presupuesto_items WHERE presupuesto_id = :presupuesto_id";
            $stmtDelete = $db->prepare($sqlDelete);
            $stmtDelete->bindValue(':presupuesto_id', $presupuestoId, PDO::PARAM_INT);
            $stmtDelete->execute();

            $totalGeneral = 0.0;

            // Insertar nuevos ítems
            $sqlInsert = "INSERT INTO presupuesto_items
                          (presupuesto_id, producto_id, descripcion, cantidad, precio_unitario, total)
                          VALUES
                          (:presupuesto_id, :producto_id, :descripcion, :cantidad, :precio_unitario, :total)";
            $stmtInsert = $db->prepare($sqlInsert);

            foreach ($items as $item) {
                $productoId     = isset($item['producto_id']) ? (int)$item['producto_id'] : 0;
                $descripcion    = $item['descripcion'] ?? '';
                $cantidad       = (float)($item['cantidad'] ?? 0);
                $precioUnitario = (float)($item['precio_unitario'] ?? 0);

                if ($descripcion === '' || $cantidad <= 0 || $precioUnitario <= 0) {
                    continue;
                }

                $totalLinea = $cantidad * $precioUnitario;

                $stmtInsert->bindValue(':presupuesto_id', $presupuestoId, PDO::PARAM_INT);
                $stmtInsert->bindValue(':producto_id', $productoId > 0 ? $productoId : null, PDO::PARAM_INT);
                $stmtInsert->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
                $stmtInsert->bindValue(':cantidad', $cantidad);
                $stmtInsert->bindValue(':precio_unitario', $precioUnitario);
                $stmtInsert->bindValue(':total', $totalLinea);
                $stmtInsert->execute();

                $totalGeneral += $totalLinea;
            }

            // Actualizar total del presupuesto
            $sqlUpdateTotal = "UPDATE presupuestos SET total_general = :total_general WHERE id = :id";
            $stmtUpdateTotal = $db->prepare($sqlUpdateTotal);
            $stmtUpdateTotal->bindValue(':total_general', $totalGeneral);
            $stmtUpdateTotal->bindValue(':id', $presupuestoId, PDO::PARAM_INT);
            $stmtUpdateTotal->execute();

            $db->commit();

            return true;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Duplica un presupuesto (crea uno nuevo con los mismos datos).
     */
    public static function duplicar(int $id, int $empresaId): int
    {
        $db = db();

        // Obtener presupuesto original con items
        $presupuestoOriginal = self::obtenerConItems($id, $empresaId);
        if (!$presupuestoOriginal) {
            throw new RuntimeException("Presupuesto no encontrado");
        }

        // Generar nuevo número
        $numero = self::generarNumero($empresaId);

        try {
            $db->beginTransaction();

            // Crear nuevo presupuesto
            $sqlPres = "INSERT INTO presupuestos
                        (empresa_id, cliente_id, numero, fecha_emision, estado, validez_dias, observaciones, total_general, created_at)
                        VALUES
                        (:empresa_id, :cliente_id, :numero, CURDATE(), :estado, :validez_dias, :observaciones, :total_general, NOW())";
            $stmtPres = $db->prepare($sqlPres);
            $stmtPres->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmtPres->bindValue(':cliente_id', $presupuestoOriginal['cliente_id'], PDO::PARAM_INT);
            $stmtPres->bindValue(':numero', $numero, PDO::PARAM_STR);
            $stmtPres->bindValue(':estado', 'Pendiente', PDO::PARAM_STR);
            $stmtPres->bindValue(':validez_dias', $presupuestoOriginal['validez_dias'], PDO::PARAM_INT);
            $stmtPres->bindValue(':observaciones', $presupuestoOriginal['observaciones'], PDO::PARAM_STR);
            $stmtPres->bindValue(':total_general', $presupuestoOriginal['total_general']);
            $stmtPres->execute();

            $presupuestoNuevoId = (int)$db->lastInsertId();

            // Copiar ítems
            $sqlItem = "INSERT INTO presupuesto_items
                        (presupuesto_id, producto_id, descripcion, cantidad, precio_unitario, total)
                        VALUES
                        (:presupuesto_id, :producto_id, :descripcion, :cantidad, :precio_unitario, :total)";
            $stmtItem = $db->prepare($sqlItem);

            foreach ($presupuestoOriginal['items'] as $item) {
                $stmtItem->bindValue(':presupuesto_id', $presupuestoNuevoId, PDO::PARAM_INT);
                $stmtItem->bindValue(':producto_id', $item['producto_id'] ?? null, PDO::PARAM_INT);
                $stmtItem->bindValue(':descripcion', $item['descripcion'], PDO::PARAM_STR);
                $stmtItem->bindValue(':cantidad', $item['cantidad']);
                $stmtItem->bindValue(':precio_unitario', $item['precio_unitario']);
                $stmtItem->bindValue(':total', $item['total']);
                $stmtItem->execute();
            }

            $db->commit();

            return $presupuestoNuevoId;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Elimina un presupuesto y sus ítems (baja física).
     */
    public static function eliminar(int $id, int $empresaId): bool
    {
        $db = db();

        try {
            $db->beginTransaction();

            // Eliminar ítems
            $sqlItems = "DELETE FROM presupuesto_items WHERE presupuesto_id = :presupuesto_id";
            $stmtItems = $db->prepare($sqlItems);
            $stmtItems->bindValue(':presupuesto_id', $id, PDO::PARAM_INT);
            $stmtItems->execute();

            // Eliminar presupuesto
            $sqlPres = "DELETE FROM presupuestos WHERE id = :id AND empresa_id = :empresa_id";
            $stmtPres = $db->prepare($sqlPres);
            $stmtPres->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtPres->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $resultado = $stmtPres->execute();

            $db->commit();

            return $resultado;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Cuenta total de presupuestos con búsqueda opcional.
     */
    public static function contarPorEmpresa(int $empresaId, ?string $busqueda = null): int
    {
        $db = db();

        if ($busqueda !== null && $busqueda !== '') {
            $like = '%' . $busqueda . '%';
            $sql = "SELECT COUNT(*) as total FROM presupuestos p
                    INNER JOIN clientes c ON p.cliente_id = c.id
                    WHERE p.empresa_id = :empresa_id
                      AND (p.numero LIKE :busqueda
                        OR c.nombre LIKE :busqueda_cliente
                        OR p.observaciones LIKE :busqueda_obs)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':busqueda', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_cliente', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_obs', $like, PDO::PARAM_STR);
        } else {
            $sql = "SELECT COUNT(*) as total FROM presupuestos WHERE empresa_id = :empresa_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Lista presupuestos con paginación.
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
                        $sql = "SELECT p.id, p.numero, p.fecha_emision, p.cliente_id, c.nombre AS cliente_nombre,
                                                     p.estado, p.validez_dias,
                                                     p.total_general, p.observaciones, p.created_at
                    FROM presupuestos p
                    INNER JOIN clientes c ON p.cliente_id = c.id
                    WHERE p.empresa_id = :empresa_id
                      AND (p.numero LIKE :busqueda
                        OR c.nombre LIKE :busqueda_cliente
                        OR p.observaciones LIKE :busqueda_obs)
                                        ORDER BY p.fecha_emision DESC, p.numero DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':busqueda', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_cliente', $like, PDO::PARAM_STR);
            $stmt->bindValue(':busqueda_obs', $like, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        } else {
                 $sql = "SELECT p.id, p.numero, p.fecha_emision, p.cliente_id, c.nombre AS cliente_nombre,
                          p.estado, p.validez_dias,
                          p.total_general, p.observaciones, p.created_at
                    FROM presupuestos p
                    INNER JOIN clientes c ON p.cliente_id = c.id
                    WHERE p.empresa_id = :empresa_id
                      ORDER BY p.fecha_emision DESC, p.numero DESC
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

