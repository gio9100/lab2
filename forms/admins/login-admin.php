<?php
// Iniciar sesión
session_start();

// Incluir configuración
require_once "config-admin.php";

// Verificar si ya está logueado
if (isset($_SESSION['admin_id'])) {
    header('Location: index-admin.php');
    exit();
}

// Variables para mensajes
$mensaje = "";
$exito = false;

// Procesar formulario
if($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Obtener y limpiar datos
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    
    // Validaciones
    if ($email === "" || $password === "") {
        $mensaje = "Ingresa tu email y contraseña";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $mensaje = "Email inválido";
    } 
    else {
        // Intentar login
        $admin = loginAdmin($email, $password, $conn);
        
        if ($admin) {
            // Traer funciones de 2FA
            require_once '../2fa_functions.php';
            
            // Verificar si tiene 2FA activado
            $stmt_2fa = $conn->prepare("SELECT two_factor_enabled FROM admins WHERE id = ?");
            $stmt_2fa->bind_param("i", $admin["id"]);
            $stmt_2fa->execute();
            $result_2fa = $stmt_2fa->get_result();
            $tiene2FA = false;
            
            if ($result_2fa && $result_2fa->num_rows > 0) {
                $row_2fa = $result_2fa->fetch_assoc();
                $tiene2FA = ($row_2fa['two_factor_enabled'] == 1);
            }
            
            if ($tiene2FA) {
                // Enviar código 2FA
                $codigo = generarCodigo2FA();
                guardarCodigo2FA($conn, 'admin', $admin['id'], $codigo);
                enviarCodigo2FA($admin['email'], $admin['nombre'], $codigo);
                
                // Guardar para verificación
                $_SESSION['pending_2fa'] = [
                    'type' => 'admin',
                    'id' => $admin['id'],
                    'email' => $admin['email'],
                    'nombre' => $admin['nombre']
                ];
                
                // Redirigir a verificación
                header('Location: ../verify_2fa.php');
                exit();
                
            } else {
                // Login normal sin 2FA
                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_nombre"] = $admin["nombre"];
                $_SESSION["admin_email"] = $admin["email"];
                $_SESSION["admin_nivel"] = $admin["nivel"];
                
                $mensaje = "🧪 Bienvenido al Panel de Administración, " . $admin["nombre"] . "!";
                $exito = true;
                
                // Redirección automática
                echo "
                <script>
                    setTimeout(function() {
                        window.location.href = 'index-admin.php';
                    }, 2000);
                </script>
                ";
            }
        } else {
            $mensaje = "⚠️ Email o contraseña incorrectos";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión Administrador - Lab-Explora</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="../../assets/css/inicio-sesion.css" rel="stylesheet">
</head>
<body>
    <!-- Formulario de Login -->
    <form method="post" class="formulario" novalidate>
        
        <div class="logo-lab">
            <img src="../../assets/img/logo/logo-labexplora.png" alt="Logo Lab">
            <h1>Inicio de Sesión Administrador</h1>
            <p class="subtitulo">Panel de Administración Lab-Explora</p>
        </div>
        
        <section class="seccion-informacion">
            
            <label>Email Administrador</label>
            <input type="email" 
                   name="email" 
                   placeholder="admin@labexplorer.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required>
            
            <label>Contraseña</label>
            <input type="password" 
                   name="password" 
                   placeholder="Tu contraseña de administrador"
                   required 
                   minlength="6">
            
        </section>
        
        <section class="seccion-botones">
            <button type="submit">Iniciar Sesión como Administrador</button>
            <p>¿No tienes cuenta de administrador? <a href="register-admin.php">Solicitar acceso</a></p>
            <p><a href="../../pagina-principal.php">← Volver al sitio principal</a></p>
        </section>
    </form>
    
    <!-- Modal de Mensajes -->
    <?php if($mensaje): ?>
        <div class="modal-mensaje <?= $exito ? 'exito' : 'error' ?>">
            <div class="modal-contenido">
                <h2><?= $exito ? "🧪 Acceso Concedido" : "⚠️ Acceso Denegado" ?></h2>
                <p><?= htmlspecialchars($mensaje) ?></p>
                
                <?php if($exito): ?>
                    <p style="font-style: italic; margin-top: 15px;">
                        Redirigiendo al panel de administración...
                    </p>
                <?php else: ?>
                    <button onclick="cerrarmodal()">Cerrar</button>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
            function cerrarmodal() {
                document.querySelector('.modal-mensaje').style.display='none';
            }
        </script>
    <?php endif; ?>
    
    <script src="../../assets/js/accessibility-widget.js?v=3.2"></script>
</body>
</html>