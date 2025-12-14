<?php
require_once 'forms/conexion.php';

$sql = file_get_contents('base_db/update_audit_logs.sql');
if (!$sql) {
    die("Error reading SQL file.");
}

// Remove comments and split by semi-colon
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $stmt) {
    if (!empty($stmt)) {
        if ($conexion->query($stmt) === TRUE) {
            echo "Success: " . substr($stmt, 0, 50) . "...\n";
        } else {
            echo "Error: " . $conexion->error . "\nRunning: " . $stmt . "\n";
        }
    }
}
echo "Migration completed.";
?>
