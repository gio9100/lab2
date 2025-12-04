<?php
// Archivo que maneja la actualización de publicaciones existentes
// Permite al publicador editar título, contenido, imagen, etc.

session_start();
require_once 'config-publicadores.php';

// Verificar que el publicador esté logueado
if (!isset($_SESSION['publicador_id'])) {
    header('Location: login.php');
    exit();
}

$publicador_id = $_SESSION['publicador_id'];

// Verificar que se recibió el ID de la publicación
if (!isset($_POST['publicacion_id']) || empty($_POST['publicacion_id'])) {
    $_SESSION['error'] = "ID de publicación no válido";
    header('Location: mis-publicaciones.php');
    exit();
}

// intval() = convierte a número entero para seguridad
$publicacion_id = intval($_POST['publicacion_id']);

// Verificar que la publicación pertenece al publicador (seguridad)
$check = $conn->prepare("SELECT id FROM publicaciones WHERE id = ? AND publicador_id = ?");
$check->bind_param("ii", $publicacion_id, $publicador_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "No tienes permiso para editar esta publicación";
    header('Location: mis-publicaciones.php');
    exit();
}

// Obtener datos del formulario
// trim() = quita espacios al inicio y final
$titulo = trim($_POST['titulo'] ?? '');
$categoria_id = intval($_POST['categoria_id'] ?? 0);
$tipo = $_POST['tipo'] ?? 'articulo';
$resumen = trim($_POST['resumen'] ?? '');
$meta_descripcion = trim($_POST['meta_descripcion'] ?? '');
$contenido = $_POST['contenido'] ?? '';
$tags = trim($_POST['tags'] ?? '');
$estado = $_POST['estado'] ?? 'borrador';
$fecha_publicacion = !empty($_POST['fecha_publicacion']) ? $_POST['fecha_publicacion'] : null;

// Validar campos obligatorios
if (empty($titulo)) {
    $_SESSION['error'] = "El título es obligatorio";
    header("Location: editar_publicacion.php?id=$publicacion_id");
    exit();
}

if ($categoria_id <= 0) {
    $_SESSION['error'] = "Debes seleccionar una categoría";
    header("Location: editar_publicacion.php?id=$publicacion_id");
    exit();
}

// '<p><br></p>' = contenido vacío del editor Quill
if (empty($contenido) || $contenido === '<p><br></p>') {
    $_SESSION['error'] = "El contenido es obligatorio";
    header("Location: editar_publicacion.php?id=$publicacion_id");
    exit();
}

// Procesar nueva imagen principal si se subió
$imagen_principal = null;
if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $filename = $_FILES['imagen_principal']['name'];
    // pathinfo() = extrae información de la ruta
    // strtolower() = convierte a minúsculas
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        // uniqid() = genera ID único
        // time() = timestamp actual
        $newname = uniqid() . '_' . time() . '.' . $ext;
        $upload_path = '../../uploads/' . $newname;
        
        // move_uploaded_file() = mueve archivo temporal a ubicación final
        if (move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $upload_path)) {
            $imagen_principal = $newname;
            
            // Eliminar imagen anterior si existe
            $old_image_query = $conn->prepare("SELECT imagen_principal FROM publicaciones WHERE id = ?");
            $old_image_query->bind_param("i", $publicacion_id);
            $old_image_query->execute();
            $old_image_result = $old_image_query->get_result();
            $old_image_data = $old_image_result->fetch_assoc();
            
            if (!empty($old_image_data['imagen_principal'])) {
                $old_image_path = '../../uploads/' . $old_image_data['imagen_principal'];
                if (file_exists($old_image_path)) {
                    // unlink() = elimina archivo del servidor
                    unlink($old_image_path);
                }
            }
        }
    }
}

// Actualizar en base de datos
if ($imagen_principal) {
    // Si se subió nueva imagen, actualizar también el campo imagen_principal
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
    
    $stmt = $conn->prepare($query);
    // "sissssssssii" = tipos de datos para bind_param
    // s=string, i=integer
    $stmt->bind_param("sissssssssii", 
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
    // Si NO se subió nueva imagen, mantener la anterior
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
    $_SESSION['success'] = "✅ Publicación actualizada correctamente";
    header("Location: mis-publicaciones.php");
} else {
    // $conn->error = mensaje de error de MySQL
    $_SESSION['error'] = "Error al actualizar la publicación: " . $conn->error;
    header("Location: editar_publicacion.php?id=$publicacion_id");
}

$stmt->close();
$conn->close();
exit();
?>
