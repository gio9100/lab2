<?php
// Abrimos PHP
session_start();
// Iniciamos la sesión para poder guardar datos del usuario
require_once "usuario.php";
// Traemos el archivo usuario.php que tiene funciones de sesión
require_once "conexion.php";
// Traemos el archivo de conexión a la base de datos

$mensaje = "";
// Creamos una variable vacía para guardar mensajes de error o éxito
$exito = false;
// Variable que nos dice si el login fue exitoso o no

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Checamos si el formulario se envió (si alguien le dio click a "Iniciar Sesión")
    $correo = trim($_POST["correo"] ?? "");
    // Obtenemos el correo del formulario y le quitamos espacios con trim
    // El ?? "" significa que si no existe, poner una cadena vacía
    $contrasena = $_POST["contrasena"] ?? "";
    // Obtenemos la contraseña del formulario
    if ($correo === "" || $contrasena === "") {
        // Si el correo o la contraseña están vacíos
        $mensaje = "Ingresa Tu Correo Y Contraseña";
        // Guardamos un mensaje de error
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        // Si el correo no tiene formato válido (no tiene @ o está mal escrito)
        $mensaje = "correo invalido";
        // Mensaje de error para correo inválido
    } else {
        // Si todo está bien, buscamos el usuario en la base de datos
        $sql = "SELECT id, nombre, correo, contrasena_hash FROM usuarios WHERE correo = ?";
        // Query SQL para buscar al usuario por su correo
        $stmt = $conexion->prepare($sql);
        // Preparamos la consulta (esto previene inyecciones SQL)
        $stmt->bind_param("s", $correo);
        // Le pasamos el correo como parámetro (la "s" significa string)
        $stmt->execute();
        // Ejecutamos la consulta
        $resultado = $stmt->get_result();
        // Obtenemos el resultado de la búsqueda
        if ($resultado && $resultado->num_rows === 1) {
            // Si encontramos exactamente 1 usuario con ese correo
            $usuario = $resultado->fetch_assoc();
            // Convertimos el resultado en un array con los datos del usuario
            if (password_verify($contrasena, $usuario["contrasena_hash"])) {
                // Verificamos si la contraseña que escribió coincide con la guardada
                // password_verify compara la contraseña en texto plano con el hash
                
                // Si la contraseña es correcta, establecemos la sesión
                $_SESSION["usuario_id"] = $usuario["id"];
                // Guardamos el ID del usuario en la sesión
                $_SESSION["usuario_nombre"] = $usuario["nombre"];
                // Guardamos el nombre del usuario en la sesión
                $_SESSION["usuario_correo"] = $usuario["correo"];
                // Guardamos el correo del usuario en la sesión
                
                // Checamos si el usuario es administrador
                $stmt_admin = $conexion->prepare("SELECT id FROM admins WHERE email = ? AND estado = 'activo'");
                // Buscamos en la tabla de admins si este correo está ahí
                $stmt_admin->bind_param("s", $usuario["correo"]);
                // Le pasamos el correo como parámetro
                $stmt_admin->execute();
                // Ejecutamos la consulta
                $resultado_admin = $stmt_admin->get_result();
                // Obtenemos el resultado
                $_SESSION["es_admin"] = ($resultado_admin && $resultado_admin->num_rows > 0);
                // Si encontramos el correo en admins, es_admin = true, si no = false
                $stmt_admin->close();
                // Cerramos la consulta de admin
                
                $mensaje = " 🧪 Bienvenido a Lab-Explorer, " . $usuario["nombre"] . "!";
                // Mensaje de bienvenida con el nombre del usuario
                $exito = true;
                // Marcamos que el login fue exitoso
                // No redirigimos inmediatamente, primero mostramos el modal
            } else {
                // Si la contraseña no coincide
                $mensaje = " ⚠️Correo o contraseña incorrectos.";
                // Mensaje de error (no decimos cuál está mal por seguridad)
            }
        } else {
            // Si no encontramos ningún usuario con ese correo
            $mensaje = " ⚠️Correo no encontrado.";
            // Mensaje de error
        }
        $stmt->close();
        // Cerramos la consulta
    }
}
?>
<!-- Cerramos PHP -->
<!DOCTYPE html>
<!-- Le decimos al navegador que esto es HTML5 -->
<html lang="en">
<!-- Abrimos el HTML (está en inglés pero debería ser "es") -->
<head>
<!-- Aquí van los metadatos -->
    <meta charset="UTF-8">
    <!-- Para que se vean bien los acentos -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Para que se vea bien en celulares -->
    <title>Inicio de sesión Lab-Explorer</title>
    <!-- Título que aparece en la pestaña del navegador -->
    <link href="../assets/css/inicio-sesion.css" rel="stylesheet">
    <!-- Cargamos el CSS del login -->
</head>
<!-- Cerramos el head -->
<body>
<!-- Abrimos el body -->
    <form method="post" class="formulario" novalidate>
    <!-- Formulario que se envía por POST -->
    <!-- novalidate desactiva la validación automática del navegador -->
        <div class="logo-lab">
        <!-- Contenedor del logo y título -->
            <img src="../assets/img/logo/logo-lab.ico" alt="Logo Lab">
            <!-- Logo del laboratorio -->
            <h1>Inicio de Sesión Lab-Explorer</h1>
            <!-- Título principal -->
            <p class="subtitulo">Lab explorer (cbtis52)</p>
            <!-- Subtítulo con el nombre de la escuela -->
        </div>
        <!-- Cerramos logo-lab -->
        <section class="seccion-informacion">
        <!-- Sección donde van los inputs -->
            <label>Correo</label>
            <!-- Etiqueta para el input de correo -->
            <input type="email" name="correo" value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>" required>
            <!-- Input de correo -->
            <!-- value mantiene el correo escrito si hubo error -->
            <!-- required hace que sea obligatorio -->
            <label>Contraseña</label>
            <!-- Etiqueta para el input de contraseña -->
            <input type="password" name="contrasena" required minlength="6">
            <!-- Input de contraseña -->
            <!-- minlength="6" requiere mínimo 6 caracteres -->
        </section>
        <!-- Cerramos seccion-informacion -->
        <section class="seccion-botones">
        <!-- Sección de botones y links -->
            <button type="submit">Iniciar Sesión</button>
            <!-- Botón para enviar el formulario -->
            <p>¿No tienes cuenta? <a href="register.php">Crea una Cuenta</a></p>
            <!-- Link para ir al registro -->
            <p>Olvidaste tu contraseña <a href="recuperar.php">Recuperar Contraseña</a></p>
            <!-- Link para recuperar contraseña -->
            <p><a href="admins/login-admin.php">Acceso Panel Admin</a></p>
            <!-- Link para que los admins entren a su panel -->
        </section>
        <!-- Cerramos seccion-botones -->
    </form>
    <!-- Cerramos el formulario -->
    <?php if ($mensaje): ?>
    <!-- Si hay un mensaje (error o éxito), mostramos el modal -->
        <div class="modal-mensaje <?= $exito ? 'exito' : 'error' ?>">
        <!-- Modal que cambia de clase según si fue éxito o error -->
            <div class="modal-contenido">
            <!-- Contenido del modal -->
                <h2><?= $exito ? '🧪 Bienvenido A Lab Explorer!' : 'Error' ?></h2>
                <!-- Título del modal (cambia según éxito o error) -->
                <p><?= htmlspecialchars($mensaje) ?></p>
                <!-- Mostramos el mensaje -->
                <?php if ($exito): ?>
                <!-- Si fue exitoso -->
                    <p style="font-style: italic; margin-top: 15px;">Serás redirigido automáticamente en 2 segundos...</p>
                    <!-- Mensaje que dice que se va a redirigir -->
                <?php else: ?>
                <!-- Si hubo error -->
                    <button onclick="cerrarmodal()">Cerrar Modal</button>
                    <!-- Botón para cerrar el modal de error -->
                <?php endif; ?>
                <!-- Cerramos el if/else -->
            </div>
            <!-- Cerramos modal-contenido -->
        </div>
        <!-- Cerramos modal-mensaje -->
        <script>
        // Abrimos JavaScript
            function cerrarmodal() {
            // Función para cerrar el modal
                document.querySelector('.modal-mensaje').style.display='none';
                // Buscamos el modal y lo ocultamos cambiando su display a none
            }
            // Cerramos la función
            <?php if ($exito): ?>
            // Si el login fue exitoso
            setTimeout(function() {
            // Esperamos 2 segundos (2000 milisegundos)
                window.location.href = '../index.php';
                // Redirigimos al index
            }, 2000);
            // Cerramos setTimeout
            <?php endif; ?>
            <!-- Cerramos el if de PHP -->
        </script>
        <!-- Cerramos el script -->
    <?php endif; ?>
    <!-- Cerramos el if de mostrar modal -->
</body>
<!-- Cerramos el body -->
</html>
<!-- Cerramos el HTML -->
