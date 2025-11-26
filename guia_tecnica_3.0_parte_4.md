# 📚 GUÍA TÉCNICA 3.0 - PARTE 4
## Recuperación de Contraseña y Gestión de Publicaciones

---

# 🔐 RECUPERACIÓN DE CONTRASEÑA

## `recuperar.php` - Sistema de Recuperación de Contraseña

### 🎯 Propósito
Permite a los usuarios recuperar su contraseña olvidada mediante un sistema de tokens seguros enviados por email.

### 📋 Flujo del Sistema
```
1. Usuario ingresa su email
2. Sistema genera token único aleatorio
3. Token se guarda en BD con expiración (1 hora)
4. Se envía email con link que contiene el token
5. Usuario hace click en el link
6. Sistema valida que el token exista y no haya expirado
7. Usuario ingresa nueva contraseña
8. Sistema actualiza la contraseña y elimina el token
```

### 📋 Código Completo Explicado

```php
<?php
// ============================================================================
// SECCIÓN 1: INICIALIZACIÓN Y CONFIGURACIÓN
// ============================================================================

session_start();
// Iniciamos la sesión

// ============================================================================
// CONFIGURACIÓN DE BASE DE DATOS CON PDO
// ============================================================================

$host = '127.0.0.1';
// ========================================================================
// 📌 EXPLICACIÓN DE 127.0.0.1 vs localhost
// ========================================================================
// 127.0.0.1 = Dirección IP numérica del localhost
// localhost = Nombre de host que se resuelve a 127.0.0.1
//
// ¿SON IGUALES?
// Generalmente sí, pero hay diferencias sutiles:
//
// 127.0.0.1:
// - Conexión directa por IP
// - Más rápido (no necesita resolución DNS)
// - Siempre usa IPv4
//
// localhost:
// - Necesita resolución DNS
// - Puede usar IPv4 (127.0.0.1) o IPv6 (::1)
// - Puede ser más lento en algunos sistemas
//
// RECOMENDACIÓN:
// Usar 127.0.0.1 para desarrollo local (más confiable)

$dbname = 'lab_exp_db';
$username = 'root';
$password = '';

// ============================================================================
// CREAR CONEXIÓN CON PDO
// ============================================================================

$pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8", $username, $password);
// ========================================================================
// 📌 EXPLICACIÓN DE PDO (PHP Data Objects)
// ========================================================================
// PDO es una interfaz para acceder a bases de datos en PHP.
//
// SINTAXIS:
// new PDO("driver:parametros", usuario, contraseña)
//
// DRIVER:
// mysql = MySQL/MariaDB
// pgsql = PostgreSQL
// sqlite = SQLite
// sqlsrv = SQL Server
//
// PARÁMETROS (DSN - Data Source Name):
// host=127.0.0.1 = Servidor
// port=3306 = Puerto de MySQL (3306 es el predeterminado)
// dbname=lab_exp_db = Base de datos
// charset=utf8 = Codificación de caracteres
//
// EJEMPLO COMPLETO:
// "mysql:host=127.0.0.1;port=3306;dbname=lab_exp_db;charset=utf8"
//
// DIFERENCIAS PDO vs MySQLi:
//
// PDO:
// - Funciona con múltiples bases de datos (MySQL, PostgreSQL, etc.)
// - Usa excepciones para errores
// - Sintaxis orientada a objetos
// - Más portable
//
// MySQLi:
// - Solo funciona con MySQL
// - Puede usar procedural u orientado a objetos
// - Ligeramente más rápido para MySQL
// - Menos portable
//
// EJEMPLO DE PORTABILIDAD:
// // Cambiar de MySQL a PostgreSQL con PDO:
// // Solo cambiar el DSN:
// $pdo = new PDO("pgsql:host=...", $user, $pass);
// // El resto del código sigue igual
//
// // Con MySQLi necesitarías reescribir todo el código

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// ========================================================================
// 📌 EXPLICACIÓN DE setAttribute()
// ========================================================================
// Configura atributos del objeto PDO.
//
// SINTAXIS:
// $pdo->setAttribute(atributo, valor)
//
// ATRIBUTOS COMUNES:
//
// PDO::ATTR_ERRMODE = Modo de manejo de errores
//   - PDO::ERRMODE_SILENT = No muestra errores (predeterminado)
//   - PDO::ERRMODE_WARNING = Muestra warnings
//   - PDO::ERRMODE_EXCEPTION = Lanza excepciones (RECOMENDADO)
//
// PDO::ATTR_DEFAULT_FETCH_MODE = Modo de obtención de datos
//   - PDO::FETCH_ASSOC = Array asociativo
//   - PDO::FETCH_NUM = Array numérico
//   - PDO::FETCH_OBJ = Objeto
//
// PDO::ATTR_EMULATE_PREPARES = Emular sentencias preparadas
//   - true = Emular (menos seguro)
//   - false = Usar nativas (más seguro)
//
// ¿POR QUÉ USAR ERRMODE_EXCEPTION?
// Para poder usar try-catch y manejar errores elegantemente.
//
// EJEMPLO:
// try {
//     $stmt = $pdo->query("SELECT * FROM tabla_inexistente");
// } catch (PDOException $e) {
//     echo "Error: " . $e->getMessage();
// }

// ============================================================================
// INCLUIR PHPMAILER
// ============================================================================

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// ========================================================================
// 📌 EXPLICACIÓN DE use (NAMESPACES)
// ========================================================================
// use importa clases de un namespace.
//
// ¿QUÉ ES UN NAMESPACE?
// Un namespace es como una carpeta para organizar clases.
// Evita conflictos de nombres entre clases.
//
// SINTAXIS:
// use Namespace\Clase;
//
// EJEMPLO:
// use PHPMailer\PHPMailer\PHPMailer;
// Ahora podemos usar: new PHPMailer()
// Sin use tendríamos que usar: new \PHPMailer\PHPMailer\PHPMailer()
//
// ANALOGÍA:
// Es como importar en Python:
// from PHPMailer.PHPMailer import PHPMailer
//
// ALIAS:
// use PHPMailer\PHPMailer\PHPMailer as Mailer;
// new Mailer(); // En vez de new PHPMailer()
//
// MÚLTIPLES IMPORTS:
// use PHPMailer\PHPMailer\{PHPMailer, SMTP, Exception};
// Importa las 3 clases del mismo namespace

$mensaje = "";
$tipo_mensaje = "";

// ============================================================================
// PASO 1: USUARIO SOLICITA RECUPERAR CONTRASEÑA
// ============================================================================

if (isset($_POST['correo']) && !isset($_POST['nueva_password'])) {
    // ====================================================================
    // 📌 EXPLICACIÓN DE LA CONDICIÓN
    // ====================================================================
    // isset($_POST['correo']) = Viene el campo correo del formulario
    // !isset($_POST['nueva_password']) = NO viene el campo nueva_password
    //
    // ¿POR QUÉ ESTA CONDICIÓN?
    // Porque este archivo maneja 2 formularios diferentes:
    // 1. Formulario para solicitar recuperación (solo tiene 'correo')
    // 2. Formulario para cambiar contraseña (tiene 'nueva_password' y 'token')
    //
    // Esta condición identifica que es el PRIMER formulario.
    
    $correo = trim($_POST['correo']);
    
    // ====================================================================
    // BUSCAR USUARIO EN LA BASE DE DATOS
    // ====================================================================
    
    $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE correo = ?");
    // ====================================================================
    // 📌 DIFERENCIA prepare() EN PDO vs MySQLi
    // ====================================================================
    // PDO:
    // $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
    // $stmt->execute([$correo]);
    //
    // MySQLi:
    // $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    // $stmt->bind_param("s", $correo);
    // $stmt->execute();
    //
    // VENTAJA DE PDO:
    // No necesita bind_param(), pasa los valores directamente en execute()
    
    $stmt->execute([$correo]);
    // ====================================================================
    // 📌 EXPLICACIÓN DE execute([])
    // ====================================================================
    // En PDO, pasamos los valores como array a execute().
    //
    // SINTAXIS:
    // $stmt->execute([valor1, valor2, ...])
    //
    // EJEMPLO:
    // $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ? AND activo = ?");
    // $stmt->execute([$correo, 1]);
    //
    // ORDEN IMPORTANTE:
    // El orden del array debe coincidir con el orden de los ?
    //
    // ALTERNATIVA (NAMED PARAMETERS):
    // $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    // $stmt->execute(['correo' => $correo]);
    //
    // VENTAJA DE NAMED PARAMETERS:
    // Más legible, no importa el orden
    
    $usuario = $stmt->fetch();
    // ====================================================================
    // 📌 EXPLICACIÓN DE fetch() EN PDO
    // ====================================================================
    // fetch() obtiene la siguiente fila del resultado.
    //
    // RETORNA:
    // - Array asociativo por defecto
    // - false si no hay más filas
    //
    // MODOS DE FETCH:
    // fetch(PDO::FETCH_ASSOC) = ['id' => 5, 'nombre' => 'Juan']
    // fetch(PDO::FETCH_NUM) = [5, 'Juan']
    // fetch(PDO::FETCH_OBJ) = objeto con propiedades
    //
    // DIFERENCIA CON MySQLi:
    // MySQLi: $stmt->get_result()->fetch_assoc()
    // PDO: $stmt->fetch()
    //
    // OBTENER TODAS LAS FILAS:
    // $usuarios = $stmt->fetchAll();
    
    if ($usuario) {
        // Si encontramos al usuario
        
        // ================================================================
        // GENERAR TOKEN ÚNICO Y SEGURO
        // ================================================================
        
        $token = bin2hex(random_bytes(32));
        // ================================================================
        // 📌 EXPLICACIÓN DE random_bytes() y bin2hex()
        // ================================================================
        // random_bytes(n) genera n bytes aleatorios criptográficamente seguros.
        //
        // EJEMPLO:
        // random_bytes(32) = 32 bytes aleatorios
        // Resultado: "\x3a\x9f\x2b..." (datos binarios)
        //
        // PROBLEMA:
        // Los bytes binarios no se pueden usar en URLs o emails.
        //
        // SOLUCIÓN:
        // bin2hex() convierte bytes binarios a hexadecimal.
        //
        // EJEMPLO:
        // random_bytes(32) = 32 bytes
        // bin2hex(random_bytes(32)) = 64 caracteres hexadecimales
        //
        // ¿POR QUÉ 64 CARACTERES?
        // Cada byte se convierte en 2 caracteres hexadecimales.
        // 32 bytes × 2 = 64 caracteres
        //
        // EJEMPLO DE TOKEN:
        // "a3f5b2c8d1e4f7a9b0c3d6e9f2a5b8c1d4e7f0a3b6c9d2e5f8a1b4c7d0e3f6a9"
        //
        // SEGURIDAD:
        // - Criptográficamente seguro (no predecible)
        // - 2^256 combinaciones posibles (prácticamente imposible de adivinar)
        //
        // ALTERNATIVAS INSEGURAS (NO USAR):
        // rand() = Predecible, no seguro
        // mt_rand() = Mejor que rand() pero no criptográfico
        // uniqid() = Basado en tiempo, predecible
        //
        // USOS:
        // - Tokens de recuperación de contraseña
        // - Tokens de verificación de email
        // - Tokens CSRF
        // - Claves de API
        
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        // ================================================================
        // 📌 EXPLICACIÓN DE strtotime() y date()
        // ================================================================
        // strtotime() convierte texto a timestamp Unix.
        //
        // SINTAXIS:
        // strtotime(texto_fecha)
        //
        // EJEMPLOS:
        // strtotime('now') = timestamp actual
        // strtotime('+1 hour') = timestamp dentro de 1 hora
        // strtotime('+1 day') = timestamp dentro de 1 día
        // strtotime('+1 week') = timestamp dentro de 1 semana
        // strtotime('+1 month') = timestamp dentro de 1 mes
        // strtotime('+1 year') = timestamp dentro de 1 año
        // strtotime('-1 hour') = timestamp hace 1 hora
        // strtotime('2025-12-31') = timestamp de esa fecha
        // strtotime('next Monday') = timestamp del próximo lunes
        //
        // TIMESTAMP UNIX:
        // Número de segundos desde el 1 de enero de 1970 00:00:00 UTC
        // Ejemplo: 1705334400 = 15 de enero de 2025
        //
        // date() formatea un timestamp a texto legible.
        //
        // SINTAXIS:
        // date(formato, timestamp)
        //
        // FORMATOS COMUNES:
        // 'Y-m-d' = 2025-01-15
        // 'Y-m-d H:i:s' = 2025-01-15 14:30:45
        // 'd/m/Y' = 15/01/2025
        // 'l, d F Y' = Tuesday, 15 January 2025
        // 'H:i' = 14:30
        //
        // CARACTERES DE FORMATO:
        // Y = Año con 4 dígitos (2025)
        // y = Año con 2 dígitos (25)
        // m = Mes con 2 dígitos (01-12)
        // n = Mes sin cero inicial (1-12)
        // d = Día con 2 dígitos (01-31)
        // j = Día sin cero inicial (1-31)
        // H = Hora 24h con 2 dígitos (00-23)
        // h = Hora 12h con 2 dígitos (01-12)
        // i = Minutos (00-59)
        // s = Segundos (00-59)
        // A = AM/PM
        //
        // EJEMPLO COMPLETO:
        // $ahora = time(); // Timestamp actual
        // $en_una_hora = strtotime('+1 hour');
        // $texto = date('Y-m-d H:i:s', $en_una_hora);
        // Resultado: "2025-01-15 15:30:45"
        
        // ================================================================
        // GUARDAR TOKEN EN LA BASE DE DATOS
        // ================================================================
        
        $stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, token_expira = ? WHERE correo = ?");
        // ================================================================
        // 📌 EXPLICACIÓN DE UPDATE
        // ================================================================
        // UPDATE modifica filas existentes en una tabla.
        //
        // SINTAXIS:
        // UPDATE tabla SET columna1 = valor1, columna2 = valor2 WHERE condicion
        //
        // IMPORTANTE:
        // - SET especifica qué columnas actualizar
        // - WHERE especifica qué filas actualizar
        // - SIN WHERE actualiza TODAS las filas (¡peligroso!)
        //
        // EJEMPLO SIN WHERE (MAL):
        // UPDATE usuarios SET reset_token = 'abc123'
        // Esto pondría el mismo token a TODOS los usuarios
        //
        // CON WHERE (BIEN):
        // UPDATE usuarios SET reset_token = 'abc123' WHERE correo = 'juan@email.com'
        // Solo actualiza el usuario con ese correo
        //
        // MÚLTIPLES COLUMNAS:
        // UPDATE usuarios SET nombre = 'Juan', edad = 30 WHERE id = 5
        //
        // RETORNO:
        // No devuelve filas, pero podemos obtener:
        // $stmt->rowCount() = Número de filas afectadas
        
        if ($stmt->execute([$token, $expiracion, $correo])) {
            // Si se guardó correctamente
            
            // ============================================================
            // PREPARAR Y ENVIAR EMAIL CON PHPMAILER
            // ============================================================
            
            $enlace = "http://localhost/lab/forms/recuperar.php?token=$token";
            // ========================================================
            // 📌 EXPLICACIÓN DEL ENLACE
            // ========================================================
            // El enlace incluye el token como parámetro GET.
            //
            // ESTRUCTURA:
            // http://localhost/lab/forms/recuperar.php?token=abc123...
            //
            // CUANDO EL USUARIO HACE CLICK:
            // El navegador abre esa URL
            // PHP recibe el token en $_GET['token']
            //
            // SEGURIDAD:
            // - El token es aleatorio (imposible de adivinar)
            // - Expira en 1 hora
            // - Solo se puede usar una vez
            
            $mail = new PHPMailer(true);
            // ========================================================
            // 📌 EXPLICACIÓN DE new PHPMailer(true)
            // ========================================================
            // Crea un nuevo objeto PHPMailer.
            //
            // PARÁMETRO true:
            // Habilita excepciones (lanza errores como Exception)
            // Sin true, los errores se manejan con if/else
            //
            // CON EXCEPCIONES (true):
            // try {
            //     $mail->send();
            // } catch (Exception $e) {
            //     echo "Error: " . $e->getMessage();
            // }
            //
            // SIN EXCEPCIONES (false o sin parámetro):
            // if (!$mail->send()) {
            //     echo "Error: " . $mail->ErrorInfo;
            // }
            
            try {
                // ====================================================
                // CONFIGURACIÓN DEL SERVIDOR SMTP
                // ====================================================
                
                $mail->isSMTP();
                // ================================================
                // 📌 EXPLICACIÓN DE SMTP
                // ================================================
                // SMTP = Simple Mail Transfer Protocol
                // Protocolo estándar para enviar correos electrónicos.
                //
                // isSMTP() le dice a PHPMailer que use SMTP.
                //
                // ALTERNATIVA:
                // $mail->isMail() = Usar función mail() de PHP (menos confiable)
                // $mail->isSendmail() = Usar sendmail (solo Linux)
                
                $mail->Host = 'smtp.gmail.com';
                // ================================================
                // 📌 SERVIDORES SMTP COMUNES
                // ================================================
                // Gmail: smtp.gmail.com
                // Outlook/Hotmail: smtp-mail.outlook.com
                // Yahoo: smtp.mail.yahoo.com
                // Office 365: smtp.office365.com
                
                $mail->SMTPAuth = true;
                // Requiere autenticación (usuario y contraseña)
                
                $mail->Username = 'lab.explorer2025@gmail.com';
                // Correo desde el que enviamos
                
                $mail->Password = 'yero ewft jacf vjzp';
                // ================================================
                // 📌 CONTRASEÑA DE APLICACIÓN DE GMAIL
                // ================================================
                // NO es la contraseña normal de Gmail.
                // Es una contraseña especial para aplicaciones.
                //
                // ¿CÓMO OBTENERLA?
                // 1. Ir a myaccount.google.com
                // 2. Seguridad
                // 3. Verificación en 2 pasos (debe estar activada)
                // 4. Contraseñas de aplicaciones
                // 5. Generar nueva contraseña
                //
                // FORMATO:
                // 16 caracteres separados en grupos de 4
                // Ejemplo: "abcd efgh ijkl mnop"
                //
                // SEGURIDAD:
                // - Cada contraseña es única para cada aplicación
                // - Se puede revocar sin cambiar la contraseña principal
                // - No da acceso completo a la cuenta
                
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                // ================================================
                // 📌 TIPOS DE ENCRIPTACIÓN
                // ================================================
                // PHPMailer::ENCRYPTION_STARTTLS = TLS (puerto 587)
                // PHPMailer::ENCRYPTION_SMTPS = SSL (puerto 465)
                //
                // DIFERENCIA:
                // STARTTLS: Inicia sin encriptar, luego actualiza a TLS
                // SMTPS: Encriptado desde el inicio
                //
                // RECOMENDACIÓN:
                // Usar STARTTLS (más compatible)
                
                $mail->Port = 587;
                // Puerto para STARTTLS
                // Puerto 465 para SMTPS
                
                $mail->CharSet = 'UTF-8';
                // ================================================
                // 📌 CHARSET PARA EMAILS
                // ================================================
                // UTF-8 soporta todos los caracteres:
                // - Acentos: á, é, í, ó, ú
                // - Ñ
                // - Emojis: 😀, 🎉, ❤️
                // - Caracteres especiales: €, £, ¥
                
                $mail->Encoding = 'base64';
                // ================================================
                // 📌 CODIFICACIÓN DEL CONTENIDO
                // ================================================
                // base64 = Codifica el contenido en base64
                // Asegura que caracteres especiales se transmitan correctamente
                //
                // ALTERNATIVAS:
                // '7bit' = Solo caracteres ASCII (no usar con UTF-8)
                // '8bit' = Permite caracteres extendidos
                // 'quoted-printable' = Codifica solo caracteres especiales
                
                // ====================================================
                // CONFIGURAR REMITENTE Y DESTINATARIO
                // ====================================================
                
                $mail->setFrom('lab.explorer2025@gmail.com', 'Restablecer password');
                // ================================================
                // 📌 setFrom()
                // ================================================
                // Establece el remitente del correo.
                //
                // SINTAXIS:
                // setFrom(email, nombre)
                //
                // EJEMPLO:
                // setFrom('noreply@ejemplo.com', 'Mi Aplicación')
                //
                // APARECE EN EL EMAIL COMO:
                // De: Mi Aplicación <noreply@ejemplo.com>
                
                $mail->addAddress($correo, $usuario['nombre']);
                // ================================================
                // 📌 addAddress()
                // ================================================
                // Agrega un destinatario.
                //
                // SINTAXIS:
                // addAddress(email, nombre)
                //
                // MÚLTIPLES DESTINATARIOS:
                // $mail->addAddress('juan@email.com', 'Juan');
                // $mail->addAddress('maria@email.com', 'María');
                //
                // OTROS MÉTODOS:
                // addCC('email@ejemplo.com') = Copia (CC)
                // addBCC('email@ejemplo.com') = Copia oculta (BCC)
                // addReplyTo('email@ejemplo.com') = Responder a
                
                // ====================================================
                // CONFIGURAR CONTENIDO DEL EMAIL
                // ====================================================
                
                $mail->isHTML(true);
                // ================================================
                // 📌 isHTML()
                // ================================================
                // Indica que el correo tiene formato HTML.
                //
                // CON isHTML(true):
                // Podemos usar <h1>, <p>, <a>, <img>, etc.
                //
                // SIN isHTML() o isHTML(false):
                // Solo texto plano, sin formato
                
                $mail->Subject = "Restablecer password Lab Explorer";
                // Asunto del correo
                
                $mail->addEmbeddedImage('../assets/img/logo/logo-lab.ico', 'logoLab');
                // ================================================
                // 📌 addEmbeddedImage()
                // ================================================
                // Incrusta una imagen en el correo.
                //
                // SINTAXIS:
                // addEmbeddedImage(ruta, cid)
                //
                // PARÁMETROS:
                // ruta = Ruta del archivo de imagen
                // cid = Content ID (identificador único)
                //
                // USO EN HTML:
                // <img src="cid:logoLab">
                //
                // DIFERENCIA CON ADJUNTOS:
                // addEmbeddedImage() = Imagen visible en el cuerpo
                // addAttachment() = Archivo adjunto para descargar
                
                $mail->Body = "
                    <center>
                        <img src='cid:logoLab' width='150' style='margin-bottom:20px;'>
                    </center>

                    <h2>Recuperación de contraseña</h2>
                    Hola <strong>{$usuario['nombre']}</strong>,<br><br>

                    Has solicitado recuperar tu contraseña.<br><br>

                    <!-- Botón para restablecer -->
                    <a href='$enlace' 
                       style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;font-weight:bold;'>
                       Restablecer contraseña
                    </a>

                    <br><br>
                    Si el botón no funciona abre este enlace:<br>
                    $enlace
                    <br><br>
                    Este enlace expira en 1 hora.<br>
                    <strong>Si tú no solicitaste el cambio de contraseña, ignora este correo.</strong>
                ";
                // ================================================
                // 📌 INTERPOLACIÓN DE VARIABLES EN STRINGS
                // ================================================
                // {$variable} = Interpola variable en string
                //
                // EJEMPLO:
                // $nombre = "Juan";
                // echo "Hola {$nombre}"; // Hola Juan
                //
                // TAMBIÉN FUNCIONA:
                // echo "Hola $nombre"; // Hola Juan
                //
                // PERO {} ES MÁS CLARO CON ARRAYS:
                // echo "Hola {$usuario['nombre']}"; // Funciona
                // echo "Hola $usuario['nombre']"; // Error de sintaxis
                
                $mail->AltBody = "Hola {$usuario['nombre']}, usa este enlace para recuperar tu contraseña: $enlace";
                // ================================================
                // 📌 AltBody
                // ================================================
                // Versión en texto plano del correo.
                //
                // ¿PARA QUÉ?
                // Algunos clientes de correo no soportan HTML.
                // AltBody se muestra en esos casos.
                //
                // BUENA PRÁCTICA:
                // Siempre incluir AltBody cuando uses HTML.
                
                $mail->send();
                // Envía el correo
                
                $mensaje = "Se ha enviado un correo con el enlace para recuperar tu contraseña.";
                $tipo_mensaje = "success";
                
            } catch (Exception $e) {
                // Si hubo un error al enviar el correo
                $mensaje = "No se pudo enviar el correo: " . $mail->ErrorInfo;
                // ================================================
                // 📌 ErrorInfo
                // ================================================
                // Propiedad que contiene información del error.
                //
                // ERRORES COMUNES:
                // - "SMTP connect() failed" = No puede conectar al servidor
                // - "Invalid address" = Email inválido
                // - "Authentication failed" = Usuario/contraseña incorrectos
                // - "Could not instantiate mail function" = mail() no disponible
                
                $tipo_mensaje = "error";
            }
        }
    } else {
        // Si el correo no existe en la base de datos
        $mensaje = "Ese correo no está registrado.";
        $tipo_mensaje = "error";
    }
}

// ============================================================================
// PASO 2: VERIFICAR TOKEN DEL ENLACE
// ============================================================================

$token_valido = false;

if (isset($_GET['token'])) {
    // ====================================================================
    // 📌 EXPLICACIÓN DE $_GET
    // ====================================================================
    // $_GET es un array con parámetros de la URL.
    //
    // EJEMPLO DE URL:
    // http://localhost/recuperar.php?token=abc123&lang=es
    //
    // $_GET:
    // [
    //     'token' => 'abc123',
    //     'lang' => 'es'
    // ]
    //
    // ACCESO:
    // $_GET['token'] = 'abc123'
    // $_GET['lang'] = 'es'
    //
    // DIFERENCIA CON $_POST:
    // $_GET = Datos en la URL (visibles)
    // $_POST = Datos en el cuerpo (ocultos)
    //
    // CUÁNDO USAR CADA UNO:
    // $_GET: Enlaces, filtros, búsquedas, paginación
    // $_POST: Formularios, datos sensibles, crear/actualizar
    
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND token_expira > NOW()");
    // ====================================================================
    // 📌 EXPLICACIÓN DE NOW() EN MYSQL
    // ====================================================================
    // NOW() devuelve la fecha y hora actual del servidor MySQL.
    //
    // FORMATO:
    // 'YYYY-MM-DD HH:MM:SS'
    // Ejemplo: '2025-01-15 14:30:45'
    //
    // COMPARACIÓN:
    // token_expira > NOW()
    // Verifica que el token NO haya expirado.
    //
    // EJEMPLO:
    // token_expira = '2025-01-15 15:30:00'
    // NOW() = '2025-01-15 14:30:00'
    // 15:30 > 14:30 = true (token válido)
    //
    // token_expira = '2025-01-15 13:30:00'
    // NOW() = '2025-01-15 14:30:00'
    // 13:30 > 14:30 = false (token expirado)
    //
    // FUNCIONES RELACIONADAS:
    // NOW() = Fecha y hora actual
    // CURDATE() = Solo fecha actual
    // CURTIME() = Solo hora actual
    // UTC_TIMESTAMP() = Fecha/hora en UTC
    
    $stmt->execute([$token]);
    $token_valido = $stmt->fetch();
    // Si devuelve algo, el token es válido
}

// ============================================================================
// PASO 3: CAMBIAR CONTRASEÑA
// ============================================================================

if (isset($_POST['nueva_password']) && isset($_POST['token'])) {
    // Si viene la nueva contraseña y el token
    
    $nueva_password = $_POST['nueva_password'];
    $token = $_POST['token'];
    
    // Validar longitud de contraseña
    if (strlen($nueva_password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";
    } else {
        // Hashear la nueva contraseña
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Actualizar contraseña y eliminar token
        $stmt = $pdo->prepare("UPDATE usuarios SET contrasena_hash = ?, reset_token = NULL, token_expira = NULL WHERE reset_token = ?");
        // ================================================================
        // 📌 EXPLICACIÓN DE NULL EN SQL
        // ================================================================
        // NULL = Ausencia de valor (diferente de vacío o cero)
        //
        // EJEMPLOS:
        // reset_token = NULL (no tiene token)
        // reset_token = '' (string vacío, diferente de NULL)
        // reset_token = 0 (cero, diferente de NULL)
        //
        // VERIFICAR NULL:
        // WHERE columna IS NULL (correcto)
        // WHERE columna = NULL (incorrecto, siempre false)
        //
        // ¿POR QUÉ PONER NULL?
        // Para invalidar el token después de usarlo.
        // Así no se puede usar el mismo enlace dos veces.
        
        if ($stmt->execute([$password_hash, $token])) {
            $mensaje = "Contraseña actualizada correctamente. Ya puedes iniciar sesión.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar la contraseña.";
            $tipo_mensaje = "error";
        }
    }
}
?>
```

### 🔑 Conceptos Clave Resumidos

#### Flujo Completo
```
1. Usuario solicita recuperación → Genera token
2. Token se guarda en BD con expiración
3. Email enviado con PHPMailer
4. Usuario hace click → Valida token
5. Usuario cambia contraseña → Token se invalida
```

#### Seguridad Implementada
- ✅ Token aleatorio criptográfico (random_bytes)
- ✅ Expiración de 1 hora
- ✅ Token se invalida después de usar
- ✅ Contraseña hasheada con bcrypt
- ✅ Validación de longitud de contraseña

---

*Continuará en Parte 5...*
