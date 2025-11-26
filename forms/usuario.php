<?php
// ============================================================================
// 👤 GESTOR DE SESIÓN DEL USUARIO - USUARIO.PHP
// ============================================================================
// Este archivo es el "corazón" de la gestión de usuarios en el sitio público.
// Se encarga de verificar quién está visitando la página y qué permisos tiene.
//
// 🔗 INTEGRACIÓN CON OTRAS PÁGINAS:
//
// 1. EN INDEX.PHP (Página Principal):
//    - Permite mostrar el saludo personalizado: "Hola, Juan"
//    - Decide qué botones mostrar en el menú:
//      * Si NO está logueado -> Muestra "Inicia sesión" y "Crear Cuenta"
//      * Si SÍ está logueado -> Muestra "Cerrar Sesión"
//
// 2. EN PERFIL.PHP (Página de Perfil):
//    - Actúa como "guardia de seguridad". Si no hay usuario logueado,
//      redirige inmediatamente al login.
//    - Provee los datos para mostrar la foto de perfil y el correo.
//
// ============================================================================

// ============================================================================
// 📌 EXPLICACIÓN DE session_status() y session_start()
// ============================================================================
// Antes de iniciar una sesión, verificamos si ya hay una activa.
// PHP_SESSION_NONE significa que las sesiones están habilitadas pero no hay una iniciada.
// Esto evita errores de "session already started" si este archivo se incluye varias veces.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluimos la conexión a la base de datos para poder hacer consultas
require_once "conexion.php";

// ----------------------------------------------------------------------------
// 1. INICIALIZACIÓN DE VARIABLES
// ----------------------------------------------------------------------------
// Definimos estas variables por defecto para evitar errores de "undefined variable"
// si el usuario no está logueado.
$usuario_logueado = false; // Asumimos que NO está logueado al principio
$usuario = null;           // No hay datos de usuario todavía

// ----------------------------------------------------------------------------
// 2. VERIFICACIÓN DE SESIÓN ACTIVA
// ----------------------------------------------------------------------------
// isset() verifica si la variable $_SESSION['usuario_id'] existe.
// Esta variable se crea en 'inicio-sesion.php' cuando el login es exitoso.
if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    
    // Preparamos la consulta SQL (SELECT)
    // Buscamos id, nombre, correo e imagen del usuario con el ID de la sesión
    $stmt = $conexion->prepare("SELECT id, nombre, correo, imagen FROM usuarios WHERE id = ?");
    
    if ($stmt) {
        // Vinculamos el parámetro ID (es un entero "i")
        $stmt->bind_param("i", $_SESSION['usuario_id']);
        
        // Ejecutamos la consulta
        $stmt->execute();
        
        // Obtenemos el resultado
        $resultado = $stmt->get_result();
        
        // Si encontramos exactamente 1 usuario (lo normal)
        if ($resultado->num_rows === 1) {
            // fetch_assoc() convierte la fila de la BD en un array asociativo de PHP
            // $usuario['nombre'], $usuario['correo'], etc.
            $usuario = $resultado->fetch_assoc();
            
            // Ahora SÍ marcamos al usuario como logueado
            $usuario_logueado = true;
            
            // ================================================================
            // 💾 ACTUALIZAR VARIABLES DE SESIÓN
            // ================================================================
            // Actualizamos la sesión con los datos frescos de la BD.
            // Esto asegura que 'index.php' muestre el nombre y foto correctos.
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            $_SESSION['usuario_imagen'] = $usuario['imagen'];
            
            // ================================================================
            // 🕵️ VERIFICACIÓN DE ROLES (PUBLICADOR / ADMIN)
            // ================================================================
            // Aquí verificamos si este usuario normal también tiene privilegios especiales.
            // Lo hacemos buscando su correo en las tablas 'publicadores' y 'admins'.
            
            // A) ¿ES PUBLICADOR?
            // Buscamos en la tabla 'publicadores' por email y que esté 'activo'
            $stmt_pub = $conexion->prepare("SELECT id FROM publicadores WHERE email = ? AND estado = 'activo'");
            if ($stmt_pub) {
                $stmt_pub->bind_param("s", $usuario['correo']);
                $stmt_pub->execute();
                $resultado_pub = $stmt_pub->get_result();
                
                // Si encontramos una fila, es publicador
                if ($resultado_pub->num_rows > 0) {
                    $_SESSION['es_publicador'] = true;
                } else {
                    $_SESSION['es_publicador'] = false;
                }
                $stmt_pub->close(); // Cerramos esta consulta secundaria
            }
            
            // B) ¿ES ADMINISTRADOR?
            // Buscamos en la tabla 'admins' por email y que esté 'activo'
            $stmt_admin = $conexion->prepare("SELECT id FROM admins WHERE email = ? AND estado = 'activo'");
            if ($stmt_admin) {
                $stmt_admin->bind_param("s", $usuario['correo']);
                $stmt_admin->execute();
                $resultado_admin = $stmt_admin->get_result();
                
                // Si encontramos una fila, es administrador
                if ($resultado_admin->num_rows > 0) {
                    $_SESSION['es_admin'] = true;
                } else {
                    $_SESSION['es_admin'] = false;
                }
                $stmt_admin->close(); // Cerramos esta consulta secundaria
            }
            
        } else {
            // ================================================================
            // ⚠️ CASO EXTRAÑO: USUARIO NO ENCONTRADO
            // ================================================================
            // Si la sesión dice que hay un ID, pero la BD no lo encuentra
            // (ej. el usuario fue borrado manualmente de la BD).
            
            // Destruimos la sesión por seguridad
            session_destroy();
            $usuario_logueado = false;
            $usuario = null;
        }
        
        $stmt->close();
    }
}

// ----------------------------------------------------------------------------
// 3. FUNCIÓN UTILITARIA: VERIFICAR CORREO
// ----------------------------------------------------------------------------

/**
 * 📧 FUNCIÓN: correoExiste
 * Verifica si un correo electrónico ya está registrado en la base de datos.
 * Se usa principalmente en el registro para evitar duplicados.
 * 
 * @param string $correo - El email a verificar (ej: "juan@gmail.com")
 * @param object $conexion - La conexión activa a la base de datos
 * @return bool - true si existe, false si no existe
 */
function correoExiste($correo, $conexion) {
    try {
        // Preparamos la consulta SQL
        // SELECT id es más eficiente que SELECT * porque trae menos datos
        $query = "SELECT id FROM usuarios WHERE correo = ?";
        $stmt = $conexion->prepare($query);
        
        // Si la preparación falla (ej. error de sintaxis SQL), retornamos false
        if (!$stmt) {
            return false;
        }
        
        // Vinculamos el parámetro ("s" = string)
        $stmt->bind_param("s", $correo);
        
        // Ejecutamos
        $stmt->execute();
        
        // Obtenemos el resultado
        $result = $stmt->get_result();
        
        // Cerramos el statement para liberar memoria
        $stmt->close();
        
        // Si num_rows > 0, significa que encontró al menos un registro con ese correo
        return $result->num_rows > 0;
        
    } catch (Exception $e) {
        // Si hay error, lo registramos y asumimos que no existe (o devolvemos false para manejar el error)
        error_log("Error en correoExiste: " . $e->getMessage());
        return false;
    }
}
?>