<?php
require 'forms/conexion.php';
$result = $conexion->query("SELECT * FROM anuncios_sistema");
echo "Count: " . $result->num_rows . "\n";
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
