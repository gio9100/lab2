<?php
// Abrimos PHP
// Este archivo es para DEPURACIÓN (debugging)
// Se usa para ver qué está pasando con las sesiones

error_reporting(E_ALL);
// error_reporting(E_ALL) muestra TODOS los errores de PHP
// E_ALL es una constante que incluye todos los tipos de errores
ini_set('display_errors', 1);
// ini_set() cambia configuraciones de PHP
// 'display_errors' = 1 hace que los errores se muestren en pantalla

echo "<h1>Debug de Sesión - Versión 2</h1>";
// echo imprime texto en la página

echo "<h2>1. Variables de Sesión:</h2>";
echo "<pre>";
// <pre> es una etiqueta HTML que muestra texto formateado
print_r($_SESSION);
// print_r() imprime el contenido de un array de forma legible
// $_SESSION es el array con todas las variables de sesión
echo "</pre>";

echo "<h2>2. Intentando cargar usuario.php:</h2>";
try {
    // try-catch es para manejar errores
    // El código dentro de try se ejecuta
    // Si hay un error, se ejecuta el código de catch
    require_once "usuario.php";
    // Intentamos cargar usuario.php
    echo "✅ usuario.php cargado correctamente<br>";
} catch (Exception $e) {
    // Exception es una clase de PHP para errores
    // $e es el objeto que contiene información del error
    echo "❌ Error al cargar usuario.php: " . $e->getMessage() . "<br>";
    // getMessage() obtiene el mensaje del error
}

echo "<h2>3. Estado de usuario.php:</h2>";
echo "usuario_logueado: " . (isset($usuario_logueado) && $usuario_logueado ? 'true' : 'false') . "<br>";
// Operador ternario: condición ? si_true : si_false
echo "usuario: ";
echo "<pre>";
print_r(isset($usuario) ? $usuario : 'NULL');
// Si $usuario existe, lo imprimimos, si no, imprimimos 'NULL'
echo "</pre>";

echo "<h2>4. Verificar conexión a BD:</h2>";
if (isset($conexion)) {
    // Si la variable $conexion existe
    echo "✅ Conexión a BD disponible<br>";
    echo "Tipo de conexión: " . get_class($conexion) . "<br>";
    // get_class() devuelve el nombre de la clase de un objeto
    // En este caso debería devolver "mysqli"
    
    // Intentamos hacer una consulta manual
    echo "<h3>Probando consulta manual:</h3>";
    try {
        $stmt = $conexion->prepare("SELECT id, nombre, correo, imagen FROM usuarios WHERE id = ?");
        // Preparamos una consulta
        if ($stmt) {
            // Si se preparó correctamente
            $usuario_id = $_SESSION['usuario_id'];
            $stmt->bind_param("i", $usuario_id);
            // "i" significa integer (número entero)
            $stmt->execute();
            // Ejecutamos la consulta
            $resultado = $stmt->get_result();
            // Obtenemos el resultado
            echo "Número de filas encontradas: " . $resultado->num_rows . "<br>";
            // num_rows es la cantidad de filas que devolvió la consulta
            if ($resultado->num_rows > 0) {
                // Si encontró al menos una fila
                $datos = $resultado->fetch_assoc();
                // fetch_assoc() convierte la fila en un array asociativo
                echo "Datos del usuario:<br>";
                echo "<pre>";
                print_r($datos);
                // Imprimimos los datos del usuario
                echo "</pre>";
            }
        } else {
            // Si no se pudo preparar la consulta
            echo "❌ Error al preparar la consulta: " . $conexion->error . "<br>";
            // $conexion->error contiene el mensaje de error de MySQL
        }
    } catch (Exception $e) {
        // Si hubo un error
        echo "❌ Error en consulta: " . $e->getMessage() . "<br>";
    }
} else {
    // Si la variable $conexion no existe
    echo "❌ Conexión a BD NO disponible<br>";
}

echo "<h2>5. Información de Sesión PHP:</h2>";
echo "Session ID: " . session_id() . "<br>";
// session_id() devuelve el ID único de la sesión actual
// Es un código largo como "abc123def456"
echo "Session Status: " . session_status() . "<br>";
// session_status() devuelve un número que indica el estado de las sesiones
// 0 = deshabilitadas, 1 = habilitadas pero no iniciadas, 2 = iniciadas
echo "Session Name: " . session_name() . "<br>";
// session_name() devuelve el nombre de la sesión (normalmente "PHPSESSID")
?>
<!-- Cerramos PHP -->
