<?php
// Abrimos PHP
session_start();
// Iniciamos la sesión

require_once 'config-publicadores.php';
// Traemos las funciones para publicadores
require_once '../admins/enviar_correo_publicador.php';
// Traemos funciones para enviar correos a los admins

// Variables para mensajes
$mensaje = "";
$exito = false;

// Lista de dominios de correo permitidos
$dominios_validos = [
    'gmail.com',
    'outlook.com',
    'outlook.es',
];
// Array con los dominios que aceptamos

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Si el formulario se envió
    
    // Obtenemos los datos del formulario
    $nombre = trim($_POST["nombre"] ?? "");
    // trim() quita espacios al inicio y final
    $correo = trim($_POST["correo"] ?? "");
    $correo = mb_strtolower($correo, 'UTF-8');
    // mb_strtolower() convierte a minúsculas (soporta acentos)
    $contrasena = $_POST["contrasena"] ?? "";
    $especialidad = trim($_POST["especialidad"] ?? "");
    $titulo_academico = trim($_POST["titulo_academico"] ?? "");
    $institucion = trim($_POST["institucion"] ?? "");

    // Validaciones
    if ($nombre === "" || $correo === "" || $contrasena === "" || $especialidad === "") {
        // Si algún campo obligatorio está vacío
        $mensaje = "Completa todos los campos obligatorios";
    }
    elseif(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        // filter_var() valida el formato del correo
        $mensaje = "El correo no tiene un formato válido";
    }
    else {
        // Si todo está bien hasta ahora
        
        $partes_correo = explode('@', $correo);
        // explode() divide el correo en dos partes
        // Ejemplo: "ana@gmail.com" se vuelve ["ana", "gmail.com"]
        $dominio = $partes_correo[1] ?? '';
        // Tomamos la segunda parte (el dominio)

        if(!in_array($dominio, $dominios_validos)) {
            // in_array() busca si el dominio está en la lista
            $dominios_lista = implode(', ', array_slice($dominios_validos, 0, 5));
            // array_slice() toma los primeros 5 elementos
            // implode() los une con comas
            $mensaje = "Solo se permiten correos de dominio verificados como: " . $dominios_lista;
        }
        elseif (strlen($contrasena) < 6) {
            // strlen() cuenta los caracteres
            $mensaje = "La contraseña debe tener al menos 6 caracteres";
        }
        else {
            // Si todo está correcto
            
            if (emailExiste($correo, $conn)) {
                // emailExiste() es una función de config-publicadores.php
                // Verifica si el correo ya está registrado
                $mensaje = "Este correo electrónico ya está registrado";
            } else {
                // Si el correo no existe, procedemos a registrar
                
                $datos = [
                    'nombre' => $nombre,
                    'email' => $correo,
                    'password' => $contrasena,
                    'especialidad' => $especialidad,
                    'titulo_academico' => $titulo_academico,
                    'institucion' => $institucion
                ];
                // Creamos un array con todos los datos
                
                if (registrarPublicador($datos, $conn)) {
                    // registrarPublicador() es una función de config-publicadores.php
                    // Inserta el nuevo publicador en la BD con estado 'pendiente'
                    
                    // Si el usuario está logueado, marcarlo como publicador pendiente
                    if (isset($_SESSION['usuario_id'])) {
                        // Si hay un usuario logueado
                        $_SESSION["es_publicador_pendiente"] = true;
                        // Marcamos que tiene solicitud pendiente
                    }
                    
                    // Notificamos a los administradores
                    enviarCorreoNuevoPublicadorAAdmins($nombre, $correo, $especialidad, $conn);
                    // Esta función envía un correo a todos los admins
                    // para que sepan que hay un nuevo publicador esperando aprobación
                    
                    $mensaje = "Registro exitoso. Tu cuenta está pendiente de aprobación.";
                    $exito = true;
                    $_POST = array();
                    // Limpiamos $_POST para que el formulario se vacíe
                }
            }
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
    <title>Registro Publicadores - Lab-Explorer</title>
    <!-- Título de la pestaña -->
    
    <link rel="stylesheet" href="../../assets/css/registro.css">
    <!-- Cargamos el CSS -->
</head>
<!-- Cerramos head -->
<body>
<!-- Abrimos body -->
    <div class="form-container">
    <!-- Contenedor del formulario -->
        <form method="post" class="formulario" novalidate>
        <!-- Formulario que se envía por POST -->
        <!-- novalidate desactiva la validación automática del navegador -->
            
            <!-- Logo y título -->
            <div class="logo-Lab">
                <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                <!-- Logo -->
                <h1>Registro Publicadores Lab-explorer</h1>
                <!-- Título -->
                <p class="subtitulo">Panel de Publicadores (cbtis52)</p>
                <!-- Subtítulo -->
            </div>
            <!-- Cerramos logo-Lab -->

            <!-- Sección de inputs -->
            <section class="seccion-informacion">
                
                <label>Nombre Completo *</label>
                <!-- Etiqueta (el * indica que es obligatorio) -->
                <input type="text" 
                       name="nombre" 
                       id="nombre"
                       placeholder="Ej: Dr. Juan Pérez" 
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                       required>
                <!-- Input de nombre -->
                <!-- htmlspecialchars() previene ataques XSS -->

                <label>Correo Electrónico *</label>
                <!-- Etiqueta -->
                <input type="email" 
                       id="correo" 
                       name="correo" 
                       placeholder="ejemplo@gmail.com"
                       value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                       required>
                <!-- Input de correo -->
                <div id="mensaje-correo" class="mensaje-correo"></div>
                <!-- Div vacío donde JavaScript mostrará mensajes -->

                <label>Contraseña *</label>
                <!-- Etiqueta -->
                <input type="password" 
                       id="contrasena"
                       name="contrasena" 
                       placeholder="Mínimo 6 caracteres"
                       required 
                       minlength="6">
                <!-- Input de contraseña -->
                <div id="mensaje-contrasena" class="mensaje-validacion"></div>
                <!-- Div para mensajes de validación -->

                <label>Especialidad *</label>
                <!-- Etiqueta -->
                <input type="text" 
                       name="especialidad" 
                       id="especialidad"
                       placeholder="Ej: Bacteriología, Hematología, etc." 
                       value="<?= htmlspecialchars($_POST['especialidad'] ?? '') ?>"
                       required>
                <!-- Input de especialidad -->

                <label>Título Académico</label>
                <!-- Etiqueta (sin *, es opcional) -->
                <input type="text" 
                       name="titulo_academico" 
                       id="titulo_academico"
                       placeholder="Ej: Doctor en Microbiología" 
                       value="<?= htmlspecialchars($_POST['titulo_academico'] ?? '') ?>">
                <!-- Input opcional -->

                <label>Institución</label>
                <!-- Etiqueta (opcional) -->
                <input type="text" 
                       name="institucion" 
                       id="institucion"
                       placeholder="Ej: Hospital General, Universidad Nacional" 
                       value="<?= htmlspecialchars($_POST['institucion'] ?? '') ?>">
                <!-- Input opcional -->
            </section>
            <!-- Cerramos seccion-informacion -->

            <section class="seccion-botones">
            <!-- Sección de botones -->
                <button type="submit">Registrarse como Publicador</button>
                <!-- Botón para enviar -->
                
                <p>¿Ya tienes cuenta? <a href="inicio-sesion-publicadores.php">Inicia sesión</a></p>
                <!-- Link al login -->
                <p><a href="../../index.php">← Volver al sitio principal</a></p>
                <!-- Link para volver -->
            </section>
            <!-- Cerramos seccion-botones -->
        </form>
        <!-- Cerramos formulario -->
    </div>
    <!-- Cerramos form-container -->

    <!-- Modal de mensajes -->
    <?php if($mensaje): ?>
    <!-- Si hay un mensaje -->
    <div class="modal-mensaje <?= $exito ? 'exito' : 'error' ?>">
    <!-- Modal que cambia de clase según éxito o error -->
        <div class="modal-contenido">
        <!-- Contenido del modal -->
            <h2><?= $exito ? "🧪 Registro Completado" : "⚠️ Error" ?></h2>
            <!-- Título dinámico -->
            
            <p><?= htmlspecialchars($mensaje) ?></p>
            <!-- Mensaje -->
            
            <?php if($exito): ?>
            <!-- Si fue exitoso -->
                <p style="font-style: italic; margin-top: 15px;">
                    Tu cuenta está pendiente de aprobación por los administradores.
                </p>
                <!-- Mensaje adicional -->
            <?php else: ?>
            <!-- Si hubo error -->
                <button onclick="cerrarModal()">Cerrar</button>
                <!-- Botón para cerrar -->
            <?php endif; ?>
            <!-- Cerramos if/else -->
        </div>
        <!-- Cerramos modal-contenido -->
    </div>
    <!-- Cerramos modal-mensaje -->
    
    <!-- JavaScript para cerrar el modal -->
    <script>
    // Abrimos JavaScript
        function cerrarModal() { 
        // Función para cerrar el modal
            document.querySelector('.modal-mensaje').style.display='none';
            // querySelector() busca el elemento y lo oculta
        }
        // Cerramos función
    </script>
    <!-- Cerramos script -->
    <?php endif; ?>
    <!-- Cerramos if -->

    <!-- JavaScript para validación en tiempo real -->
    <script>
    // Abrimos JavaScript
        // Lista de dominios válidos (igual que en PHP)
        const dominiosValidos = [
            'gmail.com',
            'outlook.com',
            'outlook.es',
        ];
        // Array de dominios permitidos

        const correoInput = document.getElementById('correo');
        // Obtenemos el input de correo
        const mensajeCorreo = document.getElementById('mensaje-correo');
        // Obtenemos el div de mensajes

        // Validación del correo en tiempo real
        correoInput.addEventListener('input', function() {
            // addEventListener() ejecuta una función cada vez que el usuario escribe
            // 'input' es el evento que se dispara al escribir
            
            const val = this.value.trim().toLowerCase();
            // Obtenemos el valor, quitamos espacios y convertimos a minúsculas
            
            if (!val) {
                // Si está vacío
                correoInput.classList.remove('error', 'success');
                // classList.remove() quita clases CSS
                mensajeCorreo.style.display = 'none';
                // Ocultamos el mensaje
                return;
                // Salimos de la función
            }

            const partesCorreo = val.split('@');
            // split() divide el texto por el @
            const dominio = partesCorreo[1] || '';
            // Tomamos la segunda parte
            
            if (dominiosValidos.includes(dominio)) {
                // includes() busca si el dominio está en el array
                // Es como in_array() de PHP pero en JavaScript
                
                // Correo válido
                correoInput.classList.remove('error');
                correoInput.classList.add('success');
                // classList.add() agrega una clase CSS
                mensajeCorreo.textContent = '✓ Correo válido';
                // textContent cambia el texto del elemento
                mensajeCorreo.style.color = 'green';
                // Cambiamos el color a verde
                mensajeCorreo.style.display = 'block';
                // Mostramos el mensaje
            } else {
                // Correo no válido
                correoInput.classList.remove('success');
                correoInput.classList.add('error');
                mensajeCorreo.textContent = '✗ Dominio no permitido';
                mensajeCorreo.style.color = 'red';
                // Color rojo
                mensajeCorreo.style.display = 'block';
            }
        });
        // Cerramos addEventListener

        const contrasenaInput = document.getElementById('contrasena');
        // Obtenemos el input de contraseña
        const mensajeContrasena = document.getElementById('mensaje-contrasena');
        // Obtenemos el div de mensajes

        // Validación de contraseña en tiempo real
        contrasenaInput.addEventListener('input', function() {
            // Cada vez que el usuario escribe
            
            const val = this.value;
            // Obtenemos el valor
            
            if (!val) {
                // Si está vacío
                contrasenaInput.classList.remove('error', 'success');
                mensajeContrasena.style.display = 'none';
                return;
            }

            if (val.length >= 6) {
                // length es la propiedad que nos dice cuántos caracteres tiene
                // Si tiene 6 o más caracteres
                
                // Contraseña válida
                contrasenaInput.classList.remove('error');
                contrasenaInput.classList.add('success');
                mensajeContrasena.textContent = '✓ Contraseña válida';
                mensajeContrasena.style.color = 'green';
                mensajeContrasena.style.display = 'block';
            } else {
                // Contraseña muy corta
                contrasenaInput.classList.remove('success');
                contrasenaInput.classList.add('error');
                mensajeContrasena.textContent = '✗ Mínimo 6 caracteres';
                mensajeContrasena.style.color = 'red';
                mensajeContrasena.style.display = 'block';
            }
        });
        // Cerramos addEventListener

        <?php if($exito): ?>
        // Si el registro fue exitoso
        setTimeout(function() {
            // setTimeout() ejecuta una función después de un tiempo
            cerrarModal();
            // Cerramos el modal después de 5 segundos
        }, 5000);
        // 5000 milisegundos = 5 segundos
        <?php endif; ?>
        <!-- Cerramos if de PHP -->
    </script>
    <!-- Cerramos script -->
</body>
<!-- Cerramos body -->
</html>
<!-- Cerramos HTML -->