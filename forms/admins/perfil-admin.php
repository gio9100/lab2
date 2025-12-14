<?php
// forms/admins/perfil-admin.php

// Iniciar sesión para acceder a las variables de $_SESSION
// Esto es necesario para verificar si el administrador está logueado
session_start();

// Verificamos si existe la variable de sesión 'admin_id'
// Si no existe, significa que no ha iniciado sesión, así que lo mandamos al login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login-admin.php");
    exit(); // Detenemos la ejecución del script aquí
}

// Configuración de la base de datos
// Definimos las credenciales para conectar a MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

// Crear conexión
// Instanciamos un objeto mysqli para interactuar con la BD
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
// Si hay error, matamos el proceso y mostramos el error
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset a UTF-8 para evitar problemas con tildes y ñ
$conn->set_charset("utf8mb4");

// Obtenemos el ID del admin desde la sesión
$admin_id = $_SESSION['admin_id'];
$mensaje = ""; // Variable para mensajes de éxito/error

// Procesar formulario de actualización
// Si el método de la solicitud es POST, significa que enviaron el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- CASO 1: ACTUALIZACIÓN DE DATOS (Nombre, Email, Password) ---
    if (isset($_POST['actualizar_datos'])) {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password_nueva = $_POST['password_nueva'] ?? '';

        if (empty($nombre) || empty($email)) {
            $mensaje = "❌ El nombre y el correo son obligatorios.";
        } else {
            $update_fields = ["nombre = ?", "email = ?"];
            $params = [$nombre, $email];
            $types = "ss";

            if (!empty($password_nueva)) {
                $update_fields[] = "password = ?";
                $params[] = password_hash($password_nueva, PASSWORD_DEFAULT);
                $types .= "s";
            }
            
            $sql = "UPDATE admins SET " . implode(", ", $update_fields) . " WHERE id = ?";
            $params[] = $admin_id;
            $types .= "i";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $_SESSION['admin_nombre'] = $nombre;
                $_SESSION['admin_email'] = $email;
                $mensaje = "✅ Perfil actualizado correctamente.";
            } else {
                $mensaje = "❌ Error al actualizar: " . $conn->error;
            }
        }
    }

    // --- CASO 2: SUBIDA DE FOTO SEPARADA ---
    if (isset($_FILES['foto_perfil']) && isset($_POST['subir_foto'])) {
        if ($_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
             $file_tmp = $_FILES['foto_perfil']['tmp_name'];
             $file_name = $_FILES['foto_perfil']['name'];
             $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
             $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
             
             if (in_array($file_ext, $allowed_exts)) {
                 $new_name = "admin_" . $admin_id . "_" . time() . "." . $file_ext;
                 $upload_dir = "../../assets/img/uploads/";
                 if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                 
                 $dest_path = $upload_dir . $new_name;
                 
                 if (move_uploaded_file($file_tmp, $dest_path)) {
                     // Update DB
                     $stmt_img = $conn->prepare("UPDATE admins SET foto_perfil = ? WHERE id = ?");
                     $stmt_img->bind_param("si", $dest_path, $admin_id);
                     if ($stmt_img->execute()) {
                         $mensaje = "✅ Foto actualizada correctamente.";
                     } else {
                         $mensaje = "❌ Error al guardar en BD.";
                     }
                 } else {
                     $mensaje = "❌ Error al mover archivo.";
                 }
             } else {
                 $mensaje = "❌ Formato no permitido.";
             }
        }
    }

    // --- CASO 3: ELIMINAR FOTO ---
    if (isset($_POST['eliminar_foto'])) {
        // Obtenemos la foto actual
        $query_foto = "SELECT foto_perfil FROM admins WHERE id = ?";
        $stmt_f = $conn->prepare($query_foto);
        $stmt_f->bind_param("i", $admin_id);
        $stmt_f->execute();
        $res_f = $stmt_f->get_result();
        $adm_data = $res_f->fetch_assoc();
        $stmt_f->close();

        if (!empty($adm_data['foto_perfil']) && file_exists($adm_data['foto_perfil'])) {
            @unlink($adm_data['foto_perfil']);
        }
        
        $stmt_del = $conn->prepare("UPDATE admins SET foto_perfil = NULL WHERE id = ?");
        $stmt_del->bind_param("i", $admin_id);
        
        if ($stmt_del->execute()) {
            $mensaje = "✅ Foto eliminada correctamente.";
        } else {
            $mensaje = "❌ Error al eliminar foto de BD.";
        }
        $stmt_del->close();
    }
}

// Obtener datos actuales del administrador para mostrarlos en el formulario
// Hacemos un SELECT para traer nombre, email, nivel y fecha de registro
$sql_datos = "SELECT nombre, email, nivel, fecha_registro, foto_perfil FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql_datos);
$stmt->bind_param("i", $admin_id); // Vinculamos el ID
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc(); // Guardamos los datos en un array asociativo

// Variables para la vista
// Usamos htmlspecialchars() al mostrar datos para evitar ataques XSS (Cross-Site Scripting)
$admin_nombre = htmlspecialchars($admin['nombre']);
$admin_email = htmlspecialchars($admin['email']);
$admin_nivel = htmlspecialchars($admin['nivel']);
$fecha_registro = date("d/m/Y", strtotime($admin['fecha_registro'])); // Formateamos fecha
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Admin Lab-Explora</title>
    
    <!-- Fuentes de Google (Roboto, Poppins, Nunito) para tipografía moderna -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS Framework para diseño responsive y componentes -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons para iconos vectoriales -->
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <!-- Estilos personalizados -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <!-- LIBRERÍA EXTERNA: html2pdf.js -->
    <!-- Documentación: https://ekoopmans.github.io/html2pdf.js/ -->
    <!-- Esta librería permite convertir cualquier elemento HTML (como un div) en un archivo PDF descargable -->
    <!-- Funciona renderizando el HTML en un canvas usando html2canvas y luego poniéndolo en un PDF usando jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        /* Estilos específicos para la credencial digital */
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
<body class="admin-page">

    <!-- Header con botón hamburguesa -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <!-- Botón para abrir sidebar en móvil (d-md-none para ocultar en escritorio) -->
                    <button class="btn btn-outline-primary sidebar-toggle me-3 d-md-none" id="sidebar-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <a href="../../pagina-principal.php" class="logo d-flex align-items-end">
                        <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                        <h1 class="sitename">Lab-Explora</h1><span></span>
                    </a>
                </div>
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <span class="saludo d-none d-md-inline">👨‍💼 Hola, <?= $admin_nombre ?> (<?= $admin_nivel ?>)</span>
                        <a href="logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- Sidebar (Menú lateral) -->
                <div class="col-md-3 mb-4 sidebar-wrapper" id="sidebarWrapper">
                    <?php include 'sidebar-admin.php'; ?>
                </div>

                <!-- Contenido Principal -->
                <div class="col-md-9">
                    
                    <!-- Mensaje de feedback (éxito/error) -->
                    <?php if(!empty($mensaje)): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?= $mensaje ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <div class="section-title">
                        <h2>Mi Perfil</h2>
                        <p>Administra tus datos y descarga tu credencial oficial</p>
                    </div>

                    <div class="row">
                        <!-- Columna Izquierda: Formulario de Datos -->
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Información Personal</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="actualizar_datos" value="1">

                                        <div class="mb-3">
                                            <label for="nombre" class="form-label">Nombre Completo</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= $admin_nombre ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Correo Electrónico</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= $admin_email ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nivel" class="form-label">Nivel de Acceso</label>
                                            <input type="text" class="form-control" id="nivel" value="<?= ucfirst($admin_nivel) ?>" disabled>
                                            <div class="form-text">El nivel de acceso no se puede cambiar.</div>
                                        </div>
                                        <hr>
                                        <div class="mb-3">
                                            <label for="password_nueva" class="form-label">Nueva Contraseña (Opcional)</label>
                                            <input type="password" class="form-control" id="password_nueva" name="password_nueva" placeholder="Dejar en blanco para mantener la actual">
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-save me-2"></i>Guardar Cambios
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Foto de Perfil + 2FA -->
                        <div class="col-lg-6 mb-4">
                            <!-- TARJETA PARA SUBIR FOTO -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Foto de Perfil</h5>
                                </div>
                                <div class="card-body text-center">
                                    <form method="POST" action="" enctype="multipart/form-data" class="d-flex flex-column align-items-center gap-3">
                                        <input type="hidden" name="subir_foto" value="1">
                                        <!-- Mostrar foto actual pequeña -->
                                        <div class="position-relative">
                                            <img src="<?= !empty($admin['foto_perfil']) ? htmlspecialchars($admin['foto_perfil']) . '?v='.time() : '../../assets/img/defecto.png' ?>" 
                                                 alt="Vista previa" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #e9ecef;">
                                        </div>
                                        
                                        <div class="w-100">
                                            <label for="foto_perfil_input" class="form-label visually-hidden">Subir nueva foto</label>
                                            <input type="file" class="form-control" id="foto_perfil_input" name="foto_perfil" accept="image/*" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="bi bi-upload me-1"></i> Actualizar Foto
                                        </button>
                                    </form>

                                    <!-- Formulario separado para eliminar -->
                                    <?php if (!empty($admin['foto_perfil'])): ?>
                                        <form method="POST" action="" class="w-100 mt-2">
                                            <input type="hidden" name="eliminar_foto" value="1">
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('¿Estás seguro de eliminar tu foto de perfil?')">
                                                <i class="bi bi-trash me-1"></i> Eliminar Foto
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- SECCIÓN 2FA -->
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-shield-lock me-2"></i>
                                        Verificación en 2 Pasos
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        Protege tu cuenta con una capa extra de seguridad. Al iniciar sesión, 
                                        recibirás un código de 6 dígitos en tu correo electrónico.
                                    </p>
                                    
                                    <div id="2fa-status" class="mb-3">
                                        <!-- El estado se carga dinámicamente -->
                                    </div>
                                    
                                    <button id="toggle-2fa-btn" class="btn btn-primary w-100" onclick="togglear2FA()">
                                        <i class="bi bi-gear"></i> <span id="btn-text">Cargando...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CREDENCIAL DIGITAL - Fila completa abajo -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Credencial Digital</h5>
                                    <button class="btn btn-outline-danger btn-sm no-print" onclick="descargarCredencial()">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i>Descargar Oficial
                                    </button>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center bg-light" style="min-height: 400px;">
                                    
                                    <!-- AQUI EMPIEZA LA CREDENCIAL QUE SE VA A IMPRIMIR -->
                                    <div id="credencial-content" class="credential-card">
                                        
                                        <div class="credential-header">
                                            <!-- Logo y Título -->
                                            <div class="d-flex align-items-center justify-content-center mb-2">
                                                <!-- LOGO AGREGADO -->
                                                <img src="../../assets/img/logo/logobrayan2.ico" alt="Logo" style="width: 40px; margin-right: 10px; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3));">
                                                <h4 style="margin:0; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">Lab-Explora</h4>
                                            </div>
                                            <small style="letter-spacing: 2px; text-transform: uppercase; font-size: 0.75rem;">Acreditación Oficial</small>
                                        </div>

                                        <div class="credential-body d-flex align-items-center">
                                            <!-- Avatar Genérico o Usuario -->
                                            <div style="flex-shrink: 0;">
                                                <img src="<?= !empty($admin['foto_perfil']) ? htmlspecialchars($admin['foto_perfil']) . '?v='.time() : '../../assets/img/defecto.png' ?>" 
                                                     alt="Avatar" class="credential-avatar" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;">
                                            </div>
                                            
                                            <div class="credential-info ms-3 flex-grow-1">
                                                <h3 style="font-size: 1.1rem; margin-bottom: 2px;"><?= $admin_nombre ?></h3>
                                                <p style="font-size: 0.85rem; word-break: break-all; opacity: 0.9;"><?= $admin_email ?></p>
                                                <span class="badge bg-warning text-dark mt-1 shadow-sm" style="font-size: 0.75rem;">
                                                    <?= strtoupper($admin_nivel) ?>
                                                </span>
                                            </div>
                                            
                                            <!-- CODIGO QR -->
                                            <?php
                                                // Generamos la URL de verificacion
                                                // En produccion cambiar 'localhost/lab2' por el dominio real
                                                // Hash de seguridad
                                                $data_to_hash = $admin_id . $admin_nombre . $admin_email . "LAB_EXPLORA_ADMIN_SECURE_2024";
                                                $hash_seguro = strtoupper(substr(hash('sha256', $data_to_hash), 0, 24));
                                                
                                                // URL Publica (Ajustar segun entorno)
                                                // Usamos $_SERVER['HTTP_HOST'] para detectar host actual automaticamente
                                                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                                                $verification_url = "$protocol://" . $_SERVER['HTTP_HOST'] . "/lab2/verificar-credencial.php?id=$admin_id&tipo=admin&hash=$hash_seguro";
                                                
                                                // API QR Publica
                                                $qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=" . urlencode($verification_url);
                                            ?>
                                            <div style="flex-shrink: 0; margin-left: 10px;">
                                                <img src="<?= $qr_api ?>" alt="QR Verificacion" 
                                                     style="width: 80px; height: 80px; border-radius: 8px; border: 2px solid white; background: white; padding: 2px; display: block;">
                                            </div>
                                        </div>

                                        <div class="credential-footer">
                                            <p>Miembro desde: <?= $fecha_registro ?></p>
                                            <p>ID: #<?= str_pad($admin_id, 4, '0', STR_PAD_LEFT) ?></p>

                                            <div class="mt-2 mb-2 p-2" style="background: rgba(0,0,0,0.1); border-radius: 5px; font-size: 0.7rem; text-align: justify;">
                                                <strong>ROL: ADMINISTRADOR</strong><br>
                                                Esta credencial digital valida la identidad y permisos del usuario en Lab-Explora. La firma electrónica inferior es una cadena única e irrepetible generada criptográficamente, lo que garantiza su autenticidad y evita duplicaciones o falsificaciones de identidad.
                                            </div>

                                            <!-- FIRMA ELECTRÓNICA VISUAL -->
                                            <div class="signature-box">
                                                <div class="signature-label">Firma Digital Autenticada</div>
                                                <div class="signature-hash">
                                                    <?php 
                                                    // Generamos un hash visual basado en los datos del usuario + una "salt" secreta
                                                    $data_to_hash = $admin_id . $admin_nombre . $admin_email . "LAB_EXPLORA_ADMIN_SECURE_2024";
                                                    echo strtoupper(substr(hash('sha256', $data_to_hash), 0, 24)); // Mostramos los primeros 24 caracteres
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- SELLO OFICIAL -->
                                        <div class="credential-seal">
                                            <span>Official<br>Certified<br>Science</span>
                                        </div>
                                    </div>
                                    <!-- FIN DE LA CREDENCIAL -->

                                </div>
                                <div class="card-footer text-muted text-center" style="font-size: 0.8rem;">
                                    Tu identificación oficial como administrador de Lab-Explora.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Script para la generación del PDF -->
    <script>
        // Función asíncrona que maneja la descarga
        function descargarCredencial() {
            // 1. Seleccionamos el elemento del DOM que queremos convertir
            const elemento = document.getElementById('credencial-content');
            
            // 2. Configuramos las opciones para html2pdf
            const opciones = {
                margin:       [10, 10, 10, 10],
                filename:     'Credencial_LabExplorer_<?= $admin_nombre ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { 
                    scale: 2, 
                    useCORS: true, 
                    scrollY: 0,
                    logging: true
                },
                jsPDF:        { unit: 'mm', format: 'a5', orientation: 'portrait' } 
            };

            // 3. Ejecutamos la conversión
            // .from(elemento) -> toma el div
            // .set(opciones) -> aplica config
            // .save() -> genera y descarga el archivo
            html2pdf().set(opciones).from(elemento).save();
        }
    </script>
    
    <!-- Scripts de Bootstrap requeridos para el sidebar y navbar -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

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
            
            if (statusDiv && btn && btnText) {
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
