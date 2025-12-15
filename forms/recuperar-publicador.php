<?php
// Abrimos PHP
// Este archivo maneja la recuperación de contraseña para PUBLICADORES
// Es similar al recuperar.php pero usa la tabla "publicadores" en vez de "usuarios"

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

// Creamos la conexión usando PDO
$pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8", $username, $password);
// PDO es otra forma de conectarse a MySQL

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Le decimos a PDO que nos avise si hay errores

// Traemos la librería PHPMailer para enviar correos


// Variables para mostrar mensajes
$mensaje = "";
// Aquí guardamos el mensaje que le mostraremos al publicador
$tipo_mensaje = "";
// Tipo de mensaje (success o error)

// PASO 1: Si el publicador envió su email para recuperar contraseña
if (isset($_POST['email']) && !isset($_POST['nueva_password'])) {
    // Si viene el email pero NO viene nueva_password (o sea, es el primer paso)

    $email = trim($_POST['email']);
    // Obtenemos el email y le quitamos espacios

    // Buscamos si ese email existe en la tabla de publicadores
    $stmt = $pdo->prepare("SELECT id, nombre FROM publicadores WHERE email = ? AND estado = 'activo'");
    // Preparamos la consulta (solo publicadores activos)
    $stmt->execute([$email]);
    // La ejecutamos pasándole el email
    $publicador = $stmt->fetch();
    // Obtenemos el resultado

    if ($publicador) {
        // Si encontramos al publicador

        // Generamos un código único y secreto (token)
        $token = bin2hex(random_bytes(32));
        // random_bytes(32) crea 32 bytes aleatorios
        // bin2hex lo convierte a texto (letras y números)

        // El token expira en 1 hora
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        // strtotime('+1 hour') suma 1 hora a la hora actual
        // date() lo convierte al formato que MySQL entiende

        // Guardamos el token en la base de datos
        $stmt = $pdo->prepare("UPDATE publicadores SET reset_token = ?, token_expira = ? WHERE email = ?");
        // Actualizamos el publicador con el token y su fecha de expiración
        
        if ($stmt->execute([$token, $expiracion, $email])) {
            // Si se guardó correctamente

            // Creamos el enlace que le enviaremos por correo
            $enlace = "http://localhost/lab/forms/recuperar-publicador.php?token=$token";
            // El enlace incluye el token como parámetro

            // Preparamos el correo electrónico
            // Preparamos el correo electrónico
            require_once 'EmailHelper.php';
            
            $mensaje_html = "
                <p>Has solicitado recuperar tu contraseña de publicador. Haz clic en el siguiente botón para restablecerla.</p>
                <p>Este enlace expira en 1 hora.</p>
            ";
            
            $exito = EmailHelper::enviarCorreo(
                $email,
                "Restablecer contraseña - Publicador Lab Explora",
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
        // Si el email no existe en la tabla de publicadores
        $mensaje = "Ese correo no está registrado como publicador o tu cuenta no está activa.";
        $tipo_mensaje = "error";
    }
}

// PASO 2: Verificamos si el publicador hizo click en el enlace del correo
$token_valido = false;
// Variable que dice si el token es válido

if (isset($_GET['token'])) {
    // Si viene un token en la URL

    $token = $_GET['token'];
    // Obtenemos el token

    // Buscamos si existe un publicador con ese token Y que no haya expirado
    $stmt = $pdo->prepare("SELECT id FROM publicadores WHERE reset_token = ? AND token_expira > NOW()");
    // NOW() es la fecha y hora actual de MySQL
    $stmt->execute([$token]);
    // Ejecutamos la consulta
    $token_valido = $stmt->fetch();
    // Si devuelve algo, el token es válido
}

// PASO 3: Si el publicador envió el formulario con la nueva contraseña
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
        $stmt = $pdo->prepare("SELECT id FROM publicadores WHERE reset_token = ? AND token_expira > NOW()");
        $stmt->execute([$token]);
        $publicador = $stmt->fetch();

        if ($publicador) {
            // Si el token es válido

            // Encriptamos la nueva contraseña
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            // password_hash crea un hash seguro

            // Actualizamos la contraseña y borramos el token
            $stmt = $pdo->prepare("UPDATE publicadores 
                                   SET password = ?, reset_token = NULL, token_expira = NULL 
                                   WHERE id = ?");
            // Ponemos el token en NULL para que no se pueda usar de nuevo
            $stmt->execute([$password_hash, $publicador['id']]);

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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Publicador Lab-Explora</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- CSS personalizado (mismo que inicio de sesión para estilo blanco) -->
    <link href="../assets/css/inicio-sesion.css" rel="stylesheet">
    
    <style>
        .mensaje-validacion { margin-top: 5px; font-size: 0.9rem; font-weight: 500; display: none; }
        .mensaje-validacion.error { color: #dc3545; display: block; }
        .mensaje-validacion.success { color: #28a745; display: block; }
    </style>
</head>
<body>

    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
            
                <?php if (!$token_valido && !isset($_POST['token'])): ?>
                <!-- CASO 1: SOLICITAR ENLACE (Token no presente o inválido) -->
                <form method="POST" class="formulario" novalidate>
                    <div class="logo-lab text-center mb-4">
                        <img src="../assets/img/logo/logo-labexplora.png" alt="logo-lab" class="mb-3">
                        <h1 class="h3">Lab Explora</h1>
                        <p class="subtitulo text-muted">Recuperar Contraseña Publicador</p>
                    </div>

                    <section class="seccion-informacion mb-4">
                        <p class="text-center text-muted small mb-3">Ingresa tu correo de publicador para recibir el enlace.</p>
                        
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="Tu correo de publicador" required>
                        </div>
                    </section>

                    <section class="seccion-botones text-center">
                        <button type="submit" class="btn btn-primary w-100 mb-3">Enviar enlace</button>
                        <p class="mb-0"><a href="publicadores/inicio-sesion-publicadores.php" class="text-decoration-none">Regresar al inicio de sesión</a></p>
                    </section>
                </form>

                <?php else: ?>
                <!-- CASO 2: CAMBIAR CONTRASEÑA (Token válido) -->
                <form method="POST" class="formulario" novalidate>
                    <div class="logo-lab text-center mb-4">
                        <img src="../assets/img/logo/logo-labexplora.png" alt="logo-lab" class="mb-3">
                        <h1 class="h3">Lab Explora</h1>
                        <p class="subtitulo text-muted">Nueva Contraseña</p>
                    </div>

                    <section class="seccion-informacion mb-4">
                        <p class="text-center text-muted small mb-3">Escribe tu nueva contraseña (mínimo 6 caracteres)</p>
                        
                        <!-- Input oculto con el token -->
                        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? $_POST['token']) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="nueva_password" class="form-control" placeholder="Nueva contraseña" minlength="6" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="confirmar_password" class="form-control" placeholder="Confirmar contraseña" minlength="6" required>
                        </div>
                    </section>

                    <section class="seccion-botones text-center">
                        <button type="submit" class="btn btn-primary w-100 mb-3">Cambiar contraseña</button>
                        <p class="mb-0"><a href="publicadores/inicio-sesion-publicadores.php" class="text-decoration-none">Regresar al inicio de sesión</a></p>
                    </section>
                </form>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Modal de Mensajes -->
    <?php if ($mensaje): ?>
        <div class="modal-mensaje <?= $tipo_mensaje ?>">
            <div class="modal-contenido">
                <h2><?= $tipo_mensaje === 'success' ? '¡Éxito!' : 'Información' ?></h2>
                <p><?= htmlspecialchars($mensaje) ?></p>
                
                <?php if ($tipo_mensaje === 'success' && isset($_POST['nueva_password'])): ?>
                    <p style="font-style: italic; margin-top: 15px;">Redirigiendo al inicio de sesión...</p>
                    <script>
                        setTimeout(function() {
                            window.location.href = 'publicadores/inicio-sesion-publicadores.php';
                        }, 2000);
                    </script>
                <?php else: ?>
                    <button onclick="cerrarmodal()" class="btn btn-secondary mt-3">Cerrar</button>
                <?php endif; ?>
            </div>
        </div>

        <script>
            function cerrarmodal() {
                document.querySelector('.modal-mensaje').style.display='none';
            }
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/accessibility-widget.js?v=3.2"></script>
</body>
</html>
