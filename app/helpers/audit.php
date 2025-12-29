<?php
declare(strict_types=1);

/**
 * Registra una acción en el log de auditoría.
 *
 * @param string $action Acción realizada: 'create', 'update', 'delete', 'login', 'logout', etc.
 * @param string $entityType Tipo de entidad: 'presupuesto', 'cliente', 'producto', 'usuario', etc.
 * @param int|null $entityId ID de la entidad afectada
 * @param array|null $oldValues Valores anteriores (para updates)
 * @param array|null $newValues Valores nuevos (para creates/updates)
 * @return bool
 */
function audit_log(
    string $action,
    string $entityType,
    ?int $entityId = null,
    ?array $oldValues = null,
    ?array $newValues = null
): bool {
    try {
        $db = db();

        $userId = $_SESSION['user_id'] ?? null;
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // Limitar tamaño del user agent
        if ($userAgent && strlen($userAgent) > 255) {
            $userAgent = substr($userAgent, 0, 255);
        }

        $sql = "INSERT INTO audit_logs 
                (user_id, empresa_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                VALUES 
                (:user_id, :empresa_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent)";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':action', $action, PDO::PARAM_STR);
        $stmt->bindValue(':entity_type', $entityType, PDO::PARAM_STR);
        $stmt->bindValue(':entity_id', $entityId, PDO::PARAM_INT);
        $stmt->bindValue(':old_values', $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null, PDO::PARAM_STR);
        $stmt->bindValue(':new_values', $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null, PDO::PARAM_STR);
        $stmt->bindValue(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt->bindValue(':user_agent', $userAgent, PDO::PARAM_STR);

        return $stmt->execute();
    } catch (Throwable $e) {
        // Log el error pero no interrumpir la aplicación
        error_log('Error al registrar audit log: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene logs de auditoría con filtros opcionales.
 *
 * @param array $filters Filtros: user_id, empresa_id, action, entity_type, entity_id, limit, offset
 * @return array
 */
function get_audit_logs(array $filters = []): array
{
    try {
        $db = db();

        $where = [];
        $params = [];

        if (isset($filters['user_id'])) {
            $where[] = 'user_id = :user_id';
            $params[':user_id'] = (int)$filters['user_id'];
        }

        if (isset($filters['empresa_id'])) {
            $where[] = 'empresa_id = :empresa_id';
            $params[':empresa_id'] = (int)$filters['empresa_id'];
        }

        if (isset($filters['action'])) {
            $where[] = 'action = :action';
            $params[':action'] = $filters['action'];
        }

        if (isset($filters['entity_type'])) {
            $where[] = 'entity_type = :entity_type';
            $params[':entity_type'] = $filters['entity_type'];
        }

        if (isset($filters['entity_id'])) {
            $where[] = 'entity_id = :entity_id';
            $params[':entity_id'] = (int)$filters['entity_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 100;
        $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;

        $sql = "SELECT al.*, u.nombre as user_nombre, u.email as user_email
                FROM audit_logs al
                LEFT JOIN usuarios u ON al.user_id = u.id
                $whereClause
                ORDER BY al.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    } catch (Throwable $e) {
        error_log('Error al obtener audit logs: ' . $e->getMessage());
        return [];
    }
}

/**
 * Cuenta total de logs con filtros.
 *
 * @param array $filters
 * @return int
 */
function count_audit_logs(array $filters = []): int
{
    try {
        $db = db();

        $where = [];
        $params = [];

        if (isset($filters['user_id'])) {
            $where[] = 'user_id = :user_id';
            $params[':user_id'] = (int)$filters['user_id'];
        }

        if (isset($filters['empresa_id'])) {
            $where[] = 'empresa_id = :empresa_id';
            $params[':empresa_id'] = (int)$filters['empresa_id'];
        }

        if (isset($filters['action'])) {
            $where[] = 'action = :action';
            $params[':action'] = $filters['action'];
        }

        if (isset($filters['entity_type'])) {
            $where[] = 'entity_type = :entity_type';
            $params[':entity_type'] = $filters['entity_type'];
        }

        if (isset($filters['entity_id'])) {
            $where[] = 'entity_id = :entity_id';
            $params[':entity_id'] = (int)$filters['entity_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) as total FROM audit_logs $whereClause";
        $stmt = $db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    } catch (Throwable $e) {
        error_log('Error al contar audit logs: ' . $e->getMessage());
        return 0;
    }
}
