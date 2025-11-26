<?php
/**
 * Manejador de JWT (JSON Web Tokens)
 * Sistema de Gestión Académica
 * 
 * Genera y valida tokens JWT para autenticación sin estado
 * Implementación nativa sin dependencias externas
 */

class JWTHandler {
    private $secret;
    private $algorithm;
    private $expiration;
    
    public function __construct() {
        $this->secret = env('JWT_SECRET', 'default_secret_change_in_production');
        $this->algorithm = env('JWT_ALGORITHM', 'HS256');
        $this->expiration = (int)env('JWT_EXPIRATION', 3600); // 1 hora por defecto
    }
    
    /**
     * Generar un token JWT
     * 
     * @param array $payload Datos a incluir en el token
     * @return string Token JWT generado
     */
    public function generate($payload) {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];
        
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->expiration;
        
        $payload['iat'] = $issuedAt;
        $payload['exp'] = $expiresAt;
        
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = $this->sign($headerEncoded . '.' . $payloadEncoded);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    /**
     * Validar y decodificar un token JWT
     * 
     * @param string $token Token JWT a validar
     * @return array|false Payload decodificado o false si el token es inválido
     */
    public function validate($token) {
        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return false;
            }
            
            list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
            
            // Verificar la firma
            $signature = $this->sign($headerEncoded . '.' . $payloadEncoded);
            $signatureCheck = $this->base64UrlEncode($signature);
            
            if ($signatureEncoded !== $signatureCheck) {
                return false;
            }
            
            // Decodificar el payload
            $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
            
            if (!$payload) {
                return false;
            }
            
            // Verificar expiración
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }
            
            return $payload;
            
        } catch (Exception $e) {
            error_log("Error al validar JWT: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extraer token del header Authorization
     * 
     * @return string|false Token o false si no existe
     */
    public function extractFromHeader() {
        $headers = $this->getAuthorizationHeader();
        
        if (!$headers) {
            return false;
        }
        
        // El formato esperado es: "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
            return $matches[1];
        }
        
        return false;
    }
    
    /**
     * Obtener el header Authorization
     * 
     * @return string|false
     */
    private function getAuthorizationHeader() {
        // Intentar obtener del header Authorization
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }
        
        // Apache puede almacenarlo en otro lugar
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        
        // Nginx
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
        }
        
        return false;
    }
    
    /**
     * Firmar datos usando HMAC
     * 
     * @param string $data Datos a firmar
     * @return string Firma
     */
    private function sign($data) {
        $algorithm = 'sha256';
        
        switch ($this->algorithm) {
            case 'HS256':
                $algorithm = 'sha256';
                break;
            case 'HS384':
                $algorithm = 'sha384';
                break;
            case 'HS512':
                $algorithm = 'sha512';
                break;
        }
        
        return hash_hmac($algorithm, $data, $this->secret, true);
    }
    
    /**
     * Codificar en Base64 URL-safe
     * 
     * @param string $data
     * @return string
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Decodificar desde Base64 URL-safe
     * 
     * @param string $data
     * @return string
     */
    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Generar token de refresh (duración más larga)
     * 
     * @param array $payload
     * @return string
     */
    public function generateRefreshToken($payload) {
        $originalExpiration = $this->expiration;
        $this->expiration = 604800; // 7 días
        
        $payload['type'] = 'refresh';
        $token = $this->generate($payload);
        
        $this->expiration = $originalExpiration;
        
        return $token;
    }
    
    /**
     * Verificar si el token es de tipo refresh
     * 
     * @param array $payload
     * @return bool
     */
    public function isRefreshToken($payload) {
        return isset($payload['type']) && $payload['type'] === 'refresh';
    }
}
