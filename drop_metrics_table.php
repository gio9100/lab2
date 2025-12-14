<?php
// drop_metrics_table.php
require 'forms/conexion.php';

if ($conn->query("DROP TABLE IF EXISTS metricas_ia")) {
    echo "Tabla 'metricas_ia' eliminada correctamente.";
} else {
    echo "Error al eliminar tabla: " . $conn->error;
}
?>
