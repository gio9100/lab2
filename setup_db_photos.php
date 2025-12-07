<?php
// setup_db_photos.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Función auxiliar para verificar y crear columnas
function addColumnIfNotExists($conn, $table, $column, $definition) {
    $check = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if ($conn->query($sql) === TRUE) {
            echo "Columna `$column` agregada a tabla `$table` correctamente.<br>";
        } else {
            echo "Error agregando columna a $table: " . $conn->error . "<br>";
        }
    } else {
        echo "Columna `$column` ya existe en tabla `$table`.<br>";
    }
}

// Agregar columna foto_perfil a administradores
addColumnIfNotExists($conn, "admins", "foto_perfil", "VARCHAR(255) DEFAULT NULL");

// Agregar columna foto_perfil a publicadores
addColumnIfNotExists($conn, "publicadores", "foto_perfil", "VARCHAR(255) DEFAULT NULL");

$conn->close();
echo "Configuración de base de datos completada.";
?>
