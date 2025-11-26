<?php
// =============================================================================
// 📄 ARCHIVO: ModeradorLocal.php
// =============================================================================
//
// 🎯 PROPÓSITO PRINCIPAL:
// Esta es la CLASE más importante del sistema de moderación. Es como el "cerebro"
// que analiza las publicaciones y decide si aprobarlas o rechazarlas.
//
// 🧠 ¿QUÉ HACE?
// 1. Lee una publicación de la base de datos
// 2. La analiza buscando problemas (groserías, spam, mala calidad)
// 3. Le da una puntuación de 0 a 100
// 4. Decide si aprobarla, rechazarla o enviarla a revisión manual
// 5. Actualiza el estado en la base de datos
// 6. Envía correos al publicador y a los administradores
//
// 🔧 TECNOLOGÍAS USADAS:
// - PHP puro (sin frameworks)
// - PHPMailer (para enviar correos)
// - MySQL (base de datos)
//
// 📦 DEPENDENCIAS:
// - PHPMailer (en ../forms/PHPMailer/)
// - Conexión a base de datos ($conn)
// =============================================================================

// -----------------------------------------------------------------------------
// PASO 1: Incluir la librería PHPMailer
// -----------------------------------------------------------------------------
// PHPMailer es una librería que facilita el envío de correos electrónicos
// Necesitamos 3 archivos:

// PHPMailer.php: La clase principal que hace todo el trabajo
require_once __DIR__ . '/../forms/PHPMailer/PHPMailer.php';

// SMTP.php: Maneja la conexión con servidores SMTP (como Gmail)
require_once __DIR__ . '/../forms/PHPMailer/SMTP.php';

// Exception.php: Maneja los errores que puedan ocurrir
require_once __DIR__ . '/../forms/PHPMailer/Exception.php';

// -----------------------------------------------------------------------------
// PASO 2: Importar las clases de PHPMailer al namespace actual
// -----------------------------------------------------------------------------
// "use" es como decirle a PHP: "cuando diga PHPMailer, me refiero a esta clase"
// Esto evita tener que escribir el nombre completo cada vez
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =============================================================================
// CLASE: ModeradorLocal
// =============================================================================
// Esta clase contiene TODA la lógica de moderación
// Es como una "máquina" que procesa publicaciones
class ModeradorLocal {
    
    // =========================================================================
    // PROPIEDADES PRIVADAS (Variables de la clase)
    // =========================================================================
    // "private" significa que solo esta clase puede acceder a estas variables
    // Nadie de afuera puede modificarlas directamente
    
    // -------------------------------------------------------------------------
    // $conn: Conexión a la base de datos
    // -------------------------------------------------------------------------
    // Esta variable guarda la conexión a MySQL
    // La usamos para hacer consultas (SELECT, UPDATE, INSERT)
    // Tipo: objeto mysqli
    private $conn;
    
    // -------------------------------------------------------------------------
    // $palabras_prohibidas: Lista de palabras que NO se permiten
    // -------------------------------------------------------------------------
    // Este array contiene palabras que automáticamente rechazan una publicación
    // Si encontramos alguna de estas palabras, la publicación se rechaza al instante
    // Tipo: array de strings
    private $palabras_prohibidas = [
        // Groserías comunes en español
        // Estas son palabras ofensivas que no queremos en contenido académico
        'puto', 'puta', 'pendejo', 'pendeja', 'cabrón', 'cabrona',
        'chingar', 'verga', 'mierda', 'coño', 'joder',
        
        // Spam y publicidad
        // Palabras típicas de correos basura o publicidad engañosa
        'viagra', 'casino', 'poker', 'apuestas', 'ganar dinero fácil',
        'haz clic aquí', 'compra ahora', 'oferta limitada',
        
        // Contenido inapropiado
        // Palabras relacionadas con contenido adulto o inapropiado
        'porno', 'xxx', 'sexo gratis', 'desnudo',
        
        // NOTA: Puedes agregar más palabras según las necesidades de tu plataforma
    ];
    
    // -------------------------------------------------------------------------
    // $palabras_academicas: Lista de palabras que indican calidad académica
    // -------------------------------------------------------------------------
    // Este array contiene palabras típicas de contenido científico/académico
    // Si encontramos estas palabras, SUMA puntos a la publicación
    // Tipo: array de strings
    private $palabras_academicas = [
        // Palabras relacionadas con investigación científica
        'investigación', 'estudio', 'análisis', 'metodología',
        'resultados', 'conclusión', 'hipótesis', 'experimento',
        'teoría', 'evidencia', 'datos', 'muestra', 'bibliografía',
        'referencias', 'abstract', 'resumen', 'objetivo'
        
        // NOTA: Estas palabras indican que el contenido es serio y académico
    ];
    
    // =========================================================================
    // MÉTODO CONSTRUCTOR
    // =========================================================================
    // El constructor es una función ESPECIAL que se ejecuta automáticamente
    // cuando creamos una nueva instancia de la clase
    // Ejemplo: $moderador = new ModeradorLocal($conn);
    //
    // @param mysqli $conexion_bd - Conexión a la base de datos MySQL
    public function __construct($conexion_bd) {
        // Guardar la conexión en la propiedad $conn
        // $this->conn significa "la variable $conn de ESTA instancia"
        // Ahora podemos usar $this->conn en cualquier método de la clase
        $this->conn = $conexion_bd;
    }
    
    // =========================================================================
    // MÉTODO PRINCIPAL: analizarPublicacion
    // =========================================================================
    // Este es el método MÁS IMPORTANTE de toda la clase
    // Es el que orquesta TODO el proceso de moderación
    //
    // FLUJO:
    // 1. Obtiene la publicación de la BD
    // 2. Valida longitud mínima
    // 3. Busca palabras prohibidas
    // 4. Analiza la calidad del contenido
    // 5. Decide aprobar/rechazar/revisar
    // 6. Guarda el análisis en la BD
    // 7. Actualiza el estado y envía correos
    //
    // @param int $publicacion_id - ID de la publicación a analizar
    // @return array - Resultado del análisis con 'success', 'decision', 'razon', etc.
    public function analizarPublicacion($publicacion_id) {
        // ---------------------------------------------------------------------
        // PASO 1: Obtener los datos de la publicación desde la BD
        // ---------------------------------------------------------------------
        // Llamamos al método obtenerPublicacion (definido más abajo)
        // Este método hace un SELECT en la tabla 'publicaciones'
        $publicacion = $this->obtenerPublicacion($publicacion_id);
        
        // Verificar si la publicación existe
        // Si no existe, $publicacion será null
        if (!$publicacion) {
            // Retornar un array indicando que hubo un error
            return [
                'success' => false,  // false = algo salió mal
                'error' => 'Publicación no encontrada'
            ];
        }
        
        // ---------------------------------------------------------------------
        // PASO 2: Inicializar variables para el análisis
        // ---------------------------------------------------------------------
        // $puntuacion: Empezamos con 100 puntos (perfecto)
        // Iremos RESTANDO puntos si encontramos problemas
        $puntuacion = 100;
        
        // $razones: Array que guardará las razones de la decisión
        // Ejemplo: ["Contiene vocabulario académico", "Título muy corto"]
        $razones = [];
        
        // =====================================================================
        // VALIDACIÓN 1: Verificar longitud mínima del contenido
        // =====================================================================
        // strlen(): Función de PHP que cuenta caracteres en un string
        // Ejemplo: strlen("Hola") = 4
        $longitud = strlen($publicacion['contenido']);
        
        // Si el contenido tiene menos de 75 caracteres, es muy corto
        if ($longitud < 75) {
            // Decisión inmediata: RECHAZADA
            $decision = 'rechazada';
            
            // Razón específica con la longitud actual
            // {$longitud} inserta el valor de la variable en el string
            $razon = "El contenido es muy corto ({$longitud} caracteres). Mínimo requerido: 75";
            
            // Puntuación = 0 (muy malo)
            $puntuacion = 0;
            
            // Guardar este análisis en la tabla moderacion_ia_logs
            $this->guardarAnalisis($publicacion_id, $decision, $razon, $puntuacion);
            
            // Actualizar el estado de la publicación a 'rechazada'
            // También envía correos al publicador y admins
            $this->actualizarEstadoPublicacion($publicacion_id, $decision, $razon);
            
            // Retornar el resultado inmediatamente (no seguir analizando)
            return [
                'success' => true,              // true = el análisis se completó
                'decision' => $decision,        // 'rechazada'
                'razon' => $razon,              // Explicación
                'confianza' => 100,             // 100% seguro de esta decisión
                'tipo_analisis' => 'validacion_local'  // Tipo de análisis
            ];
        }
        
        // =====================================================================
        // VALIDACIÓN 2: Buscar palabras prohibidas
        // =====================================================================
        // Llamamos al método buscarPalabrasProhibidas (definido más abajo)
        // Este método busca si el título o contenido tiene palabras prohibidas
        $palabras_encontradas = $this->buscarPalabrasProhibidas($publicacion);
        
        // empty(): Verifica si un array está vacío
        // !empty() = "si NO está vacío" = "si encontró palabras prohibidas"
        if (!empty($palabras_encontradas)) {
            // implode(): Une los elementos de un array con un separador
            // Ejemplo: implode(', ', ['puta', 'mierda']) = "puta, mierda"
            $lista = implode(', ', $palabras_encontradas);
            
            // Decisión inmediata: RECHAZADA
            $decision = 'rechazada';
            $razon = "Contiene palabras prohibidas: {$lista}";
            $puntuacion = 0;
            
            // Guardar análisis y actualizar estado
            $this->guardarAnalisis($publicacion_id, $decision, $razon, $puntuacion);
            $this->actualizarEstadoPublicacion($publicacion_id, $decision, $razon);
            
            // Retornar resultado
            return [
                'success' => true,
                'decision' => $decision,
                'razon' => $razon,
                'confianza' => 100,
                'tipo_analisis' => 'validacion_local'
            ];
        }
        
        // =====================================================================
        // VALIDACIÓN 3: Analizar la calidad del contenido
        // =====================================================================
        // Si llegamos aquí, la publicación pasó las validaciones básicas
        // Ahora analizamos la CALIDAD del contenido
        
        // Llamamos al método analizarCalidad (definido más abajo)
        // Este método revisa vocabulario, estructura, título, etc.
        $analisis_calidad = $this->analizarCalidad($publicacion);
        
        // Extraer la puntuación del análisis
        // $analisis_calidad es un array con 'puntuacion' y 'razones'
        $puntuacion = $analisis_calidad['puntuacion'];
        $razones = $analisis_calidad['razones'];
        
        // =====================================================================
        // DECISIÓN FINAL basada en la puntuación
        // =====================================================================
        // Usamos la puntuación para decidir qué hacer con la publicación
        
        if ($puntuacion >= 70) {
            // CASO 1: Puntuación alta (70-100) = APROBAR
            $decision = 'publicado';  // Estado final: publicado
            
            // implode('. ', $razones): Une las razones con punto
            // Ejemplo: "Buena estructura. Vocabulario apropiado"
            $razon = "Publicación aprobada. " . implode('. ', $razones);
            
        } else if ($puntuacion >= 50) {
            // CASO 2: Puntuación media (50-69) = REVISIÓN MANUAL
            $decision = 'en_revision';  // Un admin debe revisarla
            $razon = "Requiere revisión manual. " . implode('. ', $razones);
            
        } else {
            // CASO 3: Puntuación baja (0-49) = RECHAZAR
            $decision = 'rechazada';
            $razon = "Publicación rechazada. " . implode('. ', $razones);
        }
        
        // ---------------------------------------------------------------------
        // Guardar el análisis en la base de datos
        // ---------------------------------------------------------------------
        // Esto crea un registro en la tabla 'moderacion_ia_logs'
        // Sirve para tener un historial de todas las decisiones
        $this->guardarAnalisis($publicacion_id, $decision, $razon, $puntuacion);
        
        // ---------------------------------------------------------------------
        // Actualizar el estado de la publicación
        // ---------------------------------------------------------------------
        // Esto hace 3 cosas:
        // 1. Actualiza el campo 'estado' en la tabla 'publicaciones'
        // 2. Envía un correo al publicador
        // 3. Envía correos a todos los administradores
        $this->actualizarEstadoPublicacion($publicacion_id, $decision, $razon);
        
        // ---------------------------------------------------------------------
        // Retornar el resultado del análisis
        // ---------------------------------------------------------------------
        return [
            'success' => true,                      // Análisis completado exitosamente
            'decision' => $decision,                // 'publicado', 'rechazada', 'en_revision'
            'razon' => $razon,                      // Explicación detallada
            'confianza' => $puntuacion,             // Puntuación 0-100
            'tipo_analisis' => 'moderacion_local'   // Tipo de moderación usada
        ];
    }
    
    // =========================================================================
    // MÉTODO: buscarPalabrasProhibidas
    // =========================================================================
    // Este método busca si el título o contenido contiene palabras prohibidas
    //
    // CÓMO FUNCIONA:
    // 1. Convierte todo a minúsculas (para comparar sin importar mayúsculas)
    // 2. Busca cada palabra prohibida en el texto
    // 3. Si encuentra alguna, la agrega a un array
    // 4. Retorna el array de palabras encontradas
    //
    // @param array $publicacion - Array con 'titulo' y 'contenido'
    // @return array - Array de palabras prohibidas encontradas (vacío si no hay)
    private function buscarPalabrasProhibidas($publicacion) {
        // Array vacío para guardar las palabras prohibidas que encontremos
        $encontradas = [];
        
        // Concatenar título y contenido en un solo texto
        // strtolower(): Convierte todo a minúsculas
        // Ejemplo: strtolower("HOLA Mundo") = "hola mundo"
        // Esto permite buscar sin importar si está en mayúsculas o minúsculas
        $texto = strtolower($publicacion['titulo'] . ' ' . $publicacion['contenido']);
        
        // foreach: Recorre cada elemento del array $palabras_prohibidas
        // $palabra: Variable temporal que toma el valor de cada elemento
        foreach ($this->palabras_prohibidas as $palabra) {
            // strpos(): Busca si un string contiene otro string
            // Retorna la posición si lo encuentra, o false si no
            // !== false: "si SÍ lo encontró"
            if (strpos($texto, strtolower($palabra)) !== false) {
                // Agregar la palabra al array de encontradas
                // []: Sintaxis para agregar al final del array
                $encontradas[] = $palabra;
            }
        }
        
        // Retornar el array (puede estar vacío si no encontró nada)
        return $encontradas;
    }
    
    // =========================================================================
    // MÉTODO: analizarCalidad
    // =========================================================================
    // Este método analiza la CALIDAD del contenido y le da una puntuación
    //
    // CRITERIOS QUE EVALÚA:
    // 1. Vocabulario académico (¿usa palabras científicas?)
    // 2. Estructura (¿está bien organizado en párrafos?)
    // 3. Longitud del título (¿es apropiada?)
    //
    // @param array $publicacion - Array con 'titulo' y 'contenido'
    // @return array - Array con 'puntuacion' (0-100) y 'razones' (array de strings)
    private function analizarCalidad($publicacion) {
        // Empezamos con 100 puntos (perfecto)
        $puntuacion = 100;
        
        // Array para guardar las razones (explicaciones)
        $razones = [];
        
        // Extraer título y contenido para trabajar con ellos
        $titulo = $publicacion['titulo'];
        $contenido = $publicacion['contenido'];
        
        // Crear un texto completo en minúsculas para buscar palabras
        $texto_completo = strtolower($titulo . ' ' . $contenido);
        
        // =====================================================================
        // CRITERIO 1: Verificar palabras académicas
        // =====================================================================
        // Contamos cuántas palabras académicas tiene el texto
        $palabras_acad_encontradas = 0;
        
        // Recorrer cada palabra académica de nuestra lista
        foreach ($this->palabras_academicas as $palabra) {
            // Si encontramos la palabra en el texto, incrementar contador
            if (strpos($texto_completo, strtolower($palabra)) !== false) {
                $palabras_acad_encontradas++;  // ++ = incrementar en 1
            }
        }
        
        // Evaluar según cuántas palabras académicas encontramos
        if ($palabras_acad_encontradas >= 3) {
            // BUENO: Tiene 3 o más palabras académicas
            // No restamos puntos, agregamos una razón positiva
            $razones[] = "Contiene vocabulario académico apropiado";
            
        } else if ($palabras_acad_encontradas >= 1) {
            // REGULAR: Tiene 1 o 2 palabras académicas
            $puntuacion -= 10;  // Restar 10 puntos
            $razones[] = "Vocabulario académico limitado";
            
        } else {
            // MALO: No tiene palabras académicas
            $puntuacion -= 20;  // Restar 20 puntos
            $razones[] = "No contiene vocabulario académico";
        }
        
        // =====================================================================
        // CRITERIO 2: Verificar estructura (párrafos)
        // =====================================================================
        // explode(): Divide un string en un array usando un separador
        // "\n" = salto de línea (enter)
        // Ejemplo: explode("\n", "Hola\nMundo") = ["Hola", "Mundo"]
        $parrafos = explode("\n", $contenido);
        
        // array_filter(): Filtra un array según una condición
        // function($p): Función anónima que recibe cada párrafo
        // strlen(trim($p)) > 50: Solo párrafos con más de 50 caracteres
        // trim(): Quita espacios al inicio y final
        $parrafos = array_filter($parrafos, function($p) {
            return strlen(trim($p)) > 50;
        });
        
        // count(): Cuenta elementos en un array
        if (count($parrafos) >= 3) {
            // BUENO: Tiene 3 o más párrafos
            $razones[] = "Bien estructurado en párrafos";
            
        } else {
            // MALO: Tiene pocos párrafos
            $puntuacion -= 15;
            $razones[] = "Estructura mejorable (pocos párrafos)";
        }
        
        // =====================================================================
        // CRITERIO 3: Verificar longitud del título
        // =====================================================================
        if (strlen($titulo) < 10) {
            // Título muy corto (menos de 10 caracteres)
            $puntuacion -= 10;
            $razones[] = "Título muy corto";
            
        } else if (strlen($titulo) > 100) {
            // Título muy largo (más de 100 caracteres)
            $puntuacion -= 5;
            $razones[] = "Título muy largo";
            
        } else {
            // Título de longitud apropiada (10-100 caracteres)
            $razones[] = "Título de longitud adecuada";
        }
        
        // ---------------------------------------------------------------------
        // Retornar resultado del análisis
        // ---------------------------------------------------------------------
        // max(): Retorna el valor máximo
        // min(): Retorna el valor mínimo
        // max(0, min(100, $puntuacion)): Asegura que esté entre 0 y 100
        return [
            'puntuacion' => max(0, min(100, $puntuacion)),
            'razones' => $razones
        ];
    }
    
    // =========================================================================
    // MÉTODO: obtenerPublicacion
    // =========================================================================
    // Este método obtiene los datos de una publicación desde la base de datos
    //
    // @param int $id - ID de la publicación
    // @return array|null - Array con los datos de la publicación, o null si no existe
    private function obtenerPublicacion($id) {
        // Consulta SQL preparada (segura contra inyección SQL)
        // ?: Marcador de posición que será reemplazado por el ID
        $query = "SELECT * FROM publicaciones WHERE id = ?";
        
        // prepare(): Prepara la consulta para ejecución segura
        $stmt = $this->conn->prepare($query);
        
        // bind_param(): Vincula el parámetro con el marcador ?
        // "i": Indica que es un integer (número entero)
        // $id: El valor que reemplazará al ?
        $stmt->bind_param("i", $id);
        
        // execute(): Ejecuta la consulta
        $stmt->execute();
        
        // get_result(): Obtiene el resultado de la consulta
        $resultado = $stmt->get_result();
        
        // Verificar si encontró alguna fila
        if ($resultado->num_rows > 0) {
            // fetch_assoc(): Obtiene la fila como array asociativo
            // Ejemplo: ['id' => 1, 'titulo' => 'Mi artículo', ...]
            return $resultado->fetch_assoc();
        }
        
        // Si no encontró nada, retornar null
        return null;
    }
    
    // =========================================================================
    // MÉTODO: guardarAnalisis
    // =========================================================================
    // Este método guarda el resultado del análisis en la tabla moderacion_ia_logs
    // Esto crea un historial de todas las decisiones de moderación
    //
    // @param int $publicacion_id - ID de la publicación analizada
    // @param string $decision - Decisión tomada ('publicado', 'rechazada', etc.)
    // @param string $razon - Explicación de la decisión
    // @param int $confianza - Puntuación 0-100
    private function guardarAnalisis($publicacion_id, $decision, $razon, $confianza) {
        // Consulta INSERT para agregar un nuevo registro
        // NOW(): Función de MySQL que retorna la fecha/hora actual
        $query = "INSERT INTO moderacion_ia_logs 
                  (publicacion_id, decision, razon, confianza, fecha_analisis) 
                  VALUES (?, ?, ?, ?, NOW())";
        
        // Preparar la consulta
        $stmt = $this->conn->prepare($query);
        
        // bind_param(): Vincular los 4 parámetros
        // "issi": i=integer, s=string, s=string, i=integer
        $stmt->bind_param("issi", $publicacion_id, $decision, $razon, $confianza);
        
        // Ejecutar la consulta
        $stmt->execute();
    }
    
    // =========================================================================
    // MÉTODO: actualizarEstadoPublicacion
    // =========================================================================
    // Este método hace 3 cosas importantes:
    // 1. Actualiza el estado de la publicación en la BD
    // 2. Envía un correo al publicador
    // 3. Envía correos a todos los administradores
    //
    // @param int $publicacion_id - ID de la publicación
    // @param string $nuevo_estado - Nuevo estado ('publicado', 'rechazada', etc.)
    // @param string|null $razon - Razón del cambio (opcional)
    private function actualizarEstadoPublicacion($publicacion_id, $nuevo_estado, $razon = null) {
        // ---------------------------------------------------------------------
        // PASO 1: Obtener datos del publicador ANTES de actualizar
        // ---------------------------------------------------------------------
        // Necesitamos el email y nombre del publicador para enviarle un correo
        // Hacemos un JOIN para obtener datos de ambas tablas
        $query_pub = "SELECT p.*, pub.nombre as publicador_nombre, pub.email as publicador_email 
                      FROM publicaciones p 
                      INNER JOIN publicadores pub ON p.publicador_id = pub.id 
                      WHERE p.id = ?";
        
        $stmt = $this->conn->prepare($query_pub);
        $stmt->bind_param("i", $publicacion_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        // fetch_assoc(): Obtiene los datos como array
        $datos = $resultado->fetch_assoc();
        
        // close(): Cerrar el statement para liberar recursos
        $stmt->close();
        
        // ---------------------------------------------------------------------
        // PASO 2: Actualizar el estado en la base de datos
        // ---------------------------------------------------------------------
        // Si es rechazada Y hay una razón, guardar la razón
        if ($nuevo_estado === 'rechazada' && $razon !== null) {
            // UPDATE con mensaje_rechazo
            $query = "UPDATE publicaciones SET estado = ?, mensaje_rechazo = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            
            // "ssi": string, string, integer
            $stmt->bind_param("ssi", $nuevo_estado, $razon, $publicacion_id);
            
        } else {
            // UPDATE sin mensaje_rechazo (lo ponemos en NULL)
            $query = "UPDATE publicaciones SET estado = ?, mensaje_rechazo = NULL WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            
            // "si": string, integer
            $stmt->bind_param("si", $nuevo_estado, $publicacion_id);
        }
        
        // Ejecutar el UPDATE
        $stmt->execute();
        $stmt->close();
        
        // ---------------------------------------------------------------------
        // PASO 3: Enviar correos electrónicos
        // ---------------------------------------------------------------------
        // Verificar que tengamos los datos del publicador
        // isset(): Verifica si una variable existe y no es null
        if ($datos && isset($datos['publicador_email'])) {
            // Enviar correo al PUBLICADOR
            $this->enviarCorreoNotificacion(
                $datos['publicador_email'],     // Email del publicador
                $datos['publicador_nombre'],    // Nombre del publicador
                $datos['titulo'],                // Título de la publicación
                $nuevo_estado,                   // Estado ('publicado', 'rechazada')
                $razon                           // Razón (puede ser null)
            );
            
            // Enviar correos a todos los ADMINISTRADORES
            $this->notificarAdministradores($datos, $nuevo_estado, $razon);
        }
    }
    
    // =========================================================================
    // MÉTODO: notificarAdministradores
    // =========================================================================
    // Este método envía un correo a TODOS los administradores activos
    // informándoles sobre la decisión de moderación
    //
    // @param array $datos_publicacion - Datos de la publicación
    // @param string $estado - Estado de la publicación
    // @param string|null $razon - Razón de la decisión
    private function notificarAdministradores($datos_publicacion, $estado, $razon = null) {
        // Consulta para obtener todos los administradores
        // Solo seleccionamos email y nombre (no necesitamos más)
        $query = "SELECT email, nombre FROM admins";
        
        // query(): Ejecuta una consulta simple (sin parámetros)
        $resultado = $this->conn->query($query);
        
        // Verificar si hay administradores
        // &&: Operador AND (ambas condiciones deben ser verdaderas)
        if ($resultado && $resultado->num_rows > 0) {
            // while: Recorrer cada administrador
            while ($admin = $resultado->fetch_assoc()) {
                // Enviar correo a este administrador
                $this->enviarCorreoAdmin(
                    $admin['email'],        // Email del admin
                    $admin['nombre'],       // Nombre del admin
                    $datos_publicacion,     // Datos de la publicación
                    $estado,                // Estado
                    $razon                  // Razón
                );
            }
        }
    }
    
    // =========================================================================
    // MÉTODO: configurarPHPMailer
    // =========================================================================
    // Este método configura PHPMailer con las credenciales de Gmail
    // Es como "preparar" el sistema de correo antes de enviar
    //
    // @return PHPMailer - Objeto PHPMailer configurado y listo para usar
    private function configurarPHPMailer() {
        // Crear nueva instancia de PHPMailer
        // true: Habilitar excepciones (manejo de errores)
        $mail = new PHPMailer(true);
        
        // ---------------------------------------------------------------------
        // Configuración del servidor SMTP (Gmail)
        // ---------------------------------------------------------------------
        // isSMTP(): Le dice a PHPMailer que use SMTP (no mail() de PHP)
        $mail->isSMTP();
        
        // Host: Dirección del servidor SMTP
        // smtp.gmail.com es el servidor de Gmail
        $mail->Host = 'smtp.gmail.com';
        
        // SMTPAuth: Habilitar autenticación
        // true = necesitamos usuario y contraseña
        $mail->SMTPAuth = true;
        
        // Username: Email de la cuenta que enviará los correos
        $mail->Username = 'lab.explorer2025@gmail.com';
        
        // Password: Contraseña de aplicación de Gmail
        // NOTA: NO es la contraseña normal, es una "contraseña de aplicación"
        // Se genera en la configuración de seguridad de Google
        $mail->Password = 'yero ewft jacf vjzp';
        
        // SMTPSecure: Tipo de encriptación
        // ENCRYPTION_STARTTLS: Encriptación TLS (más seguro)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        
        // Port: Puerto del servidor SMTP
        // 587 es el puerto estándar para STARTTLS
        $mail->Port = 587;
        
        // ---------------------------------------------------------------------
        // Configuración de codificación
        // ---------------------------------------------------------------------
        // CharSet: Conjunto de caracteres
        // UTF-8 permite usar ñ, tildes, emojis, etc.
        $mail->CharSet = 'UTF-8';
        
        // Encoding: Tipo de codificación
        // base64 es compatible con la mayoría de clientes de correo
        $mail->Encoding = 'base64';
        
        // ---------------------------------------------------------------------
        // Configurar remitente
        // ---------------------------------------------------------------------
        // setFrom(): Establece quién envía el correo
        // Parámetros: (email, nombre)
        $mail->setFrom('lab.explorer2025@gmail.com', 'Lab Explorer');
        
        // Retornar el objeto configurado
        return $mail;
    }
    
    // =========================================================================
    // MÉTODO: enviarCorreoAdmin
    // =========================================================================
    // Este método envía un correo a UN administrador específico
    // informándole sobre una decisión de moderación
    //
    // @param string $email_admin - Email del administrador
    // @param string $nombre_admin - Nombre del administrador
    // @param array $datos_pub - Datos de la publicación
    // @param string $estado - Estado de la publicación
    // @param string|null $razon - Razón de la decisión
    private function enviarCorreoAdmin($email_admin, $nombre_admin, $datos_pub, $estado, $razon = null) {
        // Solo enviar correos para estados finales
        // !== : Operador "no idéntico" (compara valor Y tipo)
        if ($estado !== 'publicado' && $estado !== 'rechazada') {
            return;  // Salir sin hacer nada
        }
        
        // Configurar asunto y color según el estado
        // === : Operador "idéntico" (compara valor Y tipo)
        $asunto = $estado === 'publicado' 
            ? "Publicacion Aprobada Automaticamente - Lab-Explorer"
            : "Publicacion Rechazada Automaticamente - Lab-Explorer";
        
        // ?: Operador ternario (if-else en una línea)
        // condicion ? valor_si_true : valor_si_false
        $color = $estado === 'publicado' ? "#28a745" : "#dc3545";
        $titulo = $estado === 'publicado' ? "Publicación Aprobada" : "Publicación Rechazada";
        
        // Construir mensaje HTML simple
        // strtoupper(): Convierte a mayúsculas
        $mensaje_html = "<html><body style='font-family: Arial;'>
            <h2 style='color: {$color};'>{$titulo}</h2>
            <p>Hola <strong>{$nombre_admin}</strong>,</p>
            <p><strong>Título:</strong> {$datos_pub['titulo']}</p>
            <p><strong>Publicador:</strong> {$datos_pub['publicador_nombre']}</p>
            <p><strong>Estado:</strong> " . strtoupper($estado) . "</p>";
        
        // Si hay razón, agregarla
        if ($razon) {
            $mensaje_html .= "<p><strong>Motivo:</strong> {$razon}</p>";
        }
        
        $mensaje_html .= "</body></html>";
        
        // try-catch: Manejo de errores
        try {
            // Configurar PHPMailer
            $mail = $this->configurarPHPMailer();
            
            // addAddress(): Agregar destinatario
            $mail->addAddress($email_admin, $nombre_admin);
            
            // Subject: Asunto del correo
            $mail->Subject = $asunto;
            
            // isHTML(true): Indicar que el cuerpo es HTML
            $mail->isHTML(true);
            
            // Body: Cuerpo del correo
            $mail->Body = $mensaje_html;
            
            // send(): Enviar el correo
            $mail->send();
            
            // Registrar en el log
            $this->log("Correo enviado a admin: {$email_admin}");
            
        } catch (Exception $e) {
            // Si hay error, registrarlo en el log
            // getMessage(): Obtiene el mensaje de error
            $this->log("Error enviando correo a admin: " . $e->getMessage());
        }
    }
    
    // =========================================================================
    // FUNCIÓN: enviarCorreoNotificacion
    // =========================================================================
    // 
    // 🎯 PROPÓSITO:
    // Esta función envía un correo electrónico al PUBLICADOR cuando su 
    // publicación es aprobada o rechazada por el sistema de moderación.
    // 
    // 📧 CARACTERÍSTICAS DEL CORREO:
    // - Diseño HTML profesional con CSS
    // - Emojis para mejor experiencia visual
    // - Información completa: título, tipo, estado, fecha
    // - Botón de acción para ver publicaciones
    // - Responsive (se ve bien en móviles)
    // 
    // @param string $email - Email del publicador (ej: "juan@gmail.com")
    // @param string $nombre - Nombre completo del publicador (ej: "Juan Pérez")
    // @param string $titulo_publicacion - Título de la publicación
    // @param string $estado - Estado final: 'publicado' o 'rechazada'
    // @param string $razon - Motivo del rechazo (opcional, solo si es rechazada)
    // =========================================================================
    private function enviarCorreoNotificacion($email, $nombre, $titulo_publicacion, $estado, $razon = null) {
        // ---------------------------------------------------------------------
        // PASO 1: Validar que solo enviemos correos para estados finales
        // ---------------------------------------------------------------------
        // Solo enviamos correos cuando la publicación está APROBADA o RECHAZADA
        // No enviamos para estados intermedios como 'en_revision'
        if ($estado !== 'publicado' && $estado !== 'rechazada') {
            return; // Salir sin hacer nada
        }
        
        // ---------------------------------------------------------------------
        // PASO 2: Configurar el asunto y colores según el estado
        // ---------------------------------------------------------------------
        if ($estado === 'publicado') {
            // --- CASO: PUBLICACIÓN APROBADA ---
            $asunto = "✅ Tu publicacion ha sido aprobada - Lab Explorer";
            $color = "#28a745";  // Verde para éxito
            $titulo_correo = "✅ Publicación Aprobada";
            $estado_texto = "Publicado";
            $icono_estado = "✅";
        } else {
            // --- CASO: PUBLICACIÓN RECHAZADA ---
            $asunto = "❌ Tu publicacion requiere revision - Lab Explorer";
            $color = "#dc3545";  // Rojo para rechazo
            $titulo_correo = "❌ Publicación Rechazada";
            $estado_texto = "Rechazado";
            $icono_estado = "❌";
        }
        
        // ---------------------------------------------------------------------
        // PASO 3: Obtener la fecha actual formateada
        // ---------------------------------------------------------------------
        // date_default_timezone_set(): Establece la zona horaria
        // 'America/Mexico_City': Zona horaria de México (GMT-6)
        date_default_timezone_set('America/Mexico_City');
        
        // date(): Formatea la fecha/hora actual
        // 'd/m/Y H:i': Formato día/mes/año hora:minuto
        // Ejemplo: "24/11/2025 00:59"
        $fecha_actual = date('d/m/Y H:i');
        
        // =====================================================================
        // PASO 4: Construir el HTML del correo (VERSIÓN PROFESIONAL)
        // =====================================================================
        // Aquí construimos un correo HTML completo con:
        // - DOCTYPE y estructura HTML5
        // - CSS inline (los estilos van dentro del HTML)
        // - Diseño responsive (se adapta a móviles)
        // - Colores dinámicos según el estado
        
        $mensaje_html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                /* ========================================= */
                /* ESTILOS CSS DEL CORREO                   */
                /* ========================================= */
                
                /* Estilos generales del body */
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;           /* Espaciado entre líneas */
                    color: #333;                /* Color de texto gris oscuro */
                    background-color: #f4f4f4;  /* Fondo gris claro */
                    margin: 0;
                    padding: 0;
                }
                
                /* Contenedor principal - centrado y con ancho máximo */
                .container {
                    max-width: 600px;           /* Ancho máximo 600px */
                    margin: 20px auto;          /* Centrado horizontal */
                    background-color: #ffffff;  /* Fondo blanco */
                    border-radius: 10px;        /* Esquinas redondeadas */
                    overflow: hidden;           /* Ocultar contenido que se salga */
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);  /* Sombra suave */
                }
                
                /* Encabezado con color dinámico (verde o rojo) */
                .header {
                    background-color: {$color}; /* Color dinámico desde PHP */
                    color: white;
                    padding: 30px 20px;
                    text-align: center;
                }
                
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                    font-weight: 600;
                }
                
                /* Contenido principal del correo */
                .content {
                    padding: 30px 20px;
                }
                
                /* Saludo personalizado */
                .greeting {
                    font-size: 16px;
                    margin-bottom: 20px;
                }
                
                /* Mensaje principal */
                .main-message {
                    background-color: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 25px;
                    border-left: 4px solid {$color};  /* Borde izquierdo de color */
                }
                
                /* Caja de información de la publicación */
                .info-box {
                    background-color: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                
                /* Cada línea de información */
                .info-item {
                    padding: 10px 0;
                    border-bottom: 1px solid #e9ecef;  /* Línea separadora */
                    display: flex;                      /* Flexbox para alinear */
                    align-items: center;
                }
                
                .info-item:last-child {
                    border-bottom: none;  /* Última línea sin borde */
                }
                
                /* Etiqueta (ej: 📌 Título:) */
                .info-label {
                    font-weight: 600;     /* Negrita */
                    color: #555;
                    min-width: 120px;     /* Ancho mínimo para alineación */
                }
                
                /* Valor de la información */
                .info-value {
                    color: #333;
                    flex: 1;              /* Ocupa el espacio restante */
                }
                
                /* Caja de motivo de rechazo (solo para rechazos) */
                .reason-box {
                    background-color: #fff3cd;  /* Amarillo claro */
                    border-left: 4px solid #ffc107;  /* Borde amarillo */
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 5px;
                }
                
                /* Botón de acción */
                .button {
                    display: inline-block;
                    padding: 12px 30px;
                    background-color: {$color};  /* Color dinámico */
                    color: white;
                    text-decoration: none;       /* Sin subrayado */
                    border-radius: 5px;
                    font-weight: 600;
                    margin-top: 20px;
                    text-align: center;
                }
                
                .button:hover {
                    opacity: 0.9;  /* Efecto hover: ligeramente transparente */
                }
                
                /* Pie de página */
                .footer {
                    background-color: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #6c757d;
                    border-top: 1px solid #e9ecef;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <!-- ============================================ -->
                <!-- ENCABEZADO                                   -->
                <!-- ============================================ -->
                <div class='header'>
                    <h1>{$titulo_correo}</h1>
                </div>
                
                <!-- ============================================ -->
                <!-- CONTENIDO PRINCIPAL                          -->
                <!-- ============================================ -->
                <div class='content'>
                    <!-- Saludo personalizado -->
                    <div class='greeting'>
                        Hola <strong>{$nombre}</strong>,
                    </div>";
        
        // =====================================================================
        // PASO 5: Contenido específico según el estado
        // =====================================================================
        if ($estado === 'publicado') {
            // --- CONTENIDO PARA PUBLICACIÓN APROBADA ---
            $mensaje_html .= "
                    <!-- Mensaje de felicitación -->
                    <div class='main-message'>
                        <p style='margin: 0; font-size: 16px;'>
                            ¡Excelentes noticias! Tu publicación ha sido <strong>aprobada</strong> 
                            y ahora está visible para todos los usuarios de Lab Explorer.
                        </p>
                    </div>
                    
                    <!-- Información de la publicación -->
                    <div class='info-box'>
                        <!-- Título -->
                        <div class='info-item'>
                            <span class='info-label'>📌 Título:</span>
                            <span class='info-value'>{$titulo_publicacion}</span>
                        </div>
                        
                        <!-- Tipo (hardcodeado por ahora) -->
                        <div class='info-item'>
                            <span class='info-label'>📂 Tipo:</span>
                            <span class='info-value'>Artículo Científico</span>
                        </div>
                        
                        <!-- Estado con color dinámico -->
                        <div class='info-item'>
                            <span class='info-label'>📊 Estado:</span>
                            <span class='info-value' style='color: {$color}; font-weight: 600;'>
                                {$icono_estado} {$estado_texto}
                            </span>
                        </div>
                        
                        <!-- Fecha actual -->
                        <div class='info-item'>
                            <span class='info-label'>📅 Fecha:</span>
                            <span class='info-value'>{$fecha_actual}</span>
                        </div>
                    </div>
                    
                    <!-- Mensaje adicional -->
                    <p style='color: #555; margin-top: 20px;'>
                        Tu contenido ya está disponible en la plataforma y los usuarios pueden acceder a él.
                    </p>
                    
                    <!-- Botón de acción -->
                    <div style='text-align: center;'>
                        <a href='http://localhost/Lab/forms/publicadores/mis-publicaciones.php' class='button'>
                            📝 Ver Mis Publicaciones
                        </a>
                    </div>";
                    
        } else {
            // --- CONTENIDO PARA PUBLICACIÓN RECHAZADA ---
            $mensaje_html .= "
                    <!-- Mensaje de rechazo -->
                    <div class='main-message'>
                        <p style='margin: 0; font-size: 16px;'>
                            Lamentamos informarte que tu publicación <strong>no ha sido aprobada</strong> 
                            en este momento.
                        </p>
                    </div>
                    
                    <!-- Información de la publicación -->
                    <div class='info-box'>
                        <div class='info-item'>
                            <span class='info-label'>📌 Título:</span>
                            <span class='info-value'>{$titulo_publicacion}</span>
                        </div>
                        <div class='info-item'>
                            <span class='info-label'>📊 Estado:</span>
                            <span class='info-value' style='color: {$color}; font-weight: 600;'>
                                {$icono_estado} {$estado_texto}
                            </span>
                        </div>
                        <div class='info-item'>
                            <span class='info-label'>📅 Fecha:</span>
                            <span class='info-value'>{$fecha_actual}</span>
                        </div>
                    </div>";
            
            // Si hay un motivo de rechazo, mostrarlo
            if ($razon) {
                $mensaje_html .= "
                    <!-- Motivo del rechazo -->
                    <div class='reason-box'>
                        <strong>⚠️ Motivo del rechazo:</strong><br>
                        <p style='margin: 10px 0 0 0;'>{$razon}</p>
                    </div>";
            }
            
            $mensaje_html .= "
                    <!-- Mensaje de ayuda -->
                    <p style='color: #555; margin-top: 20px;'>
                        Te invitamos a revisar el contenido y volver a enviarlo cumpliendo 
                        con nuestras políticas de publicación.
                    </p>
                    
                    <!-- Botón de acción -->
                    <div style='text-align: center;'>
                        <a href='http://localhost/Lab/forms/publicadores/mis-publicaciones.php' class='button'>
                            📝 Ver Mis Publicaciones
                        </a>
                    </div>";
        }
        
        // =====================================================================
        // PASO 6: Cerrar el HTML y agregar pie de página
        // =====================================================================
        $mensaje_html .= "
                </div>
                
                <!-- ============================================ -->
                <!-- PIE DE PÁGINA                                -->
                <!-- ============================================ -->
                <div class='footer'>
                    <p style='margin: 5px 0;'>
                        Este es un mensaje automático del sistema de moderación de Lab Explorer.
                    </p>
                    <p style='margin: 5px 0;'>
                        Por favor, no respondas a este correo.
                    </p>
                    <p style='margin: 5px 0;'>
                        © 2025 Lab Explorer - Plataforma Académica de Laboratorio Clínico
                    </p>
                </div>
            </div>
        </body>
        </html>";
        
        // =====================================================================
        // PASO 7: Enviar el correo usando PHPMailer
        // =====================================================================
        try {
            // Configurar PHPMailer con las credenciales SMTP
            $mail = $this->configurarPHPMailer();
            
            // addAddress(): Agregar destinatario (el publicador)
            // Parámetros: (email, nombre)
            $mail->addAddress($email, $nombre);
            
            // Subject: Asunto del correo
            $mail->Subject = $asunto;
            
            // isHTML(true): Indicar que el cuerpo es HTML (no texto plano)
            $mail->isHTML(true);
            
            // Body: Asignar el cuerpo del correo (HTML)
            $mail->Body = $mensaje_html;
            
            // send(): Enviar el correo
            // Retorna true si se envió correctamente, false si falló
            $mail->send();
            
            // Registrar éxito en el log
            // ✅: Emoji para indicar éxito
            $this->log("✅ Correo enviado exitosamente a publicador: {$email} - Estado: {$estado}");
            
        } catch (Exception $e) {
            // Si hay error al enviar, registrarlo en el log
            // ❌: Emoji para indicar error
            // $e->getMessage(): Obtiene el mensaje de error de la excepción
            $this->log("❌ Error enviando correo a publicador {$email}: " . $e->getMessage());
        }
    }
    
    // =========================================================================
    // MÉTODO: log
    // =========================================================================
    // Este método registra mensajes en un archivo de log
    // Es útil para debugging y para saber qué está pasando con los correos
    //
    // @param string $mensaje - Mensaje a registrar en el log
    private function log($mensaje) {
        // __DIR__: Constante mágica que contiene la ruta del directorio actual
        // Ejemplo: "C:\xampp\htdocs\Lab\ollama_ia"
        $log_dir = __DIR__ . '/logs';
        
        // file_exists(): Verifica si un archivo o directorio existe
        if (!file_exists($log_dir)) {
            // mkdir(): Crea un directorio
            // Parámetros:
            //   - Ruta del directorio
            //   - 0777: Permisos (lectura/escritura/ejecución para todos)
            //   - true: Crear directorios padres si no existen
            // @: Suprimir errores (si falla, no mostrar warning)
            @mkdir($log_dir, 0777, true);
        }
        
        // Ruta completa del archivo de log
        $log_file = $log_dir . '/email_log.txt';
        
        // Construir el mensaje con fecha/hora
        // date('Y-m-d H:i:s'): Formato año-mes-día hora:minuto:segundo
        // Ejemplo: "2025-11-24 01:30:45"
        // \n: Salto de línea
        $log_msg = date('Y-m-d H:i:s') . " - {$mensaje}\n";
        
        // file_put_contents(): Escribe contenido en un archivo
        // Parámetros:
        //   - Ruta del archivo
        //   - Contenido a escribir
        //   - FILE_APPEND: Agregar al final (no sobrescribir)
        // @: Suprimir errores
        @file_put_contents($log_file, $log_msg, FILE_APPEND);
    }
    
    // =========================================================================
    // MÉTODO: agregarPalabraProhibida
    // =========================================================================
    // Este método permite agregar palabras prohibidas dinámicamente
    // Es público para que se pueda llamar desde fuera de la clase
    //
    // EJEMPLO DE USO:
    // $moderador = new ModeradorLocal($conn);
    // $moderador->agregarPalabraProhibida("spam");
    //
    // @param string $palabra - Palabra a agregar a la lista de prohibidas
    public function agregarPalabraProhibida($palabra) {
        // in_array(): Verifica si un valor existe en un array
        // array_map(): Aplica una función a cada elemento de un array
        // 'strtolower': Convierte cada elemento a minúsculas
        // !in_array(): "si NO está en el array"
        if (!in_array(strtolower($palabra), array_map('strtolower', $this->palabras_prohibidas))) {
            // Agregar la palabra al array (en minúsculas)
            // []: Sintaxis para agregar al final del array
            $this->palabras_prohibidas[] = strtolower($palabra);
        }
    }
}

// =============================================================================
// FIN DE LA CLASE ModeradorLocal
// =============================================================================

?>
