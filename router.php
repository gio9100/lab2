<?php
$request = $_SERVER['REQUEST_URI'];

// Si acceden a la raíz "/", servir pagina-principal.php
if ($request == '/' || $request == '') {
    include 'pagina-principal.php';
    exit();
}

// Para otras rutas, comportamiento normal
if (file_exists(__DIR__ . $request)) {
    return false; // El servidor sirve el archivo normalmente
} else {
    // Si no existe el archivo, mostrar error o redirigir
    http_response_code(404);
    echo "Página no encontrada";
}
?>