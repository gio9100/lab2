<?php
// Función para validar correos permitidos (Institucionales o Whitelist)
// Esta función verifica si un correo puede registrarse basándose en dominios permitidos o lista blanca de DB

function esCorreoPermitido($correo, $tipo_intento, $conn) {
    // $correo: El email que el usuario intenta registrar
    // $tipo_intento: 'usuario' o 'publicador' (qué intenta ser)
    // $conn: La conexión activa a la base de datos

    // 1. Lista de dominios públicos permitidos por defecto
    // Estos son los que siempre aceptamos sin consultar la base de datos
    $dominios_default = [
        'gmail.com',    // Google
        'outlook.com',  // Microsoft
        'outlook.es'    // Microsoft Regional
    ];

    // Limpiamos y preparamos el correo
    $correo = trim($correo);
    // trim() elimina espacios accidentales al inicio o final
    $correo = mb_strtolower($correo, 'UTF-8');
    // mb_strtolower() convierte todo a minúsculas para comparar mejor

    // Separamos el usuario del dominio
    $partes = explode('@', $correo);
    // explode() rompe el string en el arroba
    $dominio_usuario = $partes[1] ?? '';
    // Obtenemos solo la parte después del @ (el dominio)

    // 2. Verificamos si es un dominio default
    // Si el dominio está en nuestra lista básica, es válido automáticamente
    if (in_array($dominio_usuario, $dominios_default)) {
        return true; 
        // Retornamos verdadero, el correo pasa
    }

    // 3. Si no es default, consultamos la base de datos
    // Modificamos la consulta para soportar dominios guardados como 'domain.com' O '@domain.com'
    
    $sql = "SELECT id FROM correos_permitidos 
            WHERE (valor = ? OR valor = ? OR valor = CONCAT('@', ?)) 
            AND (tipo_acceso = ? OR tipo_acceso = 'ambos' OR tipo_acceso IS NULL)";
            
    // valor = ?: Coincidencia exacta dominio (ej: empresa.com)
    // valor = ?: Coincidencia exacta correo (ej: ud@empresa.com)
    // valor = CONCAT('@', ?): Coincidencia dominio con arroba (ej: @empresa.com)

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // "ssss": 4 strings (dominio, correo, dominio, tipo)
        $stmt->bind_param("ssss", $dominio_usuario, $correo, $dominio_usuario, $tipo_intento);
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $stmt->close();
            return true; 
        }
        
        $stmt->close();
    }

    return false;
}

// Función auxiliar para obtener dominios permitidos (limpios) para JS
function obtenerDominiosExtra($conn) {
    $dominios = [];
    
    // Obtenemos TODO y filtramos en PHP para ser flexibles
    $sql = "SELECT valor FROM correos_permitidos";
    $resultado = $conn->query($sql);
    
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $val = trim($fila['valor']);
            
            // Si empieza con @ (ej: @escuela.edu), es un dominio -> lo limpiamos (escuela.edu)
            if (strpos($val, '@') === 0) {
                $dominios[] = substr($val, 1);
            }
            // Si NO tiene @ (ej: escuela.edu), es un dominio
            elseif (strpos($val, '@') === false) {
                $dominios[] = $val;
            }
            // Si tiene @ en medio (ej: juan@gmail.com), NO es dominio, lo ignoramos aqui
        }
    }
    
    return $dominios;
}

// Función auxiliar para obtener emails específicos (whitelist) para JS
function obtenerCorreosExtra($conn) {
    $correos = [];
    
    $sql = "SELECT valor FROM correos_permitidos";
    $resultado = $conn->query($sql);
    
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $val = trim(mb_strtolower($fila['valor']));
            
            // Solo si tiene @ Y NO está al principio (ej: juan@escuela.edu)
            if (strpos($val, '@') !== false && strpos($val, '@') > 0) {
                $correos[] = $val;
            }
        }
    }
    
    return $correos;
}
?>
