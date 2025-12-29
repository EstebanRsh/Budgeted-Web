<?php
/** @var array $presupuesto */
// Variables esperadas: $numero, $fechaForm, $empresaNombre, $empresaDomicilio, $empresaTelefono, $empresaEmail, $empresaWeb, $empresaIva, $empresaCuit, $empresaIIBB, $empresaInicioAct, $cliente, $cuitDni, $condIva, $domicilio, $telefono, $email, $items, $comentarios, $validezTexto, $subtotal, $ivaCalculado, $totalGeneral, $logoData

// Adaptar las variables desde $presupuesto (igual que en ver.php)
$numero       = $presupuesto['numero'] ?? '';
$fechaRaw     = $presupuesto['fecha_emision'] ?? null;
$fechaFormateada = $fechaRaw ? date('d/m/Y', strtotime($fechaRaw)) : '-';
$empresaNombre = $presupuesto['empresa_nombre'] ?? '';
$empresaDomicilio = $presupuesto['empresa_domicilio'] ?? '';
$empresaTelefono = $presupuesto['empresa_telefono'] ?? '';
$empresaEmail = $presupuesto['empresa_email'] ?? '';
$empresaWeb = $presupuesto['empresa_web'] ?? '';
$empresaIva = $presupuesto['empresa_iva'] ?? '';
$empresaCuit = $presupuesto['empresa_cuit'] ?? '';
$empresaIIBB = $presupuesto['empresa_iibb'] ?? '';
$empresaInicioAct = $presupuesto['empresa_inicio_act'] ?? '';
$logoData = $presupuesto['empresa_logo'] ?? null;
$cliente = $presupuesto['cliente_nombre'] ?? '';
$cuitDni = $presupuesto['cliente_cuit_dni'] ?? '';
$condIva = $presupuesto['cliente_condicion_iva'] ?? '';
$domicilio = $presupuesto['cliente_domicilio'] ?? '';
$telefono = $presupuesto['cliente_telefono'] ?? '';
$email = $presupuesto['cliente_email'] ?? '';
$items = $presupuesto['items'] ?? [];
$comentarios = $presupuesto['observaciones'] ?? '';
$validezTexto = isset($presupuesto['validez_dias']) ? ((int)$presupuesto['validez_dias']) . ' días' : '';
$subtotal = $presupuesto['subtotal'] ?? 0;
$ivaCalculado = $presupuesto['iva'] ?? 0;
$totalGeneral = $presupuesto['total_general'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto <?= htmlspecialchars($numero) ?> - Imprimir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 12mm 18mm 12mm;
        }
        /* Copiar estilos clave de pdf.php, adaptados para impresión */
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            color: #222;
            background: #fff;
            margin: 0;
            box-sizing: border-box;
        }
        .print-a4-container {
            width: 100%;
            max-width: 900px;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 0 0.5mm rgba(0,0,0,0.05);
            padding: 12px 8px 24px 8px;
        }

        @media (max-width: 950px) {
            .print-a4-container {
                max-width: 100vw;
                min-width: 0;
                padding: 4vw 0.5vw 8vw 0.5vw;
                box-shadow: none;
            }
            .box-border, .items-table, .totales-table {
                font-size: 10pt;
            }
            .header-table td, .cliente-table td {
                padding: 2px 2px;
            }
        }

        @media (max-width: 600px) {
            .print-a4-container {
                padding: 2vw 0.5vw 4vw 0.5vw;
            }
            .box-border, .items-table, .totales-table {
                font-size: 9pt;
            }
            .header-table td, .cliente-table td {
                padding: 1px 1px;
            }
        }
        .box-border {
            border: 1px solid #000;
            border-radius: 4px;
            margin-bottom: 12px;
            padding: 10px 14px;
            background: #fff;
        }
        .header-table, .cliente-table, .items-table, .totales-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td, .cliente-table td {
            vertical-align: top;
            padding: 2px 6px;
        }
        .emisor-logo img {
            max-width: 110px;
            max-height: 60px;
        }
        .emisor-nombre {
            font-size: 15pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .emisor-detalles {
            font-size: 9pt;
            color: #444;
        }
        .uppercase { text-transform: uppercase; }
        .bold { font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        /* --- Ítems --- */
        .items-container { min-height: 200px; }
        .items-table {
            border: 1px solid #000;
            margin-top: 8px;
        }
        .items-table th {
            background: #e0e0e0;
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 7px 4px;
            font-size: 9pt;
            font-weight: bold;
        }
        .items-table th:last-child { border-right: none; }
        .items-table td {
            padding: 7px 5px;
            border-right: 1px solid #000;
            border-bottom: 1px solid #ccc;
            font-size: 9.5pt;
        }
        .items-table td:last-child { border-right: none; }
        /* --- Footer --- */
        .footer-cols { width: 100%; margin-top: 18px; }
        .col-obs { width: 60%; padding-right: 18px; }
        .col-totales { width: 40%; }
        .obs-box {
            border: 1px solid #000;
            padding: 7px;
            font-size: 9pt;
            border-radius: 2px;
            min-height: 40px;
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
            background: #e0e0e0;
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
        /* Print: ocultar botones y navegación */
        @media print {
            html, body {
                width: auto;
                min-width: 0;
                min-height: 0;
                margin: 0;
                padding: 0;
                background: #fff !important;
            }
            .print-a4-container {
                width: 210mm !important;
                min-height: 297mm !important;
                max-width: 210mm !important;
                margin: 0 auto !important;
                box-shadow: none !important;
            }
            .no-print { display: none !important; }
        }
    </style>
    </style>
</style>
<script>
// Solo auto-print si está en ventana nueva o no hay opener
window.addEventListener('DOMContentLoaded', function() {
    if (window.opener == null || window === window.top) {
        setTimeout(function() {
            window.print();
        }, 100);
    }
});
</script>
</head>
<body>
<div class="print-a4-container">
    <!-- Header -->
    <div class="box-border">
        <table class="header-table">
            <tr>
                <td style="width: 32%;">
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
                <td style="width: 13%; text-align: center;">
                    <div style="font-size: 22pt; font-weight: bold; border: 2px solid #000; border-radius: 6px; width: 38px; height: 38px; margin: 0 auto 6px; line-height: 38px;">X</div>
                    <div style="font-size: 9pt;">COD. 00</div>
                </td>
                <td style="width: 55%;">
                    <div class="uppercase" style="font-size: 13pt; font-weight: bold;">Presupuesto</div>
                    <table class="header-table" style="margin-top: 6px;">
                        <tr>
                            <td style="width: 45%;">Nº Comprobante:</td>
                            <td class="bold" style="font-size: 11pt;"> <?= htmlspecialchars($numero ?: '00000000') ?> </td>
                        </tr>
                        <tr>
                            <td>Fecha de Emisión:</td>
                            <td><?= $fechaFormateada ?></td>
                        </tr>
                        <tr><td colspan="2" style="height: 8px;"></td></tr>
                        <tr>
                            <td>CUIT:</td>
                            <td><?= htmlspecialchars($empresaCuit) ?></td>
                        </tr>
                        <tr>
                            <td>Ingresos Brutos:</td>
                            <td><?= htmlspecialchars($empresaIIBB) ?></td>
                        </tr>
                        <tr>
                            <td>Inicio Actividades:</td>
                            <td><?= htmlspecialchars($empresaInicioAct) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <!-- Cliente -->
    <div class="box-border" style="background: #fcfcfc;">
        <table class="cliente-table">
            <tr>
                <td style="width: 15%;">Señor(es):</td>
                <td style="width: 35%;" class="bold uppercase"> <?= htmlspecialchars($cliente) ?> </td>
                <td style="width: 15%; text-align: right;">CUIT/DNI:</td>
                <td style="width: 35%; text-align: right;"> <?= htmlspecialchars($cuitDni) ?> </td>
            </tr>
            <tr>
                <td>Domicilio:</td>
                <td><?= htmlspecialchars($domicilio) ?></td>
                <td style="text-align: right;">Cond. IVA:</td>
                <td style="text-align: right;"> <?= htmlspecialchars($condIva) ?> </td>
            </tr>
            <tr>
                <td>Contacto:</td>
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
                            <td class="text-center"> <?= number_format((float)($item['cantidad'] ?? 0), 2, ',', '.') ?> </td>
                            <td class="text-right">$ <?= number_format((float)($item['precio_unitario'] ?? 0), 2, ',', '.') ?></td>
                            <td class="text-right bold">$ <?= number_format((float)($item['total'] ?? 0), 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
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
    <!-- Footer -->
    <div class="footer-cols">
        <table style="width: 100%;">
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
