# 🚀 GUÍA TÉCNICA 3.0 ULTRA DETALLADA - Lab Explorer
## Explicación Completa de TODOS los Archivos del Proyecto

---

## 📁 ESTRUCTURA COMPLETA DEL PROYECTO

```
Lab/
├── 📄 index.php                           # Página principal del sitio
├── 📄 ver-publicacion.php                 # Vista individual de publicación (usuarios)
├── 📄 ver-publicacion-admins.php          # Vista individual de publicación (admins)
├── 📄 update_db_rechazo.php               # Script para actualizar BD
│
├── 📂 forms/                              # Carpeta principal de formularios
│   ├── 📄 conexion.php                    # Conexión global a la base de datos
│   ├── 📄 usuario.php                     # Gestión de sesión de usuarios
│   ├── 📄 inicio-sesion.php               # Login de usuarios normales
│   ├── 📄 logout.php                      # Logout de usuarios normales
│   ├── 📄 register.php                    # Registro de usuarios normales
│   ├── 📄 recuperar.php                   # Recuperación de contraseña usuarios
│   ├── 📄 recuperar-publicador.php        # Recuperación de contraseña publicadores
│   ├── 📄 perfil.php                      # Perfil de usuario normal
│   ├── 📄 procesar_imagen.php             # Subir foto de perfil
│   ├── 📄 eliminar_foto.php               # Eliminar foto de perfil
│   ├── 📄 debug_sesion.php                # Herramienta de debug de sesiones
│   ├── 📄 test.php                        # Archivo de pruebas
│   │
│   ├── 📂 publicadores/                   # Módulo de publicadores
│   │   ├── 📄 config-publicadores.php     # Configuración y funciones de publicadores
│   │   ├── 📄 inicio-sesion-publicadores.php  # Login de publicadores
│   │   ├── 📄 logout-publicadores.php     # Logout de publicadores
│   │   ├── 📄 registro-publicadores.php   # Registro de nuevos publicadores
│   │   ├── 📄 index-publicadores.php      # Dashboard de publicadores
│   │   ├── 📄 perfil.php                  # Perfil de publicador
│   │   ├── 📄 crear_nueva_publicacion.php # Formulario crear publicación
│   │   ├── 📄 editar_publicacion.php      # Formulario editar publicación
│   │   ├── 📄 guardar_publicacion.php     # Procesar nueva publicación
│   │   ├── 📄 actualizar_publicacion.php  # Procesar edición de publicación
│   │   ├── 📄 mis-publicaciones.php       # Listado de publicaciones del publicador
│   │   ├── 📄 subir_imagen_contenido.php  # Subir imágenes del editor Quill
│   │   ├── 📄 publicacion.php             # Vista de publicación (publicadores)
│   │   └── 📂 models/
│   │       └── 📄 publicadores.php        # Modelo de datos de publicadores
│   │
│   └── 📂 admins/                         # Módulo de administradores
│       ├── 📄 config-admin.php            # Configuración y funciones de admins
│       ├── 📄 login-admin.php             # Login de administradores
│       ├── 📄 logout-admin.php            # Logout de administradores
│       ├── 📄 register-admin.php          # Registro de nuevos admins
│       ├── 📄 index-admin.php             # Dashboard principal de admins
│       ├── 📄 admins.php                  # Gestión de administradores (superadmin)
│       ├── 📄 gestionar_publicadores.php  # Gestión de publicadores
│       ├── 📄 enviar_correo_publicador.php # Envío de correos a publicadores
│       ├── 📄 gestionar-publicaciones.php # Gestión de publicaciones
│       ├── 📄 editar-publicacion.php      # Editar publicación (admin)
│       ├── 📄 historial-publicaciones.php # Historial de todas las publicaciones
│       └── 📂 categorias/                 # Módulo de categorías
│           ├── 📄 config-categorias.php   # Configuración PDO para categorías
│           ├── 📄 categoria.php           # Clase Categoria (POO)
│           ├── 📄 crear_categoria.php     # Formulario crear categoría
│           ├── 📄 editar_categoria.php    # Formulario editar categoría
│           ├── 📄 eliminar_categoria.php  # Eliminar categoría
│           └── 📄 listar_categorias.php   # Listado de categorías
│
└── 📂 assets/                             # Recursos estáticos
    ├── 📂 css/                            # Hojas de estilo
    ├── 📂 js/                             # JavaScript
    ├── 📂 img/                            # Imágenes
    └── 📂 vendor/                         # Librerías externas
        └── 📂 bootstrap/                  # Framework Bootstrap
```

---

# 📄 ARCHIVOS RAÍZ

## `index.php` - Página Principal

### 🎯 Propósito
Página principal del sitio que muestra todas las publicaciones organizadas por categorías.

### 📋 Código Detallado

```php
<?php
// ============================================================================
// SECCIÓN 1: INICIALIZACIÓN DE SESIÓN
// ============================================================================
session_start();
// ¿Qué hace? Inicia o reanuda la sesión del usuario
// ¿Por qué? Para saber si hay alguien logueado y mostrar su nombre
// session_start() DEBE ir antes de cualquier salida HTML

// ============================================================================
// SECCIÓN 2: INCLUIR ARCHIVOS NECESARIOS
// ============================================================================
require_once './forms/conexion.php';
// Incluye el archivo de conexión a la base de datos
// require_once = incluye solo una vez, si falla detiene el script

require_once __DIR__ . "/forms/usuario.php";
// Incluye el archivo que gestiona la sesión del usuario
// __DIR__ = constante mágica con la ruta del directorio actual

// ============================================================================
// SECCIÓN 3: CONSULTA PRINCIPAL - OBTENER PUBLICACIONES
// ============================================================================

// 3.1 Preparar la consulta SQL con LEFT JOIN
$query = "SELECT 
    p.*,                                    -- Todos los campos de publicaciones
    c.nombre as categoria_nombre,           -- Nombre de la categoría
    pub.nombre as publicador_nombre         -- Nombre del publicador
FROM publicaciones p                        -- Tabla principal: publicaciones (alias 'p')
LEFT JOIN categorias c ON p.categoria_id = c.id     -- Unir con categorías
LEFT JOIN publicadores pub ON p.publicador_id = pub.id  -- Unir con publicadores
WHERE p.estado = 'publicado'                -- Solo publicaciones publicadas
ORDER BY p.fecha_creacion DESC";            -- Más recientes primero

// EXPLICACIÓN DE LEFT JOIN:
// LEFT JOIN mantiene TODAS las filas de la tabla izquierda (publicaciones)
// aunque no haya coincidencia en la tabla derecha (categorías/publicadores)
// 
// Ejemplo:
// Si una publicación tiene categoria_id = NULL
// LEFT JOIN devolverá: categoria_nombre = NULL
// INNER JOIN NO devolvería esa fila
//
// ¿Por qué LEFT JOIN aquí?
// Para mostrar publicaciones incluso si no tienen categoría asignada

// 3.2 Ejecutar la consulta
$result = $conexion->query($query);
// query() ejecuta la consulta SQL directa (sin parámetros)
// Devuelve un objeto mysqli_result con los resultados

// 3.3 Verificar si hay resultados
if (!$result) {
    // Si la consulta falló
    die("Error en la consulta: " . $conexion->error);
    // die() detiene el script y muestra el mensaje
    // $conexion->error contiene el mensaje de error de MySQL
}

// ============================================================================
// SECCIÓN 4: ORGANIZAR PUBLICACIONES POR CATEGORÍA
// ============================================================================

// 4.1 Crear array vacío para agrupar
$publicaciones_por_categoria = [];
// Array asociativo donde la clave será el ID de categoría
// y el valor será un array de publicaciones

// 4.2 Recorrer resultados y agrupar
while ($pub = $result->fetch_assoc()) {
    // fetch_assoc() obtiene la siguiente fila como array asociativo
    // Ejemplo de $pub:
    // [
    //     'id' => 1,
    //     'titulo' => 'Análisis de Sangre',
    //     'categoria_id' => 5,
    //     'categoria_nombre' => 'Hematología',
    //     'publicador_nombre' => 'Dr. Juan Pérez'
    // ]
    
    $cat_id = $pub['categoria_id'];
    // Obtener el ID de la categoría de esta publicación
    
    if (!isset($publicaciones_por_categoria[$cat_id])) {
        // Si esta categoría aún no existe en el array
        $publicaciones_por_categoria[$cat_id] = [];
        // Crear un array vacío para esta categoría
    }
    
    $publicaciones_por_categoria[$cat_id][] = $pub;
    // Agregar la publicación al array de su categoría
    // [] = agregar al final del array
}

// Resultado final de $publicaciones_por_categoria:
// [
//     5 => [  // Categoría ID 5 (Hematología)
//         ['id' => 1, 'titulo' => 'Análisis...'],
//         ['id' => 3, 'titulo' => 'Glóbulos...']
//     ],
//     7 => [  // Categoría ID 7 (Parasitología)
//         ['id' => 2, 'titulo' => 'Parásitos...']
//     ]
// ]

// ============================================================================
// SECCIÓN 5: OBTENER CATEGORÍAS PARA EL MENÚ
// ============================================================================

// 5.1 Consulta para obtener todas las categorías activas
$query_categorias = "SELECT * FROM categorias WHERE estado = 'activo' ORDER BY nombre ASC";
// ORDER BY nombre ASC = ordenar alfabéticamente de A-Z

$result_categorias = $conexion->query($query_categorias);

// 5.2 Guardar categorías en un array
$categorias = [];
while ($cat = $result_categorias->fetch_assoc()) {
    $categorias[] = $cat;
    // Agregar cada categoría al array
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- UTF-8 permite usar acentos y caracteres especiales -->
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Hace que la página sea responsive (se adapte a móviles) -->
    <!-- width=device-width = ancho igual al del dispositivo -->
    <!-- initial-scale=1.0 = zoom inicial al 100% -->
    
    <title>Lab Explorer - Laboratorio Clínico</title>
    <!-- Título que aparece en la pestaña del navegador -->
    
    <meta name="description" content="Portal de información de laboratorio clínico">
    <!-- Meta descripción para SEO (Google) -->
    
    <!-- ================================================================ -->
    <!-- SECCIÓN 6: ENLACES A HOJAS DE ESTILO (CSS) -->
    <!-- ================================================================ -->
    
    <!-- 6.1 Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <!-- preconnect = pre-conectar al servidor para cargar más rápido -->
    
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <!-- gstatic.com = CDN de Google para fuentes -->
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Carga la fuente Roboto con diferentes pesos (300=ligera, 700=negrita) -->
    <!-- display=swap = muestra texto con fuente del sistema mientras carga -->
    
    <!-- 6.2 Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap = framework CSS con componentes pre-hechos -->
    
    <!-- 6.3 Bootstrap Icons -->
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- Librería de iconos de Bootstrap -->
    
    <!-- 6.4 AOS (Animate On Scroll) -->
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <!-- Librería para animaciones al hacer scroll -->
    
    <!-- 6.5 CSS Principal -->
    <link href="assets/css/main.css" rel="stylesheet">
    <!-- Nuestros estilos personalizados -->
</head>

<body>
    <!-- ================================================================ -->
    <!-- SECCIÓN 7: HEADER (ENCABEZADO) -->
    <!-- ================================================================ -->
    <header id="header" class="header position-relative">
        <!-- id="header" = identificador único para JavaScript/CSS -->
        <!-- class="header" = clase para estilos -->
        <!-- position-relative = posición relativa (Bootstrap) -->
        
        <div class="container-fluid container-xl position-relative">
            <!-- container-fluid = contenedor de ancho completo -->
            <!-- container-xl = contenedor con ancho máximo en pantallas XL -->
            
            <div class="top-row d-flex align-items-center justify-content-between">
                <!-- d-flex = display: flex (Bootstrap) -->
                <!-- align-items-center = alinear verticalmente al centro -->
                <!-- justify-content-between = espacio entre elementos -->
                
                <!-- 7.1 Logo -->
                <a href="index.php" class="logo d-flex align-items-end">
                    <img src="assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1>
                </a>
                
                <!-- 7.2 Menú de Usuario -->
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <!-- Si hay usuario logueado -->
                            <span class="saludo">
                                Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                            </span>
                            <!-- htmlspecialchars() previene XSS -->
                            <!-- <?= ?> = atajo de <?php echo ?> -->
                            
                            <a href="forms/logout.php" class="btn-publicador">
                                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                            </a>
                        <?php else: ?>
                            <!-- Si NO hay usuario logueado -->
                            <a href="forms/inicio-sesion.php" class="btn-publicador">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ================================================================ -->
    <!-- SECCIÓN 8: CONTENIDO PRINCIPAL -->
    <!-- ================================================================ -->
    <main class="main">
        <div class="container">
            <h2 class="text-center my-5">Publicaciones por Categoría</h2>
            <!-- my-5 = margin-y (vertical) de 5 unidades (Bootstrap) -->
            
            <?php foreach ($publicaciones_por_categoria as $cat_id => $publicaciones): ?>
                <!-- Recorrer cada categoría -->
                <!-- $cat_id = ID de la categoría -->
                <!-- $publicaciones = array de publicaciones de esa categoría -->
                
                <?php
                // Obtener nombre de la categoría
                $nombre_categoria = "Sin categoría";
                foreach ($categorias as $cat) {
                    if ($cat['id'] == $cat_id) {
                        $nombre_categoria = $cat['nombre'];
                        break; // Salir del bucle cuando la encuentre
                    }
                }
                ?>
                
                <section class="categoria-section mb-5" data-aos="fade-up">
                    <!-- data-aos="fade-up" = animación de aparición -->
                    
                    <h3 class="categoria-titulo">
                        <?= htmlspecialchars($nombre_categoria) ?>
                    </h3>
                    
                    <div class="row">
                        <!-- row = fila de Bootstrap Grid -->
                        
                        <?php foreach ($publicaciones as $pub): ?>
                            <!-- Recorrer cada publicación de esta categoría -->
                            
                            <div class="col-md-4 mb-4">
                                <!-- col-md-4 = 4 columnas de 12 (33.33%) en pantallas medianas -->
                                <!-- mb-4 = margin-bottom 4 -->
                                
                                <div class="card h-100">
                                    <!-- card = tarjeta de Bootstrap -->
                                    <!-- h-100 = height 100% -->
                                    
                                    <?php if (!empty($pub['imagen_principal'])): ?>
                                        <img src="<?= htmlspecialchars($pub['imagen_principal']) ?>" 
                                             class="card-img-top" 
                                             alt="<?= htmlspecialchars($pub['titulo']) ?>">
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?= htmlspecialchars($pub['titulo']) ?>
                                        </h5>
                                        
                                        <p class="card-text">
                                            <?php
                                            // Mostrar resumen (máximo 150 caracteres)
                                            $resumen = $pub['resumen'] ?? strip_tags($pub['contenido']);
                                            // ?? = operador null coalescing
                                            // Si resumen es null, usa contenido sin HTML
                                            
                                            echo htmlspecialchars(substr($resumen, 0, 150)) . '...';
                                            // substr() = obtener substring
                                            // 0 = inicio, 150 = longitud
                                            ?>
                                        </p>
                                        
                                        <div class="meta-info">
                                            <small class="text-muted">
                                                <i class="bi bi-person"></i>
                                                <?= htmlspecialchars($pub['publicador_nombre']) ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i>
                                                <?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?>
                                                <!-- date() formatea fecha -->
                                                <!-- strtotime() convierte string a timestamp -->
                                            </small>
                                        </div>
                                        
                                        <a href="ver-publicacion.php?id=<?= $pub['id'] ?>" 
                                           class="btn btn-primary mt-3">
                                            Leer más
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- ================================================================ -->
    <!-- SECCIÓN 9: SCRIPTS DE JAVASCRIPT -->
    <!-- ================================================================ -->
    
    <!-- 9.1 Bootstrap JS -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- bundle = incluye Popper.js (para tooltips, dropdowns) -->
    
    <!-- 9.2 AOS JS -->
    <script src="assets/vendor/aos/aos.js"></script>
    <script>
        // Inicializar AOS (animaciones)
        AOS.init({
            duration: 1000,  // Duración de animación en ms
            once: true       // Animar solo una vez
        });
    </script>
    
    <!-- 9.3 Script Principal -->
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
// ============================================================================
// SECCIÓN 10: CERRAR CONEXIÓN
// ============================================================================
$conexion->close();
// Cerrar la conexión a la base de datos
// Libera recursos del servidor MySQL
?>
```

### 🔑 Conceptos Clave Explicados

#### LEFT JOIN vs INNER JOIN
```sql
-- LEFT JOIN: Mantiene TODAS las publicaciones
SELECT p.*, c.nombre 
FROM publicaciones p 
LEFT JOIN categorias c ON p.categoria_id = c.id;
-- Resultado: 100 publicaciones (algunas con categoria_nombre = NULL)

-- INNER JOIN: Solo publicaciones CON categoría
SELECT p.*, c.nombre 
FROM publicaciones p 
INNER JOIN categorias c ON p.categoria_id = c.id;
-- Resultado: 85 publicaciones (solo las que tienen categoría)
```

#### Operador Null Coalescing (??)
```php
$resumen = $pub['resumen'] ?? strip_tags($pub['contenido']);
// Si $pub['resumen'] existe y no es null, usa ese
// Si no, usa strip_tags($pub['contenido'])

// Equivalente a:
if (isset($pub['resumen']) && $pub['resumen'] !== null) {
    $resumen = $pub['resumen'];
} else {
    $resumen = strip_tags($pub['contenido']);
}
```

---

## `ver-publicacion.php` - Vista Individual de Publicación

### 🎯 Propósito
Muestra una publicación completa con todo su contenido, imágenes y metadatos.

### 📋 Código Detallado Continuará en siguiente sección...

---

*Nota: Esta es la Parte 1 de la Guía Técnica 3.0. Continuará con todos los demás archivos...*
