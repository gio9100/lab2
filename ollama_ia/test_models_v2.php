<?php
// Script de diagnóstico para listar modelos disponibles de Gemini
header('Content-Type: text/plain');
require_once '../forms/admins/config-admin.php';

$query = "SELECT gemini_api_key FROM configuracion_sistema LIMIT 1";
$result = $conn->query($query);
$apiKey = $result->fetch_assoc()['gemini_api_key'];

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        if (in_array("generateContent", $model['supportedGenerationMethods'])) {
            echo $model['name'] . "\n";
        }
    }
} else {
    echo "Error: " . $response;
}
?>
