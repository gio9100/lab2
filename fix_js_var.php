<?php
$file = 'c:/xampp/htdocs/lab2/forms/publicadores/registro-publicadores.php';
$content = file_get_contents($file);

// Replace variable declaration
$content = str_replace(
    "const nombreInput = document.getElementById('nombre');",
    "const nombreInputPub = document.getElementById('nombre');",
    $content
);

// Replace usage in if condition
$content = str_replace(
    "if (nombreInput) {",
    "if (nombreInputPub) {",
    $content
);

// Replace usage in event listener
$content = str_replace(
    "nombreInput.addEventListener",
    "nombreInputPub.addEventListener",
    $content
);

file_put_contents($file, $content);
echo "Fixed JS variable in $file\n";
?>
