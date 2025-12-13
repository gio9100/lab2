<?php
/**
 * FuncionesTexto.php
 * Helper para extracción y manipulación de texto en diferentes formatos.
 */

class FuncionesTexto {
    
    /**
     * Extrae texto plano de un archivo PDF usando PHP nativo.
     * Intenta decodificar streams comprimidos y extraer texto de objetos.
     */
    public static function extraerTextoPdf($filename) {
        if (!file_exists($filename)) return "";

        // Leemos el contenido binario del archivo
        $content = file_get_contents($filename);
        $text = "";

        // 1. Extraer objetos de texto (BT...ET)
        // Buscamos bloques entre BT (Begin Text) y ET (End Text)
        if (preg_match_all('/BT[\s\S]*?ET/', $content, $matches)) {
            foreach ($matches[0] as $object) {
                // Buscamos strings entre paréntesis (...) o corchetes [...]
                if (preg_match_all('/\((.*?)\)|\[(.*?)\]/', $object, $texts)) {
                    foreach ($texts[0] as $t) {
                        // Limpiamos paréntesis y comandos PDF básicos
                        $clean = trim($t, '()[]'); 
                        // Decodificamos caracteres escapados básicos
                        $clean = str_replace(['\\\\', '\(', '\)'], ['\\', '(', ')'], $clean);
                        $text .= $clean . " ";
                    }
                }
            }
        }

        // 2. Si no encontramos mucho texto, intentar extraer todo lo que parezca texto entre paréntesis
        // Esto sirve para PDFs que no usan BT/ET estrictamente o tienen estructura diferente
        if (strlen($text) < 50) {
            if (preg_match_all('/\((.*?)\)/', $content, $matches)) {
                foreach ($matches[1] as $m) {
                    $text .= $m . " ";
                }
            }
        }

        // 3. Intento de manejo de compresión (FlateDecode)
        // Buscamos streams comprimidos
        if (preg_match_all('/stream[\r\n](.*?)endstream/s', $content, $streams)) {
            foreach ($streams[1] as $stream) {
                // Intentamos descomprimir
                $uncompressed = @gzuncompress($stream);
                if ($uncompressed) {
                    // Si descomprimió, buscamos texto ahí también
                    if (preg_match_all('/\((.*?)\)/', $uncompressed, $matches)) {
                        foreach ($matches[1] as $m) {
                            $text .= $m . " ";
                        }
                    }
                }
            }
        }

        // Limpieza final
        // Eliminar caracteres de control y basura binaria
        $text = preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.,;:?!-]/', '', $text);
        
        // Reducir espacios múltiples
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Extrae texto plano de un archivo DOCX (Word) usando PHP nativo (ZipArchive + XML).
     * Si ZipArchive no está disponible, intenta usar el comando 'tar' del sistema.
     */
    public static function extraerTextoDocx($filename) {
        if (!file_exists($filename)) return "";
        
        $xml_content = "";

        // MÉTODO A: Intentar usar Clase Nativa ZipArchive (Si está activada en php.ini)
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            $res = $zip->open($filename);
            if ($res === true) {
                if (($index = $zip->locateName('word/document.xml')) !== false) {
                    $xml_content = $zip->getFromIndex($index);
                }
                $zip->close();
            }
        } 
        
        // MÉTODO B: Fallback usando PowerShell (Nativo en Windows)
        // Esto es más confiable que 'tar' en entornos Windows Server / XAMPP
        if (empty($xml_content) && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
             $cmd_filename = escapeshellarg($filename);
             // Script de PowerShell para extraer el XML del ZIP en memoria y imprimirlo
             $psCommand = "powershell -Command \"& { Add-Type -AssemblyName System.IO.Compression.FileSystem; try { \$zip = [System.IO.Compression.ZipFile]::OpenRead($cmd_filename); \$entry = \$zip.GetEntry('word/document.xml'); if (\$entry) { \$reader = New-Object System.IO.StreamReader(\$entry.Open()); \$reader.ReadToEnd(); } } catch { Write-Host 'ErrorPS'; } }\"";
             
             $output = shell_exec($psCommand);
             
             if ($output && strpos($output, '<') !== false) {
                 $xml_content = $output;
             }
        }

        // MÉTODO C: Fallback legado usando 'tar' (si existe, para Linux/Mac o Git Bash en Windows)
        if (empty($xml_content)) {
            // Escapamos comillas para seguridad
            $cmd_filename = escapeshellarg($filename);
            // Ejecutamos tar para extraer a STDOUT (-O) el archivo específico
            // 2>&1 para capturar errores si los hay
            $output = shell_exec("tar -xf $cmd_filename -O word/document.xml 2>&1");
             
            // Verificamos si parece XML válido (comienza con <)
            if ($output && strpos(trim($output), '<') === 0) {
                $xml_content = $output;
            }
        }

        // Si fallaron ambos métodos, retornar vacío
        if (empty($xml_content)) {
            return "";
        }

        // Procesar XML extraído
        $content = "";
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        // Load XML string
        if ($dom->loadXML($xml_content, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            // Extraer texto de nodos <w:t>
            $nodes = $dom->getElementsByTagName('t'); 
            foreach ($nodes as $node) {
                $content .= $node->nodeValue . " ";
            }
        }
        
        return trim($content);
    }
}
?>
