<?php
declare(strict_types=1);

/**
 * Valida un email.
 *
 * @param string $email
 * @return bool
 */
function validar_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida que un email sea único en la tabla usuarios.
 *
 * @param string $email Email a validar
 * @param int|null $exceptoId ID de usuario a excluir (para edición)
 * @return bool
 */
function email_unico(string $email, ?int $exceptoId = null): bool
{
    $db = db();
    
    if ($exceptoId !== null) {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = :email AND id != :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':id', $exceptoId, PDO::PARAM_INT);
    } else {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = :email";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $row = $stmt->fetch();
    
    return (int)($row['total'] ?? 0) === 0;
}

/**
 * Valida formato de teléfono argentino.
 * Acepta formatos: +54 11 1234-5678, 011-1234-5678, 11-1234-5678, etc.
 *
 * @param string $telefono
 * @return bool
 */
function validar_telefono_ar(string $telefono): bool
{
    // Remover espacios, guiones y paréntesis
    $tel = preg_replace('/[\s\-\(\)]/', '', $telefono);
    
    // Debe tener entre 10 y 13 dígitos (con o sin +54)
    if (!preg_match('/^\+?\d{10,13}$/', $tel)) {
        return false;
    }
    
    return true;
}

/**
 * Formatea un teléfono argentino al formato estándar.
 *
 * @param string $telefono
 * @return string
 */
function formatear_telefono_ar(string $telefono): string
{
    $tel = preg_replace('/[\s\-\(\)]/', '', $telefono);
    
    // Si empieza con +54, quitarlo temporalmente
    $tel = preg_replace('/^\+54/', '', $tel);
    
    // Si empieza con 0, quitarlo
    $tel = ltrim($tel, '0');
    
    // Agregar +54
    return '+54 ' . $tel;
}

/**
 * Valida que una contraseña sea segura.
 * Mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número.
 *
 * @param string $password
 * @return bool
 */
function validar_password_segura(string $password): bool
{
    if (strlen($password) < 8) {
        return false;
    }
    
    // Al menos una mayúscula
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // Al menos una minúscula
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // Al menos un número
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    return true;
}

/**
 * Sanitiza un string para prevenir XSS.
 *
 * @param string $str
 * @return string
 */
function sanitize_string(string $str): string
{
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida que un número sea positivo.
 *
 * @param mixed $num
 * @return bool
 */
function validar_numero_positivo($num): bool
{
    return is_numeric($num) && (float)$num > 0;
}

/**
 * Valida un rango de fecha.
 *
 * @param string $fecha Fecha en formato Y-m-d
 * @return bool
 */
function validar_fecha(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}
