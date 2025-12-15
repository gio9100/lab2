<?php
// Abrimos PHP
session_start();
// Iniciamos la sesión

// Incluir conexión a BD para actualizar estado
require_once '../../mensajes/db.php';
// Verificar si hay sesión de admin
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    // Actualizar ultimo_acceso a NULL para desconectar
    $stmt = $conn->prepare("UPDATE admins SET ultimo_acceso = NULL WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Destruimos todas las variables de sesión
$_SESSION = array();
// array() crea un array vacío, esto borra todas las variables de sesión

// Ahora vamos a borrar también la cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    // ini_get() lee configuraciones de PHP
    // "session.use_cookies" nos dice si PHP usa cookies para las sesiones
    
    $params = session_get_cookie_params();
    // session_get_cookie_params() obtiene los parámetros de la cookie de sesión
    // Devuelve un array con: path, domain, secure, httponly
    
    setcookie(session_name(), '', time() - 42000,
        // setcookie() crea o modifica una cookie
        // session_name() devuelve el nombre de la cookie de sesión (normalmente "PHPSESSID")
        // '' = valor vacío
        // time() - 42000 = fecha en el pasado (hace 42000 segundos)
        // Poner una fecha pasada hace que el navegador borre la cookie
        $params["path"], $params["domain"],
        // path y domain definen dónde es válida la cookie
        $params["secure"], $params["httponly"]
        // secure = solo HTTPS, httponly = no accesible desde JavaScript
    );
}

// Finalmente, destruimos la sesión del servidor
session_destroy();
// Esto borra el archivo de sesión del servidor

// Redirigimos al login de administración
header("Location: login-admin.php");
// Mandamos al admin de vuelta al login
exit();
// Detenemos el código
?>
