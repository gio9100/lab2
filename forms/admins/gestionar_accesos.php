<?php
// Iniciamos la sesión para verificar permisos
session_start();

// Incluimos la configuración del admin (conexión a BD y funciones)
require_once "config-admin.php";

// Verificamos que sea administrador antes de continuar
// Si no está logueado, lo manda al login
requerirAdmin();

// Inicializamos variables para mensajes
$mensaje = "";
$tipo_mensaje = "";

// Lógica para procesar el formulario de agregar
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    
    // Limpiamos los datos recibidos
    $nuevo_valor = trim($_POST['valor'] ?? '');
    // Obtiene el valor (email o dominio)
    $tipo_acceso = $_POST['tipo'] ?? 'ambos';
    // Obtiene si es para usuario, publicador o ambos

    if (empty($nuevo_valor)) {
        // Si envió el campo vacío
        $mensaje = "El campo no puede estar vacío";
        $tipo_mensaje = "error";
    } else {
        // Preparamos la inserción en BD
        $sql = "INSERT INTO correos_permitidos (valor, tipo_acceso) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            // Enlazamos: s = string, s = string
            $stmt->bind_param("ss", $nuevo_valor, $tipo_acceso);
            
            if ($stmt->execute()) {
                // Si guardó correctamente
                $mensaje = "Acceso agregado correctamente";
                $tipo_mensaje = "exito";
            } else {
                // Si falló (probablemente duplicado o error SQL)
                $mensaje = "Error al agregar: " . $conn->error;
                $tipo_mensaje = "error";
            }
            $stmt->close();
            // Cerramos sentencia
        }
    }
}

// Lógica para eliminar un registro
if (isset($_GET['eliminar'])) {
    // Obtenemos el ID a eliminar y lo convertimos a entero por seguridad
    $id_eliminar = intval($_GET['eliminar']);
    
    $sql_del = "DELETE FROM correos_permitidos WHERE id = ?";
    $stmt_del = $conn->prepare($sql_del);
    
    if ($stmt_del) {
        $stmt_del->bind_param("i", $id_eliminar);
        
        if ($stmt_del->execute()) {
            $mensaje = "Registro eliminado";
            $tipo_mensaje = "exito";
        }
        $stmt_del->close();
    }
}

// Obtener la lista actual de correos/dominios permitidos
// Ordenamos por fecha de registro (más nuevos primero)
$query_lista = "SELECT * FROM correos_permitidos ORDER BY fecha_registro DESC";
$resultado_lista = $conn->query($query_lista);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Accesos - Admin</title>
    
    <!-- Fuentes (Igual que index-admin.php) -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- Main CSS -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <style>
        /* Ajustes específicos para las tablas de esta vista */
        .icon-box { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #f0f4ff; color: #4154f1; margin-right: 15px; }
        .card-title-custom { color: #012970; font-family: "Poppins", sans-serif; font-weight: 500; }
    </style>
</head>
<body class="admin-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                
                <div class="d-flex align-items-center">
                    <!-- Toggle solo visible en móvil -->
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
                        <a href="perfil-admin.php" class="saludo d-none d-md-inline text-decoration-none text-dark me-3">
                            👨‍💼 Hola, <?= htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin') ?>
                        </a>
                        <a href="logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- Sidebar (Columna Izquierda idéntica) -->
                <div class="col-md-3 mb-4 sidebar-wrapper" id="sidebarWrapper">
                    <?php include 'sidebar-admin.php'; ?>
                </div>

                <!-- Contenido Derecho (Columna Derecha) -->
                <div class="col-md-9">
                    
                    <!-- Mensajes de Alerta -->
                    <?php if (isset($mensaje) && $mensaje): ?>
                    <div class="alert-message <?= $tipo_mensaje == 'exito' ? 'success' : 'error' ?> mb-4" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Gestión de Correos Institucionales</h2>
                        <p>Administra los dominios y correos permitidos para el registro</p>
                    </div>

                    <div class="row" data-aos="fade-up" data-aos-delay="100">
                        
                        <!-- Columna: Formulario Agregar -->
                        <div class="col-lg-5 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <h5 class="card-title-custom mb-3 d-flex align-items-center">
                                        <div class="icon-box"><i class="bi bi-plus-lg"></i></div>
                                        Agregar Nuevo Acceso
                                    </h5>
                                    
                                    <form method="POST" class="mt-4">
                                        <input type="hidden" name="accion" value="agregar">
                                        
                                        <div class="mb-4">
                                            <label class="form-label text-muted fw-bold small">CORREO O DOMINIO</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                                                <input type="text" name="valor" class="form-control border-start-0 ps-0 bg-light" 
                                                       placeholder="ej: @universidad.edu" required>
                                            </div>
                                            <div class="form-text text-muted small mt-2">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Usa <strong>@dominio.com</strong> para permitir a toda una organización, o un correo específico.
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label text-muted fw-bold small">APLICA PARA</label>
                                            <select name="tipo" class="form-select bg-light">
                                                <option value="ambos" selected>Todo el personal (Usuarios y Publicadores)</option>
                                                <option value="usuario">Solo Usuarios</option>
                                                <option value="publicador">Solo Publicadores</option>
                                            </select>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                            <i class="bi bi-save me-2"></i> Guardar Configuración
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Columna: Tabla Listado -->
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <h5 class="card-title-custom mb-4 d-flex align-items-center">
                                        <div class="icon-box"><i class="bi bi-list-check"></i></div>
                                        Accesos Configurados
                                    </h5>

                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="border-0">Valor</th>
                                                    <th class="border-0 text-center">Tipo</th>
                                                    <th class="border-0 text-end">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($resultado_lista && $resultado_lista->num_rows > 0): ?>
                                                    <?php while($row = $resultado_lista->fetch_assoc()): ?>
                                                        <tr>
                                                            <td class="fw-bold text-primary py-3">
                                                                <?= htmlspecialchars($row['valor']) ?>
                                                                <div class="small text-muted fw-normal">
                                                                    <?= date('d/m/Y', strtotime($row['fecha_registro'])) ?>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if($row['tipo_acceso'] == 'usuario'): ?>
                                                                    <span class="badge bg-light text-dark border">Usuario</span>
                                                                <?php elseif($row['tipo_acceso'] == 'publicador'): ?>
                                                                    <span class="badge bg-warning text-dark">Publicador</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success bg-opacity-10 text-success">Global</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-end">
                                                                <a href="?eliminar=<?= $row['id'] ?>" 
                                                                   class="btn btn-outline-danger btn-sm rounded-circle shadow-sm"
                                                                   style="width: 32px; height: 32px; padding: 0; line-height: 30px;"
                                                                   onclick="return confirm('¿Seguro que deseas eliminar este acceso?')"
                                                                   title="Eliminar">
                                                                    <i class="bi bi-trash"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center py-5">
                                                            <img src="../../assets/img/iconos/empty.svg" alt="" style="width: 48px; opacity: 0.3; margin-bottom: 20px;">
                                                            <p class="text-muted fw-bold">Sin configuraciones</p>
                                                            <p class="text-muted small">Agrega dominios o correos a la izquierda.</p>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
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

    <!-- Vendor JS Files -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <script>
        AOS.init();
        
        // Script para el sidebar móvil (Copiado de sidebar-admin.php logic pero inline por si acaso)
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebarWrapper');
            if(toggle && sidebar) {
                toggle.addEventListener('click', function(e) {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
