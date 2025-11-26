<?php
// Abrimos PHP
session_start();
// session_start() crea una sesión o reanuda la actual
// Es necesario para poder usar $_SESSION

require_once "config-admin.php";
// require_once incluye un archivo solo una vez
// Si el archivo no existe, el código se detiene (error fatal)
// Traemos la configuración y funciones de admin

// Verificamos si ya está logueado
if (isset($_SESSION['admin_id'])) {
    // Si ya hay sesión activa
    header('Location: index-admin.php');
    // header() envía un encabezado HTTP
    // Location redirige a otra página
    // IMPORTANTE: debe ir antes de cualquier HTML
    exit();
    // Detenemos el código
}

// Variables para mensajes
$mensaje = "";
$exito = false;

// Si el formulario se envió
if($_SERVER["REQUEST_METHOD"] === "POST") {
    // $_SERVER["REQUEST_METHOD"] nos dice cómo se envió la página
    // POST significa que se envió un formulario
    
    $email = trim($_POST["email"] ?? "");
    // trim() quita espacios al inicio y final
    // ?? es el operador null coalescing
    // Si $_POST["email"] no existe, usa ""
    $password = $_POST["password"] ?? "";
    
    // Validaciones
    if ($email === "" || $password === "") {
        // Si alguno está vacío
        $mensaje = "Ingresa tu email y contraseña";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        // filter_var() valida o filtra una variable
        // FILTER_VALIDATE_EMAIL verifica que sea un email válido
        // Ejemplo: "admin@gmail.com" = válido, "admin" = inválido
        $mensaje = "Email inválido";
    } 
    else {
        // Si todo está bien, intentamos hacer login
        $admin = loginAdmin($email, $password, $conn);
        // loginAdmin() es una función de config-admin.php
        // Busca el admin en la BD y verifica la contraseña
        
        if ($admin) {
            // Si encontró al admin y la contraseña es correcta
            // Guardamos datos en la sesión
            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_nombre"] = $admin["nombre"];
            $_SESSION["admin_email"] = $admin["email"];
            $_SESSION["admin_nivel"] = $admin["nivel"];
            // Ahora estos datos están disponibles en todas las páginas
            
            $mensaje = "🧪 Bienvenido al Panel de Administración, " . $admin["nombre"] . "!";
            $exito = true;
            
            // Redirección con JavaScript
            echo "
            <script>
                setTimeout(function() {
                    window.location.href = 'index-admin.php';
                }, 2000);
            </script>
            ";
            // setTimeout() espera 2000 milisegundos (2 segundos)
            // Luego redirige al panel de admin
        } else {
            // Si no encontró al admin o la contraseña es incorrecta
            $mensaje = "⚠️ Email o contraseña incorrectos";
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
    <title>Inicio de Sesión Administrador - Lab-Explorer</title>
    <!-- Título de la pestaña -->
    <link href="../../assets/css/inicio-sesion.css" rel="stylesheet">
    <!-- Cargamos el CSS -->
</head>
<!-- Cerramos head -->
<body>
<!-- Abrimos body -->
    <!-- Formulario de Login -->
    <form method="post" class="formulario" novalidate>
    <!-- method="post" envía los datos de forma segura -->
    <!-- novalidate desactiva la validación automática del navegador -->
        
        <div class="logo-lab">
        <!-- Contenedor del logo -->
            <img src="../../assets/img/logo/nuevologo.ico" alt="Logo Lab">
            <!-- Logo -->
            <h1>Inicio de Sesión Administrador</h1>
            <!-- Título -->
            <p class="subtitulo">Panel de Administración Lab-Explorer</p>
            <!-- Subtítulo -->
        </div>
        <!-- Cerramos logo-lab -->
        
        <section class="seccion-informacion">
        <!-- Sección de inputs -->
            
            <label>Email Administrador</label>
            <!-- Etiqueta -->
            <input type="email" 
                   name="email" 
                   placeholder="admin@labexplorer.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required>
            <!-- Input de email -->
            <!-- htmlspecialchars() convierte caracteres especiales en entidades HTML -->
            <!-- Ejemplo: < se vuelve &lt; -->
            <!-- Esto previene ataques XSS (Cross-Site Scripting) -->
            
            <label>Contraseña</label>
            <!-- Etiqueta -->
            <input type="password" 
                   name="password" 
                   placeholder="Tu contraseña de administrador"
                   required 
                   minlength="6">
            <!-- Input de contraseña -->
            <!-- minlength="6" requiere mínimo 6 caracteres -->
            
        </section>
        <!-- Cerramos seccion-informacion -->
        
        <section class="seccion-botones">
        <!-- Sección de botones -->
            <button type="submit">Iniciar Sesión como Administrador</button>
            <!-- Botón para enviar el formulario -->
            <p>¿No tienes cuenta de administrador? <a href="register-admin.php">Solicitar acceso</a></p>
            <!-- Link al registro -->
            <p><a href="../../index.php">← Volver al sitio principal</a></p>
            <!-- Link para volver -->
        </section>
        <!-- Cerramos seccion-botones -->
    </form>
    <!-- Cerramos formulario -->
    
    <!-- Modal de Mensajes -->
    <?php if($mensaje): ?>
    <!-- Si hay un mensaje -->
        <div class="modal-mensaje <?= $exito ? 'exito' : 'error' ?>">
        <!-- Modal que cambia de clase según éxito o error -->
        <!-- Operador ternario: condición ? si_true : si_false -->
            <div class="modal-contenido">
            <!-- Contenido del modal -->
                <h2><?= $exito ? "🧪 Acceso Concedido" : "⚠️ Acceso Denegado" ?></h2>
                <!-- Título dinámico -->
                <p><?= htmlspecialchars($mensaje) ?></p>
                <!-- Mensaje -->
                
                <?php if($exito): ?>
                <!-- Si fue exitoso -->
                    <p style="font-style: italic; margin-top: 15px;">
                        Redirigiendo al panel de administración...
                    </p>
                    <!-- Mensaje de redirección -->
                <?php else: ?>
                <!-- Si hubo error -->
                    <button onclick="cerrarmodal()">Cerrar</button>
                    <!-- Botón para cerrar -->
                <?php endif; ?>
                <!-- Cerramos if/else -->
            </div>
            <!-- Cerramos modal-contenido -->
        </div>
        <!-- Cerramos modal-mensaje -->
        
        <script>
        // Abrimos JavaScript
            function cerrarmodal() {
            // Función para cerrar el modal
                document.querySelector('.modal-mensaje').style.display='none';
                // querySelector() busca un elemento por su selector CSS
                // Cambiamos su propiedad display a 'none' (lo ocultamos)
            }
            // Cerramos función
        </script>
        <!-- Cerramos script -->
    <?php endif; ?>
    <!-- Cerramos if -->
    
</body>
<!-- Cerramos body -->
</html>
<!-- Cerramos HTML -->