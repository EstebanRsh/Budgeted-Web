<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Logs de Correo Electrónico</h1>
        <a href="<?= BASE_URL ?>admin/usuarios" class="btn btn-secondary">
            Volver a Usuarios
        </a>
    </div>

    <?php if (empty($logs)): ?>
        <div class="alert alert-info">
            <strong>Sin registros</strong>
            <p class="mb-0">No hay logs de correo aún. Los logs aparecerán cuando se envíe el primer email.</p>
            <hr>
            <p class="mb-0 small"><strong>Archivo:</strong> <code><?= htmlspecialchars($logFile) ?></code></p>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">⚙️ Configuración SMTP</h5>
                <p>Para que los emails se envíen, debes configurar las credenciales SMTP en:</p>
                <code>config/mail.php</code>
                <hr>
                <h6>Configuración para Hostinger:</h6>
                <ul>
                    <li><strong>SMTP_HOST:</strong> smtp.hostinger.com</li>
                    <li><strong>SMTP_PORT:</strong> 587</li>
                    <li><strong>SMTP_USER:</strong> tu email (ej: noreply@tudominio.com)</li>
                    <li><strong>SMTP_PASSWORD:</strong> contraseña de esa cuenta</li>
                </ul>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span>📧 Últimas <?= count($logs) ?> entradas</span>
                <span class="badge bg-light text-dark"><?= htmlspecialchars(basename($logFile)) ?></span>
            </div>
            <div class="card-body p-0">
                <div style="max-height: 600px; overflow-y: auto; font-family: monospace; font-size: 13px;">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th style="width: 180px;">Fecha y Hora</th>
                                <th>Mensaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php
                                // Parsear log: [2025-12-13 23:30:00] Mensaje
                                preg_match('/^\[(.*?)\]\s+(.*)$/', $log, $matches);
                                $fecha = $matches[1] ?? '';
                                $mensaje = $matches[2] ?? $log;
                                
                                // Colorear según tipo
                                $class = '';
                                if (stripos($mensaje, 'error') !== false) {
                                    $class = 'table-danger';
                                } elseif (stripos($mensaje, 'enviado') !== false) {
                                    $class = 'table-success';
                                } elseif (stripos($mensaje, 'desactivado') !== false) {
                                    $class = 'table-warning';
                                }
                                ?>
                                <tr class="<?= $class ?>">
                                    <td class="text-muted small"><?= htmlspecialchars($fecha) ?></td>
                                    <td><?= htmlspecialchars($mensaje) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted small">
                <strong>Ruta completa:</strong> <code><?= htmlspecialchars($logFile) ?></code>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <strong>ℹ️ Nota:</strong> Los logs se muestran en orden inverso (más recientes primero). Se muestran las últimas 200 entradas.
        </div>
    <?php endif; ?>
</div>
