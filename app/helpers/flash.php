<?php
declare(strict_types=1);

/**
 * Establece un mensaje flash en la sesión.
 *
 * @param string $tipo Tipo de mensaje: success, error, warning, info
 * @param string $mensaje Texto del mensaje
 * @return void
 */
function flash(string $tipo, string $mensaje): void
{
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }

    $_SESSION['flash_messages'][] = [
        'tipo' => $tipo,
        'mensaje' => $mensaje,
    ];
}

/**
 * Obtiene todos los mensajes flash y los elimina de la sesión.
 *
 * @return array Array de mensajes flash
 */
function get_flash_messages(): array
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Renderiza los mensajes flash como alerts de Bootstrap 5.
 *
 * @return string HTML de los mensajes
 */
function render_flash_messages(): string
{
    $messages = get_flash_messages();
    
    if (empty($messages)) {
        return '';
    }

    $html = '<div class="flash-messages-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;">';

    foreach ($messages as $flash) {
        $tipo = $flash['tipo'];
        $mensaje = htmlspecialchars($flash['mensaje'], ENT_QUOTES, 'UTF-8');

        // Mapear tipos a clases de Bootstrap
        $alertClass = match($tipo) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
            default => 'alert-secondary',
        };

        // Iconos según el tipo
        $icono = match($tipo) {
            'success' => '✓',
            'error' => '✕',
            'warning' => '⚠',
            'info' => 'ℹ',
            default => '•',
        };

        $html .= '<div class="alert ' . $alertClass . ' alert-dismissible fade show shadow-sm" role="alert" data-flash-message>';
        $html .= '<strong>' . $icono . '</strong> ' . $mensaje;
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
        $html .= '</div>';
    }

    $html .= '</div>';

    // JavaScript para auto-dismiss después de 5 segundos
    $html .= '<script>';
    $html .= 'document.addEventListener("DOMContentLoaded", function() {';
    $html .= '    const flashMessages = document.querySelectorAll("[data-flash-message]");';
    $html .= '    flashMessages.forEach(function(message) {';
    $html .= '        setTimeout(function() {';
    $html .= '            const bsAlert = new bootstrap.Alert(message);';
    $html .= '            bsAlert.close();';
    $html .= '        }, 5000);';
    $html .= '    });';
    $html .= '});';
    $html .= '</script>';

    return $html;
}

/**
 * Helper para compatibilidad con código existente.
 */
function flash_success(string $mensaje): void
{
    flash('success', $mensaje);
}

function flash_error(string $mensaje): void
{
    flash('error', $mensaje);
}

function flash_warning(string $mensaje): void
{
    flash('warning', $mensaje);
}

function flash_info(string $mensaje): void
{
    flash('info', $mensaje);
}
