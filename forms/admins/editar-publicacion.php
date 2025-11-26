<?php
// ============================================================================
// ✏️ EDITAR PUBLICACIÓN - EDITAR-PUBLICACION.PHP
// ============================================================================
// Este archivo permite a los administradores editar una publicación existente.
//
// ¿QUÉ HACE ESTE ARCHIVO?
// 1. Recibe el ID de una publicación por URL (?id=123)
// 2. Carga los datos actuales de esa publicación
// 3. Muestra un formulario pre-rellenado con los datos
// 4. Al enviar el formulario, actualiza la publicación en la BD
//
// CAMPOS EDITABLES:
// - Título
// - Contenido
// - Categoría
// - Estado (publicada, borrador, revisión, rechazada)
// - Destacada (sí/no)
//
// CAMPOS DE SOLO LECTURA:
// - Publicador (quién la creó)
// - Fecha de creación
// - Número de vistas
// ============================================================================

// ============================================================================
// 📌 EXPLICACIÓN DE session_start()
// ============================================================================
// session_start() inicia o reanuda una sesión PHP
// ¿Qué es una sesión?
// - Es como una "memoria temporal" que guarda datos del usuario
// - Los datos se guardan en el servidor
// - Se mantienen mientras el usuario navega por el sitio
// - Se identifican con una cookie llamada PHPSESSID
// 
// IMPORTANTE: session_start() DEBE ser lo primero antes de cualquier HTML
// Si hay espacios o HTML antes, dará error
session_start();

// ----------------------------------------------------------------------------
// 1. CONFIGURACIÓN DE LA BASE DE DATOS
// ----------------------------------------------------------------------------
$servername = "localhost";  // Servidor donde está MySQL
$username = "root";         // Usuario de MySQL
$password = "";             // Contraseña (vacía en XAMPP)
$dbname = "lab_exp_db";     // Nombre de nuestra base de datos

// ============================================================================
// 📌 EXPLICACIÓN DE new mysqli()
// ============================================================================
// new mysqli() crea una nueva conexión a MySQL
// mysqli = MySQL Improved (versión mejorada)
// 
// SINTAXIS: new mysqli(servidor, usuario, contraseña, base_datos)
// 
// DEVUELVE:
// - Un objeto de conexión si es exitoso
// - Un objeto con error si falla
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificamos si hubo error al conectar
if ($conn->connect_error) {
    // ========================================================================
    // 📌 EXPLICACIÓN DE die()
    // ========================================================================
    // die() detiene completamente la ejecución del script
    // Es como un "ALTO total"
    // 
    // DIFERENCIA CON exit():
    // - die() puede mostrar un mensaje antes de detener
    // - exit() solo detiene
    // - En la práctica son casi iguales
    // 
    // EJEMPLO:
    // die("Error fatal");  // Muestra "Error fatal" y se detiene
    // exit;                // Solo se detiene
    die("Error de conexión: " . $conn->connect_error);
}

// ============================================================================
// 📌 EXPLICACIÓN DE set_charset()
// ============================================================================
// set_charset() establece el conjunto de caracteres para la conexión
// 
// ¿POR QUÉ USAR utf8mb4?
// - utf8mb4 soporta TODOS los caracteres Unicode
// - Incluye emojis (😀), acentos (á, é, í), ñ, etc.
// - utf8 normal NO soporta emojis
// 
// EJEMPLO:
// utf8mb4: "Hola 😀 ñoño" ✅ Funciona
// utf8:    "Hola 😀 ñoño" ❌ El emoji se rompe
$conn->set_charset("utf8mb4");

// ----------------------------------------------------------------------------
// 2. VERIFICAR SI ES ADMINISTRADOR
// ----------------------------------------------------------------------------
// ============================================================================
// 📌 EXPLICACIÓN DE isset()
// ============================================================================
// isset() verifica si una variable existe y NO es NULL
// 
// DEVUELVE:
// - true si la variable existe y no es NULL
// - false si no existe o es NULL
// 
// EJEMPLOS:
// $nombre = "Juan";
// isset($nombre)  // true
// 
// $edad = null;
// isset($edad)    // false
// 
// isset($noExiste) // false
//
// ============================================================================
// 📌 EXPLICACIÓN DEL OPERADOR ! (NOT)
// ============================================================================
// ! invierte el valor booleano
// 
// EJEMPLOS:
// !true = false
// !false = true
// !isset($var) = true si $var NO existe
//
// ============================================================================
// 📌 EXPLICACIÓN DEL OPERADOR || (OR)
// ============================================================================
// || significa "O" lógico
// Devuelve true si AL MENOS UNA condición es verdadera
// 
// EJEMPLOS:
// true || false = true
// false || false = false
// true || true = true
//
// EN ESTE CASO:
// Si NO existe admin_id O el nivel está vacío, redirigimos
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_nivel'] == '') {
    // ========================================================================
    // 📌 EXPLICACIÓN DE header()
    // ========================================================================
    // header() envía un encabezado HTTP al navegador
    // 
    // USOS COMUNES:
    // header("Location: url") → Redirige a otra página
    // header("Content-Type: application/json") → Indica que es JSON
    // header("HTTP/1.1 404 Not Found") → Error 404
    // 
    // IMPORTANTE:
    // - Debe usarse ANTES de cualquier HTML
    // - Después de header() siempre usar exit para detener el script
    header("Location: login-admin.php");
    exit;
}

// Obtenemos los datos del admin de la sesión
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'];

// ----------------------------------------------------------------------------
// 3. OBTENER EL ID DE LA PUBLICACIÓN A EDITAR
// ----------------------------------------------------------------------------
// ============================================================================
// 📌 EXPLICACIÓN DE $_GET
// ============================================================================
// $_GET es un array que contiene datos enviados por URL
// 
// EJEMPLO:
// URL: editar-publicacion.php?id=5&nombre=Juan
// $_GET['id'] = "5"
// $_GET['nombre'] = "Juan"
// 
// IMPORTANTE:
// - Los datos en $_GET son VISIBLES en la URL
// - Cualquiera puede modificarlos
// - NUNCA confiar en $_GET sin validar
//
// ============================================================================
// 📌 EXPLICACIÓN DE empty()
// ============================================================================
// empty() verifica si una variable está "vacía"
// 
// CONSIDERA VACÍO:
// - "" (string vacío)
// - 0 (número cero)
// - "0" (string "0")
// - null
// - false
// - array() (array vacío)
// - Variable no definida
// 
// EJEMPLOS:
// empty("") = true
// empty("Hola") = false
// empty(0) = true
// empty(5) = false
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = "No se especificó una publicación para editar";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: gestionar-publicaciones.php");
    exit;
}

// ============================================================================
// 📌 EXPLICACIÓN DE intval()
// ============================================================================
// intval() convierte un valor a número entero (integer)
// 
// ¿POR QUÉ ES IMPORTANTE?
// - SEGURIDAD: Previene inyecciones SQL
// - Si alguien pone "5 OR 1=1", intval() lo convierte a solo 5
// 
// EJEMPLOS:
// intval("123") = 123
// intval("123abc") = 123
// intval("abc") = 0
// intval("5.7") = 5
// intval("5 OR 1=1") = 5
$publicacion_id = intval($_GET['id']);

// ----------------------------------------------------------------------------
// 4. PROCESAR EL FORMULARIO SI SE ENVIÓ
// ----------------------------------------------------------------------------
// ============================================================================
// 📌 EXPLICACIÓN DE $_SERVER["REQUEST_METHOD"]
// ============================================================================
// $_SERVER es un array con información del servidor y la petición
// REQUEST_METHOD indica cómo se envió la petición:
// - "GET" = Datos en la URL
// - "POST" = Datos en el cuerpo de la petición (formularios)
// - "PUT", "DELETE", etc.
// 
// DIFERENCIA GET vs POST:
// GET:  URL visible, límite de caracteres, se puede guardar en favoritos
// POST: Datos ocultos, sin límite, más seguro para contraseñas
//
// ============================================================================
// 📌 EXPLICACIÓN DEL OPERADOR && (AND)
// ============================================================================
// && significa "Y" lógico
// Devuelve true solo si AMBAS condiciones son verdaderas
// 
// EJEMPLOS:
// true && true = true
// true && false = false
// false && false = false
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['actualizar_publicacion'])) {
    // ========================================================================
    // 📌 EXPLICACIÓN DE trim()
    // ========================================================================
    // trim() quita espacios en blanco al inicio y final de un string
    // 
    // TAMBIÉN QUITA:
    // - Espacios (" ")
    // - Tabulaciones ("\t")
    // - Saltos de línea ("\n", "\r")
    // 
    // EJEMPLOS:
    // trim("  Hola  ") = "Hola"
    // trim("\n\tTexto\n") = "Texto"
    // trim("Hola Mundo") = "Hola Mundo" (no quita espacios del medio)
    // 
    // ¿POR QUÉ USARLO?
    // - Los usuarios a veces ponen espacios sin querer
    // - Evita guardar "  Juan  " en vez de "Juan"
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $categoria_id = intval($_POST['categoria_id']);
    $estado = $_POST['estado'];
    

    
    // Preparamos la consulta UPDATE
    // ========================================================================
    // 📌 EXPLICACIÓN DE NOW()
    // ========================================================================
    // NOW() es una función de MySQL que devuelve la fecha y hora actual
    // Formato: YYYY-MM-DD HH:MM:SS
    // Ejemplo: 2024-11-22 16:30:45
    $query = "UPDATE publicaciones SET 
              titulo = ?, 
              contenido = ?, 
              categoria_id = ?, 
              estado = ?, 
              fecha_actualizacion = NOW()
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    
    // ========================================================================
    // 📌 EXPLICACIÓN DETALLADA DE bind_param()
    // ========================================================================
    // bind_param() vincula variables a los ? de la consulta
    // 
    // TIPOS DE DATOS:
    // "s" = string (texto)
    // "i" = integer (número entero)
    // "d" = double (número decimal)
    // "b" = blob (datos binarios)
    // 
    // EN ESTE CASO: "sssiii"
    // s = $titulo (string)
    // s = $contenido (string)
    // i = $categoria_id (integer)
    // s = $estado (string)
    // i = $publicacion_id (integer)
    // 
    // ORDEN IMPORTANTE:
    // El orden de las letras debe coincidir con el orden de las variables
    // Si cambias el orden, los datos se guardarán mal
    $stmt->bind_param("ssisi", $titulo, $contenido, $categoria_id, $estado, $publicacion_id);
    
    // Ejecutamos la consulta
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Publicación actualizada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        header("Location: gestionar-publicaciones.php");
        exit;
    } else {
        // ====================================================================
        // 📌 EXPLICACIÓN DE $conn->error
        // ====================================================================
        // $conn->error contiene el último mensaje de error de MySQL
        // Es útil para debugging (encontrar errores)
        // 
        // EJEMPLO:
        // Si hay un error de sintaxis SQL, mostrará algo como:
        // "You have an error in your SQL syntax..."
        $_SESSION['mensaje'] = "Error al actualizar la publicación: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

// ----------------------------------------------------------------------------
// 5. OBTENER LOS DATOS DE LA PUBLICACIÓN
// ----------------------------------------------------------------------------
// Consultamos la publicación con JOIN para obtener datos relacionados
// ============================================================================
// 📌 EXPLICACIÓN DE LEFT JOIN
// ============================================================================
// LEFT JOIN une dos tablas manteniendo TODOS los registros de la izquierda
// 
// EJEMPLO:
// Tabla publicaciones (izquierda):
// id | titulo | publicador_id
// 1  | "Post" | 5
// 
// Tabla publicadores (derecha):
// id | nombre
// 5  | "Juan"
// 
// LEFT JOIN publicadores ON publicador_id = id
// Resultado:
// id | titulo | publicador_id | nombre
// 1  | "Post" | 5             | "Juan"
// 
// Si no hay coincidencia, los campos de la derecha son NULL
//
// ============================================================================
// 📌 EXPLICACIÓN DE p.*, pub.nombre as publicador_nombre
// ============================================================================
// p.* = Todas las columnas de la tabla 'p' (publicaciones)
// pub.nombre as publicador_nombre = Renombra la columna
// 
// ¿POR QUÉ RENOMBRAR?
// - Evita confusión si hay columnas con el mismo nombre
// - Hace el código más claro
// 
// EJEMPLO:
// SELECT p.nombre, pub.nombre FROM publicaciones p...
// ❌ Confuso: ¿cuál nombre es cuál?
// 
// SELECT p.nombre as titulo, pub.nombre as publicador_nombre...
// ✅ Claro: sabemos qué es cada uno
$query = "SELECT p.*, 
          pub.nombre as publicador_nombre,
          c.nombre as categoria_nombre
          FROM publicaciones p
          LEFT JOIN publicadores pub ON p.publicador_id = pub.id
          LEFT JOIN categorias c ON p.categoria_id = c.id
          WHERE p.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $publicacion_id);
$stmt->execute();

// ============================================================================
// 📌 EXPLICACIÓN DE get_result()
// ============================================================================
// get_result() obtiene el resultado de una consulta preparada
// 
// DEVUELVE:
// - Un objeto mysqli_result con los datos
// - false si hay error
// 
// DESPUÉS DE get_result() podemos usar:
// - fetch_assoc() para obtener una fila
// - fetch_all() para obtener todas las filas
// - num_rows para contar filas
$result = $stmt->get_result();

// ============================================================================
// 📌 EXPLICACIÓN DE num_rows
// ============================================================================
// num_rows cuenta cuántas filas devolvió la consulta
// 
// EJEMPLOS:
// Si encontró la publicación: num_rows = 1
// Si no existe: num_rows = 0
// Si hay varias: num_rows = cantidad
//
// ============================================================================
// 📌 EXPLICACIÓN DEL OPERADOR === (IDÉNTICO)
// ============================================================================
// === compara valor Y tipo de dato
// == solo compara valor
// 
// EJEMPLOS:
// 5 == "5"   → true (mismo valor)
// 5 === "5"  → false (diferente tipo: int vs string)
// 0 == false → true
// 0 === false → false
// 
// BUENA PRÁCTICA: Usar === siempre que sea posible
if ($result->num_rows === 0) {
    $_SESSION['mensaje'] = "La publicación no existe";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: gestionar-publicaciones.php");
    exit;
}

// ============================================================================
// 📌 EXPLICACIÓN DE fetch_assoc()
// ============================================================================
// fetch_assoc() obtiene UNA fila del resultado como array asociativo
// 
// DEVUELVE:
// - Array con nombres de columnas como claves
// - null si no hay más filas
// 
// EJEMPLO:
// $row = $result->fetch_assoc();
// $row['titulo'] = "Mi publicación"
// $row['contenido'] = "Texto..."
// 
// DIFERENCIA CON fetch_array():
// fetch_assoc() → Solo nombres: $row['titulo']
// fetch_array() → Nombres e índices: $row['titulo'] o $row[0]
$publicacion = $result->fetch_assoc();

// ----------------------------------------------------------------------------
// 6. OBTENER TODAS LAS CATEGORÍAS PARA EL SELECT
// ----------------------------------------------------------------------------
// ============================================================================
// 📌 EXPLICACIÓN DE ORDER BY
// ============================================================================
// ORDER BY ordena los resultados
// 
// SINTAXIS: ORDER BY columna [ASC|DESC]
// ASC = Ascendente (A-Z, 0-9) [por defecto]
// DESC = Descendente (Z-A, 9-0)
// 
// EJEMPLOS:
// ORDER BY nombre → Alfabético A-Z
// ORDER BY nombre DESC → Alfabético Z-A
// ORDER BY fecha DESC → Más recientes primero
// ORDER BY precio ASC → Más baratos primero
$query_categorias = "SELECT id, nombre FROM categorias WHERE (estado = 'activa' OR estado = 'activo' OR estado IS NULL OR estado = '') ORDER BY nombre";
$result_categorias = $conn->query($query_categorias);

// ============================================================================
// 📌 EXPLICACIÓN DE fetch_all(MYSQLI_ASSOC)
// ============================================================================
// fetch_all() obtiene TODAS las filas de una vez
// 
// MYSQLI_ASSOC = Array asociativo (con nombres de columnas)
// MYSQLI_NUM = Array numérico (con índices 0, 1, 2...)
// MYSQLI_BOTH = Ambos (asociativo y numérico)
// 
// EJEMPLO:
// $categorias = [
//     ['id' => 1, 'nombre' => 'Ciencia'],
//     ['id' => 2, 'nombre' => 'Tecnología']
// ]
$categorias = $result_categorias->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicación - Lab-Explorer</title>
    
    <!-- Fuentes de Google -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS de Bootstrap y vendors -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- CSS personalizado -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <style>
        /* Estilos personalizados para el editor */
        .form-control, .form-select {
            border-radius: 8px;  /* Bordes redondeados */
        }
        .btn-primary {
            /* Gradiente morado para el botón principal */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-secondary {
            background: #6c757d;  /* Gris para botón secundario */
            border: none;
        }
        textarea.form-control {
            min-height: 300px;  /* Altura mínima del textarea */
        }
    </style>
</head>
<body class="admin-page">

    <!-- ================================================================ -->
    <!-- HEADER -->
    <!-- ================================================================ -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <!-- Logo -->
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
                </a>
                
                <!-- Información del admin -->
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <!-- ================================================ -->
                        <!-- 📌 EXPLICACIÓN DE htmlspecialchars() -->
                        <!-- ================================================ -->
                        <!-- htmlspecialchars() convierte caracteres especiales a entidades HTML -->
                        <!-- -->
                        <!-- ¿POR QUÉ ES IMPORTANTE? -->
                        <!-- - SEGURIDAD: Previene ataques XSS (Cross-Site Scripting) -->
                        <!-- - Si un usuario pone <script>alert('hack')</script> en su nombre -->
                        <!-- - htmlspecialchars() lo convierte a &lt;script&gt;... -->
                        <!-- - Así se muestra como texto, no se ejecuta como código -->
                        <!-- -->
                        <!-- CONVERSIONES: -->
                        <!-- < → &lt; -->
                        <!-- > → &gt; -->
                        <!-- " → &quot; -->
                        <!-- ' → &#039; -->
                        <!-- & → &amp; -->
                        <!-- -->
                        <!-- EJEMPLO: -->
                        <!-- $nombre = "<script>alert('XSS')</script>"; -->
                        <!-- echo htmlspecialchars($nombre); -->
                        <!-- Muestra: &lt;script&gt;alert('XSS')&lt;/script&gt; -->
                        <!-- (Se ve como texto, no se ejecuta) -->
                        <span class="saludo">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</span>
                        <a href="logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ================================================================ -->
    <!-- CONTENIDO PRINCIPAL -->
    <!-- ================================================================ -->
    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Sidebar de navegación -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="../../index.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Página principal
                            </a>
                            <a href="index-admin.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-house me-2"></i>Panel Principal
                            </a>
                            <a href="gestionar_publicadores.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-people me-2"></i>Gestionar Publicadores
                            </a>
                            <a href="usuarios.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-person-badge me-2"></i>Usuarios Registrados
                            </a>
                            <a href="gestionar-publicaciones.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
                            </a>
                            <a href="./categorias/crear_categoria.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-tags me-2"></i>Categorías
                            </a>
                            <?php if($admin_nivel == 'superadmin'): ?>
                            <a href="admins.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Contenido principal -->
                <div class="col-md-9">
                    <!-- Mensajes de éxito o error -->
                    <?php if(isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php 
                        // ====================================================
                        // 📌 EXPLICACIÓN DE unset()
                        // ====================================================
                        // unset() elimina una variable
                        // 
                        // USOS:
                        // - Liberar memoria
                        // - Limpiar datos temporales
                        // - Eliminar elementos de arrays
                        // 
                        // EJEMPLOS:
                        // $nombre = "Juan";
                        // unset($nombre);
                        // // Ahora $nombre no existe
                        // 
                        // $array = [1, 2, 3];
                        // unset($array[1]);
                        // // $array = [1, 3]
                        // 
                        // EN ESTE CASO:
                        // Limpiamos los mensajes después de mostrarlos
                        // para que no aparezcan en la siguiente recarga
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                    endif; ?>

                    <!-- Título de la página -->
                    <div class="section-title" data-aos="fade-up">
                        <h2>Editar Publicación</h2>
                        <p>Modifica los datos de la publicación</p>
                    </div>

                    <!-- Formulario de edición -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header warning-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pencil me-2"></i>
                                Editando: <?= htmlspecialchars($publicacion['titulo']) ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Formulario que envía los datos por POST -->
                            <form method="POST" action="">
                                
                                <!-- Campo: Título -->
                                <div class="mb-3">
                                    <label class="form-label">Título *</label>
                                    <input type="text" 
                                           name="titulo" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars($publicacion['titulo']) ?>" 
                                           required>
                                    <small class="text-muted">El título de la publicación</small>
                                </div>

                                <!-- Campo: Contenido -->
                                <div class="mb-3">
                                    <label class="form-label">Contenido *</label>
                                    <textarea name="contenido" 
                                              class="form-control" 
                                              rows="10" 
                                              required><?= htmlspecialchars($publicacion['contenido']) ?></textarea>
                                    <small class="text-muted">El contenido completo de la publicación</small>
                                </div>

                                <div class="row">
                                    <!-- Campo: Categoría -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Categoría *</label>
                                            <select name="categoria_id" class="form-select" required>
                                                <option value="">Selecciona una categoría</option>
                                                <!-- ================================ -->
                                                <!-- 📌 EXPLICACIÓN DE foreach -->
                                                <!-- ================================ -->
                                                <!-- foreach recorre cada elemento de un array -->
                                                <!-- SINTAXIS: foreach($array as $variable) -->
                                                <!-- -->
                                                <!-- EN ESTE CASO: -->
                                                <!-- Recorre cada categoría del array $categorias -->
                                                <!-- En cada vuelta, $cat contiene una categoría -->
                                                <?php foreach($categorias as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" 
                                                        <?= $publicacion['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['nombre']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Campo: Estado -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Estado *</label>
                                            <select name="estado" class="form-select" required>
                                                <option value="publicada" <?= $publicacion['estado'] == 'publicada' ? 'selected' : '' ?>>Publicada</option>
                                                <option value="borrador" <?= $publicacion['estado'] == 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                                <option value="revision" <?= $publicacion['estado'] == 'revision' ? 'selected' : '' ?>>En Revisión</option>
                                                <option value="rechazada" <?= $publicacion['estado'] == 'rechazada' ? 'selected' : '' ?>>Rechazada</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>



                                <!-- Información adicional (solo lectura) -->
                                <div class="alert alert-info">
                                    <strong>Información:</strong><br>
                                    Publicador: <?= htmlspecialchars($publicacion['publicador_nombre']) ?><br>
                                    <!-- ======================================== -->
                                    <!-- 📌 EXPLICACIÓN DE date() y strtotime() -->
                                    <!-- ======================================== -->
                                    <!-- strtotime() convierte una fecha de texto a timestamp (número) -->
                                    <!-- date() formatea un timestamp a texto legible -->
                                    <!-- -->
                                    <!-- FORMATO date(): -->
                                    <!-- d = día (01-31) -->
                                    <!-- m = mes (01-12) -->
                                    <!-- Y = año (2024) -->
                                    <!-- H = hora 24h (00-23) -->
                                    <!-- i = minutos (00-59) -->
                                    <!-- s = segundos (00-59) -->
                                    <!-- -->
                                    <!-- EJEMPLO: -->
                                    <!-- $fecha = "2024-11-22 16:30:00"; -->
                                    <!-- echo date('d/m/Y H:i', strtotime($fecha)); -->
                                    <!-- Resultado: 22/11/2024 16:30 -->
                                    Fecha de creación: <?= date('d/m/Y H:i', strtotime($publicacion['fecha_publicacion'])) ?><br>
                                    Vistas: <?= $publicacion['vistas'] ?>
                                </div>

                                <!-- Botones de acción -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="gestionar-publicaciones.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                    </a>
                                    <button type="submit" name="actualizar_publicacion" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Actualizar Publicación
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer id="footer" class="footer dark-background">
        <div class="container">
            <h3 class="sitename">Lab-Explorer</h3>
            <p>Panel de Administración</p>
            <div class="copyright">
                <span>Copyright</span> <strong class="px-1 sitename">Lab-Explorer</strong> <span>Todos los derechos reservados</span>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <script>
        // Inicializamos las animaciones AOS
        AOS.init({
            duration: 600,        // Duración de la animación en milisegundos
            easing: 'ease-in-out', // Tipo de animación (suave)
            once: true,           // Solo animar una vez
            mirror: false         // No animar al hacer scroll hacia arriba
        });
    </script>

</body>
</html>
<?php
// ============================================================================
// 📌 EXPLICACIÓN DE $conn->close()
// ============================================================================
// close() cierra la conexión a la base de datos
// 
// ¿POR QUÉ ES IMPORTANTE?
// - Libera recursos del servidor
// - Evita que se acumulen conexiones abiertas
// - MySQL tiene un límite de conexiones simultáneas
// 
// BUENA PRÁCTICA:
// - Siempre cerrar la conexión al final del script
// - PHP lo hace automáticamente, pero es mejor ser explícito
$conn->close();
?>
