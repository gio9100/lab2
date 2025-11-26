<?php
// ============================================================================
// 📋 ARCHIVO: validaciones.php
// ============================================================================
// PROPÓSITO: Funciones de validación reutilizables para todo el sistema
//
// FUNCIONES INCLUIDAS:
// - validarNombre(): Valida que el nombre solo contenga letras y espacios
// - validarEmail(): Valida formato de email y dominio
// - validarTelefono(): Valida formato de teléfono
// - validarPassword(): Valida requisitos de contraseña
//
// USO:
// require_once 'validaciones.php';
// $resultado = validarNombre($nombre);
// if (!$resultado['valido']) {
//     echo $resultado['mensaje'];
// }
// ============================================================================

/**
 * ============================================================================
 * FUNCIÓN: validarNombre
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Valida que un nombre solo contenga letras, espacios, tildes y la letra ñ
 * NO permite números ni caracteres especiales
 * 
 * ¿CUÁNDO SE USA?
 * Al registrar o editar usuarios, publicadores o administradores
 * 
 * PARÁMETROS:
 * @param string $nombre - El nombre a validar
 * @param int $min_length - Longitud mínima (por defecto 3)
 * @param int $max_length - Longitud máxima (por defecto 100)
 * 
 * RETORNA:
 * Array con dos elementos:
 * - 'valido' (bool): true si es válido, false si no
 * - 'mensaje' (string): mensaje de error si no es válido
 * 
 * EJEMPLOS:
 * validarNombre("Juan Pérez")        → ['valido' => true, 'mensaje' => '']
 * validarNombre("María José Núñez")  → ['valido' => true, 'mensaje' => '']
 * validarNombre("Juan123")           → ['valido' => false, 'mensaje' => 'El nombre no puede contener números']
 * validarNombre("Juan@Pérez")        → ['valido' => false, 'mensaje' => 'El nombre solo puede contener letras...']
 */
function validarNombre($nombre, $min_length = 3, $max_length = 100) {
    // Quitamos espacios en blanco al inicio y final
    $nombre = trim($nombre);
    
    // ====================================================================
    // VALIDACIÓN 1: Verificar que no esté vacío
    // ====================================================================
    if (empty($nombre)) {
        // Si está vacío, retornamos error
        return [
            'valido' => false,
            'mensaje' => '❌ El nombre no puede estar vacío'
        ];
    }
    
    // ====================================================================
    // VALIDACIÓN 2: Verificar longitud mínima y máxima
    // ====================================================================
    $longitud = mb_strlen($nombre);
    // mb_strlen cuenta correctamente caracteres con tildes y ñ
    
    if ($longitud < $min_length) {
        // Si es muy corto
        return [
            'valido' => false,
            'mensaje' => "❌ El nombre debe tener al menos $min_length caracteres"
        ];
    }
    
    if ($longitud > $max_length) {
        // Si es muy largo
        return [
            'valido' => false,
            'mensaje' => "❌ El nombre no puede tener más de $max_length caracteres"
        ];
    }
    
    // ====================================================================
    // VALIDACIÓN 3: Verificar que NO contenga números
    // ====================================================================
    if (preg_match('/[0-9]/', $nombre)) {
        // preg_match busca si hay algún dígito del 0 al 9
        return [
            'valido' => false,
            'mensaje' => '❌ El nombre no puede contener números'
        ];
    }
    
    // ====================================================================
    // VALIDACIÓN 4: Verificar que solo contenga letras, espacios y tildes
    // ====================================================================
    // Esta expresión regular permite:
    // - Letras mayúsculas y minúsculas (A-Z, a-z)
    // - Letras con tildes (á, é, í, ó, ú, Á, É, Í, Ó, Ú)
    // - La letra ñ y Ñ
    // - Espacios en blanco
    // - Apóstrofes (') para nombres como O'Connor
    // - Guiones (-) para nombres compuestos como María-José
    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s'\-]+$/u", $nombre)) {
        // Si contiene otros caracteres
        return [
            'valido' => false,
            'mensaje' => '❌ El nombre solo puede contener letras, espacios, tildes y guiones'
        ];
    }
    
    // ====================================================================
    // VALIDACIÓN 5: Verificar que no tenga espacios múltiples
    // ====================================================================
    if (preg_match('/\s{2,}/', $nombre)) {
        // Si tiene dos o más espacios seguidos
        return [
            'valido' => false,
            'mensaje' => '❌ El nombre no puede tener espacios múltiples'
        ];
    }
    
    // ====================================================================
    // VALIDACIÓN 6: Verificar que no empiece o termine con espacio
    // ====================================================================
    if ($nombre !== trim($nombre)) {
        // Si tiene espacios al inicio o final (aunque ya lo limpiamos arriba)
        return [
            'valido' => false,
            'mensaje' => '❌ El nombre no puede empezar o terminar con espacios'
        ];
    }
    
    // ====================================================================
    // SI PASÓ TODAS LAS VALIDACIONES, ES VÁLIDO
    // ====================================================================
    return [
        'valido' => true,
        'mensaje' => ''
    ];
}

/**
 * ============================================================================
 * FUNCIÓN: validarEmailCompleto
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Valida el formato del email Y verifica que el dominio pueda recibir correos
 * 
 * PARÁMETROS:
 * @param string $email - El email a validar
 * 
 * RETORNA:
 * Array con 'valido' y 'mensaje'
 */
function validarEmailCompleto($email) {
    // Quitamos espacios
    $email = trim($email);
    
    // Verificamos formato básico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'valido' => false,
            'mensaje' => '❌ El formato del email no es válido'
        ];
    }
    
    // Extraemos el dominio
    $dominio = substr(strrchr($email, "@"), 1);
    
    // Verificamos registros MX
    if (!checkdnsrr($dominio, 'MX')) {
        return [
            'valido' => false,
            'mensaje' => "❌ El dominio del email ($dominio) no es válido o no puede recibir correos"
        ];
    }
    
    return [
        'valido' => true,
        'mensaje' => ''
    ];
}

/**
 * ============================================================================
 * FUNCIÓN: validarTelefono
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Valida que un teléfono solo contenga números, espacios, guiones y paréntesis
 * 
 * PARÁMETROS:
 * @param string $telefono - El teléfono a validar
 * @param bool $requerido - Si es obligatorio (por defecto false)
 * 
 * RETORNA:
 * Array con 'valido' y 'mensaje'
 */
function validarTelefono($telefono, $requerido = false) {
    // Quitamos espacios
    $telefono = trim($telefono);
    
    // Si no es requerido y está vacío, es válido
    if (!$requerido && empty($telefono)) {
        return [
            'valido' => true,
            'mensaje' => ''
        ];
    }
    
    // Si es requerido y está vacío, error
    if ($requerido && empty($telefono)) {
        return [
            'valido' => false,
            'mensaje' => '❌ El teléfono es obligatorio'
        ];
    }
    
    // Verificamos que solo contenga números, espacios, guiones, paréntesis y el símbolo +
    if (!preg_match('/^[\d\s\-\(\)\+]+$/', $telefono)) {
        return [
            'valido' => false,
            'mensaje' => '❌ El teléfono solo puede contener números, espacios, guiones y paréntesis'
        ];
    }
    
    // Contamos solo los dígitos
    $solo_digitos = preg_replace('/\D/', '', $telefono);
    // \D = cualquier cosa que NO sea dígito
    
    // Verificamos longitud (mínimo 10 dígitos para México)
    if (strlen($solo_digitos) < 10) {
        return [
            'valido' => false,
            'mensaje' => '❌ El teléfono debe tener al menos 10 dígitos'
        ];
    }
    
    return [
        'valido' => true,
        'mensaje' => ''
    ];
}

/**
 * ============================================================================
 * FUNCIÓN: validarPassword
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Valida que una contraseña cumpla con requisitos de seguridad
 * 
 * PARÁMETROS:
 * @param string $password - La contraseña a validar
 * @param int $min_length - Longitud mínima (por defecto 6)
 * 
 * RETORNA:
 * Array con 'valido' y 'mensaje'
 */
function validarPassword($password, $min_length = 6) {
    // Verificamos longitud mínima
    if (strlen($password) < $min_length) {
        return [
            'valido' => false,
            'mensaje' => "❌ La contraseña debe tener al menos $min_length caracteres"
        ];
    }
    
    // Verificamos que no sea solo espacios
    if (trim($password) === '') {
        return [
            'valido' => false,
            'mensaje' => '❌ La contraseña no puede estar vacía'
        ];
    }
    
    return [
        'valido' => true,
        'mensaje' => ''
    ];
}

/**
 * ============================================================================
 * FUNCIÓN: limpiarNombre
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Limpia un nombre eliminando espacios múltiples y capitalizando correctamente
 * 
 * PARÁMETROS:
 * @param string $nombre - El nombre a limpiar
 * 
 * RETORNA:
 * String con el nombre limpio
 * 
 * EJEMPLO:
 * limpiarNombre("  juan   pérez  ") → "Juan Pérez"
 */
function limpiarNombre($nombre) {
    // Quitamos espacios al inicio y final
    $nombre = trim($nombre);
    
    // Reemplazamos múltiples espacios por uno solo
    $nombre = preg_replace('/\s+/', ' ', $nombre);
    
    // Convertimos a minúsculas primero
    $nombre = mb_strtolower($nombre, 'UTF-8');
    
    // Capitalizamos cada palabra
    $nombre = mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');
    
    return $nombre;
}
?>
