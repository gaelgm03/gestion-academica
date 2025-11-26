<?php
/**
 * Autenticación LDAP
 * Sistema de Gestión Académica
 * 
 * Maneja la autenticación contra el servidor LDAP institucional
 */

class LDAPAuth {
    private $host;
    private $port;
    private $baseDN;
    private $adminDN;
    private $adminPass;
    private $connection;
    
    public function __construct() {
        $this->host = env('LDAP_HOST', 'ldap://localhost');
        $this->port = env('LDAP_PORT', 389);
        $this->baseDN = env('LDAP_BASE_DN', 'dc=universidad,dc=edu,dc=mx');
        $this->adminDN = env('LDAP_ADMIN_DN', '');
        $this->adminPass = env('LDAP_ADMIN_PASS', '');
    }
    
    /**
     * Conectar al servidor LDAP
     * 
     * @return bool True si la conexión fue exitosa
     * @throws Exception Si no se puede conectar
     */
    private function connect() {
        $this->connection = ldap_connect($this->host, $this->port);
        
        if (!$this->connection) {
            throw new Exception("No se pudo conectar al servidor LDAP");
        }
        
        // Configurar opciones LDAP
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->connection, LDAP_OPT_NETWORK_TIMEOUT, 10);
        
        return true;
    }
    
    /**
     * Autenticar usuario contra LDAP
     * 
     * @param string $email Email institucional del usuario
     * @param string $password Contraseña del usuario
     * @return array|false Datos del usuario si la autenticación es exitosa, false en caso contrario
     */
    public function authenticate($email, $password) {
        try {
            // Si LDAP no está configurado, usar modo de prueba
            if (empty($this->adminDN) || $this->host === 'ldap://localhost') {
                return $this->authenticateMockMode($email, $password);
            }
            
            $this->connect();
            
            // Buscar el usuario por email
            $userDN = $this->findUserDN($email);
            
            if (!$userDN) {
                return false;
            }
            
            // Intentar autenticar con las credenciales del usuario
            $bind = @ldap_bind($this->connection, $userDN, $password);
            
            if (!$bind) {
                return false;
            }
            
            // Obtener información del usuario
            $userData = $this->getUserData($userDN);
            
            ldap_close($this->connection);
            
            return $userData;
            
        } catch (Exception $e) {
            error_log("Error en autenticación LDAP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar el DN del usuario por email
     * 
     * @param string $email
     * @return string|false DN del usuario o false si no se encuentra
     */
    private function findUserDN($email) {
        // Bind con credenciales de administrador para buscar
        $bind = @ldap_bind($this->connection, $this->adminDN, $this->adminPass);
        
        if (!$bind) {
            throw new Exception("No se pudo autenticar con credenciales de administrador LDAP");
        }
        
        // Buscar usuario por email
        $filter = "(mail=$email)";
        $search = ldap_search($this->connection, $this->baseDN, $filter);
        
        if (!$search) {
            return false;
        }
        
        $entries = ldap_get_entries($this->connection, $search);
        
        if ($entries['count'] === 0) {
            return false;
        }
        
        return $entries[0]['dn'];
    }
    
    /**
     * Obtener datos del usuario desde LDAP
     * 
     * @param string $userDN
     * @return array Datos del usuario
     */
    private function getUserData($userDN) {
        $filter = "(objectClass=*)";
        $search = ldap_read($this->connection, $userDN, $filter);
        $entries = ldap_get_entries($this->connection, $search);
        
        $user = $entries[0];
        
        return [
            'email' => $user['mail'][0] ?? '',
            'nombre' => $user['cn'][0] ?? $user['displayname'][0] ?? '',
            'dn' => $userDN
        ];
    }
    
    /**
     * Modo de autenticación de prueba (sin servidor LDAP real)
     * Útil para desarrollo y pruebas
     * 
     * @param string $email
     * @param string $password
     * @return array|false
     */
    private function authenticateMockMode($email, $password) {
        // En modo de prueba, aceptar cualquier password "test123" o "password"
        if ($password !== 'test123' && $password !== 'password') {
            return false;
        }
        
        // Validar que el email tenga formato válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Validar que el email sea institucional (@up.edu.mx)
        if (!str_ends_with($email, '@up.edu.mx')) {
            return false;
        }
        
        // Extraer nombre del email
        $nombre = ucwords(str_replace('.', ' ', explode('@', $email)[0]));
        
        return [
            'email' => $email,
            'nombre' => $nombre,
            'dn' => "cn=$nombre,$this->baseDN"
        ];
    }
    
    /**
     * Verificar si LDAP está configurado y disponible
     * 
     * @return bool
     */
    public function isAvailable() {
        if (!function_exists('ldap_connect')) {
            return false;
        }
        
        if (empty($this->adminDN) || $this->host === 'ldap://localhost') {
            // Modo de prueba siempre disponible
            return true;
        }
        
        try {
            $this->connect();
            $bind = @ldap_bind($this->connection, $this->adminDN, $this->adminPass);
            ldap_close($this->connection);
            return $bind !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}
