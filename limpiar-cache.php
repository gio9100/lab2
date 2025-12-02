<?php
// Limpiar caché de OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ Caché de OPcache limpiado\n";
} else {
    echo "⚠️ OPcache no está habilitado\n";
}

// Limpiar caché de archivos
clearstatcache(true);
echo "✅ Caché de archivos limpiado\n";

echo "\n🔄 Por favor refresca tu navegador (Ctrl + Shift + R)\n";
?>
