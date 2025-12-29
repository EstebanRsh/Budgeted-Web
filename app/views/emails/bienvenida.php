<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .button { display: inline-block; padding: 12px 30px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido a Presupuestador!</h1>
        </div>
        <div class="content">
            <h2>Hola <?= htmlspecialchars($nombre) ?>,</h2>
            <p>Tu cuenta ha sido creada exitosamente en Presupuestador.</p>
            <p>Ya puedes iniciar sesión con tu correo electrónico: <strong><?= htmlspecialchars($email) ?></strong></p>
            <p>Accede a la plataforma haciendo clic en el siguiente botón:</p>
            <a href="<?= htmlspecialchars($login_url) ?>" class="button">Ir a Presupuestador</a>
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            <p>¡Que tengas un excelente día!</p>
        </div>
        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
        </div>
    </div>
</body>
</html>
