<?php
ob_start(); // Iniciar buffer de salida para evitar errores de JSON
// ============================================================================
// ⚡ PROCESADOR DE INTERACCIONES - PROCESAR-INTERACCIONES.PHP
// ============================================================================
// Este archivo procesa todas las interacciones de los usuarios con las publicaciones
// mediante peticiones AJAX. Maneja comentarios, likes, guardar y reportes.
// ============================================================================

// Iniciamos la sesión para verificar que el usuario esté logueado
session_start();

// Incluimos el archivo de usuario que tiene todas las funciones
require_once "usuario.php";



// Configuramos el header para retornar JSON
header('Content-Type: application/json');

// Verificamos que el usuario esté logueado
// Si no está logueado, retornamos error y terminamos
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para realizar esta acción'
    ]);
    exit();
}

// Obtenemos el ID del usuario logueado
$usuario_id = $_SESSION['usuario_id'];

// Verificamos que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit();
}

// Obtenemos la acción que se quiere realizar
$accion = $_POST['accion'] ?? '';

// ----------------------------------------------------------------------------
// PROCESAMOS LA ACCIÓN SOLICITADA
// ----------------------------------------------------------------------------

switch ($accion) {
    
    // ========================================================================
    // ACCIÓN: AGREGAR COMENTARIO
    // ========================================================================
    case 'agregar_comentario':
        // Obtenemos los datos del comentario
        $publicacion_id = intval($_POST['publicacion_id'] ?? 0);
        $contenido = trim($_POST['contenido'] ?? '');
        
        // Validamos que los datos sean correctos
        if ($publicacion_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de publicación inválido'
            ]);
            break;
        }
        
        if (empty($contenido)) {
            echo json_encode([
                'success' => false,
                'message' => 'El comentario no puede estar vacío'
            ]);
            break;
        }
        
        if (strlen($contenido) > 500) {
            echo json_encode([
                'success' => false,
                'message' => 'El comentario no puede tener más de 500 caracteres'
            ]);
            break;
        }
        
        // Intentamos agregar el comentario
        if (agregarComentario($publicacion_id, $usuario_id, $contenido, $conexion)) {
            // Obtenemos los datos del usuario para retornarlos
            $stmt = $conexion->prepare("SELECT nombre, imagen FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $usuario_data = $stmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => 'Comentario agregado correctamente',
                'comentario' => [
                    'usuario_nombre' => $usuario_data['nombre'],
                    'usuario_imagen' => $usuario_data['imagen'],
                    'contenido' => filtrarMalasPalabras($contenido),
                    'fecha_creacion' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al agregar el comentario'
            ]);
        }
        break;

    // ========================================================================
    // ACCIÓN: ELIMINAR COMENTARIO
    // ========================================================================
    case 'eliminar_comentario':
        $comentario_id = intval($_POST['comentario_id'] ?? 0);
        
        if ($comentario_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de comentario inválido'
            ]);
            break;
        }
        
        if (eliminarComentario($comentario_id, $usuario_id, $conexion)) {
            echo json_encode([
                'success' => true,
                'message' => 'Comentario eliminado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el comentario. Verifica que sea tuyo.'
            ]);
        }
        break;
    
    // ========================================================================
    // ACCIÓN: DAR LIKE O DISLIKE
    // ========================================================================
    case 'dar_like':
        // Obtenemos los datos
        $publicacion_id = intval($_POST['publicacion_id'] ?? 0);
        $tipo = $_POST['tipo'] ?? ''; // 'like' o 'dislike'
        
        // Validamos
        if ($publicacion_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de publicación inválido'
            ]);
            break;
        }
        
        if (!in_array($tipo, ['like', 'dislike'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Tipo de voto inválido'
            ]);
            break;
        }
        
        // Procesamos el like/dislike
        if (agregarLike($publicacion_id, $usuario_id, $tipo, $conexion)) {
            // Obtenemos el conteo actualizado
            $conteo = contarLikes($publicacion_id, $conexion);
            
            echo json_encode([
                'success' => true,
                'message' => 'Voto registrado',
                'likes' => $conteo['likes'],
                'dislikes' => $conteo['dislikes']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar el voto'
            ]);
        }
        break;
    
    // ========================================================================
    // ACCIÓN: GUARDAR PARA LEER MÁS TARDE
    // ========================================================================
    case 'guardar_leer_mas_tarde':
        // Obtenemos el ID de la publicación
        $publicacion_id = intval($_POST['publicacion_id'] ?? 0);
        
        // Validamos
        if ($publicacion_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de publicación inválido'
            ]);
            break;
        }
        
        // Procesamos (funciona como toggle)
        if (guardarParaLeerMasTarde($publicacion_id, $usuario_id, $conexion)) {
            // Verificamos si quedó guardada o se quitó
            $esta_guardada = verificarSiGuardada($publicacion_id, $usuario_id, $conexion);
            
            echo json_encode([
                'success' => true,
                'message' => $esta_guardada ? 'Publicación guardada' : 'Publicación eliminada de guardados',
                'guardada' => $esta_guardada
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar la solicitud'
            ]);
        }
        break;
    
    // ========================================================================
    // ACCIÓN: CREAR REPORTE
    // ========================================================================
    case 'crear_reporte':
        // Obtenemos los datos del reporte
        $tipo = $_POST['tipo'] ?? ''; // 'publicacion' o 'comentario'
        $referencia_id = intval($_POST['referencia_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        // Validamos
        if (!in_array($tipo, ['publicacion', 'comentario'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Tipo de reporte inválido'
            ]);
            break;
        }
        
        if ($referencia_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de referencia inválido'
            ]);
            break;
        }
        
        if (empty($motivo)) {
            echo json_encode([
                'success' => false,
                'message' => 'Debes seleccionar un motivo'
            ]);
            break;
        }
        
        // Creamos el reporte
        if (crearReporte($tipo, $referencia_id, $usuario_id, $motivo, $descripcion, $conexion)) {
            
            // ENVIAR CORREO A LOS ADMINS (sin detener el proceso si falla)
            try {
                // Verificamos que la función existe antes de llamarla
                if (function_exists('obtenerCorreosAdmins')) {
                    $admins = @obtenerCorreosAdmins($conexion);
                    
                    if (!empty($admins) && is_array($admins)) {
                        require_once 'EmailHelper.php';
                        
                        $asunto = "⚠️ Nuevo Reporte: " . ucfirst($tipo);
                        
                        $mensaje_html = "Se ha recibido un nuevo reporte que requiere atención.";
                        
                        // Enviamos correo a cada admin
                        foreach ($admins as $admin) {
                            if (isset($admin['email']) && !empty($admin['email'])) {
                                EmailHelper::enviarCorreo(
                                    $admin['email'],
                                    $asunto,
                                    $mensaje_html . "<br><br><strong>Tipo:</strong> " . ucfirst($tipo) . "<br><strong>Motivo:</strong> " . htmlspecialchars($motivo) . "<br><strong>Descripción:</strong> " . htmlspecialchars($descripcion),
                                    'Ver Panel de Reportes',
                                    'http://localhost/Lab/forms/admins/gestionar-reportes.php'
                                );
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // Si falla el correo, no detenemos el proceso
                // El reporte ya fue guardado exitosamente
            }

            // Limpiamos cualquier salida previa (warnings, notices, debugs)
            ob_clean();
            
            echo json_encode([
                'success' => true,
                'message' => 'Reporte enviado correctamente. Será revisado por un administrador.'
            ]);
        } else {
            // Limpiamos cualquier salida previa
            ob_clean();
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al enviar el reporte'
            ]);
        }
        break;
    
    // ========================================================================
    // ACCIÓN DESCONOCIDA
    // ========================================================================
    default:
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
        break;
}

// Cerramos la conexión a la base de datos
$conexion->close();
