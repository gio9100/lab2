<?php
// Esta línea abre el código PHP
require_once "./forms/usuario.php";
// Incluimos el archivo usuario.php que tiene funciones para manejar usuarios (esto también inicia la sesión)
require_once "./forms/conexion.php";
// Incluimos el archivo conexion.php que conecta con la base de datos

// Línea vacía para separar secciones
$publicaciones = [];
// Creamos una variable llamada $publicaciones que es un array vacío
$sql = "SELECT p.id, p.titulo, p.contenido, p.resumen, p.fecha_publicacion, p.fecha_creacion, p.imagen_principal, " .
// Empezamos a escribir una consulta SQL que va a traer datos de la base de datos (agregamos fecha_creacion)
               "c.nombre AS categoria_nombre, pub.nombre AS autor_nombre " .
// Continuamos la consulta, pidiendo el nombre de la categoría y del autor
        "FROM publicaciones p " .
// Le decimos que busque en la tabla "publicaciones" y la llamamos "p" para abreviar
        "LEFT JOIN categorias c ON p.categoria_id = c.id " .
// Unimos la tabla categorias para saber a qué categoría pertenece cada publicación
        "LEFT JOIN publicadores pub ON p.publicador_id = pub.id " .
// Unimos la tabla publicadores para saber quién escribió cada publicación
        "WHERE p.estado = 'publicado' " .
// Solo queremos las publicaciones que estén publicadas (no borradores)
        "ORDER BY c.nombre, p.fecha_publicacion DESC";
// Ordenamos primero por nombre de categoría y luego por fecha (más recientes primero)

// Línea vacía
$result = $conexion->query($sql);
// Ejecutamos la consulta SQL en la base de datos y guardamos el resultado en $result

// Línea vacía
if ($result) {
// Si la consulta funcionó (si hay resultados)
    while ($fila = $result->fetch_assoc()) {
// Mientras haya filas en el resultado, las vamos sacando una por una
        $categoria = $fila["categoria_nombre"] ?? "Sin categoría";
// Guardamos el nombre de la categoría, si no tiene le ponemos "Sin categoría"
        if (!isset($publicaciones[$categoria])) {
// Si esta categoría todavía no existe en nuestro array
            $publicaciones[$categoria] = [];
// Creamos un array vacío para esta categoría
        }
// Cerramos el if
        $publicaciones[$categoria][] = $fila;
// Agregamos esta publicación al array de su categoría
    }
// Cerramos el while
}
// Cerramos el if

// Línea vacía
// Obtener todas las categorías que tienen publicaciones para los filtros
$categorias_filtro = [];
// Creamos un array vacío para guardar las categorías
$sql_categorias = "SELECT DISTINCT c.id, c.nombre FROM categorias c " .
// Consulta SQL para traer categorías distintas
                  "INNER JOIN publicaciones p ON c.id = p.categoria_id " .
// Solo categorías que tienen publicaciones
                  "WHERE p.estado = 'publicado' " .
// Solo publicaciones publicadas
                  "ORDER BY c.nombre";
// Ordenadas alfabéticamente
$result_categorias = $conexion->query($sql_categorias);
// Ejecutamos la consulta
if ($result_categorias) {
// Si la consulta funcionó
    while ($cat = $result_categorias->fetch_assoc()) {
// Recorremos cada categoría
        $categorias_filtro[] = $cat;
// La agregamos al array
    }
// Cerramos el while
}
// Cerramos el if
    $cat = strtolower($categoria ?? "");
// Convertimos el nombre de la categoría a minúsculas para comparar
function acortar($texto, $limite = 150) {
// Definimos una función para acortar textos largos, por defecto corta a 150 caracteres
    $texto = strip_tags($texto);
// Quitamos todas las etiquetas HTML del texto
    return strlen($texto) > $limite ? substr($texto, 0, $limite) . "..." : $texto;
// Si el texto es más largo que el límite, lo cortamos y agregamos "...", si no, lo dejamos igual
}
// Cerramos la función acortar
?>
<!-- Cerramos el código PHP -->
<!DOCTYPE html>
<!-- Declaramos que este es un documento HTML5 -->
<html lang="es">
<!-- Abrimos la etiqueta HTML y le decimos que el idioma es español -->
<head>
<!-- Abrimos la sección head donde van los metadatos -->
    <meta charset="UTF-8">
<!-- Definimos que el charset es UTF-8 para que se vean bien los acentos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Hacemos que la página sea responsive (se adapte a celulares) -->
    <title>Lab-Explorer - Publicaciones</title>
<!-- Título que aparece en la pestaña del navegador -->

    <!-- Fonts -->
<!-- Comentario que indica que aquí van las fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
<!-- Pre-conectamos con Google Fonts para que cargue más rápido -->
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
<!-- Pre-conectamos con el CDN de Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<!-- Cargamos las fuentes Roboto, Poppins y Nunito de Google Fonts -->
    

    <!-- Bootstrap Icons -->
<!-- Comentario que indica que aquí van los iconos de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<!-- Cargamos los iconos de Bootstrap desde un CDN -->
    

    <!-- Bootstrap CSS -->
<!-- Comentario que indica que aquí va el CSS de Bootstrap -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<!-- Cargamos el framework Bootstrap para que todo se vea ordenado -->
    

    <!-- Main CSS -->
    <link href="assets/css/main.css" rel="stylesheet">
</head>
<!-- Cerramos la etiqueta head -->
 <style>
        /* Abrimos una etiqueta style para escribir CSS personalizado */

 :root {
    /* Definimos variables CSS globales */
    --primary: #7390A0;
    /* Variable para el color azul principal del sitio */
    --primary-dark: #5a7080;
    /* Variable para el azul más oscuro (cuando pasas el mouse) */
    --accent: #f75815;
    /* Variable para el color naranja de acentos */
    --text: #212529;
    /* Variable para el color del texto normal (casi negro) */
    --text-light: #6c757d;
    /* Variable para el color del texto secundario (gris) */
    --background: #f8f9fa;
    /* Variable para el color de fondo de la página (gris muy claro) */
    --white: #ffffff;
    /* Variable para el color blanco puro */
    --border: #e9ecef;
    /* Variable para el color de los bordes (gris claro) */
    --shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    /* Variable para la sombra normal de las tarjetas */
    --shadow-hover: 0 8px 20px rgba(0, 0, 0, 0.12);
    /* Variable para la sombra cuando pasas el mouse sobre las tarjetas */
}


/* Cerramos las variables CSS */


/* Línea vacía */

* {
    /* Selector universal que afecta a TODOS los elementos */
    margin: 0;
    /* Quitamos todos los márgenes por defecto */
    padding: 0;
    /* Quitamos todo el padding por defecto */
    box-sizing: border-box;
    /* Hacemos que el padding y border no agranden las cajas */
}


/* Cerramos el selector universal */


/* Línea vacía */

body {
    /* Estilos para el body (todo el documento) */
    font-family: 'Inter', sans-serif;
    /* Ponemos el color de fondo usando la variable */
    color: var(--text);
    /* Ponemos el color del texto usando la variable */
    line-height: 1.6;
    /* Espaciado entre líneas de texto */
}


/* Cerramos los estilos del body */


/* Línea vacía */


/* Main Content */


/* Comentario CSS para la sección de contenido principal */

.main-content {
    /* Clase para el contenedor principal de todo el contenido */
    max-width: 1400px;
    /* Ancho máximo de 1400px para que no se vea muy estirado en pantallas grandes */
    margin: 0 auto;
    /* Centramos el contenedor horizontalmente */
    padding: 40px 20px;
    /* Espaciado interno: 40px arriba/abajo, 20px izquierda/derecha */
}


/* Cerramos la clase main-content */


/* Línea vacía */

.page-title {
    /* Clase para el título de la página */
    text-align: center;
    /* Centramos el texto */
    margin-bottom: 50px;
    /* Espacio de 50px abajo del título */
}


/* Cerramos la clase page-title */


/* Línea vacía */

.page-title h1 {
    /* Estilos para el h1 dentro de page-title */
    font-family: 'Nunito', sans-serif;
    /* Usamos la fuente Nunito para el título */
    font-size: 2.5rem;
    /* Tamaño de fuente grande (2.5 veces el tamaño base) */
    color: var(--text);
    /* Color del texto usando la variable */
    margin-bottom: 10px;
    /* Espacio de 10px abajo del h1 */
    font-weight: 600;
    /* Grosor del texto (semi-bold) */
}


/* Cerramos los estilos del h1 */


/* Línea vacía */

.page-title p {
    /* Estilos para el párrafo dentro de page-title */
    color: var(--text-light);
    /* Color gris claro para el texto */
    font-size: 1.1rem;
    /* Tamaño un poco más grande que el normal */
}


/* Cerramos los estilos del párrafo */


/* Línea vacía */


/* Category Section */


/* Comentario CSS para la sección de categorías */

.category-section {
    /* Clase para cada sección de categoría */
    margin-bottom: 60px;
    /* Espacio de 60px entre cada categoría */
}


/* Cerramos la clase category-section */


/* Línea vacía */

.category-header {
    /* Clase para el encabezado de cada categoría */
    display: flex;
    /* Usamos flexbox para alinear el icono y el texto */
    align-items: center;
    /* Centramos verticalmente los elementos */
    gap: 15px;
    /* Espacio de 15px entre el icono y el texto */
    margin-bottom: 30px;
    /* Espacio de 30px abajo del encabezado */
    padding-top: 20px;
    /* Espacio de 20px arriba del encabezado */
    padding-bottom: 15px;
    /* Espacio de 15px abajo del encabezado */
    border-top: 3px solid var(--primary);
    /* Línea azul de 3px arriba para separar categorías */
}


/* Cerramos la clase category-header */


/* Línea vacía */

.category-header i {
    /* Estilos para el icono dentro del encabezado */
    font-size: 1.8rem;
    /* Tamaño del icono */
    color: var(--primary);
    /* Color azul del icono */
}


/* Cerramos los estilos del icono */


/* Línea vacía */

.category-header h2 {
    /* Estilos para el h2 dentro del encabezado */
    font-family: 'Nunito', sans-serif;
    /* Usamos la fuente Nunito */
    font-size: 1.8rem;
    /* Tamaño del título de categoría */
    color: var(--text);
    /* Color del texto */
    margin: 0;
    /* Sin margen */
    font-weight: 600;
    /* Grosor del texto (semi-bold) */
}


/* Cerramos los estilos del h2 */


/* Línea vacía */


/* Publication Grid */


/* Comentario CSS para el grid de publicaciones */

.publications-grid {
    /* Clase para el contenedor de las tarjetas */
    display: grid;
    /* Usamos CSS Grid para organizar las tarjetas */
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    /* Columnas automáticas de mínimo 320px que se ajustan al espacio */
    gap: 30px;
    /* Espacio de 30px entre cada tarjeta */
}


/* Cerramos la clase publications-grid */


/* Línea vacía */


/* Publication Card */


/* Comentario CSS para las tarjetas de publicación */

.publication-card {
    /* Clase para cada tarjeta de publicación */
    background: var(--white);
    /* Fondo blanco */
    border-radius: 16px;
    /* Esquinas redondeadas de 16px */
    overflow: hidden;
    /* Escondemos lo que se salga de la tarjeta */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    /* Sombra más pronunciada */
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    /* Animación suave con curva personalizada */
    display: flex;
    /* Usamos flexbox */
    flex-direction: column;
    /* Los elementos van en columna (uno abajo del otro) */
    height: 100%;
    /* Altura del 100% del contenedor */
    border: 1px solid rgba(115, 144, 160, 0.1);
    /* Borde sutil con el color principal */
}


/* Cerramos la clase publication-card */


/* Línea vacía */

.publication-card:hover {
    /* Estilos cuando pasas el mouse sobre la tarjeta */
    transform: translateY(-12px);
    /* Movemos la tarjeta 12px hacia arriba (más que antes) */
    box-shadow: 0 15px 40px rgba(115, 144, 160, 0.2);
    /* Sombra más grande con tinte azul */
    border-color: rgba(115, 144, 160, 0.3);
    /* Borde más visible */
}


/* Cerramos el hover de la tarjeta */


/* Línea vacía */

.card-image {
    /* Clase para el contenedor de la imagen */
    position: relative;
    /* Posición relativa para poder poner el badge encima */
    width: 100%;
    /* Ancho del 100% */
    height: 220px;
    /* Altura fija de 220px */
    overflow: hidden;
    /* Escondemos lo que se salga */
}


/* Cerramos la clase card-image */


/* Línea vacía */

.card-image img {
    /* Estilos para la imagen dentro de card-image */
    width: 100%;
    /* Ancho del 100% */
    height: 100%;
    /* Altura del 100% */
    object-fit: cover;
    /* La imagen cubre todo el espacio sin deformarse */
    transition: transform 0.3s ease;
    /* Animación suave para el zoom */
}


/* Cerramos los estilos de la imagen */


/* Línea vacía */

.publication-card:hover .card-image img {
    /* Estilos para la imagen cuando pasas el mouse sobre la tarjeta */
    transform: scale(1.1);
    /* Hacemos zoom a la imagen (110% del tamaño original) --> */
}


/* Cerramos el hover de la imagen */


/* Línea vacía */

.category-badge {
    /* Clase para el badge de categoría que va sobre la imagen */
    position: absolute;
    /* Posición absoluta para ponerlo encima de la imagen */
    top: 15px;
    /* 15px desde arriba */
    right: 15px;
    /* 15px desde la derecha */
    background: linear-gradient(135deg, #7390A0 0%, #5a7080 100%);
    /* Fondo con degradado azul */
    color: var(--white);
    /* Texto blanco */
    padding: 8px 16px;
    /* Espaciado interno: 8px arriba/abajo, 16px izquierda/derecha */
    border-radius: 25px;
    /* Esquinas muy redondeadas (forma de píldora) */
    font-size: 0.85rem;
    /* Tamaño de fuente pequeño */
    font-weight: 600;
    /* Texto semi-bold */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    /* Sombra más pronunciada para que se vea sobre la imagen */
    backdrop-filter: blur(10px);
    /* Efecto de desenfoque del fondo */
}


/* Cerramos la clase category-badge */


/* Línea vacía */

.card-content {
    /* Clase para el contenido de la tarjeta (todo menos la imagen) */
    padding: 25px;
    /* Espaciado interno de 25px */
    flex: 1;
    /* Ocupa todo el espacio disponible */
    display: flex;
    /* Usamos flexbox */
    flex-direction: column;
    /* Elementos en columna */
}


/* Cerramos la clase card-content */


/* Línea vacía */

.card-title {
    /* Clase para el título de la publicación */
    font-family: 'Nunito', sans-serif;
    /* Usamos la fuente Nunito */
    font-size: 1.4rem;
    /* Tamaño del título */
    font-weight: 700;
    /* Texto bold (grueso) */
    color: var(--text);
    /* Color del texto */
    margin-bottom: 12px;
    /* Espacio de 12px abajo */
    line-height: 1.3;
    /* Espaciado entre líneas */
}


/* Cerramos la clase card-title */


/* Línea vacía */

.card-excerpt {
    /* Clase para el resumen/extracto de la publicación */
    color: var(--text-light);
    /* Color gris claro */
    font-size: 0.95rem;
    /* Tamaño un poco más pequeño */
    margin-bottom: 20px;
    /* Espacio de 20px abajo */
    line-height: 1.6;
    /* Espaciado entre líneas */
    flex: 1;
    /* Ocupa el espacio disponible */
}


/* Cerramos la clase card-excerpt */


/* Línea vacía */

.card-meta {
    /* Clase para el contenedor de metadatos (autor y fecha) */
    display: flex;
    /* Usamos flexbox */
    flex-direction: column;
    /* Elementos en columna */
    gap: 8px;
    /* Espacio de 8px entre elementos */
    margin-bottom: 20px;
    /* Espacio de 20px abajo */
    padding-top: 15px;
    /* Espacio de 15px arriba */
    border-top: 1px solid var(--border);
    /* Línea separadora arriba */
}


/* Cerramos la clase card-meta */


/* Línea vacía */

.meta-item {
    /* Clase para cada item de metadatos */
    display: flex;
    /* Usamos flexbox */
    align-items: center;
    /* Centramos verticalmente */
    gap: 8px;
    /* Espacio de 8px entre icono y texto */
    font-size: 0.9rem;
    /* Tamaño de fuente pequeño */
    color: var(--text-light);
    /* Color gris claro */
}


/* Cerramos la clase meta-item */


/* Línea vacía */

.meta-item i {
    /* Estilos para el icono dentro de meta-item */
    color: var(--primary);
    /* Color azul */
    font-size: 1rem;
    /* Tamaño del icono */
}


/* Cerramos los estilos del icono */


/* Línea vacía */

.card-footer {
    /* Clase para el footer de la tarjeta (donde va el botón) */
    padding-top: 15px;
    /* Espacio de 15px arriba */
}


/* Cerramos la clase card-footer */


/* Línea vacía */

.btn-read-more {
    /* Clase para el botón de "Leer más" */
    display: inline-flex;
    /* Usamos inline-flex para que sea un botón flexible en línea */
    align-items: center;
    /* Centramos verticalmente el contenido del botón */
    gap: 8px;
    /* Espacio de 8px entre el texto y el icono */
    padding: 12px 28px;
    /* Espaciado interno: 12px arriba/abajo, 28px izquierda/derecha */
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    /* Fondo con degradado azul del color principal al oscuro */
    color: var(--white);
    /* Texto blanco */
    text-decoration: none;
    /* Sin subrayado */
    border-radius: 25px;
    /* Bordes muy redondeados (forma de píldora) */
    font-weight: 600;
    /* Texto semi-bold */
    transition: all 0.3s ease;
    /* Transición suave para todos los cambios */
    box-shadow: 0 4px 12px rgba(115, 144, 160, 0.3);
    /* Sombra azulada para dar profundidad */
}


/* Cerramos la clase btn-read-more */


/* Línea vacía */

.btn-read-more:hover {
    /* Estilos cuando pasas el mouse sobre el botón */
    transform: translateY(-2px);
    /* Movemos el botón 2px hacia arriba */
    box-shadow: 0 6px 20px rgba(115, 144, 160, 0.4);
    /* Sombra más grande y pronunciada */
    color: var(--white);
    /* Mantenemos el texto blanco */
}


/* Cerramos el hover del botón */


/* Línea vacía */

.btn-read-more i {
    /* Estilos para el icono dentro del botón */
    transition: transform 0.3s ease;
    /* Transición suave para el icono */
}


/* Cerramos los estilos del icono */


/* Línea vacía */

.btn-read-more:hover i {
    /* Estilos para el icono cuando pasas el mouse sobre el botón */
    transform: translateX(5px);
    /* Movemos el icono 5px a la derecha (efecto de flecha) */
}


/* Cerramos el hover del icono */


/* Línea vacía */


/* Empty State */


/* Comentario CSS para el estado vacío */

.empty-state {
    /* Clase para cuando no hay publicaciones */
    text-align: center;
    /* Centramos el texto */
    padding: 60px 20px;
    /* Espaciado interno: 60px arriba/abajo, 20px izquierda/derecha */
    color: var(--text-light);
    /* Color gris claro */
}


/* Cerramos la clase empty-state */


/* Línea vacía */

.empty-state i {
    /* Estilos para el icono del estado vacío */
    font-size: 4rem;
    /* Tamaño muy grande */
    color: var(--border);
    /* Color gris muy claro */
    margin-bottom: 20px;
    /* Espacio de 20px abajo */
}


/* Cerramos los estilos del icono */


/* Línea vacía */


/* Search and Filter Section - DISEÑO MEJORADO */


/* Comentario CSS para la sección de búsqueda y filtros mejorada */

.search-filter-section {
    /* Contenedor principal de búsqueda y filtros */
    max-width: 1200px;
    /* Ancho máximo */
    margin: 0 auto 50px;
    /* Centrado y más margen abajo */
    padding: 30px 20px;
    /* Más padding */
    background: linear-gradient(135deg, rgba(115, 144, 160, 0.05) 0%, rgba(115, 144, 160, 0.12) 100%);
    /* Fondo degradado azul muy sutil */
    border-radius: 20px;
    /* Bordes redondeados */
    box-shadow: 0 4px 20px rgba(115, 144, 160, 0.1);
    /* Sombra azulada sutil */
    border: 1px solid rgba(115, 144, 160, 0.1);
    /* Borde azul muy sutil */
}


/* Cerramos search-filter-section */


/* Línea vacía */

.search-box {
    /* Contenedor del buscador */
    position: relative;
    /* Posición relativa para el icono */
    max-width: 700px;
    /* Ancho máximo más grande */
    margin: 0 auto 25px;
    /* Centrado y margen abajo */
}


/* Cerramos search-box */


/* Línea vacía */

.search-icon {
    /* Icono de lupa */
    position: absolute;
    /* Posición absoluta dentro del contenedor */
    left: 25px;
    /* 25px desde la izquierda */
    top: 50%;
    /* Centrado verticalmente */
    transform: translateY(-50%);
    /* Ajuste de centrado */
    color: var(--primary);
    /* Color azul principal */
    font-size: 1.3rem;
    /* Tamaño del icono más grande */
    pointer-events: none;
    /* No interfiere con el input */
    z-index: 2;
    /* Encima del input */
    transition: all 0.3s ease;
    /* Transición suave */
}


/* Cerramos search-icon */


/* Línea vacía */

.search-input {
    /* Campo de texto del buscador */
    width: 100%;
    /* Ancho completo */
    padding: 18px 25px 18px 60px;
    /* Padding con espacio para el icono */
    border: 2px solid rgba(115, 144, 160, 0.2);
    /* Borde azul sutil */
    border-radius: 50px;
    /* Bordes muy redondeados (píldora) */
    font-size: 1.05rem;
    /* Tamaño de fuente */
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    /* Transición suave con curva personalizada */
    background: white;
    /* Fondo blanco */
    color: var(--text);
    /* Color de texto */
    box-shadow: 0 4px 15px rgba(115, 144, 160, 0.15), inset 0 1px 3px rgba(0, 0, 0, 0.05);
    /* Sombra azulada y sombra interna */
    font-weight: 500;
    /* Peso medio */
}


/* Cerramos search-input */


/* Línea vacía */

.search-input::placeholder {
    /* Placeholder del input */
    color: var(--text-light);
    /* Color gris */
    font-weight: 400;
    /* Peso normal */
}


/* Cerramos placeholder */


/* Línea vacía */

.search-input:focus {
    /* Cuando el input tiene foco (está activo) */
    outline: none;
    /* Quitamos el outline por defecto */
    border-color: var(--primary);
    /* Borde azul */
    box-shadow: 0 8px 30px rgba(115, 144, 160, 0.25), 0 0 0 4px rgba(115, 144, 160, 0.1), inset 0 1px 3px rgba(0, 0, 0, 0.05);
    /* Sombra azul más intensa, anillo de foco y sombra interna */
    transform: translateY(-2px);
    /* Efecto de levantar */
}


/* Cerramos search-input:focus */


/* Línea vacía */

.search-input:focus+.search-icon {
    /* Icono cuando el input tiene foco */
    color: var(--primary-dark);
    /* Azul más oscuro */
    transform: translateY(-50%) scale(1.1);
    /* Agrandar ligeramente */
}


/* Cerramos focus del icono */


/* Línea vacía */

.category-filters {
    /* Contenedor de los botones de categorías */
    display: flex;
    /* Flexbox */
    flex-wrap: wrap;
    /* Permitir que se envuelvan */
    gap: 15px;
    /* Más espacio entre botones */
    justify-content: center;
    /* Centrar los botones */
}


/* Cerramos category-filters */


/* Línea vacía */

.category-filter-btn {
    /* Botón de filtro de categoría */
    padding: 12px 24px;
    /* Más padding interno */
    border: 2px solid rgba(115, 144, 160, 0.25);
    /* Borde azul sutil */
    border-radius: 30px;
    /* Bordes más redondeados */
    background: linear-gradient(135deg, white 0%, rgba(115, 144, 160, 0.03) 100%);
    /* Degradado blanco a azul muy sutil */
    color: var(--text);
    /* Texto oscuro */
    font-size: 0.95rem;
    /* Tamaño de fuente */
    font-weight: 600;
    /* Peso más pesado */
    cursor: pointer;
    /* Cursor de manita */
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    /* Transición suave con curva personalizada */
    display: flex;
    /* Flexbox para alinear icono y texto */
    align-items: center;
    /* Centrar verticalmente */
    gap: 8px;
    /* Espacio entre icono y texto */
    box-shadow: 0 3px 10px rgba(115, 144, 160, 0.12);
    /* Sombra azulada sutil */
    position: relative;
    /* Posición relativa para efectos */
    overflow: hidden;
    /* Ocultar desbordamiento para efecto */
}


/* Cerramos category-filter-btn */


/* Línea vacía */

.category-filter-btn::before {
    /* Pseudo-elemento para efecto de fondo */
    content: '';
    /* Contenido vacío */
    position: absolute;
    /* Posición absoluta */
    top: 0;
    /* Arriba */
    left: -100%;
    /* Fuera de la vista a la izquierda */
    width: 100%;
    /* Ancho completo */
    height: 100%;
    /* Alto completo */
    background: linear-gradient(90deg, transparent, rgba(115, 144, 160, 0.15), transparent);
    /* Degradado de brillo */
    transition: left 0.5s ease;
    /* Transición del movimiento */
}


/* Cerramos pseudo-elemento */


/* Línea vacía */

.category-filter-btn::after {
    /* Segundo pseudo-elemento para brillo adicional */
    content: '';
    /* Contenido vacío */
    position: absolute;
    /* Posición absoluta */
    top: -50%;
    /* Arriba fuera de vista */
    left: -50%;
    /* Izquierda fuera de vista */
    width: 200%;
    /* Doble de ancho */
    height: 200%;
    /* Doble de alto */
    background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
    /* Degradado radial de brillo */
    opacity: 0;
    /* Invisible por defecto */
    transition: opacity 0.3s ease;
    /* Transición de opacidad */
}


/* Cerramos segundo pseudo-elemento */


/* Línea vacía */

.category-filter-btn:hover {
    /* Hover del botón */
    border-color: var(--primary);
    /* Borde azul */
    background: linear-gradient(135deg, rgba(115, 144, 160, 0.08) 0%, rgba(115, 144, 160, 0.15) 100%);
    /* Fondo degradado azul más visible */
    transform: translateY(-3px);
    /* Efecto de levantar más pronunciado */
    box-shadow: 0 10px 25px rgba(115, 144, 160, 0.25);
    /* Sombra más grande */
}


/* Cerramos category-filter-btn:hover */


/* Línea vacía */

.category-filter-btn:hover::before {
    /* Efecto de brillo al hacer hover */
    left: 100%;
    /* Se mueve a la derecha */
}


/* Cerramos hover del pseudo-elemento */


/* Línea vacía */

.category-filter-btn:hover::after {
    /* Brillo adicional al hover */
    opacity: 1;
    /* Visible */
}


/* Cerramos hover del segundo pseudo-elemento */


/* Línea vacía */

.category-filter-btn.active {
    /* Botón activo */
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    /* Degradado azul */
    color: white;
    /* Texto blanco */
    border-color: var(--primary);
    /* Borde azul */
    box-shadow: 0 8px 25px rgba(115, 144, 160, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.2);
    /* Sombra azulada más pronunciada y brillo interno */
    transform: translateY(-2px);
    /* Ligeramente levantado */
}


/* Cerramos category-filter-btn.active */


/* Línea vacía */

.category-filter-btn.active::before {
    /* Pseudo-elemento del botón activo */
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    /* Brillo blanco */
}


/* Cerramos pseudo-elemento activo */


/* Línea vacía */

.category-filter-btn.active:hover {
    /* Hover del botón activo */
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    /* Degradado azul invertido */
    transform: translateY(-4px) scale(1.02);
    /* Más levantado y ligeramente más grande */
    box-shadow: 0 12px 35px rgba(115, 144, 160, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.3);
    /* Sombra aún más grande y más brillo interno */
}


/* Cerramos category-filter-btn.active:hover */


/* Línea vacía */

.category-filter-btn i {
    /* Iconos en los botones */
    font-size: 1.1rem;
    /* Tamaño del icono */
    transition: transform 0.3s ease;
    /* Transición para animación */
}


/* Cerramos icono */


/* Línea vacía */

.category-filter-btn:hover i {
    /* Icono al hacer hover */
    transform: scale(1.2) rotate(5deg);
    /* Agrandar y rotar ligeramente */
}


/* Cerramos hover del icono */


/* Línea vacía */

.category-filter-btn.active i {
    /* Icono del botón activo */
    animation: pulse-icon 0.6s ease;
    /* Animación de pulso */
}


/* Cerramos icono activo */


/* Línea vacía */

@keyframes pulse-icon {
    /* Animación de pulso para el icono */
    0%,
    100% {
        /* Inicio y fin */
        transform: scale(1);
        /* Tamaño normal */
    }
    /* Cerramos 0% y 100% */
    50% {
        /* Mitad de la animación */
        transform: scale(1.3);
        /* Más grande */
    }
    /* Cerramos 50% */
}


/* Cerramos keyframes */


/* Línea vacía */


/* Responsive */


/* Comentario CSS para estilos responsive */

@media (max-width: 768px) {
    /* Media query para pantallas de máximo 768px (celulares y tablets) */
    .page-title h1 {
        /* Estilos para el h1 en pantallas pequeñas */
        font-size: 2rem;
        /* Hacemos el título más pequeño */
    }
    /* Cerramos los estilos del h1 */
    /* Línea vacía */
    .category-header h2 {
        /* Estilos para el h2 en pantallas pequeñas */
        font-size: 1.5rem;
        /* Hacemos el título de categoría más pequeño */
    }
    /* Cerramos los estilos del h2 */
    /* Línea vacía */
    .publications-grid {
        /* Estilos para el grid en pantallas pequeñas */
        grid-template-columns: 1fr;
        /* Solo una columna en celulares */
        gap: 20px;
        /* Menos espacio entre tarjetas */
    }
    /* Cerramos los estilos del grid */
    /* Línea vacía */
    .top-row {
        /* Estilos para la fila superior en pantallas pequeñas */
        flex-direction: column;
        /* Elementos en columna en vez de fila */
        gap: 15px;
        /* Espacio entre elementos */
    }
    /* Cerramos los estilos de top-row */
    /* Línea vacía */
    .social-links {
        /* Estilos para los links sociales en pantallas pequeñas */
        flex-wrap: wrap;
        /* Permitimos que se envuelvan a la siguiente línea */
        justify-content: center;
        /* Centramos los elementos */
    }
    /* Cerramos los estilos de social-links */
}


/* Cerramos el media query*/
 </style>
<body>
<!-- Abrimos la etiqueta body (el cuerpo visible de la página) -->
    <!-- Header -->
<!-- Comentario HTML para el header -->
    <header id="header" class="header position-relative">
<!-- Abrimos el header con id y clases de Bootstrap -->
        <div class="container-fluid container-xl position-relative">
<!-- Contenedor con clases de Bootstrap para el ancho -->
            <div class="top-row d-flex align-items-center justify-content-between">
<!-- Fila superior con flexbox de Bootstrap -->
                <a href="index.php" class="logo d-flex align-items-end">
<!-- Link al inicio con clases de flexbox -->
                    <img src="assets/img/logo/nuevologo.ico" alt="logo-lab">
<!-- Imagen del logo -->
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
<!-- Nombre del sitio y un span vacío -->
                </a>
<!-- Cerramos el link del logo -->

<!-- Línea vacía -->
                <div class="d-flex align-items-center">
<!-- Contenedor con flexbox para alinear elementos -->
                    <div class="social-links">
<!-- Contenedor para los links sociales y opciones de usuario -->
                        <a href="#" title="Facebook"><i class="bi bi-facebook"></i></a>
<!-- Link a Facebook con icono -->
                        <a href="#" title="Twitter"><i class="bi bi-twitter"></i></a>
<!-- Link a Twitter con icono -->
                        <a href="#" title="Instagram"><i class="bi bi-instagram"></i></a>
<!-- Link a Instagram con icono -->
                        
<!-- Línea vacía -->
                        <?php if (isset($_SESSION['usuario_id'])): ?>
<!-- Si hay un usuario logueado (si existe la variable de sesión) -->
                            <span class="saludo">Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
<!-- Mostramos un saludo con el nombre del usuario (htmlspecialchars previene ataques XSS) -->
                            <a href="./forms/perfil.php">Perfil</a>
                            <a href="forms/logout.php" class="btn-publicador">
<!-- Link para cerrar sesión -->
                                <i class="bi bi-box-arrow-right"></i>
<!-- Icono de salida -->
                                Cerrar Sesión
<!-- Texto del botón -->
                            </a>
<!-- Cerramos el link de cerrar sesión -->
                        <?php else: ?>
<!-- Si NO hay usuario logueado -->
                            <a href="forms/inicio-sesion.php" class="btn-publicador">
<!-- Link para iniciar sesión -->
                                <i class="bi bi-box-arrow-in-right"></i>
<!-- Icono de entrada -->
                                Inicia sesión
<!-- Texto del botón -->
                            </a>
<!-- Cerramos el link de iniciar sesión -->
                            <a href="forms/register.php" class="btn-publicador">
<!-- Link para crear cuenta -->
                                <i class="bi bi-person-plus"></i>
<!-- Icono de persona con plus -->
                                Crear Cuenta
<!-- Texto del botón -->
                            </a>
<!-- Cerramos el link de crear cuenta -->
                        <?php endif; ?>
<!-- Cerramos el if/else de usuario logueado -->
                        
<!-- Línea vacía -->
                        <span style="color: var(--border); margin: 0 5px;">|</span>
<!-- Separador visual (una línea vertical) -->
                        
<!-- Línea vacía -->
                        <a href="forms/publicadores/inicio-sesion-publicadores.php" class="btn-publicador">
<!-- Link para publicadores -->
                            <i class="bi bi-pencil-square"></i>
<!-- Icono de lápiz -->
                            ¿Eres publicador?
<!-- Texto del link -->
                        </a>
<!-- Cerramos el link de publicadores -->
                    </div>
<!-- Cerramos el contenedor de social-links -->
                </div>
<!-- Cerramos el contenedor de flexbox -->
            </div>
<!-- Cerramos la fila superior -->
        </div>
<!-- Cerramos el contenedor del header -->
    </header>
<!-- Cerramos el header -->

<!-- Línea vacía -->
    <!-- Main Content -->
<!-- Comentario HTML para el contenido principal -->
    <main class="main-content">
<!-- Abrimos el main con la clase main-content -->
        <!-- Page Title -->
<!-- Comentario HTML para el título de la página -->
        <div class="page-title">
<!-- Contenedor del título -->
            <h1>Publicaciones Científicas</h1>
<!-- Título principal de la página -->
            <p>Explora nuestro contenido especializado en laboratorio clínico</p>
<!-- Subtítulo descriptivo -->
        </div>
<!-- Cerramos el contenedor del título -->

<!-- Línea vacía -->
        <!-- Buscador y Filtros de Categorías -->
<!-- Comentario HTML para la sección de búsqueda y filtros -->
        <div class="search-filter-section">
<!-- Contenedor de búsqueda y filtros -->
            <!-- Buscador -->
<!-- Comentario para el buscador -->
            <div class="search-box">
<!-- Contenedor del buscador -->
                <i class="bi bi-search search-icon"></i>
<!-- Icono de lupa -->
                <input type="text" id="searchInput" class="search-input" placeholder="Buscar publicaciones...">
<!-- Campo de texto para buscar -->
            </div>
<!-- Cerramos el buscador -->

<!-- Línea vacía -->
            <!-- Filtros de Categorías -->
<!-- Comentario para los filtros -->
            <div class="category-filters">
<!-- Contenedor de los botones de categorías -->
                <button class="category-filter-btn active" data-category="todas">
<!-- Botón para mostrar todas las categorías (activo por defecto) -->
                    <i class="bi bi-grid-fill"></i>
<!-- Icono de grid -->
                    Todas
<!-- Texto del botón -->
                </button>
<!-- Cerramos el botón de todas -->
                <?php foreach ($categorias_filtro as $cat): ?>
<!-- Recorremos cada categoría del array -->
                <button class="category-filter-btn" data-category="<?= htmlspecialchars(strtolower($cat['nombre'])) ?>">
<!-- Botón para cada categoría con data-attribute -->
                    <i class="bi bi-folder-fill"></i>
<!-- Icono de carpeta -->
                    <?= htmlspecialchars($cat['nombre']) ?>
<!-- Nombre de la categoría -->
                </button>
<!-- Cerramos el botón de categoría -->
                <?php endforeach; ?>
<!-- Cerramos el foreach de categorías -->
            </div>
<!-- Cerramos los filtros de categorías -->
        </div>
<!-- Cerramos la sección de búsqueda y filtros -->

<!-- Línea vacía -->
        <?php if (!empty($publicaciones)): ?>
<!-- Si hay publicaciones en el array (si no está vacío) -->
            <?php foreach ($publicaciones as $categoria => $posts): ?>
<!-- Recorremos cada categoría del array de publicaciones -->
                <section class="category-section">
<!-- Abrimos una sección para esta categoría -->
                    <!-- Category Header -->
<!-- Comentario HTML para el encabezado de categoría -->
                    <div class="category-header">
<!-- Contenedor del encabezado -->
                        <i class="bi bi-folder"></i>
<!-- Icono de carpeta -->
                        <h2><?= htmlspecialchars($categoria) ?></h2>
<!-- Nombre de la categoría (htmlspecialchars previene ataques XSS) -->
                    </div>
<!-- Cerramos el encabezado -->

<!-- Línea vacía -->
                    <!-- Publications Grid -->
<!-- Comentario HTML para el grid de publicaciones -->
                    <div class="publications-grid">
<!-- Contenedor del grid -->
                        <?php foreach ($posts as $pub): ?>
<!-- Recorremos cada publicación de esta categoría -->
                            <?php
                            // Abrimos código PHP
                            $img = !empty($pub["imagen_principal"]) 
                                // Si la publicación tiene imagen propia
                                ? "uploads/" . htmlspecialchars($pub["imagen_principal"]) 
                                // Si no, usamos el logo como fallback
                                : "assets/img/logo/nuevologo.ico";

                            $contenido = !empty($pub["resumen"])
                                // Si la publicación tiene resumen
                                ? acortar($pub["resumen"]) 
                                // Usamos el resumen acortado
                                : acortar($pub["contenido"]);
                                // Si no, usamos el contenido acortado
                            ?>
<!-- Cerramos el código PHP -->
                            <article class="publication-card">
<!-- Abrimos una tarjeta de publicación -->
                                <div class="card-image">
<!-- Contenedor de la imagen -->
                                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($pub["titulo"]) ?>">
<!-- Imagen con la ruta que calculamos arriba y alt con el título -->
                                    <span class="category-badge">
<!-- Badge de categoría -->
                                        <i class="bi bi-tag"></i> <?= htmlspecialchars($pub["categoria_nombre"]) ?>
<!-- Icono de etiqueta y nombre de la categoría -->
                                    </span>
<!-- Cerramos el badge -->
                                </div>
<!-- Cerramos el contenedor de la imagen -->

<!-- Línea vacía -->
                                <div class="card-content">
<!-- Contenedor del contenido de la tarjeta -->
                                    <h3 class="card-title"><?= htmlspecialchars($pub["titulo"]) ?></h3>
<!-- Título de la publicación -->
                                    <p class="card-excerpt"><?= htmlspecialchars($contenido) ?></p>
<!-- Resumen/extracto de la publicación -->

<!-- Línea vacía -->
                                    <div class="card-meta">
<!-- Contenedor de metadatos -->
                                        <div class="meta-item">
<!-- Item de metadato (autor) -->
                                            <i class="bi bi-person"></i>
<!-- Icono de persona -->
                                            <span><?= htmlspecialchars($pub['autor_nombre']) ?></span>
<!-- Mostramos el nombre real del autor traído de la base de datos (con seguridad htmlspecialchars) -->
                                        </div>
<!-- Cerramos el item de autor -->
                                        <div class="meta-item">
<!-- Item de metadato (fecha) -->
                                            <i class="bi bi-calendar"></i>
<!-- Icono de calendario -->
                                            <span><?= date('d/m/Y', strtotime($pub["fecha_publicacion"] ?: $pub["fecha_creacion"])) ?></span>
<!-- Fecha formateada (día/mes/año) usando fecha de publicación o creación si la primera falta -->
                                        </div>
<!-- Cerramos el item de fecha -->
                                    </div>
<!-- Cerramos el contenedor de metadatos -->

<!-- Línea vacía -->
                                    <div class="card-footer">
<!-- Footer de la tarjeta -->
                                        <a href="ver-publicacion.php?id=<?= $pub['id'] ?>" class="btn-read-more">
<!-- Link para ver la publicación completa, pasando el ID por URL -->
                                            Leer más <i class="bi bi-arrow-right"></i>
<!-- Texto del botón y flecha -->
                                        </a>
<!-- Cerramos el link -->
                                    </div>
<!-- Cerramos el footer -->
                                </div>
<!-- Cerramos el contenido de la tarjeta -->
                            </article>
<!-- Cerramos la tarjeta -->
                        <?php endforeach; ?>
<!-- Cerramos el foreach de publicaciones -->
                    </div>
<!-- Cerramos el grid -->
                </section>
<!-- Cerramos la sección de categoría -->
            <?php endforeach; ?>
<!-- Cerramos el foreach de categorías -->
        <?php else: ?>
<!-- Si NO hay publicaciones -->
            <div class="empty-state">
<!-- Contenedor del estado vacío -->
                <i class="bi bi-inbox"></i>
<!-- Icono de bandeja vacía -->
                <h3>No hay publicaciones disponibles</h3>
<!-- Título del mensaje -->
                <p>Vuelve pronto para ver nuevo contenido</p>
<!-- Texto descriptivo -->
            </div>
<!-- Cerramos el estado vacío -->
        <?php endif; ?>
<!-- Cerramos el if de publicaciones -->
    </main>
<!-- Cerramos el main -->

<!-- Línea vacía -->
    <!-- Script de Búsqueda y Filtros -->
<!-- Comentario para el script de búsqueda y filtros -->
    <script>
// Abrimos script JavaScript
        // Funcionalidad del buscador
        const searchInput = document.getElementById('searchInput');
// Obtenemos el campo de búsqueda
        const publicationCards = document.querySelectorAll('.publication-card');
// Obtenemos todas las tarjetas de publicación
        const categoryHeaders = document.querySelectorAll('.category-header');
// Obtenemos todos los encabezados de categoría

// Línea vacía
        // Evento de búsqueda en tiempo real
        searchInput.addEventListener('input', function() {
// Escuchamos cuando el usuario escribe
            const searchTerm = this.value.toLowerCase();
// Convertimos el texto de búsqueda a minúsculas
            
// Línea vacía
            publicationCards.forEach(card => {
// Recorremos cada tarjeta
                const title = card.querySelector('.card-title').textContent.toLowerCase();
// Obtenemos el título en minúsculas
                const excerpt = card.querySelector('.card-excerpt').textContent.toLowerCase();
// Obtenemos el extracto en minúsculas
                
// Línea vacía
                if (title.includes(searchTerm) || excerpt.includes(searchTerm)) {
// Si el título o extracto contienen el término de búsqueda
                    card.style.display = 'flex';
// Mostramos la tarjeta
                } else {
// Si no coincide
                    card.style.display = 'none';
// Ocultamos la tarjeta
                }
// Cerramos el if
            });
// Cerramos el forEach
            
// Línea vacía
            // Ocultar categorías vacías
            categoryHeaders.forEach(header => {
// Recorremos cada encabezado de categoría
                const section = header.closest('section');
// Obtenemos la sección completa
                const visibleCards = section.querySelectorAll('.publication-card[style*="display: flex"]');
// Contamos las tarjetas visibles
                
// Línea vacía
                if (visibleCards.length === 0) {
// Si no hay tarjetas visibles
                    section.style.display = 'none';
// Ocultamos toda la sección
                } else {
// Si hay tarjetas visibles
                    section.style.display = 'block';
// Mostramos la sección
                }
// Cerramos el if
            });
// Cerramos el forEach
        });
// Cerramos el evento input

// Línea vacía
        // Funcionalidad de filtros de categoría
        const filterButtons = document.querySelectorAll('.category-filter-btn');
// Obtenemos todos los botones de filtro
        
// Línea vacía
        filterButtons.forEach(button => {
// Recorremos cada botón
            button.addEventListener('click', function() {
// Escuchamos el click
                // Quitar clase active de todos los botones
                filterButtons.forEach(btn => btn.classList.remove('active'));
// Quitamos la clase active de todos
                
// Línea vacía
                // Agregar clase active al botón clickeado
                this.classList.add('active');
// Agregamos active al botón que se clickeó
                
// Línea vacía
                const selectedCategory = this.getAttribute('data-category');
// Obtenemos la categoría seleccionada
                
// Línea vacía
                // Limpiar búsqueda
                searchInput.value = '';
// Limpiamos el campo de búsqueda
                
// Línea vacía
                if (selectedCategory === 'todas') {
// Si seleccionaron "Todas"
                    // Mostrar todas las secciones y tarjetas
                    categoryHeaders.forEach(header => {
// Recorremos cada sección
                        header.closest('section').style.display = 'block';
// Mostramos la sección
                    });
// Cerramos el forEach
                    publicationCards.forEach(card => {
// Recorremos cada tarjeta
                        card.style.display = 'flex';
// Mostramos la tarjeta
                    });
// Cerramos el forEach
                } else {
// Si seleccionaron una categoría específica
                    // Mostrar solo la categoría seleccionada
                    categoryHeaders.forEach(header => {
// Recorremos cada sección
                        const section = header.closest('section');
// Obtenemos la sección
                        const categoryName = header.querySelector('h2').textContent.toLowerCase();
// Obtenemos el nombre de la categoría
                        
// Línea vacía
                        if (categoryName === selectedCategory) {
// Si es la categoría seleccionada
                            section.style.display = 'block';
// Mostramos la sección
                            section.querySelectorAll('.publication-card').forEach(card => {
// Mostramos todas sus tarjetas
                                card.style.display = 'flex';
// Mostramos cada tarjeta
                            });
// Cerramos el forEach
                        } else {
// Si no es la categoría seleccionada
                            section.style.display = 'none';
// Ocultamos la sección
                        }
// Cerramos el if
                    });
// Cerramos el forEach
                }
// Cerramos el if
            });
// Cerramos el evento click
        });
// Cerramos el forEach de botones
    </script>
<!-- Cerramos el script -->
</body>
<!-- Cerramos el body -->
</html>
<!-- Cerramos el HTML -->
