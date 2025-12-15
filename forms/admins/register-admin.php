<?php
// Iniciar sesión
session_start();
// Incluir configuración
require_once "config-admin.php";

// Verificar si ya está logueado
if (isset($_SESSION['admin_id'])) {
    header('Location: index-admin.php');
    exit();
}

// Claves secretas para registro
define('CLAVE_ADMIN', 'labexplora2025');
define('CLAVE_SUPERADMIN', 'superlabexplora2025');

$mensaje = "";
$exito = false;

// Dominios permitidos
$dominios_validos = [
    'gmail.com',
    'outlook.com',
    'outlook.es',
];

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Obtener y limpiar datos
    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $email = mb_strtolower($email, 'UTF-8');
    $password = $_POST["password"] ?? "";
    $clave_maestra = trim($_POST["clave_maestra"] ?? "");
    $nivel = $_POST["nivel"] ?? "admin";

    // Validaciones
    if ($nombre === "" || $email === "" || $password === "" || $clave_maestra === "") {
        $mensaje = "Completa todos los campos";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El email no tiene un formato válido";
    }
    elseif(preg_match('/[0-9]/', $nombre)) {
        $mensaje = "El nombre no puede contener números";
    }
    elseif(!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\'-]+$/', $nombre)) {
        $mensaje = "El nombre solo puede contener letras, espacios, tildes y guiones";
    }
    else {
        // Validar dominio
        $partes_email = explode('@', $email);
        $dominio = $partes_email[1] ?? '';

        if(!in_array($dominio, $dominios_validos)) {
            $dominios_lista = implode(',', array_slice($dominios_validos, 0, 5));
            $mensaje = "Solo se permiten correos de: " . $dominios_lista;
        }
        elseif (strlen($password) < 6) {
            $mensaje = "La contraseña debe tener al menos 6 caracteres";
        }
        else {
            // Validar clave secreta según nivel
            $clave_valida = false;
            
            if ($nivel == 'admin' && $clave_maestra === CLAVE_ADMIN) {
                $clave_valida = true;
            } 
            elseif ($nivel == 'superadmin' && $clave_maestra === CLAVE_SUPERADMIN) {
                $clave_valida = true;
            }
            
            if (!$clave_valida) {
                $mensaje = "Clave secreta incorrecta para el nivel de administrador seleccionado";
            }
            else {
                // Verificar si ya existe
                if (adminExiste($email, $conn)) {
                    $mensaje = "Este email ya está registrado como administrador";
                } else {
                    // Registrar nuevo admin
                    $datos = [
                        'nombre' => $nombre,
                        'email' => $email,
                        'password' => $password,
                        'nivel' => $nivel
                    ];
                    
                    if (registrarAdmin($datos, $conn)) {
                        $mensaje = ucfirst($nivel) . " registrado exitosamente";
                        $exito = true;
                        
                        // Redirección
                        echo "
                        <script>
                            setTimeout(function() {
                                window.location.href = 'login-admin.php';
                            }, 2000);
                        </script>
                        ";
                    } else {
                        $mensaje = "Error al registrar administrador";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Administrador - Lab-Explora</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/registro.css">
</head>
<body>
    <div class="form-container">
        <form method="post" class="formulario" novalidate>
            
            <div class="logo-Lab">
                <img src="../../assets/img/logo/logo-labexplora.png" alt="logo-lab">
                <h1>Registro Administrador</h1>
                <p class="subtitulo">Panel de Administración Lab-Explora</p>
            </div>

            <section class="seccion-informacion">
                
                <label>Nombre Completo</label>
                <input type="text" 
                       name="nombre" 
                       placeholder="Ej: Administrador Principal" 
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                       required>

                <label>Email</label>
                <input type="email" 
                       name="email" 
                       placeholder="admin@labexplora.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>

                <label>Contraseña</label>
                <input type="password" 
                       name="password" 
                       placeholder="Mínimo 6 caracteres"
                       required 
                       minlength="6">

                <label>Nivel de Administrador</label>
                <select name="nivel" required>
                    <option value="admin">Administrador</option>
                    <option value="superadmin">Super Administrador</option>
                </select>

                <label>Clave Secreta</label>
                <input type="password" 
                       name="clave_maestra" 
                       placeholder="Clave Secreta"
                       required>
                
            </section>

            <section class="seccion-botones">
                <button type="submit">Registrar Administrador</button>
                <p>¿Ya tienes cuenta? <a href="login-admin.php">Inicia sesión como administrador</a></p>
                <p><a href="../../pagina-principal.php">← Volver al sitio principal</a></p>
            </section>
        </form>
    </div>

    <?php if($mensaje): ?>
    <div class="modal-mensaje <?= $exito ? 'exito' : 'error' ?>">
        <div class="modal-contenido">
            <h2><?= $exito ? "🧪 Administrador Registrado" : "⚠️ Error" ?></h2>
            <p><?= htmlspecialchars($mensaje) ?></p>
            
            <?php if($exito): ?>
                <p style="font-style: italic; margin-top: 15px;">
                    Serás redirigido al login en 2 segundos...
                </p>
            <?php else: ?>
                <button onclick="cerrarModal()">Cerrar</button>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function cerrarModal() { 
            document.querySelector('.modal-mensaje').style.display='none';
        }
    </script>
    <script src="../../assets/js/validaciones-frontend.js"></script>
    <?php endif; ?>
    
    <script>
        // Validación en tiempo real
        const nombreInput = document.querySelector('input[name="nombre"]');

        if (nombreInput) {
            nombreInput.addEventListener('input', function() {
                this.value = this.value.replace(/[0-9]/g, '');
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'\-]/g, '');
                this.value = this.value.replace(/\s{2,}/g, ' ');
            });
        }
    </script>
    <script src="../../assets/js/accessibility-widget.js?v=3.2"></script>
</body>
</html>