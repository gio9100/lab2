<?php
// Abrimos PHP
session_start();
// Iniciamos la sesión

// Verificamos que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    // Si no hay usuario_id en la sesión, no está logueado
    header("Location: login.php");
    // Lo mandamos al login
    exit();
    // Detenemos el código
}
require_once "conexion.php";
// Traemos la conexión a la BD

$usuario_id = $_SESSION['usuario_id'];
// Guardamos el ID del usuario en una variable

// Verificamos que se envió un archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen'])) {
    // $_SERVER['REQUEST_METHOD'] nos dice si el formulario se envió por POST
    // $_FILES es un array especial de PHP que contiene los archivos subidos
    $imagen = $_FILES['imagen'];
    // Guardamos la info del archivo en una variable
    
    // Verificamos que no haya errores en la subida
    if ($imagen['error'] === UPLOAD_ERR_OK) {
        // UPLOAD_ERR_OK es una constante de PHP que significa "sin errores" (valor 0)
        
        // Verificamos que sea una imagen válida
        $tipo = mime_content_type($imagen['tmp_name']);
        // mime_content_type() detecta el tipo real del archivo (no confía en la extensión)
        // tmp_name es donde PHP guarda temporalmente el archivo subido
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        // Array con los tipos MIME que aceptamos
        
        if (in_array($tipo, $tipos_permitidos)) {
            // in_array() busca si $tipo está dentro del array $tipos_permitidos
            
            // Verificamos tamaño (máximo 2MB)
            if ($imagen['size'] <= 2 * 1024 * 1024) {
                // size viene en bytes, entonces 2 * 1024 * 1024 = 2,097,152 bytes = 2MB
                
                // Creamos la carpeta si no existe
                if (!file_exists('../assets/img/uploads')) {
                    // file_exists() checa si existe una carpeta o archivo
                    mkdir('../assets/img/uploads', 0777, true);
                    // mkdir() crea una carpeta
                    // 0777 son los permisos (lectura, escritura, ejecución para todos)
                    // true hace que cree carpetas intermedias si no existen
                }
                
                // Generamos un nombre único para la imagen
                $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
                // pathinfo() extrae información de una ruta
                // PATHINFO_EXTENSION saca solo la extensión (jpg, png, etc)
                $nombre_imagen = 'usuario_' . $usuario_id . '_' . time() . '.' . $extension;
                // time() devuelve la fecha/hora actual en segundos (timestamp)
                // Esto hace nombres únicos como: usuario_1_1700000000.jpg
                $ruta_destino = '../assets/img/uploads/' . $nombre_imagen;
                // Ruta completa donde guardaremos la imagen
                
                // Movemos el archivo subido a su ubicación final
                if (move_uploaded_file($imagen['tmp_name'], $ruta_destino)) {
                    // move_uploaded_file() mueve el archivo temporal a la carpeta final
                    // Es más seguro que copy() porque verifica que sea un archivo subido por POST
                    
                    // Actualizamos en la base de datos
                    $stmt = $conexion->prepare("UPDATE usuarios SET imagen = ? WHERE id = ?");
                    // Preparamos la consulta
                    $stmt->bind_param("si", $nombre_imagen, $usuario_id);
                    // "si" significa: primer parámetro es string, segundo es integer
                    
                    if ($stmt->execute()) {
                        // Si se actualizó correctamente
                        
                        // Actualizamos la sesión también
                        $_SESSION['usuario_imagen'] = $nombre_imagen;
                        // Guardamos el nuevo nombre en la sesión
                        header("Location: perfil.php?success=Imagen actualizada correctamente");
                        // Redirigimos con mensaje de éxito en la URL
                        exit();
                        // Detenemos el código
                    } else {
                        // Si falló la actualización en BD
                        header("Location: perfil.php?error=Error al actualizar la base de datos");
                        exit();
                    }
                } else {
                    // Si falló al mover el archivo
                    header("Location: perfil.php?error=Error al guardar la imagen");
                    exit();
                }
            } else {
                // Si el archivo es muy grande
                header("Location: perfil.php?error=La imagen es demasiado grande (máximo 2MB)");
                exit();
            }
        } else {
            // Si el tipo de archivo no está permitido
            header("Location: perfil.php?error=Formato de imagen no permitido. Use JPG, PNG o GIF");
            exit();
        }
    } else {
        // Si hubo error al subir
        header("Location: perfil.php?error=Error al subir la imagen");
        exit();
    }
} else {
    // Si no se envió ningún archivo
    header("Location: perfil.php?error=No se seleccionó ninguna imagen");
    exit();
}
?>