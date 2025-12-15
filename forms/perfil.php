<?php
session_start();
require_once 'conexion.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: inicio-sesion.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_logueado = true;

// Obtener datos del usuario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

// Función para obtener publicaciones guardadas
function obtenerLeerMasTarde($usuario_id, $conexion) {
    $stmt = $conexion->prepare("
        SELECT p.*, pub.nombre as publicador_nombre, lmt.fecha_agregado
        FROM leer_mas_tarde lmt
        JOIN publicaciones p ON lmt.publicacion_id = p.id
        LEFT JOIN publicadores pub ON p.publicador_id = pub.id
        WHERE lmt.usuario_id = ?
        ORDER BY lmt.fecha_agregado DESC
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $guardados = [];
    while ($row = $result->fetch_assoc()) {
        $guardados[] = $row;
    }
    $stmt->close();
    return $guardados;
}

// Procesar subida de foto
$mensaje_foto = "";
$exito_foto = false;

// Procesar eliminación de foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_foto'])) {
    // Obtener la foto actual del usuario
    $stmt = $conexion->prepare("SELECT imagen FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datos_usuario = $resultado->fetch_assoc();
    $stmt->close();
    
    // Eliminar archivo físico si existe
    if (!empty($datos_usuario['imagen']) && file_exists('../' . $datos_usuario['imagen'])) {
        @unlink('../' . $datos_usuario['imagen']);
    }
    
    // Actualizar BD para poner NULL en la columna imagen
    $stmt = $conexion->prepare("UPDATE usuarios SET imagen = NULL WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        // Recargar datos del usuario
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        $mensaje_foto = "✅ Foto eliminada correctamente";
        $exito_foto = true;
    } else {
        $mensaje_foto = "❌ Error al eliminar la foto de la base de datos";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $target_dir = "../assets/img/uploads/";
    
    // Validar que el archivo fue subido sin errores
    if ($_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $imageFileType = strtolower(pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION));
        $new_filename = "usuario_" . $usuario_id . "_" . time() . "." . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        // Validar que sea una imagen real y tamaño
        $check = getimagesize($_FILES["foto_perfil"]["tmp_name"]);
        if($check !== false && $_FILES["foto_perfil"]["size"] <= 2000000) {
            // Validar extensiones permitidas
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($imageFileType, $extensiones_permitidas)) {
                if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file)) {
                    // Actualizar base de datos con la columna correcta
                    $stmt = $conexion->prepare("UPDATE usuarios SET imagen = ? WHERE id = ?");
                    $foto_path = "assets/img/uploads/" . $new_filename;
                    $stmt->bind_param("si", $foto_path, $usuario_id);
                    
                    if ($stmt->execute()) {
                        $stmt->close();
                        // Recargar datos del usuario
                        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
                        $stmt->bind_param("i", $usuario_id);
                        $stmt->execute();
                        $resultado = $stmt->get_result();
                        $usuario = $resultado->fetch_assoc();
                        $stmt->close();
                        
                        $mensaje_foto = "✅ Foto actualizada correctamente";
                        $exito_foto = true;
                    } else {
                        $mensaje_foto = "❌ Error al actualizar la base de datos";
                    }
                } else {
                    $mensaje_foto = "❌ Error al mover el archivo subido";
                }
            } else {
                $mensaje_foto = "❌ Formato no permitido. Usa JPG, PNG, GIF o WEBP";
            }
        } else {
            $mensaje_foto = "❌ El archivo debe ser una imagen válida y menor a 2MB";
        }
    } else {
        $mensaje_foto = "❌ Error al subir el archivo: " . $_FILES['foto_perfil']['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Lab-Explora</title>
    
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    
    <!-- Librería PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        body { background: #f8f9fa; }
        .profile-container { max-width: 1200px; margin: 50px auto; padding: 20px; }
        .profile-card { background: white; border-radius: 15px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); padding: 30px; margin-bottom: 20px; }
        .profile-header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #eee; }
        .profile-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; }
        
        /* Credencial Digital */
        .credential-card {
            background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 400px;
            margin: 20px auto;
            position: relative;
        }
        .credential-header { text-align: center; border-bottom: 1px solid rgba(255,255,255,0.3); padding-bottom: 10px; margin-bottom: 15px; }
        .credential-body { display: flex; align-items: center; gap: 15px; }
        .credential-avatar { width: 70px; height: 70px; background: white; border-radius: 50%; overflow: hidden; }
        .credential-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .credential-info h4 { font-size: 1.2rem; margin: 0; font-weight: 700; }
        .credential-footer { font-size: 0.8rem; text-align: center; margin-top: 15px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.2); }
        .signature-box { margin-top: 15px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.3); text-align: center; }
        .signature-hash { font-family: 'Courier New', monospace; font-size: 0.65rem; background: rgba(0,0,0,0.2); padding: 2px 4px; border-radius: 4px; }
    </style>
    
    <!-- Librería para PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list sidebar-toggle me-3" id="sidebar-toggle"></i>
                    <a href="../pagina-principal.php" class="logo d-flex align-items-end">
                        <img src="../assets/img/logo/logo-labexplora.png" alt="logo-lab">
                        <h1 class="sitename">Lab-Explora</h1><span></span>
                    </a>
                </div>

                <div class="d-flex align-items-center">
                    <div class="social-links d-none d-lg-block">
                        <a href="https://www.facebook.com/laboratorioabcdejacona?locale=es_LA" target="_blank"><i class="bi bi-facebook"></i></a>
                        <a href="#" target="_blank"><i class="bi bi-twitter"></i></a>
                        <a href="https://www.instagram.com/lab_explorer_cbtis_52/" target="_blank"><i class="bi bi-instagram"></i></a>
                        
                        <span style="color: var(--border); margin: 0 5px;">|</span>
                        
                        <a href="../terminos.php">Términos</a>
                        <a href="../privacidad.php">Privacidad</a>
                        
                        <a href="../index.php">Explorar</a>
                        <a href="logout.php" class="btn-publicador">
                            <i class="bi bi-box-arrow-right"></i>
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="profile-container">
        <!-- Perfil Header -->
        <div class="profile-card">
            <div class="profile-header">
                <img src="<?= !empty($usuario['imagen']) ? '../' . htmlspecialchars($usuario['imagen']) : '../assets/img/defecto.png' ?>" 
                     alt="Foto de perfil" class="profile-avatar">
                <h2><?= htmlspecialchars($usuario['nombre']) ?></h2>
                <p class="text-muted"><?= htmlspecialchars($usuario['correo']) ?></p>
                <span class="badge bg-info">Usuario</span>
            </div>
        </div>

        <div class="row">
            <!-- Información Personal + Foto -->
            <div class="col-lg-6 mb-4">
                <div class="profile-card">
                    <h5 class="mb-3"><i class="bi bi-person-circle me-2"></i>Información Personal</h5>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
                    <p><strong>ID:</strong> #<?= $usuario['id'] ?></p>
                </div>

                <!-- Subir Foto -->
                <div class="profile-card">
                    <h5 class="mb-3"><i class="bi bi-camera me-2"></i>Actualizar Foto de Perfil</h5>
                    
                    <?php if($mensaje_foto): ?>
                    <div class="alert alert-<?= $exito_foto ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($mensaje_foto) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" name="foto_perfil" class="form-control" accept="image/*" required>
                            <small class="text-muted">JPG, PNG, GIF, WEBP. Máximo 2MB</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-2"></i>Subir Foto
                        </button>
                    </form>
                    
                    <?php if (!empty($usuario['imagen'])): ?>
                        <form method="POST" class="mt-2">
                            <input type="hidden" name="eliminar_foto" value="1">
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('¿Estás seguro de eliminar tu foto de perfil?')">
                                <i class="bi bi-trash me-2"></i>Eliminar Foto
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Credencial Digital -->
            <div class="col-lg-6 mb-4">
                <div class="profile-card">
                    <h5 class="text-center mb-3">Credencial Digital</h5>
                    <div id="credencial-content" class="credential-card">
                        <div class="credential-header text-center">
                            <img src="../assets/img/logo/logo-labexplora.png" class="mb-2" style="width: 40px; filter: invert(1);">
                            <br>
                            <strong>Lab-Explora</strong><br>
                            <small style="letter-spacing: 1px;">MIEMBRO OFICIAL</small>
                        </div>
                        <div class="credential-body">
                            <div class="credential-avatar">
                                <?php if (!empty($usuario['imagen'])): ?>
                                    <img src="../<?= htmlspecialchars($usuario['imagen']) ?>">
                                <?php else: ?>
                                    <i class="bi bi-person-fill" style="font-size: 2rem; color: #0dcaf0; padding-top: 15px;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="credential-info">
                                <h4><?= htmlspecialchars($usuario['nombre']) ?></h4>
                                <p><?= htmlspecialchars($usuario['correo']) ?></p>
                                <span class="badge bg-light text-dark">Estudiante / Lector</span>
                            </div>
                        </div>
                        <div class="credential-footer">
                            ID: #<?= str_pad($usuario['id'], 4, '0', STR_PAD_LEFT) ?><br>
                            Válido: <?= date('Y') ?> - <?= date('Y') + 1 ?>
                            
                            <div class="mt-2 mb-2 p-2" style="background: rgba(0,0,0,0.1); border-radius: 5px; font-size: 0.7rem; text-align: justify;">
                                <strong>ROL: LECTOR / ESTUDIANTE</strong><br>
                                Esta credencial digital valida la identidad del usuario como lector autorizado en Lab-Explora. Permite el acceso a contenido científico, la interacción con publicaciones mediante comentarios y likes, y la creación de listas de lectura personalizadas. La firma electrónica inferior es una cadena única e irrepetible generada criptográficamente, lo que garantiza su autenticidad y evita duplicaciones o falsificaciones de identidad.
                            </div>
                            
                            <div class="signature-box">
                                <div style="font-size: 0.7rem; opacity: 0.8;">Firma Digital</div>
                                <div class="signature-hash">
                                    <?= strtoupper(substr(hash('sha256', $usuario['id'] . $usuario['nombre'] . $usuario['correo'] . "LAB_EXPLORA_2024"), 0, 24)) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-outline-info w-100 mt-3" onclick="descargarCredencialConQR()">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Descargar PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Publicaciones Guardadas -->
        <div class="profile-card">
            <h5 class="mb-4"><i class="bi bi-bookmark-heart me-2"></i>Guardado para Leer Más Tarde</h5>
            <?php 
            $guardados = obtenerLeerMasTarde($usuario['id'], $conexion);
            if (empty($guardados)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bookmark" style="font-size: 3rem;"></i>
                    <p class="mt-3">No has guardado ninguna publicación aún</p>
                    <a href="../index.php" class="btn btn-primary">Explorar Publicaciones</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($guardados as $pub): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <span class="badge bg-secondary mb-2"><?= htmlspecialchars($pub['tipo'] ?? 'Artículo') ?></span>
                                <h6><a href="../ver-publicacion.php?id=<?= $pub['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($pub['titulo']) ?>
                                </a></h6>
                                <small class="text-muted">Por <?= htmlspecialchars($pub['publicador_nombre']) ?></small>
                                <hr>
                                <button onclick="eliminarGuardado(<?= $pub['id'] ?>)" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include "sidebar-usuario.php"; ?>

    <script>
    // Mobile sidebar toggle logic is handled in sidebar-usuario.php
    
    // Función simple para descargar PDF (copiada de perfil-admin que funciona)
    function descargarCredencialConQR() {
        const elemento = document.getElementById('credencial-content');
        const opciones = {
            margin: [10, 10, 10, 10],
            filename: 'Credencial_Usuario_<?= preg_replace("/[^a-zA-Z0-9]/", "_", $usuario['nombre']) ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2, 
                useCORS: true, 
                scrollY: 0,
                logging: true 
            },
            jsPDF: { unit: 'mm', format: 'a5', orientation: 'portrait' }
        };
        html2pdf().set(opciones).from(elemento).save();
    }


    function eliminarGuardado(publicacionId) {
        if (!confirm('¿Eliminar esta publicación de tus guardados?')) return;
        
        fetch('procesar-interacciones.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `accion=guardar_leer_mas_tarde&publicacion_id=${publicacionId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error: ' + data.message);
        })
        .catch(() => alert('Error al eliminar'));
    }
    </script>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>