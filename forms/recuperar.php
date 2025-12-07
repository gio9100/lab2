<?php
// Abrimos PHP
// Este archivo maneja todo el proceso de recuperar contraseña olvidada
// Tiene 3 pasos: 1) Usuario pone su correo, 2) Le enviamos un link, 3) Cambia su contraseña

session_start();
// Iniciamos la sesión

// Configuración para conectarnos a la base de datos
$host = '127.0.0.1';
// Servidor local
$dbname = 'lab_exp_db';
// Nombre de nuestra base de datos
$username = 'root';
// Usuario de MySQL
$password = '';
// Contraseña vacía (por defecto en XAMPP)

// Creamos la conexión usando PDO (es diferente a mysqli pero hace lo mismo)
$pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8", $username, $password);
// PDO es otra forma de conectarse a MySQL, más moderna

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Le decimos a PDO que nos avise si hay errores

// Traemos la librería PHPMailer para enviar correos


// Variables para mostrar mensajes
$mensaje = "";
// Aquí guardamos el mensaje que le mostraremos al usuario
$tipo_mensaje = "";
// Tipo de mensaje (success o error)

// PASO 1: Si el usuario envió su correo para recuperar contraseña
if (isset($_POST['correo']) && !isset($_POST['nueva_password'])) {
    // Si viene el correo pero NO viene nueva_password (o sea, es el primer paso)

    $correo = trim($_POST['correo']);
    // Obtenemos el correo y le quitamos espacios

    // Buscamos si ese correo existe en nuestra base de datos
    $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE correo = ?");
    // Preparamos la consulta
    $stmt->execute([$correo]);
    // La ejecutamos pasándole el correo
    $usuario = $stmt->fetch();
    // Obtenemos el resultado

    if ($usuario) {
        // Si encontramos al usuario

        // Generamos un código único y secreto (token)
        $token = bin2hex(random_bytes(32));
        // random_bytes(32) crea 32 bytes aleatorios
        // bin2hex lo convierte a texto (letras y números)

        // El token expira en 1 hora
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        // strtotime('+1 hour') suma 1 hora a la hora actual
        // date() lo convierte al formato que MySQL entiende

        // Guardamos el token en la base de datos
        $stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, token_expira = ? WHERE correo = ?");
        // Actualizamos el usuario con el token y su fecha de expiración
        
        if ($stmt->execute([$token, $expiracion, $correo])) {
            // Si se guardó correctamente

            // Creamos el enlace que le enviaremos por correo
            $enlace = "http://localhost/lab/forms/recuperar.php?token=$token";
            // El enlace incluye el token como parámetro

            // Preparamos el correo electrónico
            // Preparamos el correo electrónico
            require_once 'EmailHelper.php';
            
            $enlace_html = "<a href='$enlace' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Restablecer contraseña</a>";
            
            $mensaje_html = "
                <p>Has solicitado recuperar tu contraseña. Haz clic en el siguiente botón para restablecerla.</p>
                <p>Este enlace expira en 1 hora.</p>
            ";
            
            $exito = EmailHelper::enviarCorreo(
                $correo,
                "Restablecer password Lab Explorer",
                $mensaje_html,
                'Restablecer contraseña',
                $enlace
            );

            if ($exito) {
                $mensaje = "Se ha enviado un correo con el enlace para recuperar tu contraseña.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "No se pudo enviar el correo. Por favor intenta más tarde.";
                $tipo_mensaje = "error";
            }

        }

    } else {
        // Si el correo no existe en la base de datos
        $mensaje = "Ese correo no está registrado.";
        $tipo_mensaje = "error";
    }
}

// PASO 2: Verificamos si el usuario hizo click en el enlace del correo
$token_valido = false;
// Variable que dice si el token es válido

if (isset($_GET['token'])) {
    // Si viene un token en la URL

    $token = $_GET['token'];
    // Obtenemos el token

    // Buscamos si existe un usuario con ese token Y que no haya expirado
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND token_expira > NOW()");
    // NOW() es la fecha y hora actual de MySQL
    $stmt->execute([$token]);
    // Ejecutamos la consulta
    $token_valido = $stmt->fetch();
    // Si devuelve algo, el token es válido
}

// PASO 3: Si el usuario envió el formulario con la nueva contraseña
if (isset($_POST['nueva_password']) && isset($_POST['token'])) {
    // Si viene la nueva contraseña y el token

    $nueva_password = $_POST['nueva_password'];
    // Nueva contraseña
    $confirmar_password = $_POST['confirmar_password'];
    // Confirmación de la contraseña
    $token = $_POST['token'];
    // Token

    // Verificamos que las dos contraseñas sean iguales
    if ($nueva_password !== $confirmar_password) {
        // Si no coinciden
        $mensaje = " ⚠️Las contraseñas no coinciden.";
        $tipo_mensaje = "error";

    } else {
        // Si coinciden

        // Verificamos de nuevo que el token sea válido
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND token_expira > NOW()");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Si el token es válido

            // Encriptamos la nueva contraseña
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            // password_hash crea un hash seguro

            // Actualizamos la contraseña y borramos el token
            $stmt = $pdo->prepare("UPDATE usuarios 
                                   SET contrasena_hash = ?, reset_token = NULL, token_expira = NULL 
                                   WHERE id = ?");
            // Ponemos el token en NULL para que no se pueda usar de nuevo
            $stmt->execute([$password_hash, $usuario['id']]);

            $mensaje = " ✔️ Tu contraseña fue cambiada correctamente. Ya puedes iniciar sesión.";
            $tipo_mensaje = "success";

        } else {
            // Si el token ya expiró o no es válido
            $mensaje = " ⚠️El enlace ya expiró o no es válido.";
            $tipo_mensaje = "error";
        }
    }
}

?>
<!-- Cerramos PHP -->
<!DOCTYPE html>
<!-- Le decimos al navegador que esto es HTML5 -->
<html lang="es">
<!-- Abrimos el HTML en español -->
    <head>
    <!-- Aquí van los metadatos -->
    <meta charset="utf-8">
    <!-- Para que se vean bien los acentos -->
    <title>Recuperar contraseña Lab-Explora</title>
    <!-- Título de la pestaña -->
    <link rel="stylesheet" href="../assets/css/registro.css">
    <!-- Cargamos el CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Para que se vea bien en celulares -->
    <style>
        /* Estilos para los mensajes */
        .message {
            /* Estilos base para todos los mensajes */
            padding: 15px;
            /* Espaciado interno */
            margin-bottom: 20px;
            /* Espacio abajo del mensaje */
            border-radius: 8px;
            /* Esquinas redondeadas */
            font-weight: bold;
            /* Texto en negrita */
            text-align: center;
            /* Texto centrado */
        }
        
        .message.error {
            /* Estilos para mensajes de error */
            background-color: #f8d7da;
            /* Fondo rojo claro */
            color: #721c24;
            /* Texto rojo oscuro */
            border: 2px solid #f5c6cb;
            /* Borde rojo */
        }
        
        .message.success {
            /* Estilos para mensajes de éxito */
            background-color: #d4edda;
            /* Fondo verde claro */
            color: #155724;
            /* Texto verde oscuro */
            border: 2px solid #c3e6cb;
            /* Borde verde */
        }
    </style>
    <!-- Cerramos los estilos -->
    </head>
    <!-- Cerramos el head -->
<body>
<!-- Abrimos el body -->

<div class="box">
<!-- Contenedor principal -->
<div class="formulario">
<!-- Contenedor del formulario -->


<?php if ($mensaje): ?>
<!-- Si hay un mensaje, lo mostramos -->
    <div class="message <?= $tipo_mensaje ?>"><?= $mensaje ?></div>
    <!-- Div con el mensaje (cambia de color según el tipo) -->
<?php endif; ?>
<!-- Cerramos el if -->

<?php 
// Si el token NO es válido, mostramos el formulario para pedir el correo
if (!$token_valido && !isset($_POST['token'])): 
?>
    <!-- Formulario para pedir el correo -->
    <div class="logo-lab">
    <!-- Contenedor del logo -->
       
      <img src="../assets/img/logo/logobrayan2.ico" alt="logo Lab">
      <!-- Logo -->
      <h1>Lab Explorer</h1>
      <!-- Título -->
      <p class="subtitulo">Recuperar Tu contraseña</p>
      <!-- Subtítulo -->
     <p>Ingresa tu correo para enviarte un enlace de recuperación.</p>
     <!-- Instrucciones -->
     <style>
         p {
             color: yellow;
             /* Color amarillo para el texto */
         }
     </style>
     <!-- CSS inline para el párrafo -->
      </div>
      <!-- Cerramos logo-lab -->
    <form method="POST">
    <!-- Formulario que se envía por POST -->
        <input type="email" name="correo" placeholder="Tu correo" required>
        <!-- Input de correo -->
        <button type="submit">Enviar enlace</button>
        <!-- Botón para enviar -->
        <a href="../forms/inicio-sesion.php">Regresar al inicio de sesion</a>
        <!-- Link para volver al login -->
    </form>
    <!-- Cerramos el formulario -->
</div>
<!-- Cerramos formulario -->

<?php else: ?>
<!-- Si el token SÍ es válido, mostramos el formulario para cambiar la contraseña -->
    
    <form method="POST">
    <!-- Formulario que se envía por POST -->
        <div class="logo-lab">
        <!-- Contenedor del logo -->
            <img src="../assets/img/logo/logobrayan2.ico" alt="logo lab">
            <!-- Logo -->
            <p class="subtitulo">Escribe tu nueva contraseña (minimo 6 caracteres)</p>
            <!-- Instrucciones -->
        
        <!-- Input oculto con el token -->
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? $_POST['token']) ?>">
        <!-- type="hidden" hace que no se vea pero se envía con el formulario -->
        
        <input type="password" name="nueva_password" placeholder="Nueva contraseña" minlength="6" required>
        <!-- Input para la nueva contraseña -->
        <input type="password" name="confirmar_password" placeholder="Confirmar contraseña" minlength="6" required>
        <!-- Input para confirmar la contraseña -->
        <button type="submit">Cambiar contraseña</button>
        <!-- Botón para enviar -->
        <a href="inicio-sesion.php">Regresar al inicio de sesion</a>
        <!-- Link para volver al login -->
    </form>
    <!-- Cerramos el formulario -->

<?php endif; ?>
<!-- Cerramos el if/else -->

</div>
<!-- Cerramos box -->

</body>
<!-- Cerramos el body -->
</html>
<!-- Cerramos el HTML -->
