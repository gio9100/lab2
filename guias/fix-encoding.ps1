# Script para corregir problemas de codificacion en todos los archivos PHP
# Reemplaza caracteres mal codificados por sus equivalentes correctos

$projectPath = "c:\xampp\htdocs\Lab"

# Obtener todos los archivos PHP (excluyendo backups, PHPMailer y vendor)
$phpFiles = Get-ChildItem -Path $projectPath -Filter "*.php" -Recurse | Where-Object { 
    $_.FullName -notmatch '\.backup' -and 
    $_.FullName -notmatch 'vendor' -and
    $_.FullName -notmatch 'node_modules' -and
    $_.FullName -notmatch 'PHPMailer'
}

$totalFiles = $phpFiles.Count
$processedFiles = 0
$modifiedFiles = 0

Write-Host "Encontrados $totalFiles archivos PHP para procesar..." -ForegroundColor Cyan
Write-Host ""

foreach ($file in $phpFiles) {
    $processedFiles++
    Write-Progress -Activity "Corrigiendo codificacion" -Status "Procesando: $($file.Name)" -PercentComplete (($processedFiles / $totalFiles) * 100)
    
    try {
        # Leer el contenido del archivo
        $content = Get-Content $file.FullName -Raw -Encoding UTF8
        $originalContent = $content
        
        # Aplicar reemplazos de caracteres individuales
        $content = $content -replace 'Ã³', 'ó'
        $content = $content -replace 'Ã­', 'í'
        $content = $content -replace 'Ã©', 'é'
        $content = $content -replace 'Ã¡', 'á'
        $content = $content -replace 'Ãº', 'ú'
        $content = $content -replace 'Ã±', 'ñ'
        $content = $content -replace 'Ã"', 'Ó'
        $content = $content -replace 'Ã', 'Í'
        $content = $content -replace 'Ã‰', 'É'
        $content = $content -replace 'Ãš', 'Ú'
        $content = $content -replace 'Ã¼', 'ü'
        $content = $content -replace 'Ã¶', 'ö'
        $content = $content -replace 'Ã¤', 'ä'
        $content = $content -replace 'Ã§', 'ç'
        $content = $content -replace 'Â¿', '¿'
        $content = $content -replace 'Â¡', '¡'
        $content = $content -replace 'Â°', '°'
        
        # Aplicar reemplazos de palabras comunes
        $content = $content -replace 'publicaciÃ³n', 'publicación'
        $content = $content -replace 'descripciÃ³n', 'descripción'
        $content = $content -replace 'informaciÃ³n', 'información'
        $content = $content -replace 'sesiÃ³n', 'sesión'
        $content = $content -replace 'categorÃ­a', 'categoría'
        $content = $content -replace 'categorÃ­as', 'categorías'
        $content = $content -replace 'mÃ¡ximo', 'máximo'
        $content = $content -replace 'mÃ­nimo', 'mínimo'
        $content = $content -replace 'tÃ­tulo', 'título'
        $content = $content -replace 'cÃ³digo', 'código'
        $content = $content -replace 'nÃºmero', 'número'
        $content = $content -replace 'pÃ¡gina', 'página'
        $content = $content -replace 'imÃ¡genes', 'imágenes'
        $content = $content -replace 'aquÃ­', 'aquí'
        $content = $content -replace 'allÃ­', 'allí'
        $content = $content -replace 'ahÃ­', 'ahí'
        $content = $content -replace 'tambiÃ©n', 'también'
        $content = $content -replace 'ademÃ¡s', 'además'
        $content = $content -replace 'despuÃ©s', 'después'
        $content = $content -replace 'espaÃ±ol', 'español'
        $content = $content -replace 'aÃ±o', 'año'
        $content = $content -replace 'aÃ±os', 'años'
        $content = $content -replace 'niÃ±o', 'niño'
        $content = $content -replace 'seÃ±or', 'señor'
        $content = $content -replace 'contraseÃ±a', 'contraseña'
        $content = $content -replace 'electrÃ³nico', 'electrónico'
        $content = $content -replace 'electrÃ³nica', 'electrónica'
        $content = $content -replace 'notificaciÃ³n', 'notificación'
        $content = $content -replace 'verificaciÃ³n', 'verificación'
        $content = $content -replace 'configuraciÃ³n', 'configuración'
        $content = $content -replace 'administraciÃ³n', 'administración'
        $content = $content -replace 'moderaciÃ³n', 'moderación'
        $content = $content -replace 'creaciÃ³n', 'creación'
        $content = $content -replace 'ediciÃ³n', 'edición'
        $content = $content -replace 'eliminaciÃ³n', 'eliminación'
        $content = $content -replace 'validaciÃ³n', 'validación'
        $content = $content -replace 'autenticaciÃ³n', 'autenticación'
        $content = $content -replace 'autorizaciÃ³n', 'autorización'
        $content = $content -replace 'operaciÃ³n', 'operación'
        $content = $content -replace 'funciÃ³n', 'función'
        $content = $content -replace 'soluciÃ³n', 'solución'
        $content = $content -replace 'versiÃ³n', 'versión'
        $content = $content -replace 'revisiÃ³n', 'revisión'
        $content = $content -replace 'decisiÃ³n', 'decisión'
        $content = $content -replace 'precisiÃ³n', 'precisión'
        $content = $content -replace 'inclusiÃ³n', 'inclusión'
        $content = $content -replace 'exclusiÃ³n', 'exclusión'
        $content = $content -replace 'conclusiÃ³n', 'conclusión'
        $content = $content -replace 'atenciÃ³n', 'atención'
        $content = $content -replace 'direcciÃ³n', 'dirección'
        $content = $content -replace 'relaciÃ³n', 'relación'
        $content = $content -replace 'situaciÃ³n', 'situación'
        $content = $content -replace 'aplicaciÃ³n', 'aplicación'
        $content = $content -replace 'comunicaciÃ³n', 'comunicación'
        
        # Solo guardar si hubo cambios
        if ($content -ne $originalContent) {
            Set-Content $file.FullName -Value $content -Encoding UTF8 -NoNewline
            $modifiedFiles++
            Write-Host "Corregido: $($file.Name)" -ForegroundColor Green
        }
    }
    catch {
        Write-Host "Error en: $($file.Name) - $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Proceso completado:" -ForegroundColor Cyan
Write-Host "  - Archivos procesados: $processedFiles" -ForegroundColor White
Write-Host "  - Archivos modificados: $modifiedFiles" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
