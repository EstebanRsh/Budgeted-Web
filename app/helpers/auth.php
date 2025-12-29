<?php
declare(strict_types=1);

/**
 * Verifica que el usuario haya iniciado sesión.
 * Si no hay sesión, redirige al login y corta la ejecución.
 */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}

/**
 * Devuelve el ID del usuario logueado o null.
 */
function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Devuelve el ID de la empresa del usuario logueado o null.
 */
function current_empresa_id(): ?int
{
    $userId = current_user_id();
    if (!$userId) {
        return null;
    }

    // Buscar empresa del usuario
    $sql = 'SELECT id FROM empresas WHERE usuario_id = :usuario_id LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([':usuario_id' => $userId]);
    $empresa = $stmt->fetch();

    return $empresa ? (int)$empresa['id'] : null;
}

/**
 * Devuelve true si el usuario actual es superadmin.
 */
function is_superadmin(): bool
{
    return !empty($_SESSION['is_superadmin']);
}
/**
 * Genera un token CSRF y lo almacena en la sesión.
 * Si ya existe un token, lo reutiliza.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Devuelve un campo input hidden con el token CSRF.
 * Para usar en formularios: <?= csrf_field() ?>
 */
function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verifica que el token CSRF sea válido.
 * Debe llamarse al inicio de controladores que procesen POST.
 * 
 * Retorna true si el token es válido, false si es inválido.
 */
function verify_csrf_token(): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    $token = $_POST['csrf_token'] ?? '';

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Valida un número de CUIT/DNI argentino.
 * CUIT: 11 dígitos (XX-XXXXXXXX-X)
 * DNI: 8 dígitos sin formato
 * 
 * Soporta formatos:
 * - 20-12345678-9 (CUIT formateado)
 * - 20123456789 (CUIT sin formato)
 * - 12345678 (DNI)
 * 
 * Retorna true si es válido, false si no.
 */
function validar_cuit_dni(string $valor): bool
{
    // Remover espacios y guiones
    $valor = trim(str_replace([' ', '-'], '', $valor));

    // Validar que solo contenga dígitos
    if (!ctype_digit($valor)) {
        return false;
    }

    $longitud = strlen($valor);

    // Si tiene 8 dígitos, es un DNI válido
    if ($longitud === 8) {
        return true;
    }

    // Si tiene 11 dígitos, validar como CUIT usando algoritmo
    if ($longitud === 11) {
        return validar_cuit_algoritmo($valor);
    }

    // Cualquier otra longitud es inválida
    return false;
}

/**
 * Valida un CUIT según el algoritmo de verificación argentino.
 * El CUIT tiene 11 dígitos: XX-XXXXXXXX-X
 * 
 * El último dígito es un dígito verificador calculado así:
 * 1. Multiplicar cada dígito (de izquierda a derecha, excepto el último) por:
 *    5, 4, 3, 2, 7, 6, 5, 4, 3, 2
 * 2. Sumar todos los productos
 * 3. Dividir la suma por 11 y obtener el resto
 * 4. Restar el resto a 11 (si el resultado es 11, es 0)
 * 5. Comparar con el último dígito del CUIT
 */
function validar_cuit_algoritmo(string $cuit): bool
{
    if (strlen($cuit) !== 11 || !ctype_digit($cuit)) {
        return false;
    }

    $multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    $suma = 0;

    for ($i = 0; $i < 10; $i++) {
        $suma += (int)$cuit[$i] * $multiplicadores[$i];
    }

    $resto = $suma % 11;
    $digito_verificador = 11 - $resto;

    if ($digito_verificador === 11) {
        $digito_verificador = 0;
    }

    return (int)$cuit[10] === $digito_verificador;
}

/**
 * Formatea un CUIT/DNI al formato estándar argentino.
 * CUIT: XX-XXXXXXXX-X
 * DNI: XX.XXX.XXX (sin guion final para DNI)
 */
function formatear_cuit_dni(string $valor): string
{
    // Remover caracteres especiales
    $valor = trim(str_replace([' ', '-', '.'], '', $valor));

    // Si no son solo dígitos, devolver como está
    if (!ctype_digit($valor)) {
        return $valor;
    }

    if (strlen($valor) === 8) {
        // Formato DNI: XX.XXX.XXX
        return substr($valor, 0, 2) . '.' . substr($valor, 2, 3) . '.' . substr($valor, 5, 3);
    } elseif (strlen($valor) === 11) {
        // Formato CUIT: XX-XXXXXXXX-X
        return substr($valor, 0, 2) . '-' . substr($valor, 2, 8) . '-' . substr($valor, 10, 1);
    }

    return $valor;
}
