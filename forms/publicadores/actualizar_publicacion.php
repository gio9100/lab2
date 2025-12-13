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
$resumen = trim($_POST['resumen'] ?? '');
$contenido = $_POST['contenido'] ?? '';
// Estado siempre es 'revision' al editar
$estado = 'revision';

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
// Validar si tiene archivo actual en BD
$check_file = $conn->prepare("SELECT archivo_url FROM publicaciones WHERE id = ?");
$check_file->bind_param("i", $publicacion_id);
$check_file->execute();
$res_file = $check_file->get_result()->fetch_assoc();
$tiene_archivo_previo = !empty($res_file['archivo_url']);
$subiendo_archivo = isset($_FILES['archivo_contenido']) && $_FILES['archivo_contenido']['error'] === UPLOAD_ERR_OK;

if ((empty($contenido) || $contenido === '<p><br></p>') && !$tiene_archivo_previo && !$subiendo_archivo) {
    $_SESSION['error'] = "El contenido es obligatorio (Texto o Archivo)";
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
// Actualizar en base de datos
// (Lógica dinámica abajo)
// Procesar archivo de contenido (PDF, Doc, Imagen) si se subió
$archivo_url = null;
$tipo_archivo = null;
$actualizar_archivo = false;

if (isset($_FILES['archivo_contenido']) && $_FILES['archivo_contenido']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../../uploads/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_name = $_FILES['archivo_contenido']['name'];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_doc_extensions = ['pdf', 'doc', 'docx'];
    $allowed_img_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    
    // Determinar tipo de archivo
    if (in_array($file_extension, $allowed_doc_extensions)) {
        $tipo_archivo = $file_extension;
    } elseif (in_array($file_extension, $allowed_img_extensions)) {
        $tipo_archivo = 'imagen_contenido';
    }
    
    if ($tipo_archivo) {
        $new_filename = 'doc_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['archivo_contenido']['tmp_name'], $upload_path)) {
            $archivo_url = $new_filename;
            $actualizar_archivo = true;
            
            // Eliminar archivo anterior si existe
            $old_file_query = $conn->prepare("SELECT archivo_url FROM publicaciones WHERE id = ?");
            $old_file_query->bind_param("i", $publicacion_id);
            $old_file_query->execute();
            $old_file_result = $old_file_query->get_result();
            $old_file_data = $old_file_result->fetch_assoc();
            
            if (!empty($old_file_data['archivo_url'])) {
                $old_file_path = '../../uploads/' . $old_file_data['archivo_url'];
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
        }
    }
}

// Construir la consulta de actualización dinámica
$query = "UPDATE publicaciones SET 
          titulo = ?,
          categoria_id = ?,
          resumen = ?,
          contenido = ?,
          estado = ?,
          fecha_actualizacion = NOW()";

$params = [$titulo, $categoria_id, $resumen, $contenido, $estado];
$types = "sisss";

if ($imagen_principal) {
    $query .= ", imagen_principal = ?";
    $params[] = $imagen_principal;
    $types .= "s";
}

if ($actualizar_archivo) {
    $query .= ", archivo_url = ?, tipo_archivo = ?";
    $params[] = $archivo_url;
    $params[] = $tipo_archivo;
    $types .= "ss";
}

$query .= " WHERE id = ? AND publicador_id = ?";
$params[] = $publicacion_id;
$params[] = $publicador_id;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
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
