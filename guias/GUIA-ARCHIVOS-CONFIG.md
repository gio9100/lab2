# 🔧 Guía de Archivos de Configuración - Lab-Explorer

## 📋 Índice

1. [Introducción](#introducción)
2. [config-admin.php](#config-adminphp)
3. [config-publicadores.php](#config-publicadoresphp)
4. [config-usuarios.php](#config-usuariosph p)
5. [config-categorias.php](#config-categoriasphp)
6. [Comparación de Archivos Config](#comparación-de-archivos-config)
7. [Flujo de Uso](#flujo-de-uso)

---

## 🎯 Introducción

En Lab-Explorer existen **4 archivos de configuración principales** (`config-*.php`). Cada uno tiene un propósito específico y es usado por diferentes partes del sistema.

### ¿Por qué múltiples archivos config?

- **Separación de responsabilidades**: Cada módulo tiene su propia configuración
- **Seguridad**: Funciones específicas para cada tipo de usuario
- **Mantenibilidad**: Más fácil encontrar y modificar código
- **Organización**: Código limpio y estructurado

### Los 4 Archivos de Configuración

1. **`config-admin.php`** - Para administradores del sistema
2. **`config-publicadores.php`** - Para publicadores de contenido
3. **`config-usuarios.php`** - Para usuarios normales del sitio
4. **`config-categorias.php`** - Para el sistema de categorías

---

## 📄 config-admin.php

**Ubicación**: `forms/admins/config-admin.php`

### ¿Para qué sirve?

Es el archivo de configuración **central del sistema de administración**. Contiene:
- Conexión a la base de datos
- Funciones para administradores
- Funciones para gestionar usuarios
- Funciones para gestionar publicadores
- Funciones de seguridad

### Configuración de Base de Datos

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
```

**Características:**
- Usa **mysqli** (MySQL Improved)
- Charset UTF-8 para acentos y emojis
- Zona horaria: America/Mexico_City
- Manejo de errores activado

### Funciones Incluidas

#### 🔐 Autenticación de Administradores

| Función | Propósito | Parámetros | Retorna |
|---------|-----------|------------|---------|
| `loginAdmin()` | Verificar credenciales de admin | email, password, conn | Array con datos del admin o false |
| `registrarAdmin()` | Crear nuevo administrador | datos[], conn | true/false |
| `adminExiste()` | Verificar si email ya existe | email, conn | true/false |
| `esAdministrador()` | Verificar sesión activa | - | true/false |
| `requerirAdmin()` | Proteger páginas | - | Redirige si no hay sesión |

**Ejemplo de uso:**
```php
// En login-admin.php
$admin = loginAdmin($email, $password, $conn);
if ($admin) {
    $_SESSION['admin_id'] = $admin['id'];
    header('Location: index-admin.php');
}
```

#### 📊 Estadísticas del Sistema

| Función | Propósito | Retorna |
|---------|-----------|---------|
| `obtenerEstadisticasAdmin()` | Obtener conteos generales | Array con 5 estadísticas |

**Estadísticas que devuelve:**
- `total_usuarios`: Usuarios normales registrados
- `total_publicadores`: Publicadores totales
- `publicadores_pendientes`: Publicadores esperando aprobación
- `total_publicaciones`: Publicaciones totales
- `total_admins`: Administradores activos

**Ejemplo de uso:**
```php
// En index-admin.php
$stats = obtenerEstadisticasAdmin($conn);
echo "Usuarios: " . $stats['total_usuarios'];
```

#### 👥 Gestión de Publicadores

| Función | Propósito | Parámetros |
|---------|-----------|------------|
| `obtenerPublicadoresPendientes()` | Lista de pendientes | conn |
| `obtenerTodosPublicadores()` | Todos los publicadores | conn |
| `aprobarPublicador()` | Aprobar publicador | id, conn |
| `rechazarPublicador()` | Rechazar con motivo | id, motivo, conn |
| `suspenderPublicador()` | Suspender con motivo | id, motivo, conn |
| `activarPublicador()` | Reactivar suspendido | id, conn |

**Ejemplo de uso:**
```php
// En gestionar_publicadores.php
if (isset($_POST['aprobar_publicador'])) {
    $publicador_id = intval($_POST['publicador_id']);
    aprobarPublicador($publicador_id, $conn);
}
```

#### 👤 Gestión de Usuarios Normales

| Función | Propósito | Parámetros |
|---------|-----------|------------|
| `obtenerUsuariosNormales()` | Lista de usuarios | conn |
| `obtenerUsuarioPorId()` | Usuario específico | id, conn |
| `crearUsuario()` | Crear nuevo usuario | datos[], conn |
| `editarUsuario()` | Actualizar usuario | id, datos[], conn |
| `eliminarUsuario()` | Eliminar usuario | id, conn |
| `usuarioExiste()` | Verificar email duplicado | correo, conn, excluir_id |

**Ejemplo de uso:**
```php
// En usuarios.php
if (isset($_POST['crear_usuario'])) {
    $datos = [
        'nombre' => $_POST['nombre'],
        'correo' => $_POST['correo'],
        'password' => $_POST['password']
    ];
    crearUsuario($datos, $conn);
}
```

### Archivos que usan config-admin.php

1. **`login-admin.php`** - Inicio de sesión de administradores
2. **`register-admin.php`** - Registro de nuevos administradores
3. **`index-admin.php`** - Dashboard principal
4. **`usuarios.php`** - Gestión de usuarios
5. **`gestionar_publicadores.php`** - Gestión de publicadores
6. **`gestionar-publicaciones.php`** - Gestión de publicaciones
7. **`historial-publicaciones.php`** - Historial de cambios
8. **`admins.php`** - Gestión de administradores

**Total de archivos**: 8+ archivos del panel de administración

---

## 📄 config-publicadores.php

**Ubicación**: `forms/publicadores/config-publicadores.php`

### ¿Para qué sirve?

Es el archivo de configuración **para el sistema de publicadores**. Contiene:
- Conexión a la base de datos (independiente)
- Funciones para autenticación de publicadores
- Funciones para gestionar publicaciones
- Funciones para estadísticas de publicadores

### Configuración de Base de Datos

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");
```

**Características:**
- Usa **mysqli** (igual que config-admin.php)
- Misma base de datos pero conexión independiente
- Configuración de límites de subida de archivos (10MB)

### Funciones Incluidas

#### 🔐 Autenticación de Publicadores

| Función | Propósito | Parámetros | Retorna |
|---------|-----------|------------|---------|
| `loginPublicador()` | Verificar credenciales | email, password, conn | Array con datos o false |
| `registrarPublicador()` | Crear nuevo publicador | datos[], conn | true/false |
| `emailExiste()` | Verificar email duplicado | email, conn | true/false |
| `estaLogueado()` | Verificar sesión | - | true/false |
| `requerirLogin()` | Proteger páginas | - | Redirige si no hay sesión |

**Diferencia con config-admin.php:**
- Verifica que el publicador esté **activo** (aprobado por admin)
- Actualiza `ultimo_acceso` al hacer login
- Guarda publicadores con estado 'pendiente' al registrar

**Ejemplo de uso:**
```php
// En inicio-sesion-publicadores.php
$publicador = loginPublicador($email, $password, $conn);
if ($publicador) {
    $_SESSION['publicador_id'] = $publicador['id'];
    $_SESSION['publicador_nombre'] = $publicador['nombre'];
    header('Location: index-publicadores.php');
}
```

#### 📝 Gestión de Publicaciones

| Función | Propósito | Parámetros |
|---------|-----------|------------|
| `obtenerPublicacionesPublicador()` | Publicaciones de un publicador | id, conn |
| `crearPublicacion()` | Crear nueva publicación | datos[], conn |
| `crearSlug()` | Crear URL amigable | texto |

**Características de crearPublicacion():**
- Crea slug único automáticamente
- Convierte tags a JSON
- Estado por defecto: 'borrador'
- Incluye JOIN con categorías

**Ejemplo de uso:**
```php
// En crear_nueva_publicacion.php
$datos = [
    'titulo' => $_POST['titulo'],
    'contenido' => $_POST['contenido'],
    'resumen' => $_POST['resumen'],
    'publicador_id' => $_SESSION['publicador_id'],
    'categoria_id' => $_POST['categoria_id'],
    'estado' => 'pendiente',
    'tipo' => 'articulo',
    'tags' => $_POST['tags']
];
crearPublicacion($datos, $conn);
```

#### 📊 Estadísticas y Utilidades

| Función | Propósito | Parámetros |
|---------|-----------|------------|
| `obtenerEstadisticasPublicador()` | Estadísticas del publicador | id, conn |
| `obtenerCategorias()` | Lista de categorías activas | conn |
| `obtenerTodosPublicadores()` | Todos los publicadores | conn |

**Estadísticas que devuelve:**
- `total_publicaciones`: Total de publicaciones
- `publicadas`: Publicaciones publicadas
- `borradores`: Borradores guardados
- `en_revision`: En revisión por admins

**Ejemplo de uso:**
```php
// En index-publicadores.php
$stats = obtenerEstadisticasPublicador($_SESSION['publicador_id'], $conn);
echo "Tienes " . $stats['total_publicaciones'] . " publicaciones";
```

### Archivos que usan config-publicadores.php

1. **`inicio-sesion-publicadores.php`** - Login de publicadores
2. **`registro-publicadores.php`** - Registro de publicadores
3. **`index-publicadores.php`** - Dashboard de publicador
4. **`perfil.php`** - Perfil del publicador
5. **`mis-publicaciones.php`** - Lista de publicaciones
6. **`crear_nueva_publicacion.php`** - Crear publicación
7. **`editar_publicacion.php`** - Editar publicación
8. **`guardar_publicacion.php`** - Guardar cambios
9. **`actualizar_publicacion.php`** - Actualizar publicación
10. **`subir_imagen_contenido.php`** - Subir imágenes

**Total de archivos**: 10+ archivos del panel de publicadores

---

## 📄 config-usuarios.php

**Ubicación**: `forms/config-usuarios.php`

### ¿Para qué sirve?

Es el archivo de configuración **para usuarios normales del sitio público**. Contiene:
- Conexión a la base de datos
- Funciones para autenticación de usuarios
- Gestión de sesiones
- Verificación de roles (si el usuario también es publicador o admin)
- Funciones utilitarias

### Configuración de Base de Datos

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

$conexion = new mysqli($servername, $username, $password, $dbname);
$conexion->set_charset("utf8mb4");
```

**Características:**
- Usa **mysqli** (igual que config-admin.php y config-publicadores.php)
- Variable de conexión se llama `$conexion` (no `$conn`)
- Inicia sesión automáticamente con `session_start()`
- Zona horaria: America/Mexico_City

### Funciones Incluidas

#### 🔐 Autenticación de Usuarios

| Función | Propósito | Parámetros | Retorna |
|---------|-----------|------------|---------|
| `loginUsuario()` | Verificar credenciales | correo, password, conexion | Array con datos o false |
| `registrarUsuario()` | Crear nuevo usuario | datos[], conexion | true/false |
| `correoExiste()` | Verificar correo duplicado | correo, conexion | true/false |
| `estaLogueado()` | Verificar sesión activa | - | true/false |
| `requerirLogin()` | Proteger páginas | - | Redirige si no hay sesión |

**Diferencia con otros configs:**
- Usa **correo** en lugar de **email** (tabla usuarios usa 'correo')
- No verifica estado 'activo' (usuarios normales no requieren aprobación)
- Actualiza `ultimo_acceso` al hacer login

**Ejemplo de uso:**
```php
// En inicio-sesion.php
$usuario = loginUsuario($correo, $password, $conexion);
if ($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_correo'] = $usuario['correo'];
    header('Location: ../index.php');
}
```

#### 👤 Gestión de Perfil

| Función | Propósito | Parámetros |
|---------|-----------|------------|
| `obtenerUsuarioPorId()` | Obtener datos de usuario | id, conexion |
| `actualizarPerfil()` | Actualizar datos del perfil | id, datos[], conexion |

**Características de actualizarPerfil():**
- Contraseña opcional al editar
- Solo actualiza si se proporciona nueva contraseña
- Hashea la contraseña con `password_hash()`

**Ejemplo de uso:**
```php
// En perfil.php
$datos = [
    'nombre' => $_POST['nombre'],
    'correo' => $_POST['correo']
];

// Solo agregar password si se proporcionó
if (!empty($_POST['password'])) {
    $datos['password'] = $_POST['password'];
}

actualizarPerfil($_SESSION['usuario_id'], $datos, $conexion);
```

#### 🕵️ Verificación de Roles

| Función | Propósito | Parámetros |
|---------|-----------|------------|
| `verificarRoles()` | Verificar si tiene roles especiales | correo, conexion |

**¿Qué hace esta función?**
- Verifica si el usuario también es **publicador activo**
- Verifica si el usuario también es **administrador activo**
- Retorna array con `es_publicador` y `es_admin`

**Ejemplo de uso:**
```php
// En usuario.php (gestor de sesión)
$roles = verificarRoles($usuario['correo'], $conexion);
$_SESSION['es_publicador'] = $roles['es_publicador'];
$_SESSION['es_admin'] = $roles['es_admin'];

// Ahora en index.php puedes mostrar enlaces especiales:
if ($_SESSION['es_publicador']) {
    echo '<a href="forms/publicadores/index-publicadores.php">Panel Publicador</a>';
}
if ($_SESSION['es_admin']) {
    echo '<a href="forms/admins/index-admin.php">Panel Admin</a>';
}
```

### Archivos que usan config-usuarios.php

1. **`inicio-sesion.php`** - Login de usuarios normales
2. **`registro.php`** - Registro de nuevos usuarios
3. **`perfil.php`** - Perfil del usuario
4. **`usuario.php`** - Gestor de sesión (actualmente usa conexion.php, debería migrar)
5. **`editar-perfil.php`** - Editar datos del perfil
6. **`cerrar-sesion.php`** - Logout de usuarios

**Total de archivos**: 6+ archivos del sitio público

### Diferencias con usuario.php actual

**usuario.php actual:**
- Usa `require_once "conexion.php"`
- Tiene lógica de verificación de roles embebida
- Solo tiene función `correoExiste()`

**config-usuarios.php nuevo:**
- Centraliza todas las funciones de usuarios
- Separa lógica de verificación en función `verificarRoles()`
- Incluye funciones completas de autenticación y gestión

**Migración recomendada:**
```php
// ANTES (usuario.php):
require_once "conexion.php";
// ... código embebido ...

// DESPUÉS (usuario.php):
require_once "config-usuarios.php";
// Usar las funciones del config
if (estaLogueado()) {
    $usuario = obtenerUsuarioPorId($_SESSION['usuario_id'], $conexion);
    $roles = verificarRoles($usuario['correo'], $conexion);
}
```

> [!NOTE]
> **Estado actual del proyecto**: `config-usuarios.php` existe como plantilla/referencia futura, pero **NO se está usando actualmente**. El sistema funciona con `usuario.php` + `conexion.php` y no requiere migración inmediata.

---

## 📄 usuario.php (Gestor de Sesión)

**Ubicación**: `forms/usuario.php`

### ⚠️ Importante: NO es un archivo de configuración

`usuario.php` **NO es un archivo config**, es un **gestor de sesión** que se ejecuta automáticamente. Lo incluimos aquí porque trabaja estrechamente con el sistema de usuarios y es importante entender la diferencia.

### ¿Para qué sirve?

Es el **"guardia de seguridad"** del sitio público. Se incluye en páginas que necesitan saber si hay un usuario logueado.

**Funciones principales:**
1. ✅ Verifica si hay una sesión activa
2. ✅ Obtiene los datos del usuario de la BD
3. ✅ Actualiza variables de sesión
4. ✅ Verifica si el usuario también es publicador o admin
5. ✅ Destruye sesión si el usuario ya no existe en BD

### ¿Cómo funciona?

**Se ejecuta automáticamente al incluirlo:**
```php
// En index.php, perfil.php, etc.
require_once "forms/usuario.php";

// Después de esta línea, YA tienes disponibles:
// - $usuario_logueado (true/false)
// - $usuario (array con datos o null)
// - $_SESSION['es_publicador'] (true/false)
// - $_SESSION['es_admin'] (true/false)
```

### Flujo de Ejecución

```
1. Se incluye usuario.php
   ↓
2. Inicia sesión si no está iniciada
   ↓
3. ¿Existe $_SESSION['usuario_id']?
   ↓ Sí                    ↓ No
4. Buscar en BD      $usuario_logueado = false
   ↓                  $usuario = null
5. ¿Encontrado?
   ↓ Sí              ↓ No
6. $usuario_logueado = true    Destruir sesión
   $usuario = [datos]          (usuario eliminado)
   ↓
7. Verificar roles
   - ¿Es publicador activo?
   - ¿Es admin activo?
   ↓
8. Actualizar $_SESSION
   - usuario_nombre
   - usuario_correo
   - usuario_imagen
   - es_publicador
   - es_admin
```

### Variables que proporciona

Después de incluir `usuario.php`, tienes acceso a:

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$usuario_logueado` | boolean | true si hay usuario logueado |
| `$usuario` | array/null | Datos del usuario (id, nombre, correo, imagen) |
| `$_SESSION['usuario_id']` | int | ID del usuario |
| `$_SESSION['usuario_nombre']` | string | Nombre del usuario |
| `$_SESSION['usuario_correo']` | string | Correo del usuario |
| `$_SESSION['usuario_imagen']` | string | Ruta de la imagen de perfil |
| `$_SESSION['es_publicador']` | boolean | true si también es publicador activo |
| `$_SESSION['es_admin']` | boolean | true si también es administrador activo |

### Función incluida

**`correoExiste($correo, $conexion)`**
- Verifica si un correo ya está registrado
- Usada principalmente en `registro.php`
- Previene duplicados

### Ejemplo de uso en páginas

**En index.php (página pública):**
```php
<?php require_once "forms/usuario.php"; ?>

<nav>
    <?php if ($usuario_logueado): ?>
        <!-- Usuario logueado -->
        <span>Hola, <?= htmlspecialchars($usuario['nombre']) ?></span>
        
        <?php if ($_SESSION['es_publicador']): ?>
            <a href="forms/publicadores/index-publicadores.php">Panel Publicador</a>
        <?php endif; ?>
        
        <?php if ($_SESSION['es_admin']): ?>
            <a href="forms/admins/index-admin.php">Panel Admin</a>
        <?php endif; ?>
        
        <a href="forms/cerrar-sesion.php">Cerrar Sesión</a>
    <?php else: ?>
        <!-- Usuario NO logueado -->
        <a href="forms/inicio-sesion.php">Iniciar Sesión</a>
        <a href="forms/registro.php">Registrarse</a>
    <?php endif; ?>
</nav>
```

**En perfil.php (página protegida):**
```php
<?php 
require_once "usuario.php";

// Si no está logueado, redirigir
if (!$usuario_logueado) {
    header('Location: inicio-sesion.php');
    exit();
}
?>

<h1>Mi Perfil</h1>
<p>Nombre: <?= htmlspecialchars($usuario['nombre']) ?></p>
<p>Correo: <?= htmlspecialchars($usuario['correo']) ?></p>
<img src="<?= htmlspecialchars($usuario['imagen']) ?>" alt="Foto de perfil">
```

### Archivos que usan usuario.php

1. **`index.php`** - Página principal (muestra saludo si está logueado)
2. **`perfil.php`** - Perfil del usuario (requiere login)
3. **`ver-publicacion.php`** - Ver publicación (muestra opciones según usuario)
4. Cualquier página que necesite saber si hay usuario logueado

**Total de archivos**: 3+ archivos del sitio público

### Diferencia con config-usuarios.php

| Característica | usuario.php | config-usuarios.php |
|----------------|-------------|---------------------|
| **Tipo** | Gestor de sesión | Archivo de configuración |
| **Se ejecuta automáticamente** | ✅ Sí | ❌ No |
| **Proporciona variables** | ✅ Sí ($usuario_logueado, $usuario) | ❌ No |
| **Tiene funciones** | 1 función (correoExiste) | 8+ funciones |
| **Conexión BD** | Usa conexion.php | Tiene su propia conexión |
| **Usado en** | Páginas públicas | Archivos de autenticación |
| **Propósito** | Verificar sesión activa | Proveer funciones reutilizables |
| **Estado actual** | ✅ En uso activo | ⚠️ Plantilla/referencia |

### Relación con config-usuarios.php

**Actualmente:**
- `usuario.php` usa `conexion.php` directamente
- Tiene su propia lógica embebida
- Funciona independientemente

**Potencialmente (migración futura):**
- `usuario.php` podría usar funciones de `config-usuarios.php`
- Código más limpio y organizado
- Consistencia con otros módulos

**Ejemplo de migración:**
```php
// ANTES (actual):
require_once "conexion.php";
if (isset($_SESSION['usuario_id'])) {
    $stmt = $conexion->prepare("SELECT id, nombre, correo, imagen FROM usuarios WHERE id = ?");
    // ... más código ...
}

// DESPUÉS (con config-usuarios.php):
require_once "config-usuarios.php";
if (estaLogueado()) {
    $usuario = obtenerUsuarioPorId($_SESSION['usuario_id'], $conexion);
    $roles = verificarRoles($usuario['correo'], $conexion);
}
```

> [!IMPORTANT]
> **No es necesario migrar ahora**. El sistema funciona perfectamente con `usuario.php` en su estado actual. La migración a `config-usuarios.php` es opcional y solo se recomienda si vas a refactorizar el proyecto completo.

---

## 📄 config-categorias.php

**Ubicación**: `forms/admins/categorias/config-categorias.php`

### ¿Para qué sirve?

Es el archivo de configuración **para el sistema de categorías**. Contiene:
- Conexión a la base de datos usando **PDO**
- Clase Database para conexión orientada a objetos

### Configuración de Base de Datos

```php
class Database {
    private $host = "localhost";
    private $db_name = "lab_exp_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                              $this->username, $this->password);
        $this->conn->exec("set names utf8");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $this->conn;
    }
}
```

**Características:**
- Usa **PDO** (PHP Data Objects) en lugar de mysqli
- Patrón de diseño: Clase con método getConnection()
- Manejo de excepciones con try-catch
- Charset UTF-8

### ¿Por qué PDO en lugar de mysqli?

| Característica | PDO | mysqli |
|----------------|-----|--------|
| Compatibilidad | Múltiples bases de datos | Solo MySQL |
| Sintaxis | Orientada a objetos | Procedural u OO |
| Excepciones | Nativas | Requiere configuración |
| Uso en Lab-Explorer | Sistema de categorías | Admins y publicadores |

**Ejemplo de uso:**
```php
// En crear_categoria.php
$database = new Database();
$conn = $database->getConnection();

$query = "INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)";
$stmt = $conn->prepare($query);
$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->execute();
```

### Archivos que usan config-categorias.php

1. **`crear_categoria.php`** - Crear nuevas categorías
2. **`editar_categoria.php`** - Editar categorías existentes
3. **`eliminar_categoria.php`** - Eliminar categorías
4. **`listar_categorias.php`** - Listar todas las categorías
5. **`categoria.php`** - Vista pública de categoría

**Total de archivos**: 5 archivos del sistema de categorías

---

## 🔄 Comparación de Archivos Config

### Tabla Comparativa

| Característica | config-admin.php | config-publicadores.php | config-usuarios.php | config-categorias.php |
|----------------|------------------|-------------------------|---------------------|----------------------|
| **Ubicación** | `forms/admins/` | `forms/publicadores/` | `forms/` | `forms/admins/categorias/` |
| **Tecnología BD** | mysqli | mysqli | mysqli | PDO |
| **Variable conexión** | `$conn` | `$conn` | `$conexion` | `$conn` (PDO) |
| **Usuarios** | Administradores | Publicadores | Usuarios normales | Ambos (indirectamente) |
| **Funciones** | 15+ funciones | 10+ funciones | 8+ funciones | 1 clase |
| **Propósito** | Gestión del sistema | Gestión de publicaciones | Sitio público | Gestión de categorías |
| **Autenticación** | Admins | Publicadores | Usuarios | No |
| **Requiere aprobación** | No | Sí (estado activo) | No | N/A |
| **Estadísticas** | Sí (sistema completo) | Sí (por publicador) | No | No |
| **CRUD Usuarios** | Sí | No | Sí (propio perfil) | No |
| **CRUD Publicaciones** | Parcial (moderación) | Sí (completo) | No | No |
| **Verificación de roles** | No | No | Sí | No |
| **Archivos que lo usan** | 8+ | 10+ | 6+ | 5 |

### Diferencias Clave

#### 1. Tecnología de Conexión

**config-admin.php, config-publicadores.php y config-usuarios.php (mysqli):**
```php
$conn = new mysqli($servername, $username, $password, $dbname);
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
```

**config-categorias.php (PDO):**
```php
$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$stmt = $conn->prepare($query);
$stmt->bindParam(':email', $email);
```

#### 2. Funciones de Autenticación

**config-admin.php:**
- Verifica admins en tabla `admins`
- Requiere estado 'activo'
- Guarda nivel (admin/superadmin)

**config-publicadores.php:**
- Verifica publicadores en tabla `publicadores`
- Requiere estado 'activo' (aprobado)
- Guarda especialidad e institución

**config-usuarios.php:**
- Verifica usuarios en tabla `usuarios`
- NO requiere estado (usuarios auto-aprobados)
- Verifica roles adicionales (publicador/admin)

#### 3. Alcance de Funciones

**config-admin.php** - Más amplio:
- Gestión de usuarios
- Gestión de publicadores
- Gestión de admins
- Estadísticas globales

**config-publicadores.php** - Específico:
- Solo publicadores
- Solo sus publicaciones
- Estadísticas personales

**config-usuarios.php** - Sitio público:
- Solo usuarios normales
- Gestión de perfil propio
- Verificación de roles múltiples

**config-categorias.php** - Minimalista:
- Solo conexión
- Sin funciones adicionales
- Patrón de clase

---

## 🔄 Flujo de Uso

### Flujo 1: Administrador Gestiona Usuarios

```
1. Admin hace login
   └── login-admin.php
       └── require_once "config-admin.php"
           └── loginAdmin($email, $password, $conn)

2. Admin ve dashboard
   └── index-admin.php
       └── require_once "config-admin.php"
           ├── obtenerEstadisticasAdmin($conn)
           ├── obtenerPublicadoresPendientes($conn)
           └── obtenerUsuariosNormales($conn)

3. Admin gestiona usuarios
   └── usuarios.php
       └── require_once "config-admin.php"
           ├── crearUsuario($datos, $conn)
           ├── editarUsuario($id, $datos, $conn)
           └── eliminarUsuario($id, $conn)
```

### Flujo 2: Publicador Crea Publicación

```
1. Publicador hace login
   └── inicio-sesion-publicadores.php
       └── require_once "config-publicadores.php"
           └── loginPublicador($email, $password, $conn)

2. Publicador ve su dashboard
   └── index-publicadores.php
       └── require_once "config-publicadores.php"
           ├── obtenerEstadisticasPublicador($id, $conn)
           └── obtenerPublicacionesPublicador($id, $conn)

3. Publicador crea publicación
   └── crear_nueva_publicacion.php
       └── require_once "config-publicadores.php"
           ├── obtenerCategorias($conn)
           └── crearPublicacion($datos, $conn)
```

### Flujo 3: Admin Gestiona Categorías

```
1. Admin accede a categorías
   └── listar_categorias.php
       └── require_once "config-categorias.php"
           └── new Database()
               └── getConnection()

2. Admin crea categoría
   └── crear_categoria.php
       └── require_once "config-categorias.php"
           └── INSERT con PDO

3. Admin edita categoría
   └── editar_categoria.php
       └── require_once "config-categorias.php"
           └── UPDATE con PDO
```

---

## 🔒 Seguridad en los Archivos Config

### Medidas Comunes en los 3 Archivos

#### 1. Prepared Statements

**config-admin.php y config-publicadores.php (mysqli):**
```php
$query = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
```

**config-categorias.php (PDO):**
```php
$query = "SELECT * FROM categorias WHERE nombre = :nombre";
$stmt = $conn->prepare($query);
$stmt->bindParam(':nombre', $nombre);
$stmt->execute();
```

#### 2. Hash de Contraseñas

**En ambos config-admin.php y config-publicadores.php:**
```php
// Al registrar
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Al verificar
if (password_verify($password_ingresada, $password_hash_bd)) {
    // Contraseña correcta
}
```

#### 3. Validación de Estado

**config-admin.php:**
```php
WHERE email = ? AND estado = 'activo'
```

**config-publicadores.php:**
```php
WHERE email = ? AND estado = 'activo'
```

Esto asegura que solo usuarios/publicadores aprobados puedan acceder.

---

## 📊 Resumen Visual

### Arquitectura de Configuración

```
Lab-Explorer
│
├── forms/admins/
│   ├── config-admin.php ─────────┐
│   │   ├── Conexión mysqli       │
│   │   ├── 15+ funciones         │
│   │   └── Gestión completa      │
│   │                              │
│   ├── login-admin.php ───────────┤
│   ├── index-admin.php ───────────┤
│   ├── usuarios.php ──────────────┤
│   ├── gestionar_publicadores.php─┤
│   └── ... (8+ archivos) ─────────┘
│
├── forms/publicadores/
│   ├── config-publicadores.php ──┐
│   │   ├── Conexión mysqli       │
│   │   ├── 10+ funciones         │
│   │   └── Gestión publicaciones │
│   │                              │
│   ├── inicio-sesion-publicadores.php ─┤
│   ├── index-publicadores.php ─────────┤
│   ├── crear_nueva_publicacion.php ────┤
│   └── ... (10+ archivos) ─────────────┘
│
├── forms/
│   ├── config-usuarios.php ──────┐
│   │   ├── Conexión mysqli       │
│   │   ├── 8+ funciones          │
│   │   └── Sitio público         │
│   │                              │
│   ├── inicio-sesion.php ─────────┤
│   ├── registro.php ──────────────┤
│   ├── perfil.php ────────────────┤
│   ├── usuario.php ───────────────┤
│   └── ... (6+ archivos) ─────────┘
│
└── forms/admins/categorias/
    ├── config-categorias.php ────┐
    │   ├── Conexión PDO          │
    │   ├── Clase Database        │
    │   └── Método getConnection()│
    │                              │
    ├── crear_categoria.php ──────┤
    ├── editar_categoria.php ─────┤
    └── ... (5 archivos) ─────────┘
```

---

## 💡 Buenas Prácticas

### 1. Siempre usar require_once

```php
// ✅ CORRECTO
require_once "config-admin.php";

// ❌ INCORRECTO
include "config-admin.php";  // Puede incluir múltiples veces
require "config-admin.php";   // Puede causar errores de redefinición
```

### 2. Verificar la conexión

```php
// Todos los archivos config verifican la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
```

### 3. Usar las funciones apropiadas

```php
// ✅ CORRECTO - Usar función del config apropiado
require_once "config-admin.php";
$admin = loginAdmin($email, $password, $conn);

// ❌ INCORRECTO - Mezclar configs
require_once "config-publicadores.php";
$admin = loginAdmin($email, $password, $conn);  // Esta función no existe aquí
```

### 4. Cerrar conexiones (opcional pero recomendado)

```php
// Al final del script
$conn->close();  // Para mysqli
$conn = null;    // Para PDO
```

---

## 🎯 Conclusión

### Cuándo usar cada archivo config

| Situación | Archivo a usar |
|-----------|----------------|
| Trabajando en panel de administración | `config-admin.php` |
| Trabajando en panel de publicadores | `config-publicadores.php` |
| Trabajando en sitio público (usuarios) | `config-usuarios.php` |
| Trabajando con categorías | `config-categorias.php` |
| Necesitas autenticar admin | `config-admin.php` |
| Necesitas autenticar publicador | `config-publicadores.php` |
| Necesitas autenticar usuario normal | `config-usuarios.php` |
| Necesitas gestionar usuarios (admin) | `config-admin.php` |
| Necesitas gestionar perfil propio | `config-usuarios.php` |
| Necesitas crear publicaciones | `config-publicadores.php` |
| Necesitas estadísticas globales | `config-admin.php` |
| Necesitas estadísticas de publicador | `config-publicadores.php` |
| Necesitas verificar roles múltiples | `config-usuarios.php` |

### Puntos Clave

✅ **config-admin.php** es el más completo (15+ funciones)  
✅ **config-publicadores.php** es específico para publicadores  
✅ **config-usuarios.php** es para el sitio público y verifica roles  
✅ **config-categorias.php** usa PDO en lugar de mysqli  
✅ Los 4 se conectan a la misma base de datos (`lab_exp_db`)  
✅ Cada uno tiene funciones específicas para su módulo  
✅ No se deben mezclar funciones entre archivos config  
✅ Todos implementan medidas de seguridad (prepared statements, hashing)  

---

**Fecha de creación**: 25 de noviembre de 2025  
**Sistema**: Lab-Explorer  
**Versión**: 2.0  
**Archivos documentados**: 4 archivos de configuración
