<?php
// Abrimos PHP
session_start();
// Iniciamos la sesión
require_once 'config-publicadores.php';
// Traemos la configuración

// Verificamos que el publicador esté logueado
if (!isset($_SESSION['publicador_id'])) {
    // Si no hay sesión
    header('Location: login.php');
    // Lo mandamos al login
    exit();
    // Detenemos el código
}

$publicador_id = $_SESSION['publicador_id'];
// Guardamos el ID del publicador

// Verificamos que se recibió el ID de la publicación
if (!isset($_POST['publicacion_id']) || empty($_POST['publicacion_id'])) {
    // Si no viene el ID o está vacío
    $_SESSION['error'] = "ID de publicación no válido";
    // Guardamos mensaje de error en la sesión
    header('Location: mis-publicaciones.php');
    // Redirigimos a mis publicaciones
    exit();
}

$publicacion_id = intval($_POST['publicacion_id']);
// intval() convierte a número entero (seguridad)

// Verificamos que la publicación pertenece al publicador
$check = $conn->prepare("SELECT id FROM publicaciones WHERE id = ? AND publicador_id = ?");
// Preparamos consulta para verificar propiedad
$check->bind_param("ii", $publicacion_id, $publicador_id);
// "ii" significa dos integers (números enteros)
$check->execute();
// Ejecutamos
$result = $check->get_result();
// Obtenemos resultado

if ($result->num_rows === 0) {
    // Si no encontró ninguna fila, no es dueño de la publicación
    $_SESSION['error'] = "No tienes permiso para editar esta publicación";
    header('Location: mis-publicaciones.php');
    exit();
}

// Recogemos los datos del formulario
$titulo = trim($_POST['titulo'] ?? '');
// trim() quita espacios al inicio y final
$categoria_id = intval($_POST['categoria_id'] ?? 0);
$tipo = $_POST['tipo'] ?? 'articulo';
$resumen = trim($_POST['resumen'] ?? '');
$meta_descripcion = trim($_POST['meta_descripcion'] ?? '');
$contenido = $_POST['contenido'] ?? '';
$tags = trim($_POST['tags'] ?? '');
$estado = $_POST['estado'] ?? 'borrador';
$fecha_publicacion = !empty($_POST['fecha_publicacion']) ? $_POST['fecha_publicacion'] : null;
// Si viene vacía, la ponemos en null

// Validaciones básicas
if (empty($titulo)) {
    // Si el título está vacío
    $_SESSION['error'] = "El título es obligatorio";
    header("Location: editar_publicacion.php?id=$publicacion_id");
    exit();
}

if ($categoria_id <= 0) {
    // Si no seleccionó categoría
    $_SESSION['error'] = "Debes seleccionar una categoría";
    header("Location: editar_publicacion.php?id=$publicacion_id");
    exit();
}

if (empty($contenido) || $contenido === '<p><br></p>') {
    // Si el contenido está vacío o solo tiene un párrafo vacío
    // '<p><br></p>' es lo que Quill (el editor) pone cuando está vacío
    $_SESSION['error'] = "El contenido es obligatorio";
    header("Location: editar_publicacion.php?id=$publicacion_id");
    exit();
}

// Manejamos la imagen principal (si se subió una nueva)
$imagen_principal = null;
if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
    // Si se subió una imagen nueva
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    // Extensiones permitidas
    $filename = $_FILES['imagen_principal']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    // strtolower() convierte a minúsculas
    // pathinfo() extrae la extensión
    
    if (in_array($ext, $allowed)) {
        // Si la extensión está permitida
        $newname = uniqid() . '_' . time() . '.' . $ext;
        // uniqid() genera un ID único
        // time() agrega timestamp
        // Resultado: abc123_1700000000.jpg
        $upload_path = '../../uploads/' . $newname;
        // Ruta donde guardaremos
        
        if (move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $upload_path)) {
            // Si se movió correctamente
            $imagen_principal = $newname;
            // Guardamos el nombre
            
            // Eliminamos la imagen anterior si existe
            $old_image_query = $conn->prepare("SELECT imagen_principal FROM publicaciones WHERE id = ?");
            $old_image_query->bind_param("i", $publicacion_id);
            $old_image_query->execute();
            $old_image_result = $old_image_query->get_result();
            $old_image_data = $old_image_result->fetch_assoc();
            // Obtenemos la imagen anterior
            
            if (!empty($old_image_data['imagen_principal'])) {
                // Si había una imagen anterior
                $old_image_path = '../../uploads/' . $old_image_data['imagen_principal'];
                if (file_exists($old_image_path)) {
                    // Si el archivo existe
                    unlink($old_image_path);
                    // unlink() elimina el archivo
                }
            }
        }
    }
}

// Actualizamos la publicación en la base de datos
if ($imagen_principal) {
    // Si se subió una nueva imagen
    $query = "UPDATE publicaciones SET 
              titulo = ?,
              categoria_id = ?,
              tipo = ?,
              resumen = ?,
              meta_descripcion = ?,
              contenido = ?,
              tags = ?,
              estado = ?,
              fecha_publicacion = ?,
              imagen_principal = ?,
              fecha_actualizacion = NOW()
              WHERE id = ? AND publicador_id = ?";
    // NOW() es una función de MySQL que devuelve la fecha/hora actual
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sissssssssii", 
        // "sissssssssii" = string, int, string, string, string, string, string, string, string, string, int, int
        $titulo, 
        $categoria_id, 
        $tipo, 
        $resumen, 
        $meta_descripcion, 
        $contenido, 
        $tags, 
        $estado, 
        $fecha_publicacion,
        $imagen_principal,
        $publicacion_id,
        $publicador_id
    );
} else {
    // Si NO se subió una nueva imagen, mantenemos la anterior
    $query = "UPDATE publicaciones SET 
              titulo = ?,
              categoria_id = ?,
              tipo = ?,
              resumen = ?,
              meta_descripcion = ?,
              contenido = ?,
              tags = ?,
              estado = ?,
              fecha_publicacion = ?,
              fecha_actualizacion = NOW()
              WHERE id = ? AND publicador_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sississsii", 
        // "sississsii" = string, int, string, int, string, string, string, string, int, int
        $titulo, 
        $categoria_id, 
        $tipo, 
        $resumen, 
        $meta_descripcion, 
        $contenido, 
        $tags, 
        $estado, 
        $fecha_publicacion,
        $publicacion_id,
        $publicador_id
    );
}

if ($stmt->execute()) {
    // Si se actualizó correctamente
    $_SESSION['success'] = "✅ Publicación actualizada correctamente";
    header("Location: mis-publicaciones.php");
} else {
    // Si hubo error
    $_SESSION['error'] = "Error al actualizar la publicación: " . $conn->error;
    // $conn->error contiene el mensaje de error de MySQL
    header("Location: editar_publicacion.php?id=$publicacion_id");
}

$stmt->close();
// Cerramos el statement
$conn->close();
// Cerramos la conexión
exit();
?>
