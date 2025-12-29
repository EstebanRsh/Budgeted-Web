<?php
/**
 * Servicio de envío de correos electrónicos
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private static $config = null;
    private static $logFile = __DIR__ . '/../../logs/mail.log';
    
    /**
     * Cargar configuración de correo
     */
    private static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/mail.php';
        }
        return self::$config;
    }
    
    /**
     * Enviar correo electrónico
     * 
     * @param string $to Email destinatario
     * @param string $subject Asunto
     * @param string $htmlBody Cuerpo HTML
     * @param string $textBody Cuerpo en texto plano (opcional)
     * @return bool
     */
    public static function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $config = self::getConfig();
        
        // Si el envío está desactivado, solo registrar
        if (!$config['enabled']) {
            self::log("Correo NO enviado (desactivado): $to - $subject");
            return true;
        }
        
        try {
            $mail = new PHPMailer(true);
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $config['smtp']['host'];
            $mail->Port = $config['smtp']['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp']['username'];
            $mail->Password = $config['smtp']['password'];
            $mail->SMTPSecure = $config['smtp']['encryption'] === 'ssl' 
                ? PHPMailer::ENCRYPTION_SMTPS 
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Timeout = $config['timeout'];
            $mail->CharSet = $config['charset'];
            
            // Remitente
            $mail->setFrom($config['from']['email'], $config['from']['name']);
            
            // Destinatario
            $mail->addAddress($to);
            
            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?: strip_tags($htmlBody);
            
            // Enviar
            $mail->send();
            
            self::log("Correo enviado a: $to - $subject");
            return true;
            
        } catch (Exception $e) {
            self::log("Error al enviar correo a $to: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar en log
     */
    private static function log(string $message): void
    {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $written = @file_put_contents(
            self::$logFile,
            "[$timestamp] $message\n",
            FILE_APPEND
        );

        if ($written === false) {
            error_log('MailService: no se pudo escribir en ' . self::$logFile);
        }
    }
    
    /**
     * Cargar y procesar plantilla
     */
    private static function loadTemplate(string $template, array $vars = []): string
    {
        $file = __DIR__ . '/../views/emails/' . $template . '.php';
        
        if (!file_exists($file)) {
            return '';
        }
        
        ob_start();
        extract($vars);
        include $file;
        return ob_get_clean();
    }
    
    /**
     * Enviar email de bienvenida
     */
    public static function sendBienvenida(array $usuario): bool
    {
        $vars = [
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'login_url' => BASE_URL . 'login',
        ];
        
        $html = self::loadTemplate('bienvenida', $vars);
        $subject = 'Bienvenido a Presupuestador';
        
        return self::send($usuario['email'], $subject, $html);
    }
    
    /**
     * Enviar email cuando se activa una cuenta
     */
    public static function sendCuentaActivada(array $usuario): bool
    {
        $vars = [
            'nombre' => $usuario['nombre'],
            'login_url' => BASE_URL . 'login',
        ];
        
        $html = self::loadTemplate('cuenta_activada', $vars);
        $subject = 'Tu cuenta ha sido activada';
        
        return self::send($usuario['email'], $subject, $html);
    }
    
    /**
     * Enviar email cuando se desactiva una cuenta
     */
    public static function sendCuentaDesactivada(array $usuario): bool
    {
        $vars = [
            'nombre' => $usuario['nombre'],
        ];
        
        $html = self::loadTemplate('cuenta_desactivada', $vars);
        $subject = 'Tu cuenta ha sido desactivada';
        
        return self::send($usuario['email'], $subject, $html);
    }
    
    /**
     * Enviar email de cambio de contraseña
     */
    public static function sendPasswordCambiada(array $usuario): bool
    {
        $vars = [
            'nombre' => $usuario['nombre'],
            'fecha' => date('d/m/Y H:i'),
        ];
        
        $html = self::loadTemplate('password_cambiada', $vars);
        $subject = 'Tu contraseña ha sido modificada';
        
        return self::send($usuario['email'], $subject, $html);
    }
}
