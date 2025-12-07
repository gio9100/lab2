<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo "Start Debugging...<br>";

require_once 'init.php';
echo "Auth Checked (Role: " . ($current_user_role ?? 'None') . ")<br>";

$path_prefix = '../forms/publicadores/';
if(isset($current_user_role) && $current_user_role == 'admin') {
    $path_prefix = '../forms/admins/';
    echo "Including Admin Sidebar from: $path_prefix...<br>";
    include '../forms/admins/sidebar-admin.php'; 
} else {
    echo "Including Publisher Sidebar from: $path_prefix...<br>";
    include '../forms/publicadores/sidebar-publicador.php';
}
echo "<br>End Debugging.";
?>
