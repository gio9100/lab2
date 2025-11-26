# 🚀 GUÍA TÉCNICA 2.0 - Lab Explorer
## Organizada por Tecnologías y Carpetas del Proyecto

---

## 📁 ESTRUCTURA DEL PROYECTO

```
Lab/
├── index.php                    # Página principal
├── ver-publicacion.php          # Ver publicación individual
├── ver-publicacion-admins.php   # Ver publicación (admin)
├── forms/                       # Carpeta de formularios
│   ├── conexion.php            # Conexión a BD
│   ├── usuario.php             # Gestión de sesión usuario
│   ├── inicio-sesion.php       # Login usuarios
│   ├── logout.php              # Logout usuarios
│   ├── register.php            # Registro usuarios
│   ├── recuperar.php           # Recuperar contraseña usuarios
│   ├── perfil.php              # Perfil usuario
│   ├── procesar_imagen.php     # Subir foto perfil
│   ├── eliminar_foto.php       # Eliminar foto perfil
│   ├── publicadores/           # Carpeta publicadores
│   │   ├── config-publicadores.php
│   │   ├── inicio-sesion-publicadores.php
│   │   ├── logout-publicadores.php
│   │   ├── registro-publicadores.php
│   │   ├── index-publicadores.php
│   │   ├── perfil.php
│   │   ├── crear_nueva_publicacion.php
│   │   ├── editar_publicacion.php
│   │   ├── guardar_publicacion.php
│   │   ├── actualizar_publicacion.php
│   │   ├── mis-publicaciones.php
│   │   └── subir_imagen_contenido.php
│   └── admins/                 # Carpeta administradores
│       ├── config-admin.php
│       ├── login-admin.php
│       ├── logout-admin.php
│       ├── register-admin.php
│       ├── index-admin.php
│       ├── admins.php
│       ├── gestionar_publicadores.php
│       ├── enviar_correo_publicador.php
│       ├── gestionar-publicaciones.php
│       ├── editar-publicacion.php
│       ├── historial-publicaciones.php
│       └── categorias/
│           ├── config-categorias.php
│           ├── categoria.php (clase)
│           ├── crear_categoria.php
│           ├── editar_categoria.php
│           ├── eliminar_categoria.php
│           └── listar_categorias.php
└── assets/                     # Recursos estáticos
    ├── css/
    ├── js/
    ├── img/
    └── vendor/
        └── bootstrap/
```

---

## 🎨 BOOTSTRAP - FRAMEWORK CSS

### ¿Qué es Bootstrap?
Framework CSS que nos da estilos y componentes pre-hechos para crear interfaces bonitas rápidamente.

### Componentes Usados en el Proyecto

#### 1. **Grid System (Sistema de Cuadrículas)**
```html
<div class="container">           <!-- Contenedor principal -->
    <div class="row">              <!-- Fila -->
        <div class="col-md-6">     <!-- Columna (50% en pantallas medianas) -->
            Contenido
        </div>
        <div class="col-md-6">     <!-- Otra columna (50%) -->
            Más contenido
        </div>
    </div>
</div>
```

**Explicación:**
- `container` = Contenedor con márgenes automáticos
- `container-fluid` = Contenedor de ancho completo
- `row` = Fila que contiene columnas
- `col-md-6` = Columna que ocupa 6/12 (50%) en pantallas medianas
- `col-12` = Columna de ancho completo
- `col-md-4` = 33.33% (4/12)
- `col-md-3` = 25% (3/12)

#### 2. **Formularios**
```html
<form>
    <div class="mb-3">                    <!-- Margen bottom 3 -->
        <label class="form-label">Nombre</label>
        <input type="text" class="form-control" placeholder="Tu nombre">
    </div>
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>
```

**Clases de Formularios:**
- `form-control` = Input con estilos Bootstrap
- `form-label` = Etiqueta de formulario
- `form-select` = Select con estilos
- `form-check` = Checkbox/radio
- `mb-3` = Margin bottom 3 (espaciado)

#### 3. **Botones**
```html
<button class="btn btn-primary">Primario</button>
<button class="btn btn-success">Éxito</button>
<button class="btn btn-danger">Peligro</button>
<button class="btn btn-warning">Advertencia</button>
<button class="btn btn-secondary">Secundario</button>
<button class="btn btn-outline-primary">Outline</button>
<button class="btn btn-sm">Pequeño</button>
<button class="btn btn-lg">Grande</button>
```

**Variantes:**
- `btn-primary` = Azul
- `btn-success` = Verde
- `btn-danger` = Rojo
- `btn-warning` = Amarillo
- `btn-outline-*` = Solo borde
- `btn-sm` = Pequeño
- `btn-lg` = Grande

#### 4. **Cards (Tarjetas)**
```html
<div class="card">
    <div class="card-header">Encabezado</div>
    <div class="card-body">
        <h5 class="card-title">Título</h5>
        <p class="card-text">Contenido de la tarjeta</p>
    </div>
    <div class="card-footer">Pie de tarjeta</div>
</div>
```

**Usado en:** Listado de categorías, publicaciones, estadísticas

#### 5. **Modals (Ventanas Modales)**
```html
<!-- Botón que abre el modal -->
<button data-bs-toggle="modal" data-bs-target="#miModal">Abrir</button>

<!-- Modal -->
<div class="modal fade" id="miModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Título</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Contenido</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
```

**Usado en:** Confirmaciones de eliminación, motivo de rechazo

#### 6. **Alerts (Alertas)**
```html
<div class="alert alert-success">¡Éxito!</div>
<div class="alert alert-danger">¡Error!</div>
<div class="alert alert-warning">¡Advertencia!</div>
<div class="alert alert-info">Información</div>
```

**Usado en:** Mensajes de éxito/error en formularios

#### 7. **Badges (Insignias)**
```html
<span class="badge bg-success">Activo</span>
<span class="badge bg-danger">Inactivo</span>
<span class="badge bg-warning">Pendiente</span>
```

**Usado en:** Estados de publicaciones, categorías

#### 8. **Tables (Tablas)**
```html
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Juan</td>
            <td>juan@email.com</td>
        </tr>
    </tbody>
</table>
```

**Clases de Tablas:**
- `table` = Tabla básica
- `table-striped` = Filas alternadas
- `table-hover` = Efecto hover
- `table-responsive` = Scroll horizontal en móviles

#### 9. **Flexbox Utilities**
```html
<div class="d-flex justify-content-between align-items-center">
    <div>Izquierda</div>
    <div>Derecha</div>
</div>
```

**Clases Flexbox:**
- `d-flex` = Display flex
- `justify-content-between` = Espacio entre elementos
- `justify-content-center` = Centrar horizontalmente
- `align-items-center` = Centrar verticalmente
- `flex-column` = Dirección vertical

#### 10. **Spacing (Espaciado)**
```html
<div class="mt-3">Margen top 3</div>
<div class="mb-4">Margen bottom 4</div>
<div class="p-5">Padding 5</div>
<div class="mx-auto">Margen horizontal auto (centrado)</div>
```

**Sistema de Espaciado:**
- `m` = margin, `p` = padding
- `t` = top, `b` = bottom, `l` = left, `r` = right, `x` = horizontal, `y` = vertical
- Números: 0, 1, 2, 3, 4, 5 (0rem a 3rem)

#### 11. **Bootstrap Icons**
```html
<i class="bi bi-person"></i>        <!-- Icono persona -->
<i class="bi bi-envelope"></i>      <!-- Icono correo -->
<i class="bi bi-trash"></i>         <!-- Icono basura -->
<i class="bi bi-pencil"></i>        <!-- Icono lápiz -->
<i class="bi bi-eye"></i>           <!-- Icono ojo -->
```

**Usado en:** Botones, menús, estadísticas

---

## 📄 PHP - ARCHIVOS RAÍZ

### `index.php` - Página Principal

**Propósito:** Mostrar todas las publicaciones organizadas por categoría

**Conceptos Técnicos:**
```php
// 1. Iniciar sesión
session_start();

// 2. Incluir archivos
require_once './forms/conexion.php';
require_once './forms/usuario.php';

// 3. Consulta con LEFT JOIN
$query = "SELECT p.*, c.nombre as categoria_nombre, pub.nombre as publicador_nombre 
          FROM publicaciones p 
          LEFT JOIN categorias c ON p.categoria_id = c.id 
          LEFT JOIN publicadores pub ON p.publicador_id = pub.id 
          WHERE p.estado = 'publicado' 
          ORDER BY p.fecha_creacion DESC";

// 4. Agrupar por categorías
$publicaciones_por_categoria = [];
while ($pub = $result->fetch_assoc()) {
    $cat_id = $pub['categoria_id'];
    $publicaciones_por_categoria[$cat_id][] = $pub;
}

// 5. Mostrar en HTML
foreach ($publicaciones_por_categoria as $cat_id => $pubs) {
    // Mostrar cada categoría con sus publicaciones
}
```

**Funciones Clave:**
- `LEFT JOIN` = Une tablas (mantiene filas aunque no haya coincidencia)
- `fetch_assoc()` = Obtiene fila como array asociativo
- Agrupación por categoría con arrays multidimensionales

### `ver-publicacion.php` - Ver Publicación Individual

**Propósito:** Mostrar una publicación completa con su contenido

**Conceptos Técnicos:**
```php
// 1. Obtener ID de la URL
$publicacion_id = intval($_GET['id']);

// 2. Función para procesar contenido HTML
function procesarContenido($contenido) {
    // Si ya tiene HTML, solo agregar clases a imágenes
    if (strip_tags($contenido) !== $contenido) {
        return preg_replace('/\<img(?![^\>]*class=)/', '<img class="content-image"', $contenido);
    }
    
    // Si es texto plano, convertir saltos de línea
    $contenido = nl2br(htmlspecialchars($contenido));
    
    // Buscar rutas de imágenes y convertirlas en <img>
    $patron = '/uploads\/contenido\/[a-zA-Z0-9_\-\.]+\.(jpg|jpeg|png|gif|webp)/i';
    $contenido = preg_replace_callback($patron, function($matches) {
        return '<img src="' . $matches[0] . '" class="content-image" onclick="abrirLightbox(this.src)">';
    }, $contenido);
    
    return $contenido;
}

// 3. Lightbox para imágenes (JavaScript)
function abrirLightbox(src) {
    document.getElementById('lightbox').classList.add('active');
    document.getElementById('lightbox-img').src = src;
}
```

**Funciones Clave:**
- `strip_tags()` = Verifica si hay HTML
- `preg_replace_callback()` = Busca patrón y ejecuta función
- `nl2br()` = Convierte \n en <br>
- Lightbox = Modal para ver imágenes en grande

---

## 👥 PHP - CARPETA `forms/` (Usuarios)

### `conexion.php` - Conexión a Base de Datos

```php
// Verificar si ya hay sesión iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Datos de conexión
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

// Crear conexión con MySQLi
$conexion = new mysqli($servername, $username, $password, $dbname);

// Verificar errores
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar charset UTF-8
$conexion->set_charset("utf8mb4");
```

**Conceptos:**
- `session_status()` = Evita error "sesión ya iniciada"
- `mysqli` = Extensión para MySQL
- `utf8mb4` = Soporte completo Unicode (emojis, acentos)

### `inicio-sesion.php` - Login de Usuarios

```php
// 1. Validar email
$email = trim($_POST['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Email inválido";
}

// 2. Buscar usuario
$query = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// 3. Verificar contraseña
if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();
    if (password_verify($password, $usuario['password'])) {
        // Login exitoso
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        header('Location: ../index.php');
    }
}
```

**Flujo de Login:**
1. Validar formato de email
2. Buscar usuario en BD
3. Verificar contraseña con `password_verify()`
4. Crear sesión
5. Redirigir

### `register.php` - Registro de Usuarios

```php
// 1. Validar dominio de email
$dominios_permitidos = ['gmail.com', 'outlook.com', 'hotmail.com'];
$partes = explode('@', $email);
$dominio = $partes[1];

if (!in_array($dominio, $dominios_permitidos)) {
    $error = "Dominio no permitido";
}

// 2. Verificar si email ya existe
$check = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $error = "Email ya registrado";
}

// 3. Encriptar contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// 4. Insertar usuario
$insert = $conexion->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
$insert->bind_param("sss", $nombre, $email, $password_hash);
$insert->execute();
```

**Validaciones:**
- Dominio de email permitido
- Email único
- Contraseña hasheada con bcrypt

### `recuperar.php` - Recuperar Contraseña

```php
// 1. Generar token único
$token = bin2hex(random_bytes(32));
$expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

// 2. Guardar token en BD
$update = $conexion->prepare("UPDATE usuarios SET token_recuperacion = ?, token_expira = ? WHERE email = ?");
$update->bind_param("sss", $token, $expira, $email);
$update->execute();

// 3. Enviar email con PHPMailer
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'tu_email@gmail.com';
$mail->Password = 'tu_contraseña_app';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$link = "http://localhost/Lab/forms/recuperar.php?token=$token";
$mail->Body = "Haz click aquí para recuperar tu contraseña: $link";
$mail->send();
```

**Proceso:**
1. Generar token aleatorio seguro
2. Guardar token con expiración (1 hora)
3. Enviar email con link
4. Validar token al hacer click
5. Permitir cambio de contraseña

### `procesar_imagen.php` - Subir Foto de Perfil

```php
// 1. Validar tipo de archivo
$tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$tipo_real = mime_content_type($_FILES['foto']['tmp_name']);

if (!in_array($tipo_real, $tipos_permitidos)) {
    die("Tipo de archivo no permitido");
}

// 2. Validar tamaño (máx 5MB)
$tamano_maximo = 5 * 1024 * 1024;
if ($_FILES['foto']['size'] > $tamano_maximo) {
    die("Archivo muy grande");
}

// 3. Crear nombre único
$extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
$nombre_nuevo = 'perfil_' . $usuario_id . '_' . time() . '.' . $extension;

// 4. Mover archivo
$ruta_destino = '../uploads/perfiles/' . $nombre_nuevo;
move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino);

// 5. Actualizar BD
$update = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
$update->bind_param("si", $nombre_nuevo, $usuario_id);
$update->execute();
```

**Seguridad:**
- Validar tipo MIME real (no solo extensión)
- Limitar tamaño
- Nombre único (evita sobrescribir)
- Guardar fuera de carpeta pública

---

## 📝 PHP - CARPETA `publicadores/`

### `config-publicadores.php` - Configuración y Funciones

**Funciones Principales:**

#### 1. `loginPublicador()`
```php
function loginPublicador($email, $password, $conn) {
    $query = "SELECT * FROM publicadores WHERE email = ? AND estado = 'activo'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $publicador = $result->fetch_assoc();
        if (password_verify($password, $publicador['password'])) {
            // Actualizar último acceso
            $update = $conn->prepare("UPDATE publicadores SET ultimo_acceso = NOW() WHERE id = ?");
            $update->bind_param("i", $publicador['id']);
            $update->execute();
            return $publicador;
        }
    }
    return false;
}
```

**Diferencia con usuarios:** Solo permite login si `estado = 'activo'`

#### 2. `obtenerPublicaciones()`
```php
function obtenerPublicaciones($publicador_id, $conn) {
    $query = "SELECT p.*, c.nombre as categoria_nombre 
              FROM publicaciones p 
              LEFT JOIN categorias c ON p.categoria_id = c.id 
              WHERE p.publicador_id = ? 
              ORDER BY p.fecha_creacion DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $publicador_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

#### 3. `crearSlugPublicacion()`
```php
function crearSlugPublicacion($titulo, $conn) {
    // Crear slug base
    $slug = preg_replace('~[^\pL\d]+~u', '-', $titulo);
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
    $slug = preg_replace('~[^-\w]+~', '', $slug);
    $slug = trim($slug, '-');
    $slug = preg_replace('~-+~', '-', $slug);
    $slug = strtolower($slug);
    
    // Verificar si existe
    $check = $conn->prepare("SELECT id FROM publicaciones WHERE slug = ?");
    $check->bind_param("s", $slug);
    $check->execute();
    
    // Si existe, agregar número
    if ($check->get_result()->num_rows > 0) {
        $slug .= '-' . time();
    }
    
    return $slug;
}
```

**Diferencia con slug de categoría:** Verifica unicidad y agrega timestamp si existe

### `crear_nueva_publicacion.php` - Crear Publicación

**Tecnologías Usadas:**
- **Quill Editor** = Editor de texto enriquecido
- **JavaScript** = Validaciones y preview
- **Bootstrap** = Estilos

**Código Quill:**
```javascript
// Inicializar Quill
const quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image'],
            ['clean']
        ]
    },
    placeholder: 'Escribe el contenido de tu publicación...'
});

// Obtener contenido HTML
const contenido = quill.root.innerHTML;

// Insertar en campo oculto del formulario
document.getElementById('contenido_html').value = contenido;
```

**Validaciones JavaScript:**
```javascript
// Validar antes de enviar
form.addEventListener('submit', function(e) {
    const titulo = document.getElementById('titulo').value;
    const contenido = quill.root.innerHTML;
    
    if (titulo.length < 10) {
        e.preventDefault();
        alert('El título debe tener al menos 10 caracteres');
        return;
    }
    
    if (contenido.length < 50) {
        e.preventDefault();
        alert('El contenido es muy corto');
        return;
    }
});
```

### `guardar_publicacion.php` - Guardar Publicación

```php
// 1. Validar campos obligatorios
$campos_requeridos = ['titulo', 'contenido', 'categoria_id', 'tipo'];
foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        die("Campo $campo es obligatorio");
    }
}

// 2. Procesar imagen principal
if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['imagen_principal']['name'], PATHINFO_EXTENSION);
    $nombre_imagen = 'pub_' . time() . '_' . uniqid() . '.' . $extension;
    $ruta = 'uploads/publicaciones/' . $nombre_imagen;
    move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $ruta);
}

// 3. Crear slug
$slug = crearSlugPublicacion($_POST['titulo'], $conn);

// 4. Insertar en BD
$query = "INSERT INTO publicaciones (titulo, slug, contenido, resumen, imagen_principal, 
          categoria_id, publicador_id, tipo, estado, fecha_creacion) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($query);
$stmt->bind_param("sssssiiis", 
    $_POST['titulo'],
    $slug,
    $_POST['contenido'],
    $_POST['resumen'],
    $nombre_imagen,
    $_POST['categoria_id'],
    $_SESSION['publicador_id'],
    $_POST['tipo'],
    $_POST['estado']
);
$stmt->execute();

// 5. Si el estado es 'revision', notificar a admins
if ($_POST['estado'] === 'revision') {
    notificarAdmins($stmt->insert_id, $conn);
}
```

**Estados de Publicación:**
- `borrador` = Guardado pero no enviado
- `revision` = Enviado para revisión
- `publicado` = Aprobado y visible
- `rechazada` = Rechazado por admin

### `subir_imagen_contenido.php` - Subir Imágenes del Editor

```php
// 1. Verificar sesión
if (!isset($_SESSION['publicador_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

// 2. Validar archivo
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No se recibió imagen']);
    exit();
}

// 3. Validar tipo
$tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES['imagen']['type'], $tipos_permitidos)) {
    echo json_encode(['success' => false, 'error' => 'Tipo no permitido']);
    exit();
}

// 4. Validar tamaño (5MB)
if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'Imagen muy grande']);
    exit();
}

// 5. Crear carpeta si no existe
$directorio = 'uploads/contenido/';
if (!file_exists($directorio)) {
    mkdir($directorio, 0755, true);
}

// 6. Guardar con nombre único
$extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
$nombre = 'img_' . time() . '_' . uniqid() . '.' . $extension;
$ruta_completa = $directorio . $nombre;

if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {
    // 7. Devolver ruta en JSON
    echo json_encode([
        'success' => true,
        'url' => $ruta_completa
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar']);
}
```

**Uso en Quill:**
```javascript
// Handler personalizado para imágenes
quill.getModule('toolbar').addHandler('image', function() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();
    
    input.onchange = async function() {
        const file = input.files[0];
        const formData = new FormData();
        formData.append('imagen', file);
        
        // Subir imagen
        const response = await fetch('subir_imagen_contenido.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Insertar imagen en el editor
            const range = quill.getSelection();
            quill.insertEmbed(range.index, 'image', data.url);
        }
    };
});
```

---

## 👨‍💼 PHP - CARPETA `admins/`

### `config-admin.php` - Configuración de Admins

**Funciones Principales:**

#### 1. `requerirAdmin()`
```php
function requerirAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login-admin.php');
        exit();
    }
}
```

#### 2. `requerirSuperAdmin()`
```php
function requerirSuperAdmin() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['admin_nivel'] !== 'superadmin') {
        header('Location: index-admin.php');
        exit();
    }
}
```

#### 3. `obtenerEstadisticasAdmin()`
```php
function obtenerEstadisticasAdmin($conn) {
    $stats = [];
    
    // Total usuarios
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $stats['total_usuarios'] = $result->fetch_assoc()['total'];
    
    // Publicadores pendientes
    $result = $conn->query("SELECT COUNT(*) as total FROM publicadores WHERE estado = 'pendiente'");
    $stats['publicadores_pendientes'] = $result->fetch_assoc()['total'];
    
    // Publicaciones en revisión
    $result = $conn->query("SELECT COUNT(*) as total FROM publicaciones WHERE estado = 'revision'");
    $stats['publicaciones_revision'] = $result->fetch_assoc()['total'];
    
    return $stats;
}
```

### `gestionar_publicadores.php` - Gestión de Publicadores

**Acciones Principales:**

#### 1. Aprobar Publicador
```php
if (isset($_POST['aprobar'])) {
    $id = intval($_POST['publicador_id']);
    
    // Actualizar estado
    $update = $conn->prepare("UPDATE publicadores SET estado = 'activo', fecha_aprobacion = NOW() WHERE id = ?");
    $update->bind_param("i", $id);
    $update->execute();
    
    // Enviar email de aprobación
    enviarCorreoAprobacion($id, $conn);
    
    $_SESSION['mensaje'] = "Publicador aprobado exitosamente";
    header('Location: gestionar_publicadores.php');
}
```

#### 2. Rechazar Publicador
```php
if (isset($_POST['rechazar'])) {
    $id = intval($_POST['publicador_id']);
    $motivo = $_POST['motivo_rechazo'];
    
    // Actualizar estado
    $update = $conn->prepare("UPDATE publicadores SET estado = 'rechazado', motivo_rechazo = ? WHERE id = ?");
    $update->bind_param("si", $motivo, $id);
    $update->execute();
    
    // Enviar email de rechazo
    enviarCorreoRechazo($id, $motivo, $conn);
    
    $_SESSION['mensaje'] = "Publicador rechazado";
    header('Location: gestionar_publicadores.php');
}
```

#### 3. Suspender Publicador
```php
if (isset($_POST['suspender'])) {
    $id = intval($_POST['publicador_id']);
    
    $update = $conn->prepare("UPDATE publicadores SET estado = 'suspendido' WHERE id = ?");
    $update->bind_param("i", $id);
    $update->execute();
    
    $_SESSION['mensaje'] = "Publicador suspendido";
}
```

### `enviar_correo_publicador.php` - Envío de Correos

#### Correo de Aprobación
```php
function enviarCorreoAprobacion($publicador_id, $conn) {
    // Obtener datos del publicador
    $query = $conn->prepare("SELECT * FROM publicadores WHERE id = ?");
    $query->bind_param("i", $publicador_id);
    $query->execute();
    $publicador = $query->get_result()->fetch_assoc();
    
    $mail = new PHPMailer(true);
    
    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tu_email@gmail.com';
        $mail->Password = 'tu_contraseña_app';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Configuración del correo
        $mail->setFrom('noreply@labexplorer.com', 'Lab Explorer');
        $mail->addAddress($publicador['email'], $publicador['nombre']);
        $mail->CharSet = 'UTF-8';
        
        $mail->isHTML(true);
        $mail->Subject = '¡Tu cuenta ha sido aprobada!';
        $mail->Body = "
            <h2>¡Felicidades {$publicador['nombre']}!</h2>
            <p>Tu cuenta de publicador ha sido aprobada.</p>
            <p>Ya puedes iniciar sesión y comenzar a crear publicaciones.</p>
            <a href='http://localhost/Lab/forms/publicadores/inicio-sesion-publicadores.php'>Iniciar Sesión</a>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}
```

### `gestionar-publicaciones.php` - Gestión de Publicaciones

**Funcionalidades:**

#### 1. Cambiar Estado
```php
if (isset($_POST['cambiar_estado'])) {
    $publicacion_id = intval($_POST['publicacion_id']);
    $nuevo_estado = $_POST['nuevo_estado'];
    
    $update = $conn->prepare("UPDATE publicaciones SET estado = ? WHERE id = ?");
    $update->bind_param("si", $nuevo_estado, $publicacion_id);
    $update->execute();
    
    // Si es rechazada, pedir motivo
    if ($nuevo_estado == 'rechazada') {
        $_SESSION['pedir_motivo_id'] = $publicacion_id;
    }
}
```

#### 2. Guardar Motivo de Rechazo
```php
if (isset($_POST['guardar_motivo'])) {
    $publicacion_id = intval($_POST['publicacion_id']);
    $mensaje = $_POST['mensaje_rechazo'];
    
    $update = $conn->prepare("UPDATE publicaciones SET mensaje_rechazo = ? WHERE id = ?");
    $update->bind_param("si", $mensaje, $publicacion_id);
    $update->execute();
}
```

#### 3. Filtros JavaScript
```javascript
// Filtrar por estado
function filtrarPorEstado(estado) {
    const filas = document.querySelectorAll('.fila-publicacion');
    filas.forEach(fila => {
        if (estado === '' || fila.dataset.estado === estado) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

// Buscar por título
function filtrarPublicaciones(termino) {
    const filas = document.querySelectorAll('.fila-publicacion');
    const busqueda = termino.toLowerCase();
    
    filas.forEach(fila => {
        const texto = fila.dataset.titulo;
        if (texto.includes(busqueda)) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}
```

---

## 🏷️ PHP - CARPETA `categorias/`

### `categoria.php` - Clase Categoria (POO)

**Clase Completa:**
```php
class Categoria {
    // Propiedades privadas
    private $conn;
    private $table_name = "categorias";
    
    // Propiedades públicas
    public $id;
    public $nombre;
    public $slug;
    public $descripcion;
    public $color;
    public $icono;
    public $estado;
    public $fecha_creacion;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Método privado para crear slug
    private function crearSlug($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }
    
    // Método público para crear categoría
    public function crear() {
        $query = "INSERT INTO {$this->table_name} 
                  SET nombre=:nombre, slug=:slug, descripcion=:descripcion, 
                      color=:color, icono=:icono, estado=:estado";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->slug = $this->crearSlug($this->nombre);
        
        // Vincular
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":slug", $this->slug);
        // ... más parámetros
        
        return $stmt->execute();
    }
    
    // Leer todas
    public function leer() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Leer una
    public function leerUna() {
        $query = "SELECT * FROM {$this->table_name} WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->nombre = $row['nombre'];
            $this->slug = $row['slug'];
            // ... más propiedades
            return true;
        }
        return false;
    }
    
    // Actualizar
    public function actualizar() {
        $query = "UPDATE {$this->table_name} 
                  SET nombre=:nombre, slug=:slug, descripcion=:descripcion 
                  WHERE id=:id";
        // ... similar a crear
        return $stmt->execute();
    }
    
    // Eliminar
    public function eliminar() {
        $query = "DELETE FROM {$this->table_name} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
```

**Uso de la Clase:**
```php
// 1. Crear instancia
$database = new Database();
$db = $database->getConnection();
$categoria = new Categoria($db);

// 2. Crear nueva categoría
$categoria->nombre = "Hematología";
$categoria->descripcion = "Estudio de la sangre";
$categoria->color = "#FF5733";
$categoria->icono = "fa-flask";
$categoria->estado = "activo";
$categoria->crear();

// 3. Leer todas
$stmt = $categoria->leer();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['nombre'];
}

// 4. Leer una
$categoria->id = 5;
if ($categoria->leerUna()) {
    echo $categoria->nombre;
}

// 5. Actualizar
$categoria->id = 5;
$categoria->nombre = "Hematología Clínica";
$categoria->actualizar();

// 6. Eliminar
$categoria->id = 5;
$categoria->eliminar();
```

### `config-categorias.php` - Configuración PDO

```php
class Database {
    private $host = "localhost";
    private $db_name = "lab_exp_db";
    private $username = "root";
    private $password = "";
    public $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
```

**Diferencia con MySQLi:**
- PDO usa objetos y excepciones
- Sintaxis diferente para prepare/bind
- Más portable (funciona con otras BD)

---

## 🎨 CSS PERSONALIZADO

### Variables CSS (Custom Properties)
```css
:root {
    --primary: #7390A0;
    --primary-light: #8fa9b8;
    --primary-dark: #5a7080;
    --secondary: #6c757d;
    --accent: #f75815;
    --text: #212529;
    --background: #f8f9fa;
    --white: #ffffff;
    --border: #e9ecef;
    --shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* Uso */
.boton {
    background: var(--primary);
    color: var(--white);
    box-shadow: var(--shadow);
}
```

### Gradientes
```css
.hero-section {
    background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
}
```

### Transiciones y Animaciones
```css
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
```

---

## 🔧 JAVASCRIPT AVANZADO

### Fetch API (AJAX Moderno)
```javascript
// Subir imagen
async function subirImagen(file) {
    const formData = new FormData();
    formData.append('imagen', file);
    
    try {
        const response = await fetch('subir_imagen_contenido.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            return data.url;
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al subir imagen');
    }
}
```

### LocalStorage
```javascript
// Guardar borrador
function guardarBorrador() {
    const datos = {
        titulo: document.getElementById('titulo').value,
        contenido: quill.root.innerHTML,
        categoria: document.getElementById('categoria').value
    };
    
    localStorage.setItem('borrador_publicacion', JSON.stringify(datos));
}

// Cargar borrador
function cargarBorrador() {
    const borrador = localStorage.getItem('borrador_publicacion');
    if (borrador) {
        const datos = JSON.parse(borrador);
        document.getElementById('titulo').value = datos.titulo;
        quill.root.innerHTML = datos.contenido;
        document.getElementById('categoria').value = datos.categoria;
    }
}
```

### Event Delegation
```javascript
// En vez de agregar listener a cada botón
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-eliminar')) {
        const id = e.target.dataset.id;
        eliminarPublicacion(id);
    }
});
```

---

## 📊 SQL - CONSULTAS IMPORTANTES

### LEFT JOIN vs INNER JOIN
```sql
-- LEFT JOIN: Mantiene todas las filas de la tabla izquierda
SELECT p.*, c.nombre as categoria_nombre
FROM publicaciones p
LEFT JOIN categorias c ON p.categoria_id = c.id;
-- Resultado: Todas las publicaciones, incluso sin categoría

-- INNER JOIN: Solo filas con coincidencia en ambas tablas
SELECT p.*, c.nombre as categoria_nombre
FROM publicaciones p
INNER JOIN categorias c ON p.categoria_id = c.id;
-- Resultado: Solo publicaciones CON categoría
```

### GROUP BY y COUNT
```sql
-- Contar publicaciones por categoría
SELECT c.nombre, COUNT(p.id) as total_publicaciones
FROM categorias c
LEFT JOIN publicaciones p ON c.id = p.categoria_id
GROUP BY c.id, c.nombre
ORDER BY total_publicaciones DESC;
```

### CASE (Condicionales en SQL)
```sql
SELECT 
    titulo,
    CASE 
        WHEN estado = 'publicado' THEN 'Visible'
        WHEN estado = 'revision' THEN 'Pendiente'
        WHEN estado = 'rechazada' THEN 'Rechazado'
        ELSE 'Borrador'
    END as estado_texto
FROM publicaciones;
```

---

## 🔐 SEGURIDAD - MEJORES PRÁCTICAS

### 1. Prevenir SQL Injection
```php
// ❌ MAL (vulnerable)
$query = "SELECT * FROM usuarios WHERE email = '{$_POST['email']}'";

// ✅ BIEN (seguro)
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $_POST['email']);
```

### 2. Prevenir XSS
```php
// ❌ MAL
echo $_POST['nombre'];

// ✅ BIEN
echo htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
```

### 3. Validar Archivos
```php
// Validar tipo MIME real
$tipo_real = mime_content_type($_FILES['archivo']['tmp_name']);
$tipos_permitidos = ['image/jpeg', 'image/png'];

if (!in_array($tipo_real, $tipos_permitidos)) {
    die("Tipo no permitido");
}
```

### 4. CSRF Protection
```php
// Generar token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// En formulario
echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';

// Validar
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Token inválido");
}
```

---

## 📝 RESUMEN DE TECNOLOGÍAS

| Tecnología | Uso en el Proyecto |
|------------|-------------------|
| **PHP 7.4+** | Backend, lógica de negocio |
| **MySQL** | Base de datos |
| **MySQLi** | Conexión BD (mayoría archivos) |
| **PDO** | Conexión BD (categorías, recuperación) |
| **Bootstrap 5** | Framework CSS |
| **Bootstrap Icons** | Iconos |
| **JavaScript ES6** | Interactividad frontend |
| **Quill Editor** | Editor de texto enriquecido |
| **PHPMailer** | Envío de correos |
| **AOS** | Animaciones al scroll |
| **Font Awesome** | Iconos adicionales |

---

**¡Guía Técnica 2.0 Completa! 🎉**
Organizada por tecnologías y carpetas del proyecto Lab Explorer.
