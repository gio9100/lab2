<?php
// Archivo de funciones auxiliares para 2FA
// Funciones reutilizables para verificación en 2 pasos

// Generar código aleatorio de 6 dígitos
function generarCodigo2FA() {
    // rand() genera número entre 100000 y 999999
    return rand(100000, 999999);
}

// Guardar código en la base de datos
function guardarCodigo2FA($conn, $userType, $userId, $codigo) {
    // Calcular cuándo expira (10 minutos desde ahora)
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Obtener IP del usuario
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Encriptar el código usando password_hash para seguridad
    $codigoEncriptado = password_hash($codigo, PASSWORD_BCRYPT);
    
    // Primero invalidar códigos anteriores del mismo usuario
    $stmt = $conn->prepare("UPDATE two_factor_codes SET used = 1 
                           WHERE user_type = ? AND user_id = ? AND used = 0");
    $stmt->bind_param("si", $userType, $userId);
    $stmt->execute();
    
    // Insertar nuevo código encriptado
    $stmt = $conn->prepare("INSERT INTO two_factor_codes 
                           (user_type, user_id, code, expires_at, ip_address) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $userType, $userId, $codigoEncriptado, $expires, $ip);
    
    return $stmt->execute();
}

// Enviar código por email
function enviarCodigo2FA($email, $nombre, $codigo) {
    // Usar PHPMailer que ya está configurado
    require_once __DIR__ . '/EmailHelper.php';
    
    // Crear asunto del email
    $asunto = "Código de verificación - Lab-Explora";
    
    // Crear cuerpo del email con HTML bonito
    $cuerpo = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: #7390A0; padding: 30px; text-align: center; color: white;'>
            <h1 style='margin: 0;'>🔐 Verificación en 2 Pasos</h1>
        </div>
        <div style='padding: 30px; background: #f9f9f9;'>
            <p>Hola <strong>$nombre</strong>,</p>
            <p>Alguien (esperamos que tú) intentó iniciar sesión en Lab-Explora.</p>
            <p>Tu código de verificación es:</p>
            <div style='background: white; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #7390A0; font-size: 36px; margin: 0; letter-spacing: 5px;'>$codigo</h2>
            </div>
            <p><strong>Este código expira en 10 minutos.</strong></p>
            <p style='color: #666; font-size: 14px;'>
                Si no fuiste tú, ignora este email y tu cuenta permanecerá segura.
            </p>
        </div>
        <div style='text-align: center; padding: 20px; color: #999; font-size: 12px;'>
            Lab-Explora - Plataforma Educativa
        </div>
    </div>
    ";
    
    // Enviar el email usando la clase EmailHelper
    return EmailHelper::enviarCorreo($email, $asunto, $cuerpo);
}


// Validar código ingresado
function validarCodigo2FA($conn, $userType, $userId, $codigoIngresado) {
    // Buscar códigos válidos (no usados y no expirados) del usuario
    $stmt = $conn->prepare("SELECT id, code FROM two_factor_codes 
                           WHERE user_type = ? AND user_id = ? 
                           AND used = 0 AND expires_at > NOW()");
    $stmt->bind_param("si", $userType, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Verificar cada código (compatible con texto plano y encriptados)
    while ($row = $result->fetch_assoc()) {
        $codigoAlmacenado = $row['code'];
        $codeId = $row['id'];
        
        // Verificar si el código coincide
        // Opción 1: Código encriptado (nuevos códigos con bcrypt)
        $esValido = password_verify($codigoIngresado, $codigoAlmacenado);
        
        // Opción 2: Código en texto plano (códigos antiguos)
        // Si password_verify falla, intentar comparación directa
        if (!$esValido && $codigoIngresado === $codigoAlmacenado) {
            $esValido = true;
        }
        
        if ($esValido) {
            // Marcar el código como usado
            $stmt2 = $conn->prepare("UPDATE two_factor_codes SET used = 1 WHERE id = ?");
            $stmt2->bind_param("i", $codeId);
            $stmt2->execute();
            
            return true; // Código válido
        }
    }
    
    return false; // Código inválido o expirado
}

// Ocultar parte del email (privacidad)
function ocultarEmail($email) {
    // Separar nombre de usuario y dominio
    $partes = explode('@', $email);
    
    if (count($partes) != 2) {
        return $email; // Si no es email válido, devolver tal cual
    }
    
    $nombre = $partes[0];
    $dominio = $partes[1];
    
    // Ocultar caracteres del medio con asteriscos
    $largo = strlen($nombre);
    
    if ($largo <= 2) {
        // Si es muy corto, mostrar solo primer caracter
        $nombreOculto = $nombre[0] . '***';
    } else {
        // Mostrar primer y último caracter, resto con asteriscos
        $nombreOculto = $nombre[0] . str_repeat('*', $largo - 2) . $nombre[$largo - 1];
    }
    
    return $nombreOculto . '@' . $dominio;
}

// Verificar si usuario está bloqueado
function estaBloqueado($conn, $userType, $userId) {
    // Determinar tabla según tipo de usuario
    $tabla = '';
    if ($userType == 'usuario') $tabla = 'usuarios';
    elseif ($userType == 'publicador') $tabla = 'publicadores';
    elseif ($userType == 'admin') $tabla = 'admins';
    
    // Consultar si está bloqueado
    $stmt = $conn->prepare("SELECT blocked_until FROM $tabla WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $blockedUntil = $row['blocked_until'];
        
        // Si tiene fecha de bloqueo y aún no pasó
        if ($blockedUntil && strtotime($blockedUntil) > time()) {
            return true; // Está bloqueado
        }
    }
    
    return false; // No está bloqueado
}

// Bloquear usuario por intentos fallidos
function bloquearUsuario($conn, $userType, $userId, $minutos = 15) {
    // Calcular hasta cuándo bloquear
    $blockedUntil = date('Y-m-d H:i:s', strtotime("+$minutos minutes"));
    
    // Determinar tabla
    $tabla = '';
    if ($userType == 'usuario') $tabla = 'usuarios';
    elseif ($userType == 'publicador') $tabla = 'publicadores';
    elseif ($userType == 'admin') $tabla = 'admins';
    
    // Actualizar fecha de bloqueo
    $stmt = $conn->prepare("UPDATE $tabla SET blocked_until = ? WHERE id = ?");
    $stmt->bind_param("si", $blockedUntil, $userId);
    
    return $stmt->execute();
}
?>
