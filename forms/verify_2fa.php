<?php
// Página de verificación para ingresar el código 2FA
session_start();
require_once '2fa_functions.php';

// Si no hay sesión pendiente de 2FA, volver al login
if (!isset($_SESSION['pending_2fa'])) {
    header('Location: inicio-sesion.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación en 2 Pasos - Lab-Explora</title>
    
    <!-- CSS del inicio de sesión -->
    <link href="../assets/css/inicio-sesion.css" rel="stylesheet">
    
    <style>
        /* Estilos adicionales para el input del código */
        .code-input {
            font-size: 1.8rem;
            letter-spacing: 8px;
            text-align: center;
            font-weight: bold;
            padding: 15px;
            margin: 20px 0;
        }
        
        /* Remover flechas de input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
        
        /* Email oculto */
        .email-hidden {
            font-family: monospace;
            color: #666;
            font-size: 0.95rem;
        }
        
        /* Enlaces adicionales */
        .extra-links {
            margin-top: 15px;
            font-size: 0.9rem;
        }
        
        .extra-links a {
            color: #0d6efd;
            text-decoration: none;
        }
        
        .extra-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Formulario de Verificación 2FA -->
    <form method="post" action="check_2fa.php" class="formulario" novalidate>
        
        <div class="logo-lab">
            <img src="../assets/img/logo/logo-labexplora.png" alt="Logo Lab">
            <h1>Verificación en 2 Pasos</h1>
            <p class="subtitulo">Ingresa el código enviado a tu correo</p>
        </div>
        
        <section class="seccion-informacion">
            
            <?php if (isset($_SESSION['error_2fa'])): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px; background: rgba(220, 53, 69, 0.1); border-left: 3px solid #dc3545; border-radius: 5px;">
                    <?= htmlspecialchars($_SESSION['error_2fa']) ?>
                    <?php unset($_SESSION['error_2fa']); ?>
                </div>
            <?php endif; ?>
            
            <p style="text-align: center; margin-bottom: 10px;">
                Hemos enviado un código de 6 dígitos a:
            </p>
            <p class="email-hidden" style="text-align: center; margin-bottom: 25px;">
                <?= ocultarEmail($_SESSION['pending_2fa']['email']) ?>
            </p>
            
            <label>Código de Verificación</label>
            <input type="number" 
                   name="code" 
                   class="code-input"
                   placeholder="000000"
                   maxlength="6"
                   required 
                   autofocus
                   id="codeInput">
            
            <p style="text-align: center; color: #666; font-size: 0.85rem; margin-top: 10px;">
                ⏱️ El código expira en 10 minutos
            </p>
            
        </section>
        
        <section class="seccion-botones">
            <button type="submit">✓ Verificar Código</button>
            
            <div class="extra-links">
                <p>
                    <a href="resend_2fa.php">¿No recibiste el código? Reenviar</a>
                </p>
                <p>
                    <a href="cancel_2fa.php">← Cancelar e ir al login</a>
                </p>
            </div>
        </section>
    </form>
    
    <script>
        // Auto-submit cuando complete 6 dígitos
        const input = document.getElementById('codeInput');
        
        input.addEventListener('input', function() {
            // Limitar a 6 dígitos
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
            
            // Auto-submit cuando tenga 6 dígitos
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
        
        // Solo permitir números
        input.addEventListener('keypress', function(e) {
            if (e.key < '0' || e.key > '9') {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
