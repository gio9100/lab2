<?php
// ============================================================================
// 🔧 ARCHIVO DE CONFIGURACIÓN PRINCIPAL - CONFIG-ADMIN.PHP
// ============================================================================
// Este archivo es el "cerebro" del sistema de administración.
// Contiene la configuración de la base de datos y funciones reutilizables.
//
// FUNCIONES INCLUIDAS:
// - Conexión a la base de datos
// - Login de administradores
// - Registro de administradores
// - Estadísticas del sistema
// - Gestión de estados de publicadores (aprobar, rechazar, suspender)
// ============================================================================

// ----------------------------------------------------------------------------
// 1. CONFIGURACIÓN DE LA BASE DE DATOS
// ----------------------------------------------------------------------------
$servername = "localhost";  // Servidor de base de datos (generalmente localhost en desarrollo)
$username = "root";         // Usuario de MySQL (root es el predeterminado en XAMPP)
$password = "";             // Contraseña de MySQL (vacía por defecto en XAMPP)
$dbname = "lab_exp_db";     // Nombre de la base de datos que vamos a usar

// ============================================================================
// 📌 EXPLICACIÓN DE new mysqli()
// ============================================================================
// new mysqli() es una función constructora que crea un objeto de conexión 
// a la base de datos MySQL.
//
// PARÁMETROS:
// 1. $servername: La dirección del servidor (ej. 'localhost').
// 2. $username: El nombre de usuario para acceder a la BD.
// 3. $password: La contraseña del usuario.
// 4. $dbname: El nombre de la base de datos a la que queremos conectarnos.
//
// RETORNO:
// Devuelve un objeto que representa la conexión activa.
$conn = new mysqli($servername, $username, $password, $dbname);

// ============================================================================
// 📌 EXPLICACIÓN DE $conn->connect_error
// ============================================================================
// Esta propiedad del objeto $conn contiene una descripción del último error de conexión.
// Si la conexión fue exitosa, esta propiedad será NULL (vacía).
// Si hubo un error (ej. contraseña incorrecta), tendrá un mensaje de texto.
if ($conn->connect_error) {
    // ========================================================================
    // 📌 EXPLICACIÓN DE die()
    // ========================================================================
    // die() es una función que detiene la ejecución del script PHP inmediatamente.
    // Imprime el mensaje que le pasamos entre paréntesis y luego "mata" el proceso.
    // Es útil para errores críticos donde no se puede continuar sin la base de datos.
    die("Error de conexión: " . $conn->connect_error);
}

// ============================================================================
// 📌 EXPLICACIÓN DE set_charset("utf8mb4")
// ============================================================================
// Este método establece el conjunto de caracteres para la conexión.
// "utf8mb4" es la codificación recomendada porque soporta TODOS los caracteres
// Unicode, incluyendo emojis y símbolos especiales, que utf8 normal a veces no soporta.
$conn->set_charset("utf8mb4");

// ============================================================================
// 📌 EXPLICACIÓN DE date_default_timezone_set()
// ============================================================================
// Configura la zona horaria predeterminada que usarán todas las funciones de fecha/hora
// en este script (como date() o time()).
// Esto asegura que cuando guardemos fechas, correspondan a la hora de México.
date_default_timezone_set('America/Mexico_City');

// ============================================================================
// 📌 EXPLICACIÓN DE define()
// ============================================================================
// define() crea una CONSTANTE global.
// A diferencia de las variables ($variable), las constantes:
// 1. No llevan el signo $ al inicio.
// 2. No pueden cambiar su valor una vez definidas.
// 3. Son accesibles desde cualquier parte del código (ámbito global).
// Se usan para valores fijos de configuración como claves maestras.
define('CLAVE_MAESTRA_ADMIN', 'labexplorer2025');

// ============================================================================
// 🛠️ FUNCIONES REUTILIZABLES
// ============================================================================

/**
 * 🔓 FUNCIÓN: loginAdmin
 * Verifica las credenciales de un administrador.
 * 
 * @param string $email - El correo electrónico ingresado.
 * @param string $password - La contraseña ingresada (texto plano).
 * @param object $conn - La conexión a la base de datos.
 * @return array|false - Devuelve los datos del admin si es correcto, o false si falla.
 */
function loginAdmin($email, $password, $conn) {
    // Preparamos la consulta SQL. Usamos ? como marcadores de posición.
    // Esto es vital para prevenir Inyección SQL.
    $query = "SELECT * FROM admins WHERE email = ? AND estado = 'activo'";
    
    // ========================================================================
    // 📌 EXPLICACIÓN DE prepare()
    // ========================================================================
    // prepare() prepara la sentencia SQL para su ejecución.
    // El servidor de base de datos analiza, compila y optimiza el plan de consulta.
    // Esto hace que la consulta sea más rápida y segura.
    $stmt = $conn->prepare($query);
    
    // ========================================================================
    // 📌 EXPLICACIÓN DE bind_param()
    // ========================================================================
    // bind_param() vincula las variables de PHP a los marcadores ? de la consulta.
    // El primer argumento string especifica los tipos de datos:
    // "s" = string (cadena de texto)
    // "i" = integer (número entero)
    // "d" = double (número decimal)
    // "b" = blob (datos binarios)
    // Aquí usamos "s" porque el email es un texto.
    $stmt->bind_param("s", $email);
    
    // Ejecutamos la consulta preparada con los valores vinculados.
    $stmt->execute();
    
    // ========================================================================
    // 📌 EXPLICACIÓN DE get_result()
    // ========================================================================
    // Obtiene el conjunto de resultados de la sentencia preparada.
    // Devuelve un objeto mysqli_result que podemos usar para obtener las filas.
    $result = $stmt->get_result();
    
    // Verificamos si se encontró exactamente un usuario (num_rows === 1)
    if ($result->num_rows === 1) {
        // ====================================================================
        // 📌 EXPLICACIÓN DE fetch_assoc()
        // ====================================================================
        // Obtiene una fila de resultados como un array asociativo.
        // Las claves del array serán los nombres de las columnas de la tabla (id, nombre, etc).
        $admin = $result->fetch_assoc();
        
        // ====================================================================
        // 📌 EXPLICACIÓN DE password_verify()
        // ====================================================================
        // Comprueba si la contraseña ingresada (texto plano) coincide con el hash guardado en la BD.
        // PHP maneja automáticamente la sal (salt) y el algoritmo usado.
        // Devuelve true si coinciden, false si no.
        if (password_verify($password, $admin['password'])) {
            // Si la contraseña es correcta, actualizamos la fecha de último acceso
            $update_query = "UPDATE admins SET ultimo_acceso = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            
            // Vinculamos el ID como entero ("i")
            $update_stmt->bind_param("i", $admin['id']);
            $update_stmt->execute();
            
            // Devolvemos el array con los datos del administrador
            return $admin;
        }
    }
    
    // Si no se encontró el email o la contraseña no coincide, devolvemos false
    return false;
}

/**
 * ✍️ FUNCIÓN: registrarAdmin
 * Crea un nuevo administrador en la base de datos.
 * 
 * @param array $datos - Array con nombre, email, password, nivel.
 * @param object $conn - Conexión a la BD.
 * @return bool - true si se insertó correctamente, false si hubo error.
 */
function registrarAdmin($datos, $conn) {
    $query = "INSERT INTO admins (nombre, email, password, nivel) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    // ========================================================================
    // 📌 EXPLICACIÓN DE password_hash()
    // ========================================================================
    // Crea un hash seguro de la contraseña.
    // PASSWORD_DEFAULT usa el algoritmo más fuerte disponible en la versión actual de PHP (actualmente bcrypt).
    // Esto es crucial: NUNCA guardar contraseñas tal cual en la base de datos.
    $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
    
    // Vinculamos 4 strings ("ssss"): nombre, email, password_hash, nivel
    $stmt->bind_param("ssss", 
        $datos['nombre'],
        $datos['email'],
        $password_hash,
        $datos['nivel']
    );
    
    // execute() devuelve true si la inserción fue exitosa
    return $stmt->execute();
}

/**
 * 🔍 FUNCIÓN: adminExiste
 * Verifica si un email ya está registrado como administrador.
 * 
 * @return bool - true si ya existe, false si no.
 */
function adminExiste($email, $conn) {
    $query = "SELECT id FROM admins WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Si num_rows es mayor a 0, significa que encontró al menos un registro
    return $result->num_rows > 0;
}

/**
 * 📊 FUNCIÓN: obtenerEstadisticasAdmin
 * Obtiene conteos generales para el dashboard.
 * 
 * @return array - Array asociativo con los conteos.
 */
function obtenerEstadisticasAdmin($conn) {
    // Inicializamos el array con valores en 0 por si fallan las consultas
    $stats = [
        'total_usuarios' => 0,
        'total_publicadores' => 0,
        'publicadores_pendientes' => 0,
        'total_publicaciones' => 0,
        'total_admins' => 0
    ];
    
    // COUNT(*) cuenta el total de filas que cumplen la condición
    
    // 1. Total Usuarios
    $query = "SELECT COUNT(*) as total FROM usuarios";
    $result = $conn->query($query); // Usamos query() directo porque no hay parámetros variables
    if ($result) {
        $stats['total_usuarios'] = $result->fetch_assoc()['total'];
    }
    
    // 2. Total Publicadores
    $query = "SELECT COUNT(*) as total FROM publicadores";
    $result = $conn->query($query);
    if ($result) {
        $stats['total_publicadores'] = $result->fetch_assoc()['total'];
    }
    
    // 3. Publicadores Pendientes
    $query = "SELECT COUNT(*) as total FROM publicadores WHERE estado = 'pendiente'";
    $result = $conn->query($query);
    if ($result) {
        $stats['publicadores_pendientes'] = $result->fetch_assoc()['total'];
    }
    
    // 4. Total Publicaciones
    $query = "SELECT COUNT(*) as total FROM publicaciones";
    $result = $conn->query($query);
    if ($result) {
        $stats['total_publicaciones'] = $result->fetch_assoc()['total'];
    }
    
    // 5. Total Admins Activos
    $query = "SELECT COUNT(*) as total FROM admins WHERE estado = 'activo'";
    $result = $conn->query($query);
    if ($result) {
        $stats['total_admins'] = $result->fetch_assoc()['total'];
    }
    
    return $stats;
}

/**
 * ⏳ FUNCIÓN: obtenerPublicadoresPendientes
 * Obtiene lista de publicadores esperando aprobación.
 * 
 * @return array - Lista de publicadores (array de arrays).
 */
function obtenerPublicadoresPendientes($conn) {
    $query = "SELECT * FROM publicadores WHERE estado = 'pendiente' ORDER BY fecha_registro DESC";
    $result = $conn->query($query);
    
    if ($result) {
        // ====================================================================
        // 📌 EXPLICACIÓN DE fetch_all(MYSQLI_ASSOC)
        // ====================================================================
        // Obtiene TODAS las filas del resultado de una sola vez y las devuelve
        // como un array de arrays asociativos.
        // MYSQLI_ASSOC indica que queremos arrays asociativos (claves con nombres de columna).
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return []; // Si falla, devolvemos array vacío
}

/**
 * 👥 FUNCIÓN: obtenerTodosPublicadores
 * Obtiene todos los publicadores registrados.
 */
function obtenerTodosPublicadores($conn) {
    $query = "SELECT * FROM publicadores ORDER BY fecha_registro DESC";
    $result = $conn->query($query);
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

/**
 * 👤 FUNCIÓN: obtenerUsuariosNormales
 * Obtiene usuarios que no son admins ni publicadores.
 */
function obtenerUsuariosNormales($conn) {
    $query = "SELECT id, nombre, correo, fecha_registro FROM usuarios WHERE rol = 'usuario' OR rol IS NULL ORDER BY fecha_registro DESC";
    $result = $conn->query($query);
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

/**
 * ✅ FUNCIÓN: aprobarPublicador
 * Cambia el estado de un publicador a 'activo'.
 */
function aprobarPublicador($publicador_id, $conn) {
    // NOW() es una función de MySQL que devuelve la fecha y hora actual del servidor de BD
    $query = "UPDATE publicadores SET estado = 'activo', fecha_activacion = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $publicador_id);
    return $stmt->execute();
}

/**
 * ❌ FUNCIÓN: rechazarPublicador
 * Cambia el estado de un publicador a 'rechazado'.
 */
function rechazarPublicador($publicador_id, $motivo, $conn) {
    $query = "UPDATE publicadores SET estado = 'rechazado', motivo_suspension = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    // "si" = string (motivo) e integer (id)
    $stmt->bind_param("si", $motivo, $publicador_id);
    return $stmt->execute();
}

/**
 * ⏸️ FUNCIÓN: suspenderPublicador
 * Cambia el estado de un publicador a 'suspendido'.
 */
function suspenderPublicador($publicador_id, $motivo, $conn) {
    $query = "UPDATE publicadores SET estado = 'suspendido', motivo_suspension = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $motivo, $publicador_id);
    return $stmt->execute();
}

/**
 * ▶️ FUNCIÓN: activarPublicador
 * Reactiva un publicador suspendido.
 */
function activarPublicador($publicador_id, $conn) {
    // Al reactivar, borramos el motivo de suspensión (NULL)
    $query = "UPDATE publicadores SET estado = 'activo', motivo_suspension = NULL WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $publicador_id);
    return $stmt->execute();
}

/**
 * 🔐 FUNCIÓN: esAdministrador
 * Verifica si hay una sesión de admin activa.
 * 
 * @return bool - true si está logueado, false si no.
 */
function esAdministrador() {
    // ========================================================================
    // 📌 EXPLICACIÓN DE isset()
    // ========================================================================
    // isset() comprueba si una variable está definida y no es NULL.
    // Aquí verificamos si la variable 'admin_id' existe en la sesión ($_SESSION).
    // Si existe, significa que el usuario pasó por el login exitosamente.
    return isset($_SESSION['admin_id']);
}

/**
 * 🚪 FUNCIÓN: requerirAdmin
 * Redirige al login si el usuario no es administrador.
 * Se usa al principio de las páginas protegidas.
 */
function requerirAdmin() {
    if (!esAdministrador()) {
        // ====================================================================
        // 📌 EXPLICACIÓN DE header()
        // ====================================================================
        // header() envía un encabezado HTTP al navegador.
        // 'Location: ...' le dice al navegador que cargue otra URL.
        // IMPORTANTE: No debe haber ningún output (echo, HTML) antes de llamar a header().
        header('Location: login-admin.php');
        
        // ====================================================================
        // 📌 EXPLICACIÓN DE exit()
        // ====================================================================
        // exit() termina la ejecución del script inmediatamente.
        // Es fundamental llamarlo después de una redirección para asegurar que
        // el resto del código de la página protegida NO se ejecute.
        exit();
    }
}

// ============================================================================
// ⚙️ CONFIGURACIÓN DE ERRORES
// ============================================================================
// ============================================================================
// 📌 EXPLICACIÓN DE ini_set() y error_reporting()
// ============================================================================
// ini_set() permite modificar directivas de configuración de PHP en tiempo de ejecución.
// 'display_errors' = 1 hace que los errores se muestren en pantalla (útil para desarrollo).
// error_reporting(E_ALL) configura PHP para que notifique TODOS los errores, advertencias y avisos.
// En un entorno de producción (sitio real), esto debería desactivarse por seguridad.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
