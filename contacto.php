<?php
// Iniciar sesión para mensajes antes de cualquier salida HTML
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Contacto - Lab-Explora</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Main CSS -->
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        :root {
            --primary: #7390A0;
            --primary-dark: #5a7080;
            --text: #212529;
            --text-light: #6c757d;
            --background: #f8f9fa;
            --white: #ffffff;
            --success: #28a745;
            --error: #dc3545;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
            padding-top: 40px;
            padding-bottom: 40px;
        }
        .container-contact {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        h1 {
            font-family: 'Nunito', sans-serif;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        .form-label {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(115, 144, 160, 0.15);
        }
        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(115, 144, 160, 0.3);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(115, 144, 160, 0.4);
        }
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .contact-info {
            background: linear-gradient(135deg, rgba(115, 144, 160, 0.05) 0%, rgba(115, 144, 160, 0.12) 100%);
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: center;
        }
        .contact-info h3 {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        .contact-info p {
            margin-bottom: 10px;
            color: var(--text);
        }
        .required {
            color: var(--error);
        }
    </style>
</head>
<body>
    <div class="container container-contact">
        <a href="javascript:history.back()" class="back-link"><i class="bi bi-arrow-left"></i> Volver</a>
        
        <div class="text-center mb-4">
            <img src="assets/img/logo/logo.png" alt="Lab-Explora Logo" style="height: 120px;">
        </div>
        
        <h1>Formulario de Contacto</h1>
        <p class="subtitle">Para preguntas, comentarios o inquietudes sobre nuestros Términos y Condiciones o Política de Privacidad</p>

        <?php
        if (isset($_SESSION['contact_success'])) {
            echo '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' . htmlspecialchars($_SESSION['contact_success']) . '</div>';
            unset($_SESSION['contact_success']);
        }
        if (isset($_SESSION['contact_error'])) {
            echo '<div class="alert alert-error"><i class="bi bi-exclamation-triangle me-2"></i>' . htmlspecialchars($_SESSION['contact_error']) . '</div>';
            unset($_SESSION['contact_error']);
        }
        ?>

        <form action="#" method="POST" id="contactForm" onsubmit="return mostrarMensajeDemo(event)">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre completo <span class="required">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Correo electrónico <span class="required">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="asunto" class="form-label">Asunto <span class="required">*</span></label>
                <select class="form-select" id="asunto" name="asunto" required>
                    <option value="" selected disabled>Seleccione un asunto</option>
                    <option value="terminos">Consulta sobre Términos y Condiciones</option>
                    <option value="privacidad">Consulta sobre Política de Privacidad</option>
                    <option value="arco">Ejercicio de Derechos ARCO (Acceso, Rectificación, Cancelación, Oposición)</option>
                    <option value="eliminacion">Solicitud de eliminación de cuenta</option>
                    <option value="legal">Asunto legal</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono (opcional)</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ejemplo: +52 33 1234 5678">
            </div>

            <div class="mb-3">
                <label for="mensaje" class="form-label">Mensaje <span class="required">*</span></label>
                <textarea class="form-control" id="mensaje" name="mensaje" rows="6" required placeholder="Describa detalladamente su consulta o solicitud..."></textarea>
                <small class="text-muted">Mínimo 20 caracteres</small>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="aceptaPrivacidad" name="acepta_privacidad" required>
                <label class="form-check-label" for="aceptaPrivacidad">
                    Acepto la <a href="privacidad.php" target="_blank">Política de Privacidad</a> y autorizo el tratamiento de mis datos personales <span class="required">*</span>
                </label>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-submit">
                    <i class="bi bi-send me-2"></i>Enviar Mensaje
                </button>
            </div>
        </form>

        <div class="contact-info">
            <h3><i class="bi bi-envelope me-2"></i>Contacto Directo</h3>
            <p><strong>Departamento Legal:</strong> legal@lab-explora.com</p>
            <p><strong>Privacidad y Protección de Datos:</strong> privacidad@lab-explora.com</p>
            <p class="mb-0"><small class="text-muted">Tiempo de respuesta estimado: 2-5 días hábiles</small></p>
        </div>
    </div>

    <script>
        // Función de demostración
        function mostrarMensajeDemo(event) {
            event.preventDefault();
            
            const mensaje = document.getElementById('mensaje').value;
            if (mensaje.length < 20) {
                alert('El mensaje debe tener al menos 20 caracteres.');
                return false;
            }
            
            // Mensaje de demostración
            alert('📧 FORMULARIO DE DEMOSTRACIÓN\n\nEste formulario es solo para fines demostrativos.\n\nPara contacto real, envía un email a:\nlegal@lab-explora.com');
            
            // Limpiar formulario
            document.getElementById('contactForm').reset();
            
            return false;
        }
    </script>
    <script src="assets/js/accessibility-widget.js?v=3.2"></script>
</body>
</html>
