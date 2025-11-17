<?php
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            // Aseguramos que las constantes existan antes de usarlas
            if (!defined('DB_HOST')) {
                throw new Exception("Configuraci車n de base de datos no cargada (DB_HOST no definido)");
            }

            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            // Usamos error_log para no mostrar detalles sensibles al usuario
            error_log("Error de conexi車n: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos");
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
    
    // Prevenir clonaci車n
    private function __clone() {}
    
    // Prevenir deserializaci車n
    public function __wakeup() {
        throw new Exception("No se puede deserializar un Singleton");
    }
}