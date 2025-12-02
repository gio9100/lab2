<?php
// ============================================================================
// 🔧 ARCHIVO DE CONFIGURACIÓN - CONFIG-USUARIOS.PHP
// ============================================================================
// Este archivo es la configuración central para el sistema de usuarios normales.
// Contiene la conexión a la base de datos y funciones reutilizables.
//
// PROPÓSITO:
// - Gestión de usuarios normales (no admins ni publicadores)
// - Autenticación de usuarios
// - Verificación de roles
// - Funciones utilitarias
//
// ARCHIVOS QUE LO USAN:
// - inicio-sesion.php (login de usuarios)
// - registro.php (registro de usuarios)
// - perfil.php (perfil de usuario)
// - usuario.php (gestor de sesión)
// ============================================================================

// ----------------------------------------------------------------------------
// 1. CONFIGURACIÓN DE LA BASE DE DATOS
// ----------------------------------------------------------------------------
$servername = "localhost";  // Servidor de base de datos
$username = "root";         // Usuario de MySQL
$password = "";             // Contraseña de MySQL (vacía en XAMPP por defecto)
$dbname = "lab_exp_db";     // Nombre de la base de datos

// ============================================================================
// 📌 EXPLICACIÓN DE new mysqli()
// ============================================================================
// mysqli = MySQL Improved (versión mejorada de MySQL)
// Crea una conexión a la base de datos MySQL
// Parámetros: servidor, usuario, contraseña, nombre de BD
$conexion = new mysqli($servername, $username, $password, $dbname);

// ============================================================================
// 📌 EXPLICACIÓN DE connect_error
// ============================================================================
// Verifica si hubo un error al conectar
// Si hay error, detiene el script y muestra el mensaje
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// ============================================================================
// 📌 EXPLICACIÓN DE set_charset()
// ============================================================================
// Configura el conjunto de caracteres a UTF-8
// Esto permite usar acentos, ñ, emojis, etc.
$conexion->set_charset("utf8mb4");

// ----------------------------------------------------------------------------
// 2. CONFIGURACIÓN DE ZONA HORARIA
// ----------------------------------------------------------------------------
// Configura la zona horaria para México
// Todas las funciones de fecha/hora usarán esta zona
date_default_timezone_set('America/Mexico_City');

// ============================================================================
// 📌 EXPLICACIÓN DE session_status()
// ============================================================================
// Verifica si ya hay una sesión iniciada
// PHP_SESSION_NONE = no hay sesión activa
// Esto evita el error "session already started"
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// 🛠️ FUNCIONES REUTILIZABLES
// ============================================================================

/**
 * 🔓 FUNCIÓN: loginUsuario
 * Verifica las credenciales de un usuario normal
 * 
 * @param string $correo - El correo electrónico ingresado
 * @param string $password - La contraseña ingresada (texto plano)
 * @param object $conexion - La conexión a la base de datos
 * @return array|false - Devuelve los datos del usuario si es correcto, o false si falla
 */
function loginUsuario($correo, $password, $conexion) {
    // Preparamos la consulta SQL
    // Buscamos por correo (los usuarios usan correo para login, no email)
    $query = "SELECT * FROM usuarios WHERE correo = ?";
    
    // Preparar la sentencia (previene inyección SQL)
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        return false;
    }
    
    // Vincular parámetros ("s" = string)
    $stmt->bind_param("s", $correo);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Obtener resultado
    $result = $stmt->get_result();
    
    // Verificar si encontró exactamente 1 usuario
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        // ====================================================================
        // 📌 EXPLICACIÓN DE password_verify()
        // ====================================================================
        // Compara la contraseña ingresada con el hash guardado en la BD
        // Devuelve true si coinciden, false si no
        if (password_verify($password, $usuario['password'])) {
            
            // Actualizar último acceso (opcional)
            $update_query = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
            $update_stmt = $conexion->prepare($update_query);
            if ($update_stmt) {
                $update_stmt->bind_param("i", $usuario['id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            return $usuario;
        }
    }
    
    return false;
}

/**
 * ✍️ FUNCIÓN: registrarUsuario
 * Crea un nuevo usuario en la base de datos
 * 
 * @param array $datos - Array con nombre, correo, password
 * @param object $conexion - Conexión a la BD
 * @return bool - true si se insertó correctamente, false si hubo error
 */
function registrarUsuario($datos, $conexion) {
    $query = "INSERT INTO usuarios (nombre, correo, password, fecha_registro) VALUES (?, ?, ?, NOW())";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        return false;
    }
    
    // ====================================================================
    // 📌 EXPLICACIÓN DE password_hash()
    // ====================================================================
    // Crea un hash seguro de la contraseña
    // PASSWORD_DEFAULT usa bcrypt (algoritmo muy seguro)
    // NUNCA guardar contraseñas en texto plano
    $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
    
    // Vincular parámetros: 3 strings (nombre, correo, password_hash)
    $stmt->bind_param("sss", 
        $datos['nombre'],
        $datos['correo'],
        $password_hash
    );
    
    // Ejecutar y retornar resultado
    return $stmt->execute();
}

/**
 * 🔍 FUNCIÓN: correoExiste
 * Verifica si un correo ya está registrado
 * 
 * @param string $correo - El correo a verificar
 * @param object $conexion - Conexión a la BD
 * @return bool - true si ya existe, false si no
 */
function correoExiste($correo, $conexion) {
    try {
        $query = "SELECT id FROM usuarios WHERE correo = ?";
        $stmt = $conexion->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        // Si num_rows > 0, el correo ya existe
        return $result->num_rows > 0;
        
    } catch (Exception $e) {
        error_log("Error en correoExiste: " . $e->getMessage());
        return false;
    }
}

/**
 * 👤 FUNCIÓN: obtenerUsuarioPorId
 * Obtiene los datos de un usuario por su ID
 * 
 * @param int $usuario_id - ID del usuario
 * @param object $conexion - Conexión a la BD
 * @return array|null - Datos del usuario o null si no existe
 */
function obtenerUsuarioPorId($usuario_id, $conexion) {
    $query = "SELECT id, nombre, correo, imagen, fecha_registro FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * 🔐 FUNCIÓN: estaLogueado
 * Verifica si hay un usuario logueado
 * 
 * @return bool - true si está logueado, false si no
 */
function estaLogueado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * 🚪 FUNCIÓN: requerirLogin
 * Redirige al login si el usuario no está autenticado
 * Se usa al principio de páginas protegidas
 */
function requerirLogin() {
    if (!estaLogueado()) {
        header('Location: ../forms/inicio-sesion.php');
        exit();
    }
}

/**
 * 🕵️ FUNCIÓN: verificarRoles
 * Verifica si el usuario tiene roles especiales (publicador/admin)
 * 
 * @param string $correo - Correo del usuario
 * @param object $conexion - Conexión a la BD
 * @return array - Array con es_publicador y es_admin
 */
function verificarRoles($correo, $conexion) {
    $roles = [
        'es_publicador' => false,
        'es_admin' => false
    ];
    
    // Verificar si es publicador
    $stmt_pub = $conexion->prepare("SELECT id FROM publicadores WHERE email = ? AND estado = 'activo'");
    if ($stmt_pub) {
        $stmt_pub->bind_param("s", $correo);
        $stmt_pub->execute();
        $resultado_pub = $stmt_pub->get_result();
        $roles['es_publicador'] = ($resultado_pub->num_rows > 0);
        $stmt_pub->close();
    }
    
    // Verificar si es administrador
    $stmt_admin = $conexion->prepare("SELECT id FROM admins WHERE email = ? AND estado = 'activo'");
    if ($stmt_admin) {
        $stmt_admin->bind_param("s", $correo);
        $stmt_admin->execute();
        $resultado_admin = $stmt_admin->get_result();
        $roles['es_admin'] = ($resultado_admin->num_rows > 0);
        $stmt_admin->close();
    }
    
    return $roles;
}

/**
 * ✏️ FUNCIÓN: actualizarPerfil
 * Actualiza los datos del perfil de un usuario
 * 
 * @param int $usuario_id - ID del usuario
 * @param array $datos - Datos a actualizar
 * @param object $conexion - Conexión a la BD
 * @return bool - true si se actualizó, false si hubo error
 */
function actualizarPerfil($usuario_id, $datos, $conexion) {
    // Si se proporciona nueva contraseña, actualizar todo
    if (isset($datos['password']) && !empty($datos['password'])) {
        $query = "UPDATE usuarios SET nombre = ?, correo = ?, password = ? WHERE id = ?";
        $stmt = $conexion->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("sssi", 
            $datos['nombre'],
            $datos['correo'],
            $password_hash,
            $usuario_id
        );
    } else {
        // Si no hay nueva contraseña, solo actualizar nombre y correo
        $query = "UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?";
        $stmt = $conexion->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("ssi", 
            $datos['nombre'],
            $datos['correo'],
            $usuario_id
        );
    }
    
    return $stmt->execute();
}

// ============================================================================
// ⚙️ CONFIGURACIÓN DE ERRORES
// ============================================================================
// Mostrar errores solo en desarrollo
// En producción, cambiar a 0
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
