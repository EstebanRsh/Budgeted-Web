<?php
$numero        = $presupuesto['numero'] ?? '';
$fecha         = $presupuesto['fecha_emision'] ?? '';
$estado        = $presupuesto['estado'] ?? '';
$cliente       = $presupuesto['cliente_nombre'] ?? '';
$cuitDni       = $presupuesto['cliente_cuit_dni'] ?? '';
$condIva       = $presupuesto['cliente_condicion_iva'] ?? '';
$domicilio     = $presupuesto['cliente_domicilio'] ?? '';
$email         = $presupuesto['cliente_email'] ?? '';
$telefono      = $presupuesto['cliente_telefono'] ?? '';
$comentarios   = $presupuesto['observaciones'] ?? '';
$validezDias   = $presupuesto['validez_dias'] ?? 15;
$items         = $presupuesto['items'] ?? [];

// Datos de la empresa (Desde DB)
$empresaNombre    = $presupuesto['empresa_nombre'] ?? 'Tu Empresa S.R.L.';
$empresaCuit      = $presupuesto['empresa_cuit'] ?? '';
$empresaDomicilio = $presupuesto['empresa_domicilio'] ?? '';
$empresaTelefono  = $presupuesto['empresa_telefono'] ?? '';
$empresaEmail     = $presupuesto['empresa_email'] ?? '';
$empresaWeb       = $presupuesto['empresa_web'] ?? '';
$empresaIva       = $presupuesto['empresa_condicion_iva'] ?? 'Responsable Inscripto';
$empresaInicioAct = !empty($presupuesto['empresa_inicio_actividades']) ? date('d/m/Y', strtotime($presupuesto['empresa_inicio_actividades'])) : '';
$empresaIIBB      = $presupuesto['empresa_iibb'] ?? '';

// Procesamiento de Logo
$logoTexto = 'LOGO';
$logoData  = null;

// Intentar cargar logo de la empresa (ruta relativa en DB)
$logoDbPath = $presupuesto['empresa_logo_path'] ?? null;
$logoPath   = null;

if ($logoDbPath && file_exists(APP_ROOT . '/public/' . $logoDbPath)) {
    $logoPath = APP_ROOT . '/public/' . $logoDbPath;
} elseif (file_exists(APP_ROOT . '/public/assets/img/logo.png')) {
    // Fallback al logo por defecto si existe
    $logoPath = APP_ROOT . '/public/assets/img/logo.png';
}

if ($logoPath) {
    $mime = 'image/png';
    $ext  = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg'])) {
        $mime = 'image/jpeg';
    } elseif ($ext === 'svg') {
        $mime = 'image/svg+xml';
    }
    $logoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
}

// Formatos
$fechaFormateada = $fecha ? date('d/m/Y', strtotime($fecha)) : '-';
$validezTexto    = $validezDias > 0 ? $validezDias . ' días' : 'Consultar';

// Cálculos Totales
$subtotal = 0.0;
foreach ($items as $item) {
    $subtotal += (float)($item['total'] ?? 0);
}
$subtotal      = round($subtotal, 2);
$totalGeneral  = (float)($presupuesto['total_general'] ?? $subtotal);

// Estimación de IVA para visualización (si no está desglosado en DB)
$ivaCalculado = 0.0;
if ($totalGeneral > $subtotal) {
    $ivaCalculado = round($totalGeneral - $subtotal, 2);
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            color: #000;
            line-height: 1.3;
        }

        /* --- Estructura General --- */
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }
        td, th {
            vertical-align: top;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* --- Cajas Principales --- */
        .box-border {
            border: 1px solid #000;
            margin-bottom: 8px;
            background-color: #fff;
        }

        /* --- Header --- */
        .header-table td { padding: 0; }
        
        .col-emisor {
            width: 45%;
            border-right: 1px solid #000;
            padding: 12px !important;
        }
        .col-letra {
            width: 10%;
            border-right: 1px solid #000;
            text-align: center;
            padding-top: 0 !important;
        }
        .col-info {
            width: 45%;
            padding: 12px !important;
        }

        /* Letra X */
        .letra-container {
            background: #000;
            width: 42px;
            height: 42px;
            margin: 0 auto;
            color: #fff;
            font-size: 28px;
            font-weight: bold;
            line-height: 42px;
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
        }
        .letra-codigo {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 5px;
        }

        /* Datos Emisor */
        .emisor-logo {
            text-align: center;
            margin-bottom: 8px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .emisor-logo img {
            max-height: 100%;
            max-width: 100%;
        }
        .emisor-nombre {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 6px;
            text-align: center;
        }
        .emisor-detalles {
            font-size: 8pt;
            text-align: center;
            line-height: 1.4;
        }

        /* Datos Comprobante (Derecha) */
        .doc-titulo {
            font-size: 20pt;
            font-weight: bold;
            text-align: right;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 2px 0;
            font-size: 9pt;
        }
        .info-label {
            font-weight: bold;
            width: 40%;
        }
        .info-value {
            width: 60%;
            text-align: right;
        }

        /* --- Cliente --- */
        .cliente-table td {
            padding: 6px 10px;
            font-size: 9.5pt;
        }
        .cliente-label {
            font-weight: bold;
            width: 15%;
        }
        .cliente-value {
            width: 35%;
        }

        /* --- Ítems --- */
        .items-container {
            min-height: 400px; /* Altura mínima visual */
        }
        .items-table {
            border: 1px solid #000;
        }
        .items-table th {
            background-color: #e0e0e0;
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 8px 5px;
            font-size: 9pt;
            font-weight: bold;
        }
        .items-table th:last-child { border-right: none; }
        
        .items-table td {
            padding: 8px 6px;
            border-right: 1px solid #000;
            border-bottom: 1px solid #ccc;
            font-size: 9.5pt;
        }
        .items-table td:last-child { border-right: none; }
        
        /* --- Footer Fijo --- */
        .footer-fixed {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 55mm;
            background-color: #fff;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        .footer-cols {
            width: 100%;
        }
        .col-obs {
            width: 60%;
            padding-right: 20px;
        }
        .col-totales {
            width: 40%;
        }

        .obs-box {
            border: 1px solid #000;
            padding: 8px;
            height: 35mm;
            font-size: 9pt;
            border-radius: 2px;
        }
        
        .totales-table {
            border: 1px solid #000;
        }
        .totales-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #ccc;
        }
        .totales-table tr:last-child td {
            border-bottom: none;
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 11pt;
            border-top: 1px solid #000;
        }

        .leyenda-fiscal {
            text-align: center;
            font-size: 8pt;
            margin-top: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Espacio para no pisar el footer */
        .page-content {
            padding-bottom: 60mm;
        }
    </style>
</head>
<body>
    <!-- Footer Fijo -->
    <div class="footer-fixed">
        <table class="footer-cols">
            <tr>
                <td class="col-obs">
                    <div class="bold" style="margin-bottom: 4px; font-size: 9pt;">OBSERVACIONES / CONDICIONES DE VENTA</div>
                    <div class="obs-box">
                        <?= nl2br(htmlspecialchars($comentarios)) ?>
                        <br><br>
                        <span class="bold">Validez de la oferta:</span> <?= $validezTexto ?>
                    </div>
                </td>
                <td class="col-totales">
                    <table class="totales-table">
                        <tr>
                            <td class="text-right bold">Subtotal:</td>
                            <td class="text-right">$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td class="text-right bold">IVA (21%):</td>
                            <td class="text-right">$ <?= number_format($ivaCalculado, 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td class="text-right bold">TOTAL:</td>
                            <td class="text-right">$ <?= number_format($totalGeneral, 2, ',', '.') ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="leyenda-fiscal">
            Documento no válido como factura
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="page-content">
        
        <!-- Header -->
        <div class="box-border">
            <table class="header-table">
                <tr>
                    <!-- Emisor -->
                    <td class="col-emisor">
                        <div class="emisor-logo">
                            <?php if ($logoData): ?>
                                <img src="<?= $logoData ?>" alt="Logo">
                            <?php endif; ?>
                        </div>
                        <div class="emisor-nombre uppercase"><?= htmlspecialchars($empresaNombre) ?></div>
                        <div class="emisor-detalles">
                            <?= htmlspecialchars($empresaDomicilio) ?><br>
                            Tel: <?= htmlspecialchars($empresaTelefono) ?> | <?= htmlspecialchars($empresaEmail) ?><br>
                            <?= htmlspecialchars($empresaWeb) ?><br>
                            <span class="bold"><?= htmlspecialchars($empresaIva) ?></span>
                        </div>
                    </td>
                    
                    <!-- Letra -->
                    <td class="col-letra">
                        <div class="letra-container">X</div>
                        <div class="letra-codigo">COD. 00</div>
                    </td>
                    
                    <!-- Info Comprobante -->
                    <td class="col-info">
                        <div class="doc-titulo uppercase">Presupuesto</div>
                        <table class="info-table">
                            <tr>
                                <td class="info-label">Nº Comprobante:</td>
                                <td class="info-value bold" style="font-size: 11pt;"><?= htmlspecialchars($numero ?: '00000000') ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Fecha de Emisión:</td>
                                <td class="info-value"><?= $fechaFormateada ?></td>
                            </tr>
                            <tr><td colspan="2" style="height: 8px;"></td></tr> <!-- Separador -->
                            <tr>
                                <td class="info-label">CUIT:</td>
                                <td class="info-value"><?= htmlspecialchars($empresaCuit) ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Ingresos Brutos:</td>
                                <td class="info-value"><?= htmlspecialchars($empresaIIBB) ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Inicio Actividades:</td>
                                <td class="info-value"><?= htmlspecialchars($empresaInicioAct) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Cliente -->
        <div class="box-border" style="background-color: #fcfcfc;">
            <table class="cliente-table">
                <tr>
                    <td class="cliente-label">Señor(es):</td>
                    <td class="cliente-value bold uppercase"><?= htmlspecialchars($cliente) ?></td>
                    <td class="cliente-label text-right" style="width: 15%;">CUIT/DNI:</td>
                    <td class="cliente-value text-right" style="width: 35%;"><?= htmlspecialchars($cuitDni) ?></td>
                </tr>
                <tr>
                    <td class="cliente-label">Domicilio:</td>
                    <td class="cliente-value"><?= htmlspecialchars($domicilio) ?></td>
                    <td class="cliente-label text-right">Cond. IVA:</td>
                    <td class="cliente-value text-right"><?= htmlspecialchars($condIva) ?></td>
                </tr>
                <tr>
                    <td class="cliente-label">Contacto:</td>
                    <td colspan="3">
                        <?= htmlspecialchars($telefono) ?>
                        <?php if($telefono && $email) echo ' | '; ?>
                        <?= htmlspecialchars($email) ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Ítems -->
        <div class="items-container">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 10%;" class="text-center uppercase">Código</th>
                        <th style="width: 45%;" class="uppercase">Descripción / Producto</th>
                        <th style="width: 10%;" class="text-center uppercase">Cant.</th>
                        <th style="width: 15%;" class="text-right uppercase">Precio Unit.</th>
                        <th style="width: 20%;" class="text-right uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 30px; color: #666;">
                                - No se han cargado ítems en este presupuesto -
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="text-center"><?= str_pad($i++, 3, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($item['descripcion'] ?? '') ?></td>
                                <td class="text-center"><?= number_format((float)($item['cantidad'] ?? 0), 2, ',', '.') ?></td>
                                <td class="text-right">$ <?= number_format((float)($item['precio_unitario'] ?? 0), 2, ',', '.') ?></td>
                                <td class="text-right bold">$ <?= number_format((float)($item['total'] ?? 0), 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Relleno opcional para mantener estructura si hay pocos items -->
                        <?php for($k=0; $k < max(0, 5 - count($items)); $k++): ?>
                            <tr>
                                <td style="color: transparent;">.</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endfor; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Firma -->
        <div style="margin-top: 20px; page-break-inside: avoid;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 60%;"></td>
                    <td style="width: 40%; text-align: center;">
                        <div style="border-top: 1px solid #000; padding-top: 5px; margin: 0 20px;">
                            <span style="font-size: 9pt;">Firma y Aclaración</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>
</html>
