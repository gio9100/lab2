<?php
// Script para limpiar BOM de archivos PHP
$directorios = [
    '.', // Directorio actual
    '../forms/admins',
    // Agrega otros directorios si es necesario
];

foreach ($directorios as $dir) {
    if (is_dir($dir)) {
        $archivos = glob($dir . '/*.php');
        foreach ($archivos as $archivo) {
            $contenido = file_get_contents($archivo);
            // Verificar si tiene BOM
            if (substr($contenido, 0, 3) == "\xEF\xBB\xBF") {
                $contenido = substr($contenido, 3);
                file_put_contents($archivo, $contenido);
                echo "BOM eliminado de: $archivo<br>";
            }
        }
    }
}

echo "Proceso completado!";
?>