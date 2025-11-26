<?php
session_start();
require_once 'config-publicadores.php';

/* -------------------------------
   VALIDACIÓN DE SESIÓN PUBLICADOR
-------------------------------- */
if (!isset($_SESSION['publicador_id'])) {
    header("Location: login.php");
    exit();
}

$publicador_id = $_SESSION['publicador_id'];
$publicador_nombre = $_SESSION['publicador_nombre'] ?? 'Publicador';


/* -------------------------------
   ELIMINAR PUBLICACIÓN
-------------------------------- */
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);

    // Verificar que la publicación sea suya
    $q = $conn->prepare("SELECT id FROM publicaciones WHERE id = ? AND publicador_id = ?");
    $q->bind_param("ii", $id, $publicador_id);
    $q->execute();
    $r = $q->get_result();

    if ($r->num_rows === 1) {
        $delete = $conn->prepare("DELETE FROM publicaciones WHERE id = ?");
        $delete->bind_param("i", $id);
        if ($delete->execute()) {
            $mensaje_exito = "Publicación eliminada correctamente";
        } else {
            $mensaje_error = "Error al eliminar la publicación";
        }
    } else {
        $mensaje_error = "No tienes permiso para eliminar esta publicación";
    }
}


/* -------------------------------
   OBTENER PUBLICACIONES DEL PUBLICADOR
-------------------------------- */
$q = $conn->prepare("
    SELECT 
        p.id,
        p.titulo,
        p.estado,
        p.fecha_creacion,
        p.fecha_publicacion,
        p.imagen_principal,
        p.resumen,
        p.mensaje_rechazo,
        c.nombre AS categoria
    FROM publicaciones p
    LEFT JOIN categorias c ON c.id = p.categoria_id
    WHERE p.publicador_id = ?
    ORDER BY p.fecha_creacion DESC
");
$q->bind_param("i", $publicador_id);
$q->execute();
$publicaciones = $q->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Publicaciones - Lab-Explorer</title>

<!-- Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect">
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

<!-- Vendor CSS -->
<link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

<!-- Main CSS -->
<link href="../../assets/css/main.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css-admins/admin.css">

<style>
.publicacion-card{
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 2px 15px rgba(0,0,0,.1);
    transition:.3s;
}
.publicacion-card:hover{
    transform:translateY(-3px);
}
.publicacion-imagen{
    width:100%;
    height:140px;
    object-fit:cover;
}
.status-badge{
    padding:5px 12px;
    border-radius:20px;
    font-size:.8rem;
    font-weight:bold;
}
.status-badge.publicado{background:#d1f3e0;color:#198754;}
.status-badge.borrador{background:#e2e3e5;color:#6c757d;}
.status-badge.revision{background:#fff3cd;color:#997404;}
.status-badge.rechazada{background:#f8d7da;color:#721c24;}
</style>
</head>
<body class="publicador-page">

<!-- Header -->
<header id="header" class="header position-relative">
    <div class="container-fluid container-xl position-relative">
        <div class="top-row d-flex align-items-center justify-content-between">
            <a href="../../index.php" class="logo d-flex align-items-end">
                <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                <h1 class="sitename">Lab-Explorer</h1><span></span>
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

<div class="container mt-4">

    <!-- MENSAJES -->
    <?php if(isset($mensaje_exito)): ?>
    <div class="alert alert-success"><?= $mensaje_exito ?></div>
    <?php endif; ?>

    <?php if(isset($mensaje_error)): ?>
    <div class="alert alert-danger"><?= $mensaje_error ?></div>
    <?php endif; ?>


    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-newspaper"></i> Mis Publicaciones</h2>
            <p class="text-muted">Gestiona todas tus publicaciones.</p>
        </div>
        <a href="crear_nueva_publicacion.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Publicación
        </a>
    </div>


    <!-- LISTA DE PUBLICACIONES -->
    <div class="row">
        <?php if(empty($publicaciones)): ?>
            <div class="col-12 text-center text-muted mt-5">
                <i class="bi bi-file-earmark-x" style="font-size:50px;"></i>
                <p>No tienes publicaciones aún.</p>
                <a href="crear_nueva_publicacion.php" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle"></i> Crear mi primera publicación
                </a>
            </div>
        <?php else: ?>

        <?php foreach ($publicaciones as $p): ?>
        <div class="col-md-6 mb-4">
            <div class="publicacion-card">

                <!-- Imagen -->
                <?php if(!empty($p['imagen_principal'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($p['imagen_principal']) ?>" class="publicacion-imagen">
                <?php else: ?>
                <div class="publicacion-imagen bg-light d-flex justify-content-center align-items-center">
                    <i class="bi bi-image" style="font-size:40px;color:#ccc;"></i>
                </div>
                <?php endif; ?>

                <div class="p-3">
                    <h5><?= htmlspecialchars($p['titulo']) ?></h5>
                    <span class="status-badge <?= $p['estado'] ?>">
                        <?= ucfirst($p['estado']) ?>
                    </span>
                    <?php if($p['estado'] === 'rechazada' && !empty($p['mensaje_rechazo'])): ?>
                    <button class="btn btn-outline-danger btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalRechazo" data-mensaje="<?= htmlspecialchars($p['mensaje_rechazo']) ?>">
                        <i class="bi bi-exclamation-circle"></i> Ver Motivo
                    </button>
                    <?php endif; ?>
                    
                    <p class="text-muted mt-2">
                        <strong>Categoría:</strong> <?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?><br>
                        <strong>Creada:</strong> <?= date("d/m/Y", strtotime($p['fecha_creacion'])) ?>
                    </p>

                    <!-- RESUMEN -->
                    <?php if(!empty($p['resumen'])): ?>
                    <p><?= htmlspecialchars(substr($p['resumen'], 0, 100)) ?>...</p>
                    <?php endif; ?>

                    <!-- BOTONES -->
                    <div class="mt-3 d-flex justify-content-between">
                        <div>
                            <a href="editar_publicacion.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil"></i> Editar
                            </a>

                            <a href="../../ver-publicacion.php?id=<?= $p['id'] ?>" 
                               class="btn btn-outline-secondary btn-sm" target="_blank">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </div>

                        <button class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminar"
                                data-id="<?= $p['id'] ?>"
                                data-titulo="<?= htmlspecialchars($p['titulo']) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar Publicación</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Seguro que quieres eliminar:
                <strong id="tituloEliminar"></strong>?
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a id="btnEliminar" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RECHAZO -->
<div class="modal fade" id="modalRechazo" tabindex="-1" aria-labelledby="modalRechazoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalRechazoLabel"><i class="bi bi-x-circle"></i> Motivo de Rechazo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalRechazoBody">
                <!-- El mensaje se inserta vía JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Modal Eliminar
const modalEliminar = document.getElementById("modalEliminar");
modalEliminar.addEventListener("show.bs.modal", event => {
    let button = event.relatedTarget;
    let id = button.getAttribute("data-id");
    let titulo = button.getAttribute("data-titulo");

    document.getElementById("tituloEliminar").textContent = titulo;
    document.getElementById("btnEliminar").href = "mis-publicaciones.php?eliminar=" + id;
});

// Modal Rechazo - mostrar mensaje de rechazo
const modalRechazo = document.getElementById('modalRechazo');
modalRechazo.addEventListener('show.bs.modal', event => {
    let button = event.relatedTarget;
    let mensaje = button.getAttribute('data-mensaje');
    document.getElementById('modalRechazoBody').textContent = mensaje;
});
</script>

</body>
</html>
