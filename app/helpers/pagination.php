<?php
declare(strict_types=1);

/**
 * Calcula los datos de paginación.
 *
 * @param int $totalRegistros Total de registros disponibles
 * @param int $paginaActual Página actual (1-indexed)
 * @param int $registrosPorPagina Registros a mostrar por página
 * @return array Información de paginación
 */
function calcular_paginacion(int $totalRegistros, int $paginaActual, int $registrosPorPagina = 10): array
{
    $totalPaginas = (int)ceil($totalRegistros / $registrosPorPagina);
    
    // Asegurar que la página actual sea válida
    if ($paginaActual < 1) {
        $paginaActual = 1;
    }
    if ($paginaActual > $totalPaginas && $totalPaginas > 0) {
        $paginaActual = $totalPaginas;
    }
    
    $offset = ($paginaActual - 1) * $registrosPorPagina;
    
    // Calcular rango de páginas a mostrar (ej: 1 2 3 ... 8 9 10)
    $rangoPaginas = [];
    $maxPaginasVisibles = 7;
    
    if ($totalPaginas <= $maxPaginasVisibles) {
        // Mostrar todas las páginas
        for ($i = 1; $i <= $totalPaginas; $i++) {
            $rangoPaginas[] = $i;
        }
    } else {
        // Mostrar páginas con elipsis
        if ($paginaActual <= 4) {
            // Inicio: 1 2 3 4 5 ... 10
            for ($i = 1; $i <= 5; $i++) {
                $rangoPaginas[] = $i;
            }
            $rangoPaginas[] = '...';
            $rangoPaginas[] = $totalPaginas;
        } elseif ($paginaActual >= $totalPaginas - 3) {
            // Final: 1 ... 6 7 8 9 10
            $rangoPaginas[] = 1;
            $rangoPaginas[] = '...';
            for ($i = $totalPaginas - 4; $i <= $totalPaginas; $i++) {
                $rangoPaginas[] = $i;
            }
        } else {
            // Medio: 1 ... 4 5 6 ... 10
            $rangoPaginas[] = 1;
            $rangoPaginas[] = '...';
            for ($i = $paginaActual - 1; $i <= $paginaActual + 1; $i++) {
                $rangoPaginas[] = $i;
            }
            $rangoPaginas[] = '...';
            $rangoPaginas[] = $totalPaginas;
        }
    }
    
    return [
        'total_registros' => $totalRegistros,
        'pagina_actual' => $paginaActual,
        'registros_por_pagina' => $registrosPorPagina,
        'total_paginas' => $totalPaginas,
        'offset' => $offset,
        'tiene_anterior' => $paginaActual > 1,
        'tiene_siguiente' => $paginaActual < $totalPaginas,
        'rango_paginas' => $rangoPaginas,
        'inicio_rango' => min($offset + 1, $totalRegistros),
        'fin_rango' => min($offset + $registrosPorPagina, $totalRegistros),
    ];
}

/**
 * Renderiza los controles de paginación (Bootstrap 5).
 *
 * @param array $paginacion Datos de paginación
 * @param string $baseUrl URL base para los enlaces
 * @param array $parametrosExtra Parámetros adicionales a incluir en los enlaces
 * @param string $nombreParametro Nombre del parámetro de página en la URL
 * @param array $htmxConfig Configuración HTMX opcional
 * @return string HTML de los controles
 */
function renderizar_paginacion(array $paginacion, string $baseUrl, array $parametrosExtra = [], string $nombreParametro = 'pagina', array $htmxConfig = []): string
{
    if ($paginacion['total_paginas'] <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Paginación">';
    $html .= '<ul class="pagination justify-content-center mb-0">';
    
    // Botón anterior
    $claseDisabled = !$paginacion['tiene_anterior'] ? ' disabled' : '';
    $url = construir_url_paginacion($baseUrl, $paginacion['pagina_actual'] - 1, $parametrosExtra, $nombreParametro);
    $htmxAttrs = !empty($htmxConfig) && $paginacion['tiene_anterior'] ? construir_atributos_htmx($htmxConfig) : '';
    $html .= '<li class="page-item' . $claseDisabled . '">';
    $html .= '<a class="page-link" href="' . h($url) . '" ' . $htmxAttrs . ' aria-label="Anterior">';
    $html .= '<span aria-hidden="true">&laquo;</span>';
    $html .= '</a></li>';
    
    // Páginas numeradas
    foreach ($paginacion['rango_paginas'] as $pagina) {
        if ($pagina === '...') {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        } else {
            $claseActiva = ($pagina === $paginacion['pagina_actual']) ? ' active' : '';
            $url = construir_url_paginacion($baseUrl, $pagina, $parametrosExtra, $nombreParametro);
            $htmxAttrs = !empty($htmxConfig) ? construir_atributos_htmx($htmxConfig) : '';
            $html .= '<li class="page-item' . $claseActiva . '">';
            $html .= '<a class="page-link" href="' . h($url) . '" ' . $htmxAttrs . '>' . $pagina . '</a>';
            $html .= '</li>';
        }
    }
    
    // Botón siguiente
    $claseDisabled = !$paginacion['tiene_siguiente'] ? ' disabled' : '';
    $url = construir_url_paginacion($baseUrl, $paginacion['pagina_actual'] + 1, $parametrosExtra, $nombreParametro);
    $htmxAttrs = !empty($htmxConfig) && $paginacion['tiene_siguiente'] ? construir_atributos_htmx($htmxConfig) : '';
    $html .= '<li class="page-item' . $claseDisabled . '">';
    $html .= '<a class="page-link" href="' . h($url) . '" ' . $htmxAttrs . ' aria-label="Siguiente">';
    $html .= '<span aria-hidden="true">&raquo;</span>';
    $html .= '</a></li>';
    
    $html .= '</ul>';
    
    $html .= '</nav>';
    
    return $html;
}

/**
 * Construye URL con parámetros de paginación.
 */
function construir_url_paginacion(string $baseUrl, int $pagina, array $parametrosExtra = [], string $nombreParametro = 'pagina'): string
{
    $parametros = array_merge($parametrosExtra, [$nombreParametro => $pagina]);
    $query = http_build_query($parametros);
    
    return $baseUrl . ($query ? '?' . $query : '');
}

/**
 * Construye atributos HTMX para enlaces de paginación.
 */
function construir_atributos_htmx(array $config): string
{
    $attrs = [];
    
    // Usamos hx-boost para que los enlaces funcionen como AJAX respetando href
    $attrs[] = 'hx-boost="true"';

    if (!empty($config['target'])) {
        $attrs[] = 'hx-target="' . htmlspecialchars($config['target'], ENT_QUOTES) . '"';
    }
    if (!empty($config['indicator'])) {
        $attrs[] = 'hx-indicator="' . htmlspecialchars($config['indicator'], ENT_QUOTES) . '"';
    }
    if (!empty($config['swap'])) {
        $attrs[] = 'hx-swap="' . htmlspecialchars($config['swap'], ENT_QUOTES) . '"';
    }
    
    return implode(' ', $attrs);
}

/**
 * Escapa HTML para prevenir XSS.
 */
function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
