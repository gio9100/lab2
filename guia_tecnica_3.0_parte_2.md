# 📚 GUÍA TÉCNICA 3.0 - PARTE 2
## Archivos de Configuración y Funciones Core

---

# 📂 CARPETA `forms/` - ARCHIVOS PRINCIPALES

## `conexion.php` - Conexión Global a la Base de Datos

### 🎯 Propósito
Archivo central que establece la conexión a MySQL y se incluye en TODOS los demás archivos que necesitan acceso a la base de datos.

### 📋 Código Completo Explicado

```php
<?php 
// ============================================================================
// LÍNEA 1: Apertura de bloque PHP
// ============================================================================
// <?php indica el inicio de código PHP
// Todo lo que esté entre <?php y ?> será interpretado como PHP

// ============================================================================
// LÍNEAS 2-7: VERIFICACIÓN DE SESIÓN
// ============================================================================

// Verificamos si ya hay una sesión iniciada antes de crear una nueva
if(session_status() === PHP_SESSION_NONE) {
    // ========================================================================
    // 📌 EXPLICACIÓN DE session_status()
    // ========================================================================
    // session_status() devuelve el estado actual de las sesiones.
    // Puede devolver 3 valores:
    // 
    // 1. PHP_SESSION_DISABLED = Las sesiones están deshabilitadas (raro)
    // 2. PHP_SESSION_NONE = Las sesiones están habilitadas pero NO hay ninguna activa
    // 3. PHP_SESSION_ACTIVE = Hay una sesión activa
    //
    // ¿POR QUÉ VERIFICAR?
    // Si llamamos session_start() cuando ya hay una sesión activa,
    // PHP lanzará un WARNING: "session already started"
    //
    // COMPARACIÓN CON ===:
    // === compara valor Y tipo de dato (más estricto que ==)
    // PHP_SESSION_NONE es una constante que vale 1 (integer)
    
    session_start();
    // ====================================================================
    // 📌 EXPLICACIÓN DE session_start()
    // ====================================================================
    // session_start() hace 3 cosas importantes:
    //
    // 1. Busca una cookie llamada PHPSESSID en el navegador del usuario
    // 2. Si existe, carga los datos de sesión del servidor
    // 3. Si no existe, crea una nueva sesión y genera un ID único
    //
    // DESPUÉS de session_start(), podemos usar $_SESSION:
    // $_SESSION['usuario_id'] = 123;
    // $_SESSION['nombre'] = "Juan";
    //
    // IMPORTANTE: session_start() DEBE ir antes de cualquier salida HTML
    // (antes de echo, print, o cualquier HTML)
}

// ============================================================================
// LÍNEAS 9-12: CREDENCIALES DE LA BASE DE DATOS
// ============================================================================

$servidor_db = "localhost";
// ========================================================================
// 📌 EXPLICACIÓN DE "localhost"
// ========================================================================
// "localhost" es la dirección del servidor de base de datos.
// En desarrollo local (XAMPP, WAMP, MAMP), la BD está en la misma computadora.
// "localhost" = 127.0.0.1 (dirección IP local)
//
// En producción (servidor real), esto cambiaría a:
// - Una IP: "192.168.1.100"
// - Un dominio: "db.miservidor.com"

$usuario_bd = "root";
// ========================================================================
// 📌 EXPLICACIÓN DE "root"
// ========================================================================
// "root" es el usuario administrador de MySQL.
// En XAMPP, el usuario por defecto es "root".
// En producción, NUNCA usar "root", crear un usuario específico:
// CREATE USER 'lab_user'@'localhost' IDENTIFIED BY 'contraseña_segura';

$contrasena_bd = "";
// ========================================================================
// 📌 EXPLICACIÓN DE contraseña vacía
// ========================================================================
// En XAMPP, la contraseña de root está vacía por defecto.
// En producción, SIEMPRE usar contraseña fuerte.
// Ejemplo: $contrasena_bd = "P@ssw0rd!2025_Secure";

$nombre_bd = "lab_exp_db";
// ========================================================================
// 📌 EXPLICACIÓN DEL NOMBRE DE LA BASE DE DATOS
// ========================================================================
// "lab_exp_db" es el nombre de nuestra base de datos.
// Debe existir en MySQL antes de conectarnos.
// Para crearla: CREATE DATABASE lab_exp_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

// ============================================================================
// LÍNEA 15: CREAR CONEXIÓN CON MySQLi
// ============================================================================

// mysqli es la forma moderna de conectarse a MySQL en PHP
$conexion = new mysqli($servidor_db, $usuario_bd, $contrasena_bd, $nombre_bd);

// ========================================================================
// 📌 EXPLICACIÓN DE new mysqli()
// ========================================================================
// new mysqli() es un CONSTRUCTOR que crea un objeto de conexión.
//
// PARÁMETROS (en orden):
// 1. $servidor_db: Dirección del servidor ("localhost")
// 2. $usuario_bd: Usuario de MySQL ("root")
// 3. $contrasena_bd: Contraseña del usuario ("")
// 4. $nombre_bd: Base de datos a usar ("lab_exp_db")
//
// RETORNA:
// Un objeto mysqli que representa la conexión activa.
//
// DIFERENCIA CON mysql_connect() (OBSOLETO):
// mysql_connect() está DEPRECADO desde PHP 5.5 y ELIMINADO en PHP 7.0
// mysqli = MySQL Improved (mejorado)
//
// VENTAJAS DE MySQLi:
// - Soporta sentencias preparadas (previene SQL injection)
// - Mejor manejo de errores
// - Soporta transacciones
// - Más rápido y seguro

// ============================================================================
// LÍNEAS 17-20: VERIFICAR ERRORES DE CONEXIÓN
// ============================================================================

if ($conexion->connect_error) {
    // ====================================================================
    // 📌 EXPLICACIÓN DE $conexion->connect_error
    // ====================================================================
    // connect_error es una PROPIEDAD del objeto $conexion.
    // Contiene el mensaje de error si la conexión falló.
    // Si la conexión fue exitosa, connect_error es NULL (vacío).
    //
    // POSIBLES ERRORES:
    // - "Access denied for user 'root'@'localhost'" = contraseña incorrecta
    // - "Unknown database 'lab_exp_db'" = la BD no existe
    // - "Can't connect to MySQL server" = MySQL no está corriendo
    
    // die() detiene todo el código y muestra un mensaje
    die("error de conexion a msyql:" . $conexion->connect_error);
    // ====================================================================
    // 📌 EXPLICACIÓN DE die()
    // ====================================================================
    // die() hace 2 cosas:
    // 1. Imprime el mensaje que le pasamos
    // 2. Detiene la ejecución del script inmediatamente
    //
    // EQUIVALENTE A:
    // echo "error de conexion a msyql:" . $conexion->connect_error;
    // exit();
    //
    // OPERADOR DE CONCATENACIÓN (.):
    // El punto (.) une strings en PHP
    // "Hola" . " " . "Mundo" = "Hola Mundo"
}

// ============================================================================
// LÍNEAS 22-26: CONFIGURAR CHARSET UTF-8
// ============================================================================

// set_charset configura la codificación de caracteres
// utf8mb4 soporta emojis y caracteres especiales (mejor que utf8 normal)
if (!$conexion->set_charset("utf8mb4")) {
    // ====================================================================
    // 📌 EXPLICACIÓN DE set_charset("utf8mb4")
    // ====================================================================
    // set_charset() establece el conjunto de caracteres para la conexión.
    //
    // ¿QUÉ ES UTF-8?
    // UTF-8 es una codificación de caracteres que soporta TODOS los idiomas.
    //
    // ¿POR QUÉ utf8mb4 Y NO utf8?
    // - utf8 en MySQL solo soporta caracteres de 1-3 bytes
    // - utf8mb4 soporta caracteres de 1-4 bytes
    // - Emojis (😀, 🎉, ❤️) necesitan 4 bytes
    // - Algunos caracteres chinos/japoneses necesitan 4 bytes
    //
    // EJEMPLO SIN utf8mb4:
    // Intentar guardar "Hola 😀" resultaría en "Hola ?"
    //
    // CON utf8mb4:
    // "Hola 😀" se guarda correctamente
    //
    // RETORNO:
    // set_charset() devuelve true si tuvo éxito, false si falló
    //
    // OPERADOR ! (NOT):
    // ! invierte el valor booleano
    // !true = false
    // !false = true
    // Entonces if (!$conexion->set_charset()) = if (falló)
    
    die ("error al configurar UTF-8:" . $conexion->connect_error);
}

?>
```

### 🔑 Conceptos Clave

#### session_status() vs isset($_SESSION)
```php
// ❌ FORMA INCORRECTA (puede dar error)
if (!isset($_SESSION)) {
    session_start();
}
// Problema: $_SESSION siempre existe después de session_start()

// ✅ FORMA CORRECTA
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Verifica si realmente hay una sesión activa
```

#### MySQLi vs PDO
```php
// MySQLi (usado en este proyecto)
$conn = new mysqli("localhost", "root", "", "lab_exp_db");
$result = $conn->query("SELECT * FROM usuarios");

// PDO (usado en categorías)
$conn = new PDO("mysql:host=localhost;dbname=lab_exp_db", "root", "");
$result = $conn->query("SELECT * FROM usuarios");
```

**Diferencias:**
- MySQLi solo funciona con MySQL
- PDO funciona con MySQL, PostgreSQL, SQLite, etc.
- MySQLi es ligeramente más rápido
- PDO es más portable

---

## `config-publicadores.php` - Configuración de Publicadores

### 🎯 Propósito
Archivo de configuración central para el módulo de publicadores. Contiene la conexión a BD y todas las funciones reutilizables.

### 📋 Código Completo Explicado

```php
<?php
// =============================================================================
// ARCHIVO: config-publicadores.php
// CONFIGURACIÓN: Para el panel de publicadores
// =============================================================================

// ============================================================================
// SECCIÓN 1: CONFIGURACIÓN DE LA BASE DE DATOS
// ============================================================================

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

// Crear la conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si hubo error en la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar el juego de caracteres
$conn->set_charset("utf8mb4");

// ============================================================================
// SECCIÓN 2: CONFIGURACIÓN DE ZONA HORARIA
// ============================================================================

date_default_timezone_set('America/Mexico_City');
// ========================================================================
// 📌 EXPLICACIÓN DE date_default_timezone_set()
// ========================================================================
// Esta función establece la zona horaria predeterminada para TODAS las
// funciones de fecha/hora en este script.
//
// ¿POR QUÉ ES IMPORTANTE?
// Sin esto, PHP usa la zona horaria del servidor (puede ser UTC).
// Si el servidor está en otro país, las fechas estarán mal.
//
// EJEMPLO SIN CONFIGURAR:
// Servidor en USA (UTC-5), guardamos fecha:
// date('Y-m-d H:i:s') = "2025-01-15 10:00:00" (hora de USA)
//
// CON CONFIGURACIÓN:
// date('Y-m-d H:i:s') = "2025-01-15 11:00:00" (hora de México)
//
// ZONAS HORARIAS COMUNES:
// - 'America/Mexico_City' = Ciudad de México (UTC-6)
// - 'America/New_York' = Nueva York (UTC-5)
// - 'Europe/Madrid' = Madrid (UTC+1)
// - 'Asia/Tokyo' = Tokio (UTC+9)

// ============================================================================
// SECCIÓN 3: FUNCIONES PARA PUBLICADORES
// ============================================================================

/**
 * FUNCIÓN: loginPublicador
 * PROPÓSITO: Verificar si el email y password son correctos para publicadores
 * 
 * @param string $email - Email del publicador
 * @param string $password - Contraseña en texto plano
 * @param mysqli $conn - Objeto de conexión a la BD
 * @return array|false - Datos del publicador si es correcto, false si falla
 */
function loginPublicador($email, $password, $conn) {
    // ====================================================================
    // PASO 1: Preparar la consulta SQL
    // ====================================================================
    $query = "SELECT * FROM publicadores WHERE email = ? AND estado = 'activo'";
    // ====================================================================
    // 📌 EXPLICACIÓN DE LA CONSULTA
    // ====================================================================
    // SELECT * = Seleccionar todas las columnas
    // FROM publicadores = De la tabla publicadores
    // WHERE email = ? = Donde el email coincida con el parámetro
    // AND estado = 'activo' = Y el estado sea 'activo'
    //
    // ¿POR QUÉ VERIFICAR estado = 'activo'?
    // Para evitar que publicadores suspendidos o rechazados puedan entrar.
    //
    // ESTADOS POSIBLES:
    // - 'pendiente' = Esperando aprobación del admin
    // - 'activo' = Aprobado, puede iniciar sesión
    // - 'suspendido' = Temporalmente bloqueado
    // - 'rechazado' = Solicitud rechazada
    //
    // ¿QUÉ ES EL ? (PLACEHOLDER)?
    // Es un marcador de posición para el valor del email.
    // Previene SQL Injection (ataques de seguridad).
    
    // ====================================================================
    // PASO 2: Preparar la sentencia
    // ====================================================================
    $stmt = $conn->prepare($query);
    // ====================================================================
    // 📌 EXPLICACIÓN DE prepare()
    // ====================================================================
    // prepare() hace 3 cosas:
    // 1. Analiza la sintaxis SQL
    // 2. Compila la consulta
    // 3. Crea un plan de ejecución optimizado
    //
    // VENTAJAS:
    // - Previene SQL Injection automáticamente
    // - Más rápido si ejecutamos la misma consulta varias veces
    // - Separa la lógica SQL de los datos
    //
    // RETORNA:
    // Un objeto mysqli_stmt (statement)
    
    // ====================================================================
    // PASO 3: Vincular parámetros
    // ====================================================================
    $stmt->bind_param("s", $email);
    // ====================================================================
    // 📌 EXPLICACIÓN DE bind_param()
    // ====================================================================
    // bind_param() vincula variables PHP a los marcadores ? de la consulta.
    //
    // SINTAXIS:
    // bind_param(tipos, variable1, variable2, ...)
    //
    // TIPOS DE DATOS:
    // "s" = string (texto)
    // "i" = integer (número entero)
    // "d" = double (número decimal)
    // "b" = blob (datos binarios)
    //
    // EJEMPLOS:
    // bind_param("s", $email) = 1 string
    // bind_param("si", $nombre, $edad) = 1 string, 1 integer
    // bind_param("ssi", $nombre, $email, $id) = 2 strings, 1 integer
    //
    // ¿POR QUÉ ES SEGURO?
    // MySQL trata los valores como DATOS, no como código SQL.
    // Ejemplo de ataque prevenido:
    // $email = "admin@email.com' OR '1'='1"
    // Sin prepare: SELECT * FROM publicadores WHERE email = 'admin@email.com' OR '1'='1'
    // (Devolvería TODOS los usuarios)
    // Con prepare: SELECT * FROM publicadores WHERE email = "admin@email.com' OR '1'='1"
    // (Busca literalmente ese email raro, no encuentra nada)
    
    // ====================================================================
    // PASO 4: Ejecutar la consulta
    // ====================================================================
    $stmt->execute();
    // ====================================================================
    // 📌 EXPLICACIÓN DE execute()
    // ====================================================================
    // execute() envía la consulta preparada al servidor MySQL.
    // El servidor:
    // 1. Reemplaza los ? con los valores vinculados
    // 2. Ejecuta la consulta
    // 3. Devuelve los resultados
    //
    // RETORNA:
    // true si tuvo éxito, false si falló
    
    // ====================================================================
    // PASO 5: Obtener resultados
    // ====================================================================
    $result = $stmt->get_result();
    // ====================================================================
    // 📌 EXPLICACIÓN DE get_result()
    // ====================================================================
    // get_result() obtiene el conjunto de resultados de la consulta.
    //
    // RETORNA:
    // Un objeto mysqli_result con las filas encontradas.
    //
    // MÉTODOS ÚTILES DEL RESULTADO:
    // - $result->num_rows = Número de filas encontradas
    // - $result->fetch_assoc() = Obtener siguiente fila como array
    // - $result->fetch_all() = Obtener todas las filas
    
    // ====================================================================
    // PASO 6: Verificar si encontró exactamente 1 publicador
    // ====================================================================
    if ($result->num_rows === 1) {
        // ================================================================
        // 📌 EXPLICACIÓN DE num_rows
        // ================================================================
        // num_rows es una propiedad que contiene el número de filas.
        //
        // ¿POR QUÉ === 1 Y NO > 0?
        // Porque el email debe ser ÚNICO en la base de datos.
        // Si encontramos 2 o más, hay un problema de integridad de datos.
        //
        // COMPARACIÓN === vs ==:
        // === compara valor Y tipo
        // == solo compara valor
        // 
        // Ejemplo:
        // 1 == "1" = true (valores iguales)
        // 1 === "1" = false (tipos diferentes: int vs string)
        
        $publicador = $result->fetch_assoc();
        // ============================================================
        // 📌 EXPLICACIÓN DE fetch_assoc()
        // ============================================================
        // fetch_assoc() obtiene la siguiente fila como array asociativo.
        //
        // RETORNA:
        // Array donde las claves son los nombres de las columnas:
        // [
        //     'id' => 5,
        //     'nombre' => 'Dr. Juan Pérez',
        //     'email' => 'juan@email.com',
        //     'password' => '$2y$10$abcd1234...',
        //     'especialidad' => 'Hematología',
        //     'estado' => 'activo'
        // ]
        //
        // DIFERENCIA CON fetch_row():
        // fetch_row() devuelve array numérico: [5, 'Dr. Juan Pérez', ...]
        // fetch_assoc() devuelve array asociativo: ['id' => 5, ...]
        
        // ============================================================
        // PASO 7: Verificar contraseña
        // ============================================================
        if (password_verify($password, $publicador['password'])) {
            // ========================================================
            // 📌 EXPLICACIÓN DE password_verify()
            // ========================================================
            // password_verify() compara una contraseña en texto plano
            // con un hash generado por password_hash().
            //
            // PARÁMETROS:
            // 1. $password: Contraseña ingresada (texto plano)
            // 2. $publicador['password']: Hash guardado en BD
            //
            // EJEMPLO:
            // $password = "miContraseña123"
            // $hash = "$2y$10$abcd1234efgh5678..."
            // password_verify("miContraseña123", $hash) = true
            // password_verify("otraContraseña", $hash) = false
            //
            // ¿CÓMO FUNCIONA INTERNAMENTE?
            // 1. Extrae el "salt" (sal) del hash
            // 2. Aplica el mismo algoritmo bcrypt a la contraseña ingresada
            // 3. Compara el resultado con el hash guardado
            //
            // SEGURIDAD:
            // - Cada hash tiene un "salt" único aleatorio
            // - Mismo password genera hashes diferentes cada vez
            // - Imposible revertir el hash a la contraseña original
            //
            // EJEMPLO DE HASHES DIFERENTES:
            // password_hash("abc123", PASSWORD_DEFAULT)
            // 1ra vez: "$2y$10$xyz123..."
            // 2da vez: "$2y$10$abc789..." (diferente!)
            // Pero password_verify("abc123", ambos) = true
            
            // ========================================================
            // PASO 8: Actualizar último acceso
            // ========================================================
            $update_query = "UPDATE publicadores SET ultimo_acceso = NOW() WHERE id = ?";
            // ====================================================
            // 📌 EXPLICACIÓN DE NOW()
            // ====================================================
            // NOW() es una función de MySQL que devuelve la fecha/hora actual.
            //
            // FORMATO:
            // 'YYYY-MM-DD HH:MM:SS'
            // Ejemplo: '2025-01-15 14:30:45'
            //
            // DIFERENCIA CON CURDATE() Y CURTIME():
            // NOW() = '2025-01-15 14:30:45' (fecha + hora)
            // CURDATE() = '2025-01-15' (solo fecha)
            // CURTIME() = '14:30:45' (solo hora)
            
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $publicador['id']);
            // "i" = integer, porque id es un número
            
            $update_stmt->execute();
            
            // ========================================================
            // PASO 9: Devolver datos del publicador
            // ========================================================
            return $publicador;
            // Devuelve el array completo con todos los datos
        }
    }
    
    // Si llegamos aquí, el login falló
    return false;
}

/**
 * FUNCIÓN: registrarPublicador
 * PROPÓSITO: Crear un nuevo publicador en la base de datos
 */
function registrarPublicador($datos, $conn) {
    $query = "INSERT INTO publicadores (
        nombre, 
        email, 
        password, 
        especialidad, 
        titulo_academico, 
        institucion, 
        estado
    ) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";
    // ====================================================================
    // 📌 EXPLICACIÓN DE INSERT INTO
    // ====================================================================
    // INSERT INTO agrega una nueva fila a la tabla.
    //
    // SINTAXIS:
    // INSERT INTO tabla (columna1, columna2, ...) VALUES (valor1, valor2, ...)
    //
    // IMPORTANTE:
    // - El número de columnas debe coincidir con el número de valores
    // - El orden importa
    // - estado = 'pendiente' es un valor fijo (no viene de $datos)
    //
    // ¿POR QUÉ 'pendiente'?
    // Los nuevos publicadores deben ser aprobados por un admin antes de poder publicar.
    
    $stmt = $conn->prepare($query);
    
    // ====================================================================
    // PASO: Hashear la contraseña
    // ====================================================================
    $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
    // ====================================================================
    // 📌 EXPLICACIÓN DE password_hash()
    // ====================================================================
    // password_hash() convierte una contraseña en texto plano en un hash seguro.
    //
    // PARÁMETROS:
    // 1. $datos['password']: Contraseña en texto plano
    // 2. PASSWORD_DEFAULT: Algoritmo a usar (actualmente bcrypt)
    //
    // EJEMPLO:
    // password_hash("abc123", PASSWORD_DEFAULT)
    // Resultado: "$2y$10$abcdefgh1234567890..."
    //
    // ESTRUCTURA DEL HASH:
    // $2y$ = Algoritmo bcrypt
    // 10$ = Cost factor (complejidad)
    // abcdefgh... = Salt (sal aleatoria)
    // 1234567890... = Hash resultante
    //
    // SEGURIDAD:
    // - Cada vez genera un hash diferente (gracias al salt aleatorio)
    // - Imposible revertir a la contraseña original
    // - Resistente a ataques de fuerza bruta
    //
    // ¿POR QUÉ NO GUARDAR LA CONTRASEÑA EN TEXTO PLANO?
    // Si alguien hackea la BD, tendría todas las contraseñas.
    // Con hashes, solo tiene códigos inútiles.
    
    // ====================================================================
    // PASO: Vincular 6 parámetros
    // ====================================================================
    $stmt->bind_param("ssssss", 
        $datos['nombre'],
        $datos['email'],
        $password_hash,
        $datos['especialidad'],
        $datos['titulo_academico'],
        $datos['institucion']
    );
    // ====================================================================
    // 📌 EXPLICACIÓN DE "ssssss"
    // ====================================================================
    // "ssssss" = 6 strings (uno por cada ?)
    //
    // ORDEN DE LOS PARÁMETROS:
    // 1. nombre (string)
    // 2. email (string)
    // 3. password_hash (string)
    // 4. especialidad (string)
    // 5. titulo_academico (string)
    // 6. institucion (string)
    //
    // IMPORTANTE:
    // El orden DEBE coincidir con el orden de los ? en la consulta.
    
    return $stmt->execute();
    // Devuelve true si se insertó correctamente, false si falló
}

// ... (más funciones continúan)
```

### 🔑 Conceptos Clave Adicionales

#### Estados de Publicadores
```php
// Flujo de estados:
'pendiente'   → Nuevo registro, esperando aprobación
'activo'      → Aprobado por admin, puede publicar
'suspendido'  → Temporalmente bloqueado
'rechazado'   → Solicitud rechazada permanentemente
```

#### Diferencia entre prepare() y query()
```php
// query() - Para consultas sin parámetros
$result = $conn->query("SELECT * FROM categorias");

// prepare() - Para consultas con parámetros (MÁS SEGURO)
$stmt = $conn->prepare("SELECT * FROM publicadores WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
```

---

*Continuará en siguiente sección con más archivos...*
