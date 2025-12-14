<?php
// check_active_announcements.php
require 'forms/conexion.php';

echo "<h2>Verificando Anuncios Activos</h2>";
echo "Fecha/Hora PHP: " . date('Y-m-d H:i:s') . "<br>";

$query = "SELECT * FROM anuncios_sistema WHERE activo = 1";
$result = $conexion->query($query);

if ($result->num_rows > 0) {
    echo "<h3>Anuncios con activo=1:</h3><ul>";
    while ($row = $result->fetch_assoc()) {
        $fecha_fin = $row['fecha_fin'] ? $row['fecha_fin'] : 'NULL';
        echo "<li>ID: {$row['id']} - Msj: {$row['mensaje']} - Fin: {$fecha_fin}</li>";
        
        // Check query logic manually
        if ($row['fecha_fin'] === NULL || strtotime($row['fecha_fin']) > time()) {
            echo " -> <strong>DEBERÍA SER VISIBLE</strong> (Cumple fecha)";
        } else {
            echo " -> <strong>NO VISIBLE</strong> (Fecha expirada)";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>NO HAY anuncios con activo=1 en la base de datos.</p>";
}
?>
