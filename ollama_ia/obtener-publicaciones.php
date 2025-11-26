<?php
// =============================================================================
// 📄 ARCHIVO: obtener-publicaciones.php
// =============================================================================
//
// 🎯 PROPÓSITO:
// Este archivo es un ENDPOINT que devuelve las publicaciones PENDIENTES de
// moderación en formato JSON. Es llamado por AJAX desde panel-moderacion.php
// para cargar la lista de publicaciones que necesitan ser revisadas.
//
// 📥 ENTRADA:
// - No recibe parámetros (solo verifica sesión de admin)
//
// 📤 SALIDA:
// - JSON con array de publicaciones pendientes
// - Cada publicación incluye: id, titulo, contenido, resumen, estado, fecha, autor
//
// 🔒 SEGURIDAD:
// - Solo administradores autenticados pueden acceder
// - Usa requerirAdmin() para verificar permisos
// =============================================================================

// -----------------------------------------------------------------------------
// PASO 1: Iniciar sesión de PHP
// -----------------------------------------------------------------------------
// session_start(): Inicia o reanuda una sesión PHP
// Esto permite acceder a $_SESSION['admin_id'] para verificar autenticación
session_start();

// -----------------------------------------------------------------------------
// PASO 2: Incluir dependencias
// -----------------------------------------------------------------------------
// config-admin.php: Contiene:
//   - Función requerirAdmin(): Verifica si hay sesión de admin activa
//   - Variable $conn: Conexión a la base de datos MySQL
//   - Otras funciones de utilidad para administradores
require_once '../forms/admins/config-admin.php';

// -----------------------------------------------------------------------------
// PASO 3: Configurar respuesta como JSON
// -----------------------------------------------------------------------------
// header(): Envía un encabezado HTTP al navegador
// Content-Type: application/json le dice al navegador que la respuesta es JSON
// Esto es CRÍTICO para que JavaScript pueda parsear la respuesta correctamente
header('Content-Type: application/json');

// =============================================================================
// PASO 4: Verificar permisos de administrador
// =============================================================================
// try-catch: Manejo de errores - captura excepciones
try {
    // requerirAdmin(): Función de config-admin.php que:
    //   1. Verifica si existe $_SESSION['admin_id']
    //   2. Si NO existe, redirige a login-admin.php
    //   3. Si existe, permite continuar
    requerirAdmin();
    
} catch (Exception $e) {
    // Si hay error en la verificación (muy raro), devolver error JSON
    // json_encode(): Convierte array PHP a string JSON
    echo json_encode([
        'success' => false,  // Indica que hubo un error
        'error' => 'No tienes permisos para ver las publicaciones.'
    ]);
    
    // exit(): Termina la ejecución del script inmediatamente
    exit();
}

// =============================================================================
// PASO 5: Construir consulta SQL para obtener publicaciones pendientes
// =============================================================================

// Consulta SQL con múltiples características:
// 
// SELECT: Especifica qué columnas queremos obtener
//   - p.id: ID de la publicación (tabla publicaciones)
//   - p.titulo: Título de la publicación
//   - p.contenido: Contenido completo (texto del artículo)
//   - p.resumen: Resumen breve
//   - p.estado: Estado actual ('borrador', 'revision', etc.)
//   - p.fecha_creacion: Cuándo se creó
//   - pub.nombre as autor: Nombre del publicador (renombrado como 'autor')
//
// FROM publicaciones p: Tabla principal (alias 'p')
//
// LEFT JOIN publicadores pub: Unir con tabla de publicadores
//   - LEFT JOIN: Incluye publicaciones AUNQUE no tengan publicador
//   - ON p.publicador_id = pub.id: Condición de unión
//   - pub: Alias para la tabla publicadores
//
// WHERE: Filtros para seleccionar solo publicaciones pendientes
//   - IN ('borrador', 'revision', 'en_revision', 'pendiente'): 
//     Incluye publicaciones en estos estados
//   - NOT IN ('rechazada', 'publicado'):
//     EXCLUYE publicaciones ya procesadas
//
// ORDER BY p.fecha_creacion DESC: Ordenar por fecha (más recientes primero)
//   - DESC: Descendente (de más nuevo a más viejo)
//
// LIMIT 50: Máximo 50 resultados (evita sobrecargar el navegador)
$query = "SELECT 
            p.id,
            p.titulo,
            p.contenido,
            p.resumen,
            p.estado,
            p.fecha_creacion,
            pub.nombre as autor
          FROM publicaciones p
          LEFT JOIN publicadores pub ON p.publicador_id = pub.id
          WHERE p.estado IN ('borrador', 'revision', 'en_revision', 'pendiente')
          AND p.estado NOT IN ('rechazada', 'publicado')
          ORDER BY p.fecha_creacion DESC
          LIMIT 50";

// -----------------------------------------------------------------------------
// PASO 6: Ejecutar la consulta
// -----------------------------------------------------------------------------
// $conn->query(): Ejecuta la consulta SQL en la base de datos
// Retorna un objeto mysqli_result si tiene éxito, o false si falla
$resultado = $conn->query($query);

// Verificar si hubo error en la consulta
// !$resultado: El operador ! niega, así que esto es "si NO hay resultado"
if (!$resultado) {
    // Si hay error, devolver JSON con el mensaje de error
    // $conn->error: Propiedad que contiene el mensaje de error de MySQL
    echo json_encode([
        'success' => false,
        'error' => 'Error al consultar la base de datos: ' . $conn->error
    ]);
    exit();
}

// =============================================================================
// PASO 7: Procesar los resultados y crear array de publicaciones
// =============================================================================

// Crear array vacío para almacenar las publicaciones
// []: Sintaxis corta para array() en PHP 5.4+
$publicaciones = [];

// while: Bucle que se ejecuta mientras haya filas en el resultado
// $resultado->fetch_assoc(): Obtiene la siguiente fila como array asociativo
//   - Retorna un array con los nombres de columnas como claves
//   - Retorna null cuando no hay más filas (termina el while)
//   - Ejemplo: ['id' => 1, 'titulo' => 'Mi artículo', ...]
while ($fila = $resultado->fetch_assoc()) {
    // []: Agregar elemento al final del array
    // Creamos un nuevo array con la estructura que necesita el frontend
    $publicaciones[] = [
        'id' => $fila['id'],                    // ID numérico de la publicación
        'titulo' => $fila['titulo'],            // Título del artículo
        'contenido' => $fila['contenido'],      // Texto completo
        'resumen' => $fila['resumen'],          // Resumen breve
        'estado' => $fila['estado'],            // Estado actual
        'fecha_creacion' => $fila['fecha_creacion'], // Fecha en formato MySQL
        
        // ?? 'Desconocido': Operador de fusión null
        // Si $fila['autor'] es null, usa 'Desconocido'
        // Esto puede pasar si el LEFT JOIN no encuentra un publicador
        'autor' => $fila['autor'] ?? 'Desconocido'
    ];
}

// =============================================================================
// PASO 8: Enviar respuesta JSON exitosa
// =============================================================================

// json_encode(): Convierte el array PHP a formato JSON
// Parámetros:
//   1. Array a convertir
//   2. JSON_UNESCAPED_UNICODE: Permite caracteres especiales (ñ, tildes, emojis)
//      sin escaparlos como \u00f1
echo json_encode([
    'success' => true,                      // Indica que todo salió bien
    'publicaciones' => $publicaciones,      // Array con todas las publicaciones
    'total' => count($publicaciones)        // Cantidad total de publicaciones
], JSON_UNESCAPED_UNICODE);

// Nota: No es necesario exit() aquí porque es el final del archivo
// PHP terminará automáticamente

?>
