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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/inicio-sesion.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .mensaje-validacion {
            font-size: 0.95rem;
            margin-top: 3px;
            margin-bottom: 5px;
            font-weight: 500;
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
                        <img src="../../assets/img/logo/logo-labexplora.png" alt="logo-lab" class="mb-3">
                        <h1>Registro Administrador</h1>
                        <p class="subtitulo">Panel de Administración Lab-Explora</p>
                    </div>

                    <section class="seccion-informacion mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" 
                                   name="nombre" 
                                   class="form-control"
                                   placeholder="Ej: Administrador Principal" 
                                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control"
                                   placeholder="admin@labexplora.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control"
                                   placeholder="Mínimo 6 caracteres"
                                   required 
                                   minlength="6">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nivel de Administrador</label>
                            <select name="nivel" class="form-select" required>
                                <option value="admin">Administrador</option>
                                <option value="superadmin">Super Administrador</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Clave Secreta</label>
                            <input type="password" 
                                   name="clave_maestra" 
                                   class="form-control"
                                   placeholder="Clave Secreta"
                                   required>
                        </div>
                        
                    </section>

                    <section class="seccion-botones text-center">
                        <button type="submit" class="btn btn-primary w-100 mb-3">Registrar Administrador</button>
                        <div class="d-flex flex-column gap-2">
                            <p class="mb-0">¿Ya tienes cuenta? <a href="login-admin.php" class="text-decoration-none">Inicia sesión como administrador</a></p>
                            <p class="mb-0"><a href="../../pagina-principal.php" class="text-decoration-none">← Volver al sitio principal</a></p>
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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