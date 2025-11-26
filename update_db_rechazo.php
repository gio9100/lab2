<?php
// Abrimos PHP
// Este archivo agrega la columna 'mensaje_rechazo' a la tabla publicaciones
// Se usa para guardar el motivo cuando un admin rechaza una publicación

$servername = "localhost";
// Servidor de la base de datos
$username = "root";
// Usuario de MySQL
$password = "";
// Contraseña (vacía en XAMPP)
$dbname = "lab_exp_db";
// Nombre de la base de datos

// Creamos la conexión
$conn = new mysqli($servername, $username, $password, $dbname);
// new mysqli() crea un objeto de conexión a MySQL

// Verificamos si hubo error en la conexión
if ($conn->connect_error) {
    // Si connect_error tiene algo, hubo un error
    die("Connection failed: " . $conn->connect_error);
    // die() detiene todo el código y muestra el mensaje
}

// Verificamos si la columna ya existe
$sql = "SHOW COLUMNS FROM publicaciones LIKE 'mensaje_rechazo'";
// SHOW COLUMNS FROM muestra las columnas de una tabla
// LIKE 'mensaje_rechazo' busca específicamente esa columna
$result = $conn->query($sql);
// query() ejecuta una consulta SQL directa (sin preparar)

if ($result->num_rows == 0) {
    // Si num_rows es 0, significa que la columna NO existe
    
    // Agregamos la columna
    $sql_add = "ALTER TABLE publicaciones ADD COLUMN mensaje_rechazo TEXT NULL AFTER estado";
    // ALTER TABLE modifica la estructura de una tabla
    // ADD COLUMN agrega una nueva columna
    // TEXT es el tipo de dato (texto largo)
    // NULL significa que puede estar vacía
    // AFTER estado la coloca después de la columna 'estado'
    
    if ($conn->query($sql_add) === TRUE) {
        // Si la consulta fue exitosa
        echo "Columna 'mensaje_rechazo' añadida correctamente.";
    } else {
        // Si hubo un error
        echo "Error al añadir columna: " . $conn->error;
        // $conn->error contiene el mensaje de error de MySQL
    }
} else {
    // Si num_rows es mayor a 0, la columna ya existe
    echo "La columna 'mensaje_rechazo' ya existe.";
}

$conn->close();
// close() cierra la conexión a la base de datos
// Es buena práctica cerrar las conexiones cuando ya no las necesitamos
?>
<!-- Cerramos PHP -->
