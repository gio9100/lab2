<?php
// Archivo que procesa todas las interacciones del usuario con publicaciones
// Maneja: likes, comentarios, reportes, guardar para leer después

// ob_start() = inicia buffer de salida (guarda todo en memoria antes de enviarlo)
ob_start();
// session_start() = inicia o continúa la sesión del usuario
session_start();
// require_once = incluye archivo solo una vez (evita duplicados)
require_once "usuario.php";

// Limpiar cualquier salida previa incluyendo BOM (Byte Order Mark)
// ob_end_clean() = descarta el buffer actual
ob_end_clean();
// Iniciamos nuevo buffer limpio
ob_start();

// header() = envía encabezado HTTP al navegador
// Indica que la respuesta será JSON con codificación UTF-8
header('Content-Type: application/json; charset=utf-8');

// Verificar que el usuario esté logueado
// isset() = verifica si una variable existe y no es null
if (!isset($_SESSION['usuario_id'])) {
    // json_encode() = convierte array PHP a formato JSON
    // JSON_UNESCAPED_UNICODE = muestra acentos correctamente sin \u00e1
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión'], JSON_UNESCAPED_UNICODE);
    // exit() = detiene la ejecución del script
    exit();
}

// Verificar que la petición sea POST (no GET)
// $_SERVER['REQUEST_METHOD'] = método HTTP usado (GET, POST, etc)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Obtener datos del usuario y la acción solicitada
$usuario_id = $_SESSION['usuario_id'];
// ?? '' = operador null coalescing, si no existe usa ''
$accion = $_POST['accion'] ?? '';

// switch = evalúa la variable $accion y ejecuta el caso correspondiente
switch ($accion) {
    case 'agregar_comentario':
        // intval() = convierte a número entero (seguridad contra inyección SQL)
        $publicacion_id = intval($_POST['publicacion_id'] ?? 0);
        // trim() = quita espacios al inicio y final del texto
        $contenido = trim($_POST['contenido'] ?? '');
        
        // Validar que los datos sean correctos
        // empty() = verifica si una variable está vacía
        if ($publicacion_id <= 0 || empty($contenido)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos'], JSON_UNESCAPED_UNICODE);
            break; // break = sale del switch
        }
        
        // strlen() = cuenta la cantidad de caracteres en un texto
        if (strlen($contenido) > 500) {
            echo json_encode(['success' => false, 'message' => 'Máximo 500 caracteres'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        // Intentar agregar el comentario a la base de datos
        if (agregarComentario($publicacion_id, $usuario_id, $contenido, $conexion)) {
            // Obtener datos del usuario para mostrar en el comentario
            // prepare() = prepara consulta SQL (previene inyección SQL)
            $stmt = $conexion->prepare("SELECT nombre, imagen FROM usuarios WHERE id = ?");
            // bind_param() = vincula variables a los ? de la consulta
            // "i" = tipo integer (entero)
            $stmt->bind_param("i", $usuario_id);
            // execute() = ejecuta la consulta preparada
            $stmt->execute();
            // get_result() = obtiene los resultados
            // fetch_assoc() = convierte resultado en array asociativo
            $usuario_data = $stmt->get_result()->fetch_assoc();
            
            // Enviamos respuesta JSON exitosa con los datos del comentario
            // El navegador recibirá esto y lo mostrará sin recargar la página
            echo json_encode([
                'success' => true,
                'message' => 'Comentario agregado',
                'comentario' => [
                    'usuario_nombre' => $usuario_data['nombre'],
                    'usuario_imagen' => $usuario_data['imagen'],
                    'contenido' => filtrarMalasPalabras($contenido),
                    // date() = obtiene fecha/hora actual en formato especificado
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al agregar comentario'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'eliminar_comentario':
        $comentario_id = intval($_POST['comentario_id'] ?? 0);
        
        if ($comentario_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        if (eliminarComentario($comentario_id, $usuario_id, $conexion)) {
            echo json_encode(['success' => true, 'message' => 'Comentario eliminado'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'dar_like':
        $publicacion_id = intval($_POST['publicacion_id'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        
        // in_array() = verifica si un valor está en un array
        if ($publicacion_id <= 0 || !in_array($tipo, ['like', 'dislike'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        if (agregarLike($publicacion_id, $usuario_id, $tipo, $conexion)) {
            $conteo = contarLikes($publicacion_id, $conexion);
            echo json_encode([
                'success' => true,
                'message' => 'Voto registrado',
                'likes' => $conteo['likes'],
                'dislikes' => $conteo['dislikes']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al votar'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'guardar_leer_mas_tarde':
        $publicacion_id = intval($_POST['publicacion_id'] ?? 0);
        
        if ($publicacion_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        if (guardarParaLeerMasTarde($publicacion_id, $usuario_id, $conexion)) {
            $esta_guardada = verificarSiGuardada($publicacion_id, $usuario_id, $conexion);
            // Operador ternario: condición ? si_true : si_false
            echo json_encode([
                'success' => true,
                'message' => $esta_guardada ? 'Publicación guardada' : 'Publicación eliminada',
                'guardada' => $esta_guardada
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'crear_reporte':
        $tipo = $_POST['tipo'] ?? '';
        $referencia_id = intval($_POST['referencia_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        if (!in_array($tipo, ['publicacion', 'comentario']) || $referencia_id <= 0 || empty($motivo)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        if (crearReporte($tipo, $referencia_id, $usuario_id, $motivo, $descripcion, $conexion)) {
            // try-catch = manejo de errores, intenta ejecutar código
            try {
                // function_exists() = verifica si una función existe
                if (function_exists('obtenerCorreosAdmins')) {
                    $admins = obtenerCorreosAdmins($conexion);
                    if (!empty($admins)) {
                        require_once 'EmailHelper.php';
                        $asunto = "⚠️ Nuevo Reporte: " . ucfirst($tipo);
                        $mensaje = "Se ha recibido un nuevo reporte.";
                        // foreach = recorre cada elemento del array
                        foreach ($admins as $admin) {
                            if (isset($admin['email'])) {
                                EmailHelper::enviarCorreo(
                                    $admin['email'],
                                    $asunto,
                                    $mensaje,
                                    'Ver Reportes',
                                    'http://localhost/Lab/forms/admins/gestionar-reportes.php'
                                );
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // catch = captura errores si el try falla
                // Continuar aunque falle el correo
            }
            
            echo json_encode(['success' => true, 'message' => 'Reporte enviado'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al reportar'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    default:
        // default = se ejecuta si ningún case coincide
        echo json_encode(['success' => false, 'message' => 'Acción no válida'], JSON_UNESCAPED_UNICODE);
        break;
}

// close() = cierra la conexión a la base de datos
$conexion->close();
