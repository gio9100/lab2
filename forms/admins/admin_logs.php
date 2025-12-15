<?php
// forms/admins/admin_logs.php
session_start();
require_once "config-admin.php";
requerirAdmin();

// Filtrado simple
$filtro_accion = $_GET['accion'] ?? '';
$where = "1=1";
if (!empty($filtro_accion)) {
    $accion_safe = $conn->real_escape_string($filtro_accion);
    $where .= " AND logs.accion = '$accion_safe'";
}

// Consulta con JOIN para obtener nombre del admin
$sql = "SELECT logs.*, admins.nombre as admin_nombre 
        FROM logs_auditoria logs 
        LEFT JOIN admins ON logs.admin_id = admins.id 
        WHERE $where 
        ORDER BY logs.fecha DESC 
        LIMIT 100";
$logs = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría - Lab-Explora</title>
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css?v=2.0">
</head>
<body class="admin-page">
    
    <!-- Inline header for consistency -->
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
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Logs de Auditoría</h4>
                        <div>
                            <select class="form-select form-select-sm" onchange="location = this.value;">
                                <option value="admin_logs.php">Filtrar por Acción...</option>
                                <option value="admin_logs.php?accion=APROBAR" <?= $filtro_accion == 'APROBAR' ? 'selected' : '' ?>>Aprobaciones</option>
                                <option value="admin_logs.php?accion=RECHAZAR" <?= $filtro_accion == 'RECHAZAR' ? 'selected' : '' ?>>Rechazos</option>
                                <option value="admin_logs.php?accion=SUSPENDER" <?= $filtro_accion == 'SUSPENDER' ? 'selected' : '' ?>>Suspensiones</option>
                                <option value="admin_logs.php?accion=LOGIN" <?= $filtro_accion == 'LOGIN' ? 'selected' : '' ?>>Inicios de Sesión</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Admin</th>
                                        <th>Acción</th>
                                        <th>Objeto</th>
                                        <th>Detalles</th>
                                        <th>IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($logs && $logs->num_rows > 0): ?>
                                    <?php while ($log = $logs->fetch_assoc()): ?>
                                    <tr>
                                        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($log['fecha'])) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($log['admin_nombre'] ?? 'Desconocido') ?></td>
                                        <td>
                                            <?php
                                            $badge_class = 'bg-secondary';
                                            if (stripos($log['accion'], 'APROBAR') !== false) $badge_class = 'bg-success';
                                            if (stripos($log['accion'], 'RECHAZAR') !== false) $badge_class = 'bg-danger';
                                            if (stripos($log['accion'], 'SUSPENDER') !== false) $badge_class = 'bg-warning text-dark';
                                            if (stripos($log['accion'], 'ACTIVAR') !== false) $badge_class = 'bg-info text-dark';
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($log['accion']) ?></span>
                                        </td>
                                        <td>
                                            <?php if($log['tipo_objeto']): ?>
                                            <small class="d-block text-muted"><?= htmlspecialchars($log['tipo_objeto']) ?></small>
                                            <small>ID: <?= $log['objeto_id'] ?></small>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-break" style="max-width: 200px;">
                                            <?php 
                                            // Fix visual para acentos rotos en logs antiguos
                                            $detalles = str_replace('Aprobaci?n', 'Aprobación', $log['detalles']); 
                                            echo htmlspecialchars($detalles);
                                            ?>
                                        </td>
                                        <td class="small font-monospace"><?= htmlspecialchars($log['ip_origen']) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-4">No hay registros de actividad recientes.</td></tr>
                                    <?php endif; ?>
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
        // AOS.init();
    </script>
</body>
</html>
