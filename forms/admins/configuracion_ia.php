<?php
// Iniciar sesión y reporte de errores
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config-admin.php';
requerirAdmin();

// Procesar formulario
$mensaje = "";
$tipo_alerta = "";

// Obtener configuración actual
$query = "SELECT * FROM configuracion_sistema LIMIT 1";
$result = $conn->query($query);

if (!$result) {
    // Si la tabla no existe o hay error
    $mensaje = "Error de Base de Datos: " . $conn->error;
    $tipo_alerta = "danger";
    $config = [
        'gemini_api_key' => '', 'enable_cognitive_tools' => 0,
        'enable_quiz' => 0, 'enable_chat_qa' => 0,
        'enable_writing_assistant' => 0, 'enable_auto_moderation' => 0,
        'enable_complexity_slider' => 0
    ];
} else {
    $config = $result->fetch_assoc();
    if (!$config) {
        // Tabla existe pero está vacía
        $conn->query("INSERT INTO configuracion_sistema (gemini_api_key) VALUES ('')");
        // Recargar por defecto
        $config = [
            'gemini_api_key' => '', 'enable_cognitive_tools' => 0,
            'enable_quiz' => 0, 'enable_chat_qa' => 0,
            'enable_writing_assistant' => 0, 'enable_auto_moderation' => 0,
            'enable_complexity_slider' => 0
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_key = trim($_POST['api_key']);
    $enable_tools = isset($_POST['enable_tools']) ? 1 : 0;
    $enable_quiz = isset($_POST['enable_quiz']) ? 1 : 0;
    $enable_chat_qa = isset($_POST['enable_chat_qa']) ? 1 : 0;
    $enable_chat_qa = isset($_POST['enable_chat_qa']) ? 1 : 0;
    $enable_writing_assistant = isset($_POST['enable_writing_assistant']) ? 1 : 0;
    
    // Update genérico
    $update_sql = "UPDATE configuracion_sistema SET 
        gemini_api_key = ?, 
        enable_cognitive_tools = ?,
        enable_quiz = ?,
        enable_chat_qa = ?,
        enable_writing_assistant = ?
        WHERE id = 1"; // Asumimos id=1 o único registro
        
    // Si no hay id=1, intentamos sin WHERE (solo hay 1 fila)
    if (!$result || $result->num_rows == 0) $update_sql = "UPDATE configuracion_sistema SET gemini_api_key = ?, enable_cognitive_tools = ?, enable_quiz = ?, enable_chat_qa = ?, enable_writing_assistant = ?";

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("siiii", $api_key, $enable_tools, $enable_quiz, $enable_chat_qa, $enable_writing_assistant);
    
    if ($stmt->execute()) {
        $mensaje = "Configuración actualizada correctamente";
        $tipo_alerta = "success";
        // Actualizar variable local
        $config['gemini_api_key'] = $api_key;
        $config['enable_cognitive_tools'] = $enable_tools;
        $config['enable_quiz'] = $enable_quiz;
        $config['enable_chat_qa'] = $enable_chat_qa;
        $config['enable_writing_assistant'] = $enable_writing_assistant;


    } else {
        $mensaje = "Error al guardar: " . $conn->error;
        $tipo_alerta = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración IA - Panel Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css?v=2.0">
</head>
<body class="admin-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary sidebar-toggle me-3 d-md-none" id="sidebar-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                  <a href="../../pagina-principal.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/logo-labexplora.png" alt="logo-lab">
                    <h1 class="sitename">Lab-Explora</h1><span></span>
                </a>
                </div>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <a href="perfil-admin.php" class="saludo d-none d-md-inline text-decoration-none text-dark me-3">👨‍💼 Hola, Admin</a>
                        <a href="logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-3 mb-4 sidebar-wrapper" id="sidebarWrapper">
                    <?php include 'sidebar-admin.php'; ?>
                </div>

                <!-- Config Content -->
                <div class="col-md-9">
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
                            <?php echo $mensaje; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Configuración IA</h2>
                        <p>Gestiona las herramientas cognitivas del sistema</p>
                    </div>

                    <div class="row g-4 justify-content-center">
                        <div class="col-lg-10">
                            <div class="admin-card card-config p-4" data-aos="fade-up" data-aos-delay="100">
                                <div class="card-body">
                                    <h4 class="card-title mb-4 border-bottom pb-2">
                                        <i class="bi bi-robot text-primary me-2"></i> Motor Cognitivo (Gemini)
                                    </h4>
                                    <form method="POST">
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Google Gemini API Key</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                                <input type="password" name="api_key" class="form-control api-key-input" 
                                                       value="<?php echo htmlspecialchars($config['gemini_api_key']); ?>" 
                                                       placeholder="Pegue aquí su clave API (empezando con AIza...)" id="apiKeyInput">
                                                <button class="btn btn-outline-secondary" type="button" id="toggleKey">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text mt-2 text-muted">
                                                <i class="bi bi-info-circle"></i> Necesitas una clave válida de Google AI Studio. 
                                                <a href="https://aistudio.google.com/" target="_blank" class="text-decoration-underline">Obtener una aquí</a>.
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <div class="d-flex flex-column gap-3">
                                                
                                                <!-- Global Master Switch -->
                                                <div class="form-check form-switch p-3 bg-light rounded border">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-check-label fw-bold" for="enableTools">
                                                            <i class="bi bi-cpu me-2"></i>Herramientas Cognitivas (Global)
                                                        </label>
                                                        <input class="form-check-input fs-4 m-0" type="checkbox" role="switch" 
                                                               id="enableTools" name="enable_tools" 
                                                               <?php echo $config['enable_cognitive_tools'] ? 'checked' : ''; ?>>
                                                    </div>
                                                    <div class="small text-muted">
                                                        Activa o desactiva las funciones básicas (Simplificar, Resumir, Traducir) para el público general.
                                                    </div>
                                                </div>

                                                <!-- Nuevas Funciones Phase 2 -->
                                                <h5 class="mt-2 mb-0 fs-6 text-uppercase text-muted fw-bold">Funciones Extendidas</h5>

                                                <!-- Quiz -->
                                                <div class="form-check form-switch p-3 bg-white rounded border">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <label class="form-check-label fw-bold d-block" for="enableQuiz">
                                                                <i class="bi bi-mortarboard me-2 text-primary"></i>Generador de Quizzes
                                                            </label>
                                                            <div class="small text-muted">Botón "Ponme a prueba" en publicaciones.</div>
                                                        </div>
                                                        <input class="form-check-input fs-4" type="checkbox" role="switch" 
                                                               id="enableQuiz" name="enable_quiz" 
                                                               <?php echo $config['enable_quiz'] ? 'checked' : ''; ?>>
                                                    </div>
                                                </div>

                                                <!-- Chat Q&A -->
                                                <div class="form-check form-switch p-3 bg-white rounded border">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <label class="form-check-label fw-bold d-block" for="enableChat">
                                                                <i class="bi bi-chat-dots me-2 text-success"></i>Chat con el Artículo
                                                            </label>
                                                            <div class="small text-muted">Permite a los usuarios hacer preguntas sobre el contenido.</div>
                                                        </div>
                                                        <input class="form-check-input fs-4" type="checkbox" role="switch" 
                                                               id="enableChat" name="enable_chat_qa" 
                                                               <?php echo $config['enable_chat_qa'] ? 'checked' : ''; ?>>
                                                    </div>
                                                </div>

                                                <!-- Writing Assistant -->
                                                <div class="form-check form-switch p-3 bg-white rounded border">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <label class="form-check-label fw-bold d-block" for="enableWriter">
                                                                <i class="bi bi-pencil-square me-2 text-warning"></i>Asistente de Escritura
                                                            </label>
                                                            <div class="small text-muted">Herramientas para Publicadores (Mejorar redacción, SEO).</div>
                                                        </div>
                                                        <input class="form-check-input fs-4" type="checkbox" role="switch" 
                                                               id="enableWriter" name="enable_writing_assistant" 
                                                               <?php echo $config['enable_writing_assistant'] ? 'checked' : ''; ?>>
                                                    </div>
                                                    </div>
                                                </div>





                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <a href="index-admin.php" class="btn btn-secondary me-md-2">Cancelar</a>
                                            <button type="submit" class="btn btn-primary px-5">
                                                <i class="bi bi-save me-2"></i> Guardar Cambios
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script>
        AOS.init();

        // Toggle Password View
        document.getElementById('toggleKey').addEventListener('click', function() {
            const input = document.getElementById('apiKeyInput');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });

        // Update Toggle Text
        document.getElementById('enableTools').addEventListener('change', function() {
            const text = document.getElementById('statusText');
            if (this.checked) {
                text.textContent = 'Activo';
                text.classList.remove('text-muted');
                text.classList.add('text-success', 'fw-bold');
            } else {
                text.textContent = 'Inactivo';
                text.classList.remove('text-success', 'fw-bold');
                text.classList.add('text-muted');
            }
        });
        
        // Trigger initial
        document.getElementById('enableTools').dispatchEvent(new Event('change'));
    </script>
</body>
</html>
