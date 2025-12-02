# 📚 GUÍA TÉCNICA 3.0 - PARTE 5
## Programación Orientada a Objetos y Administración

---

# 🎯 PROGRAMACIÓN ORIENTADA A OBJETOS (POO)

## `categoria.php` - Clase Categoria

### 🎯 Propósito
Clase que maneja TODAS las operaciones CRUD (Create, Read, Update, Delete) de categorías usando POO y PDO.

### 📋 Conceptos de POO

#### ¿Qué es POO?
```
POO = Programación Orientada a Objetos
Es una forma de programar que organiza el código en "objetos"
que tienen propiedades (datos) y métodos (funciones).

ANALOGÍA:
Un carro es un objeto:
- Propiedades: color, marca, modelo, velocidad
- Métodos: acelerar(), frenar(), girar()
```

#### Clase vs Objeto
```php
// CLASE = Plano/Molde
class Carro {
    public $color;
    public $marca;
    
    public function acelerar() {
        echo "Acelerando...";
    }
}

// OBJETO = Instancia específica del plano
$mi_carro = new Carro();
$mi_carro->color = "rojo";
$mi_carro->marca = "Toyota";
$mi_carro->acelerar(); // "Acelerando..."

$tu_carro = new Carro();
$tu_carro->color = "azul";
$tu_carro->marca = "Honda";
```

### 📋 Código Completo Explicado

```php
<?php
// ============================================================================
// CLASE CATEGORIA
// ============================================================================

class Categoria {
    // ====================================================================
    // 📌 EXPLICACIÓN DE class
    // ====================================================================
    // class define una clase (plantilla para crear objetos).
    //
    // SINTAXIS:
    // class NombreClase {
    //     // propiedades
    //     // métodos
    // }
    //
    // CONVENCIÓN DE NOMBRES:
    // - PascalCase (primera letra mayúscula)
    // - Singular (Categoria, no Categorias)
    // - Descriptivo (Usuario, Publicacion, Producto)
    
    // ====================================================================
    // PROPIEDADES PRIVADAS
    // ====================================================================
    
    private $conn;
    private $table_name = "categorias";
    // ====================================================================
    // 📌 EXPLICACIÓN DE private
    // ====================================================================
    // private = Solo accesible dentro de esta clase
    //
    // MODIFICADORES DE ACCESO:
    //
    // public:
    // - Accesible desde cualquier parte
    // - Ejemplo: $categoria->nombre = "Hematología";
    //
    // private:
    // - Solo accesible dentro de la clase
    // - Ejemplo: $categoria->conn (ERROR desde fuera)
    //
    // protected:
    // - Accesible en la clase y clases hijas
    // - Usado en herencia
    //
    // EJEMPLO:
    // class Categoria {
    //     public $nombre;      // Accesible desde fuera
    //     private $conn;       // Solo dentro de la clase
    //     protected $config;   // Clase y clases hijas
    // }
    //
    // $cat = new Categoria();
    // $cat->nombre = "Hematología";  // ✓ Funciona (public)
    // $cat->conn = $db;               // ✗ Error (private)
    //
    // ¿POR QUÉ USAR private?
    // - Encapsulación (ocultar detalles internos)
    // - Seguridad (evitar modificación accidental)
    // - Control (validar datos antes de modificar)
    //
    // BUENA PRÁCTICA:
    // - Propiedades: private
    // - Métodos para acceder: public (getters/setters)
    
    // ====================================================================
    // PROPIEDADES PÚBLICAS
    // ====================================================================
    
    public $id;
    public $nombre;
    public $slug;
    public $descripcion;
    public $color;
    public $icono;
    public $estado;
    public $fecha_creacion;
    
    // ====================================================================
    // CONSTRUCTOR
    // ====================================================================
    
    public function __construct($db) {
        // ================================================================
        // 📌 EXPLICACIÓN DE __construct()
        // ================================================================
        // __construct() es un método especial llamado CONSTRUCTOR.
        // Se ejecuta automáticamente al crear un objeto.
        //
        // SINTAXIS:
        // public function __construct(parámetros) {
        //     // código de inicialización
        // }
        //
        // EJEMPLO DE USO:
        // $db = new PDO(...);
        // $categoria = new Categoria($db);  // Llama a __construct($db)
        //
        // ¿PARA QUÉ SIRVE?
        // - Inicializar propiedades
        // - Configurar el objeto
        // - Validar parámetros
        //
        // EJEMPLO COMPLETO:
        // class Usuario {
        //     private $nombre;
        //     private $edad;
        //     
        //     public function __construct($nombre, $edad) {
        //         $this->nombre = $nombre;
        //         $this->edad = $edad;
        //     }
        // }
        //
        // $usuario = new Usuario("Juan", 25);
        // Automáticamente: nombre = "Juan", edad = 25
        //
        // ¿QUÉ ES $this?
        // $this se refiere al objeto actual.
        // $this->conn = "Asignar a la propiedad conn de ESTE objeto"
        //
        // ANALOGÍA:
        // $this es como decir "mi" o "este"
        // $this->nombre = "mi nombre"
        // $this->acelerar() = "yo acelero"
        
        $this->conn = $db;
        // Guardamos la conexión a la BD en la propiedad $conn
    }
    
    // ====================================================================
    // MÉTODO PRIVADO: crearSlug
    // ====================================================================
    
    private function crearSlug($text) {
        // ================================================================
        // 📌 ¿QUÉ ES UN SLUG?
        // ================================================================
        // Un slug es una versión "URL-friendly" de un texto.
        //
        // EJEMPLOS:
        // "Hematología Clínica" → "hematologia-clinica"
        // "Análisis de Sangre" → "analisis-de-sangre"
        // "COVID-19 Testing" → "covid-19-testing"
        //
        // ¿PARA QUÉ SIRVE?
        // Para crear URLs amigables:
        // /categoria/hematologia-clinica
        // /publicacion/analisis-de-sangre
        //
        // CARACTERÍSTICAS:
        // - Solo letras minúsculas
        // - Sin acentos
        // - Sin espacios (reemplazados por guiones)
        // - Sin caracteres especiales
        //
        // ¿POR QUÉ ES IMPORTANTE?
        // - SEO (Google prefiere URLs legibles)
        // - Usabilidad (fácil de recordar y compartir)
        // - Compatibilidad (funciona en todos los navegadores)
        
        // ================================================================
        // PASO 1: Reemplazar caracteres especiales por guiones
        // ================================================================
        
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // ================================================================
        // 📌 EXPLICACIÓN DE preg_replace()
        // ================================================================
        // preg_replace() busca un patrón (regex) y lo reemplaza.
        //
        // SINTAXIS:
        // preg_replace(patrón, reemplazo, texto)
        //
        // PARÁMETROS:
        // 1. Patrón: Expresión regular entre delimitadores (~)
        // 2. Reemplazo: Texto que reemplazará las coincidencias
        // 3. Texto: String donde buscar
        //
        // PATRÓN: ~[^\pL\d]+~u
        // Desglosado:
        // ~ = Delimitador (inicio)
        // [^...] = Clase de caracteres negada (todo lo que NO sea...)
        // \pL = Letra Unicode (a-z, A-Z, á, é, ñ, etc.)
        // \d = Dígito (0-9)
        // + = Uno o más
        // ~ = Delimitador (fin)
        // u = Modificador Unicode
        //
        // TRADUCCIÓN:
        // "Busca uno o más caracteres que NO sean letras ni dígitos"
        //
        // EJEMPLO:
        // "Hola Mundo!" → "Hola-Mundo-"
        // "COVID-19" → "COVID-19" (no cambia, tiene letras y dígitos)
        // "Test@123" → "Test-123"
        //
        // OTROS PATRONES COMUNES:
        // '/[0-9]+/' = Uno o más dígitos
        // '/[a-z]+/i' = Letras (i = case-insensitive)
        // '/\s+/' = Espacios en blanco
        // '/[^a-zA-Z0-9]/' = Caracteres especiales
        
        // ================================================================
        // PASO 2: Convertir acentos a letras normales
        // ================================================================
        
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // ================================================================
        // 📌 EXPLICACIÓN DE iconv()
        // ================================================================
        // iconv() convierte entre codificaciones de caracteres.
        //
        // SINTAXIS:
        // iconv(codificación_origen, codificación_destino, texto)
        //
        // PARÁMETROS:
        // 'utf-8' = Codificación origen (Unicode)
        // 'us-ascii//TRANSLIT' = Codificación destino + transliteración
        // $text = Texto a convertir
        //
        // ¿QUÉ ES //TRANSLIT?
        // Transliteración = Convertir caracteres similares
        //
        // EJEMPLOS DE TRANSLITERACIÓN:
        // á → a
        // é → e
        // í → i
        // ó → o
        // ú → u
        // ñ → n
        // ü → u
        // ç → c
        // ß → ss (alemán)
        // æ → ae
        //
        // EJEMPLO COMPLETO:
        // "Hematología" → "Hematologia"
        // "Niño" → "Nino"
        // "Café" → "Cafe"
        //
        // ALTERNATIVA (SIN TRANSLIT):
        // iconv('utf-8', 'us-ascii', $text)
        // Problema: Elimina caracteres que no puede convertir
        // "Hematología" → "Hematolog" (pierde la í)
        //
        // CON TRANSLIT:
        // "Hematología" → "Hematologia" (convierte í → i)
        //
        // CODIFICACIONES COMUNES:
        // UTF-8 = Unicode (soporta todos los idiomas)
        // US-ASCII = Solo caracteres básicos (A-Z, 0-9)
        // ISO-8859-1 = Latin-1 (Europa Occidental)
        // Windows-1252 = Codificación de Windows
        
        // ================================================================
        // PASO 3: Eliminar caracteres no permitidos
        // ================================================================
        
        $text = preg_replace('~[^-\w]+~', '', $text);
        // ================================================================
        // 📌 EXPLICACIÓN DEL PATRÓN ~[^-\w]+~
        // ================================================================
        // [^-\w]+ = Todo lo que NO sea guión (-) o palabra (\w)
        //
        // \w = Palabra (letras, dígitos, guión bajo)
        // Equivalente a: [a-zA-Z0-9_]
        //
        // EJEMPLO:
        // "hola-mundo!" → "hola-mundo"
        // "test@123" → "test123"
        // "a_b-c" → "a_b-c" (no cambia)
        
        // ================================================================
        // PASO 4: Quitar guiones de los extremos
        // ================================================================
        
        $text = trim($text, '-');
        // ================================================================
        // 📌 EXPLICACIÓN DE trim() CON PARÁMETRO
        // ================================================================
        // trim($texto, $caracteres) elimina caracteres específicos.
        //
        // SINTAXIS:
        // trim(texto, caracteres_a_eliminar)
        //
        // EJEMPLO:
        // trim("-hola-", "-") = "hola"
        // trim("--test--", "-") = "test"
        // trim("-a-b-c-", "-") = "a-b-c"
        //
        // FUNCIONES RELACIONADAS:
        // ltrim() = Elimina del lado izquierdo
        // rtrim() = Elimina del lado derecho
        // trim() = Elimina de ambos lados
        //
        // EJEMPLO:
        // ltrim("-hola-", "-") = "hola-"
        // rtrim("-hola-", "-") = "-hola"
        // trim("-hola-", "-") = "hola"
        
        // ================================================================
        // PASO 5: Reemplazar múltiples guiones por uno solo
        // ================================================================
        
        $text = preg_replace('~-+~', '-', $text);
        // ================================================================
        // 📌 EXPLICACIÓN DEL PATRÓN ~-+~
        // ================================================================
        // -+ = Uno o más guiones consecutivos
        //
        // EJEMPLO:
        // "hola---mundo" → "hola-mundo"
        // "test--123" → "test-123"
        // "a-b-c" → "a-b-c" (no cambia)
        //
        // ¿POR QUÉ?
        // Para evitar slugs feos como "hola---mundo"
        
        // ================================================================
        // PASO 6: Convertir a minúsculas
        // ================================================================
        
        $text = strtolower($text);
        // ================================================================
        // 📌 EXPLICACIÓN DE strtolower()
        // ================================================================
        // Convierte todo el texto a minúsculas.
        //
        // EJEMPLO:
        // strtolower("HOLA") = "hola"
        // strtolower("HoLa MuNdO") = "hola mundo"
        //
        // FUNCIÓN INVERSA:
        // strtoupper("hola") = "HOLA"
        //
        // OTRAS FUNCIONES:
        // ucfirst("hola") = "Hola" (primera letra mayúscula)
        // ucwords("hola mundo") = "Hola Mundo" (cada palabra)
        //
        // IMPORTANTE CON ACENTOS:
        // strtolower("JOSÉ") = "josÉ" (no convierte É)
        // mb_strtolower("JOSÉ", 'UTF-8') = "josé" (convierte todo)
        
        // ================================================================
        // PASO 7: Validar que no esté vacío
        // ================================================================
        
        if (empty($text)) {
            return 'n-a';
            // ============================================================
            // 📌 EXPLICACIÓN DE empty()
            // ============================================================
            // empty() verifica si una variable está vacía.
            //
            // RETORNA true SI:
            // - "" (string vacío)
            // - 0 (cero)
            // - "0" (string "0")
            // - null
            // - false
            // - [] (array vacío)
            //
            // RETORNA false SI:
            // - "hola" (string no vacío)
            // - 1 (número diferente de cero)
            // - true
            // - [1, 2, 3] (array con elementos)
            //
            // DIFERENCIA CON isset():
            // isset() = ¿Existe y no es null?
            // empty() = ¿Está vacío?
            //
            // EJEMPLO:
            // $var = "";
            // isset($var) = true (existe)
            // empty($var) = true (está vacío)
            //
            // $var = null;
            // isset($var) = false (es null)
            // empty($var) = true (está vacío)
        }
        
        return $text;
    }
    
    // ====================================================================
    // MÉTODO PÚBLICO: crear
    // ====================================================================
    
    public function crear() {
        // ================================================================
        // 📌 SINTAXIS DE INSERT EN PDO
        // ================================================================
        // PDO usa named parameters (:nombre) en vez de ? (placeholders)
        //
        // SINTAXIS:
        // INSERT INTO tabla SET columna=:valor
        //
        // DIFERENCIA CON MySQLi:
        // MySQLi: INSERT INTO tabla (col1, col2) VALUES (?, ?)
        // PDO: INSERT INTO tabla SET col1=:col1, col2=:col2
        //
        // VENTAJA DE NAMED PARAMETERS:
        // - Más legible
        // - No importa el orden
        // - Más fácil de mantener
        
        $query = "INSERT INTO " . $this->table_name . " 
                 SET nombre=:nombre, slug=:slug, descripcion=:descripcion, 
                     color=:color, icono=:icono, estado=:estado";
        
        $stmt = $this->conn->prepare($query);
        
        // ================================================================
        // SANITIZAR DATOS
        // ================================================================
        
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        // ================================================================
        // 📌 EXPLICACIÓN DE htmlspecialchars() y strip_tags()
        // ================================================================
        // Son funciones de seguridad para prevenir XSS.
        //
        // strip_tags():
        // Elimina etiquetas HTML y PHP.
        //
        // EJEMPLO:
        // strip_tags("<b>Hola</b>") = "Hola"
        // strip_tags("<script>alert('XSS')</script>") = "alert('XSS')"
        //
        // htmlspecialchars():
        // Convierte caracteres especiales a entidades HTML.
        //
        // CONVERSIONES:
        // < → &lt;
        // > → &gt;
        // & → &amp;
        // " → &quot;
        // ' → &#039;
        //
        // EJEMPLO:
        // htmlspecialchars("<script>") = "&lt;script&gt;"
        //
        // COMBINADOS:
        // $texto = "<b>Hola</b> & <script>alert('XSS')</script>";
        // strip_tags($texto) = "Hola & alert('XSS')"
        // htmlspecialchars(strip_tags($texto)) = "Hola &amp; alert('XSS')"
        //
        // ¿POR QUÉ USAR AMBOS?
        // 1. strip_tags() elimina HTML
        // 2. htmlspecialchars() escapa caracteres especiales
        //
        // PREVIENE:
        // XSS (Cross-Site Scripting)
        // Inyección de código malicioso
        
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->icono = htmlspecialchars(strip_tags($this->icono));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        // Crear slug automáticamente
        $this->slug = $this->crearSlug($this->nombre);
        
        // ================================================================
        // VINCULAR PARÁMETROS EN PDO
        // ================================================================
        
        $stmt->bindParam(":nombre", $this->nombre);
        // ================================================================
        // 📌 EXPLICACIÓN DE bindParam() EN PDO
        // ================================================================
        // bindParam() vincula una variable a un named parameter.
        //
        // SINTAXIS:
        // $stmt->bindParam(":parametro", $variable)
        //
        // DIFERENCIA CON MySQLi:
        // MySQLi: bind_param("s", $variable)
        // PDO: bindParam(":nombre", $variable)
        //
        // EJEMPLO COMPLETO:
        // $query = "INSERT INTO usuarios SET nombre=:nombre, edad=:edad";
        // $stmt = $pdo->prepare($query);
        // $stmt->bindParam(":nombre", $nombre);
        // $stmt->bindParam(":edad", $edad);
        // $stmt->execute();
        //
        // ALTERNATIVA (MÁS COMÚN):
        // $stmt->execute([
        //     ':nombre' => $nombre,
        //     ':edad' => $edad
        // ]);
        
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
    
    // ====================================================================
    // MÉTODO PÚBLICO: leer
    // ====================================================================
    
    public function leer() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
        // ================================================================
        // 📌 RETORNAR EL STATEMENT
        // ================================================================
        // Devolvemos el statement (no los datos).
        //
        // USO:
        // $categoria = new Categoria($db);
        // $stmt = $categoria->leer();
        // while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //     echo $row['nombre'];
        // }
        //
        // VENTAJA:
        // Permite iterar sobre los resultados sin cargar todo en memoria.
    }
    
    // ====================================================================
    // MÉTODO PÚBLICO: leerUna
    // ====================================================================
    
    public function leerUna() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        // ================================================================
        // 📌 bindParam CON ÍNDICE NUMÉRICO
        // ================================================================
        // Cuando usamos ? en vez de :nombre, vinculamos por posición.
        //
        // SINTAXIS:
        // $stmt->bindParam(posición, $variable)
        //
        // POSICIONES:
        // Empiezan en 1 (no en 0)
        //
        // EJEMPLO:
        // $query = "SELECT * FROM usuarios WHERE id = ? AND activo = ?";
        // $stmt->bindParam(1, $id);
        // $stmt->bindParam(2, $activo);
        
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // ================================================================
        // 📌 PDO::FETCH_ASSOC
        // ================================================================
        // Modo de obtención de datos.
        //
        // MODOS DISPONIBLES:
        // PDO::FETCH_ASSOC = Array asociativo ['id' => 5, 'nombre' => 'Juan']
        // PDO::FETCH_NUM = Array numérico [5, 'Juan']
        // PDO::FETCH_OBJ = Objeto $row->id, $row->nombre
        // PDO::FETCH_BOTH = Ambos (asociativo y numérico)
        //
        // EJEMPLO:
        // FETCH_ASSOC: $row['nombre']
        // FETCH_NUM: $row[1]
        // FETCH_OBJ: $row->nombre
        
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
    
    // ====================================================================
    // MÉTODO PÚBLICO: actualizar
    // ====================================================================
    
    public function actualizar() {
        $query = "UPDATE " . $this->table_name . " 
                 SET nombre=:nombre, slug=:slug, descripcion=:descripcion, 
                     color=:color, icono=:icono, estado=:estado 
                 WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->icono = htmlspecialchars(strip_tags($this->icono));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Actualizar slug
        $this->slug = $this->crearSlug($this->nombre);
        
        // Vincular
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
    
    // ====================================================================
    // MÉTODO PÚBLICO: eliminar
    // ====================================================================
    
    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
```

### 🔑 Uso de la Clase

```php
// ============================================================================
// EJEMPLO COMPLETO DE USO
// ============================================================================

// 1. Crear conexión PDO
$db = new PDO("mysql:host=localhost;dbname=lab_exp_db", "root", "");

// 2. Crear instancia de Categoria
$categoria = new Categoria($db);

// ============================================================================
// CREAR NUEVA CATEGORÍA
// ============================================================================
$categoria->nombre = "Hematología";
$categoria->descripcion = "Estudio de la sangre";
$categoria->color = "#FF5733";
$categoria->icono = "fa-flask";
$categoria->estado = "activo";

if ($categoria->crear()) {
    echo "Categoría creada exitosamente";
    echo "Slug generado: " . $categoria->slug; // "hematologia"
}

// ============================================================================
// LEER TODAS LAS CATEGORÍAS
// ============================================================================
$stmt = $categoria->leer();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['nombre'] . " - " . $row['slug'] . "<br>";
}

// ============================================================================
// LEER UNA CATEGORÍA ESPECÍFICA
// ============================================================================
$categoria->id = 5;
if ($categoria->leerUna()) {
    echo "Nombre: " . $categoria->nombre;
    echo "Slug: " . $categoria->slug;
}

// ============================================================================
// ACTUALIZAR CATEGORÍA
// ============================================================================
$categoria->id = 5;
$categoria->nombre = "Hematología Clínica";
$categoria->descripcion = "Estudio clínico de la sangre";
if ($categoria->actualizar()) {
    echo "Categoría actualizada";
    echo "Nuevo slug: " . $categoria->slug; // "hematologia-clinica"
}

// ============================================================================
// ELIMINAR CATEGORÍA
// ============================================================================
$categoria->id = 5;
if ($categoria->eliminar()) {
    echo "Categoría eliminada";
}
```

### 🔑 Conceptos Clave Resumidos

#### POO vs Procedural
```php
// PROCEDURAL (sin clases)
function crearCategoria($nombre, $conn) {
    $query = "INSERT INTO categorias SET nombre = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nombre);
    return $stmt->execute();
}

// POO (con clases)
$categoria = new Categoria($db);
$categoria->nombre = "Hematología";
$categoria->crear();
```

#### Ventajas de POO
- ✅ Organización (todo relacionado en un lugar)
- ✅ Reutilización (crear múltiples objetos)
- ✅ Encapsulación (ocultar detalles internos)
- ✅ Mantenibilidad (más fácil de modificar)

---

*Continuará en Parte 6...*
