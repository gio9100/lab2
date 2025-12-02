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
          "WHERE p.id = ? AND p.estado = 'publicado'";
// Solo queremos la publicación con ese ID y que esté publicada

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
// Definimos la función que recibe el contenido
    // Si el contenido ya tiene etiquetas HTML (del editor Quill), solo lo retornamos
    if (strip_tags($contenido) !== $contenido) {
// Si tiene etiquetas HTML, significa que ya viene con formato
        // Ya tiene HTML, solo aseguramos que las imágenes tengan las clases correctas
        $contenido = preg_replace('/<img(?![^>]*class=)/', '<img class="content-image"', $contenido);
// Le ponemos la clase "content-image" a las imágenes que no la tengan
        return $contenido;
// Devolvemos el contenido arreglado
    } else {
// Si no tiene HTML (es texto plano)
        // Contenido plano, convertir saltos de línea
        $contenido = nl2br(htmlspecialchars($contenido));
// Convertimos los enters en <br> y protegemos caracteres especiales
        
// Línea vacía
        // Buscar rutas de imágenes (uploads/contenido/...)
        $patron = '/uploads\/contenido\/[a-zA-Z0-9_\-\.]+\.(jpg|jpeg|png|gif|webp|jfif)/i';
// Expresión regular para encontrar rutas de imágenes en el texto
        
// Línea vacía
        // Reemplazar rutas por etiquetas <img>
        $contenido = preg_replace_callback($patron, function($matches) {
// Buscamos las rutas y las reemplazamos con una función
            $ruta = $matches[0];
// Agarramos la ruta que encontramos
            return '<img src="' . htmlspecialchars($ruta) . '" alt="Imagen de contenido" class="content-image" onclick="abrirLightbox(this.src)">';
// Devolvemos la etiqueta <img> completa con la ruta y el evento onclick para el lightbox
        }, $contenido);
// Cerramos el replace_callback
        
// Línea vacía
        return $contenido;
// Devolvemos el contenido final
    }
// Cerramos el else
}
// Cerramos la función

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
            --shadow-xl: 0 12px 30px rgba(0,0,0,0.15);
/* Sombra extra grande para mejor contraste */
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
        /* Hero Section Mejorada */
/* Comentario: Sección Hero mejorada con gradiente más atractivo */
        .hero-section {
/* Clase del hero */
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
/* Fondo degradado azul más atractivo */
            color: var(--white);
/* Texto blanco para mejor contraste */
            padding: 80px 0 60px;
/* Más padding arriba para destacar */
            text-align: center;
/* Texto centrado */
            position: relative;
/* Posición relativa para efectos */
            overflow: hidden;
/* Esconder desbordes */
        }
/* Cerramos hero-section */
        
/* Línea vacía */
        /* Efecto de partículas sutiles en el hero */
        .hero-section::before {
/* Pseudo-elemento para efecto de fondo */
            content: '';
/* Contenido vacío */
            position: absolute;
/* Posición absoluta */
            top: 0;
/* Arriba */
            left: 0;
/* Izquierda */
            right: 0;
/* Derecha */
            bottom: 0;
/* Abajo */
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.05"><circle cx="50" cy="50" r="2" fill="white"/></svg>') repeat;
/* Patrón de puntos sutiles */
            animation: float 20s infinite linear;
/* Animación flotante */
        }
/* Cerramos pseudo-elemento */
        
/* Línea vacía */
        @keyframes float {
/* Animación para el efecto de flotar */
            0% {
/* Inicio de animación */
                transform: translateY(0px);
/* Sin movimiento */
            }
/* Cerramos 0% */
            100% {
/* Fin de animación */
                transform: translateY(-100px);
/* Se mueve hacia arriba */
            }
/* Cerramos 100% */
        }
/* Cerramos keyframes */
        
/* Línea vacía */
        .hero-content {
/* Contenido del hero */
            max-width: 900px;
/* Ancho máximo */
            margin: 0 auto;
/* Centrado horizontal */
            padding: 0 20px;
/* Padding a los lados */
            position: relative;
/* Posición relativa para estar sobre el fondo */
            z-index: 2;
/* Z-index alto para estar encima del efecto */
        }
/* Cerramos hero-content */
        
/* Línea vacía */
        .hero-title {
/* Título del hero */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-size: 3rem;
/* Tamaño más grande para impacto */
            font-weight: 800;
/* Negrita más pesada */
            margin-bottom: 25px;
/* Margen abajo aumentado */
            line-height: 1.1;
/* Altura de línea ajustada */
            color: var(--white);
/* Color blanco */
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
/* Sombra de texto para mejor legibilidad */
        }
/* Cerramos hero-title */
        
/* Línea vacía */
        .hero-description {
/* Descripción en el hero */
            font-size: 1.3rem;
/* Tamaño aumentado */
            color: rgba(255,255,255,0.9);
/* Blanco semi-transparente */
            margin-bottom: 35px;
/* Margen abajo aumentado */
            line-height: 1.6;
/* Altura de línea */
            font-weight: 300;
/* Peso de fuente más ligero */
        }
/* Cerramos hero-description */
        
/* Línea vacía */
        .category-badge {
/* Badge de categoría mejorado */
            display: inline-block;
/* Bloque en línea */
            background: rgba(255,255,255,0.2);
/* Fondo blanco semi-transparente */
            color: var(--white);
/* Texto blanco */
            padding: 10px 25px;
/* Padding interno aumentado */
            border-radius: 25px;
/* Bordes más redondeados */
            font-size: 0.95rem;
/* Tamaño de fuente ligeramente mayor */
            font-weight: 600;
/* Negrita */
            backdrop-filter: blur(10px);
/* Efecto de desenfoque de fondo */
            border: 1px solid rgba(255,255,255,0.3);
/* Borde sutil */
            transition: all 0.3s ease;
/* Transición suave para hover */
        }
/* Cerramos category-badge */
        
/* Línea vacía */
        .category-badge:hover {
/* Efecto hover en badge */
            background: rgba(255,255,255,0.3);
/* Fondo más blanco al pasar mouse */
            transform: translateY(-2px);
/* Efecto de levantar */
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
/* Sombra aumentada */
        }
/* Cerramos category-badge:hover */
        
/* Línea vacía */
        /* Main Content Mejorado */
/* Comentario: Contenido principal con mejor espaciado */
        .main-content {
/* Clase main-content */
            max-width: 1200px;
/* Ancho máximo */
            margin: -50px auto 60px;
/* Margen negativo mayor arriba para que se meta más en el hero */
            padding: 0 20px;
/* Padding a los lados */
        }
/* Cerramos main-content */
        
/* Línea vacía */
        /* Content Section Mejorada */
/* Comentario: Sección de contenido con mejor diseño */
        .content-section {
/* Clase content-section */
            background: var(--white);
/* Fondo blanco */
            border-radius: 20px;
/* Bordes más redondeados */
            padding: 50px;
/* Padding interno aumentado */
            box-shadow: var(--shadow-xl);
/* Sombra más pronunciada */
            margin-bottom: 40px;
/* Margen abajo aumentado */
            border: 1px solid rgba(0,0,0,0.05);
/* Borde sutil para definición */
        }
/* Cerramos content-section */
        
/* Línea vacía */
        .section-header {
/* Encabezado de la sección mejorado */
            text-align: center;
/* Centrado */
            margin-bottom: 50px;
/* Margen abajo aumentado */
            padding-bottom: 30px;
/* Padding abajo */
            border-bottom: 2px solid var(--border);
/* Línea divisoria sutil */
        }
/* Cerramos section-header */
        
/* Línea vacía */
        .section-title {
/* Título de la sección mejorado */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-size: 2.2rem;
/* Tamaño aumentado */
            font-weight: 700;
/* Negrita */
            color: var(--primary-dark);
/* Color azul oscuro */
            margin-bottom: 15px;
/* Margen abajo */
            position: relative;
/* Posición relativa para efecto decorativo */
        }
/* Cerramos section-title */
        
/* Línea vacía */
        .section-title::after {
/* Línea decorativa bajo el título */
            content: '';
/* Contenido vacío */
            position: absolute;
/* Posición absoluta */
            bottom: -10px;
/* Posicionada bajo el texto */
            left: 50%;
/* Centrada horizontalmente */
            transform: translateX(-50%);
/* Ajuste de centrado */
            width: 80px;
/* Ancho de la línea */
            height: 3px;
/* Alto de la línea */
            background: var(--accent);
/* Color naranja de acento */
            border-radius: 2px;
/* Bordes redondeados */
        }
/* Cerramos pseudo-elemento */
        
/* Línea vacía */
        .section-subtitle {
/* Subtítulo mejorado */
            color: var(--text-light);
/* Color gris */
            font-size: 1.2rem;
/* Tamaño aumentado */
            font-weight: 300;
/* Peso de fuente más ligero */
            margin-top: 25px;
/* Margen arriba */
        }
/* Cerramos section-subtitle */
        
/* Línea vacía */
        .publication-content {
/* Contenido de la publicación mejorado */
            font-size: 1.1rem;
/* Tamaño de letra aumentado para mejor legibilidad */
            line-height: 1.8;
/* Altura de línea aumentada para leer mejor */
            color: var(--text);
/* Color de texto */
        }
/* Cerramos publication-content */
        
/* Línea vacía */
        /* Mejoras específicas para títulos dentro del contenido */
        .publication-content h1,
        .publication-content h2 {
/* Estilos mejorados para h1 y h2 dentro del contenido */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-size: 1.9rem;
/* Tamaño aumentado */
            font-weight: 700;
/* Negrita más pesada */
            color: var(--primary-dark);
/* Color azul oscuro */
            margin: 40px 0 20px;
/* Márgenes aumentados */
            padding-bottom: 10px;
/* Padding abajo */
            border-bottom: 1px solid var(--border);
/* Línea sutil bajo el título */
        }
/* Cerramos h1 y h2 */
        
/* Línea vacía */
        .publication-content h3 {
/* Estilos mejorados para h3 */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-size: 1.5rem;
/* Tamaño aumentado */
            font-weight: 600;
/* Negrita */
            color: var(--primary);
/* Color azul principal */
            margin: 35px 0 18px;
/* Márgenes aumentados */
        }
/* Cerramos h3 */
        
/* Línea vacía */
        .publication-content h4,
        .publication-content h5,
        .publication-content h6 {
/* Estilos mejorados para h4, h5, h6 */
            font-family: 'Nunito', sans-serif;
/* Fuente Nunito */
            font-weight: 600;
/* Negrita */
            color: var(--text);
/* Color texto */
            margin: 30px 0 15px;
/* Márgenes aumentados */
        }
/* Cerramos h4, h5, h6 */
        
/* Línea vacía */
        .publication-content p {
/* Estilos mejorados para párrafos */
            margin-bottom: 25px;
/* Margen abajo aumentado */
            text-align: justify;
/* Texto justificado para mejor apariencia */
        }
/* Cerramos p */
        
/* Línea vacía */
        .publication-content ul,
        .publication-content ol {
/* Estilos mejorados para listas */
            margin: 25px 0;
/* Margen arriba y abajo aumentado */
            padding-left: 35px;
/* Padding a la izquierda aumentado */
        }
/* Cerramos listas */
        
/* Línea vacía */
        .publication-content li {
/* Estilos mejorados para items de lista */
            margin-bottom: 12px;
/* Margen abajo aumentado */
            position: relative;
/* Posición relativa para iconos personalizados */
        }
/* Cerramos li */
        
/* Línea vacía */
        .publication-content ul li::before {
/* Icono personalizado para listas no ordenadas */
            content: 'â–¸';
/* Triángulo como marcador */
            color: var(--primary);
/* Color azul */
            font-weight: bold;
/* Negrita */
            position: absolute;
/* Posición absoluta */
            left: -20px;
/* Posicionado a la izquierda */
        }
/* Cerramos pseudo-elemento */
        
/* Línea vacía */
        .publication-content strong,
        .publication-content b {
/* Estilos mejorados para negritas */
            font-weight: 700;
/* Negrita más pesada */
            color: var(--text);
/* Color texto */
            background: linear-gradient(transparent 60%, rgba(115, 144, 160, 0.2) 40%);
/* Subrayado degradado sutil */
        }
/* Cerramos strong y b */
        
/* Línea vacía */
        .publication-content em,
        .publication-content i {
/* Estilos mejorados para itálicas */
            font-style: italic;
/* Itálica */
            color: var(--text-light);
/* Color gris para énfasis sutil */
        }
/* Cerramos em e i */
        
/* Línea vacía */
        .publication-content a {
/* Estilos mejorados para enlaces */
            color: var(--primary);
/* Color azul */
            text-decoration: none;
/* Sin subrayado por defecto */
            border-bottom: 2px solid var(--primary-light);
/* Línea inferior en lugar de subrayado */
            padding-bottom: 2px;
/* Padding abajo para separación */
            transition: all 0.3s ease;
/* Transición suave */
            font-weight: 500;
/* Peso de fuente medio */
        }
/* Cerramos a */
        
/* Línea vacía */
        .publication-content a:hover {
/* Hover mejorado en enlaces */
            color: var(--accent);
/* Cambia a naranja */
            border-bottom-color: var(--accent);
/* Línea inferior naranja */
            background-color: rgba(247, 88, 21, 0.05);
/* Fondo sutil naranja */
        }
/* Cerramos a:hover */
        
/* Línea vacía */
        /* Estilos mucho mejorados para imágenes en el contenido */
/* Comentario: Imágenes con mejor presentación y efectos */
        .publication-content img,
        .publication-content .content-image {
/* Imágenes dentro del contenido mejoradas */
            max-width: 100%;
/* Ancho máximo 100% */
            height: auto;
/* Altura automática */
            border-radius: 16px;
/* Bordes más redondeados */
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
/* Sombra más pronunciada */
            margin: 35px auto;
/* Margen aumentado y centrado */
            display: block;
/* Bloque para que se centre */
            cursor: pointer;
/* Cursor de manita */
            transition: all 0.4s ease;
/* Transición más larga para efectos suaves */
            border: 1px solid rgba(0,0,0,0.08);
/* Borde sutil para definición */
        }
/* Cerramos img */
        
/* Línea vacía */
        .publication-content img:hover,
        .publication-content .content-image:hover {
/* Hover mejorado en imágenes */
            transform: translateY(-5px) scale(1.02);
/* Se levanta y crece ligeramente */
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
/* Sombra más intensa */
        }
/* Cerramos img:hover */
        
/* Línea vacía */
        /* Contenedor especial para imágenes con pie de foto */
        .image-container {
/* Contenedor para imágenes con descripción */
            margin: 35px 0;
/* Margen arriba y abajo */
            text-align: center;
/* Texto centrado */
        }
/* Cerramos image-container */
        
/* Línea vacía */
        .image-caption {
/* Pie de foto para imágenes */
            font-style: italic;
/* Itálica */
            color: var(--text-light);
/* Color gris */
            margin-top: 10px;
/* Margen arriba */
            font-size: 0.9rem;
/* Tamaño pequeño */
            text-align: center;
/* Centrado */
        }
/* Cerramos image-caption */
        
/* Línea vacía */
        /* Lightbox Mejorado */
/* Comentario: Lightbox con mejor diseño y funcionalidad */
        .lightbox {
/* Contenedor del lightbox mejorado */
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
            background: rgba(0,0,0,0.95);
/* Fondo negro más opaco */
            justify-content: center;
/* Centrado horizontal */
            align-items: center;
/* Centrado vertical */
            opacity: 0;
/* Inicialmente transparente */
            transition: opacity 0.3s ease;
/* Transición de opacidad */
        }
/* Cerramos lightbox */
        
/* Línea vacía */
        .lightbox.active {
/* Cuando el lightbox está activo */
            display: flex;
/* Se muestra con flex */
            opacity: 1;
/* Opacidad completa */
        }
/* Cerramos lightbox.active */
        
/* Línea vacía */
        .lightbox-content {
/* La imagen dentro del lightbox mejorada */
            max-width: 90%;
/* Ancho máximo 90% */
            max-height: 90%;
/* Alto máximo 90% */
            object-fit: contain;
/* Ajuste de imagen */
            border-radius: 12px;
/* Bordes redondeados */
            box-shadow: 0 0 40px rgba(255,255,255,0.2);
/* Sombra blanca más suave */
            transform: scale(0.9);
/* Inicialmente más pequeña */
            transition: transform 0.3s ease;
/* Transición de transformación */
        }
/* Cerramos lightbox-content */
        
/* Línea vacía */
        .lightbox.active .lightbox-content {
/* Cuando el lightbox está activo, la imagen crece */
            transform: scale(1);
/* Tamaño normal */
        }
/* Cerramos lightbox.active .lightbox-content */
        
/* Línea vacía */
        .lightbox-close {
/* Botón de cerrar mejorado */
            position: absolute;
/* Posición absoluta */
            top: 25px;
/* Arriba 25px */
            right: 45px;
/* Derecha 45px */
            font-size: 45px;
/* Tamaño aumentado */
            color: white;
/* Color blanco */
            cursor: pointer;
/* Cursor de manita */
            background: rgba(0,0,0,0.6);
/* Fondo semitransparente más oscuro */
            width: 60px;
/* Ancho aumentado */
            height: 60px;
/* Alto aumentado */
            border-radius: 50%;
/* Redondo */
            display: flex;
/* Flex para centrar la X */
            align-items: center;
/* Centrado vertical */
            justify-content: center;
/* Centrado horizontal */
            transition: all 0.3s ease;
/* Transición */
            border: 2px solid rgba(255,255,255,0.3);
/* Borde sutil */
        }
/* Cerramos lightbox-close */
        
/* Línea vacía */
        .lightbox-close:hover {
/* Hover mejorado en cerrar */
            background: rgba(255,255,255,0.2);
/* Fondo más claro */
            transform: rotate(90deg);
/* Gira 90 grados */
            border-color: rgba(255,255,255,0.5);
/* Borde más visible */
        }
/* Cerramos lightbox-close:hover */
        
/* Línea vacía */
        /* Meta Info Mejorada */
/* Comentario: Información meta con mejor diseño */
        .meta-info {
/* Contenedor de meta info mejorado */
            background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
/* Fondo con gradiente sutil */
            border-radius: 16px;
/* Bordes más redondeados */
            padding: 30px;
/* Padding interno aumentado */
            margin-bottom: 40px;
/* Margen abajo aumentado */
            display: grid;
/* Grid layout */
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
/* Columnas automáticas con ancho mínimo aumentado */
            gap: 25px;
/* Espacio entre columnas aumentado */
            box-shadow: var(--shadow);
/* Sombra */
            border: 1px solid var(--border);
/* Borde definido */
        }
/* Cerramos meta-info */
        
/* Línea vacía */
        .meta-item {
/* Cada item de meta info mejorado */
            display: flex;
/* Flex layout */
            align-items: center;
/* Centrado vertical */
            gap: 15px;
/* Espacio entre icono y texto aumentado */
            padding: 15px;
/* Padding interno */
            border-radius: 12px;
/* Bordes redondeados */
            transition: all 0.3s ease;
/* Transición suave */
        }
/* Cerramos meta-item */
        
/* Línea vacía */
        .meta-item:hover {
/* Efecto hover en meta items */
            background: rgba(115, 144, 160, 0.05);
/* Fondo azul muy sutil */
            transform: translateY(-2px);
/* Efecto de levantar */
        }
/* Cerramos meta-item:hover */
        
/* Línea vacía */
        .meta-item i {
/* Icono de meta item mejorado */
            font-size: 1.5rem;
/* Tamaño de icono aumentado */
            color: var(--primary);
/* Color azul */
            background: rgba(115, 144, 160, 0.1);
/* Fondo azul muy sutil */
            width: 50px;
/* Ancho fijo */
            height: 50px;
/* Alto fijo */
            border-radius: 50%;
/* Circular */
            display: flex;
/* Flex para centrar */
            align-items: center;
/* Centrado vertical */
            justify-content: center;
/* Centrado horizontal */
        }
/* Cerramos meta-item i */
        
/* Línea vacía */
        .meta-item-content {
/* Contenido del meta item mejorado */
            flex: 1;
/* Ocupa el espacio restante */
        }
/* Cerramos meta-item-content */
        
/* Línea vacía */
        .meta-label {
/* Etiqueta mejorada (ej: Autor) */
            font-size: 0.9rem;
/* Tamaño ligeramente mayor */
            color: var(--text-light);
/* Color gris */
            margin-bottom: 5px;
/* Margen abajo aumentado */
            font-weight: 500;
/* Peso de fuente medio */
        }
/* Cerramos meta-label */
        
/* Línea vacía */
        .meta-value {
/* Valor mejorado (ej: Juan Pérez) */
            font-weight: 700;
/* Negrita más pesada */
            color: var(--text);
/* Color oscuro */
            font-size: 1.1rem;
/* Tamaño aumentado */
        }
/* Cerramos meta-value */
        
/* Línea vacía */
        /* Bloque de cita mejorado */
        .publication-content blockquote {
/* Estilos para citas o bloques de texto destacados */
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
/* Fondo gradiente sutil */
            border-left: 5px solid var(--primary);
/* Borde izquierdo azul */
            padding: 25px;
/* Padding interno */
            margin: 30px 0;
/* Margen arriba y abajo */
            border-radius: 0 12px 12px 0;
/* Bordes redondeados solo a la derecha */
            font-style: italic;
/* Itálica */
            color: var(--text-light);
/* Color gris */
            box-shadow: var(--shadow);
/* Sombra */
            position: relative;
/* Posición relativa para icono */
        }
/* Cerramos blockquote */
        
/* Línea vacía */
        .publication-content blockquote::before {
/* Icono de comillas */
            content: '"';
/* Comilla */
            font-size: 4rem;
/* Tamaño grande */
            color: var(--primary-light);
/* Color azul claro */
            position: absolute;
/* Posición absoluta */
            top: 10px;
/* Arriba */
            left: 20px;
/* Izquierda */
            opacity: 0.3;
/* Semi-transparente */
            font-family: serif;
/* Fuente serif para comillas */
        }
/* Cerramos pseudo-elemento */
        
/* Línea vacía */
        /* Responsive Mejorado */
/* Comentario: Estilos para celulares mejorados */
        @media (max-width: 768px) {
/* Media query para pantallas pequeñas */
            .hero-title {
/* Título en celular mejorado */
                font-size: 2.2rem;
/* Tamaño ajustado */
                line-height: 1.2;
/* Altura de línea ajustada */
            }
/* Cerramos hero-title */
            
/* Línea vacía */
            .hero-description {
/* Descripción en celular mejorada */
                font-size: 1.1rem;
/* Tamaño ajustado */
            }
/* Cerramos hero-description */
            
/* Línea vacía */
            .content-section {
/* Sección de contenido en celular mejorada */
                padding: 30px 20px;
/* Padding ajustado */
                margin: -40px 10px 30px;
/* Márgenes ajustados */
                border-radius: 16px;
/* Bordes ligeramente menos redondeados */
            }
/* Cerramos content-section */
            
/* Línea vacía */
            .meta-info {
/* Meta info en celular mejorada */
                grid-template-columns: 1fr;
/* Una sola columna */
                gap: 15px;
/* Espacio reducido */
                padding: 20px;
/* Padding reducido */
                margin: -30px 10px 30px;
/* Márgenes ajustados */
            }
/* Cerramos meta-info */
            
/* Línea vacía */
            .meta-item {
/* Items de meta en celular */
                padding: 12px;
/* Padding reducido */
            }
/* Cerramos meta-item */
            
/* Línea vacía */
            .section-title {
/* Título de sección en celular */
                font-size: 1.8rem;
/* Tamaño reducido */
            }
/* Cerramos section-title */
            
/* Línea vacía */
            .publication-content {
/* Contenido en celular */
                font-size: 1rem;
/* Tamaño de fuente estándar */
                text-align: left;
/* Alineación izquierda en móvil */
            }
/* Cerramos publication-content */
            
/* Línea vacía */
            .publication-content p {
/* Párrafos en celular */
                text-align: left;
/* Alineación izquierda */
            }
/* Cerramos p */
            
/* Línea vacía */
            .lightbox-close {
/* Botón cerrar en celular mejorado */
                top: 15px;
/* Más arriba */
                right: 15px;
/* Más a la derecha */
                font-size: 35px;
/* Más pequeño */
                width: 50px;
/* Ancho ajustado */
                height: 50px;
/* Alto ajustado */
            }
/* Cerramos lightbox-close */
            
/* Línea vacía */
            .publication-content h1,
            .publication-content h2 {
/* Títulos h1 y h2 en celular */
                font-size: 1.6rem;
/* Tamaño reducido */
            }
/* Cerramos h1 y h2 */
            
/* Línea vacía */
            .publication-content h3 {
/* Título h3 en celular */
                font-size: 1.3rem;
/* Tamaño reducido */
            }
/* Cerramos h3 */
        }
/* Cerramos media query */
        
/* Línea vacía */
        /* Estilos para pantallas muy pequeñas */
        @media (max-width: 480px) {
/* Media query para móviles muy pequeños */
            .hero-section {
/* Hero en móviles pequeños */
                padding: 60px 0 40px;
/* Padding reducido */
            }
/* Cerramos hero-section */
            
/* Línea vacía */
            .hero-title {
/* Título en móviles pequeños */
                font-size: 1.8rem;
/* Tamaño más pequeño */
            }
/* Cerramos hero-title */
            
/* Línea vacía */
            .main-content {
/* Contenido principal en móviles pequeños */
                margin: -30px auto 30px;
/* Márgenes ajustados */
            }
/* Cerramos main-content */
        }
/* Cerramos media query para móviles pequeños */
    </style>
<!-- Cerramos style -->
</head>
<!-- Cerramos head -->

<!-- Línea vacía -->
<body>
<!-- Abrimos body -->
    <!-- Header -->
<!-- Comentario: Header de la página (sin cambios para mantener identidad) -->
    <header id="header" class="header position-relative">
<!-- Header con ID y clases -->
        <div class="container-fluid container-xl position-relative">
<!-- Contenedor fluido -->
            <div class="top-row d-flex align-items-center justify-content-between">
<!-- Fila superior flex -->
                <a href="index.php" class="logo d-flex align-items-end">
<!-- Logo link -->
                    <img src="assets/img/logo/nuevologo.ico" alt="logo-lab">
<!-- Imagen del logo -->
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
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
                            Â¿Eres publicador?
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
    <!-- Hero Section Mejorada -->
<!-- Comentario: Sección Hero con nuevo diseño -->
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
<!-- Badge de categoría mejorado -->
                <i class="bi bi-folder-fill"></i> <?= htmlspecialchars($publicacion['categoria_nombre']) ?>
<!-- Icono y nombre -->
            </span>
<!-- Cerramos badge -->
            <?php endif; ?>
<!-- Cerramos if -->
        </div>
