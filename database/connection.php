<?php
/**
 * Conexión a Base de Datos
 * Singleton Pattern para una sola instancia
 */

class Database {
    private static $instance = null;
    private $conn;
    
    // Configuración - CAMBIAR ESTOS VALORES
    private $host = 'localhost';
    private $db_name = 'opcriver_triagedb';
    private $username = 'opcriver_triage'; // Cambiar en producción
    private $password = ';%9v#zZvtiU1B0Us';     // Cambiar en producción
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die(json_encode([
                'error' => 'Error de conexión a base de datos',
                'details' => 'Por favor contacte al administrador'
            ]));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}