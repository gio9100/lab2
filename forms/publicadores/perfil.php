<?php
// Iniciar sesión
session_start();
// Incluir configuración
require_once 'config-publicadores.php';

// Verificar sesión
if (!isset($_SESSION['publicador_id'])) {
    header('Location: login.php');
    exit();
}

$publicador_id = $_SESSION['publicador_id'];

// Obtener datos del publicador
$query = "SELECT * FROM publicadores WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $publicador_id);
$stmt->execute();
$result = $stmt->get_result();
$publicador = $result->fetch_assoc();
$stmt->close();

// Procesar actualización de perfil
$mensaje = "";
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar datos
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $biografia = trim($_POST['biografia'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    
    // Validaciones
    if (empty($nombre) || empty($email)) {
        $mensaje = "El nombre y el email son obligatorios";
        $exito = false;
    } else {
        // Verificar si el email ya existe (excepto el actual)
        $check_email = $conn->prepare("SELECT id FROM publicadores WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $publicador_id);
        $check_email->execute();
        $email_result = $check_email->get_result();
        
        if ($email_result->num_rows > 0) {
            $mensaje = "El email ya está en uso por otro publicador";
            $exito = false;
        } else {
            // Actualizar datos
            $update = $conn->prepare("UPDATE publicadores SET nombre = ?, email = ?, especialidad = ?, biografia = ?, telefono = ? WHERE id = ?");
            $update->bind_param("sssssi", $nombre, $email, $especialidad, $biografia, $telefono, $publicador_id);
            
            if ($update->execute()) {
                // Actualizar sesión
                $_SESSION['publicador_nombre'] = $nombre;
                $_SESSION['publicador_email'] = $email;
                $_SESSION['publicador_especialidad'] = $especialidad;
                
                // Recargar datos locales
                $publicador['nombre'] = $nombre;
                $publicador['email'] = $email;
                $publicador['especialidad'] = $especialidad;
                $publicador['biografia'] = $biografia;
                $publicador['telefono'] = $telefono;
                
                $mensaje = "✅ Perfil actualizado correctamente";
                $exito = true;
            } else {
                $mensaje = "Error al actualizar el perfil";
                $exito = false;
            }
            $update->close();
        }
        $check_email->close();
    }
}

// Procesar cambio de contraseña
if (isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';
    
    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
        $mensaje = "Todos los campos de contraseña son obligatorios";
        $exito = false;
    } elseif ($password_nueva !== $password_confirmar) {
        $mensaje = "Las contraseñas nuevas no coinciden";
        $exito = false;
    } elseif (strlen($password_nueva) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres";
        $exito = false;
    } else {
        // Verificar contraseña actual
        if (password_verify($password_actual, $publicador['password'])) {
            // Hashear nueva contraseña
            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            $update_pass = $conn->prepare("UPDATE publicadores SET password = ? WHERE id = ?");
            $update_pass->bind_param("si", $password_hash, $publicador_id);
            
            if ($update_pass->execute()) {
                $mensaje = "✅ Contraseña actualizada correctamente";
                $exito = true;
            } else {
                $mensaje = "Error al actualizar la contraseña";
                $exito = false;
            }
            $update_pass->close();
        } else {
            $mensaje = "La contraseña actual es incorrecta";
            $exito = false;
        }
    }
}

$publicador_nombre = $_SESSION['publicador_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Lab-Explora</title>
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- Main CSS -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
</head>
<body class="publicador-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explora</h1><span></span>
                </a>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <span class="saludo">🧪 Publicador: <?= htmlspecialchars($publicador_nombre) ?></span>
                        <a href="logout-publicadores.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- Sidebar -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="index-publicadores.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Panel Principal
                            </a>
                            <a href="crear_nueva_publicacion.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-plus-circle me-2"></i>Nueva Publicación
                            </a>
                            <a href="mis-publicaciones.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-text me-2"></i>Mis Publicaciones
                            </a>
                            <a href="perfil.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-person me-2"></i>Mi Perfil
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contenido Principal -->
                <div class="col-md-9">
                    <!-- Mensajes -->
                    <?php if($mensaje): ?>
                    <div class="alert-message <?= $exito ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="close-btn">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Mi Perfil</h2>
                        <p class="text-muted">Gestiona tu información personal y configuración de cuenta</p>
                    </div>

                    <div class="row">
                        <!-- Información Personal -->
                        <div class="col-lg-8 mb-4">
                            <div class="admin-card" data-aos="fade-up">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-person-circle me-2"></i>
                                        Información Personal
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="nombre" class="form-label fw-bold">Nombre Completo *</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                                   value="<?= htmlspecialchars($publicador['nombre']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-bold">Email *</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?= htmlspecialchars($publicador['email']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="especialidad" class="form-label fw-bold">Especialidad</label>
                                            <input type="text" class="form-control" id="especialidad" name="especialidad" 
                                                   value="<?= htmlspecialchars($publicador['especialidad'] ?? '') ?>"
                                                   placeholder="Ej: Biología Molecular, Química Orgánica, etc.">
                                        </div>

                                        <div class="mb-3">
                                            <label for="telefono" class="form-label fw-bold">Teléfono</label>
                                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                                   value="<?= htmlspecialchars($publicador['telefono'] ?? '') ?>"
                                                   placeholder="Ej: +52 123 456 7890">
                                        </div>

                                        <div class="mb-3">
                                            <label for="biografia" class="form-label fw-bold">Biografía</label>
                                            <textarea class="form-control" id="biografia" name="biografia" rows="4"
                                                      placeholder="Cuéntanos sobre tu experiencia y áreas de investigación..."><?= htmlspecialchars($publicador['biografia'] ?? '') ?></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Guardar Cambios
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Cuenta -->
                        <div class="col-lg-4 mb-4">
                            <div class="admin-card" data-aos="fade-up" data-aos-delay="100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Información de Cuenta
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">Estado de la cuenta</small>
                                        <span class="badge bg-success">Activo</span>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">Fecha de registro</small>
                                        <strong><?= date('d/m/Y', strtotime($publicador['fecha_registro'])) ?></strong>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">ID de Publicador</small>
                                        <strong>#<?= $publicador['id'] ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cambiar Contraseña -->
                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="admin-card" data-aos="fade-up">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-shield-lock me-2"></i>
                                        Cambiar Contraseña
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="cambiar_password" value="1">
                                        
                                        <div class="mb-3">
                                            <label for="password_actual" class="form-label fw-bold">Contraseña Actual *</label>
                                            <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password_nueva" class="form-label fw-bold">Nueva Contraseña *</label>
                                            <input type="password" class="form-control" id="password_nueva" name="password_nueva" 
                                                   minlength="6" required>
                                            <small class="text-muted">Mínimo 6 caracteres</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password_confirmar" class="form-label fw-bold">Confirmar Nueva Contraseña *</label>
                                            <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" 
                                                   minlength="6" required>
                                        </div>

                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-key me-2"></i>Cambiar Contraseña
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>

    <!-- Main JS-->
    <script src="../../assets/js/main.js"></script>

    <script>
        // Inicializar AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Cerrar alertas
        document.querySelectorAll('.close-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>
</html>
