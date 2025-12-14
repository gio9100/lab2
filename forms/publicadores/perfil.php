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
    
    // --- CASO 1: ACTUALIZACIÓN DE PERFIL ---
    if (isset($_POST['actualizar_perfil'])) {
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
                $sql = "UPDATE publicadores SET nombre = ?, email = ?, especialidad = ?, biografia = ?, telefono = ? WHERE id = ?";
                $update = $conn->prepare($sql);
                $update->bind_param("sssssi", $nombre, $email, $especialidad, $biografia, $telefono, $publicador_id);
                
                if ($update->execute()) {
                    // Actualizar sesión y datos locales
                    $_SESSION['publicador_nombre'] = $nombre;
                    $_SESSION['publicador_email'] = $email;
                    $_SESSION['publicador_especialidad'] = $especialidad;
                    
                    $publicador['nombre'] = $nombre;
                    $publicador['email'] = $email;
                    $publicador['especialidad'] = $especialidad;
                    $publicador['biografia'] = $biografia;
                    $publicador['telefono'] = $telefono;
                    
                    $mensaje = "✅ Perfil actualizado correctamente";
                    $exito = true;
                } else {
                    $mensaje = "Error al actualizar el perfil: " . $conn->error;
                    $exito = false;
                }
                $update->close();
            }
            $check_email->close();
        }
    }

    // --- CASO 2: SUBIDA DE FOTO ---
    if (isset($_FILES['foto_perfil']) && isset($_POST['subir_foto'])) {
        if ($_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto_perfil']['tmp_name'];
            $file_name = $_FILES['foto_perfil']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_name = "pub_" . $publicador_id . "_" . time() . "." . $file_ext;
                $upload_dir = "../../assets/img/uploads/";
                
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                $dest_path = $upload_dir . $new_name;

                if (move_uploaded_file($file_tmp, $dest_path)) {
                    // Actualizar solo la columna foto_perfil
                    $stmt_foto = $conn->prepare("UPDATE publicadores SET foto_perfil = ? WHERE id = ?");
                    $stmt_foto->bind_param("si", $dest_path, $publicador_id);
                    if ($stmt_foto->execute()) {
                         $publicador['foto_perfil'] = $dest_path; // Actualizar vista
                         $mensaje = "✅ Foto actualizada correctamente";
                         $exito = true;
                    } else {
                         $mensaje = "Error al guardar ruta en BD";
                    }
                    $stmt_foto->close();
                } else {
                    $mensaje = "Error al mover la imagen subida";
                }
            } else {
                $mensaje = "Formato de imagen no permitido";
            }
        } else {
             $mensaje = "Error en la subida de imagen";
        }
    }

    // --- CASO 3: ELIMINAR FOTO ---
    if (isset($_POST['eliminar_foto'])) {
        // Verificar si tiene foto
        if (!empty($publicador['foto_perfil']) && file_exists($publicador['foto_perfil'])) {
            // Intentar borrar archivo físico (opcional, buena práctica)
            @unlink($publicador['foto_perfil']);
        }
        
        // Actualizar BD a NULL
        $stmt_del = $conn->prepare("UPDATE publicadores SET foto_perfil = NULL WHERE id = ?");
        $stmt_del->bind_param("i", $publicador_id);
        
        if ($stmt_del->execute()) {
            $publicador['foto_perfil'] = null; // Actualizar vista
            $mensaje = "✅ Foto eliminada correctamente";
            $exito = true;
        } else {
            $mensaje = "Error al eliminar la foto";
            $exito = false;
        }
        $stmt_del->close();
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

    <!-- LIBRERÍA para generar PDF (Credencial) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        /* Estilos específicos para la credencial digital (Estilo Admin) */
        .credential-card {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); /* Degradado azul profesional */
            color: white;
            border-radius: 15px; /* Bordes redondeados */
            padding: 20px;
            width: 100%;
            max-width: 400px; /* Ancho máximo tipo tarjeta */
            margin: 0 auto; /* Centrado */
            box-shadow: 0 10px 20px rgba(0,0,0,0.2); /* Sombra para efecto 3D */
            position: relative;
            border: 2px solid rgba(255,255,255,0.2);
        }
        .credential-header {
            border-bottom: 2px solid rgba(255,255,255,0.3);
            padding-bottom: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
        .credential-body {
            display: flex; /* Flexbox para alinear foto y texto */
            align-items: center;
            gap: 15px;
        }
        .credential-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%; /* Foto redonda */
            background-color: white;
            padding: 2px;
            object-fit: cover;
        }
        .credential-info h3 {
            font-size: 1.2rem;
            margin: 0;
            font-weight: 700;
        }
        .credential-info p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .credential-footer {
            margin-top: 15px;
            text-align: center;
            font-size: 0.8rem;
            opacity: 0.8;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 10px;
        }
        /* Clase para ocultar elementos al generar PDF */
        @media print {
            .no-print {
                display: none;
            }
        }

        /* ESTILOS PARA LA FIRMA ELECTRÓNICA VISUAL */
        .signature-box {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed rgba(255,255,255,0.3);
            text-align: center;
        }
        .signature-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
            margin-bottom: 2px;
        }
        .signature-hash {
            font-family: 'Courier New', monospace;
            font-size: 0.65rem;
            word-break: break-all;
            background: rgba(0,0,0,0.2);
            padding: 2px 4px;
            border-radius: 4px;
            color: #e0f7fa;
            letter-spacing: -0.5px;
        }

        /* OFFICIAL SEAL */
        .credential-seal {
            position: absolute;
            bottom: 60px;
            right: 20px;
            width: 80px;
            height: 80px;
            border: 3px double rgba(255,255,255,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-15deg);
            opacity: 0.7;
            pointer-events: none;
        }
        .credential-seal span {
            font-size: 0.6em;
            font-weight: bold;
            color: rgba(255,255,255,0.9);
            text-align: center;
            text-transform: uppercase;
            line-height: 1.2;
            border-top: 1px solid rgba(255,255,255,0.4);
            border-bottom: 1px solid rgba(255,255,255,0.4);
            padding: 4px 0;
        }
    </style>
    <!-- PWA MANIFEST & SW -->
    <link rel="manifest" href="/lab2/manifest.json">
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('/lab2/sw.js')
            .then(reg => console.log('Service Worker registrado', reg))
            .catch(err => console.log('Error SW:', err));
        });
      }
    </script>
</head>
<body class="publicador-page">

    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <!-- Hamburger Button -->
                    <button class="btn btn-outline-primary d-md-none me-2" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <!-- Logo -->
                    <a href="../../pagina-principal.php" class="logo d-flex align-items-end">
                        <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                        <h1 class="sitename">Lab-Explora</h1><span></span>
                    </a>
                </div>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <span class="saludo d-none d-md-inline">🧪 Publicador: <?= htmlspecialchars($publicador_nombre) ?></span>
                        <a href="logout-publicadores.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- Sidebar (Desktop & Mobile Overlay) -->
                <div class="col-md-3 sidebar-wrapper" id="sidebarWrapper">
                     <!-- Mobile Close Button -->
                    <div class="d-flex justify-content-end d-md-none p-2">
                        <button class="btn-close" id="sidebarClose"></button>
                    </div>
                    <?php include 'sidebar-publicador.php'; ?>
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
                        <div class="col-lg-7 mb-4">
                            <div class="admin-card" data-aos="fade-up">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-person-circle me-2"></i>
                                        Información Personal
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="actualizar_perfil" value="1">
                                        <!-- (Foto movida a la derecha) -->

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

                                    <hr class="my-4">

                                    <!-- Cambiar Contraseña -->
                                    <h6 class="fw-bold mb-3"><i class="bi bi-key me-2"></i>Cambiar Contraseña</h6>
                                    <form method="POST" action="">
                                        <input type="hidden" name="cambiar_password" value="1">
                                        
                                        <div class="mb-3">
                                            <label for="password_actual" class="form-label">Contraseña Actual *</label>
                                            <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password_nueva" class="form-label">Nueva Contraseña *</label>
                                            <input type="password" class="form-control" id="password_nueva" name="password_nueva" 
                                                   minlength="6" required>
                                            <small class="text-muted">Mínimo 6 caracteres</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password_confirmar" class="form-label">Confirmar *</label>
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


                        <!-- Información de Cuenta y Credencial -->
                        <div class="col-lg-5 mb-4">
                            
                            <!-- TARJETA PARA SUBIR FOTO (Arriba de la credencial) -->
                            <div class="admin-card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Foto de Perfil</h5>
                                </div>
                                <div class="card-body text-center">
                                    <form method="POST" action="" enctype="multipart/form-data" class="d-flex flex-column align-items-center gap-3">
                                        <input type="hidden" name="subir_foto" value="1">
                                        <!-- Mostrar foto actual pequeña -->
                                        <img src="<?= !empty($publicador['foto_perfil']) ? htmlspecialchars($publicador['foto_perfil']) : '../../assets/img/defecto.png' ?>" 
                                             alt="Vista previa" class="rounded-circle" style="width: 64px; height: 64px; object-fit: cover;">
                                        
                                        <div class="w-100">
                                            <label for="foto_perfil_input" class="form-label visually-hidden">Subir nueva foto</label>
                                            <input type="file" class="form-control" id="foto_perfil_input" name="foto_perfil" accept="image/*" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="bi bi-upload me-1"></i> Actualizar Foto
                                        </button>
                                    </form>
                                    
                                    <!-- Formulario separado para eliminar foto -->
                                    <?php if (!empty($publicador['foto_perfil'])): ?>
                                        <form method="POST" action="" class="w-100 mt-2">
                                            <input type="hidden" name="eliminar_foto" value="1">
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('¿Estás seguro de eliminar tu foto de perfil?')">
                                                <i class="bi bi-trash me-1"></i> Eliminar Foto
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- TARJETA DE CREDENCIAL -->
                            <div class="admin-card mb-4" data-aos="fade-up">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><i class="bi bi-person-badge me-2"></i>Credencial</h5>
                                    <button class="btn btn-outline-danger btn-sm no-print" onclick="descargarCredencial()">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i>Descargar Oficial
                                    </button>
                                </div>
                                <div class="card-body bg-light d-flex justify-content-center">
                                    <div id="credencial-content" class="credential-card w-100">
                                        
                                        <div class="credential-header">
                                            <!-- Logo y Título -->
                                            <div class="d-flex align-items-center justify-content-center mb-2">
                                                <img src="../../assets/img/logo/logobrayan2.ico" alt="Logo" style="width: 40px; margin-right: 10px; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3));">
                                                <h4 style="margin:0; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">Lab-Explora</h4>
                                            </div>
                                            <small style="letter-spacing: 2px; text-transform: uppercase; font-size: 0.75rem;">Acreditación Oficial</small>
                                        </div>

                                        <div class="credential-body d-flex align-items-center">
                                            <!-- Avatar Genérico o Usuario -->
                                            <div style="flex-shrink: 0;">
                                                <img src="<?= !empty($publicador['foto_perfil']) ? htmlspecialchars($publicador['foto_perfil']) . '?v='.time() : '../../assets/img/defecto.png' ?>" 
                                                     alt="Avatar" class="credential-avatar" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;">
                                            </div>
                                            
                                            <div class="credential-info ms-3 flex-grow-1">
                                                <h3 style="font-size: 1.1rem; margin-bottom: 2px;"><?= htmlspecialchars($publicador['nombre']) ?></h3>
                                                <p style="font-size: 0.85rem; word-break: break-all; opacity: 0.9;"><?= htmlspecialchars($publicador['email']) ?></p>
                                                <span class="badge bg-warning text-dark mt-1 shadow-sm" style="font-size: 0.75rem;">
                                                    <?= strtoupper($publicador['especialidad'] ?: 'Investigador') ?>
                                                </span>
                                            </div>
                                            
                                            <!-- CODIGO QR -->
                                            <?php
                                                // Generamos la URL de verificacion
                                                $data_to_hash = $publicador['id'] . $publicador['nombre'] . $publicador['email'] . "LAB_EXPLORA_PUB_SECURE_KEY";
                                                $hash_seguro = strtoupper(substr(hash('sha256', $data_to_hash), 0, 24));
                                                
                                                // URL Publica
                                                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                                                $verification_url = "$protocol://" . $_SERVER['HTTP_HOST'] . "/lab2/verificar-credencial.php?id=$publicador_id&tipo=publicador&hash=$hash_seguro";
                                                
                                                // API QR Publica
                                                // Nota: margin=0 para quitar borde blanco extra si la API lo soporta, qzone=1
                                                $qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=" . urlencode($verification_url);
                                            ?>
                                            <div style="flex-shrink: 0; margin-left: 10px;">
                                                <img src="<?= $qr_api ?>" alt="QR Verificacion" 
                                                     style="width: 80px; height: 80px; border-radius: 8px; border: 2px solid white; background: white; padding: 2px; display: block;">
                                            </div>
                                        </div>

                                        <div class="credential-footer">
                                            <p>Miembro desde: <?= date('d/m/Y', strtotime($publicador['fecha_registro'])) ?></p>
                                            <p>ID: #<?= str_pad($publicador['id'], 4, '0', STR_PAD_LEFT) ?></p>

                                            <div class="mt-2 mb-2 p-2" style="background: rgba(0,0,0,0.1); border-radius: 5px; font-size: 0.7rem; text-align: justify;">
                                                <strong>ROL: PUBLICADOR</strong><br>
                                                Esta credencial digital valida la capacidad del usuario para publicar contenido científico en Lab-Explora. La firma electrónica inferior es una cadena única e irrepetible generada criptográficamente, lo que garantiza su autenticidad y evita duplicaciones o falsificaciones de identidad.
                                            </div>

                                            <!-- FIRMA ELECTRÓNICA VISUAL -->
                                            <div class="signature-box" style="margin-top: 15px; border-top: 1px dashed rgba(255,255,255,0.4); padding-top: 5px;">
                                                <div class="signature-label" style="font-size: 0.6rem; text-transform: uppercase;">Firma Digital Verificada</div>
                                                <div class="signature-hash" style="font-family: 'Courier New', monospace; font-size: 0.7rem; background: rgba(0,0,0,0.1); padding: 2px 4px; border-radius: 4px;">
                                                    <?php 
                                                    // Generamos un hash visual único para el publicador
                                                    $data_to_hash = $publicador['id'] . $publicador['nombre'] . $publicador['email'] . "LAB_EXPLORA_PUB_SECURE_KEY";
                                                    echo strtoupper(substr(hash('sha256', $data_to_hash), 0, 24)); 
                                                    ?>
                                                </div>
                                            </div>
                                            <!-- SELLO OFICIAL -->
                                            <div class="credential-seal">
                                                <span>Official<br>Certified<br>Member</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 2FA -->
                            <div class="admin-card mb-4" data-aos="fade-up">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-shield-lock me-2"></i>
                                        Verificación en 2 Pasos
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-2">
                                        Protege tu cuenta con seguridad extra.
                                    </p>
                                    <div id="2fa-status" class="mb-2">
                                        <!-- El estado se carga dinámicamente -->
                                    </div>
                                    <button id="toggle-2fa-btn" class="btn btn-primary btn-sm w-100" onclick="togglear2FA()">
                                        <i class="bi bi-gear"></i> <span id="btn-text">Cargando...</span>
                                    </button>
                                </div>
                            </div>
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
        // Sidebar Toggle Logic
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarWrapper = document.getElementById('sidebarWrapper');
        const sidebarClose = document.getElementById('sidebarClose');

        if(sidebarToggle && sidebarWrapper) {
            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);

            function toggleSidebar() {
                sidebarWrapper.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            }

            sidebarToggle.addEventListener('click', toggleSidebar);
            if(sidebarClose) sidebarClose.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
        }
    </script>
    <script>
        // Función para descargar PDF
        function descargarCredencial() {
            const elemento = document.getElementById('credencial-content');
            const opciones = {
                margin:       [10, 10, 10, 10], // Margenes top, left, bottom, right
                filename:     'Credencial_LabExplorer_<?= preg_replace("/[^a-zA-Z0-9]/", "_", $publicador['nombre']) ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { 
                    scale: 2, 
                    useCORS: true, 
                    scrollY: 0,
                    logging: true 
                },
                jsPDF:        { unit: 'mm', format: 'a5', orientation: 'portrait' } 
            };
            html2pdf().set(opciones).from(elemento).save();
        }
    </script>

    <!-- JavaScript para 2FA -->
    <script>
    function cargarEstado2FA() {
        fetch('../toggle_2fa.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'accion=verificar'
        })
        .then(res => res.json())
        .then(data => {
            const statusDiv = document.getElementById('2fa-status');
            const btn = document.getElementById('toggle-2fa-btn');
            const btnText = document.getElementById('btn-text');
            
            if (data.enabled) {
                statusDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-shield-check"></i> <strong>ACTIVADA</strong> - Tu cuenta está protegida</div>';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-danger');
                btnText.textContent = 'Desactivar 2FA';
            } else {
                statusDiv.innerHTML = '<div class="alert alert-warning"><i class="bi bi-shield-exclamation"></i> <strong>DESACTIVADA</strong> - Actívala para mayor seguridad</div>';
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-success');
                btnText.textContent = 'Activar 2FA';
            }
        });
    }

    function togglear2FA() {
        const btn = document.getElementById('toggle-2fa-btn');
        const esActivar = btn.classList.contains('btn-success');
        const accion = esActivar ? 'activar' : 'desactivar';
        const mensaje = esActivar ? '¿Activar verificación en 2 pasos?' : '¿Desactivar 2FA?';
        
        if (!confirm(mensaje)) return;
        
        fetch('../toggle_2fa.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'accion=' + accion
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                cargarEstado2FA();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }

    if (document.getElementById('2fa-status')) {
        cargarEstado2FA();
    }
    </script>
</body>
</html>
