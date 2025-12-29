<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2196F3; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        .info-box { background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }
        .warning { background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Contraseña Modificada</h1>
        </div>
        <div class="content">
            <h2>Hola <?= htmlspecialchars($nombre) ?>,</h2>
            <p>Te informamos que tu contraseña ha sido modificada exitosamente.</p>
            
            <div class="info-box">
                <strong>Fecha y hora:</strong> <?= htmlspecialchars($fecha) ?>
            </div>
            
            <div class="warning">
                <strong>⚠️ ¿No fuiste tú?</strong><br>
                Si no realizaste este cambio, contacta inmediatamente al administrador. Tu cuenta podría estar comprometida.
            </div>
            
            <p>Si realizaste este cambio, puedes ignorar este mensaje.</p>
        </div>
        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
