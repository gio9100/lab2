<?php
// Script to detect files with BOM (Byte Order Mark)

$directories = [
    __DIR__ . '/ollama_ia',
    __DIR__ . '/forms/admins',
    __DIR__ . '/forms/PHPMailer'
];

echo "Scanning for BOM...\n";

function scanDirectory($dir) {
    if (!is_dir($dir)) {
        echo "Directory not found: $dir\n";
        return;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            checkBOM($file->getPathname());
        }
    }
}

function checkBOM($filePath) {
    $handle = fopen($filePath, 'r');
    if (!$handle) return;

    $bytes = fread($handle, 3);
    fclose($handle);

    if ($bytes === "\xEF\xBB\xBF") {
        echo "BOM FOUND: $filePath\n";
    }
}

foreach ($directories as $dir) {
    scanDirectory($dir);
}

echo "Scan complete.\n";
?>
