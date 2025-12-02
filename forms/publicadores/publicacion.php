<?php
class Publicacion {
    private $conn;
    private $table_name = "publicaciones";

    public $id;
    public $titulo;
    public $slug;
    public $contenido;
    public $resumen;
    public $imagen_principal;
    public $publicador_id;
    public $categoria_id;
    public $estado;
    public $tipo;
    public $fecha_publicacion;
    public $fecha_creacion;
    public $fecha_actualizacion;
    public $vistas;
    public $likes;
    public $meta_descripcion;
    public $tags;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear slug a partir del título
  public function crearSlug($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        
        if (empty($text)) {
            return 'n-a';
        }
        
        return $text;
    }

    // Crear publicación
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET titulo=:titulo, slug=:slug, contenido=:contenido, 
                     resumen=:resumen, imagen_principal=:imagen_principal, 
                     publicador_id=:publicador_id, categoria_id=:categoria_id, 
                     estado=:estado, tipo=:tipo, fecha_publicacion=:fecha_publicacion,
                     meta_descripcion=:meta_descripcion, tags=:tags";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->contenido = htmlspecialchars(strip_tags($this->contenido));
        $this->resumen = htmlspecialchars(strip_tags($this->resumen));
        $this->meta_descripcion = htmlspecialchars(strip_tags($this->meta_descripcion));
        $this->tags = htmlspecialchars(strip_tags($this->tags));
        
        // Crear slug
        $this->slug = $this->crearSlug($this->titulo);
        
        // Si no se proporciona fecha de publicación, usar la actual
        if (empty($this->fecha_publicacion)) {
            $this->fecha_publicacion = date('Y-m-d H:i:s');
        }
        
        // Vincular parámetros
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":contenido", $this->contenido);
        $stmt->bindParam(":resumen", $this->resumen);
        $stmt->bindParam(":imagen_principal", $this->imagen_principal);
        $stmt->bindParam(":publicador_id", $this->publicador_id);
        $stmt->bindParam(":categoria_id", $this->categoria_id);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":fecha_publicacion", $this->fecha_publicacion);
        $stmt->bindParam(":meta_descripcion", $this->meta_descripcion);
        $stmt->bindParam(":tags", $this->tags);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Leer todas las publicaciones con información de categoría
    public function leerConCategoria() {
        $query = "SELECT 
                    p.*, 
                    c.nombre as categoria_nombre,
                    c.color as categoria_color,
                    c.icono as categoria_icono
                  FROM " . $this->table_name . " p
                  LEFT JOIN categorias_laboratorio c ON p.categoria_id = c.id
                  ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Leer una publicación por ID
    public function leerUna() {
        $query = "SELECT 
                    p.*, 
                    c.nombre as categoria_nombre,
                    c.color as categoria_color,
                    c.icono as categoria_icono
                  FROM " . $this->table_name . " p
                  LEFT JOIN categorias_laboratorio c ON p.categoria_id = c.id
                  WHERE p.id = ? 
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->titulo = $row['titulo'];
            $this->slug = $row['slug'];
            $this->contenido = $row['contenido'];
            $this->resumen = $row['resumen'];
            $this->imagen_principal = $row['imagen_principal'];
            $this->publicador_id = $row['publicador_id'];
            $this->categoria_id = $row['categoria_id'];
            $this->estado = $row['estado'];
            $this->tipo = $row['tipo'];
            $this->fecha_publicacion = $row['fecha_publicacion'];
            $this->fecha_creacion = $row['fecha_creacion'];
            $this->fecha_actualizacion = $row['fecha_actualizacion'];
            $this->vistas = $row['vistas'];
            $this->likes = $row['likes'];
            $this->meta_descripcion = $row['meta_descripcion'];
            $this->tags = $row['tags'];
            return true;
        }
        return false;
    }

    // Obtener publicaciones por categoría
    public function leerPorCategoria($categoria_id) {
        $query = "SELECT 
                    p.*, 
                    c.nombre as categoria_nombre,
                    c.color as categoria_color,
                    c.icono as categoria_icono
                  FROM " . $this->table_name . " p
                  LEFT JOIN categorias_laboratorio c ON p.categoria_id = c.id
                  WHERE p.categoria_id = ? AND p.estado = 'publicado'
                  ORDER BY p.fecha_publicacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $categoria_id);
        $stmt->execute();
        return $stmt;
    }
}
?>