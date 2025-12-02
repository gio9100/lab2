# 📚 GUÍA TÉCNICA 3.0 - PARTE 3
## Autenticación de Usuarios: Login, Registro y Logout

---

# 🔐 SISTEMA DE AUTENTICACIÓN

## `inicio-sesion.php` - Login de Usuarios

### 🎯 Propósito
Permite a los usuarios normales iniciar sesión en el sistema. Valida credenciales, verifica si es administrador y establece la sesión.

### 📋 Código Completo Explicado

```php
<?php
// ============================================================================
// SECCIÓN 1: INICIALIZACIÓN
// ============================================================================

// Abrimos PHP
session_start();
// ========================================================================
// 📌 EXPLICACIÓN DE session_start()
// ========================================================================
// session_start() DEBE ser lo primero en ejecutarse.
// ¿Por qué? Porque PHP necesita enviar una cookie al navegador.
// Las cookies se envían en los HEADERS HTTP.
// Si ya enviamos HTML (echo, print, o cualquier salida), los headers
// ya se enviaron y session_start() fallará con un WARNING.
//
// ¿QUÉ HACE session_start()?
// 1. Busca una cookie llamada PHPSESSID en el navegador
// 2. Si existe, carga los datos de sesión del servidor
// 3. Si no existe, crea una nueva sesión con ID único
//
// EJEMPLO DE PHPSESSID:
// PHPSESSID=abc123def456ghi789 (cadena aleatoria de 32 caracteres)

require_once "usuario.php";
// ========================================================================
// 📌 EXPLICACIÓN DE require_once
// ========================================================================
// require_once incluye un archivo PHP.
//
// DIFERENCIAS ENTRE include, require, include_once, require_once:
//
// include: Incluye archivo, si falla muestra WARNING y continúa
// require: Incluye archivo, si falla muestra ERROR FATAL y detiene todo
// include_once: Como include pero solo una vez (evita duplicados)
// require_once: Como require pero solo una vez
//
// ¿CUÁNDO USAR CADA UNO?
// - require_once: Para archivos críticos (conexión BD, funciones core)
// - include_once: Para archivos opcionales (widgets, componentes)
// - require: Rara vez (mejor usar require_once)
// - include: Rara vez (mejor usar include_once)

require_once "conexion.php";
// Traemos el archivo de conexión a la base de datos

// ============================================================================
// SECCIÓN 2: VARIABLES DE CONTROL
// ============================================================================

$mensaje = "";
// ========================================================================
// 📌 EXPLICACIÓN DE VARIABLES DE CONTROL
// ========================================================================
// Creamos variables vacías para controlar el flujo del programa.
// $mensaje guardará mensajes de error o éxito para mostrar al usuario.
//
// ¿POR QUÉ INICIALIZAR EN ""?
// Para evitar errores de "undefined variable" si intentamos usarla
// antes de asignarle un valor.

$exito = false;
// Variable booleana que indica si el login fue exitoso.
// false = login falló
// true = login exitoso

// ============================================================================
// SECCIÓN 3: PROCESAR FORMULARIO
// ============================================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ====================================================================
    // 📌 EXPLICACIÓN DE $_SERVER["REQUEST_METHOD"]
    // ====================================================================
    // $_SERVER es un array global que contiene información del servidor.
    // REQUEST_METHOD indica cómo se accedió a la página:
    //
    // "GET" = El usuario visitó la página normalmente (escribió URL o click en link)
    // "POST" = El usuario envió un formulario
    // "PUT" = Actualizar recurso (APIs REST)
    // "DELETE" = Eliminar recurso (APIs REST)
    //
    // ¿POR QUÉ VERIFICAR REQUEST_METHOD?
    // Para ejecutar código solo cuando se envía el formulario.
    // Si no verificamos, el código se ejecutaría al cargar la página.
    //
    // COMPARACIÓN === vs ==:
    // === compara valor Y tipo (más estricto)
    // == solo compara valor
    // "POST" === "POST" = true
    // "POST" == "post" = false (case-sensitive)
    
    // ====================================================================
    // PASO 1: OBTENER DATOS DEL FORMULARIO
    // ====================================================================
    
    $correo = trim($_POST["correo"] ?? "");
    // ====================================================================
    // 📌 EXPLICACIÓN DE trim()
    // ====================================================================
    // trim() elimina espacios en blanco al inicio y final de un string.
    //
    // EJEMPLO:
    // trim("  juan@email.com  ") = "juan@email.com"
    // trim("\n\tjuan@email.com\n") = "juan@email.com"
    //
    // ¿POR QUÉ USAR trim()?
    // Los usuarios pueden copiar/pegar con espacios accidentales.
    // "juan@email.com " !== "juan@email.com" (son diferentes)
    //
    // CARACTERES QUE ELIMINA:
    // - Espacio: " "
    // - Tab: "\t"
    // - Salto de línea: "\n"
    // - Retorno de carro: "\r"
    // - NULL byte: "\0"
    // - Salto de línea vertical: "\x0B"
    
    // ====================================================================
    // 📌 EXPLICACIÓN DE $_POST
    // ====================================================================
    // $_POST es un array global que contiene datos enviados por formulario.
    //
    // ESTRUCTURA:
    // $_POST = [
    //     'correo' => 'juan@email.com',
    //     'contrasena' => 'abc123'
    // ]
    //
    // ACCESO:
    // $_POST["correo"] = "juan@email.com"
    // $_POST["contrasena"] = "abc123"
    //
    // ¿DE DÓNDE VIENE?
    // Del formulario HTML:
    // <input type="email" name="correo">
    // <input type="password" name="contrasena">
    //
    // El atributo name="correo" se convierte en la clave del array.
    
    // ====================================================================
    // 📌 EXPLICACIÓN DEL OPERADOR ?? (NULL COALESCING)
    // ====================================================================
    // ?? devuelve el primer valor que no sea null.
    //
    // SINTAXIS:
    // $variable = $valor1 ?? $valor2 ?? $valor3;
    //
    // EJEMPLO:
    // $_POST["correo"] ?? "" 
    // Si $_POST["correo"] existe y no es null, usa ese valor
    // Si no existe o es null, usa ""
    //
    // SIN ??:
    // if (isset($_POST["correo"])) {
    //     $correo = $_POST["correo"];
    // } else {
    //     $correo = "";
    // }
    //
    // CON ??:
    // $correo = $_POST["correo"] ?? "";
    //
    // VENTAJA:
    // Código más corto y legible.
    // Evita errores de "undefined index".
    
    $contrasena = $_POST["contrasena"] ?? "";
    // Obtenemos la contraseña del formulario
    
    // ====================================================================
    // PASO 2: VALIDACIONES
    // ====================================================================
    
    if ($correo === "" || $contrasena === "") {
        // ================================================================
        // 📌 EXPLICACIÓN DEL OPERADOR || (OR LÓGICO)
        // ================================================================
        // || devuelve true si AL MENOS UNA condición es verdadera.
        //
        // TABLA DE VERDAD:
        // true  || true  = true
        // true  || false = true
        // false || true  = true
        // false || false = false
        //
        // EJEMPLO:
        // $correo === "" || $contrasena === ""
        // Si el correo está vacío O la contraseña está vacía = true
        //
        // DIFERENCIA CON &&:
        // && requiere que AMBAS condiciones sean verdaderas
        // || requiere que AL MENOS UNA sea verdadera
        
        $mensaje = "Ingresa Tu Correo Y Contraseña";
        // Guardamos un mensaje de error
        
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        // ================================================================
        // 📌 EXPLICACIÓN DE filter_var()
        // ================================================================
        // filter_var() valida y filtra variables.
        //
        // SINTAXIS:
        // filter_var($variable, $filtro)
        //
        // FILTROS COMUNES:
        // FILTER_VALIDATE_EMAIL = Valida formato de email
        // FILTER_VALIDATE_URL = Valida formato de URL
        // FILTER_VALIDATE_INT = Valida que sea número entero
        // FILTER_SANITIZE_STRING = Elimina tags HTML
        //
        // RETORNO:
        // - Si es válido: devuelve el valor filtrado
        // - Si es inválido: devuelve false
        //
        // EJEMPLO:
        // filter_var("juan@email.com", FILTER_VALIDATE_EMAIL) = "juan@email.com"
        // filter_var("correo_invalido", FILTER_VALIDATE_EMAIL) = false
        //
        // ¿QUÉ VALIDA FILTER_VALIDATE_EMAIL?
        // - Debe tener @
        // - Debe tener dominio (.com, .mx, etc.)
        // - No puede tener espacios
        // - Debe seguir el formato RFC 5322
        //
        // EMAILS VÁLIDOS:
        // juan@email.com ✓
        // juan.perez@email.com.mx ✓
        // juan+trabajo@email.com ✓
        //
        // EMAILS INVÁLIDOS:
        // juan@email ✗ (sin dominio)
        // juan email.com ✗ (sin @)
        // juan@@email.com ✗ (doble @)
        
        // ================================================================
        // 📌 EXPLICACIÓN DEL OPERADOR ! (NOT)
        // ================================================================
        // ! invierte el valor booleano.
        //
        // TABLA DE VERDAD:
        // !true = false
        // !false = true
        //
        // EJEMPLO:
        // !filter_var($correo, FILTER_VALIDATE_EMAIL)
        // Si filter_var devuelve false (inválido), ! lo convierte en true
        // Entonces entramos al if
        
        $mensaje = "correo invalido";
        // Mensaje de error para correo inválido
        
    } else {
        // ================================================================
        // PASO 3: BUSCAR USUARIO EN LA BASE DE DATOS
        // ================================================================
        
        $sql = "SELECT id, nombre, correo, contrasena_hash FROM usuarios WHERE correo = ?";
        // ================================================================
        // 📌 EXPLICACIÓN DE LA CONSULTA SQL
        // ================================================================
        // SELECT id, nombre, correo, contrasena_hash
        // - Seleccionamos solo las columnas que necesitamos
        // - No usamos SELECT * porque es menos eficiente
        //
        // FROM usuarios
        // - De la tabla usuarios
        //
        // WHERE correo = ?
        // - Filtramos por correo
        // - ? es un placeholder (marcador de posición)
        //
        // ¿POR QUÉ USAR ?
        // Para prevenir SQL Injection (ataques de seguridad).
        //
        // EJEMPLO DE ATAQUE SIN ?:
        // $sql = "SELECT * FROM usuarios WHERE correo = '$correo'";
        // Si $correo = "admin@email.com' OR '1'='1"
        // Query final: SELECT * FROM usuarios WHERE correo = 'admin@email.com' OR '1'='1'
        // Esto devolvería TODOS los usuarios (grave problema de seguridad)
        //
        // CON ?:
        // El valor se trata como DATO, no como código SQL.
        // Query final: SELECT * FROM usuarios WHERE correo = "admin@email.com' OR '1'='1"
        // Busca literalmente ese correo raro, no encuentra nada.
        
        $stmt = $conexion->prepare($sql);
        // Preparamos la consulta
        
        $stmt->bind_param("s", $correo);
        // ================================================================
        // 📌 EXPLICACIÓN DE bind_param("s", $correo)
        // ================================================================
        // Vinculamos el correo al placeholder ?.
        //
        // "s" = string (tipo de dato)
        //
        // TIPOS DISPONIBLES:
        // "s" = string (texto)
        // "i" = integer (número entero)
        // "d" = double (número decimal)
        // "b" = blob (datos binarios)
        //
        // EJEMPLOS:
        // bind_param("s", $nombre) = 1 string
        // bind_param("si", $nombre, $edad) = 1 string, 1 integer
        // bind_param("ssi", $nombre, $email, $id) = 2 strings, 1 integer
        //
        // ORDEN IMPORTANTE:
        // El orden de los parámetros debe coincidir con el orden de los ?
        
        $stmt->execute();
        // Ejecutamos la consulta
        
        $resultado = $stmt->get_result();
        // Obtenemos el resultado de la búsqueda
        
        // ================================================================
        // PASO 4: VERIFICAR SI ENCONTRAMOS AL USUARIO
        // ================================================================
        
        if ($resultado && $resultado->num_rows === 1) {
            // ============================================================
            // 📌 EXPLICACIÓN DE $resultado && $resultado->num_rows === 1
            // ============================================================
            // Verificamos DOS cosas:
            //
            // 1. $resultado = Que la consulta no falló
            // 2. $resultado->num_rows === 1 = Que encontró exactamente 1 usuario
            //
            // ¿POR QUÉ === 1 Y NO > 0?
            // Porque el correo debe ser ÚNICO.
            // Si encontramos 2 o más, hay duplicados (problema de integridad).
            //
            // OPERADOR &&:
            // Ambas condiciones deben ser verdaderas.
            // Si $resultado es false, ni siquiera evalúa num_rows (evita error).
            
            $usuario = $resultado->fetch_assoc();
            // ============================================================
            // 📌 EXPLICACIÓN DE fetch_assoc()
            // ============================================================
            // Obtiene la siguiente fila como array asociativo.
            //
            // RETORNA:
            // [
            //     'id' => 5,
            //     'nombre' => 'Juan Pérez',
            //     'correo' => 'juan@email.com',
            //     'contrasena_hash' => '$2y$10$abcd1234...'
            // ]
            //
            // DIFERENCIA CON fetch_row():
            // fetch_row() = [5, 'Juan Pérez', 'juan@email.com', '$2y$10$...']
            // fetch_assoc() = ['id' => 5, 'nombre' => 'Juan Pérez', ...]
            //
            // VENTAJA DE fetch_assoc():
            // Más legible: $usuario['nombre'] vs $usuario[1]
            
            // ============================================================
            // PASO 5: VERIFICAR CONTRASEÑA
            // ============================================================
            
            if (password_verify($contrasena, $usuario["contrasena_hash"])) {
                // ========================================================
                // 📌 EXPLICACIÓN DE password_verify()
                // ========================================================
                // Compara una contraseña en texto plano con un hash.
                //
                // PARÁMETROS:
                // 1. $contrasena: Contraseña ingresada (texto plano)
                // 2. $usuario["contrasena_hash"]: Hash guardado en BD
                //
                // RETORNA:
                // true si coinciden, false si no
                //
                // ¿CÓMO FUNCIONA INTERNAMENTE?
                // 1. Extrae el "salt" del hash
                // 2. Aplica bcrypt a la contraseña con ese salt
                // 3. Compara el resultado con el hash guardado
                //
                // EJEMPLO:
                // Contraseña: "abc123"
                // Hash en BD: "$2y$10$xyz789..."
                // password_verify("abc123", "$2y$10$xyz789...") = true
                // password_verify("abc124", "$2y$10$xyz789...") = false
                //
                // SEGURIDAD:
                // - Resistente a timing attacks
                // - No revela si el usuario existe o la contraseña es incorrecta
                // - Usa algoritmo bcrypt (muy seguro)
                
                // ====================================================
                // PASO 6: ESTABLECER SESIÓN
                // ====================================================
                
                $_SESSION["usuario_id"] = $usuario["id"];
                // ================================================
                // 📌 EXPLICACIÓN DE $_SESSION
                // ================================================
                // $_SESSION es un array global que persiste entre páginas.
                //
                // ¿CÓMO FUNCIONA?
                // 1. Los datos se guardan en el SERVIDOR (no en el navegador)
                // 2. El navegador solo guarda un ID de sesión (PHPSESSID)
                // 3. Cada vez que el usuario visita una página, PHP carga sus datos
                //
                // EJEMPLO:
                // Página 1: $_SESSION['usuario_id'] = 5;
                // Página 2: echo $_SESSION['usuario_id']; // 5
                //
                // VENTAJA VS COOKIES:
                // - Más seguro (datos en servidor, no en navegador)
                // - No tiene límite de tamaño (cookies max 4KB)
                // - No se puede manipular desde el navegador
                //
                // DESVENTAJA:
                // - Requiere que el servidor guarde datos
                // - Se pierde si el servidor se reinicia
                
                $_SESSION["usuario_nombre"] = $usuario["nombre"];
                // Guardamos el nombre del usuario en la sesión
                
                $_SESSION["usuario_correo"] = $usuario["correo"];
                // Guardamos el correo del usuario en la sesión
                
                // ====================================================
                // PASO 7: VERIFICAR SI ES ADMINISTRADOR
                // ====================================================
                
                $stmt_admin = $conexion->prepare("SELECT id FROM admins WHERE email = ? AND estado = 'activo'");
                // Buscamos en la tabla de admins si este correo está ahí
                
                $stmt_admin->bind_param("s", $usuario["correo"]);
                $stmt_admin->execute();
                $resultado_admin = $stmt_admin->get_result();
                
                $_SESSION["es_admin"] = ($resultado_admin && $resultado_admin->num_rows > 0);
                // ================================================
                // 📌 EXPLICACIÓN DE EXPRESIÓN BOOLEANA
                // ================================================
                // ($resultado_admin && $resultado_admin->num_rows > 0)
                // Esta expresión devuelve true o false.
                //
                // Si encontramos el correo en admins:
                // $_SESSION["es_admin"] = true
                //
                // Si no lo encontramos:
                // $_SESSION["es_admin"] = false
                //
                // PARÉNTESIS:
                // Los paréntesis aseguran que primero se evalúe la condición
                // y luego se asigne el resultado a $_SESSION["es_admin"].
                
                $stmt_admin->close();
                // Cerramos la consulta de admin
                
                $mensaje = " 🧪 Bienvenido a Lab-Explorer, " . $usuario["nombre"] . "!";
                // ================================================
                // 📌 EXPLICACIÓN DE CONCATENACIÓN
                // ================================================
                // El operador . une strings en PHP.
                //
                // EJEMPLO:
                // "Hola" . " " . "Mundo" = "Hola Mundo"
                // "Bienvenido, " . $nombre . "!" = "Bienvenido, Juan!"
                //
                // ALTERNATIVA (INTERPOLACIÓN):
                // $mensaje = " 🧪 Bienvenido a Lab-Explorer, {$usuario['nombre']}!";
                // O con comillas dobles:
                // $mensaje = " 🧪 Bienvenido a Lab-Explorer, $nombre!";
                
                $exito = true;
                // Marcamos que el login fue exitoso
                
            } else {
                // Si la contraseña no coincide
                $mensaje = " ⚠️Correo o contraseña incorrectos.";
                // ================================================
                // 📌 BUENA PRÁCTICA DE SEGURIDAD
                // ================================================
                // NO decimos "contraseña incorrecta" específicamente.
                // Decimos "correo o contraseña incorrectos".
                //
                // ¿POR QUÉ?
                // Para no revelar si el correo existe en la BD.
                // Si decimos "contraseña incorrecta", confirmamos que el correo existe.
                // Un atacante podría usar esto para enumerar usuarios.
            }
        } else {
            // Si no encontramos ningún usuario con ese correo
            $mensaje = " ⚠️Correo no encontrado.";
        }
        
        $stmt->close();
        // ====================================================
        // 📌 EXPLICACIÓN DE close()
        // ====================================================
        // Cierra la sentencia preparada y libera recursos.
        //
        // ¿POR QUÉ ES IMPORTANTE?
        // - Libera memoria del servidor
        // - Cierra la conexión con MySQL para esa consulta
        // - Buena práctica de programación
        //
        // ¿QUÉ PASA SI NO CERRAMOS?
        // PHP lo hace automáticamente al final del script,
        // pero es mejor hacerlo manualmente para liberar recursos antes.
    }
}
?>
```

### 🔑 Conceptos Clave Resumidos

#### Flujo de Login
```
1. Usuario envía formulario (POST)
2. Validar campos vacíos
3. Validar formato de email
4. Buscar usuario en BD por email
5. Verificar contraseña con password_verify()
6. Establecer sesión ($_SESSION)
7. Verificar si es admin
8. Redirigir o mostrar mensaje
```

#### Seguridad Implementada
- ✅ Sentencias preparadas (previene SQL Injection)
- ✅ password_verify() (contraseñas hasheadas)
- ✅ filter_var() (validación de email)
- ✅ trim() (elimina espacios)
- ✅ Mensajes genéricos (no revela info sensible)

---

## `register.php` - Registro de Usuarios

### 🎯 Propósito
Permite a nuevos usuarios crear una cuenta. Valida datos, verifica dominio de email y guarda el usuario con contraseña hasheada.

### 📋 Código Completo Explicado

```php
<?php 
// ============================================================================
// SECCIÓN 1: INICIALIZACIÓN
// ============================================================================

require_once("usuario.php");
require_once "conexion.php";

$mensaje = "";
$exito= false;

// ============================================================================
// SECCIÓN 2: LISTA DE DOMINIOS PERMITIDOS
// ============================================================================

$dominios_validos = [
    'gmail.com',
    'outlook.com',
    'outlook.es',
];
// ========================================================================
// 📌 EXPLICACIÓN DE ARRAYS EN PHP
// ========================================================================
// [] crea un array (lista de valores).
//
// SINTAXIS MODERNA (PHP 5.4+):
// $array = ['valor1', 'valor2', 'valor3'];
//
// SINTAXIS ANTIGUA:
// $array = array('valor1', 'valor2', 'valor3');
//
// ACCESO:
// $dominios_validos[0] = 'gmail.com'
// $dominios_validos[1] = 'outlook.com'
// $dominios_validos[2] = 'outlook.es'
//
// ¿POR QUÉ LIMITAR DOMINIOS?
// - Evitar emails temporales/desechables
// - Asegurar que sean emails reales
// - Facilitar verificación de identidad

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // ====================================================================
    // PASO 1: OBTENER Y LIMPIAR DATOS
    // ====================================================================
    
    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    
    $correo = mb_strtolower($correo, 'UTF-8');
    // ====================================================================
    // 📌 EXPLICACIÓN DE mb_strtolower()
    // ====================================================================
    // Convierte un string a minúsculas con soporte multibyte (UTF-8).
    //
    // DIFERENCIA CON strtolower():
    // strtolower() = Solo funciona con caracteres ASCII
    // mb_strtolower() = Funciona con acentos, ñ, emojis, etc.
    //
    // EJEMPLO:
    // strtolower("JOSÉ") = "josÉ" (no convierte É)
    // mb_strtolower("JOSÉ", 'UTF-8') = "josé" (convierte todo)
    //
    // ¿POR QUÉ CONVERTIR A MINÚSCULAS?
    // Para evitar duplicados:
    // Juan@Gmail.com
    // juan@gmail.com
    // JUAN@GMAIL.COM
    // Todos son el mismo correo, pero PHP los ve diferentes.
    //
    // PARÁMETROS:
    // 1. $correo: String a convertir
    // 2. 'UTF-8': Codificación de caracteres
    
    $contrasena = $_POST["contrasena"] ?? "";
    
    // ====================================================================
    // PASO 2: VALIDACIONES BÁSICAS
    // ====================================================================
    
    if ($nombre === "" || $correo === "" || $contrasena === "") {
        $mensaje = "Completa todos los campos";
    }
    elseif(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El correo no tiene un formato valido";
    }
    else {
        // ================================================================
        // PASO 3: VALIDAR DOMINIO DEL CORREO
        // ================================================================
        
        $partes_correo = explode('@', $correo);
        // ================================================================
        // 📌 EXPLICACIÓN DE explode()
        // ================================================================
        // Divide un string en partes usando un separador.
        //
        // SINTAXIS:
        // explode(separador, string)
        //
        // EJEMPLO:
        // explode('@', 'juan@gmail.com') = ['juan', 'gmail.com']
        // explode(' ', 'Hola Mundo') = ['Hola', 'Mundo']
        // explode('-', '2025-01-15') = ['2025', '01', '15']
        //
        // RETORNA:
        // Array con las partes separadas
        //
        // FUNCIÓN INVERSA:
        // implode() une un array en un string
        // implode('-', ['2025', '01', '15']) = '2025-01-15'
        
        $dominio = $partes_correo[1] ?? '';
        // Obtenemos la segunda parte (el dominio)
        // $partes_correo[0] = 'juan'
        // $partes_correo[1] = 'gmail.com'
        
        if(!in_array($dominio, $dominios_validos)) {
            // ============================================================
            // 📌 EXPLICACIÓN DE in_array()
            // ============================================================
            // Verifica si un valor existe en un array.
            //
            // SINTAXIS:
            // in_array(valor_a_buscar, array)
            //
            // RETORNA:
            // true si lo encuentra, false si no
            //
            // EJEMPLO:
            // in_array('gmail.com', ['gmail.com', 'outlook.com']) = true
            // in_array('yahoo.com', ['gmail.com', 'outlook.com']) = false
            //
            // TERCER PARÁMETRO (OPCIONAL):
            // in_array(valor, array, strict)
            // strict = true: Compara tipo y valor (===)
            // strict = false: Solo compara valor (==)
            //
            // EJEMPLO:
            // in_array('1', [1, 2, 3], false) = true (1 == '1')
            // in_array('1', [1, 2, 3], true) = false (1 !== '1')
            
            $dominios_lista = implode(',', array_slice($dominios_validos, 0, 5));
            // ========================================================
            // 📌 EXPLICACIÓN DE array_slice()
            // ========================================================
            // Extrae una porción de un array.
            //
            // SINTAXIS:
            // array_slice(array, inicio, longitud)
            //
            // EJEMPLO:
            // array_slice([1,2,3,4,5], 0, 3) = [1,2,3]
            // array_slice([1,2,3,4,5], 2, 2) = [3,4]
            //
            // PARÁMETROS:
            // 0 = Empezar desde el índice 0 (primer elemento)
            // 5 = Tomar máximo 5 elementos
            //
            // ¿POR QUÉ USAR array_slice?
            // Si tenemos muchos dominios (ej. 50), no queremos
            // mostrarlos todos en el mensaje de error.
            // Solo mostramos los primeros 5.
            
            // ========================================================
            // 📌 EXPLICACIÓN DE implode()
            // ========================================================
            // Une los elementos de un array en un string.
            //
            // SINTAXIS:
            // implode(separador, array)
            //
            // EJEMPLO:
            // implode(',', ['gmail.com', 'outlook.com']) = 'gmail.com,outlook.com'
            // implode(' - ', ['A', 'B', 'C']) = 'A - B - C'
            //
            // FUNCIÓN INVERSA:
            // explode() divide un string en array
            
            $mensaje = "Solo se permiten correos de dominio verificados como:" . $dominios_lista . ", etc.";
        }
        elseif (strlen($contrasena) < 6) {
            // ========================================================
            // 📌 EXPLICACIÓN DE strlen()
            // ========================================================
            // Cuenta el número de caracteres de un string.
            //
            // EJEMPLO:
            // strlen("abc123") = 6
            // strlen("Hola") = 4
            // strlen("") = 0
            //
            // IMPORTANTE CON UTF-8:
            // strlen("José") = 5 (cuenta la É como 2 bytes)
            // mb_strlen("José", 'UTF-8') = 4 (cuenta caracteres, no bytes)
            //
            // PARA CONTRASEÑAS:
            // strlen() está bien porque queremos contar bytes,
            // no caracteres visuales.
            
            $mensaje = "la contraseña debe tener al menos 6 caracteres";
        }
        else {
            // ============================================================
            // PASO 4: HASHEAR CONTRASEÑA
            // ============================================================
            
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
            // ========================================================
            // 📌 EXPLICACIÓN DETALLADA DE password_hash()
            // ========================================================
            // Convierte una contraseña en un hash seguro.
            //
            // PARÁMETROS:
            // 1. $contrasena: Contraseña en texto plano
            // 2. PASSWORD_DEFAULT: Algoritmo (actualmente bcrypt)
            //
            // EJEMPLO:
            // password_hash("abc123", PASSWORD_DEFAULT)
            // Resultado: "$2y$10$abcdefghijklmnopqrstuv..."
            //
            // ESTRUCTURA DEL HASH:
            // $2y$ = Identificador de bcrypt
            // 10$ = Cost factor (2^10 = 1024 iteraciones)
            // abcdefghij... = Salt (22 caracteres aleatorios)
            // klmnopqrstuv... = Hash resultante (31 caracteres)
            //
            // TOTAL: 60 caracteres
            //
            // ¿POR QUÉ CADA HASH ES DIFERENTE?
            // Porque el salt es aleatorio cada vez.
            //
            // EJEMPLO:
            // password_hash("abc123", PASSWORD_DEFAULT)
            // 1ra vez: "$2y$10$xyz123..."
            // 2da vez: "$2y$10$abc789..." (DIFERENTE!)
            //
            // Pero ambos son válidos:
            // password_verify("abc123", "$2y$10$xyz123...") = true
            // password_verify("abc123", "$2y$10$abc789...") = true
            //
            // ALGORITMOS DISPONIBLES:
            // PASSWORD_DEFAULT = bcrypt (recomendado)
            // PASSWORD_BCRYPT = bcrypt explícito
            // PASSWORD_ARGON2I = Argon2i (más moderno)
            // PASSWORD_ARGON2ID = Argon2id (más seguro)
            //
            // ¿POR QUÉ NO USAR md5() O sha1()?
            // - md5() y sha1() son INSEGUROS
            // - Son muy rápidos (malo para contraseñas)
            // - No usan salt automático
            // - Vulnerables a rainbow tables
            //
            // SEGURIDAD DE bcrypt:
            // - Lento a propósito (dificulta fuerza bruta)
            // - Salt automático único
            // - Resistente a ataques GPU
            // - Usado por: Facebook, Google, Twitter
            
            // ============================================================
            // PASO 5: INSERTAR EN BASE DE DATOS
            // ============================================================
            
            $sql = "INSERT INTO usuarios (nombre, correo, contrasena_hash) VALUES (?,?,?)";
            // ========================================================
            // 📌 EXPLICACIÓN DE INSERT INTO
            // ========================================================
            // INSERT INTO agrega una nueva fila a la tabla.
            //
            // SINTAXIS:
            // INSERT INTO tabla (columna1, columna2, ...) VALUES (valor1, valor2, ...)
            //
            // EJEMPLO:
            // INSERT INTO usuarios (nombre, correo) VALUES ('Juan', 'juan@email.com')
            //
            // CON PLACEHOLDERS:
            // INSERT INTO usuarios (nombre, correo) VALUES (?, ?)
            //
            // IMPORTANTE:
            // - El orden de las columnas debe coincidir con el orden de los valores
            // - El número de columnas debe coincidir con el número de valores
            // - Los tipos de datos deben ser compatibles
            //
            // RETORNO:
            // No devuelve filas, pero podemos obtener:
            // - $stmt->insert_id = ID del registro insertado
            // - $stmt->affected_rows = Número de filas afectadas (1 si tuvo éxito)
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sss", $nombre, $correo, $contrasena_hash);
            // ========================================================
            // 📌 EXPLICACIÓN DE "sss"
            // ========================================================
            // "sss" = 3 strings
            //
            // ORDEN:
            // 1. $nombre (string)
            // 2. $correo (string)
            // 3. $contrasena_hash (string)
            //
            // DEBE COINCIDIR CON:
            // INSERT INTO usuarios (nombre, correo, contrasena_hash) VALUES (?, ?, ?)
            //                        ^1      ^2     ^3                    ^1  ^2  ^3
            
            if ($stmt->execute()) {
                // Si el INSERT fue exitoso
                $mensaje = "registro exitoso. Ahora inicia sesion";
                $exito=true;
            } else {
                // Si hubo un error al insertar
                $mensaje = " ⚠️Error al registrar. El correo ya está en uso.";
                // ====================================================
                // 📌 EXPLICACIÓN DEL ERROR
                // ====================================================
                // El error más común es "Duplicate entry".
                // Esto ocurre cuando intentamos insertar un correo
                // que ya existe en la BD.
                //
                // ¿POR QUÉ?
                // La columna 'correo' tiene un índice UNIQUE.
                // MySQL no permite duplicados en columnas UNIQUE.
                //
                // ESTRUCTURA DE LA TABLA:
                // CREATE TABLE usuarios (
                //     id INT PRIMARY KEY AUTO_INCREMENT,
                //     nombre VARCHAR(100),
                //     correo VARCHAR(100) UNIQUE,  <-- UNIQUE aquí
                //     contrasena_hash VARCHAR(255)
                // );
            }
            $stmt->close();
        }
    }
}
?>
```

### 🔑 Conceptos Clave Resumidos

#### Flujo de Registro
```
1. Usuario envía formulario (POST)
2. Validar campos vacíos
3. Validar formato de email
4. Extraer dominio del email (explode)
5. Verificar dominio en lista permitida (in_array)
6. Validar longitud de contraseña (strlen)
7. Hashear contraseña (password_hash)
8. Insertar en BD (INSERT INTO)
9. Mostrar mensaje de éxito/error
```

#### Funciones de Arrays
- `explode()` = Dividir string en array
- `implode()` = Unir array en string
- `in_array()` = Buscar valor en array
- `array_slice()` = Extraer porción de array

---

## `logout.php` - Cerrar Sesión

### 🎯 Propósito
Destruye la sesión del usuario y lo redirige a la página principal.

### 📋 Código Completo Explicado

```php
<?php
session_start();
// ========================================================================
// 📌 ¿POR QUÉ session_start() EN LOGOUT?
// ========================================================================
// Necesitamos iniciar la sesión para poder destruirla.
// Es como abrir una caja para poder vaciarla.

$_SESSION = array();
// ========================================================================
// 📌 EXPLICACIÓN DE $_SESSION = array()
// ========================================================================
// array() crea un array vacío.
// Esto BORRA TODAS las variables de sesión de golpe.
//
// ANTES:
// $_SESSION = [
//     'usuario_id' => 5,
//     'usuario_nombre' => 'Juan',
//     'usuario_correo' => 'juan@email.com',
//     'es_admin' => false
// ]
//
// DESPUÉS:
// $_SESSION = []
//
// ALTERNATIVA (MENOS EFICIENTE):
// unset($_SESSION['usuario_id']);
// unset($_SESSION['usuario_nombre']);
// unset($_SESSION['usuario_correo']);
// unset($_SESSION['es_admin']);
//
// MEJOR:
// $_SESSION = array(); // Borra todo de una vez

session_destroy();
// ========================================================================
// 📌 EXPLICACIÓN DE session_destroy()
// ========================================================================
// Destruye la sesión completamente del servidor.
//
// ¿QUÉ HACE?
// 1. Elimina el archivo de sesión del servidor
// 2. Invalida el ID de sesión (PHPSESSID)
// 3. La próxima vez que el usuario visite, tendrá una sesión nueva
//
// DIFERENCIA CON $_SESSION = array():
// $_SESSION = array() = Vacía las variables pero la sesión sigue existiendo
// session_destroy() = Elimina la sesión completamente
//
// ANALOGÍA:
// $_SESSION = array() = Vaciar una caja
// session_destroy() = Quemar la caja
//
// BUENA PRÁCTICA:
// Hacer AMBAS cosas:
// 1. Vaciar variables ($_SESSION = array())
// 2. Destruir sesión (session_destroy())

header('Location: ../index.php');
// ========================================================================
// 📌 EXPLICACIÓN DE header('Location: ...')
// ========================================================================
// Redirige al usuario a otra página.
//
// SINTAXIS:
// header('Location: ruta');
//
// EJEMPLOS:
// header('Location: index.php'); // Misma carpeta
// header('Location: ../index.php'); // Carpeta padre
// header('Location: /admin/dashboard.php'); // Desde raíz
// header('Location: https://google.com'); // URL externa
//
// IMPORTANTE:
// - DEBE ir antes de cualquier salida HTML
// - No detiene la ejecución (usar exit() después)
// - Es case-sensitive: 'Location' (no 'location')
//
// ¿QUÉ ES ../  ?
// ../ = Subir un nivel en la estructura de carpetas
//
// ESTRUCTURA:
// Lab/
// ├── index.php
// └── forms/
//     └── logout.php
//
// Estamos en: Lab/forms/logout.php
// ../ nos lleva a: Lab/
// ../index.php = Lab/index.php

exit();
// ========================================================================
// 📌 EXPLICACIÓN DE exit()
// ========================================================================
// Detiene la ejecución del script inmediatamente.
//
// ¿POR QUÉ USAR exit() DESPUÉS DE header()?
// Porque header() NO detiene la ejecución.
// El código después de header() se seguiría ejecutando.
//
// EJEMPLO SIN exit():
// header('Location: index.php');
// echo "Este texto se ejecuta"; // Se ejecuta pero no se ve
// $conexion->query("DELETE FROM usuarios"); // ¡Se ejecuta! (peligro)
//
// CON exit():
// header('Location: index.php');
// exit(); // Detiene todo aquí
// echo "Esto NO se ejecuta"; // No se ejecuta
//
// ALTERNATIVA:
// die() es equivalente a exit()
// header('Location: index.php');
// die();
//
// DIFERENCIA:
// exit() = Detiene sin mensaje
// exit("mensaje") = Detiene y muestra mensaje
// die() = Alias de exit()
?>
```

### 🔑 Conceptos Clave Resumidos

#### Flujo de Logout
```
1. Iniciar sesión (session_start)
2. Vaciar variables de sesión ($_SESSION = array())
3. Destruir sesión (session_destroy())
4. Redirigir a página principal (header)
5. Detener ejecución (exit)
```

#### Diferencias Importantes
```php
// Solo vaciar variables (sesión sigue existiendo)
$_SESSION = array();

// Destruir sesión completamente
session_destroy();

// Mejor práctica: Hacer ambas
$_SESSION = array();
session_destroy();
```

---

## 📊 COMPARACIÓN DE LOS 3 ARCHIVOS

| Aspecto | inicio-sesion.php | register.php | logout.php |
|---------|-------------------|--------------|------------|
| **Propósito** | Autenticar usuario | Crear nueva cuenta | Cerrar sesión |
| **Método HTTP** | POST | POST | GET |
| **Validaciones** | Email, contraseña | Email, contraseña, dominio | Ninguna |
| **Consulta BD** | SELECT | INSERT | Ninguna |
| **Sesión** | Crear sesión | No crea sesión | Destruir sesión |
| **Seguridad** | password_verify() | password_hash() | session_destroy() |
| **Redirección** | Condicional | Condicional | Siempre |

---

*Continuará en Parte 4 con más archivos...*
