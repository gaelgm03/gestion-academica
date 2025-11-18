<?php
/**
 * Cargador de Variables de Entorno
 * Sistema de Gestión Académica
 * 
 * Carga las variables desde el archivo .env y las establece como variables de entorno
 * Compatible con PHP nativo sin dependencias externas
 */

class EnvLoader {
    /**
     * Carga las variables de entorno desde el archivo .env
     * 
     * @param string $path Ruta al directorio que contiene el archivo .env
     * @return bool True si se cargó exitosamente, false si no
     */
    public static function load($path) {
        $envFile = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '.env';
        
        // Verificar si el archivo existe
        if (!file_exists($envFile)) {
            error_log("Archivo .env no encontrado en: $envFile");
            return false;
        }
        
        // Leer el archivo línea por línea
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentarios y líneas vacías
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Parsear la línea (formato: CLAVE=valor)
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas si existen
                $value = self::removeQuotes($value);
                
                // Establecer la variable de entorno si no existe ya
                if (!getenv($key)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Remueve comillas dobles o simples del valor
     * 
     * @param string $value
     * @return string
     */
    private static function removeQuotes($value) {
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            return substr($value, 1, -1);
        }
        return $value;
    }
    
    /**
     * Obtiene el valor de una variable de entorno
     * 
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public static function get($key, $default = null) {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Convertir valores booleanos
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
    
    /**
     * Verifica si una variable de entorno existe
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        return getenv($key) !== false;
    }
}

// Cargar automáticamente el archivo .env al incluir este archivo
EnvLoader::load(__DIR__ . '/..');

/**
 * Función helper global para obtener variables de entorno
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env($key, $default = null) {
    return EnvLoader::get($key, $default);
}
