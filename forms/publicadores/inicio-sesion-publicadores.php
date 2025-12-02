<?php
// Abrimos PHP
session_start();
// Iniciamos la sesión para poder guardar datos del usuario

require_once 'config-publicadores.php';
// Traemos el archivo con las funciones para publicadores

// Variables para mostrar mensajes
$mensaje = "";
// Aquí guardamos el mensaje que le mostraremos al usuario
$exito = false;
// Esta variable nos dice si el login fue exitoso

// Verificamos si el formulario fue enviado
if($_SERVER["REQUEST_METHOD"] === "POST") {
    // $_SERVER["REQUEST_METHOD"] nos dice cómo se accedió a la página
    // POST significa que se envió un formulario
    
    $correo = trim($_POST["correo"] ?? "");
    // trim() quita espacios al inicio y final
    // ?? "" significa que si no existe, usar cadena vacía
    $contrasena = $_POST["contrasena"] ?? "";
    // Obtenemos la contraseña
    
    // Verificamos que ambos campos estén llenos
    if ($correo === "" || $contrasena === "") {
        // Si alguno está vacío
        $mensaje = "Ingresa tu correo y contraseña";
    } 
    elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)){
        // filter_var() valida o filtra una variable
        // FILTER_VALIDATE_EMAIL verifica que sea un email válido
        $mensaje = "Correo inválido";
    } 
    else {
        // Si todo está bien, intentamos hacer login
        $publicador = loginPublicador($correo, $contrasena, $conn);
        // loginPublicador() es una función de config-publicadores.php
        // Busca el publicador en la BD y verifica la contraseña
        
        if ($publicador) {
            // Si el login fue exitoso
            
            // Guardamos datos en la sesión de publicador
            $_SESSION["publicador_id"] = $publicador["id"];
            $_SESSION["publicador_nombre"] = $publicador["nombre"];
            $_SESSION["publicador_email"] = $publicador["email"];
            $_SESSION["publicador_especialidad"] = $publicador["especialidad"];
            // Ahora estos datos están disponibles en todas las páginas
            
            // También guardamos en la sesión principal
            $_SESSION["es_publicador"] = true;
            // Variable booleana que indica que es un publicador
            $_SESSION["publicador_data"] = $publicador;
            // Guardamos todos los datos del publicador
            
            $mensaje = "🧪 Bienvenido al Panel de Publicadores, " . $publicador["nombre"] . "!";
            $exito = true;
            
            // Redirección con JavaScript
            echo "
            <script>
                setTimeout(function() {
                    window.location.href = 'index-publicadores.php';
                }, 2000);
            </script>
            ";
            // setTimeout() espera 2000 milisegundos (2 segundos)
            // Luego redirige al panel de publicadores
        } else {
            // Si el login falló
            $mensaje = "⚠️ Correo o contraseña incorrectos";
        }
    }
}
?>
<!-- Cerramos PHP -->
<!DOCTYPE html>
<!-- Le decimos al navegador que esto es HTML5 -->
<html lang="es">
<!-- Idioma español -->
<head>
<!-- Aquí van los metadatos -->
    <meta charset="UTF-8">
    <!-- Para que se vean bien los acentos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Para que se vea bien en celulares -->
    <title>Inicio de Sesión Publicadores </title>
    <!-- Título de la pestaña -->
    
    <!-- Bootstrap para hacer la página responsive -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap es un framework CSS que hace que la página se vea bien en todos los dispositivos -->
    
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="../../assets/css/inicio-sesion.css">
    <!-- Nuestros estilos personalizados -->
</head>
<!-- Cerramos head -->
<body>
<!-- Abrimos body -->
    <!-- Contenedor principal usando Bootstrap -->
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <!-- container-fluid = contenedor de ancho completo -->
    <!-- vh-100 = altura del 100% del viewport (pantalla) -->
    <!-- d-flex = display flex (para centrar contenido) -->
    <!-- align-items-center = centra verticalmente -->
    <!-- justify-content-center = centra horizontalmente -->
        <div class="row w-100 justify-content-center">
        <!-- row = fila de Bootstrap -->
        <!-- w-100 = ancho del 100% -->
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
            <!-- col-12 = 12 columnas en pantallas pequeñas (celular) -->
            <!-- col-sm-8 = 8 columnas en pantallas pequeñas (tablet) -->
            <!-- col-md-6 = 6 columnas en pantallas medianas -->
            <!-- col-lg-4 = 4 columnas en pantallas grandes -->
            <!-- Esto hace que el formulario sea responsive -->
                
                <!-- Formulario de inicio de sesión -->
                <form method="post" class="formulario" novalidate>
                <!-- method="post" envía los datos de forma segura -->
                <!-- novalidate desactiva la validación automática del navegador -->
                    
                    <!-- Logo y título -->
                    <div class="logo-lab text-center mb-4">
                    <!-- text-center = texto centrado -->
                    <!-- mb-4 = margin-bottom de 4 unidades -->
                        <img src="../../assets/img/logo/logobrayan2.ico" alt="Logo Lab" class="mb-3">
                        <!-- Logo -->
                        <h1 class="h3">Inicio de Sesión Publicadores</h1>
                        <!-- h3 = tamaño de encabezado 3 -->
                        <p class="subtitulo text-muted">Panel de Publicadores</p>
                        <!-- text-muted = texto en gris claro -->
                    </div>
                    <!-- Cerramos logo-lab -->
                    
                    <!-- Sección de inputs -->
                    <section class="seccion-informacion mb-4">
                    <!-- mb-4 = margen inferior -->
                        
                        <!-- Campo de correo -->
                        <div class="mb-3">
                        <!-- mb-3 = margen inferior de 3 unidades -->
                            <label class="form-label">Correo Electrónico</label>
                            <!-- form-label = clase de Bootstrap para etiquetas -->
                            <input type="email" 
                                   name="correo" 
                                   class="form-control"
                                   value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                                   placeholder="tu@email.com"
                                   required>
                            <!-- form-control = clase de Bootstrap para inputs -->
                            <!-- htmlspecialchars() previene ataques XSS -->
                        </div>
                        <!-- Cerramos mb-3 -->
                        
                        <!-- Campo de contraseña -->
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" 
                                   name="contrasena" 
                                   class="form-control"
                                   placeholder="Tu contraseña"
                                   required 
                                   minlength="6">
                            <!-- minlength="6" requiere mínimo 6 caracteres -->
                        </div>
                        <!-- Cerramos mb-3 -->
                    </section>
                    <!-- Cerramos seccion-informacion -->
                    
                    <!-- Sección de botones y enlaces -->
                    <section class="seccion-botones text-center">
                    <!-- text-center = texto centrado -->
                        
                        <!-- Botón de login -->
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                        <!-- btn = botón de Bootstrap -->
                        <!-- btn-primary = botón azul (color primario) -->
                        <!-- w-100 = ancho del 100% -->
                            Iniciar Sesión como Publicador
                        </button>
                        
                        <!-- Enlaces -->
                        <div class="d-flex flex-column gap-2">
                        <!-- d-flex = display flex -->
                        <!-- flex-column = dirección vertical -->
                        <!-- gap-2 = espacio entre elementos -->
                            <p class="mb-0">
                            <!-- mb-0 = sin margen inferior -->
                                ¿No tienes cuenta? 
                                <a href="registro-publicadores.php" class="text-decoration-none">Regístrate como Publicador</a>
                                <!-- text-decoration-none = sin subrayado -->
                            </p>
                            <p class="mb-0">
                                <a href="recuperar.php" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
                            </p>
                            <p class="mb-0">
                                <a href="../recuperar-publicador.php" class="text-decoration-none">Recupera tu contraseña</a>
                            </p>
                            <p class="mb-0">
                                <a href="../../index.php" class="text-decoration-none">
                                    ← Volver al sitio principal
                                </a>
                            </p>
                        </div>
                        <!-- Cerramos d-flex -->
                    </section>
                    <!-- Cerramos seccion-botones -->
                </form>
                <!-- Cerramos formulario -->
            </div>
            <!-- Cerramos col -->
        </div>
        <!-- Cerramos row -->
    </div>
    <!-- Cerramos container-fluid -->

    <!-- Modal de mensajes -->
    <?php if($mensaje): ?>
    <!-- Si hay un mensaje -->
    <div class="modal-mensaje <?= $exito ? 'exito' : 'error' ?>">
    <!-- Modal que cambia de clase según éxito o error -->
    <!-- Operador ternario: condición ? si_true : si_false -->
        <div class="modal-contenido">
        <!-- Contenido del modal -->
            
            <!-- Título dinámico -->
            <h2><?= $exito ? "🧪 Bienvenido A Lab Explorer!" : "Error" ?></h2>
            
            <!-- Mensaje -->
            <p><?= htmlspecialchars($mensaje) ?></p>
            <!-- htmlspecialchars() previene XSS -->
            
            <?php if($exito): ?>
            <!-- Si fue exitoso -->
                <p style="font-style: italic; margin-top: 15px;">
                    Serás redirigido automáticamente en 2 segundos...
                </p>
            <?php else: ?>
            <!-- Si hubo error -->
                <button onclick="cerrarmodal()" class="btn btn-secondary">Cerrar</button>
                <!-- btn-secondary = botón gris -->
            <?php endif; ?>
            <!-- Cerramos if/else -->
        </div>
        <!-- Cerramos modal-contenido -->
    </div>
    <!-- Cerramos modal-mensaje -->
    
    <!-- JavaScript para cerrar el modal -->
    <script>
    // Abrimos JavaScript
        function cerrarmodal() {
        // Función para cerrar el modal
            document.querySelector('.modal-mensaje').style.display='none';
            // querySelector() busca un elemento por su selector CSS
            // Cambiamos su display a none (lo ocultamos)
        }
        // Cerramos función
    </script>
    <!-- Cerramos script -->
    <?php endif; ?>
    <!-- Cerramos if -->

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript de Bootstrap para componentes interactivos -->
</body>
<!-- Cerramos body -->
</html>
<!-- Cerramos HTML -->