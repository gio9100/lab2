<?php
function fixFile($filePath, $removeLine, $insertAfterLine, $insertContent) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return;
    }
    
    $lines = file($filePath);
    $newLines = [];
    
    foreach ($lines as $index => $line) {
        $lineNumber = $index + 1;
        
        if ($lineNumber == $removeLine) {
            // Skip this line (remove it)
            continue;
        }
        
        $newLines[] = $line;
        
        if ($lineNumber == $insertAfterLine) {
            $newLines[] = $insertContent . "\n";
        }
    }
    
    file_put_contents($filePath, implode("", $newLines));
    echo "Fixed $filePath\n";
}

// Fix registro-publicadores.php
// Remove line 46: "    else {"
// Insert "    else {" after line 53
fixFile('c:/xampp/htdocs/lab2/forms/publicadores/registro-publicadores.php', 46, 53, '    else {');

// Fix register-admin.php
// Remove line 63: "    else {"
// Insert "    else {" after line 70
fixFile('c:/xampp/htdocs/lab2/forms/admins/register-admin.php', 63, 70, '    else {');

?>
