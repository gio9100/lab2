<?php
class Publicador {
    private $conn;
    private $table_name = "publicadores";

    public $id;
    public $nombre_completo;
    public $email;
    public $telefono;
    public $direccion;
    public $fecha_registro;
    public $estado;
    public $grupo;
    public $observaciones;
    public $especialidad;
    public $ultimo_acceso;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear publicador
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET nombre_completo=?, email=?, telefono=?, 
                     direccion=?, estado=?, especialidad=?";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("ssssss", 
            $this->nombre_completo,
            $this->email,
            $this->telefono,
            $this->direccion,
            $this->estado,
            $this->especialidad
        );

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Leer todos los publicadores con filtros
    public function leerTodos($busqueda = '', $estado = '', $especialidad = '') {
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        
        $params = [];
        $types = '';
        
        if (!empty($busqueda)) {
            $query .= " AND (nombre_completo LIKE ? OR email LIKE ?)";
            $busqueda_like = "%" . $busqueda . "%";
            $params[] = $busqueda_like;
            $params[] = $busqueda_like;
            $types .= "ss";
        }
        if (!empty($estado)) {
            $query .= " AND estado = ?";
            $params[] = $estado;
            $types .= "s";
        }
        if (!empty($especialidad)) {
            $query .= " AND especialidad = ?";
            $params[] = $especialidad;
            $types .= "s";
        }
        
        $query .= " ORDER BY nombre_completo ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Eliminar publicador
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>