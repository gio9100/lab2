<?php
// forms/admins/admin_anuncios.php
session_start();
require_once "config-admin.php";
require_once "../funciones_auditoria.php"; // Para registrar quien crea el anuncio
requerirAdmin();

$mensaje = "";
$tipo_alerta = "";

// PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_anuncio'])) {
        $texto = trim($_POST['mensaje']);
        $tipo = $_POST['tipo'];
        $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : NULL;
        $admin_id = $_SESSION['admin_id'];

        if (!empty($texto)) {
            // Desactivar otros si se pide (opcional, por ahora permitimos múltiples o solo logic de display mostrará 1)
            // Vamos a desactivar todos los anteriores para que solo haya 1 activo por defecto
            $conn->query("UPDATE anuncios_sistema SET activo = 0");

            $stmt = $conn->prepare("INSERT INTO anuncios_sistema (mensaje, tipo, fecha_fin, creado_por, activo) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("sssi", $texto, $tipo, $fecha_fin, $admin_id);
            
            if ($stmt->execute()) {
                registrarLogAuditoria($conn, $admin_id, 'CREAR_ANUNCIO', 'anuncio', $conn->insert_id, "Tipo: $tipo");
                $_SESSION['alerta_anuncio'] = ["mensaje" => "Anuncio publicado correctamente", "tipo" => "success"];
                header("Location: admin_anuncios.php");
                exit;
            } else {
                $mensaje = "Error: " . $conn->error;
                $tipo_alerta = "danger";
            }
        }
    }

    if (isset($_POST['toggle_estado'])) {
        $id = intval($_POST['anuncio_id']);
        $nuevo_estado = intval($_POST['nuevo_estado']);
        
        $stmt = $conn->prepare("UPDATE anuncios_sistema SET activo = ? WHERE id = ?");
        $stmt->bind_param("ii", $nuevo_estado, $id);
        if ($stmt->execute()) {
            registrarLogAuditoria($conn, $_SESSION['admin_id'], 'CAMBIAR_ESTADO_ANUNCIO', 'anuncio', $id, "Nuevo estado: $nuevo_estado");
            $_SESSION['alerta_anuncio'] = ["mensaje" => "Estado actualizado", "tipo" => "success"];
            header("Location: admin_anuncios.php");
            exit;
        }
    }

    if (isset($_POST['eliminar_anuncio'])) {
        $id = intval($_POST['anuncio_id']);
        $conn->query("DELETE FROM anuncios_sistema WHERE id = $id");
        registrarLogAuditoria($conn, $_SESSION['admin_id'], 'ELIMINAR_ANUNCIO', 'anuncio', $id);
        $_SESSION['alerta_anuncio'] = ["mensaje" => "Anuncio eliminado", "tipo" => "success"];
        header("Location: admin_anuncios.php");
        exit;
    }
}

// Recuperar alerta de sesión si existe
if (isset($_SESSION['alerta_anuncio'])) {
    $mensaje = $_SESSION['alerta_anuncio']['mensaje'];
    $tipo_alerta = $_SESSION['alerta_anuncio']['tipo'];
    unset($_SESSION['alerta_anuncio']);
}

// OBTENER ANUNCIOS
$anuncios = $conn->query("SELECT a.*, admin.nombre as autor 
                          FROM anuncios_sistema a 
                          LEFT JOIN admins admin ON a.creado_por = admin.id 
                          ORDER BY a.creado_en DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anuncios del Sistema - Admin</title>
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css?v=2.0">
</head>
<body class="admin-page">
    
    <!-- Header Inline -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary sidebar-toggle me-3 d-md-none" id="sidebar-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <a href="../../pagina-principal.php" class="logo d-flex align-items-end">
                        <img src="../../assets/img/logo/logo-labexplora.png" alt="logo-lab">
                        <h1 class="sitename">Lab-Explora</h1><span></span>
                    </a>
                </div>
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <a href="perfil-admin.php" class="saludo d-none d-md-inline text-decoration-none text-dark me-3">👨‍💼 Hola, Admin</a>
                        <a href="logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3 mb-4 sidebar-wrapper" id="sidebarWrapper">
                <?php include 'sidebar-admin.php'; ?>
            </div>

            <div class="col-md-9">
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h4 class="card-title mb-0"><i class="bi bi-megaphone-fill text-primary me-2"></i>Publicar Nuevo Anuncio</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="p-2">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Mensaje del Anuncio</label>
                                    <input type="text" name="mensaje" class="form-control form-control-lg" placeholder="Ej: Mantenimiento programado el sábado a las 10:00 PM" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo</label>
                                    <select name="tipo" class="form-select">
                                        <option value="info">Información (Azul)</option>
                                        <option value="warning">Advertencia (Amarillo)</option>
                                        <option value="danger">Urgente (Rojo)</option>
                                        <option value="success">Éxito (Verde)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fecha Fin (Opcional)</label>
                                    <input type="datetime-local" name="fecha_fin" class="form-control">
                                    <div class="form-text">Si se deja vacío, debe desactivarse manualmente.</div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" name="crear_anuncio" class="btn btn-primary w-100">
                                        <i class="bi bi-send me-2"></i>Publicar Globalmente
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Historial de Anuncios</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Estado</th>
                                        <th>Mensaje</th>
                                        <th>Tipo</th>
                                        <th>Autor</th>
                                        <th>Creado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $anuncios->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <form method="POST">
                                                <input type="hidden" name="anuncio_id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="nuevo_estado" value="<?= $row['activo'] ? 0 : 1 ?>">
                                                <button type="submit" name="toggle_estado" class="btn btn-sm btn-<?= $row['activo'] ? 'success' : 'secondary' ?>">
                                                    <?= $row['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?= htmlspecialchars($row['mensaje']) ?></td>
                                        <td><span class="badge bg-<?= $row['tipo'] ?> text-uppercase"><?= $row['tipo'] ?></span></td>
                                        <td><?= htmlspecialchars($row['autor']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['creado_en'])) ?></td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('¿Eliminar este anuncio?');">
                                                <input type="hidden" name="anuncio_id" value="<?= $row['id'] ?>">
                                                <button type="submit" name="eliminar_anuncio" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script>
        // AOS.init(); // Si se usara AOS
    </script>
</body>
</html>
