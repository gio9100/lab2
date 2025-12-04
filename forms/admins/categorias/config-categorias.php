<?php
// Configuración de Base de Datos (Categorías)
// Clase para gestionar la conexión a la base de datos usando PDO

class Database {
    // Credenciales de la base de datos
    private $host = "localhost";
    private $db_name = "lab_exp_db";
    private $username = "root";
    private $password = "";
    public $conn;

    // Método: Obtener conexión
    // Retorna una instancia de PDO o null si falla
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Crear nueva conexión PDO
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            
            // Configurar codificación UTF-8
            $this->conn->exec("set names utf8");
            
            // Configurar modo de errores a excepciones
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            // Mostrar error si falla la conexión
            echo "Error de conexión: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
?>