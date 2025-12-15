<?php



// Configuración de DB si no existe archivo separado
if (!isset($conn)) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "lab_exp_db";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);
    $conn->set_charset("utf8mb4");
}

$id = $_GET['id'] ?? '';
$tipo = $_GET['tipo'] ?? ''; // 'admin' o 'publicador'
$hash = $_GET['hash'] ?? '';

$valido = false;
$datos = [];
$mensaje = "Credencial no válida o inexistente.";

if ($id && $tipo && $hash) {
    // Validar hash para asegurar autenticidad
    // El "secreto" debe coincidir con el usado en perfil.php y perfil-admin.php
    $secreto = ($tipo === 'admin') ? "LAB_EXPLORA_ADMIN_SECURE_2024" : "LAB_EXPLORA_PUB_SECURE_KEY";
    
    // Primero obtenemos los datos para reconstruir el hash
    $tabla = ($tipo === 'admin') ? 'admins' : 'publicadores';
    
    $stmt = $conn->prepare("SELECT id, nombre, email, foto_perfil, fecha_registro, estado FROM $tabla WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        // Reconstruir hash
        $data_to_hash = $row['id'] . $row['nombre'] . $row['email'] . $secreto;
        $hash_calculado = strtoupper(substr(hash('sha256', $data_to_hash), 0, 24));
        
        if ($hash === $hash_calculado) {
            $valido = true;
            $datos = $row;
            if ($row['estado'] !== 'activo') {
                $mensaje = "Esta credencial pertenece a un usuario inactivo o suspendido.";
                $valido = false; // Invalidar visualmente aunque sea auténtica
            } else {
                $mensaje = "Credencial Verificada Oficialmente";
            }
        } else {
            $mensaje = "Error de integridad: La firma digital no coincide.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Credencial - Lab-Explora</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; }
        .verification-card {
            max-width: 500px;
            margin: 50px auto;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            background: white;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .header-bg {
            background: <?= $valido ? 'linear-gradient(135deg, #198754 0%, #20c997 100%)' : 'linear-gradient(135deg, #dc3545 0%, #fd7e14 100%)' ?>;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .status-icon {
            font-size: 4rem;
            margin-bottom: 10px;
            animation: bounceIn 0.8s;
        }
        .user-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            margin-top: -60px;
            background: #fff;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .data-row {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .data-label { color: #6c757d; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .data-value { font-weight: 600; font-size: 1.1rem; color: #212529; }
    </style>
</head>
<body>
    
    <!-- HEADER ESTANDAR -->
    <header id="header" class="header position-relative sticky-top" style="background: white; box-shadow: 0px 2px 20px rgba(0, 0, 0, 0.1);">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <a href="index.php" class="logo d-flex align-items-end">
                        <img src="assets/img/logo/logo-labexplora.png" alt="logo-lab">
                        <h1 class="sitename">Lab-Explora</h1><span></span>
                    </a>
                </div>
                <div class="d-flex align-items-center">
                    <a href="index.php" class="btn-publicador">
                        <i class="bi bi-house-door"></i> Ir al Inicio
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="min-height: 80vh;">
        <div class="verification-card">
            <div class="header-bg">
                <div class="status-icon">
                    <i class="bi <?= $valido ? 'bi-patch-check-fill' : 'bi-x-octagon-fill' ?>"></i>
                </div>
                <h3><?= $mensaje ?></h3>
                <?php if ($valido): ?>
                    <p class="mb-0 text-white-50">Documento Oficial Validado</p>
                <?php endif; ?>
            </div>
            
            <div class="card-body text-center p-4">
                <?php if ($valido && !empty($datos)): ?>
                    <img src="<?= !empty($datos['foto_perfil']) ? str_replace('../../', '', $datos['foto_perfil']) : 'assets/img/defecto.png' ?>" class="user-photo mb-3">
                    
                    <h2 class="mb-1"><?= htmlspecialchars($datos['nombre']) ?></h2>
                    <span class="badge bg-primary mb-4"><?= strtoupper($tipo) ?></span>
                    
                    <div class="text-start mt-4">
                        <div class="data-row">
                            <div class="data-label">Email</div>
                            <div class="data-value"><?= htmlspecialchars($datos['email']) ?></div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Miembro Desde</div>
                            <div class="data-value"><?= date('d/m/Y', strtotime($datos['fecha_registro'])) ?></div>
                        </div>
                        <div class="data-row">
                            <div class="data-label">Estado Actual</div>
                            <div class="data-value text-success">
                                <i class="bi bi-check-circle-fill me-1"></i> ACTIVO
                            </div>
                        </div>
                        <div class="data-row border-0">
                            <div class="data-label">ID de Sistema</div>
                            <div class="data-value">#<?= str_pad($datos['id'], 5, '0', STR_PAD_LEFT) ?></div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="py-5 text-muted">
                        <p>No se pudo verificar la información proporcionada.</p>
                        <p class="small">Si cree que esto es un error, contacte a soporte.</p>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4 pt-3 border-top">
                    <a href="index.php" class="btn btn-outline-dark btn-sm">Ir a Lab-Explora</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/accessibility-widget.js?v=3.2"></script>
</body>
</html>
