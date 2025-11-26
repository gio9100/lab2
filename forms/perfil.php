<?php
// Abrimos PHP para escribir código del lado del servidor
require_once "usuario.php";
// Traemos el archivo usuario.php que maneja todo lo de las sesiones y verifica si hay alguien logueado

// Checamos si el usuario ya inició sesión
if (!$usuario_logueado) {
    // Si la variable $usuario_logueado es false (o sea que NO está logueado)
    header("Location: inicio-sesion.php");
    // Lo mandamos a la página de login con header
    exit();
    // Detenemos el código aquí para que no siga ejecutando nada más
}

// Verificamos que tengamos los datos del usuario cargados
if (!isset($usuario) || $usuario === null) {
    // Si la variable $usuario no existe o está vacía
    session_destroy();
    // Destruimos la sesión por seguridad
    header("Location: inicio-sesion.php");
    // Lo mandamos al login de nuevo
    exit();
    // Paramos todo aquí
}

// Checamos si viene un mensaje de éxito en la URL
if (isset($_GET['success'])) {
    // $_GET['success'] es un parámetro que viene en la URL tipo: perfil.php?success=Foto subida
    echo '<div class="mensaje-exito">¡' . htmlspecialchars($_GET['success']) . '</div>';
    // Mostramos el mensaje en un div verde (htmlspecialchars quita código malicioso)
}

// Checamos si viene un mensaje de error en la URL
if (isset($_GET['error'])) {
    // $_GET['error'] es un parámetro que viene en la URL tipo: perfil.php?error=Archivo muy grande
    echo '<div class="mensaje-error">' . htmlspecialchars($_GET['error']) . '</div>';
    // Mostramos el mensaje en un div rojo
}
?>
<!-- Cerramos el PHP -->

<!DOCTYPE html>
<!-- Le decimos al navegador que esto es HTML5 -->
<html lang="es">
<!-- Abrimos el HTML y le decimos que está en español -->
<head>
<!-- Aquí van todos los metadatos y links a CSS -->
    <meta charset="UTF-8">
    <!-- Esto hace que se vean bien los acentos y la ñ -->
    <title>Perfil de Usuario - Lab Explorer</title>
    <!-- Título que aparece en la pestaña del navegador -->
    
    <!-- Cargamos los estilos CSS de la página -->
    <link rel="stylesheet" href="../assets/css/perfil.css">
    <!-- CSS específico para la página de perfil -->
    <link rel="stylesheet" href="../assets/css/main.css">
    <!-- CSS principal del sitio -->
    <link rel="stylesheet" href="../assets/css/estilos-paginas-informacion.css">
    <!-- CSS para páginas de información -->
    
    <!--VENDOR-->
    <!-- Aquí van las librerías externas -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap para que todo se vea ordenado y responsive -->
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- Iconos de Bootstrap para los símbolos -->
    
    <!--FONTS-->
    <!-- Aquí cargamos las fuentes de Google -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <!-- Pre-conectamos con Google Fonts para que cargue más rápido -->
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <!-- Pre-conectamos con el CDN de las fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Cargamos las fuentes Roboto, Poppins y Nunito con todos sus pesos -->
</head>
<!-- Cerramos el head -->
<body>
<!-- Abrimos el body, aquí va todo lo que se ve en la página -->
 
     <header id="header" class="header position-relative">
     <!-- Header del sitio con id y clases de Bootstrap -->
        <div class="container-fluid container-xl position-relative">
        <!-- Contenedor que se adapta al tamaño de la pantalla -->

            <div class="top-row d-flex align-items-center justify-content-between">
            <!-- Fila superior con flexbox, alinea elementos al centro y los separa a los extremos -->
                <a href="../index.php" class="logo d-flex align-items-end">
                <!-- Link al inicio que también funciona como logo -->
                    <img src="../assets/img/logo/logo-lab.ico" alt="logo-lab">
                    <!-- Imagen del logo del laboratorio -->
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
                    <!-- Nombre del sitio y un span vacío para estilos -->
                </a>
                <!-- Cerramos el link del logo -->

                <div class="d-flex align-items-center">
                <!-- Contenedor con flexbox para alinear los elementos -->
                    <div class="social-links">
                    <!-- Contenedor de los links sociales y opciones de usuario -->
                        <a href="https://www.facebook.com/laboratorioabcdejacona?locale=es_LA" target="_blank" class="facebook"><i class="bi bi-facebook" ></i></a>
                        <!-- Link a Facebook que se abre en nueva pestaña -->
                        <a href="#" target="_blank" class="twitter"><i class="bi bi-twitter"></i></a>
                        <!-- Link a Twitter (por ahora sin URL real) -->
                        <a href="https://www.instagram.com/lab_explorer_cbtis_52/" target="_blank" class="instagram"><i class="bi bi-instagram"></i></a>
                        <!-- Link a Instagram que se abre en nueva pestaña -->
                        
                        <?php if($usuario_logueado): ?>
                        <!-- Si el usuario está logueado mostramos estas opciones -->
    <div class="usuario">
    <!-- Contenedor para las opciones del usuario logueado -->
        <a href="../index.php">Pagina Principal</a>
        <!-- Link para volver al inicio -->
        <a href="logout.php">Cerrar sesión</a>
        <!-- Link para cerrar sesión -->
    </div>
    <!-- Cerramos el div de usuario -->
<?php else: ?>
<!-- Si NO está logueado mostramos login y registro -->
    <a href="inicio-sesion.php">Inicia sesion</a>
    <!-- Link para ir al login -->
    <a href="register.php">Crear Cuenta</a>
    <!-- Link para ir al registro -->
    <?php endif; ?>
    <!-- Cerramos el if/else -->
                    </div>
                    <!-- Cerramos social-links -->
                </div>
                <!-- Cerramos el contenedor de flexbox -->
            </div>
            <!-- Cerramos top-row -->
        </div>
        <!-- Cerramos el container -->
</header>
<!-- Cerramos el header -->
    <div class="main-container">
    <!-- Contenedor principal de la página de perfil -->
        <div class="perfil-card">
        <!-- Tarjeta que contiene todo el perfil del usuario -->

            <div class="perfil-header">
            <!-- Sección del encabezado del perfil -->
                <div class="perfil-imagen">
                <!-- Contenedor de la imagen de perfil -->
                    <?php if (!empty($usuario["imagen"]) && $usuario["imagen"] != "default.png"): ?>
                    <!-- Checamos si el usuario tiene una foto propia (que no sea la default) -->
                        
                        <img src="../assets/img/uploads/<?= htmlspecialchars($usuario['imagen']) ?>" 
                             alt="Foto de perfil de <?= htmlspecialchars($usuario['nombre']) ?>">
                        <!-- Mostramos la foto del usuario desde la carpeta uploads -->
                        <!-- htmlspecialchars previene ataques XSS -->

                        <form action="eliminar_foto.php" method="POST" class="form-eliminar">
                        <!-- Formulario para eliminar la foto, se envía por POST -->
                            <button type="submit" class="btn-eliminar-foto" 
                                    onclick="return confirm('¿Estás seguro de eliminar tu foto?')">
                            <!-- Botón que al hacer click pregunta si estás seguro -->
                                 Eliminar foto
                                 <!-- Texto del botón -->
                            </button>
                            <!-- Cerramos el botón -->
                        </form>
                        <!-- Cerramos el formulario -->
                    <?php else: ?>
                    <!-- Si NO tiene foto propia, mostramos la imagen por defecto -->
                        <img src="../assets/img/uploads/default.png" 
                             alt="Foto de perfil por defecto">
                        <!-- Imagen genérica que se muestra cuando no hay foto -->
                    <?php endif; ?>
                    <!-- Cerramos el if/else de la imagen -->
                </div>
                <!-- Cerramos perfil-imagen -->
                
                <div class="perfil-info">
                <!-- Contenedor de la información del usuario -->
                    <h2><?= htmlspecialchars($usuario['nombre']) ?></h2>
                    <!-- Mostramos el nombre del usuario en un h2 -->
                    <div class="perfil-correo">
                    <!-- Contenedor del correo -->
                         <?= htmlspecialchars($usuario['correo']) ?>
                         <!-- Mostramos el correo del usuario -->
                    </div>
                    <!-- Cerramos perfil-correo -->
                    <span class="perfil-id">ID: <?= htmlspecialchars($usuario['id']) ?></span>
                    <!-- Mostramos el ID del usuario -->
                </div>
                <!-- Cerramos perfil-info -->
            </div>
            <!-- Cerramos perfil-header -->

            <!-- Estadísticas del usuario -->
            <div class="perfil-stats">
            <!-- Contenedor de las estadísticas -->
                <div class="stat-card">
                <!-- Tarjeta individual de estadística -->
                    <span class="stat-number">12</span>
                    <!-- Número de artículos leídos (por ahora hardcodeado) -->
                    <span class="stat-label">Artículos Leídos</span>
                    <!-- Etiqueta que dice qué significa el número -->
                </div>
                <!-- Cerramos stat-card -->
                <div class="stat-card">
                <!-- Segunda tarjeta de estadística -->
                    <span class="stat-number">5</span>
                    <!-- Número de casos revisados -->
                    <span class="stat-label">Casos Revisados</span>
                    <!-- Etiqueta descriptiva -->
                </div>
                <!-- Cerramos stat-card -->
                <div class="stat-card">
                <!-- Tercera tarjeta de estadística -->
                    <span class="stat-number">3</span>
                    <!-- Número de protocolos guardados -->
                    <span class="stat-label">Protocolos Guardados</span>
                    <!-- Etiqueta descriptiva -->
                </div>
                <!-- Cerramos stat-card -->
            </div>
            <!-- Cerramos perfil-stats -->

            <!-- Formulario para subir nueva foto de perfil -->
            <form action="procesar_imagen.php" method="POST" enctype="multipart/form-data" class="form-imagen">
            <!-- Form que envía a procesar_imagen.php por POST -->
            <!-- enctype="multipart/form-data" es necesario para subir archivos -->
                <div class="form-group">
                <!-- Grupo del formulario -->
                    <label>Actualizar imagen de perfil:</label>
                    <!-- Etiqueta que dice qué hace el input -->
                    <input type="file" name="imagen" accept="image/jpeg,image/png,image/gif" required>
                    <!-- Input de tipo file para seleccionar la imagen -->
                    <!-- accept limita a solo JPEG, PNG y GIF -->
                    <!-- required hace que sea obligatorio seleccionar un archivo -->
                </div>
                <!-- Cerramos form-group -->
                <button type="submit" class="btn-subir">📤 Subir Imagen</button>
                <!-- Botón para enviar el formulario con emoji de subida -->
            </form>
            <!-- Cerramos el formulario -->
        </div>
        <!-- Cerramos perfil-card -->
    </div>
    <!-- Cerramos main-container -->
</body>
<!-- Cerramos el body -->
</html>
<!-- Cerramos el HTML -->