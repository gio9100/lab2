<?php
// Abrimos PHP
session_start();
// Iniciamos la sesión
require_once "config-admin.php";
// Traemos la configuración y funciones de admin

// Verificamos si ya está logueado
if (isset($_SESSION['admin_id'])) {
    // Si ya hay sesión activa
    header('Location: index-admin.php');
    // Lo mandamos al panel de admin
    exit();
    // Detenemos el código
}

// Claves secretas para registrarse
define('CLAVE_ADMIN', 'labexplorer2025');
// define() crea una CONSTANTE (no cambia nunca, no lleva $)
// Esta es la clave para registrarse como admin normal
define('CLAVE_SUPERADMIN', 'superlabexplorer2025');
// Clave para registrarse como superadmin

$mensaje = "";
// Variable para mensajes
$exito = false;
// Variable para saber si fue exitoso

// Dominios de correo permitidos
$dominios_validos = [
    'gmail.com',
    'outlook.com',
    'outlook.es',
];
// Array con los dominios que aceptamos

// Si el formulario se envió
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $nombre = trim($_POST["nombre"] ?? "");
    // trim() quita espacios al inicio y final
    $email = trim($_POST["email"] ?? "");
    
    $email = mb_strtolower($email, 'UTF-8');
    // mb_strtolower() convierte a minúsculas
    // mb_ significa Multi-Byte, funciona con acentos y ñ
    // UTF-8 es la codificación de caracteres
    
    $password = $_POST["password"] ?? "";
    $clave_maestra = trim($_POST["clave_maestra"] ?? "");
    $nivel = $_POST["nivel"] ?? "admin";

    // Validaciones básicas
    if ($nombre === "" || $email === "" || $password === "" || $clave_maestra === "") {
        // Si algún campo está vacío
        $mensaje = "Completa todos los campos";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var() valida datos
        // FILTER_VALIDATE_EMAIL verifica que sea un email válido
        $mensaje = "El email no tiene un formato válido";
    }
        // Si todo está bien hasta ahora
        elseif(preg_match('/[0-9]/', $nombre)) {
            $mensaje = "El nombre no puede contener números";
        }
        elseif(!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\'-]+$/', $nombre)) {
            $mensaje = "El nombre solo puede contener letras, espacios, tildes y guiones";
        }
    else {
        
        $partes_email = explode('@', $email);
        // explode() divide un texto en partes
        // Aquí separamos por el @ 
        // Ejemplo: "juan@gmail.com" se vuelve ["juan", "gmail.com"]
        $dominio = $partes_email[1] ?? '';
        // Tomamos la segunda parte (el dominio)

        if(!in_array($dominio, $dominios_validos)) {
            // in_array() busca si un valor está en un array
            // Si el dominio NO está en nuestra lista
            
            $dominios_lista = implode(',', array_slice($dominios_validos, 0, 5));
            // array_slice() toma una parte del array (primeros 5)
            // implode() une los elementos con comas
            // Resultado: "gmail.com,outlook.com,outlook.es"
            $mensaje = "Solo se permiten correos de: " . $dominios_lista;
        }
        elseif (strlen($password) < 6) {
            // strlen() cuenta cuántos caracteres tiene un texto
            $mensaje = "La contraseña debe tener al menos 6 caracteres";
        }
        else {
            // Validamos la clave secreta según el nivel
            $clave_valida = false;
            
            if ($nivel == 'admin' && $clave_maestra === CLAVE_ADMIN) {
                // Si eligió admin Y la clave coincide
                $clave_valida = true;
            } 
            elseif ($nivel == 'superadmin' && $clave_maestra === CLAVE_SUPERADMIN) {
                // Si eligió superadmin Y la clave coincide
                $clave_valida = true;
            }
            
            if (!$clave_valida) {
                // Si la clave no es correcta
                $mensaje = "Clave secreta incorrecta para el nivel de administrador seleccionado";
            }
            else {
                // Si la clave es correcta
                if (adminExiste($email, $conn)) {
                    // adminExiste() es una función de config-admin.php
                    // Verifica si el email ya está registrado
                    $mensaje = "Este email ya está registrado como administrador";
                } else {
                    // Si el email no existe, procedemos a registrar
                    $datos = [
                        'nombre' => $nombre,
                        'email' => $email,
                        'password' => $password,
                        'nivel' => $nivel
                    ];
                    // Creamos un array con los datos
                    
                    if (registrarAdmin($datos, $conn)) {
                        // registrarAdmin() es una función de config-admin.php
                        // Inserta el nuevo admin en la BD
                        
                        $mensaje = ucfirst($nivel) . " registrado exitosamente";
                        // ucfirst() pone la primera letra en mayúscula
                        // "admin" se vuelve "Admin"
                        $exito = true;
                        
                        echo "
                        <script>
                            setTimeout(function() {
                                window.location.href = 'login-admin.php';
                            }, 2000);
                        </script>
                        ";
                        // setTimeout() espera 2 segundos y luego redirige
                    } else {
                        $mensaje = "Error al registrar administrador";
                    }
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
    <title>Registro Administrador - Lab-Explorer</title>
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
            
            <div class="logo-Lab">
            <!-- Contenedor del logo -->
                <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                <!-- Logo -->
                <h1>Registro Administrador</h1>
                <!-- Título -->
                <p class="subtitulo">Panel de Administración Lab-Explorer</p>
                <!-- Subtítulo -->
            </div>
            <!-- Cerramos logo-Lab -->

            <section class="seccion-informacion">
            <!-- Sección de inputs -->
                
                <label>Nombre Completo</label>
                <!-- Etiqueta -->
                <input type="text" 
                       name="nombre" 
                       placeholder="Ej: Administrador Principal" 
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                       required>
                <!-- Input de nombre -->
                <!-- htmlspecialchars() convierte caracteres especiales en HTML -->
                <!-- Previene ataques XSS (Cross-Site Scripting) -->

                <label>Email</label>
                <!-- Etiqueta -->
                <input type="email" 
                       name="email" 
                       placeholder="admin@labexplorer.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
                <!-- Input de email -->

                <label>Contraseña</label>
                <!-- Etiqueta -->
                <input type="password" 
                       name="password" 
                       placeholder="Mínimo 6 caracteres"
                       required 
                       minlength="6">
                <!-- Input de contraseña -->

                <label>Nivel de Administrador</label>
                <!-- Etiqueta -->
                <select name="nivel" required>
                <!-- select es un menú desplegable -->
                    <option value="admin">Administrador</option>
                    <!-- Opción 1 -->
                    <option value="superadmin">Super Administrador</option>
                    <!-- Opción 2 -->
                </select>
                <!-- Cerramos select -->

                <label>Clave Secreta</label>
                <!-- Etiqueta -->
                <input type="password" 
                       name="clave_maestra" 
                       placeholder="Clave Secreta"
                       required>
                <!-- Input para la clave secreta -->
                
            </section>
            <!-- Cerramos seccion-informacion -->

            <section class="seccion-botones">
            <!-- Sección de botones -->
                <button type="submit">Registrar Administrador</button>
                <!-- Botón para enviar -->
                <p>¿Ya tienes cuenta? <a href="login-admin.php">Inicia sesión como administrador</a></p>
                <!-- Link al login -->
                <p><a href="../index.php">← Volver al sitio principal</a></p>
                <!-- Link para volver -->
            </section>
            <!-- Cerramos seccion-botones -->
        </form>
        <!-- Cerramos formulario -->
    </div>
    <!-- Cerramos form-container -->

    <?php if($mensaje): ?>
    <!-- Si hay un mensaje -->
    <div class="modal-mensaje <?= $exito ? 'exito' : 'error' ?>">
    <!-- Modal que cambia de clase según éxito o error -->
        <div class="modal-contenido">
        <!-- Contenido del modal -->
            <h2><?= $exito ? "🧪 Administrador Registrado" : "⚠️ Error" ?></h2>
            <!-- Título del modal -->
            <p><?= htmlspecialchars($mensaje) ?></p>
            <!-- Mensaje -->
            
            <?php if($exito): ?>
            <!-- Si fue exitoso -->
                <p style="font-style: italic; margin-top: 15px;">
                    Serás redirigido al login en 2 segundos...
                </p>
                <!-- Mensaje de redirección -->
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
    
    <script>
    // Abrimos JavaScript
        function cerrarModal() { 
        // Función para cerrar el modal
            document.querySelector('.modal-mensaje').style.display='none';
            // querySelector() busca un elemento
            // Cambiamos su display a none (lo ocultamos)
        }
        // Cerramos función
    </script>
    <!-- Cerramos script -->
    <!-- Script de validaciones frontend adicional (capa extra de seguridad) -->
    <script src="../../assets/js/validaciones-frontend.js"></script>
    <!-- Cargamos el archivo de validaciones como medida de seguridad adicional -->
    <?php endif; ?>
    <!-- Cerramos if -->
    
    <script>
        // Validación del nombre en tiempo real (bloquea números mientras escribes)
        const nombreInput = document.querySelector('input[name="nombre"]');

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
<!-- Cerramos body -->
</html>
<!-- Cerramos HTML -->