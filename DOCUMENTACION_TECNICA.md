# 📚 Documentación Técnica - Lab-Explora

> **Plataforma de Publicaciones Científicas para Laboratorio Clínico**  
> Sistema completo con autenticación multifactor, moderación por IA, y gestión de contenido científico

---

## 📋 Tabla de Contenidos

1. [Arquitectura General](#arquitectura-general)
2. [Base de Datos](#base-de-datos)
3. [Sistema de Autenticación](#sistema-de-autenticación)
4. [Autenticación de Dos Factores (2FA)](#autenticación-de-dos-factores-2fa)
5. [Gestión de Usuarios](#gestión-de-usuarios)
6. [Sistema de Publicaciones](#sistema-de-publicaciones)
7. [Moderación con Inteligencia Artificial](#moderación-con-inteligencia-artificial)
8. [Sistema de Interacciones](#sistema-de-interacciones)
9. [Credenciales Digitales PDF](#credenciales-digitales-pdf)
10. [Sistema de Reportes](#sistema-de-reportes)
11. [Formulario de Contacto Legal](#formulario-de-contacto-legal)
12. [Seguridad](#seguridad)
13. [PWA (Progressive Web App)](#pwa-progressive-web-app)

---

## 🏗️ Arquitectura General

### Stack Tecnológico

```
Frontend:
├── HTML5 + CSS3
├── JavaScript Vanilla
├── Bootstrap 5.3
├── Bootstrap Icons
└── Driver.js (onboarding)

Backend:
├── PHP 8.x
├── MySQL 8.0
└── PHPMailer 6.x

Servicios Externos:
├── Ollama AI (moderación local)
├── html2pdf.js (generación PDFs)
└── SMTP (envío de correos)
```

### Estructura del Proyecto

```
lab2/
├── index.php                          # Página principal de publicaciones
├── pagina-principal.php               # Dashboard principal
├── ver-publicacion.php                # Vista detallada de publicación
├── contacto.php                       # Formulario de contacto legal
├── terminos.php                       # Términos y condiciones
├── privacidad.php                     # Política de privacidad
│
├── forms/                             # Sistema de autenticación y funciones
│   ├── conexion.php                   # Conexión a MySQL
│   ├── usuario.php                    # Funciones de usuario
│   ├── inicio-sesion.php              # Login general
│   ├── register.php                   # Registro de usuarios
│   ├── perfil.php                     # Perfil de usuario
│   │
│   ├── 2fa_functions.php              # Funciones 2FA
│   ├── verify_2fa.php                 # Interfaz de verificación 2FA
│   ├── check_2fa.php                  # Validación de código 2FA
│   ├── toggle_2fa.php                 # Activar/desactivar 2FA
│   │
│   ├── EmailHelper.php                # Clase de envío de emails
│   ├── validaciones.php               # Validaciones de formularios
│   ├── funciones-interaccion.php      # Likes, comentarios, guardados
│   ├── procesar-interacciones.php     # Procesamiento de interacciones
│   ├── procesar-contacto.php          # Procesamiento form contacto
│   │
│   ├── publicadores/                  # Panel de publicadores
│   │   ├── index-publicadores.php     # Dashboard publicador
│   │   ├── login.php                  # Login con 2FA obligatorio
│   │   ├── registro-publicadores.php  # Registro de publicadores
│   │   ├── crear_nueva_publicacion.php # Crear publicación
│   │   ├── editar_publicacion.php     # Editar publicación
│   │   ├── mis-publicaciones.php      # Listar publicaciones propias
│   │   ├── perfil.php                 # Perfil de publicador
│   │   └── sidebar-publicador.php     # Sidebar responsive
│   │
│   └── admins/                        # Panel de administración
│       ├── index-admin.php            # Dashboard admin
│       ├── login-admin.php            # Login admin con 2FA
│       ├── perfil-admin.php           # Perfil admin
│       ├── gestionar-reportes.php     # Gestión de reportes
│       ├── gestionar_accesos.php      # Aprobación de publicadores
│       ├── config-admin.php           # Funciones administrativas
│       └── sidebar-admin.php          # Sidebar responsive
│
├── assets/                            # Recursos estáticos
│   ├── css/
│   │   ├── main.css                   # Estilos principales
│   │   └── css-admins/
│   │       └── admin.css              # Estilos del panel admin
│   ├── js/
│   │   └── ai-asistente.js            # Asistente virtual con IA
│   ├── img/
│   │   ├── logo/                      # Logos del sitio
│   │   └── uploads/                   # Fotos de perfil
│   └── vendor/
│       ├── bootstrap/                 # Bootstrap 5.3
│       └── bootstrap-icons/           # Iconos
│
├── ollama_ia/                         # Moderación con IA
│   ├── moderar_publicacion.php        # Endpoint de moderación
│   └── ollama_service.php             # Servicio Ollama
│
├── manifest.json                      # Configuración PWA
├── sw.js                              # Service Worker PWA
│
└── setup SQL/
    ├── setup_2fa.sql                  # Instalación sistema 2FA
    ├── setup_contactos.sql            # Tabla de contactos
    └── fix_2fa_column.sql             # Corrección columna code
```

---

## 💾 Base de Datos

### Diagrama de Tablas Principales

```
┌─────────────────┐         ┌──────────────────┐
│   usuarios      │────┐    │  publicadores    │
├─────────────────┤    │    ├──────────────────┤
│ id (PK)         │    │    │ id (PK)          │
│ nombre          │    │    │ nombre           │
│ correo          │    │    │ email            │
│ password (hash) │    │    │ password (hash)  │
│ foto_perfil     │    │    │ two_factor_enabled│
│ blocked_until   │    │    │ blocked_until    │
└─────────────────┘    │    │ estado           │
                       │    └──────────────────┘
                       │            │
                       │            │ 1:N
                   1:N │            ▼
                       │    ┌──────────────────┐
                       └───▶│  publicaciones   │
                            ├──────────────────┤
                            │ id (PK)          │
                            │ titulo           │
                            │ contenido        │
                            │ resumen          │
                            │ publicador_id(FK)│
                            │ categoria_id(FK) │
                            │ estado           │
                            │ imagen_principal │
                            │ fecha_publicacion│
                            └──────────────────┘
                                    │
                          ┌─────────┼─────────┐
                          │         │         │
                         1:N       1:N       1:N
                          ▼         ▼         ▼
                ┌────────────┐ ┌────────────┐ ┌────────────┐
                │ comentarios│ │   likes    │ │  reportes  │
                └────────────┘ └────────────┘ └────────────┘
```

### Tabla: `usuarios`

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,              -- bcrypt hash
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    foto_perfil VARCHAR(255),
    two_factor_enabled TINYINT(1) DEFAULT 0,     -- Opcional para usuarios
    blocked_until DATETIME NULL,                  -- Bloqueo por intentos
    INDEX idx_correo (correo)
);
```

**Notas:**
- `password`: Hasheado con `password_hash($password, PASSWORD_BCRYPT)`
- `blocked_until`: Fecha límite de bloqueo temporal (15 minutos por defecto)
- `two_factor_enabled`: Los usuarios normales pueden tenerlo opcional

### Tabla: `publicadores`

```sql
CREATE TABLE publicadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    especialidad VARCHAR(100),
    institucion VARCHAR(200),
    two_factor_enabled TINYINT(1) DEFAULT 1,     -- OBLIGATORIO
    blocked_until DATETIME NULL,
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    fecha_aprobacion DATETIME,
    aprobado_por INT,                            -- admin_id
    foto_perfil VARCHAR(255),
    INDEX idx_estado (estado),
    INDEX idx_email (email)
);
```

**Notas:**
- **2FA OBLIGATORIO**: `two_factor_enabled` por defecto en 1
- Estados: `pendiente` → `aprobado` → puede publicar
- Requiere aprobación de administrador

### Tabla: `admins`

```sql
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nivel ENUM('super', 'moderador') DEFAULT 'moderador',
    two_factor_enabled TINYINT(1) DEFAULT 1,     -- OBLIGATORIO
    blocked_until DATETIME NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    foto_perfil VARCHAR(255),
    INDEX idx_nivel (nivel)
);
```

### Tabla: `publicaciones`

```sql
CREATE TABLE publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    contenido LONGTEXT NOT NULL,
    resumen TEXT,
    imagen_principal VARCHAR(255),
    publicador_id INT NOT NULL,
    categoria_id INT,
    tipo ENUM('articulo', 'investigacion', 'caso_estudio', 'revision') DEFAULT 'articulo',
    estado ENUM('borrador', 'revision', 'publicado', 'rechazado') DEFAULT 'borrador',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_publicacion DATETIME,
    moderado_por_ia TINYINT(1) DEFAULT 0,
    nota_moderacion TEXT,
    
    FOREIGN KEY (publicador_id) REFERENCES publicadores(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_estado (estado),
    INDEX idx_publicador (publicador_id),
    INDEX idx_fecha (fecha_publicacion)
);
```

**Flujo de Estados:**
```
borrador → revision (moderación IA) → publicado
                                   → rechazado
```

### Tabla: `two_factor_codes`

```sql
CREATE TABLE two_factor_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('usuario', 'publicador', 'admin') NOT NULL,
    user_id INT NOT NULL,
    code VARCHAR(255) NOT NULL,                  -- Hash bcrypt del código
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,                -- 10 minutos después
    used TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    INDEX idx_user (user_type, user_id),
    INDEX idx_expires (expires_at)
);
```

**Características:**
- Códigos encriptados con `password_hash()`
- Expiración automática en 10 minutos
- Invalidación tras uso
- Limpieza automática con evento SQL

---

## 🔐 Sistema de Autenticación

### Flujo de Login (Usuario Normal)

```
┌─────────────────┐
│ inicio-sesion.php│
└────────┬─────────┘
         │
         ▼
┌────────────────────────┐
│ 1. Validar credenciales│
│    (email + password)  │
└────────┬───────────────┘
         │
         ▼
    ¿Correcto?
         │
         ├─ NO ──▶ Error "Usuario o contraseña incorrectos"
         │
         ├─ SÍ ──▶ ¿Bloqueado?
                       │
                       ├─ SÍ ──▶ Error "Cuenta bloqueada"
                       │
                       └─ NO ──▶ ¿2FA activado?
                                     │
                                     ├─ NO ──▶ Crear sesión → Redirigir
                                     │
                                     └─ SÍ ──▶ [ Flujo 2FA ]
```

**Código Principal** (forms/inicio-sesion.php):

```php
// 1. Validar entrada
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$password = $_POST['password'];

// 2. Buscar usuario
$stmt = $conn->prepare("SELECT id, nombre, correo, password, blocked_until 
                        FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Usuario o contraseña incorrectos");
}

$usuario = $resultado->fetch_assoc();

// 3. Verificar bloqueo
if ($usuario['blocked_until'] && strtotime($usuario['blocked_until']) > time()) {
    die("Cuenta bloqueada temporalmente");
}

// 4. Verificar contraseña
if (!password_verify($password, $usuario['password'])) {
    // Registrar intento fallido (no implementado aquí)
    die("Usuario o contraseña incorrectos");
}

// 5. Verificar si tiene 2FA
$tiene_2fa = 0; // Lógica de verificación

if ($tiene_2fa) {
    // Ir a flujo 2FA
    header("Location: verify_2fa.php");
} else {
    // Crear sesión directa
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    header("Location: ../pagina-principal.php");
}
```

---

## 🔒 Autenticación de Dos Factores (2FA)

### Arquitectura del Sistema 2FA

```
┌──────────────────┐
│ Login exitoso    │
│ (user/pass OK)   │
└────────┬─────────┘
         │
         ▼
┌────────────────────────────┐
│ generarCodigo2FA()         │
│ - Genera número aleatorio  │
│ - 6 dígitos (100000-999999)│
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ guardarCodigo2FA()         │
│ - Encripta con bcrypt      │
│ - Guarda en DB             │
│ - Expira en 10 minutos     │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ enviarCodigo2FA()          │
│ - Envía email con código   │
│ - Plantilla HTML elegante  │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ verify_2fa.php             │
│ - Usuario ingresa código   │
└────────┬───────────────────┘
         │
         ▼
┌────────────────────────────┐
│ validarCodigo2FA()         │
│ - Busca en DB              │
│ - password_verify()        │
│ - Marca como usado         │
└────────┬───────────────────┘
         │
         ├─ VÁLIDO ──▶ Crear sesión completa
         │
         └─ INVÁLIDO ──▶ Contar intento
                             │
                             ▼
                         ¿3 intentos?
                             │
                             └─ SÍ ──▶ Bloquear 15 min
```

### Funciones Clave (forms/2fa_functions.php)

#### 1) Generar Código

```php
function generarCodigo2FA() {
    // Genera código aleatorio de 6 dígitos
    return rand(100000, 999999);
}
```

#### 2) Guardar Código (Encriptado)

```php
function guardarCodigo2FA($conn, $userType, $userId, $codigo) {
    // Calcular expiración
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // 🔐 CRÍTICO: Encriptar código con bcrypt
    $codigoEncriptado = password_hash($codigo, PASSWORD_BCRYPT);
    
    // Invalidar códigos anteriores
    $stmt = $conn->prepare("UPDATE two_factor_codes SET used = 1 
                           WHERE user_type = ? AND user_id = ? AND used = 0");
    $stmt->bind_param("si", $userType, $userId);
    $stmt->execute();
    
    // Insertar nuevo código encriptado
    $stmt = $conn->prepare("INSERT INTO two_factor_codes 
                           (user_type, user_id, code, expires_at, ip_address) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $userType, $userId, $codigoEncriptado, $expires, $ip);
    
    return $stmt->execute();
}
```

**Importante:** 
- El código se encripta con `password_hash()` antes de guardarse
- Imposible recuperar el código original de la BD
- Hash bcrypt genera ~60 caracteres → columna `code VARCHAR(255)`

#### 3) Validar Código

```php
function validarCodigo2FA($conn, $userType, $userId, $codigoIngresado) {
    // Buscar códigos válidos
    $stmt = $conn->prepare("SELECT id, code FROM two_factor_codes 
                           WHERE user_type = ? AND user_id = ? 
                           AND used = 0 AND expires_at > NOW()");
    $stmt->bind_param("si", $userType, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Verificar cada código con password_verify
    while ($row = $result->fetch_assoc()) {
        $codigoAlmacenado = $row['code'];
        
        // Opción 1: Código encriptado (bcrypt)
        $esValido = password_verify($codigoIngresado, $codigoAlmacenado);
        
        // Opción 2: Código en texto plano (retrocompatibilidad)
        if (!$esValido && $codigoIngresado === $codigoAlmacenado) {
            $esValido = true;
        }
        
        if ($esValido) {
            // Marcar como usado
            $stmt2 = $conn->prepare("UPDATE two_factor_codes SET used = 1 WHERE id = ?");
            $stmt2->bind_param("i", $row['id']);
            $stmt2->execute();
            
            return true;
        }
    }
    
    return false;
}
```

#### 4) Enviar Email con Código

```php
function enviarCodigo2FA($email, $nombre, $codigo) {
    require_once __DIR__ . '/EmailHelper.php';
    
    $asunto = "Código de verificación - Lab-Explora";
    
    $cuerpo = "
    <div style='font-family: Arial, sans-serif; max-width: 600px;'>
        <div style='background: #7390A0; padding: 30px; text-align: center; color: white;'>
            <h1>🔐 Verificación en 2 Pasos</h1>
        </div>
        <div style='padding: 30px; background: #f9f9f9;'>
            <p>Hola <strong>$nombre</strong>,</p>
            <p>Tu código de verificación es:</p>
            <div style='background: white; padding: 20px; text-align: center;'>
                <h2 style='color: #7390A0; font-size: 36px; letter-spacing: 5px;'>
                    $codigo
                </h2>
            </div>
            <p><strong>Este código expira en 10 minutos.</strong></p>
        </div>
    </div>
    ";
    
    return EmailHelper::enviarCorreo($email, $asunto, $cuerpo);
}
```

### Interfaz de Verificación (forms/verify_2fa.php)

```html
<form method="POST" action="check_2fa.php">
    <input type="text" 
           name="code" 
           maxlength="6" 
           pattern="\d{6}" 
           placeholder="000000"
           autofocus
           required>
    <button type="submit">Verificar</button>
</form>

<a href="resend_2fa.php">Reenviar código</a>
```

### Procesamiento (forms/check_2fa.php)

```php
session_start();
require_once 'conexion.php';
require_once '2fa_functions.php';

// Obtener datos pendientes de sesión
$userType = $_SESSION['pending_2fa']['type'];
$userId = $_SESSION['pending_2fa']['id'];
$codigoIngresado = $_POST['code'];

// Validar formato
if (strlen($codigoIngresado) != 6 || !ctype_digit($codigoIngresado)) {
    $_SESSION['error_2fa'] = "El código debe tener 6 dígitos";
    header('Location: verify_2fa.php');
    exit();
}

// Verificar si está bloqueado
if (estaBloqueado($conexion, $userType, $userId)) {
    $_SESSION['error_2fa'] = "Cuenta bloqueada. Intenta más tarde.";
    header('Location: verify_2fa.php');
    exit();
}

// Validar código
if (validarCodigo2FA($conexion, $userType, $userId, $codigoIngresado)) {
    // ✅ CÓDIGO CORRECTO
    
    // Limpiar datos temporales
    unset($_SESSION['pending_2fa']);
    unset($_SESSION['intentos_2fa']);
    
    // Crear sesión completa según tipo
    if ($userType == 'publicador') {
        $_SESSION['publicador_id'] = $userId;
        // ... más datos de sesión
        header("Location: publicadores/index-publicadores.php");
    }
    // ...
    exit();
    
} else {
    // ❌ CÓDIGO INCORRECTO
    
    if (!isset($_SESSION['intentos_2fa'])) {
        $_SESSION['intentos_2fa'] = 0;
    }
    $_SESSION['intentos_2fa']++;
    
    if ($_SESSION['intentos_2fa'] >= 3) {
        // Bloquear por 15 minutos
        bloquearUsuario($conexion, $userType, $userId, 15);
        $_SESSION['error_2fa'] = "Bloqueado por 15 minutos";
        header('Location: inicio-sesion.php');
    } else {
        $restantes = 3 - $_SESSION['intentos_2fa'];
        $_SESSION['error_2fa'] = "Código incorrecto. Te quedan $restantes intento(s)";
        header('Location: verify_2fa.php');
    }
    exit();
}
```

### Gestión de Bloqueos

```php
function bloquearUsuario($conn, $userType, $userId, $minutos = 15) {
    $blockedUntil = date('Y-m-d H:i:s', strtotime("+$minutos minutes"));
    
    $tabla = '';
    if ($userType == 'usuario') $tabla = 'usuarios';
    elseif ($userType == 'publicador') $tabla = 'publicadores';
    elseif ($userType == 'admin') $tabla = 'admins';
    
    $stmt = $conn->prepare("UPDATE $tabla SET blocked_until = ? WHERE id = ?");
    $stmt->bind_param("si", $blockedUntil, $userId);
    
    return $stmt->execute();
}
```

---

## 👥 Gestión de Usuarios

### Tipos de Usuario

| Tipo | Descripción | 2FA | Aprobación |
|------|-------------|-----|------------|
| **Usuario** | Lector de publicaciones | Opcional | Automática |
| **Publicador** | Crea publicaciones | **Obligatorio** | **Requiere admin** |
| **Admin** | Gestiona plataforma | **Obligatorio** | Manual (BD) |

### Registro de Usuarios (forms/register.php)

```php
// Validar entrada
$nombre = trim($_POST['nombre']);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$password = $_POST['password'];

// Verificar que email no exista
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    die("El correo ya está registrado");
}

// Hashear contraseña
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Insertar usuario
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password) 
                        VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $email, $passwordHash);
$stmt->execute();

// Redirigir a login
header("Location: inicio-sesion.php?registro=exitoso");
```

### Registro de Publicadores (forms/publicadores/registro-publicadores.php)

```php
// Similar a usuarios, pero:
// 1. Requiere campos adicionales
$especialidad = trim($_POST['especialidad']);
$institucion = trim($_POST['institucion']);

// 2. Estado inicial: pendiente
$estado = 'pendiente';

// 3. 2FA activado por defecto
$two_factor_enabled = 1;

$stmt = $conn->prepare("INSERT INTO publicadores 
                       (nombre, email, password, especialidad, institucion, estado, two_factor_enabled) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssi", $nombre, $email, $passwordHash, 
                  $especialidad, $institucion, $estado, $two_factor_enabled);
$stmt->execute();

// Mensaje: "Registrado. Espera aprobación del administrador"
```

### Aprobación de Publicadores (forms/admins/gestionar_accesos.php)

```php
function aprobarPublicador($conn, $publicador_id, $admin_id) {
    // Actualizar estado
    $fecha_aprobacion = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("UPDATE publicadores 
                           SET estado = 'aprobado', 
                               fecha_aprobacion = ?,
                               aprobado_por = ?
                           WHERE id = ?");
    $stmt->bind_param("sii", $fecha_aprobacion, $admin_id, $publicador_id);
    $stmt->execute();
    
    // Enviar email de notificación
    $stmt = $conn->prepare("SELECT nombre, email FROM publicadores WHERE id = ?");
    $stmt->bind_param("i", $publicador_id);
    $stmt->execute();
    $publicador = $stmt->get_result()->fetch_assoc();
    
    enviarEmailAprobacion($publicador['email'], $publicador['nombre']);
}
```

### Perfiles de Usuario

**Perfil Normal** (forms/perfil.php):
- Información personal
- Foto de perfil
- Publicaciones guardadas ("Leer más tarde")
- Credencial digital descargable
- **NO tiene 2FA obligatorio**

**Perfil Publicador** (forms/publicadores/perfil.php):
- Información profesional
- Estadísticas de publicaciones
- Credencial digital oficial
- **2FA OBLIGATORIO** (no se puede desactivar)

**Perfil Admin** (forms/admins/perfil-admin.php):
- Información administrativa
- Nivel de acceso (super/moderador)
- Credencial digital con firma
- **2FA OBLIGATORIO**

---

## 📝 Sistema de Publicaciones

### Flujo de Creación (Publicador)

```
┌─────────────────────────────┐
│ crear_nueva_publicacion.php │
└──────────────┬──────────────┘
               │
               ▼
┌──────────────────────────────┐
│ Form: título, contenido,     │
│       imagen, categoría      │
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│ Guardar en BD                │
│ estado = 'revision'          │
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│ moderar_publicacion.php      │
│ (Moderación con IA)          │
└──────────────┬───────────────┘
               │
          ┌────┴────┐
          │         │
     APROBAR    RECHAZAR
          │         │
          ▼         ▼
    'publicado'  'rechazado'
```

### Crear Publicación (forms/publicadores/crear_nueva_publicacion.php)

```php
// 1. Validar campos
$titulo = trim($_POST['titulo']);
$contenido = $_POST['contenido']; // HTML permitido
$resumen = trim($_POST['resumen']);
$categoria_id = (int) $_POST['categoria_id'];
$tipo = $_POST['tipo']; // articulo, investigacion, etc.

// 2. Procesar imagen principal
$imagen_principal = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $imagen_principal = procesarImagen($_FILES['imagen']);
}

// 3. Insertar en BD con estado 'revision'
$stmt = $conn->prepare("INSERT INTO publicaciones 
                       (titulo, contenido, resumen, imagen_principal, 
                        publicador_id, categoria_id, tipo, estado) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'revision')");
$stmt->bind_param("ssssiis", $titulo, $contenido, $resumen, $imagen_principal,
                  $_SESSION['publicador_id'], $categoria_id, $tipo);
$stmt->execute();
$publicacion_id = $conn->insert_id;

// 4. Enviar a moderación automática con IA
$url_moderacion = "http://localhost/lab2/ollama_ia/moderar_publicacion.php";
$datos = [
    'publicacion_id' => $publicacion_id,
    'titulo' => $titulo,
    'contenido' => strip_tags($contenido),
    'publicador_id' => $_SESSION['publicador_id']
];

// Llamada asíncrona (no bloquea al usuario)
file_get_contents($url_moderacion http://localhost/lab2/ollama_ia/moderar_publicacion.php . '?' . http_build_query($datos));

// 5. Mensaje al usuario
$_SESSION['mensaje'] = "Publicación enviada. Se está verificando con moderación automática.";
header("Location: mis-publicaciones.php");
```

### Editar Publicación (forms/publicadores/editar_publicacion.php)

```php
// Solo puede editar si:
// 1. Es el autor
// 2. No está en revisión por IA
// 3. O es admin

$publicacion_id = (int) $_GET['id'];

// Verificar propiedad
$stmt = $conn->prepare("SELECT publicador_id, estado 
                        FROM publicaciones WHERE id = ?");
$stmt->bind_param("i", $publicacion_id);
$stmt->execute();
$pub = $stmt->get_result()->fetch_assoc();

if ($pub['publicador_id'] != $_SESSION['publicador_id']) {
    die("No tienes permiso para editar esta publicación");
}

if ($pub['estado'] == 'revision') {
    die("No puedes editar mientras está en moderación.");
}

// Proceder con edición...
```

---

## 🤖 Moderación con Inteligencia Artificial

### Arquitectura del Sistema

```
┌──────────────────────┐
│ Nueva publicación    │
│ estado='revision'    │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────────┐
│ moderar_publicacion.php      │
│ (Endpoint de moderación)     │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ ollama_service.php           │
│ - Conecta con Ollama local   │
│ - Modelo: llama3.2:1b        │
└──────────┬───────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Analizar contenido:          │
│ 1. Lenguaje ofensivo         │
│ 2. Spam                      │
│ 3. Contenido inapropiado     │
│ 4. Relevancia científica     │
└──────────┬───────────────────┘
           │
      ┌────┴────┐
      │         │
  APROBAR   RECHAZAR
      │         │
      ▼         ▼
┌──────────┐ ┌──────────┐
│publicado│ │rechazado│
└──────────┘ └──────────┘
```

### Endpoint de Moderación (ollama_ia/moderar_publicacion.php)

```php
<?php
require_once '../forms/conexion.php';
require_once 'ollama_service.php';

// Obtener datos de la publicación
$publicacion_id = (int) $_GET['publicacion_id'];
$titulo = $_GET['titulo'];
$contenido = $_GET['contenido'];

// Preparar prompt para IA
$prompt = "
Eres un moderador experto en publicaciones científicas de laboratorio clínico.

Analiza la siguiente publicación y responde ÚNICAMENTE con 'APROBAR' o 'RECHAZAR'.

Criteriospara RECHAZAR:
- Contenido ofensivo, discriminatorio o de odio
- Spam o publicidad
- Contenido sexual inapropiado
- Información médica peligrosa o falsa
- No relacionado con ciencia/laboratorio

Criterios para APROBAR:
- Contenido científico o educativo
- Lenguaje profesional y respetuoso
- Información verificable
- Relevante para laboratorio clínico

---
TÍTULO: $titulo

CONTENIDO:
$contenido
---

Responde solo: APROBAR o RECHAZAR
";

// Llamar a Ollama
$respuesta = llamarOllama($prompt);

// Procesar decisión
if (stripos($respuesta, 'APROBAR') !== false) {
    // ✅ APROBAR
    $nuevo_estado = 'publicado';
    $fecha_publicacion = date('Y-m-d H:i:s');
    
    $stmt = $conexion->prepare("UPDATE publicaciones 
                               SET estado = ?, 
                                   fecha_publicacion = ?,
                                   moderado_por_ia = 1,
                                   nota_moderacion = 'Aprobado automáticamente por IA'
                               WHERE id = ?");
    $stmt->bind_param("ssi", $nuevo_estado, $fecha_publicacion, $publicacion_id);
    $stmt->execute();
    
    // Notificar al publicador
    enviarEmailAprobacion($publicacion_id);
    
} else {
    // ❌ RECHAZAR
    $nuevo_estado = 'rechazado';
    $motivo = extraerMotivo($respuesta);
    
    $stmt = $conexion->prepare("UPDATE publicaciones 
                               SET estado = ?,
                                   moderado_por_ia = 1,
                                   nota_moderacion = ?
                               WHERE id = ?");
    $stmt->bind_param("ssi", $nuevo_estado, $motivo, $publicacion_id);
    $stmt->execute();
    
    // Notificar al publicador del rechazo
    enviarEmailRechazo($publicacion_id, $motivo);
}

echo json_encode(['estado' => $nuevo_estado]);
?>
```

### Servicio Ollama (ollama_ia/ollama_service.php)

```php
<?php
function llamarOllama($prompt, $modelo = 'llama3.2:1b') {
    $url = 'http://localhost:11434/api/generate';
    
    $data = [
        'model' => $modelo,
        'prompt' => $prompt,
        'stream' => false,
        'options' => [
            'temperature' => 0.3,  // Más determinista
            'top_p' => 0.9
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 segundos max
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        // Si Ollama no responde, aprobar manualmente
        error_log("Ollama no disponible. Publicación requiere moderación manual.");
        return "REVISION_MANUAL";
    }
    
    $resultado = json_decode($response, true);
    return $resultado['response'] ?? '';
}
?>
```

**Características:**
- **Modelo ligero:** llama3.2:1b (rápido)
- **Timeout:** 30 segundos máximo
- **Fallback:** Si Ollama falla, marca para revisión manual
- **Temperature 0.3:** Respuestas más consistentes

---

## 💬 Sistema de Interacciones

### Tipos de Interacciones

| Interacción | Tabla | Usuario | Funcionalidad |
|-------------|-------|---------|---------------|
| **Me gusta** | `likes` | Cualquiera | Like único por publicación |
| **Comentario** | `comentarios` | Cualquiera | Múltiples comentarios |
| **Guardado** | `leer_mas_tarde` | Cualquiera | Guardar para leer después |
| **Reporte** | `reportes` | Cualquiera | Reportar contenido inapropiado |

### Endpoint de Interacciones (forms/procesar-interacciones.php)

```php
<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit();
}

$accion = $_POST['accion'];
$publicacion_id = (int) $_POST['publicacion_id'];
$usuario_id = $_SESSION['usuario_id'];

switch ($accion) {
    case 'like':
        // Verificar si ya dio like
        $stmt = $conexion->prepare("SELECT id FROM likes 
                                    WHERE publicacion_id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            // Ya existe: Quitar like
            $stmt = $conexion->prepare("DELETE FROM likes 
                                        WHERE publicacion_id = ? AND usuario_id = ?");
            $stmt->bind_param("ii", $publicacion_id, $usuario_id);
            $stmt->execute();
            $mensaje = 'Like eliminado';
        } else {
            // No existe: Agregar like
            $stmt = $conexion->prepare("INSERT INTO likes (publicacion_id, usuario_id) 
                                        VALUES (?, ?)");
            $stmt->bind_param("ii", $publicacion_id, $usuario_id);
            $stmt->execute();
            $mensaje = 'Like agregado';
        }
        
        // Obtener total de likes
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM likes 
                                    WHERE publicacion_id = ?");
        $stmt->bind_param("i", $publicacion_id);
        $stmt->execute();
        $total_likes = $stmt->get_result()->fetch_assoc()['total'];
        
        echo json_encode([
            'success' => true,
            'message' => $mensaje,
            'total_likes' => $total_likes
        ]);
        break;
        
    case 'comentar':
        $comentario = trim($_POST['comentario']);
        
        if (strlen($comentario) < 3) {
            echo json_encode(['success' => false, 'message' => 'Comentario muy corto']);
            exit();
        }
        
        $stmt = $conexion->prepare("INSERT INTO comentarios 
                                    (publicacion_id, usuario_id, comentario) 
                                    VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $publicacion_id, $usuario_id, $comentario);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Comentario agregado']);
        break;
        
    case 'guardar_leer_mas_tarde':
        // Toggle: Si existe, eliminar. Si no, agregar.
        $stmt = $conexion->prepare("SELECT id FROM leer_mas_tarde 
                                    WHERE publicacion_id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $stmt = $conexion->prepare("DELETE FROM leer_mas_tarde 
                                        WHERE publicacion_id = ? AND usuario_id = ?");
            $stmt->bind_param("ii", $publicacion_id, $usuario_id);
            $stmt->execute();
            $mensaje = 'Eliminado de guardados';
        } else {
            $stmt = $conexion->prepare("INSERT INTO leer_mas_tarde 
                                        (publicacion_id, usuario_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $publicacion_id, $usuario_id);
            $stmt->execute();
            $mensaje = 'Guardado para leer más tarde';
        }
        
        echo json_encode(['success' => true, 'message' => $mensaje]);
        break;
        
    case 'reportar':
        $motivo = trim($_POST['motivo']);
        $descripcion = trim($_POST['descripcion']);
        
        // Verificar que no haya reportado antes
        $stmt = $conexion->prepare("SELECT id FROM reportes 
                                    WHERE publicacion_id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya reportaste esta publicación']);
            exit();
        }
        
        // Insertar reporte
        $stmt = $conexion->prepare("INSERT INTO reportes 
                                    (publicacion_id, usuario_id, motivo, descripcion, estado) 
                                    VALUES (?, ?, ?, ?, 'pendiente')");
        $stmt->bind_param("iiss", $publicacion_id, $usuario_id, $motivo, $descripcion);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Reporte enviado. Será revisado por administradores.']);
        break;
}
?>
```

### Interfaz JavaScript (ver-publicacion.php)

```javascript
// Dar Like
function darLike(publicacionId) {
    fetch('forms/procesar-interacciones.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `accion=like&publicacion_id=${publicacionId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Actualizar contador
            document.getElementById('likes-count').textContent = data.total_likes;
            // Cambiar icono
            document.getElementById('like-btn').classList.toggle('liked');
        }
    });
}

// Comentar
function enviarComentario(publicacionId) {
    const comentario = document.getElementById('comentario-input').value;
    
    fetch('forms/procesar-interacciones.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `accion=comentar&publicacion_id=${publicacionId}&comentario=${encodeURIComponent(comentario)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Recargar para mostrar comentario
        } else {
            alert(data.message);
        }
    });
}
```

---

## 📄 Credenciales Digitales PDF

### Generación (forms/perfil.php)

Las credenciales se generan **del lado del cliente** usando html2pdf.js.

```html
<!-- Contenido de la credencial -->
<div id="credencial-content" class="credential-card">
    <div class="credential-header">
        <strong>Lab-Explora</strong><br>
        <small>MIEMBRO OFICIAL</small>
    </div>
    
    <div class="credential-body">
        <div class="credential-avatar">
            <img src="<?= !empty($usuario['foto_perfil']) ? '../' . $usuario['foto_perfil'] : '../assets/img/defecto.png' ?>">
        </div>
        <div class="credential-info">
            <h4><?= htmlspecialchars($usuario['nombre']) ?></h4>
            <p><?= htmlspecialchars($usuario['correo']) ?></p>
            <span class="badge bg-light text-dark">Estudiante / Lector</span>
        </div>
    </div>
    
    <div class="credential-footer">
        ID: #<?= str_pad($usuario['id'], 4, '0', STR_PAD_LEFT) ?><br>
        Válido: <?= date('Y') ?> - <?= date('Y') + 1 ?>
        
        <div class="signature-box">
            <div>Firma Digital</div>
            <div class="signature-hash">
                <?= strtoupper(substr(hash('sha256', $usuario['id'] . $usuario['nombre'] . $usuario['correo'] . "LAB_EXPLORA_2024"), 0, 24)) ?>
            </div>
        </div>
    </div>
</div>

<button onclick="descargarCredencial()">
    <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
</button>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function descargarCredencial() {
    const elemento = document.getElementById('credencial-content');
    
    const opciones = {
        margin: [10, 10, 10, 10],
        filename: 'Credencial_<?= htmlspecialchars($usuario['nombre']) ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a5', orientation: 'portrait' }
    };
    
    html2pdf().set(opciones).from(elemento).save();
}
</script>
```

**Firma Digital:**
```php
$firma = hash('sha256', 
              $usuario['id'] . 
              $usuario['nombre'] . 
              $usuario['correo'] . 
              "LAB_EXPLORA_2024");
```

**Características:**
- Hash SHA-256 único e irrepetible
- Incluye ID, nombre, correo y salt
- Visible en PDF pero sin exponer datos sensibles
- Permite verificar autenticidad

---

## 🚨 Sistema de Reportes

### Flujo de Reportes

```
Usuario identifica contenido
inapropiado
         │
         ▼
┌────────────────────┐
│ Clic en "Reportar" │
└────────┬───────────┘
         │
         ▼
┌────────────────────────┐
│ Modal: Seleccionar     │
│ - Spam                 │
│ - Contenido ofensivo   │
│ - Información falsa    │
│ - Otro (describir)     │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│ INSERT INTO reportes   │
│ estado='pendiente'     │
└────────┬───────────────┘
         │
         ▼
┌───────────────────────────┐
│ Admin ve en             │
│ gestionar-reportes.php    │
└─────────┬─────────────────┘
          │
     ┌────┴────┐
     │         │
 RESOLVER  DESCARTAR
     │         │
     ▼         ▼
┌─────────┐ ┌──────────┐
│Acción   │ │Descartar │
│tomada   │ │reporte   │
└─────────┘ └──────────┘
```

### Admin: Gestionar Reportes (forms/admins/gestionar-reportes.php)

```php
// Obtener reportes pendientes
$sql = "SELECT r.*, p.titulo, u.nombre as usuario_nombre,
               pub.nombre as publicador_nombre
        FROM reportes r
        JOIN publicaciones p ON r.publicacion_id = p.id
        JOIN usuarios u ON r.usuario_id = u.id
        JOIN publicadores pub ON p.publicador_id = pub.id
        WHERE r.estado = 'pendiente'
        ORDER BY r.fecha_reporte DESC";

$reportes = $conexion->query($sql);

foreach ($reportes as $reporte) {
    echo "
    <tr>
        <td>{$reporte['id']}</td>
        <td><a href='../ver-publicacion.php?id={$reporte['publicacion_id']}'>{$reporte['titulo']}</a></td>
        <td>{$reporte['usuario_nombre']}</td>
        <td><span class='badge bg-warning'>{$reporte['motivo']}</span></td>
        <td>{$reporte['descripcion']}</td>
        <td>
            <button onclick='resolverReporte({$reporte['id']})'>Resolver</button>
            <button onclick='descartarReporte({$reporte['id']})'>Descartar</button>
        </td>
    </tr>
    ";
}

function resolverReporte($reporte_id, $admin_id) {
    global $conexion;
    
    // Marcar como resuelto
    $stmt = $conexion->prepare("UPDATE reportes 
                                SET estado = 'resuelto',
                                    resuelto_por = ?,
                                    fecha_resolucion = NOW()
                                WHERE id = ?");
    $stmt->bind_param("ii", $admin_id, $reporte_id);
    $stmt->execute();
    
    // Opcional: Tomar acción sobre la publicación
    // (eliminar, suspender, advertir al autor, etc.)
}
```

---

## 📧 Formulario de Contacto Legal

### Estructura (contacto.php)

```html
<form action="forms/procesar-contacto.php" method="POST">
    <input type="text" name="nombre" required>
    <input type="email" name="email" required>
    
    <select name="asunto" required>
        <option value="terminos">Términos y Condiciones</option>
        <option value="privacidad">Política de Privacidad</option>
        <option value="arco">Exercise Derechos ARCO</option>
        <option value="eliminacion">Eliminación de cuenta</option>
        <option value="legal">Asunto legal</option>
    </select>
    
    <textarea name="mensaje" rows="6" required></textarea>
    
    <input type="checkbox" name="acepta_privacidad" required>
    Acepto la Política de Privacidad
    
    <button type="submit">Enviar Mensaje</button>
</form>
```

### Procesamiento (forms/procesar-contacto.php)

```php
// Validar
$nombre = sanitize_input($_POST['nombre']);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$asunto = sanitize_input($_POST['asunto']);
$mensaje = sanitize_input($_POST['mensaje']);

// Guardar en BD
$stmt = $conexion->prepare("INSERT INTO contactos_legales 
                           (nombre, email, asunto, mensaje, fecha_envio, ip_origen)
                           VALUES (?, ?, ?, ?, NOW(), ?)");
$ip = $_SERVER['REMOTE_ADDR'];
$stmt->bind_param("sssss", $nombre, $email, $asunto, $mensaje, $ip);
$stmt->execute();

// Enviar email al departamento
$destinatario = ($asunto === 'privacidad') ? 'privacidad@lab-explora.com' : 'legal@lab-explora.com';

$emailHelper = new EmailHelper();
$emailHelper->enviarCorreo($destinatario, "Nuevo contacto: $asunto", $cuerpoHTML);

// Enviar confirmación al usuario
$emailHelper->enviarCorreo($email, "Hemos recibido tu mensaje", $confirmacionHTML);
```

---

## 🔒 Seguridad

### Hashing de Contraseñas

```php
// Registro
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Login
if (password_verify($passwordIngresado, $passwordHashAlmacenado)) {
    // Correcto
}
```

### Prevención de SQL Injection

```php
// ❌ MAL (vulnerable)
$sql = "SELECT * FROM usuarios WHERE email = '$email'";

// ✅ BIEN (prepared statements)
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
```

### Prevención de XSS

```php
// Siempre escapar output
echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8');
```

### CSRF Protection

```php
// Generar token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// En formulario
echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';

// Validar
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token inválido");
}
```

---

## 📱 PWA (Progressive Web App)

### Manifest (manifest.json)

```json
{
  "name": "Lab-Explora",
  "short_name": "Lab-Explora",
  "description": "Plataforma de publicaciones científicas",
  "start_url": "/lab2/index.php",
  "display": "standalone",
  "background_color": "#7390A0",
  "theme_color": "#7390A0",
  "icons": [
    {
      "src": "assets/img/logo/logo-192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "assets/img/logo/logo-512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

### Service Worker (sw.js)

```javascript
const CACHE_NAME = 'lab-explora-v1';
const urlsToCache = [
  '/lab2/',
  '/lab2/index.php',
  '/lab2/assets/css/main.css',
  '/lab2/assets/vendor/bootstrap/css/bootstrap.min.css',
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});
```

---

## 📊 Resumen de Funcionalidades

| Funcionalidad | Descripción | Usuarios | Tecnología |
|---------------|-------------|----------|------------|
| **Autenticación** | Login/registro multi-nivel | Todos | PHP + bcrypt |
| **2FA** | Verificación en 2 pasos | Publicadores/Admins | Email + códigos encriptados |
| **Publicaciones** | CRUD de artículos científicos | Publicadores | PHP + MySQL |
| **Moderación IA** | Aprobación automática | Sistema | Ollama + llama3.2 |
| **Interacciones** | Likes, comentarios, guardados | Usuarios | AJAX + PHP |
| **Reportes** | Denuncia de contenido | Usuarios | PHP + notificaciones |
| **Credenciales PDF** | Certificados digitales | Todos | html2pdf.js + SHA-256 |
| **Contacto Legal** | Formulario ARCO | Todos | PHP + EmailHelper |
| **PWA** | Instalable en dispositivos | Todos | Service Workers |

---

**Última actualización:** 7 de diciembre de 2025  
**Versión:** 2.0  
**Autor:** Equipo Lab-Explora
