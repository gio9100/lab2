<?php
// =============================================================================
// 📄 ARCHIVO: ModeradorLocal.php
// =============================================================================
//
// 🎯 PROPÓSITO PRINCIPAL:
// Esta clase contiene toda la lógica de moderación automática basada en reglas.
// Analiza el contenido de las publicaciones para determinar si cumplen con los
// estándares de calidad y seguridad de la plataforma.
//
// 🧠 FUNCIONAMIENTO:
// 1. Obtiene la publicación de la base de datos.
// 2. Realiza múltiples validaciones (palabras prohibidas, longitud, formato).
// 3. Calcula una puntuación de calidad (0-100).
// 4. Toma una decisión automática (Aprobar o Rechazar).
// 5. Notifica a los usuarios involucrados por correo electrónico.
//
// =============================================================================

// Incluimos la librería PHPMailer para el envío de correos
require_once __DIR__ . '/../forms/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../forms/PHPMailer/SMTP.php';
require_once __DIR__ . '/../forms/PHPMailer/Exception.php';

// Incluimos el Helper de Emails para el diseño profesional
require_once __DIR__ . '/../forms/EmailHelper.php';

// Importamos las clases de PHPMailer al espacio de nombres actual
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Definición de la clase ModeradorLocal
class ModeradorLocal {
    
    // Propiedad para almacenar la conexión a la base de datos
    private $conn;
    
    // Lista de palabras que causan rechazo inmediato (groserías, spam)
    private $palabras_prohibidas = [
        'puto', 'puta', 'pendejo', 'pendeja', 'cabrón', 'cabrona',
        'chingar', 'verga', 'mierda', 'coño', 'joder',
        'viagra', 'casino', 'poker', 'apuestas', 'ganar dinero fácil',
        'haz clic aquí', 'compra ahora', 'oferta limitada',
        'porno', 'xxx', 'sexo gratis', 'desnudo'
    ];
    
    // Lista de palabras que suman puntos por calidad académica
    private $palabras_academicas = [
        'investigación', 'estudio', 'análisis', 'metodología',
        'resultados', 'conclusión', 'hipótesis', 'experimento',
        'teoría', 'evidencia', 'datos', 'muestra', 'bibliografía',
        'referencias', 'abstract', 'resumen', 'objetivo'
    ];
    
    // Constructor de la clase
    // Recibe la conexión a la base de datos y la asigna a la propiedad local
    public function __construct($conexion_bd) {
        $this->conn = $conexion_bd;
    }
    
    // Método principal que orquesta todo el análisis
    // Recibe el ID de la publicación y devuelve el resultado del análisis
    public function analizarPublicacion($publicacion_id) {
        // Obtenemos los datos completos de la publicación desde la BD
        $publicacion = $this->obtenerPublicacion($publicacion_id);
        
        // Si no se encuentra la publicación, devolvemos un error
        if (!$publicacion) {
            return [
                'success' => false,
                'error' => 'Publicación no encontrada'
            ];
        }
        
        // Inicializamos la puntuación base en 100 puntos
        $puntuacion = 100;
        
        // Inicializamos el array para guardar las razones de la decisión
        $razones = [];
        
        // --- VALIDACIÓN 1: Longitud mínima ---
        // Obtenemos la longitud del contenido
        $longitud = strlen($publicacion['contenido']);
        
        // Si es menor a 75 caracteres, rechazamos inmediatamente
        if ($longitud < 75) {
            $decision = 'rechazada';
            $razon = "Contenido demasiado corto ({$longitud} caracteres). Mínimo requerido: 75.";
            $puntuacion = 0;
            
            // Guardamos el log y actualizamos el estado
            $this->guardarAnalisis($publicacion_id, $decision, $razon, $puntuacion);
            $this->actualizarEstadoPublicacion($publicacion_id, $decision, $razon);
            
            return [
                'success' => true,
                'decision' => $decision,
                'razon' => $razon,
                'confianza' => 100,
                'tipo_analisis' => 'reglas_automaticas'
            ];
        }
        
        // --- VALIDACIÓN 2: Palabras prohibidas ---
        // Buscamos si hay palabras prohibidas en el texto
        $palabras_encontradas = $this->buscarPalabrasProhibidas($publicacion);
        
        // Si encontramos alguna, rechazamos inmediatamente
        if (!empty($palabras_encontradas)) {
            $lista = implode(', ', $palabras_encontradas);
            $decision = 'rechazada';
            $razon = "Contiene palabras prohibidas: {$lista}";
            $puntuacion = 0;
            
            $this->guardarAnalisis($publicacion_id, $decision, $razon, $puntuacion);
            $this->actualizarEstadoPublicacion($publicacion_id, $decision, $razon);
            
            return [
                'success' => true,
                'decision' => $decision,
                'razon' => $razon,
                'confianza' => 100,
                'tipo_analisis' => 'reglas_automaticas'
            ];
        }
        
        // --- ANÁLISIS DE CALIDAD ---
        // Si pasa las validaciones básicas, analizamos la calidad del contenido
        $analisis_calidad = $this->analizarCalidad($publicacion);
        
        // Actualizamos la puntuación y agregamos las razones encontradas
        $puntuacion = $analisis_calidad['puntuacion'];
        $razones = array_merge($razones, $analisis_calidad['razones']);
        
        // --- DECISIÓN FINAL ---
        // Determinamos el estado basado en la puntuación final
        // REGLA: Si la puntuación es 60 o más, se APRUEBA. Si no, se RECHAZA.
        if ($puntuacion >= 60) {
            // Puntuación suficiente: APROBADO
            $decision = 'publicado';
            $razon = "Aprobada automáticamente (Puntuación: {$puntuacion}/100). " . implode('. ', $razones);
            
        } else {
            // Puntuación insuficiente: RECHAZADO
            $decision = 'rechazada';
            $razon = "Rechazada por no cumplir estándares mínimos (Puntuación: {$puntuacion}/100). " . implode('. ', $razones);
        }
        
        // Guardamos el resultado del análisis en el historial
        $this->guardarAnalisis($publicacion_id, $decision, $razon, $puntuacion);
        
        // Actualizamos el estado de la publicación y enviamos correos
        $this->actualizarEstadoPublicacion($publicacion_id, $decision, $razon);
        
        // Devolvemos el resultado final
        return [
            'success' => true,
            'decision' => $decision,
            'razon' => $razon,
            'confianza' => $puntuacion,
            'tipo_analisis' => 'moderacion_automatica',
            'detalles' => $analisis_calidad['detalles'] // Incluimos detalles extra para el panel
        ];
    }
    
    // Método para buscar palabras prohibidas en título y contenido
    private function buscarPalabrasProhibidas($publicacion) {
        $encontradas = [];
        // Unimos título y contenido y convertimos a minúsculas
        $texto = strtolower($publicacion['titulo'] . ' ' . $publicacion['contenido']);
        
        // Recorremos la lista de palabras prohibidas
        foreach ($this->palabras_prohibidas as $palabra) {
            // Si la palabra está en el texto, la agregamos a la lista
            if (strpos($texto, strtolower($palabra)) !== false) {
                $encontradas[] = $palabra;
            }
        }
        return $encontradas;
    }
    
    // Método para analizar la calidad del contenido con múltiples criterios
    private function analizarCalidad($publicacion) {
        $puntuacion = 100;
        $razones = [];
        $detalles = []; // Array para guardar estadísticas
        
        $titulo = $publicacion['titulo'];
        $contenido = $publicacion['contenido'];
        $texto_completo = strtolower($titulo . ' ' . $contenido);
        
        // --- CRITERIO 1: Vocabulario Académico ---
        $palabras_acad_encontradas = 0;
        foreach ($this->palabras_academicas as $palabra) {
            if (strpos($texto_completo, strtolower($palabra)) !== false) {
                $palabras_acad_encontradas++;
            }
        }
        
        $detalles['palabras_academicas'] = $palabras_acad_encontradas;
        
        if ($palabras_acad_encontradas >= 3) {
            $razones[] = "Buen vocabulario académico";
        } else if ($palabras_acad_encontradas >= 1) {
            $puntuacion -= 10;
            $razones[] = "Vocabulario académico limitado";
        } else {
            $puntuacion -= 20;
            $razones[] = "Falta vocabulario técnico/científico";
        }
        
        // --- CRITERIO 2: Estructura y Párrafos ---
        // Dividimos el contenido por saltos de línea
        $parrafos = explode("\n", $contenido);
        // Filtramos párrafos vacíos o muy cortos
        $parrafos = array_filter($parrafos, function($p) {
            return strlen(trim($p)) > 30;
        });
        $num_parrafos = count($parrafos);
        
        $detalles['parrafos_validos'] = $num_parrafos;
        
        if ($num_parrafos >= 3) {
            $razones[] = "Buena estructura en párrafos";
        } else {
            $puntuacion -= 15;
            $razones[] = "Estructura pobre (pocos párrafos)";
        }
        
        // --- CRITERIO 3: Uso excesivo de mayúsculas (GRITOS) ---
        // Contamos letras mayúsculas en el título
        $mayusculas = preg_match_all('/[A-ZÁÉÍÓÚÑ]/', $titulo);
        $total_letras = strlen($titulo);
        // Si más del 50% son mayúsculas y el título es largo
        if ($total_letras > 10 && ($mayusculas / $total_letras) > 0.5) {
            $puntuacion -= 15;
            $razones[] = "Uso excesivo de mayúsculas en título";
        }
        
        // --- CRITERIO 4: Exceso de enlaces (Posible Spam) ---
        // Contamos ocurrencias de 'http' o 'www'
        $num_enlaces = substr_count($texto_completo, 'http') + substr_count($texto_completo, 'www');
        
        $detalles['enlaces_detectados'] = $num_enlaces;
        
        if ($num_enlaces > 3) {
            $puntuacion -= 25;
            $razones[] = "Exceso de enlaces externos (posible spam)";
        }
        
        // --- CRITERIO 5: Formato (Listas) ---
        // Buscamos patrones de listas (- item, * item, 1. item)
        if (preg_match('/^[\s]*[-*•]\s/m', $contenido) || preg_match('/^[\s]*\d+\.\s/m', $contenido)) {
            // Bonificación por usar listas (mejora legibilidad)
            // No sumamos a puntuacion (max 100), pero evitamos restar si estaba bajo
            $razones[] = "Uso correcto de listas";
        }
        
        // Aseguramos que la puntuación esté entre 0 y 100
        return [
            'puntuacion' => max(0, min(100, $puntuacion)),
            'razones' => $razones,
            'detalles' => $detalles
        ];
    }
    
    // Método para obtener los datos de la publicación de la BD
    private function obtenerPublicacion($id) {
        $query = "SELECT * FROM publicaciones WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        return null;
    }
    
    // Método para guardar el registro del análisis en la BD
    private function guardarAnalisis($publicacion_id, $decision, $razon, $confianza) {
        $query = "INSERT INTO moderacion_ia_logs 
                  (publicacion_id, decision, razon, confianza, fecha_analisis) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issi", $publicacion_id, $decision, $razon, $confianza);
        $stmt->execute();
    }
    
    // Método para actualizar el estado y notificar usuarios
    private function actualizarEstadoPublicacion($publicacion_id, $nuevo_estado, $razon = null) {
        // Obtenemos datos del publicador para el correo
        $query_pub = "SELECT p.*, pub.nombre as publicador_nombre, pub.email as publicador_email 
                      FROM publicaciones p 
                      INNER JOIN publicadores pub ON p.publicador_id = pub.id 
                      WHERE p.id = ?";
        $stmt = $this->conn->prepare($query_pub);
        $stmt->bind_param("i", $publicacion_id);
        $stmt->execute();
        $datos = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Actualizamos el estado en la BD
        if ($nuevo_estado === 'rechazada' && $razon !== null) {
            $query = "UPDATE publicaciones SET estado = ?, mensaje_rechazo = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssi", $nuevo_estado, $razon, $publicacion_id);
        } else {
            $query = "UPDATE publicaciones SET estado = ?, mensaje_rechazo = NULL WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $nuevo_estado, $publicacion_id);
        }
        $stmt->execute();
        $stmt->close();
        
        // Enviamos notificaciones si tenemos los datos
        if ($datos && isset($datos['publicador_email'])) {
            $this->enviarCorreoNotificacion(
                $datos['publicador_email'],
                $datos['publicador_nombre'],
                $datos['titulo'],
                $nuevo_estado,
                $razon
            );
            $this->notificarAdministradores($datos, $nuevo_estado, $razon);
        }
    }
    
    // Método para notificar a todos los administradores
    private function notificarAdministradores($datos_publicacion, $estado, $razon = null) {
        $query = "SELECT email, nombre FROM admins";
        $resultado = $this->conn->query($query);
        
        if ($resultado && $resultado->num_rows > 0) {
            while ($admin = $resultado->fetch_assoc()) {
                $this->enviarCorreoAdmin(
                    $admin['email'],
                    $admin['nombre'],
                    $datos_publicacion,
                    $estado,
                    $razon
                );
            }
        }
    }
    
    // Configuración de PHPMailer con credenciales de Gmail
    private function configurarPHPMailer() {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lab.explorer2025@gmail.com';
        $mail->Password = 'yero ewft jacf vjzp'; // Contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setFrom('lab.explorer2025@gmail.com', 'Lab Explorer');
        return $mail;
    }
    
    // Envío de correo a un administrador
    private function enviarCorreoAdmin($email_admin, $nombre_admin, $datos_pub, $estado, $razon = null) {
        if ($estado !== 'publicado' && $estado !== 'rechazada') return;
        
        $asunto = $estado === 'publicado' 
            ? "Publicación Aprobada - Lab Explorer"
            : "Publicación Rechazada - Lab Explorer";
        
        $tipo_estado = $estado === 'publicado' ? 'aprobado' : 'rechazado';
        
        // Datos para la tabla de detalles
        $detalles = [
            'Título' => $datos_pub['titulo'],
            'Publicador' => $datos_pub['publicador_nombre'],
            'Estado' => strtoupper($estado),
            'Fecha' => date('d/m/Y H:i')
        ];
        
        if ($razon) {
            $detalles['Motivo'] = $razon;
        }
        
        // Mensaje principal
        $mensaje = "Se ha procesado automáticamente una publicación.";
        
        // Botón de acción
        $boton = [
            'texto' => 'Ver Publicación',
            'url' => 'http://localhost/Lab/forms/admins/gestionar-publicaciones.php'
        ];
        
        // Generar HTML usando el Helper
        $mensaje_html = EmailHelper::render($asunto, $nombre_admin, $mensaje, $detalles, $boton, $tipo_estado);
        
        try {
            $mail = $this->configurarPHPMailer();
            $mail->addAddress($email_admin, $nombre_admin);
            $mail->Subject = $asunto;
            $mail->isHTML(true);
            $mail->Body = $mensaje_html;
            $mail->send();
            $this->log("Correo enviado a admin: {$email_admin}");
        } catch (Exception $e) {
            $this->log("Error enviando correo a admin: " . $e->getMessage());
        }
    }
    
    // Envío de correo de notificación al publicador
    private function enviarCorreoNotificacion($email, $nombre, $titulo_publicacion, $estado, $razon = null) {
        if ($estado !== 'publicado' && $estado !== 'rechazada') return;
        
        $asunto = $estado === 'publicado' 
            ? "¡Felicidades! Tu publicación ha sido aprobada"
            : "Actualización sobre tu publicación";
            
        $tipo_estado = $estado === 'publicado' ? 'aprobado' : 'rechazado';
        
        // Mensaje personalizado según el estado
        if ($estado === 'publicado') {
            $mensaje = "Nos complace informarte que tu publicación ha pasado exitosamente nuestro proceso de moderación automática y ya está visible en la plataforma.";
        } else {
            $mensaje = "Tu publicación ha sido revisada por nuestro sistema automático y lamentablemente no cumple con algunos de nuestros criterios de calidad.";
        }
        
        // Detalles para la tabla
        $detalles = [
            'Publicación' => $titulo_publicacion,
            'Estado Final' => strtoupper($estado),
            'Fecha Revisión' => date('d/m/Y H:i')
        ];
        
        if ($razon) {
            $detalles['Comentarios'] = $razon;
        }
        
        // Botón
        $boton = [
            'texto' => 'Ver Mis Publicaciones',
            'url' => 'http://localhost/Lab/forms/publicadores/mis-publicaciones.php'
        ];
        
        // Generar HTML usando el Helper
        $mensaje_html = EmailHelper::render($asunto, $nombre, $mensaje, $detalles, $boton, $tipo_estado);
            
        try {
            $mail = $this->configurarPHPMailer();
            $mail->addAddress($email, $nombre);
            $mail->Subject = $asunto;
            $mail->isHTML(true);
            $mail->Body = $mensaje_html;
            $mail->send();
            $this->log("✅ Correo enviado a publicador: {$email}");
        } catch (Exception $e) {
            $this->log("❌ Error enviando correo a publicador: " . $e->getMessage());
        }
    }
    
    // Método auxiliar para registrar logs
    private function log($mensaje) {
        $log_dir = __DIR__ . '/logs';
        if (!file_exists($log_dir)) @mkdir($log_dir, 0777, true);
        $log_file = $log_dir . '/email_log.txt';
        $log_msg = date('Y-m-d H:i:s') . " - {$mensaje}\n";
        @file_put_contents($log_file, $log_msg, FILE_APPEND);
    }
}
?>
