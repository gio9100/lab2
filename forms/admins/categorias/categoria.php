<?php
// Clase Categoria (Admin)
// Maneja operaciones CRUD para categorías usando PDO

class Categoria {
    // Propiedades privadas
    private $conn;
    private $table_name = "categorias";
    
    // Propiedades públicas (columnas de la tabla)
    public $id;
    public $nombre;
    public $slug;
    public $descripcion;
    public $color;
    public $icono;
    public $estado;
    public $fecha_creacion;
    
    // Constructor: Inicializa la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Método privado: Crear slug URL-friendly
    // Convierte texto a formato slug (ej: "Hola Mundo" -> "hola-mundo")
    private function crearSlug($text) {
        // Reemplazar caracteres no alfanuméricos por guiones (Unicode)
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // Transliterar caracteres (ej: á -> a)
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Eliminar caracteres no deseados
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Eliminar guiones al inicio y final
        $text = trim($text, '-');
        
        // Reemplazar guiones duplicados
        $text = preg_replace('~-+~', '-', $text);
        
        // Convertir a minúsculas
        $text = strtolower($text);
        
        // Retornar 'n-a' si el string está vacío
        if (empty($text)) {
            return 'n-a';
        }
        
        return $text;
    }

    // Método: Crear nueva categoría
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET nombre=:nombre, slug=:slug, descripcion=:descripcion, 
                     color=:color, icono=:icono, estado=:estado";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->icono = htmlspecialchars(strip_tags($this->icono));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Generar slug
        $this->slug = $this->crearSlug($this->nombre);
        
        // Vincular parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":icono", $this->icono);
        $stmt->bindParam(":estado", $this->estado);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método: Leer todas las categorías
    public function leer() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Método: Leer una categoría específica por ID
    public function leerUna() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->nombre = $row['nombre'];
            $this->slug = $row['slug'];
            $this->descripcion = $row['descripcion'];
            $this->color = $row['color'];
            $this->icono = $row['icono'];
            $this->estado = $row['estado'];
            $this->fecha_creacion = $row['fecha_creacion'];
            return true;
        }
        return false;
    }

    // Método: Actualizar categoría existente
    public function actualizar() {
        $query = "UPDATE " . $this->table_name . " 
                 SET nombre=:nombre, slug=:slug, descripcion=:descripcion, 
                     color=:color, icono=:icono, estado=:estado 
                 WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->icono = htmlspecialchars(strip_tags($this->icono));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Regenerar slug
        $this->slug = $this->crearSlug($this->nombre);
        
        // Vincular parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":icono", $this->icono);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);
        
        // Ejecutar actualización
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método: Eliminar categoría
    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
