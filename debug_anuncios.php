<?php
// debug_anuncios.php
require 'forms/admins/config-admin.php'; // Usa config admin para tener $conn y timezone

echo "<h1>Diagnóstico de Anuncios</h1>";
echo "Timezone PHP: " . date_default_timezone_get() . "<br>";
echo "Hora PHP: " . date('Y-m-d H:i:s') . "<br>";

$res_time = $conn->query("SELECT NOW() as db_time");
$row_time = $res_time->fetch_assoc();
echo "Hora DB (NOW()): " . $row_time['db_time'] . "<br>";

echo "<h2>Tabla anuncios_sistema</h2>";
$res = $conn->query("SELECT * FROM anuncios_sistema ORDER BY id DESC");
echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Mensaje</th><th>Activo</th><th>Fecha Fin</th><th>Status DB Logic</th></tr>";
while ($row = $res->fetch_assoc()) {
    $is_active_logic = ($row['activo'] == 1 && ($row['fecha_fin'] === NULL || $row['fecha_fin'] > $row_time['db_time'])) ? 'VISIBLE' : 'OCULTO';
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['mensaje']}</td>";
    echo "<td>{$row['activo']}</td>";
    echo "<td>" . ($row['fecha_fin'] ? $row['fecha_fin'] : 'NULL') . "</td>";
    echo "<td>{$is_active_logic}</td>";
    echo "</tr>";
}
echo "</table>";

if (file_exists('forms/admins/admin_metricas_ia.php')) {
    echo "<h2 style='color:red'>⚠️ admin_metricas_ia.php AÚN EXISTE</h2>";
} else {
    echo "<h2 style='color:green'>✅ admin_metricas_ia.php eliminado correctamente</h2>";
}
?>
