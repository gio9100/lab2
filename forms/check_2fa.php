<?php
// Procesar el código que ingresó el usuario
session_start();
require_once 'conexion.php'; // Conexión a BD
require_once '2fa_functions.php'; // Funciones 2FA

// Si no hay datos pendientes, mandar al login
if (!isset($_SESSION['pending_2fa'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos de sesión
$userType = $_SESSION['pending_2fa']['type'];
$userId = $_SESSION['pending_2fa']['id'];

// Obtener código que ingresó
$codigoIngresado = $_POST['code'] ?? '';

// Validar que tenga 6 dígitos
if (strlen($codigoIngresado) != 6 || !ctype_digit($codigoIngresado)) {
    $_SESSION['error_2fa'] = "El código debe tener exactamente 6 dígitos";
    header('Location: verify_2fa.php');
    exit();
}

// Verificar si está bloqueado
if (estaBloqueado($conexion, $userType, $userId)) {
    $_SESSION['error_2fa'] = "Demasiados intentos fallidos. Intenta más tarde.";
    header('Location: verify_2fa.php');
    exit();
}

// Validar el código con la base de datos
if (validarCodigo2FA($conexion, $userType, $userId, $codigoIngresado)) {
    // Código correcto! Iniciar sesión completa
    
    // Obtener datos completos del usuario desde la sesión pendiente
    $userData = $_SESSION['pending_2fa'];
    
    // Limpiar datos temporales de 2FA
    unset($_SESSION['pending_2fa']);
    unset($_SESSION['intentos_2fa']);
    
    // IMPORTANTE: Limpiar TODAS las sesiones anteriores de otros tipos de usuario
    // para evitar conflictos (ej: si antes era admin y ahora es usuario)
    unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['usuario_correo'], $_SESSION['es_admin']);
    unset($_SESSION['publicador_id'], $_SESSION['publicador_nombre'], $_SESSION['publicador_email'], $_SESSION['publicador_especialidad']);
    unset($_SESSION['admin_id'], $_SESSION['admin_nombre'], $_SESSION['admin_email'], $_SESSION['admin_nivel']);
    
    // Establecer sesión según tipo de usuario
    if ($userType == 'usuario') {
        // Sesión de usuario - obtener datos completos de la BD (igual que publicador/admin)
        $stmt = $conexion->prepare("SELECT nombre, correo FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
        
        $_SESSION['usuario_id'] = $userId;
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_correo'] = $usuario['correo'];
        
        // Verificar si es admin (igual que en login normal)
        $stmt_admin = $conexion->prepare("SELECT id FROM admins WHERE email = ? AND estado = 'activo'");
        $stmt_admin->bind_param("s", $usuario['correo']);
        $stmt_admin->execute();
        $resultado_admin = $stmt_admin->get_result();
        $_SESSION["es_admin"] = ($resultado_admin && $resultado_admin->num_rows > 0);
        $stmt_admin->close();
        
    } elseif ($userType == 'publicador') {
        // Sesión de publicador - obtener datos completos de la BD
        $stmt = $conexion->prepare("SELECT nombre, email, especialidad FROM publicadores WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $publicador = $result->fetch_assoc();
        $stmt->close();
        
        $_SESSION['publicador_id'] = $userId;
        $_SESSION['publicador_nombre'] = $publicador['nombre'];
        $_SESSION['publicador_email'] = $publicador['email'];
        $_SESSION['publicador_especialidad'] = $publicador['especialidad'];
        
    } elseif ($userType == 'admin') {
        // Sesión de admin - obtener datos completos de la BD
        $stmt = $conexion->prepare("SELECT nombre, email, nivel FROM admins WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();
        
        $_SESSION['admin_id'] = $userId;
        $_SESSION['admin_nombre'] = $admin['nombre'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_nivel'] = $admin['nivel'];
    }
    
    // Redirigir a dashboard específico según tipo de usuario
    if ($userType == 'usuario') {
        header("Location: ../pagina-principal.php");
    } elseif ($userType == 'publicador') {
        header("Location: publicadores/index-publicadores.php");
    } elseif ($userType == 'admin') {
        header("Location: admins/index-admin.php");
    }
    exit();
    
} else {
    // Código incorrecto
    
    // Contar intentos fallidos
    if (!isset($_SESSION['intentos_2fa'])) {
        $_SESSION['intentos_2fa'] = 0;
    }
    $_SESSION['intentos_2fa']++;
    
    // Si ya lleva 3 intentos, bloquear
    if ($_SESSION['intentos_2fa'] >= 3) {
        bloquearUsuario($conexion, $userType, $userId, 15); // 15 minutos
        $_SESSION['error_2fa'] = "Has excedido el número de intentos. Cuenta bloqueada por 15 minutos.";
        
        // Limpiar sesión temporal
        unset($_SESSION['pending_2fa']);
        header('Location: inicio-sesion.php');
        exit();
    }
    
    // Mostrar error
    $intentosRestantes = 3 - $_SESSION['intentos_2fa'];
    $_SESSION['error_2fa'] = "Código incorrecto. Te quedan $intentosRestantes intento(s).";
    header('Location: verify_2fa.php');
    exit();
}
?>
