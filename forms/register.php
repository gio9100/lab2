<?php 
// Abrimos PHP
require_once("usuario.php");
// Traemos el archivo usuario.php para manejar sesiones
require_once "conexion.php";
// Traemos la conexión a la base de datos
$mensaje = "";
// Variable para guardar mensajes de error o éxito
$exito= false;
// Variable que dice si el registro fue exitoso

// Lista de dominios de correo que aceptamos en el sitio
$dominios_validos = [
    'gmail.com',
    // Aceptamos Gmail
    'outlook.com',
    // Aceptamos Outlook.com
    'outlook.es',
    // Aceptamos Outlook.es
];
// Cerramos el array de dominios válidos

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Si el formulario se envió (alguien le dio click a "Crear Cuenta")

   // Obtenemos los datos del formulario
   $nombre = trim($_POST["nombre"] ?? "");
   // Obtenemos el nombre y le quitamos espacios al inicio y final
   $correo = trim($_POST["correo"] ?? "");
   // Obtenemos el correo y le quitamos espacios
   $correo = mb_strtolower($correo, 'UTF-8');
   // Convertimos el correo a minúsculas para evitar duplicados (Juan@gmail.com = juan@gmail.com)
   $contrasena = $_POST["contrasena"] ?? "";
   // Obtenemos la contraseña

   if ($nombre === "" || $correo === "" || $contrasena === "") {
       // Si algún campo está vacío
       $mensaje = "Completa todos los campos";
       // Mensaje de error
   }
   elseif(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
       // Si el correo no tiene formato válido (no tiene @ o está mal escrito)
       $mensaje = "El correo no tiene un formato valido";
       // Mensaje de error
   }
     elseif(preg_match('/[0-9]/', $nombre)) {
       $mensaje = "El nombre no puede contener números";
   }
   elseif(!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\'-]+$/', $nombre)) {
       $mensaje = "El nombre solo puede contener letras, espacios, tildes y guiones";
   }
   else {
       // Verificamos que el dominio del correo esté en la lista de permitidos
       $partes_correo = explode('@', $correo);
       // Separamos el correo en dos partes: antes y después del @
       // Ejemplo: "juan@gmail.com" se convierte en ["juan", "gmail.com"]
       $dominio = $partes_correo[1] ?? '';
       // Obtenemos la segunda parte (el dominio)

       if(!in_array($dominio, $dominios_validos)) {
           // Si el dominio NO está en nuestra lista de permitidos
           $dominios_lista = implode(',', array_slice($dominios_validos, 0, 5));
           // Convertimos el array de dominios en un texto separado por comas
           // array_slice toma solo los primeros 5 (por si hay muchos)
           $mensaje = "Solo se permiten correos de dominio verificados como:" . $dominios_lista . ", etc.";
           // Mensaje de error mostrando los dominios permitidos
       }
       elseif (strlen($contrasena) < 6) {
           // Si la contraseña tiene menos de 6 caracteres
           $mensaje = "la contraseña debe tener al menos 6 caracteres";
           // Mensaje de error
       }
       else {
           // Si todo está correcto, procedemos a registrar al usuario

           $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
           // Encriptamos la contraseña para guardarla segura en la BD
           // NUNCA guardamos contraseñas en texto plano
           
           // Aquí está el INSERT para guardar el nuevo usuario
           $sql = "INSERT INTO usuarios (nombre, correo, contrasena_hash) VALUES (?,?,?)";
           // Query SQL para insertar un nuevo usuario
           $stmt = $conexion->prepare($sql);
           // Preparamos la consulta (previene inyecciones SQL)
           $stmt->bind_param("sss", $nombre, $correo, $contrasena_hash);
           // Le pasamos los 3 parámetros (las 3 "s" significan que son strings)

           if ($stmt->execute()) {
               // Si el INSERT fue exitoso
               $mensaje = "registro exitoso. Ahora inicia sesion";
               // Mensaje de éxito
               $exito=true;
               // Marcamos que fue exitoso
            } else {
                // Si hubo un error al insertar (probablemente correo duplicado)
                $mensaje = " ⚠️Error al registrar. El correo ya está en uso.";
                // Mensaje de error
            }
        $stmt->close();
        // Cerramos la consulta
    }
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
<title>Registro Lab-Explorer</title>
<!-- Título que aparece en la pestaña del navegador -->
<link rel="stylesheet" href="../assets/css/registro.css">
<!-- Cargamos el CSS del registro -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Para que se vea bien en celulares -->
<style>
/* Abrimos CSS dentro del HTML */
.mensaje-validacion {
    /* Estilos para los mensajes de validación */
    font-size: 0.95rem;
    /* Tamaño de letra un poco más pequeño */
    margin-top: 3px;
    /* Espacio arriba */
    margin-bottom: 5px;
    /* Espacio abajo */
    font-weight: 500;
    /* Grosor de la letra medio */
}
/* Cerramos la clase */

.mensaje-validacion.error {
    /* Estilos para mensajes de error */
    color: #dc3545;
    /* Color rojo */
}
/* Cerramos la clase */

.mensaje-validacion.success {
    /* Estilos para mensajes de éxito */
    color: #28a745;
    /* Color verde */
}
/* Cerramos la clase */

input.error {
    /* Estilos para inputs con error */
    border-color: #dc3545 !important;
    /* Borde rojo (el !important fuerza el estilo) */
}
/* Cerramos la clase */

input.success {
    /* Estilos para inputs correctos */
    border-color: #28a745 !important;
    /* Borde verde */
}
/* Cerramos la clase */
</style>
<!-- Cerramos el CSS -->
</head>
<!-- Cerramos el head -->
<body>
<!-- Abrimos el body -->

<form method="post" class="formulario" novalidate>
<!-- Formulario que se envía por POST -->
<!-- novalidate desactiva la validación automática del navegador -->
    <div class="logo-Lab">
    <!-- Contenedor del logo y título -->
        <img src="../assets/img/logo/logobrayan2.ico" alt="Logo Lab">
        <!-- Logo del laboratorio -->
        <h1>Registro Lab-Explorer</h1>
        <!-- Título principal -->
        <p class="subtitulo">Lab Explorer (cbtis52)</p>
        <!-- Subtítulo con el nombre de la escuela -->
    </div>
    <!-- Cerramos logo-Lab -->

    <section class="seccion-informacion">
    <!-- Sección donde van los inputs -->
        <label>Nombre Completo</label>
        <!-- Etiqueta para el input de nombre -->
        <input type="text" 
               name="nombre" 
               id="nombre"
               placeholder="Ej: Edwin Herrera" 
               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
               required>
        <!-- Input de nombre -->
        <!-- placeholder es el texto de ejemplo que se ve -->
        <!-- value mantiene el nombre escrito si hubo error -->
        <!-- required hace que sea obligatorio -->

        <label>Correo Electrónico</label>
        <!-- Etiqueta para el input de correo -->
        <input type="email" 
               id="correo" 
               name="correo" 
               placeholder="ejemplo@gmail.com"
               value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
               required>
        <!-- Input de correo -->
        <div id="mensaje-correo" class="mensaje-validacion"></div>
        <!-- Div vacío donde JavaScript mostrará mensajes de validación -->

        <label>Contraseña</label>
        <!-- Etiqueta para el input de contraseña -->
        <input type="password" 
               id="contrasena"
               name="contrasena" 
               placeholder="Mínimo 6 caracteres"
               required 
               minlength="6">
        <!-- Input de contraseña -->
        <!-- minlength="6" requiere mínimo 6 caracteres -->
        <div id="mensaje-contrasena" class="mensaje-validacion"></div>
        <!-- Div vacío donde JavaScript mostrará mensajes de validación -->
    </section>
    <!-- Cerramos seccion-informacion -->

    <section class="seccion-botones">
    <!-- Sección de botones y links -->
        <button type="submit">Crear Cuenta</button>
        <!-- Botón para enviar el formulario -->
        <p>¿Ya tienes cuenta? <a href="inicio-sesion.php">Inicia sesión</a></p>
        <!-- Link para ir al login -->
    </section>
    <!-- Cerramos seccion-botones -->
</form>
<!-- Cerramos el formulario -->


<?php if($mensaje && $exito): ?>
<!-- Si hay mensaje Y fue exitoso, mostramos el modal de éxito -->
<div class="modal-mensaje exito">
<!-- Modal con clase de éxito (fondo verde) -->
    <div class="modal-contenido">
    <!-- Contenido del modal -->
        <h2>🧪 Registro Completado</h2>
        <!-- Título del modal con emoji -->
        <p><?= htmlspecialchars($mensaje) ?></p>
        <!-- Mostramos el mensaje -->
        <p style="font-style: italic; margin-top: 15px;">
            Serás redirigido automáticamente en 2 segundos...
        </p>
        <!-- Mensaje que dice que se va a redirigir -->
    </div>
    <!-- Cerramos modal-contenido -->
</div>
<!-- Cerramos modal-mensaje -->
<script>
// Abrimos JavaScript
setTimeout(function() {
    // Esperamos 2 segundos (2000 milisegundos)
    window.location.href = 'inicio-sesion.php';
    // Redirigimos al login
}, 2000);
// Cerramos setTimeout
</script>
<!-- Cerramos el script -->
<?php endif; ?>
<!-- Cerramos el if de PHP -->

<script>
// Abrimos JavaScript para validaciones en tiempo real

// Lista de dominios válidos en JavaScript (igual que en PHP)
const dominiosValidos = [
    'gmail.com',
    'outlook.com',
    'outlook.es',
];
// Cerramos el array

// Validación del correo en tiempo real
const correoInput = document.getElementById('correo');
// Obtenemos el input de correo
const mensajeCorreo = document.getElementById('mensaje-correo');
// Obtenemos el div donde mostraremos mensajes

correoInput.addEventListener('input', function() {
    // Cada vez que el usuario escribe algo en el correo
    const val = this.value.trim().toLowerCase();
    // Obtenemos el valor, le quitamos espacios y lo convertimos a minúsculas
    
    if (!val) {
        // Si el campo está vacío
        correoInput.classList.remove('error', 'success');
        // Quitamos las clases de error y éxito
        mensajeCorreo.textContent = '';
        // Borramos el mensaje
        mensajeCorreo.className = 'mensaje-validacion';
        // Dejamos solo la clase base
        return;
        // Salimos de la función
    }

    // Verificar si tiene @
    if (!val.includes('@')) {
        // Si no tiene arroba
        correoInput.classList.add('error');
        // Agregamos clase de error (borde rojo)
        correoInput.classList.remove('success');
        // Quitamos clase de éxito
        mensajeCorreo.textContent = '✗ Formato de correo inválido';
        // Mostramos mensaje de error
        mensajeCorreo.className = 'mensaje-validacion error';
        // Agregamos clase de error (texto rojo)
        return;
        // Salimos de la función
    }

    // Extraer el dominio del correo
    const partes = val.split('@');
    // Separamos el correo por el @
    const dominio = partes[1] || '';
    // Obtenemos la parte después del @

    // Verificar si el dominio está en la lista de permitidos
    if (!dominiosValidos.includes(dominio)) {
        // Si el dominio NO está en la lista
        correoInput.classList.add('error');
        // Borde rojo
        correoInput.classList.remove('success');
        // Quitamos verde
        mensajeCorreo.textContent = '✗ Dominio no permitido';
        // Mensaje de error
        mensajeCorreo.className = 'mensaje-validacion error';
        // Texto rojo
    } else {
        // Si el dominio SÍ está en la lista
        correoInput.classList.remove('error');
        // Quitamos rojo
        correoInput.classList.add('success');
        // Agregamos verde
        mensajeCorreo.textContent = '✓ Dominio válido';
        // Mensaje de éxito
        mensajeCorreo.className = 'mensaje-validacion success';
        // Texto verde
    }
});
// Cerramos el addEventListener

// Validación de contraseña en tiempo real
const contrasenaInput = document.getElementById('contrasena');
// Obtenemos el input de contraseña
const mensajeContrasena = document.getElementById('mensaje-contrasena');
// Obtenemos el div donde mostraremos mensajes

contrasenaInput.addEventListener('input', function() {
    // Cada vez que el usuario escribe en la contraseña
    const val = this.value;
    // Obtenemos el valor
    
    if (!val) {
        // Si el campo está vacío
        contrasenaInput.classList.remove('error', 'success');
        // Quitamos clases
        mensajeContrasena.textContent = '';
        // Borramos mensaje
        mensajeContrasena.className = 'mensaje-validacion';
        // Dejamos clase base
        return;
        // Salimos
    }

    if (val.length < 6) {
        // Si tiene menos de 6 caracteres
        contrasenaInput.classList.add('error');
        // Borde rojo
        contrasenaInput.classList.remove('success');
        // Quitamos verde
        mensajeContrasena.textContent = '✗ Mínimo 6 caracteres';
        // Mensaje de error
        mensajeContrasena.className = 'mensaje-validacion error';
        // Texto rojo
    } else {
        // Si tiene 6 o más caracteres
        contrasenaInput.classList.remove('error');
        // Quitamos rojo
        contrasenaInput.classList.add('success');
        // Agregamos verde
        mensajeContrasena.textContent = '✓ Contraseña válida';
        // Mensaje de éxito
        mensajeContrasena.className = 'mensaje-validacion success';
        // Texto verde
    }
});
// Cerramos el addEventListener

</script>
<!-- Cerramos el script -->
<!-- Script de validaciones frontend adicional (capa extra de seguridad) -->
<script src="../../assets/js/validaciones-frontend.js"></script>
<!-- Cargamos el archivo de validaciones como medida de seguridad adicional -->
<script>
// Validación del nombre en tiempo real (bloquea números mientras escribes)
const nombreInput = document.getElementById('nombre');

if (nombreInput) {
    nombreInput.addEventListener('input', function() {
        // Removemos números mientras el usuario escribe
        this.value = this.value.replace(/[0-9]/g, '');
        
        // Removemos caracteres especiales (excepto espacios, tildes, ñ, apóstrofes y guiones)
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'\-]/g, '');
        
        // Removemos espacios múltiples
        this.value = this.value.replace(/\s{2,}/g, ' ');
    });
}
</script>
</body>
<!-- Cerramos el body -->
</html>
<!-- Cerramos el HTML -->