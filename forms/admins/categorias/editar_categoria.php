<?php
// ============================================================================
// 📝 EDITAR CATEGORÍA - editar_categoria.php
// ============================================================================
// Este archivo permite a los administradores editar categorías existentes.
//
// FLUJO:
// 1. Verificar que el usuario sea administrador
// 2. Obtener el ID de la categoría desde la URL (?id=5)
// 3. Cargar los datos actuales de la categoría desde la BD
// 4. Mostrar formulario pre-llenado con los datos actuales
// 5. Al enviar (POST), actualizar la categoría en la BD
// 6. Mostrar mensaje de éxito o error
//
// SEGURIDAD:
// - Solo administradores pueden acceder (requerirAdmin)
// - Validación de ID en la URL
// - Datos sanitizados en la clase Categoria
// - htmlspecialchars para prevenir XSS
// ============================================================================

// ============================================================================
// PASO 1: VERIFICAR SESIÓN DE ADMINISTRADOR
// ============================================================================

session_start();
// Iniciamos la sesión para acceder a $_SESSION

require_once '../config-admin.php';
// Traemos el archivo de configuración de admins

requerirAdmin();
// Verificamos que haya un admin logueado

// ============================================================================
// PASO 2: OBTENER DATOS DEL ADMINISTRADOR
// ============================================================================

$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'];

// ============================================================================
// PASO 3: OBTENER ESTADÍSTICAS
// ============================================================================

$stats = obtenerEstadisticasAdmin($conn);

// ============================================================================
// PASO 4: INCLUIR ARCHIVOS DE CATEGORÍAS (POO)
// ============================================================================

include_once 'config-categorias.php';
include_once 'categoria.php';

$database = new Database();
$db = $database->getConnection();
$categoria = new Categoria($db);

// ============================================================================
// PASO 5: OBTENER Y VALIDAR EL ID DE LA CATEGORÍA
// ============================================================================

$categoria->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no especificado.');
// ========================================================================
// 📌 EXPLICACIÓN DEL OPERADOR TERNARIO
// ========================================================================
// condición ? valor_si_true : valor_si_false
//
// EJEMPLO:
// $edad = 18;
// $mensaje = $edad >= 18 ? "Mayor de edad" : "Menor de edad";
// Resultado: "Mayor de edad"
//
// EN ESTE CASO:
// Si isset($_GET['id']) es true:
//     $categoria->id = $_GET['id']
// Si es false:
//     die('ERROR: ID no especificado.')
//
// die() detiene la ejecución del script y muestra un mensaje
// Es útil para errores críticos que impiden continuar

// ============================================================================
// PASO 6: CARGAR DATOS DE LA CATEGORÍA
// ============================================================================

$mensaje = "";
$exito = false;

if ($categoria->leerUna()) {
    // ====================================================================
    // 📌 EXPLICACIÓN DE leerUna()
    // ====================================================================
    // Método de la clase Categoria que:
    // 1. Busca en la BD la categoría con el ID especificado
    // 2. Si la encuentra, llena las propiedades del objeto:
    //    - $categoria->nombre
    //    - $categoria->descripcion
    //    - $categoria->color
    //    - $categoria->icono
    //    - $categoria->estado
    //    - $categoria->slug
    //    - $categoria->fecha_creacion
    // 3. Devuelve true si encontró la categoría, false si no
    
    // ====================================================================
    // PASO 7: PROCESAR FORMULARIO (POST)
    // ====================================================================
    
    if ($_POST) {
        // Si el usuario envió el formulario (click en "Actualizar")
        
        // Asignamos los nuevos valores a las propiedades del objeto
        $categoria->nombre = $_POST['nombre'];
        $categoria->descripcion = $_POST['descripcion'];
        $categoria->color = $_POST['color'];
        $categoria->icono = $_POST['icono'];
        $categoria->estado = $_POST['estado'];
        
        // Intentamos actualizar la categoría
        if ($categoria->actualizar()) {
            // ============================================================
            // 📌 EXPLICACIÓN DE actualizar()
            // ============================================================
            // Método que:
            // 1. Genera el slug automáticamente del nuevo nombre
            // 2. Sanitiza los datos (htmlspecialchars, strip_tags)
            // 3. Hace UPDATE en la BD
            // 4. Devuelve true si tuvo éxito, false si falló
            
            $mensaje = 'Categoría actualizada exitosamente';
            $exito = true;
        } else {
            $mensaje = 'Error al actualizar la categoría';
            $exito = false;
        }
    }
} else {
    // Si no encontró la categoría con ese ID
    die('ERROR: Categoría no encontrada.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría - Lab-Explorer</title>
    
    <!-- Fuentes de Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS de Vendors -->
    <link href="../../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- CSS Personalizado -->
    <link href="../../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css-admins/admin.css">
    
    <style>
        /* Estilos para el preview del color */
        .color-preview {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            border: 2px solid #ddd;
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
        /* Estilos para el preview del icono */
        .icon-preview {
            font-size: 2rem;
            margin-left: 10px;
        }
    </style>
</head>
<body class="admin-page">

    <!-- HEADER -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                
                <a href="../../../index.php" class="logo d-flex align-items-end">
                    <img src="../../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
                </a>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <!-- Mostramos el nombre del admin logueado -->
                        <span class="saludo">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</span>
                        <a href="../logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- CONTENIDO PRINCIPAL -->
    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="../../../index.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Página principal
                            </a>
                            <a href="../index-admin.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Panel Principal
                            </a>
                            <a href="../gestionar_publicadores.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-people me-2"></i>Gestionar Publicadores
                            </a>
                            <a href="../gestionar-publicaciones.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
                            </a>
                            <a href="listar_categorias.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-tags me-2"></i>Ver Categorías
                            </a>
                            <a href="crear_categoria.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-plus-circle me-2"></i>Crear Categoría
                            </a>
                            
                            <?php if($admin_nivel == 'superadmin'): ?>
                            <a href="../admins.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Resumen rápido -->
                        <div class="quick-stats-card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Resumen del Sistema</h6>
                            </div>
                            <div class="card-body">
                                <div class="stat-item">
                                    <small class="text-muted">Usuarios: <?= $stats['total_usuarios'] ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Publicadores: <?= $stats['total_publicadores'] ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Publicaciones: <?= $stats['total_publicaciones'] ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Pendientes: <?= $stats['publicadores_pendientes'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTENIDO DERECHO -->
                <div class="col-md-9">
                    
                    <!-- Mensajes de Alerta -->
                    <?php if($mensaje): ?>
                    <div class="alert-message <?= $exito ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Editar Categoría</h2>
                        <p>Modifica los datos de la categoría <strong><?= htmlspecialchars($categoria->nombre) ?></strong></p>
                    </div>
                    
                    <!-- Formulario de Editar Categoría -->
                    <div class="card" data-aos="fade-up" data-aos-delay="100">
                        <div class="card-body">
                            <form method="POST" id="formCategoria">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nombre" class="form-label">Nombre de la Categoría *</label>
                                        <!-- value pre-llenado con el valor actual -->
                                        <input type="text" class="form-control" id="nombre" name="nombre" required 
                                               value="<?= htmlspecialchars($categoria->nombre) ?>"
                                               placeholder="Ej: Hematología, Parasitología...">
                                        <small class="text-muted">El slug se actualizará automáticamente</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <!-- Pre-seleccionamos el estado actual -->
                                            <option value="activo" <?= $categoria->estado == 'activo' ? 'selected' : '' ?>>Activo</option>
                                            <option value="inactivo" <?= $categoria->estado == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <!-- Mostramos la descripción actual dentro del textarea -->
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                              placeholder="Descripción breve de la categoría..."><?= htmlspecialchars($categoria->descripcion) ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="color" class="form-label">Color de la Categoría</label>
                                        <div class="d-flex align-items-center">
                                            <!-- Input tipo color con el valor actual -->
                                            <input type="color" class="form-control form-control-color" id="color" name="color" 
                                                   value="<?= htmlspecialchars($categoria->color) ?>">
                                            <!-- Preview del color actual -->
                                            <span class="color-preview ms-2" id="colorPreview" 
                                                  style="background-color: <?= htmlspecialchars($categoria->color) ?>;"></span>
                                        </div>
                                        <small class="text-muted">Este color identifica la categoría</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="icono" class="form-label">Icono (Bootstrap Icons)</label>
                                        <div class="d-flex align-items-center">
                                            <!-- Input con el icono actual -->
                                            <input type="text" class="form-control" id="icono" name="icono" 
                                                   value="<?= htmlspecialchars($categoria->icono) ?>"
                                                   placeholder="bi-flask">
                                            <!-- Preview del icono actual -->
                                            <i class="bi <?= htmlspecialchars($categoria->icono) ?> icon-preview" id="iconPreview"></i>
                                        </div>
                                        <small class="text-muted">Usa nombres de <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a></small>
                                    </div>
                                </div>
                                
                                <!-- Información adicional -->
                                <div class="alert alert-info">
                                    <strong>Información:</strong><br>
                                    <small>Slug actual: <code><?= htmlspecialchars($categoria->slug) ?></code></small><br>
                                    <small>Creado: <?= date('d/m/Y H:i', strtotime($categoria->fecha_creacion)) ?></small>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <a href="listar_categorias.php" class="btn btn-secondary me-md-2">
                                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Actualizar Categoría
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="../../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../assets/vendor/aos/aos.js"></script>
    <script>
        // ====================================================================
        // 📌 EXPLICACIÓN DE AOS (Animate On Scroll)
        // ====================================================================
        // AOS es una librería que anima elementos cuando aparecen en pantalla
        // al hacer scroll.
        //
        // CONFIGURACIÓN:
        // duration: Duración de la animación en milisegundos
        // once: Si es true, la animación solo ocurre una vez
        
        AOS.init({
            duration: 1000,  // 1 segundo
            once: true       // Solo animar una vez
        });

        // ====================================================================
        // 📌 PREVIEW DEL COLOR EN TIEMPO REAL
        // ====================================================================
        
        document.getElementById('color').addEventListener('input', function() {
            // ================================================================
            // 📌 EXPLICACIÓN DE addEventListener()
            // ================================================================
            // Escucha eventos en un elemento HTML.
            //
            // SINTAXIS:
            // elemento.addEventListener(evento, función)
            //
            // EVENTOS COMUNES:
            // 'input' = Cuando cambia el valor (en tiempo real)
            // 'change' = Cuando cambia y pierde el foco
            // 'click' = Cuando se hace click
            // 'submit' = Cuando se envía un formulario
            // 'keyup' = Cuando se suelta una tecla
            //
            // this = Referencia al elemento que disparó el evento
            // this.value = El valor actual del input
            
            document.getElementById('colorPreview').style.backgroundColor = this.value;
            // Actualizamos el color de fondo del preview
        });

        // ====================================================================
        // 📌 PREVIEW DEL ICONO EN TIEMPO REAL
        // ====================================================================
        
        document.getElementById('icono').addEventListener('input', function() {
            const iconPreview = document.getElementById('iconPreview');
            // ================================================================
            // 📌 EXPLICACIÓN DE const
            // ================================================================
            // const declara una variable de solo lectura (constante).
            //
            // DIFERENCIAS:
            // var = Alcance de función, puede redeclararse
            // let = Alcance de bloque, no puede redeclararse
            // const = Alcance de bloque, no puede reasignarse
            //
            // EJEMPLO:
            // const nombre = "Juan";
            // nombre = "Pedro"; // ERROR!
            //
            // let edad = 25;
            // edad = 26; // OK
            //
            // BUENA PRÁCTICA:
            // Usar const por defecto, let solo si necesitas reasignar
            
            iconPreview.className = 'bi ' + this.value + ' icon-preview';
            // ================================================================
            // 📌 EXPLICACIÓN DE className
            // ================================================================
            // className cambia las clases CSS de un elemento.
            //
            // EJEMPLO:
            // Si this.value = "bi-flask"
            // className = 'bi bi-flask icon-preview'
            //
            // Esto hace que el icono cambie visualmente
        });
    </script>
</body>
</html>