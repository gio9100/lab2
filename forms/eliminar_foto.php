<?php
// Abrimos PHP
session_start();
// Iniciamos la sesión
require_once "conexion.php";
// Traemos la conexión a la BD

// Verificamos si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    // Si no hay usuario_id en la sesión
    header("Location: login.php");
    // Lo mandamos al login
    exit();
    // Detenemos todo
}

$usuario_id = $_SESSION['usuario_id'];
// Guardamos el ID del usuario

// Obtenemos la imagen actual del usuario de la BD
$stmt = $conexion->prepare("SELECT imagen FROM usuarios WHERE id = ?");
// Preparamos la consulta
$stmt->bind_param("i", $usuario_id);
// "i" significa que el parámetro es un integer (número entero)
$stmt->execute();
// Ejecutamos la consulta
$resultado = $stmt->get_result();
// Obtenemos el resultado
$usuario = $resultado->fetch_assoc();
// fetch_assoc() convierte la fila en un array asociativo
// Ahora podemos usar $usuario['imagen']

// Si tiene una imagen y no es la default, la eliminamos del servidor
if (!empty($usuario['imagen']) && $usuario['imagen'] != 'default.png') {
    // empty() checa si una variable está vacía
    // && significa "Y" (ambas condiciones deben cumplirse)
    $ruta_imagen = "../assets/img/uploads/" . $usuario['imagen'];
    // Construimos la ruta completa del archivo
    if (file_exists($ruta_imagen)) {
        // Verificamos que el archivo exista
        unlink($ruta_imagen);
        // unlink() elimina un archivo del servidor
        // Es como "borrar" pero en PHP se llama unlink
    }
}

// Actualizamos la base de datos para poner la imagen default
$stmt = $conexion->prepare("UPDATE usuarios SET imagen = 'default.png' WHERE id = ?");
// Preparamos el UPDATE
$stmt->bind_param("i", $usuario_id);
// Pasamos el ID del usuario

if ($stmt->execute()) {
    // Si se actualizó correctamente
    
    // Actualizamos la sesión también
    $_SESSION['usuario_imagen'] = 'default.png';
    // Cambiamos la imagen en la sesión
    header("Location: perfil.php?success=Foto eliminada correctamente");
    // Redirigimos con mensaje de éxito
} else {
    // Si falló la actualización
    header("Location: perfil.php?error=Error al eliminar la foto");
    // Redirigimos con mensaje de error
}
exit();
// Detenemos el código
?>