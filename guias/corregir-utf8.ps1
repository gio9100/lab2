# Script para corregir codificación UTF-8 en ver-publicacion.php

Write-Host "Corrigiendo codificación de ver-publicacion.php..."

# Leer como bytes
$bytes = [System.IO.File]::ReadAllBytes("ver-publicacion.php")

# Convertir de Latin1 a UTF-8
$latin1 = [System.Text.Encoding]::GetEncoding("ISO-8859-1")
$utf8 = [System.Text.Encoding]::UTF8

$text = $latin1.GetString($bytes)
$utf8Bytes = $utf8.GetBytes($text)

# Guardar con UTF-8
[System.IO.File]::WriteAllBytes("ver-publicacion.php", $utf8Bytes)

Write-Host "✅ Codificación corregida!"
Write-Host "Ahora refresca tu navegador (Ctrl + Shift + R)"
