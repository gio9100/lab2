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

// Incluimos el archivo de validaciones compartidas (Lógica centralizada)
require_once "validaciones.php";
// Obtenemos los dominios extra desde la base de datos para pasarlos a JS (UX)
// Esto ayuda a que el usuario vea "verde" si usa un dominio corporativo permitido
$dominios_extra_db = obtenerDominiosExtra($conexion);



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
       // Llamamos a nuestra función personalizada de validación
       // Verificamos si el correo está autorizado en la BD o es un dominio público
       if (!esCorreoPermitido($correo, 'usuario', $conexion)) {
           // Si la función devuelve false, mostramos el error
           $mensaje = "Este correo o dominio no está autorizado para el registro.";
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
<title>Registro Lab-Explora</title>
<!-- Título que aparece en la pestaña del navegador -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/inicio-sesion.css">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    /* Abrimos CSS dentro del HTML */
    .mensaje-validacion {
        font-size: 0.95rem;
        margin-top: 3px;
        margin-bottom: 5px;
        font-weight: 500;
    }
    .mensaje-validacion.error {
        color: #dc3545;
    }
    .mensaje-validacion.success {
        color: #28a745;
    }
    input.error {
        border-color: #dc3545 !important;
    }
    input.success {
        border-color: #28a745 !important;
    }
    /* Override body alignment for tall forms */
    body {
        align-items: flex-start !important;
        padding-top: 40px;
        padding-bottom: 40px;
        height: auto !important;
    }
    </style>
</head>
<body>

    <div class="container-fluid min-vh-100 d-flex justify-content-center py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                
                <form method="post" class="formulario" novalidate>
                    <div class="logo-lab text-center mb-4">
                        <img src="../assets/img/logo/logo-labexplora.png" alt="Logo Lab" class="mb-3">
                        <h1>Registro Lab-Explora</h1>
                        <p class="subtitulo">Lab Explora (cbtis52)</p>
                    </div>

                    <section class="seccion-informacion mb-4">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" 
                                   name="nombre" 
                                   id="nombre"
                                   class="form-control"
                                   placeholder="Ej: Edwin Herrera" 
                                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" 
                                   id="correo" 
                                   name="correo" 
                                   class="form-control"
                                   placeholder="ejemplo@gmail.com"
                                   value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                                   required>
                            <div id="mensaje-correo" class="mensaje-validacion" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" 
                                   id="contrasena"
                                   name="contrasena" 
                                   class="form-control"
                                   placeholder="Mínimo 6 caracteres"
                                   required 
                                   minlength="6">
                            <div id="mensaje-contrasena" class="mensaje-validacion" role="alert" aria-live="polite"></div>
                        </div>
                    </section>

                    <section class="seccion-botones text-center">
                        <button type="submit" class="btn btn-primary w-100 mb-3">Crear Cuenta</button>
                        <div class="d-flex flex-column gap-2">
                            <p class="mb-0">¿Ya tienes cuenta? <a href="inicio-sesion.php" class="text-decoration-none">Inicia sesión</a></p>
                            <p class="mb-0"><a href="../index.php" class="text-decoration-none">← Volver al sitio principal</a></p>
                        </div>
                    </section>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


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

<?php if($mensaje && !$exito): ?>
<!-- MODAL DE ERROR NUEVO -->
<div class="modal-mensaje error" style="display:flex;">
    <div class="modal-contenido">
        <h2 style="color: #dc3545;">⚠️ Error en el Registro</h2>
        <p><?= htmlspecialchars($mensaje) ?></p>
        <button onclick="this.closest('.modal-mensaje').style.display='none'" 
                style="margin-top: 15px; padding: 8px 16px; border: none; background: #dc3545; color: white; border-radius: 4px; cursor: pointer;">
            Entendido
        </button>
    </div>
</div>
<?php endif; ?>
<!-- Cerramos el if de PHP -->

<script>
// Abrimos JavaScript para validaciones en tiempo real

// Lista de dominios válidos en JavaScript
// Combinamos los default + los que vienen de la Base de Datos
const dominiosValidos = [
    'gmail.com',
    'outlook.com',
    'outlook.es',
    <?php 
    // Inyectamos los dominios extra de la BD en el array de JS
    // json_encode convierte el array PHP a formato JS (seguro)
    foreach($dominios_extra_db as $d) {
        echo "'$d',";
    }
    ?>
];
// Array listo con todos los dominios permitidos

// Lista de correos específicos válidos (Excepciones)
const correosValidos = [
    <?php 
    // Inyectamos los correos específicos extra de la BD
    $correos_extra_db = obtenerCorreosExtra($conexion);
    foreach($correos_extra_db as $c) {
        echo "'$c',";
    }
    ?>
];

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

    // AQUI ESTA EL CAMBIO: Verificamos si es un correo EXACTO permitido ANTES de revisar el dominio
    if (correosValidos.includes(val)) {
        // Si el correo completo está en la lista blanca
        correoInput.classList.remove('error');
        correoInput.classList.add('success');
        mensajeCorreo.textContent = '✓ Correo autorizado';
        mensajeCorreo.className = 'mensaje-validacion success';
        return; // Es válido, salimos
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
<script src="../assets/js/validaciones-frontend.js"></script>
<!-- Cargamos el archivo de validaciones como medida de seguridad adicional -->
<script>
// Validación del nombre en tiempo real (bloquea números mientras escribes)
const nombreInput = document.getElementById('nombre');

if (nombreInput) {
    // Usamos la función global de validaciones-frontend.js
    nombreInput.addEventListener('input', function() {
        if (typeof validarNombreEnTiempoReal === 'function') {
            validarNombreEnTiempoReal(this);
        } else {
            // Fallback por si no cargó el script
             this.value = this.value.replace(/[0-9]/g, '');
             this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'\-]/g, '');
             this.value = this.value.replace(/\s{2,}/g, ' ');
        }
    });
}
</script>
    <script src="../assets/js/accessibility-widget.js?v=3.2"></script>
</body>
<!-- Cerramos el body -->
</html>
<!-- Cerramos el HTML -->