<?php
// mensajes/debug_session.php
// Script para depurar qué sesión está activa

session_start();

echo "<h2>Debug de Sesiones</h2>";

echo "<h3>Parámetro GET:</h3>";
echo "?as = " . ($_GET['as'] ?? 'NO DEFINIDO') . "<br><br>";

echo "<h3>Sesiones Activas:</h3>";
echo "Admin ID: " . ($_SESSION['admin_id'] ?? 'NO') . "<br>";
echo "Admin Nombre: " . ($_SESSION['admin_nombre'] ?? 'NO') . "<br>";
echo "Publicador ID: " . ($_SESSION['publicador_id'] ?? 'NO') . "<br>";
echo "Publicador Nombre: " . ($_SESSION['publicador_nombre'] ?? 'NO') . "<br>";

echo "<h3>Todas las variables de sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
