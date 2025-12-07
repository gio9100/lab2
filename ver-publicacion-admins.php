<?php
// Abrimos el bloque de código PHP
session_start();
// Iniciamos la sesión para saber si hay alguien conectado
require_once './forms/conexion.php';
// Traemos la conexión a la base de datos para poder hacer consultas

// Línea vacía
// Checamos si nos mandaron un ID en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
// Si no hay ID o está vacío, no sabemos qué mostrar
    header('Location: index.php');
// Así que lo mandamos de regreso al inicio
    exit();
// Y matamos el script para que no siga corriendo
}
// Cerramos el if

// VERIFICACIÓN DE SEGURIDAD: Solo administradores pueden ver esto
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Línea vacía
$publicacion_id = intval($_GET['id']);
// Convertimos el ID a número entero por seguridad (para que no nos metan texto raro)

// Línea vacía
// Preparamos la consulta para buscar la publicación
$query = "SELECT p.*, c.nombre as categoria_nombre, pub.nombre as publicador_nombre, pub.especialidad " .
// Seleccionamos todo de la publicación, más el nombre de la categoría y del publicador
          "FROM publicaciones p " .
// Buscamos en la tabla publicaciones
          "LEFT JOIN categorias c ON p.categoria_id = c.id " .
// Unimos con categorías para saber el nombre
          "LEFT JOIN publicadores pub ON p.publicador_id = pub.id " .
// Unimos con publicadores para saber quién la escribió
          "WHERE p.id = ?";

// En este archivo de admin, NO filtramos por estado. Se ve todo.

// Línea vacía
$stmt = $conexion->prepare($query);
// Preparamos la consulta para evitar hackeos SQL
$stmt->bind_param("i", $publicacion_id);
// Le pasamos el ID como un entero ("i")
$stmt->execute();
// Ejecutamos la consulta
$result = $stmt->get_result();
// Guardamos el resultado
// Línea vacía
if ($result->num_rows === 0) {
// Si no encontramos ninguna publicación con ese ID
    header('Location: index.php');
// Lo mandamos al inicio
    exit();
// Y terminamos
}
// Cerramos el if

// Línea vacía
$publicacion = $result->fetch_assoc();
// Guardamos los datos de la publicación en un array
$stmt->close();
// Cerramos la consulta preparada

// Línea vacía
// Función para arreglar el contenido HTML antes de mostrarlo
function procesarContenido($contenido) {
    // Si el contenido ya tiene etiquetas HTML (del editor Quill)
    if (strip_tags($contenido) !== $contenido) {
        // Arreglar rutas de imágenes que puedan estar incorrectas
        $contenido = preg_replace(
            '/src="(?!\/|http)([^"]*uploads\/contenido\/[^"]+)"/i',
            'src="/$1"',
            $contenido
        );
        
        // Asegurar que las imágenes tengan las clases correctas
        $contenido = preg_replace('/\<img(?![^\>]*class=)/', '\<img class="content-image"', $contenido);
        
        // Agregar onclick para lightbox
        $contenido = preg_replace(
            '/\<img([^\>]*class="[^"]*content-image[^"]*)"([^\>]*)\>/i',
            '\<img$1"$2 onclick="abrirLightbox(this.src)" style="cursor:pointer"\>',
            $contenido
        );
        
        return $contenido;
    } else {
        // Contenido plano
        $contenido = nl2br(htmlspecialchars($contenido));
        $patron = '/uploads\\/contenido\\/[a-zA-Z0-9_\\-\\.]+\\.(jpg|jpeg|png|gif|webp|jfif)/i';
        $contenido = preg_replace_callback($patron, function($matches) {
            $ruta = $matches[0];
            return '\<img src="/' . htmlspecialchars($ruta) . '" alt="Imagen de contenido" class="content-image" onclick="abrirLightbox(this.src)" style="cursor:pointer"\>';
        }, $contenido);
        return $contenido;
    }
}

// Línea vacía
// Incluir configuración de usuario para el header
require_once __DIR__ . "/forms/usuario.php";
// Traemos el archivo de usuario para saber si hay sesión iniciada
?>
<!-- Cerramos el código PHP -->

<!-- Línea vacía -->
<!DOCTYPE html>
<!-- Declaramos que es HTML5 -->
<html lang="es">
<!-- Idioma español -->
<head>
<!-- Abrimos el head -->
    <meta charset="UTF-8">
<!-- Charset UTF-8 para acentos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Responsive para celulares -->
    <title><?= htmlspecialchars($publicacion['titulo']) ?> - Lab Explorer</title>
<!-- Título de la pestaña con el nombre de la publicación -->
    <meta name="description" content="<?= htmlspecialchars($publicacion['meta_descripcion'] ?? '') ?>">
<!-- Meta descripción para Google -->
    
<!-- Línea vacía -->
    <!-- Fonts -->
<!-- Comentario: Aquí van las fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
<!-- Pre-conexión a Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
<!-- Pre-conexión al CDN -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<!-- Cargamos Roboto, Poppins y Nunito -->
    
<!-- Línea vacía -->
    <!-- Bootstrap Icons -->
<!-- Comentario: Iconos de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<!-- Link al CSS de los iconos -->
    
<!-- Línea vacía -->
    <!-- Bootstrap CSS -->
<!-- Comentario: CSS de Bootstrap -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Link al framework Bootstrap -->

<!-- Línea vacía -->
    <!-- Main CSS -->
<!-- Comentario: Nuestros estilos -->
    <link href="assets/css/main.css" rel="stylesheet">
<!-- Estilos principales -->
    <link rel="stylesheet" href="assets/css-admins/admin.css">
<!-- Estilos de admin -->

<!-- Línea vacía -->
    <style>
/* Abrimos estilos personalizados */
        :root {
/* Variables globales */
            --primary: #7390A0;
/* Azul principal */
            --primary-light: #8fa9b8;
/* Azul clarito */
            --primary-dark: #5a7080;
/* Azul oscuro */
            --secondary: #6c757d;
/* Gris secundario */
            --accent: #f75815;
/* Naranja de acento */
            --text: #212529;
/* Color de texto */
            --text-light: #6c757d;
/* Texto ligero */
            --background: #f8f9fa;
/* Fondo de página */
            --white: #ffffff;
/* Blanco puro */
            --border: #e9ecef;
/* Color de bordes */
            --shadow: 0 2px 8px rgba(0,0,0,0.08);
/* Sombra normal */
            --shadow-lg: 0 8px 20px rgba(0,0,0,0.12);
/* Sombra grande */
        }
/* Cerramos variables */
        
/* Línea vacía */
        * {
/* Selector universal */
            margin: 0;
/* Sin margen */
            padding: 0;
/* Sin padding */
            box-sizing: border-box;
/* Box sizing border-box */
        }
/* Cerramos universal */
        
/* Línea vacía */
        body {
/* Estilos del body */
            font-family: 'Inter', sans-serif;
/* Fuente Inter */
            background: var(--background);
/* Fondo gris claro */
            color: var(--text);
/* Color de texto */
            line-height: 1.6;
/* Altura de línea */
        }
/* Cerramos body */
        
/* Línea vacía */
        /* Hero Section */
/* Comentario: Sección Hero (la parte de arriba) */
        .hero-section {
/* Clase del hero */
            background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
/* Fondo degradado gris */
            color: var(--text);
/* Texto oscuro */
            padding: 60px 0;
/* Padding arriba y abajo */
            text-align: center;
/* Texto centrado */
        }
/* Cerramos hero-section */
        
/* Línea vacía */
        .hero-content {
/* Contenido del hero */
            max-width: 900px;
/* Ancho máximo */
            margin: 0 auto;
/* Centrado horizontal */
            padding: 0 20px;
/* Padding a los lados */
        }
/* Cerramos hero-content */
        
/* Línea vacía */
        .hero-title {
/* Título del hero */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-size: 2.8rem;
/* Tamaño grande */
            font-weight: 700;
/* Negrita */
            margin-bottom: 20px;
/* Margen abajo */
            line-height: 1.2;
/* Altura de línea ajustada */
            color: var(--text);
/* Color de texto */
        }
/* Cerramos hero-title */
        
/* Línea vacía */
        .hero-description {
/* Descripción en el hero */
            font-size: 1.2rem;
/* Tamaño mediano */
            color: var(--text-light);
/* Color gris */
            margin-bottom: 30px;
/* Margen abajo */
            line-height: 1.6;
/* Altura de línea */
        }
/* Cerramos hero-description */
        
/* Línea vacía */
        .category-badge {
/* Badge de categoría */
            display: inline-block;
/* Bloque en línea */
            background: var(--primary);
/* Fondo azul */
            color: var(--white);
/* Texto blanco */
            padding: 8px 20px;
/* Padding interno */
            border-radius: 20px;
/* Bordes redondos */
            font-size: 0.9rem;
/* Tamaño pequeño */
            font-weight: 600;
/* Negrita */
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
/* Sombra */
        }
/* Cerramos category-badge */
        
/* Línea vacía */
        /* Main Content */
/* Comentario: Contenido principal */
        .main-content {
/* Clase main-content */
            max-width: 1200px;
/* Ancho máximo */
            margin: -40px auto 40px;
/* Margen negativo arriba para que se meta en el hero */
            padding: 0 20px;
/* Padding a los lados */
        }
/* Cerramos main-content */
        
/* Línea vacía */
        /* Content Section */
/* Comentario: Sección de contenido */
        .content-section {
/* Clase content-section */
            background: var(--white);
/* Fondo blanco */
            border-radius: 16px;
/* Bordes redondeados */
            padding: 40px;
/* Padding interno */
            box-shadow: var(--shadow);
/* Sombra */
            margin-bottom: 30px;
/* Margen abajo */
        }
/* Cerramos content-section */
        
/* Línea vacía */
        .section-header {
/* Encabezado de la sección */
            text-align: center;
/* Centrado */
            margin-bottom: 40px;
/* Margen abajo */
        }
/* Cerramos section-header */
        
/* Línea vacía */
        .section-title {
/* Título de la sección */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-size: 2rem;
/* Tamaño grande */
            font-weight: 700;
/* Negrita */
            color: var(--primary);
/* Color azul */
            margin-bottom: 10px;
/* Margen abajo */
        }
/* Cerramos section-title */
        
/* Línea vacía */
        .section-subtitle {
/* Subtítulo */
            color: var(--text-light);
/* Color gris */
            font-size: 1.1rem;
/* Tamaño mediano */
        }
/* Cerramos section-subtitle */
        
/* Línea vacía */
        .publication-content {
/* Contenido de la publicación */
            font-size: 1.05rem;
/* Tamaño de letra */
            line-height: 1.8;
/* Altura de línea para leer mejor */
            color: var(--text);
/* Color de texto */
        }
/* Cerramos publication-content */
        
/* Línea vacía */
        .publication-content h1,
        .publication-content h2 {
/* Estilos para h1 y h2 dentro del contenido */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-size: 1.8rem;
/* Tamaño grande */
            font-weight: 600;
/* Negrita */
            color: var(--primary);
/* Color azul */
            margin: 30px 0 15px;
/* Márgenes */
        }
/* Cerramos h1 y h2 */
        
/* Línea vacía */
        .publication-content h3 {
/* Estilos para h3 */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-size: 1.4rem;
/* Tamaño mediano */
            font-weight: 600;
/* Negrita */
            color: var(--text);
/* Color texto */
            margin: 25px 0 12px;
/* Márgenes */
        }
/* Cerramos h3 */
        
/* Línea vacía */
        .publication-content h4,
        .publication-content h5,
        .publication-content h6 {
/* Estilos para h4, h5, h6 */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-weight: 600;
/* Negrita */
            color: var(--text);
/* Color texto */
            margin: 20px 0 10px;
/* Márgenes */
        }
/* Cerramos h4, h5, h6 */
        
/* Línea vacía */
        .publication-content p {
/* Estilos para párrafos */
            margin-bottom: 20px;
/* Margen abajo */
        }
/* Cerramos p */
        
/* Línea vacía */
        .publication-content ul,
        .publication-content ol {
/* Estilos para listas */
            margin: 20px 0;
/* Margen arriba y abajo */
            padding-left: 30px;
/* Padding a la izquierda */
        }
/* Cerramos listas */
        
/* Línea vacía */
        .publication-content li {
/* Estilos para items de lista */
            margin-bottom: 10px;
/* Margen abajo */
        }
/* Cerramos li */
        
/* Línea vacía */
        .publication-content strong,
        .publication-content b {
/* Estilos para negritas */
            font-weight: 600;
/* Negrita */
            color: var(--text);
/* Color texto */
        }
/* Cerramos strong y b */
        
/* Línea vacía */
        .publication-content em,
        .publication-content i {
/* Estilos para itálicas */
            font-style: italic;
/* Itálica */
        }
/* Cerramos em e i */
        
/* Línea vacía */
        .publication-content a {
/* Estilos para enlaces */
            color: var(--primary);
/* Color azul */
            text-decoration: underline;
/* Subrayado */
            transition: color 0.3s ease;
/* Transición suave */
        }
/* Cerramos a */
        
/* Línea vacía */
        .publication-content a:hover {
/* Hover en enlaces */
            color: var(--accent);
/* Cambia a naranja */
        }
/* Cerramos a:hover */
        
/* Línea vacía */
        /* Estilos mejorados para imágenes en el contenido */
/* Comentario: Estilos de imágenes */
        .publication-content img,
        .publication-content .content-image {
/* Imágenes dentro del contenido */
            max-width: 100%;
/* Ancho máximo 100% */
            height: auto;
/* Altura automática */
            border-radius: 12px;
/* Bordes redondeados */
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
/* Sombra */
            margin: 25px auto;
/* Margen centrado */
            display: block;
/* Bloque para que se centre */
            cursor: pointer;
/* Cursor de manita */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
/* Transiciones */
        }
/* Cerramos img */
        
/* Línea vacía */
        .publication-content img:hover,
        .publication-content .content-image:hover {
/* Hover en imágenes */
            transform: scale(1.02);
/* Se hace un poquito más grande */
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
/* Más sombra */
        }
/* Cerramos img:hover */
        
/* Línea vacía */
        /* Lightbox */
/* Comentario: Estilos del lightbox (la ventana modal de imágenes) */
        .lightbox {
/* Contenedor del lightbox */
            display: none;
/* Oculto por defecto */
            position: fixed;
/* Posición fija en la pantalla */
            z-index: 9999;
/* Hasta arriba de todo */
            top: 0;
/* Arriba 0 */
            left: 0;
/* Izquierda 0 */
            width: 100%;
/* Ancho completo */
            height: 100%;
/* Alto completo */
            background: rgba(0,0,0,0.9);
/* Fondo negro casi transparente */
            justify-content: center;
/* Centrado horizontal */
            align-items: center;
/* Centrado vertical */
        }
/* Cerramos lightbox */
        
/* Línea vacía */
        .lightbox.active {
/* Cuando el lightbox está activo */
            display: flex;
/* Se muestra con flex */
        }
/* Cerramos lightbox.active */
        
/* Línea vacía */
        .lightbox-content {
/* La imagen dentro del lightbox */
            max-width: 90%;
/* Ancho máximo 90% */
            max-height: 90%;
/* Alto máximo 90% */
            object-fit: contain;
/* Ajuste de imagen */
            border-radius: 8px;
/* Bordes redondeados */
            box-shadow: 0 0 30px rgba(255,255,255,0.3);
/* Sombra blanca brillante */
        }
/* Cerramos lightbox-content */
        
/* Línea vacía */
        .lightbox-close {
/* Botón de cerrar */
            position: absolute;
/* Posición absoluta */
            top: 20px;
/* Arriba 20px */
            right: 40px;
/* Derecha 40px */
            font-size: 40px;
/* Tamaño grande */
            color: white;
/* Color blanco */
            cursor: pointer;
/* Cursor de manita */
            background: rgba(0,0,0,0.5);
/* Fondo semitransparente */
            width: 50px;
/* Ancho 50px */
            height: 50px;
/* Alto 50px */
            border-radius: 50%;
/* Redondo */
            display: flex;
/* Flex para centrar la X */
            align-items: center;
/* Centrado vertical */
            justify-content: center;
/* Centrado horizontal */
            transition: background 0.3s ease;
/* Transición */
        }
/* Cerramos lightbox-close */
        
/* Línea vacía */
        .lightbox-close:hover {
/* Hover en cerrar */
            background: rgba(255,255,255,0.2);
/* Fondo más claro */
        }
/* Cerramos lightbox-close:hover */
        
/* Línea vacía */
        /* Meta Info */
/* Comentario: Información meta (autor, fecha, etc.) */
        .meta-info {
/* Contenedor de meta info */
            background: var(--background);
/* Fondo gris */
            border-radius: 12px;
/* Bordes redondeados */
            padding: 25px;
/* Padding interno */
            margin-bottom: 30px;
/* Margen abajo */
            display: grid;
/* Grid layout */
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
/* Columnas automáticas */
            gap: 20px;
/* Espacio entre columnas */
        }
/* Cerramos meta-info */
        
/* Línea vacía */
        .meta-item {
/* Cada item de meta info */
            display: flex;
/* Flex layout */
            align-items: center;
/* Centrado vertical */
            gap: 10px;
/* Espacio entre icono y texto */
        }
/* Cerramos meta-item */
        
/* Línea vacía */
        .meta-item i {
/* Icono de meta item */
            font-size: 1.3rem;
/* Tamaño de icono */
            color: var(--primary);
/* Color azul */
        }
/* Cerramos meta-item i */
        
/* Línea vacía */
        .meta-item-content {
/* Contenido del meta item */
            flex: 1;
/* Ocupa el espacio restante */
        }
/* Cerramos meta-item-content */
        
/* Línea vacía */
        .meta-label {
/* Etiqueta (ej: Autor) */
            font-size: 0.85rem;
/* Tamaño pequeño */
            color: var(--text-light);
/* Color gris */
            margin-bottom: 2px;
/* Margen abajo */
        }
/* Cerramos meta-label */
        
/* Línea vacía */
        .meta-value {
/* Valor (ej: Juan Pérez) */
            font-weight: 600;
/* Negrita */
            color: var(--text);
/* Color oscuro */
        }
/* Cerramos meta-value */
        
/* Línea vacía */
        /* Responsive */
/* Comentario: Estilos para celulares */
        @media (max-width: 768px) {
/* Media query para pantallas pequeñas */
            .hero-title {
/* Título en celular */
                font-size: 2rem;
/* Más pequeño */
            }
/* Cerramos hero-title */
            
/* Línea vacía */
            .hero-description {
/* Descripción en celular */
                font-size: 1rem;
/* Más pequeña */
            }
/* Cerramos hero-description */
            
/* Línea vacía */
            .content-section {
/* Sección de contenido en celular */
                padding: 25px;
/* Menos padding */
            }
/* Cerramos content-section */
            
/* Línea vacía */
            .meta-info {
/* Meta info en celular */
                grid-template-columns: 1fr;
/* Una sola columna */
            }
/* Cerramos meta-info */
            
/* Línea vacía */
            .lightbox-close {
/* Botón cerrar en celular */
                top: 10px;
/* Más arriba */
                right: 10px;
/* Más a la derecha */
                font-size: 30px;
/* Más pequeño */
                width: 40px;
/* Ancho menor */
                height: 40px;
/* Alto menor */
            }
/* Cerramos lightbox-close */
        }
/* Cerramos media query */
    </style>
<!-- Cerramos style -->
</head>
<!-- Cerramos head -->

<!-- Línea vacía -->
<body>
<!-- Abrimos body -->
    <!-- Header -->
<!-- Comentario: Header de la página -->
    <header id="header" class="header position-relative">
<!-- Header con ID y clases -->
        <div class="container-fluid container-xl position-relative">
<!-- Contenedor fluido -->
            <div class="top-row d-flex align-items-center justify-content-between">
<!-- Fila superior flex -->
                <a href="index.php" class="logo d-flex align-items-end">
<!-- Logo link -->
                    <img src="assets/img/logo/logobrayan2.ico" alt="logo-lab">
<!-- Imagen del logo -->
                    <h1 class="sitename">Lab-Explora</h1><span></span>
<!-- Nombre del sitio -->
                </a>
<!-- Cerramos logo -->

<!-- Línea vacía -->
                <div class="d-flex align-items-center">
<!-- Contenedor flex -->
                    <div class="social-links">
<!-- Links sociales -->
                        <a href="#" title="Facebook"><i class="bi bi-facebook"></i></a>
<!-- Facebook -->
                        <a href="#" title="Twitter"><i class="bi bi-twitter"></i></a>
<!-- Twitter -->
                        <a href="#" title="Instagram"><i class="bi bi-instagram"></i></a>
<!-- Instagram -->
                        
<!-- Línea vacía -->
                        <?php if (isset($_SESSION['usuario_id'])): ?>
<!-- Si hay usuario logueado -->
                            <span class="saludo">Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
<!-- Saludo al usuario -->
                            <a href="forms/logout.php" class="btn-publicador">
<!-- Botón logout -->
                                <i class="bi bi-box-arrow-right"></i>
<!-- Icono logout -->
                                Cerrar Sesión
<!-- Texto logout -->
                            </a>
<!-- Cerramos botón -->
                        <?php else: ?>
<!-- Si no hay usuario -->
                            <a href="forms/inicio-sesion.php" class="btn-publicador">
<!-- Botón login -->
                                <i class="bi bi-box-arrow-in-right"></i>
<!-- Icono login -->
                                Inicia sesión
<!-- Texto login -->
                            </a>
<!-- Cerramos botón -->
                            <a href="forms/register.php" class="btn-publicador">
<!-- Botón registro -->
                                <i class="bi bi-person-plus"></i>
<!-- Icono registro -->
                                Crear Cuenta
<!-- Texto registro -->
                            </a>
<!-- Cerramos botón -->
                        <?php endif; ?>
<!-- Cerramos if -->
                        
<!-- Línea vacía -->
                        <span style="color: var(--border); margin: 0 5px;">|</span>
<!-- Separador -->
                        
<!-- Línea vacía -->
                        <a href="forms/publicadores/inicio-sesion-publicadores.php" class="btn-publicador">
<!-- Link publicadores -->
                            <i class="bi bi-pencil-square"></i>
<!-- Icono lápiz -->
                            ¿Eres publicador?
<!-- Texto link -->
                        </a>
<!-- Cerramos link -->
                    </div>
<!-- Cerramos social-links -->
                </div>
<!-- Cerramos contenedor flex -->
            </div>
<!-- Cerramos top-row -->
        </div>
<!-- Cerramos container -->
    </header>
<!-- Cerramos header -->

<!-- Línea vacía -->
    <!-- Hero Section -->
<!-- Comentario: Sección Hero -->
    <section class="hero-section">
<!-- Abrimos section -->
        <div class="hero-content">
<!-- Contenido hero -->
            <h1 class="hero-title"><?= htmlspecialchars($publicacion['titulo']) ?></h1>
<!-- Título de la publicación -->
            
<!-- Línea vacía -->
            <?php if(!empty($publicacion['resumen'])): ?>
<!-- Si hay resumen -->
            <p class="hero-description"><?= htmlspecialchars($publicacion['resumen']) ?></p>
<!-- Mostramos resumen -->
            <?php endif; ?>
<!-- Cerramos if -->
            
<!-- Línea vacía -->
            <?php if(!empty($publicacion['categoria_nombre'])): ?>
<!-- Si hay categoría -->
            <span class="category-badge">
<!-- Badge de categoría -->
                <i class="bi bi-folder-fill"></i> <?= htmlspecialchars($publicacion['categoria_nombre']) ?>
<!-- Icono y nombre -->
            </span>
            <span class="category-badge" style="background: #6c757d; margin-left: 10px;">
                <i class="bi bi-file-text"></i> <?= htmlspecialchars(ucfirst($publicacion['tipo'] ?? 'Artículo')) ?>
            </span>
<!-- Cerramos badge -->
            <?php endif; ?>
<!-- Cerramos if -->
        </div>
<!-- Cerramos hero-content -->
    </section>
<!-- Cerramos section -->

<!-- Línea vacía -->
    <!-- Main Content -->
<!-- Comentario: Contenido principal -->
    <main class="main-content">
<!-- Abrimos main -->
        
<!-- Línea vacía -->
        <!-- Meta Information -->
<!-- Comentario: Información meta -->
        <div class="meta-info">
<!-- Contenedor meta -->
            <div class="meta-item">
<!-- Item meta -->
                <i class="bi bi-person-circle"></i>
<!-- Icono persona -->
                <div class="meta-item-content">
<!-- Contenido item -->
                    <div class="meta-label">Autor</div>
<!-- Etiqueta Autor -->
                    <div class="meta-value"><?= htmlspecialchars($publicacion['publicador_nombre']) ?></div>
<!-- Nombre del autor -->
                </div>
<!-- Cerramos contenido -->
            </div>
<!-- Cerramos item -->
            
<!-- Línea vacía -->
            <div class="meta-item">
<!-- Item meta -->
                <i class="bi bi-calendar-event"></i>
<!-- Icono calendario -->
                <div class="meta-item-content">
<!-- Contenido item -->
                    <div class="meta-label">Fecha de publicación</div>
<!-- Etiqueta Fecha -->
                  <div class="meta-value"><?= date('d/m/Y', strtotime($publicacion['fecha_publicacion'] ?: $publicacion['fecha_creacion'])) ?></div>
<!-- Fecha formateada -->
            </div>
<!-- Cerramos contenido -->
            </div>
<!-- Cerramos item -->
            
<!-- Línea vacía -->
            <div class="meta-item">
<!-- Item meta -->
                <i class="bi bi-file-earmark-text"></i>
<!-- Icono archivo -->
                <div class="meta-item-content">
<!-- Contenido item -->
                    <div class="meta-label">Tipo de contenido</div>
<!-- Etiqueta Tipo -->
                    <div class="meta-value"><?= htmlspecialchars($publicacion['tipo'] ?: $publicacion['tipo']) ?></div>
<!-- Tipo de contenido (default Artículo) -->
                </div>
<!-- Cerramos contenido -->
            </div>
<!-- Cerramos item -->
        </div>
<!-- Cerramos meta-info -->

<!-- Línea vacía -->
        <!-- Main Content Section -->
<!-- Comentario: Sección de contenido principal -->
        <section class="content-section">
<!-- Abrimos section -->
            <div class="section-header">
<!-- Header de sección -->
                <h2 class="section-title"><?= htmlspecialchars($publicacion['titulo']) ?></h2>
<!-- Título -->
                <p class="section-subtitle">Información detallada y especializada</p>
<!-- Subtítulo -->
            </div>
<!-- Cerramos header -->
            
<!-- Línea vacía -->
            <article class="publication-content">
<!-- Artículo de contenido -->
                <?= procesarContenido($publicacion['contenido']) ?>
<!-- Imprimimos el contenido procesado -->
            </article>
<!-- Cerramos article -->
        </section>
<!-- Cerramos section -->

<!-- Línea vacía -->
    </main>
<!-- Cerramos main -->

<!-- Línea vacía -->
    <!-- Lightbox para imágenes -->
<!-- Comentario: Lightbox -->
    <div class="lightbox" id="lightbox" onclick="cerrarLightbox()">
<!-- Contenedor lightbox con evento click para cerrar -->
        <span class="lightbox-close">&times;</span>
<!-- Botón cerrar (X) -->
        <img class="lightbox-content" id="lightbox-img" src="">
<!-- Imagen del lightbox -->
    </div>
<!-- Cerramos lightbox -->

<!-- Línea vacía -->
    <script>
// Abrimos script
        // Función para abrir lightbox
        function abrirLightbox(src) {
// Función abrirLightbox recibe la ruta de la imagen
            const lightbox = document.getElementById('lightbox');
// Obtenemos el elemento lightbox
            const lightboxImg = document.getElementById('lightbox-img');
// Obtenemos la imagen del lightbox
            lightboxImg.src = src;
// Le ponemos la ruta a la imagen
            lightbox.classList.add('active');
// Le agregamos la clase active para mostrarlo
            document.body.style.overflow = 'hidden';
// Quitamos el scroll del body
        }
// Cerramos función

// Línea vacía
        // Función para cerrar lightbox
        function cerrarLightbox() {
// Función cerrarLightbox
            const lightbox = document.getElementById('lightbox');
// Obtenemos el lightbox
            lightbox.classList.remove('active');
// Le quitamos la clase active
            document.body.style.overflow = 'auto';
// Devolvemos el scroll al body
        }
// Cerramos función

// Línea vacía
        // Agregar evento click a todas las imágenes del contenido
        document.addEventListener('DOMContentLoaded', function() {
// Cuando cargue el documento
            const imagenes = document.querySelectorAll('.publication-content img');
// Buscamos todas las imágenes del contenido
            imagenes.forEach(img => {
// Recorremos cada imagen
                img.style.cursor = 'pointer';
// Le ponemos cursor de manita
                img.addEventListener('click', function() {
// Le agregamos evento click
                    abrirLightbox(this.src);
// Abrimos el lightbox con su ruta
                });
// Cerramos evento
            });
// Cerramos forEach
        });
// Cerramos DOMContentLoaded

// Línea vacía
        // Cerrar lightbox con tecla ESC
        document.addEventListener('keydown', function(e) {
// Escuchamos teclas presionadas
            if (e.key === 'Escape') {
// Si es Escape
                cerrarLightbox();
// Cerramos el lightbox
            }
// Cerramos if
        });
// Cerramos eventListener
    </script>
<!-- Cerramos script -->

<!-- Línea vacía -->
</body>
<!-- Cerramos body -->
</html>
<!-- Cerramos html -->
