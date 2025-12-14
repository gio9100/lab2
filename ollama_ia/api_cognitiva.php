<?php
// API Backend para Herramientas Cognitivas (Gemini)
// Recibe solicitudes del frontend y se comunica con Google Gemini

header('Content-Type: application/json');
require_once '../forms/admins/config-admin.php'; // Para acceso a BD

// 1. Verificar configuración
$query = "SELECT * FROM configuracion_sistema LIMIT 1";
$result = $conn->query($query);
$config = $result ? $result->fetch_assoc() : null;

if (!$config) {
    echo json_encode(['error' => 'Error de configuración.']);
    exit;
}

$apiKey = $config['gemini_api_key'];
if (empty($apiKey)) {
    echo json_encode(['error' => 'Clave API no configurada.']);
    exit;
}

// 2. Obtener datos del request
$input = json_decode(file_get_contents('php://input'), true);
$task = $input['task'] ?? ''; 
$text = $input['text'] ?? '';
$context = $input['context'] ?? ''; // Para chat, el historial o contexto extra

if (empty($text) && $task !== 'chat_qa') { // Chat puede enviar solo pregunta en 'text' y contexto aparte
    echo json_encode(['error' => 'No se proporcionó texto.']);
    exit;
}

// 3. Validar Permisos por Tarea
$allowed = false;
switch ($task) {
    case 'simplify':
    case 'summarize':
    case 'translate':
        if ($config['enable_cognitive_tools']) $allowed = true;
        break;
    case 'quiz':
        if ($config['enable_quiz']) $allowed = true;
        break;
    case 'chat_qa':
        if ($config['enable_chat_qa']) $allowed = true;
        break;
    case 'improve_writing':
    case 'format_content':
        if ($config['enable_writing_assistant']) $allowed = true;
        break;
    case 'moderate_content':
        if ($config['enable_auto_moderation']) $allowed = true;
        break;
}

if (!$allowed) {
    echo json_encode(['error' => 'Esta función está desactivada por el administrador.']);
    exit;
}

// 4. Construir Prompt
$systemPrompt = "";
$userPrompt = "";

switch ($task) {
    case 'simplify':
        $systemPrompt = "Eres un profesor experto. Explica conceptos complejos de manera sencilla para niños.";
        $userPrompt = "Explica esto para un niño de 10 años: \n\n" . $text;
        break;
    case 'summarize':
        $systemPrompt = "Eres un analista experto. Sintetiza información.";
        $userPrompt = "Crea un resumen de máximo 300 caracteres (muy conciso) del siguiente texto: \n\n" . $text;
        break;
    case 'translate':
        // Mantenemos "Translate" genérico por si se reactiva, pero el usuario pidió quitar Purépecha del UI.
        // Lo dejamos para retrocompatibilidad o uso futuro.
        $systemPrompt = "Eres un traductor experto.";
        $userPrompt = "Traduce al Español neutro: \n\n" . $text;
        break;
    case 'quiz':
        $systemPrompt = "Eres un generador de exámenes educativos. Tu salida debe ser estrictamente un JSON válido con esta estructura: [{\"pregunta\": \"...\", \"opciones\": [\"A. ...\", \"B. ...\", \"C. ...\"], \"respuesta_correcta\": 0}] (donde respuesta_correcta es el índice 0-2).";
        $userPrompt = "Genera 3 preguntas de opción múltiple (3 opciones cada una) para evaluar la comprensión de este texto. Devuelve SOLO el JSON sin markdown ```json ```: \n\n" . $text;
        break;
    case 'chat_qa':
        $systemPrompt = "Eres un asistente útil que responde preguntas basándose ÚNICAMENTE en el contexto proporcionado del artículo. Si la respuesta no está en el texto, di que no lo sabes.";
        $userPrompt = "Contexto del Artículo:\n" . $context . "\n\nPregunta del Usuario:\n" . $text;
        break;
    case 'improve_writing':
        $systemPrompt = "Eres un editor de estilo profesional. Mejoras la gramática, fluidez y tono sin cambiar el significado.";
        $userPrompt = "Mejora la redacción del siguiente texto: \n\n" . $text;
        break;
    case 'format_content':
        $systemPrompt = "Eres un asistente de edición web experto. Tu única tarea es convertir texto sucio o desordenado en HTML semántico PERFECTO para un editor WYSIWYG.";
        $userPrompt = "Instrucciones:
        1. Analiza el siguiente texto y detecta su estructura (Títulos, Subtítulos, Listas, Párrafos).
        2. NO cambies el contenido ni el redacción, solo aplica etiquetas HTML.
        3. Usa <h2> para el tema principal, <h3> para subtemas.
        4. Convierte listados en <ul> o <ol>.
        5. Devuelve SOLAMENTE el código HTML crudo sin bloques de código Markdown (sin ```html ... ```).
        6. Si el texto es muy corto, solo devuélvelo limpio en <p>.

        Texto a formatear: 
        " . $text;
        break;
    case 'moderate_content':
        $systemPrompt = "Eres un moderador de contenido. Analiza el texto en busca de: Odio, Violencia, Sexual explícito, Spam. Devuelve un JSON: {\"risk_score\": 0-100, \"flags\": [\"flag1\", \"flag2\"], \"action\": \"approve\"|\"review\"|\"reject\"}.";
        $userPrompt = "Analiza este contenido: \n\n" . $text;
        break;
    default:
        echo json_encode(['error' => 'Tarea desconocida.']);
        exit;
}

// 4. Llamar a Gemini API (gemini-2.5-flash)
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $systemPrompt . "\n\n" . $userPrompt]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Error de conexión con Gemini: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$decoded = json_decode($response, true);

// 5. Procesar respuesta
if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
    $aiText = $decoded['candidates'][0]['content']['parts'][0]['text'];
    // Formatear Markdown básico a HTML simple si es necesario, o enviarlo raw
    // Por ahora enviamos raw, el frontend puede usar marked.js o simple replace
    echo json_encode(['success' => true, 'result' => $aiText]);
} else {
    // Error de la API (ej. clave inválida, cuota excedida)
    $errorMsg = isset($decoded['error']['message']) ? $decoded['error']['message'] : 'Respuesta inesperada de la IA';
    echo json_encode(['error' => 'Error de IA: ' . $errorMsg]);
}
?>
