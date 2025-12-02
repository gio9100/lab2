# 📚 GUÍA TÉCNICA COMPLETA - Lab Explorer
## Explicación Humanizada de Todas las Variables y Conceptos Técnicos

---

## 🔑 CONCEPTOS FUNDAMENTALES DE POO (Programación Orientada a Objetos)

### ¿Qué es `private` vs `public`?

Imagina que tienes una caja fuerte (la clase) con cosas adentro:

#### `private` - Privado (Secreto)
- **¿Qué es?** Son cosas que SOLO la clase puede ver y usar
- **Analogía:** Es como tu diario personal que solo TÚ puedes leer
- **Ejemplo en Categoria:**
  ```php
  private $conn;                      // Conexión a BD (nadie más la toca)
  private $table_name = "categorias"; // Nombre de tabla (interno)
  ```
- **¿Por qué usarlo?** Para proteger información sensible y evitar que otros archivos la modifiquen accidentalmente
- **Acceso:** Solo los métodos DENTRO de la clase pueden usarlas

#### `public` - Público (Accesible)
- **¿Qué es?** Son cosas que CUALQUIERA puede ver y modificar
- **Analogía:** Es como tu nombre en una tarjeta de presentación que todos pueden ver
- **Ejemplo en Categoria:**
  ```php
  public $id;              // ID de la categoría
  public $nombre;          // Nombre (ej: "Hematología")
  public $slug;            // Slug para URL
  public $descripcion;     // Descripción
  public $color;           // Color en hexadecimal
  public $icono;           // Icono de Font Awesome
  public $estado;          // 'activo' o 'inactivo'
  ```
- **¿Por qué usarlo?** Para permitir que otros archivos lean o modifiquen estos datos
- **Acceso:** Cualquier archivo que use la clase puede acceder: `$categoria->nombre`

#### Diferencia Práctica
```php
// ✅ ESTO FUNCIONA (public)
$categoria = new Categoria($db);
$categoria->nombre = "Hematología";  // Podemos modificarlo

// ❌ ESTO DA ERROR (private)
$categoria->conn = $nueva_conexion;  // ERROR: No podemos acceder
$categoria->table_name = "otra";     // ERROR: Es privado
```

---

## 🏷️ ¿QUÉ ES UN SLUG Y PARA QUÉ SIRVE?

### Definición Simple
Un **slug** es una versión "limpia" de un texto para usarse en URLs.

### ¿Por Qué Existe?
Las URLs no pueden tener:
- Espacios
- Acentos (á, é, í, ó, ú)
- Caracteres especiales (¿, !, @, #, etc.)
- Mayúsculas (por convención)

### Transformación de Texto a Slug

#### Ejemplos Reales:
```
"Hematología Clínica"        →  "hematologia-clinica"
"Serie Roja & Blanca"        →  "serie-roja-blanca"
"Toma de Muestra (Básico)"   →  "toma-de-muestra-basico"
"Bacteriología - Nivel 1"    →  "bacteriologia-nivel-1"
"¿Qué es Parasitología?"     →  "que-es-parasitologia"
```

### ¿Para Qué Sirve en Lab Explorer?

#### 1. URLs Amigables
```
❌ MAL:  /categoria.php?id=5
✅ BIEN: /categoria/hematologia-clinica
```

#### 2. SEO (Posicionamiento en Google)
- Google prefiere URLs descriptivas
- Mejora el ranking en búsquedas
- Los usuarios entienden de qué trata la página

#### 3. Compartir en Redes Sociales
```
❌ Feo:  lab-explorer.com/cat?id=12&type=pub
✅ Bonito: lab-explorer.com/categoria/parasitologia
```

### Proceso de Creación del Slug (Método `crearSlug`)

```php
private function crearSlug($text) {
    // PASO 1: Reemplazar caracteres especiales por guiones
    // "Hematología & Clínica" → "Hematología---Clínica"
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
    // PASO 2: Convertir acentos a letras normales
    // "Hematología" → "Hematologia"
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
    // PASO 3: Eliminar caracteres no permitidos
    // Solo quedan letras, números y guiones
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // PASO 4: Quitar guiones de inicio y final
    // "-hematologia-" → "hematologia"
    $text = trim($text, '-');
    
    // PASO 5: Reemplazar múltiples guiones por uno solo
    // "hola---mundo" → "hola-mundo"
    $text = preg_replace('~-+~', '-', $text);
    
    // PASO 6: Todo a minúsculas
    // "Hematologia" → "hematologia"
    $text = strtolower($text);
    
    return $text;
}
```

---

## 🔧 FUNCIONES TÉCNICAS EXPLICADAS

### 1. SESIONES (Session Management)

#### `session_start()`
- **¿Qué hace?** Inicia o reanuda una sesión de usuario
- **¿Cuándo usarlo?** Al inicio de CADA página que necesite saber quién está logueado
- **Analogía:** Es como abrir tu casillero personal en la escuela
- **Ejemplo:**
  ```php
  session_start();  // Abrimos la sesión
  $_SESSION['usuario_id'] = 123;  // Guardamos datos
  ```

#### `session_destroy()`
- **¿Qué hace?** Destruye TODA la sesión y sus datos
- **¿Cuándo usarlo?** Al cerrar sesión (logout)
- **Analogía:** Es como vaciar completamente tu casillero
- **Ejemplo:**
  ```php
  session_destroy();  // Borramos todo
  header('Location: login.php');  // Redirigimos al login
  ```

#### `session_status()`
- **¿Qué hace?** Verifica si hay una sesión activa
- **¿Cuándo usarlo?** Para evitar errores de "sesión ya iniciada"
- **Valores posibles:**
  - `PHP_SESSION_NONE` = No hay sesión iniciada
  - `PHP_SESSION_ACTIVE` = Hay sesión activa
- **Ejemplo:**
  ```php
  if (session_status() === PHP_SESSION_NONE) {
      session_start();  // Solo iniciamos si no hay sesión
  }
  ```

#### `$_SESSION`
- **¿Qué es?** Array global que guarda datos del usuario entre páginas
- **¿Cuándo usarlo?** Para recordar quién está logueado
- **Analogía:** Es como tu mochila que llevas a todas las clases
- **Ejemplo:**
  ```php
  $_SESSION['usuario_nombre'] = "Juan";
  $_SESSION['usuario_id'] = 123;
  $_SESSION['es_admin'] = true;
  ```

---

### 2. SEGURIDAD (Security Functions)

#### `password_hash()`
- **¿Qué hace?** Convierte una contraseña en un código secreto (hash)
- **¿Por qué?** NUNCA guardar contraseñas en texto plano
- **Algoritmo:** Usa bcrypt (muy seguro)
- **Ejemplo:**
  ```php
  $password = "miContraseña123";
  $hash = password_hash($password, PASSWORD_DEFAULT);
  // Resultado: $2y$10$abcd1234...xyz (60 caracteres)
  ```

#### `password_verify()`
- **¿Qué hace?** Verifica si una contraseña coincide con un hash
- **¿Cuándo usarlo?** Al hacer login
- **Ejemplo:**
  ```php
  $password_ingresada = "miContraseña123";
  $hash_guardado = "$2y$10$abcd1234...xyz";
  
  if (password_verify($password_ingresada, $hash_guardado)) {
      echo "¡Contraseña correcta!";
  }
  ```

#### `htmlspecialchars()`
- **¿Qué hace?** Convierte caracteres especiales en código HTML seguro
- **¿Por qué?** Previene ataques XSS (Cross-Site Scripting)
- **Transformaciones:**
  - `<` → `&lt;`
  - `>` → `&gt;`
  - `"` → `&quot;`
  - `&` → `&amp;`
- **Ejemplo:**
  ```php
  $nombre = "<script>alert('hack')</script>";
  echo htmlspecialchars($nombre);
  // Muestra: &lt;script&gt;alert('hack')&lt;/script&gt;
  // En vez de ejecutar el script malicioso
  ```

#### `filter_var()` con `FILTER_VALIDATE_EMAIL`
- **¿Qué hace?** Verifica si un email tiene formato válido
- **¿Cuándo usarlo?** Al validar formularios de registro/login
- **Ejemplo:**
  ```php
  $email = "juan@gmail.com";
  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      echo "Email válido";
  } else {
      echo "Email inválido";
  }
  ```

---

### 3. BASE DE DATOS (Database Functions)

#### MySQLi vs PDO

##### **MySQLi** (MySQL Improved)
- **¿Qué es?** Extensión para conectarse a MySQL
- **Ventajas:** Más rápido, específico para MySQL
- **Desventajas:** Solo funciona con MySQL
- **Uso en el proyecto:** Mayoría de archivos

##### **PDO** (PHP Data Objects)
- **¿Qué es?** Interfaz para conectarse a CUALQUIER base de datos
- **Ventajas:** Funciona con MySQL, PostgreSQL, SQLite, etc.
- **Desventajas:** Un poco más lento
- **Uso en el proyecto:** Clase Categoria, recuperación de contraseña

#### Funciones MySQLi

##### `new mysqli()`
```php
$conn = new mysqli("localhost", "root", "", "lab_exp_db");
// Parámetros: servidor, usuario, contraseña, base de datos
```

##### `prepare()` - Preparar consulta
```php
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
// El ? es un marcador de posición (placeholder)
```

##### `bind_param()` - Vincular parámetros
```php
$stmt->bind_param("s", $email);
// "s" = string, "i" = integer, "d" = double
// Tipos: s (string), i (int), d (double), b (blob)
```

##### `execute()` - Ejecutar consulta
```php
$stmt->execute();  // Ejecuta la consulta preparada
```

##### `get_result()` - Obtener resultado
```php
$result = $stmt->get_result();  // Devuelve objeto con los resultados
```

##### `fetch_assoc()` - Obtener fila como array
```php
$usuario = $result->fetch_assoc();
// Devuelve: ['id' => 1, 'nombre' => 'Juan', 'email' => 'juan@email.com']
```

##### `fetch_all()` - Obtener todas las filas
```php
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
// Devuelve array de arrays
```

##### `num_rows` - Contar filas
```php
if ($result->num_rows > 0) {
    echo "Se encontraron resultados";
}
```

#### Funciones PDO

##### `new PDO()`
```php
$pdo = new PDO("mysql:host=localhost;dbname=lab_exp_db", "root", "");
```

##### `bindParam()` - Vincular parámetros (PDO)
```php
$stmt->bindParam(":email", $email);
// Usa nombres en vez de ?
```

##### `fetch()` - Obtener una fila (PDO)
```php
$row = $stmt->fetch(PDO::FETCH_ASSOC);
```

##### `rowCount()` - Contar filas (PDO)
```php
$cantidad = $stmt->rowCount();
```

---

### 4. MANEJO DE ARCHIVOS (File Handling)

#### `$_FILES`
- **¿Qué es?** Array global con información de archivos subidos
- **Estructura:**
  ```php
  $_FILES['imagen'] = [
      'name' => 'foto.jpg',           // Nombre original
      'type' => 'image/jpeg',         // Tipo MIME
      'tmp_name' => '/tmp/php123',    // Ubicación temporal
      'error' => 0,                   // Código de error
      'size' => 524288                // Tamaño en bytes
  ];
  ```

#### `UPLOAD_ERR_OK`
- **¿Qué es?** Constante que vale 0
- **¿Para qué?** Verificar que la subida fue exitosa
- **Ejemplo:**
  ```php
  if ($_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
      echo "Archivo subido correctamente";
  }
  ```

#### `move_uploaded_file()`
- **¿Qué hace?** Mueve un archivo subido a su ubicación final
- **Parámetros:** (archivo_temporal, destino_final)
- **Ejemplo:**
  ```php
  $tmp = $_FILES['imagen']['tmp_name'];
  $destino = 'uploads/foto.jpg';
  move_uploaded_file($tmp, $destino);
  ```

#### `unlink()`
- **¿Qué hace?** Elimina un archivo del servidor
- **¿Cuándo usarlo?** Al borrar fotos de perfil, publicaciones, etc.
- **Ejemplo:**
  ```php
  unlink('uploads/foto_vieja.jpg');  // Borra el archivo
  ```

#### `file_exists()`
- **¿Qué hace?** Verifica si un archivo o carpeta existe
- **Ejemplo:**
  ```php
  if (file_exists('uploads/foto.jpg')) {
      echo "El archivo existe";
  }
  ```

#### `mkdir()`
- **¿Qué hace?** Crea una carpeta
- **Parámetros:** (ruta, permisos, recursivo)
- **Ejemplo:**
  ```php
  mkdir('uploads/contenido', 0755, true);
  // 0755 = permisos (lectura/escritura)
  // true = crear carpetas padres si no existen
  ```

#### `pathinfo()`
- **¿Qué hace?** Obtiene información de una ruta
- **Constantes útiles:**
  - `PATHINFO_EXTENSION` = extensión (.jpg, .png)
  - `PATHINFO_FILENAME` = nombre sin extensión
  - `PATHINFO_DIRNAME` = directorio
- **Ejemplo:**
  ```php
  $ruta = 'uploads/foto.jpg';
  $ext = pathinfo($ruta, PATHINFO_EXTENSION);  // "jpg"
  ```

#### `mime_content_type()`
- **¿Qué hace?** Detecta el tipo MIME real de un archivo
- **¿Por qué?** Para verificar que realmente es una imagen
- **Ejemplo:**
  ```php
  $tipo = mime_content_type('uploads/foto.jpg');
  // Devuelve: "image/jpeg"
  ```

---

### 5. STRINGS Y TEXTO (String Functions)

#### `trim()`
- **¿Qué hace?** Elimina espacios al inicio y final
- **Ejemplo:**
  ```php
  $texto = "  hola  ";
  echo trim($texto);  // "hola"
  ```

#### `mb_strtolower()`
- **¿Qué hace?** Convierte a minúsculas (soporta acentos)
- **¿Por qué mb_?** "Multibyte" = soporta UTF-8
- **Ejemplo:**
  ```php
  echo mb_strtolower("JOSÉ", 'UTF-8');  // "josé"
  ```

#### `strlen()`
- **¿Qué hace?** Cuenta caracteres de un texto
- **Ejemplo:**
  ```php
  $password = "abc123";
  if (strlen($password) < 6) {
      echo "Contraseña muy corta";
  }
  ```

#### `ucfirst()`
- **¿Qué hace?** Primera letra en mayúscula
- **Ejemplo:**
  ```php
  echo ucfirst("hola");  // "Hola"
  ```

#### `strtolower()`
- **¿Qué hace?** Todo a minúsculas
- **Ejemplo:**
  ```php
  echo strtolower("HOLA");  // "hola"
  ```

#### `explode()`
- **¿Qué hace?** Divide un texto en partes
- **Parámetros:** (separador, texto)
- **Ejemplo:**
  ```php
  $email = "juan@gmail.com";
  $partes = explode('@', $email);
  // Resultado: ['juan', 'gmail.com']
  ```

#### `implode()`
- **¿Qué hace?** Une un array en un texto
- **Parámetros:** (separador, array)
- **Ejemplo:**
  ```php
  $dominios = ['gmail.com', 'outlook.com'];
  echo implode(', ', $dominios);
  // Resultado: "gmail.com, outlook.com"
  ```

#### `strip_tags()`
- **¿Qué hace?** Elimina etiquetas HTML
- **Ejemplo:**
  ```php
  $html = "<p>Hola <b>mundo</b></p>";
  echo strip_tags($html);  // "Hola mundo"
  ```

#### `nl2br()`
- **¿Qué hace?** Convierte saltos de línea en `<br>`
- **Ejemplo:**
  ```php
  $texto = "Línea 1\nLínea 2";
  echo nl2br($texto);
  // Resultado: "Línea 1<br>Línea 2"
  ```

---

### 6. ARRAYS (Array Functions)

#### `in_array()`
- **¿Qué hace?** Verifica si un valor existe en un array
- **Ejemplo:**
  ```php
  $dominios = ['gmail.com', 'outlook.com'];
  if (in_array('gmail.com', $dominios)) {
      echo "Dominio permitido";
  }
  ```

#### `array_slice()`
- **¿Qué hace?** Extrae una porción de un array
- **Parámetros:** (array, inicio, cantidad)
- **Ejemplo:**
  ```php
  $numeros = [1, 2, 3, 4, 5];
  $primeros = array_slice($numeros, 0, 3);
  // Resultado: [1, 2, 3]
  ```

#### `array_filter()`
- **¿Qué hace?** Filtra elementos de un array
- **Ejemplo:**
  ```php
  $publicaciones = [...];
  $publicadas = array_filter($publicaciones, fn($p) => $p['estado'] == 'publicado');
  ```

#### `count()`
- **¿Qué hace?** Cuenta elementos de un array
- **Ejemplo:**
  ```php
  $usuarios = ['Juan', 'María', 'Pedro'];
  echo count($usuarios);  // 3
  ```

#### `empty()`
- **¿Qué hace?** Verifica si una variable está vacía
- **Valores vacíos:** "", 0, null, false, []
- **Ejemplo:**
  ```php
  if (empty($nombre)) {
      echo "El nombre está vacío";
  }
  ```

#### `isset()`
- **¿Qué hace?** Verifica si una variable existe y no es null
- **Ejemplo:**
  ```php
  if (isset($_POST['nombre'])) {
      echo "El campo nombre fue enviado";
  }
  ```

---

### 7. OPERADORES ESPECIALES

#### `??` (Null Coalescing Operator)
- **¿Qué hace?** Devuelve el primer valor que no sea null
- **Ventaja:** Evita errores de "undefined variable"
- **Ejemplo:**
  ```php
  $nombre = $_POST['nombre'] ?? '';
  // Si $_POST['nombre'] existe, lo usa
  // Si no existe, usa ''
  ```

#### `? :` (Ternario)
- **¿Qué hace?** If-else en una línea
- **Sintaxis:** `condición ? si_true : si_false`
- **Ejemplo:**
  ```php
  $edad = 20;
  $mensaje = $edad >= 18 ? "Mayor de edad" : "Menor de edad";
  ```

#### `&&` (AND lógico)
- **¿Qué hace?** Ambas condiciones deben ser verdaderas
- **Ejemplo:**
  ```php
  if ($edad >= 18 && $tiene_licencia) {
      echo "Puede conducir";
  }
  ```

#### `||` (OR lógico)
- **¿Qué hace?** Al menos una condición debe ser verdadera
- **Ejemplo:**
  ```php
  if ($es_admin || $es_publicador) {
      echo "Tiene permisos especiales";
  }
  ```

---

### 8. CONSTANTES Y VARIABLES ESPECIALES

#### `define()`
- **¿Qué hace?** Define una constante (valor que no cambia)
- **Diferencia con variables:** No lleva $ y no se puede modificar
- **Ejemplo:**
  ```php
  define('CLAVE_ADMIN', 'labexplorer2025');
  echo CLAVE_ADMIN;  // "labexplorer2025"
  ```

#### `__DIR__`
- **¿Qué es?** Constante mágica con la ruta del directorio actual
- **¿Cuándo usarlo?** Para rutas absolutas
- **Ejemplo:**
  ```php
  require_once __DIR__ . '/config.php';
  // Si estamos en /var/www/forms/
  // Busca: /var/www/forms/config.php
  ```

#### `NOW()` (SQL)
- **¿Qué es?** Función de MySQL que devuelve fecha/hora actual
- **Formato:** YYYY-MM-DD HH:MM:SS
- **Ejemplo:**
  ```sql
  UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = 1
  ```

---

### 9. EXPRESIONES REGULARES (Regex)

#### `preg_replace()`
- **¿Qué hace?** Busca un patrón y lo reemplaza
- **Parámetros:** (patrón, reemplazo, texto)
- **Uso en slugs:**
  ```php
  // Reemplazar caracteres especiales por guiones
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);
  
  // Explicación del patrón:
  // ~ = delimitador
  // [^\pL\d]+ = todo lo que NO sea letra o dígito
  // u = modo Unicode
  ```

#### `iconv()`
- **¿Qué hace?** Convierte entre codificaciones de caracteres
- **Uso en slugs:** Convertir acentos a letras normales
- **Ejemplo:**
  ```php
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', 'José');
  // Resultado: "Jose"
  
  // TRANSLIT = Transliteración
  // á → a, é → e, ñ → n
  ```

---

### 10. FECHAS Y TIEMPO

#### `date()`
- **¿Qué hace?** Formatea una fecha
- **Formatos comunes:**
  - `'Y-m-d'` = 2025-01-15
  - `'d/m/Y'` = 15/01/2025
  - `'H:i:s'` = 14:30:45
- **Ejemplo:**
  ```php
  echo date('d/m/Y');  // "23/11/2025"
  ```

#### `strtotime()`
- **¿Qué hace?** Convierte texto a timestamp
- **Ejemplo:**
  ```php
  $expira = strtotime('+1 hour');  // Suma 1 hora
  $ayer = strtotime('-1 day');     // Resta 1 día
  ```

#### `time()`
- **¿Qué hace?** Devuelve el timestamp actual
- **¿Qué es timestamp?** Segundos desde 1970-01-01
- **Ejemplo:**
  ```php
  $ahora = time();  // 1700754000
  ```

---

### 11. TOKENS Y SEGURIDAD

#### `bin2hex()`
- **¿Qué hace?** Convierte bytes binarios a hexadecimal
- **Uso:** Crear tokens únicos
- **Ejemplo:**
  ```php
  $token = bin2hex(random_bytes(32));
  // Resultado: "a3f5c9d2e1b4..."
  ```

#### `random_bytes()`
- **¿Qué hace?** Genera bytes aleatorios criptográficamente seguros
- **Uso:** Tokens de recuperación de contraseña
- **Ejemplo:**
  ```php
  $bytes = random_bytes(32);  // 32 bytes aleatorios
  ```

---

### 12. CORREO ELECTRÓNICO (PHPMailer)

#### Configuración SMTP
```php
$mail = new PHPMailer(true);
$mail->isSMTP();                      // Usar SMTP
$mail->Host = 'smtp.gmail.com';       // Servidor
$mail->SMTPAuth = true;               // Autenticación
$mail->Username = 'email@gmail.com';  // Usuario
$mail->Password = 'contraseña';       // Contraseña
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // TLS
$mail->Port = 587;                    // Puerto
```

#### Enviar Correo
```php
$mail->setFrom('from@email.com', 'Nombre');
$mail->addAddress('to@email.com', 'Destinatario');
$mail->Subject = 'Asunto del correo';
$mail->isHTML(true);                  // Formato HTML
$mail->Body = '<h1>Hola</h1>';       // Cuerpo HTML
$mail->AltBody = 'Hola';             // Texto plano
$mail->send();                        // Enviar
```

---

### 13. JAVASCRIPT IMPORTANTE

#### `addEventListener()`
- **¿Qué hace?** Escucha eventos (clicks, cambios, etc.)
- **Ejemplo:**
  ```javascript
  button.addEventListener('click', function() {
      alert('¡Click!');
  });
  ```

#### `querySelector()`
- **¿Qué hace?** Busca UN elemento en el DOM
- **Ejemplo:**
  ```javascript
  const boton = document.querySelector('.btn-primary');
  ```

#### `querySelectorAll()`
- **¿Qué hace?** Busca TODOS los elementos que coincidan
- **Ejemplo:**
  ```javascript
  const botones = document.querySelectorAll('.btn');
  ```

#### `classList`
- **¿Qué hace?** Manipula clases CSS de un elemento
- **Métodos:**
  - `add()` = agregar clase
  - `remove()` = quitar clase
  - `toggle()` = alternar clase
- **Ejemplo:**
  ```javascript
  elemento.classList.add('active');
  elemento.classList.remove('hidden');
  ```

#### `FileReader`
- **¿Qué hace?** Lee archivos del usuario
- **Uso:** Preview de imágenes antes de subir
- **Ejemplo:**
  ```javascript
  const reader = new FileReader();
  reader.readAsDataURL(archivo);
  reader.onload = function(e) {
      imagen.src = e.target.result;  // Mostrar preview
  };
  ```

---

### 14. QUILL EDITOR

#### Inicialización
```javascript
const quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline'],
            ['image', 'link'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }]
        ]
    }
});
```

#### Obtener Contenido
```javascript
const contenido = quill.root.innerHTML;  // HTML
```

---

## 🎯 RESUMEN DE CONCEPTOS CLAVE

### Seguridad
- ✅ Siempre usar `password_hash()` para contraseñas
- ✅ Siempre usar `htmlspecialchars()` al mostrar datos de usuarios
- ✅ Siempre usar `prepare()` y `bind_param()` para SQL
- ✅ Validar emails con `filter_var()`

### Sesiones
- ✅ Llamar `session_start()` al inicio de cada página
- ✅ Usar `$_SESSION` para guardar datos del usuario
- ✅ Usar `session_destroy()` al cerrar sesión

### Archivos
- ✅ Validar tipo MIME con `mime_content_type()`
- ✅ Validar tamaño de archivo
- ✅ Usar nombres únicos con `time()` y `uniqid()`
- ✅ Crear carpetas con `mkdir()` antes de guardar

### Base de Datos
- ✅ Usar sentencias preparadas SIEMPRE
- ✅ Cerrar conexiones con `close()`
- ✅ Verificar resultados con `num_rows` o `rowCount()`

### Slugs
- ✅ Crear slugs para URLs amigables
- ✅ Sin espacios, sin acentos, sin mayúsculas
- ✅ Solo letras, números y guiones

---

## 📖 GLOSARIO RÁPIDO

| Término | Significado |
|---------|-------------|
| **Hash** | Código secreto generado de una contraseña |
| **Slug** | Versión limpia de texto para URLs |
| **CRUD** | Create, Read, Update, Delete |
| **PDO** | PHP Data Objects (conexión a BD) |
| **MySQLi** | MySQL Improved (conexión a MySQL) |
| **SMTP** | Protocolo para enviar correos |
| **XSS** | Cross-Site Scripting (ataque web) |
| **SQL Injection** | Ataque inyectando código SQL |
| **Timestamp** | Segundos desde 1970-01-01 |
| **MIME Type** | Tipo de archivo (image/jpeg, etc.) |
| **Token** | Código único temporal |
| **Session** | Datos del usuario entre páginas |
| **Cookie** | Datos guardados en el navegador |

---

**¡Guía completa creada! 🎉**
Todas las funciones técnicas explicadas de forma humanizada y comprensible.
