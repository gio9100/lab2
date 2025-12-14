<?php
// debug_metrics.php
// Script para diagnosticar por qué no se guardan las métricas

require_once 'forms/admins/config-admin.php';

echo "<h2>Diagnóstico de Métricas IA</h2>";

// 1. Verificar conexión
if ($conn->connect_error) {
    die("<p style='color:red'>Error de conexión: " . $conn->connect_error . "</p>");
}
echo "<p style='color:green'>Conexión a BD exitosa.</p>";

// 2. Verificar estructura de tabla
echo "<h3>Estructura de la tabla 'metricas_ia':</h3>";
$result = $conn->query("DESCRIBE metricas_ia");
if ($result) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>Error al describir tabla: " . $conn->error . "</p>";
}

// 3. Intentar inserción de prueba
echo "<h3>Prueba de Inserción:</h3>";
$sql = "INSERT INTO metricas_ia (fecha, tokens_input, tokens_output, modelo) VALUES (NOW(), 100, 50, 'test-debug')";

if ($conn->query($sql)) {
    echo "<p style='color:green'>✅ Inserción de prueba EXITOSA.</p>";
    echo "<p>Recarga el dashboard ahora. Deberías ver datos.</p>";
} else {
    echo "<p style='color:red'>❌ Error en inserción: " . $conn->error . "</p>";
    echo "<p>Posible causa: Nombres de columnas incorrectos en el código vs base de datos.</p>";
}

// 4. Mostrar datos actuales
echo "<h3>Datos Actuales en Tabla:</h3>";
$res_count = $conn->query("SELECT COUNT(*) as c FROM metricas_ia");
$row_count = $res_count->fetch_assoc();
echo "<p>Total filas: " . $row_count['c'] . "</p>";

?>
