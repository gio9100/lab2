<?php
// forms/funciones_moderacion.php

function moderarContenido($texto, $conn) {
    if (empty($texto)) return ['texto' => $texto, 'accion' => 'aprobar'];

    // Obtener lista negra
    $sql = "SELECT palabra, tipo_coincidencia, accion FROM lista_negra";
    $result = $conn->query($sql);
    
    $accion_final = 'aprobar';
    $texto_procesado = $texto;

    while ($row = $result->fetch_assoc()) {
        $palabra = $row['palabra'];
        $tipo = $row['tipo_coincidencia'];
        $accion = $row['accion'];
        
        $encontrado = false;

        // Detección
        if ($tipo === 'exacta') {
            // Busca palabra exacta rodeada de límites de palabra
            if (preg_match('/\b' . preg_quote($palabra, '/') . '\b/i', $texto_procesado)) {
                $encontrado = true;
            }
        } else {
            // Parcial (contiene la cadena)
            if (stripos($texto_procesado, $palabra) !== false) {
                $encontrado = true;
            }
        }

        if ($encontrado) {
            // Determinar acción más severa
            if ($accion === 'rechazar') {
                return ['texto' => $texto, 'accion' => 'rechazar', 'motivo' => "Uso de palabra prohibida: $palabra"];
            }
            if ($accion === 'revision' && $accion_final !== 'rechazar') {
                $accion_final = 'revision';
            }

            // Aplicar censura si es la acción requerida
            if ($accion === 'asteriscos') {
                $reemplazo = str_repeat('*', strlen($palabra));
                if ($tipo === 'exacta') {
                    $texto_procesado = preg_replace('/\b' . preg_quote($palabra, '/') . '\b/i', $reemplazo, $texto_procesado);
                } else {
                    $texto_procesado = str_ireplace($palabra, $reemplazo, $texto_procesado);
                }
            }
        }
    }

    return ['texto' => $texto_procesado, 'accion' => $accion_final];
}
?>
