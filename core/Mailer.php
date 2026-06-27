<?php
// ============================================================
//  CORE: Mailer – Envío de correos usando API HTTP de Brevo
//  (Bypass de restricciones SMTP de Railway)
// ============================================================

class Mailer {

    private static string $senderEmail = 'hogaridealutp@gmail.com';
    private static string $senderName = 'Hogar Ideal Perú';

    /**
     * Enviar un correo electrónico a través del puerto 443 usando cURL.
     *
     * @param string $to Email del destinatario
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo del correo en HTML
     * @return bool True si se envió, false si falló
     */
    public static function send(string $to, string $subject, string $body): bool {
        
        $url = 'https://api.brevo.com/v3/smtp/email';
        
        $data = [
            'sender' => [
                'name'  => self::$senderName,
                'email' => self::$senderEmail
            ],
            'to' => [
                ['email' => $to]
            ],
            'bcc' => [
                ['email' => 'hogaridealutp@gmail.com'] // Copia oculta siempre al administrador
            ],
            'subject'     => $subject,
            'htmlContent' => $body
        ];

        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 segundos
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Permitir en XAMPP local sin certificado
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Obtener la API key de las variables de entorno de Railway (múltiples métodos por seguridad)
        $apiKey = $_ENV['BREVO_API_KEY'] ?? $_SERVER['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY') ?: '';

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Brevo devuelve HTTP 201 Created cuando el correo se encola con éxito
        if ($httpCode === 201 || $httpCode === 200) {
            return true;
        }

        error_log("Brevo API Error ($httpCode): " . $response);
        return false;
    }
}
