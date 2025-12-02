<?php
// ============================================================================
// 📂 CLASE CATEGORIA - categoria.php
// ============================================================================
// Esta clase maneja TODAS las operaciones CRUD de categorías.
// CRUD = Create (Crear), Read (Leer), Update (Actualizar), Delete (Eliminar)
//
// ¿QUÉ ES UNA CATEGORÍA?
// Las categorías organizan las publicaciones por temas (Hematología, Parasitología, etc.)
//
// DIFERENCIA CON MYSQLI:
// Esta clase usa PDO (PHP Data Objects) en vez de mysqli.
// PDO es otra forma de conectarse a bases de datos, más moderna y flexible.
// ============================================================================

class Categoria {
    // ========================================================================
    // PROPIEDADES PRIVADAS
    // ========================================================================
    // private = Solo accesible dentro de esta clase
    private $conn;                      // Conexión a la base de datos (PDO)
    private $table_name = "categorias"; // Nombre de la tabla en MySQL
    
    // ========================================================================
    // PROPIEDADES PÚBLICAS
    // ========================================================================
    // public = Accesible desde fuera de la clase
    // Estas propiedades representan las columnas de la tabla
    public $id;              // ID único de la categoría
    public $nombre;          // Nombre de la categoría (ej: "Hematología")
    public $slug;            // Versión URL-friendly del nombre (ej: "hematologia")
    public $descripcion;     // Descripción de la categoría
    public $color;           // Color en formato hexadecimal (ej: "#FF5733")
    public $icono;           // Nombre del icono de Font Awesome (ej: "fa-flask")
    public $estado;          // Estado: 'activo' o 'inactivo'
    public $fecha_creacion;  // Fecha y hora de creación
    
    // ========================================================================
    // CONSTRUCTOR
    // ========================================================================
    // __construct() se ejecuta automáticamente al crear un objeto de esta clase
    // Ejemplo de uso: $categoria = new Categoria($db);
    public function __construct($db) {
        // Guardamos la conexión a la BD en la propiedad $conn
        $this->conn = $db;
    }
    
    // ========================================================================
    // MÉTODO PRIVADO: crearSlug
    // ========================================================================
    // ¿QUÉ HACE?
    // Convierte un texto normal en un "slug" apto para URLs
    // Ejemplo: "Hematología Clínica" → "hematologia-clinica"
    //
    // ¿POR QUÉ ES IMPORTANTE?
    // Los slugs se usan en URLs amigables: /categoria/hematologia-clinica
    //
    // PROCESO DE TRANSFORMACIÓN:
    // 1. Reemplaza caracteres especiales por guiones
    // 2. Convierte acentos a letras normales (á → a)
    // 3. Elimina caracteres no permitidos
    // 4. Quita guiones del inicio y final
    // 5. Reemplaza múltiples guiones por uno solo
    // 6. Convierte todo a minúsculas
    private function crearSlug($text) {
        // ====================================================================
        // 📌 EXPLICACIÓN DE preg_replace()
        // ====================================================================
        // preg_replace() busca un patrón (regex) y lo reemplaza.
        // Sintaxis: preg_replace(patrón, reemplazo, texto)
        //
        // PASO 1: Reemplazar caracteres especiales por guiones
        // Patrón: ~[^\pL\d]+~u
        // - [^\pL\d]+ = Todo lo que NO sea letra (\pL) o dígito (\d)
        // - u = Modo Unicode (para soportar acentos)
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // ====================================================================
        // 📌 EXPLICACIÓN DE iconv()
        // ====================================================================
        // iconv() convierte entre diferentes codificaciones de caracteres.
        // Aquí lo usamos para convertir acentos a letras normales.
        // 'utf-8' → 'us-ascii//TRANSLIT'
        // TRANSLIT = Transliteración (á → a, ñ → n, ü → u)
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // PASO 2: Eliminar todo lo que no sea guión o letra/número
        // Patrón: ~[^-\w]+~
        // - [^-\w]+ = Todo lo que NO sea guión (-) o palabra (\w)
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // PASO 3: Quitar guiones del inicio y final
        // trim($text, '-') elimina guiones de los extremos
        $text = trim($text, '-');
        
        // PASO 4: Reemplazar múltiples guiones consecutivos por uno solo
        // Patrón: ~-+~
        // - -+ = Uno o más guiones seguidos
        // Ejemplo: "hola---mundo" → "hola-mundo"
        $text = preg_replace('~-+~', '-', $text);
        
        // PASO 5: Convertir todo a minúsculas
        $text = strtolower($text);
        
        // Si después de todo el proceso el texto quedó vacío
        if (empty($text)) {
            return 'n-a';  // Devolvemos "n-a" (not available)
        }
        
        // Devolvemos el slug final
        return $text;
    }

    // ========================================================================
    // MÉTODO PÚBLICO: crear
    // ========================================================================
    // Crea una nueva categoría en la base de datos
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
        
        // Crear slug
        $this->slug = $this->crearSlug($this->nombre);
        
        // Vincular parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":icono", $this->icono);
        $stmt->bindParam(":estado", $this->estado);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Leer todas las categorías
    public function leer() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Leer una categoría por ID
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

    // Actualizar categoría
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
        
        // Crear slug
        $this->slug = $this->crearSlug($this->nombre);
        
        // Vincular parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":icono", $this->icono);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar categoría
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