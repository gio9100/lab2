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

// Incluimos las validaciones centralizadas
require_once '../validaciones.php';
// Nota: usamos ../ porque estamos en una subcarpeta

// Recuperamos dominios permitidos desde la BD para el frontend
$dominios_extra_db = obtenerDominiosExtra($conn);


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
        // Si todo está bien hasta ahora
        elseif(preg_match('/[0-9]/', $nombre)) {
            $mensaje = "El nombre no puede contener números";
        }
        elseif(!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\'-]+$/', $nombre)) {
            $mensaje = "El nombre solo puede contener letras, espacios, tildes y guiones";
        }
    else {
        
        // Validamos usando la función centralizada
        // Pasamos 'publicador' como tipo para verificar permisos específicos
        if(!esCorreoPermitido($correo, 'publicador', $conn)) {
            // Si el correo no es válido/autorizado
            $mensaje = "Este correo institucional no está autorizado para registro de publicadores.";
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
    <title>Registro Publicadores - Lab-Explora</title>
    <!-- Título de la pestaña -->
    
    <link rel="stylesheet" href="../../assets/css/registro.css">
    <!-- Cargamos el CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Iconos para el widget de accesibilidad -->
    <style>
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
        
        /* FIX LOGO DIRECTO */
        .logo-Lab img {
            width: 250px !important;
            max-width: 100% !important;
            height: auto !important;
            display: block;
            margin: 0 auto 10px auto;
        }

        /* FIX DE SCROLL Y CORTE EN PANTALLAS PEQUEÑAS */
        body {
            align-items: flex-start !important; /* Evita que se corte arriba */
            padding-top: 40px !important;
            padding-bottom: 40px !important;
            height: auto !important;
            min-height: 100vh;
        }
    </style>
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
                <h1>Registro Publicadores Lab-Explora</h1>
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
        // Lista de dominios válidos incluyendo DB
    // Lista de dominios válidos incluyendo DB
        const dominiosValidos = [
            'gmail.com',
            'outlook.com',
            'outlook.es',
            <?php 
            foreach($dominios_extra_db as $d) {
                echo "'$d',";
            }
            ?>
        ];

        // Lista de correos específicos válidos (Excepciones logradas con la nueva función)
        const correosValidos = [
            <?php 
            $correos_extra_db = obtenerCorreosExtra($conn);
            foreach($correos_extra_db as $c) {
                echo "'$c',";
            }
            ?>
        ];

        const correoInput = document.getElementById('correo');
        const mensajeCorreo = document.getElementById('mensaje-correo');

        // Validación del correo en tiempo real
        if (correoInput) {
            correoInput.addEventListener('input', function() {
                const val = this.value.trim().toLowerCase();
                
                if (!val) {
                    this.classList.remove('error', 'success');
                    mensajeCorreo.textContent = '';
                    mensajeCorreo.className = 'mensaje-validacion';
                    return;
                }
            
                // Verificar formato básico
                if (!val.includes('@')) {
                    this.classList.add('error');
                    this.classList.remove('success');
                    mensajeCorreo.textContent = '✗ Correo incompleto';
                    mensajeCorreo.className = 'mensaje-validacion error';
                    return;
                }

                // 1. CHEQUEO DIRECTO: ¿Es un correo específico permitido?
                if (correosValidos.includes(val)) {
                    this.classList.remove('error');
                    this.classList.add('success');
                    mensajeCorreo.textContent = '✓ Correo autorizado';
                    mensajeCorreo.className = 'mensaje-validacion success';
                    return; 
                }
            
                // 2. Si no es un correo específico, revisamos el dominio
                const partes = val.split('@');
                const dominio = partes[1] || '';
            
                if (!dominiosValidos.includes(dominio)) {
                    this.classList.add('error');
                    this.classList.remove('success');
                    mensajeCorreo.textContent = '✗ Dominio no permitido';
                    mensajeCorreo.className = 'mensaje-validacion error';
                } else {
                    this.classList.remove('error');
                    this.classList.add('success');
                    mensajeCorreo.textContent = '✓ Dominio válido';
                    mensajeCorreo.className = 'mensaje-validacion success';
                }
            });
        }

        const contrasenaInput = document.getElementById('contrasena');
        const mensajeContrasena = document.getElementById('mensaje-contrasena');

        // Validación de contraseña en tiempo real
        if (contrasenaInput) {
            contrasenaInput.addEventListener('input', function() {
                const val = this.value;
                
                if (!val) {
                    this.classList.remove('error', 'success');
                    mensajeContrasena.style.display = 'none';
                    return;
                }

                if (val.length >= 6) {
                    this.classList.remove('error');
                    this.classList.add('success');
                    mensajeContrasena.textContent = '✓ Contraseña válida';
                    mensajeContrasena.style.color = 'green';
                    mensajeContrasena.style.display = 'block';
                } else {
                    this.classList.remove('success');
                    this.classList.add('error');
                    mensajeContrasena.textContent = '✗ Mínimo 6 caracteres';
                    mensajeContrasena.style.color = 'red';
                    mensajeContrasena.style.display = 'block';
                }
            });
        }

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
    <script>
// Validación del nombre en tiempo real (bloquea números mientras escribes)
const nombreInputPub = document.getElementById('nombre');

if (nombreInputPub) {
    nombreInputPub.addEventListener('input', function() {
        // Removemos números mientras el usuario escribe
        this.value = this.value.replace(/[0-9]/g, '');
        
        // Removemos caracteres especiales (excepto espacios, tildes, ñ, apóstrofes y guiones)
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'\-]/g, '');
        
        // Removemos espacios múltiples
        this.value = this.value.replace(/\s{2,}/g, ' ');
    });
}

    </script>
    <!-- Cerramos script -->
    <!-- Script de validaciones frontend adicional (capa extra de seguridad) -->
    <script src="../../assets/js/validaciones-frontend.js"></script>
    <!-- Cargamos el archivo de validaciones como medida de seguridad adicional -->
    <script>
        // Validar antes de enviar
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            // ... (validaciones)
        });
    </script>
    <script src="../../assets/js/accessibility-widget.js?v=3.2"></script>
</body>
<!-- Cerramos body -->
</html>
<!-- Cerramos HTML -->