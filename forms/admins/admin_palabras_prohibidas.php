<?php
// Iniciar sesión y reporte de errores
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config-admin.php';
requerirAdmin();

// Procesar formulario
$mensaje = "";
$tipo_alerta = "";

// Agregar palabra
if (isset($_POST['agregar_palabra'])) {
    $palabra = trim($_POST['palabra']);
    $accion = $_POST['accion'];
    $tipo = $_POST['tipo_coincidencia'];

    if (!empty($palabra)) {
        $stmt = $conn->prepare("INSERT INTO lista_negra (palabra, accion, tipo_coincidencia) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $palabra, $accion, $tipo);
        
        if ($stmt->execute()) {
            $mensaje = "Palabra agregada correctamente";
            $tipo_alerta = "success";
        } else {
            $mensaje = "Error: " . $conn->error;
            $tipo_alerta = "danger";
        }
    }
}

// Eliminar palabra
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM lista_negra WHERE id = $id");
    header("Location: admin_palabras_prohibidas.php");
    exit;
}

// Obtener lista
$palabras = $conn->query("SELECT * FROM lista_negra ORDER BY creado_en DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderación de Palabras - Admin</title>
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.cssív=2.0">
</head>
<body class="admin-page">
    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
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
                        <a href="perfil-admin.php" class="saludo d-none d-md-inline text-decoration-none text-dark me-3">👨‍💼 Hola, Admin</a>
                        <a href="logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3 mb-4 sidebar-wrapper">
                <?php include 'sidebar-admin.php'; ?>
            </div>

            <div class="col-md-9">
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h4 class="card-title mb-0"><i class="bi bi-shield-slash-fill text-danger me-2"></i>Lista Negra de Palabras</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-4 p-4 bg-light rounded">
                            <h5 class="mb-3">Agregar Nueva Palabra</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" name="palabra" class="form-control" placeholder="Palabra prohibida" required>
                                </div>
                                <div class="col-md-3">
                                    <select name="accion" class="form-select">
                                        <option value="asteriscos">Censurar (****)</option>
                                        <option value="rechazar">Rechazar Publicación</option>
                                        <option value="revision">Marcar para Revisión</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="tipo_coincidencia" class="form-select">
                                        <option value="parcial">Coincidencia Parcial</option>
                                        <option value="exacta">Coincidencia Exacta</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="agregar_palabra" class="btn btn-primary w-100">Agregar</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Palabra</th>
                                        <th>Acción</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $palabras->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold text-danger"><?php echo htmlspecialchars($row['palabra']); ?></td>
                                        <td>
                                            <?php 
                                            $badges = [
                                                'asteriscos' => 'bg-warning text-dark',
                                                'rechazar' => 'bg-danger',
                                                'revision' => 'bg-info'
                                            ];
                                            echo "<span class='badge " . $badges[$row['accion']] . "'>" . ucfirst($row['accion']) . "</span>";
                                            ?>
                                        </td>
                                        <td><?php echo ucfirst($row['tipo_coincidencia']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['creado_en'])); ?></td>
                                        <td>
                                            <a href="?eliminar=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta palabra?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
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

    <!-- Scripts -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>

