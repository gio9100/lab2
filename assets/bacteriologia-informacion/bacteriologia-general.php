<?php 
session_start();
require_once __DIR__. "/../../forms/usuario.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Bacteriología General - Lab Explorer</title>
    <meta name="description"
        content="Información completa sobre bacteriología general para pacientes y profesionales de la salud">
    <meta name="keywords" content="bacteriología, microbiología, bacterias, laboratorio clínico">

    <!-- Favicon -->
    <link href="../img/logo/logo-lab.ico" rel="icon">
    <link href="../img/logo/logo-lab.ico" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../vendor/aos/aos.css" rel="stylesheet">
    <link href="../vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="../vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

    <!-- Main CSS -->
    <link href="../css/main.css" rel="stylesheet">
    <link href="../css/estilos-paginas-informacion.css" rel="stylesheet">
</head>

<body class="info-paginas">

    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">

            <div class="top-row d-flex align-items-center justify-content-between">
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../img/logo/logobrayan2.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explora (Bacteriología)</h1><span></span>
                </a>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <a href="https://www.facebook.com/laboratorioabcdejacona?locale=es_LA" target="_blank"
                            class="facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" target="_blank" class="twitter"><i class="bi bi-twitter"></i></a>
                        <a href="https://www.instagram.com/lab_explorer_cbtis_52/" target="_blank" class="instagram"><i
                                class="bi bi-instagram"></i></a>
                        <?php if($usuario_logueado): ?>
                        <div class="usuario">
                            <a href="../../forms/perfil.php">Perfil</a>
                            <a href="../../forms/logout.php">Cerrar sesión</a>
                            <a href="../../Bacteorologia.php">Bacteriologia</a>
                        </div>
                        <?php else: ?>
                        <a href="../../forms/inicio-sesion.php">Inicia sesión</a>
                        <a href="../../forms/register.php">Crear Cuenta</a>
                        <a href="../../Bacteorologia.php">Bacteriología</a>
                        <?php endif; ?>
                    </div>

                    <form class="search-form ms-4">
                        <input type="text" placeholder="Search..." class="form-control">
                        <button type="submit" class="btn"><i class="bi bi-search"></i></button>
                    </form>
                </div>
            </div>

        </div>
    </header>

    <main class="main">
        <!-- Hero Section de Bacteriología General -->
        <section id="info-hero-section" class="info-hero-section section">
            <div class="container" data-aos="fade-up">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="hero-title">Bacteriología General</h1>
                        <p class="hero-subtitle">Información esencial para pacientes y profesionales de la salud</p>
                        <p class="hero-description">
                            La bacteriología es la rama de la microbiología que estudia las bacterias, su estructura,
                            fisiología, genética y su relación con el medio ambiente y los seres humanos.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <img src="../img/bacteriologia/bacteriologia-general.jfif" alt="Bacteriología General"
                            class="img-fluid hero-image">
                    </div>
                </div>
            </div>
        </section>

        <!-- Información para Pacientes -->
        <section id="info-pacientes" class="info-section section">
            <div class="container" data-aos="fade-up">
                <div class="section-header">
                    <h2 class="section-title">Información para Pacientes</h2>
                    <p class="section-description">Lo que necesitas saber sobre los estudios bacteriológicos</p>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="info-card patient">
                            <h3 class="card-title">¿Qué es un cultivo bacteriano?</h3>
                            <p>Es un examen que permite identificar bacterias en muestras como:</p>
                            <ul class="feature-list">
                                <li>Sangre (hemocultivo)</li>
                                <li>Orina (urocultivo)</li>
                                <li>Heces (coprocultivo)</li>
                                <li>Exudados de heridas</li>
                                <li>Secreciones respiratorias</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="info-card patient">
                            <h3 class="card-title">Preparación para el examen</h3>
                            <ul class="feature-list">
                                <li>Sigue las instrucciones específicas de tu médico</li>
                                <li>Informa sobre medicamentos que estés tomando</li>
                                <li>Mantén una adecuada higiene antes de la toma de muestra</li>
                                <li>Entrega la muestra en el tiempo indicado</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="warning-box">
                    <h4><i class="bi bi-exclamation-triangle"></i> Importante para pacientes</h4>
                    <p>Los resultados pueden tardar entre 24 horas y varias semanas, dependiendo del tipo de bacteria.
                        Sigue siempre las indicaciones de tu médico y completa el tratamiento antibiótico aunque te
                        sientas mejor.</p>
                </div>
            </div>
        </section>

        <!-- Información para Profesionales -->
        <section id="info-profesionales" class="info-section section bg-light">
            <div class="container" data-aos="fade-up">
                <div class="section-header">
                    <h2 class="section-title">Información para Profesionales</h2>
                    <p class="section-description">Herramientas y metodologías en bacteriología diagnóstica</p>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="info-card professional">
                            <h3 class="card-title">Métodos de Tinción</h3>
                            <ul class="feature-list">
                                <li>Tinción de Gram</li>
                                <li>Tinción de Ziehl-Neelsen</li>
                                <li>Tinción de Giemsa</li>
                                <li>Tinción de azul de lactofenol</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="info-card professional">
                            <h3 class="card-title">Medios de Cultivo</h3>
                            <ul class="feature-list">
                                <li>Agar sangre</li>
                                <li>Agar MacConkey</li>
                                <li>Agar chocolate</li>
                                <li>Caldo tioglicolato</li>
                                <li>Agar Sabouraud</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="info-card professional">
                            <h3 class="card-title">Pruebas Bioquímicas</h3>
                            <ul class="feature-list">
                                <li>Prueba de catalasa</li>
                                <li>Prueba de oxidasa</li>
                                <li>Fermentación de azúcares</li>
                                <li>Prueba de indol</li>
                                <li>Prueba de ureasa</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Metodología de Trabajo -->
                <div class="methodology-section">
                    <h3 class="methodology-title">Metodología de Trabajo en el Laboratorio</h3>
                    <div class="methodology-steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Recepción de Muestra</h4>
                                <p>Verificación de requisitos y condiciones de transporte</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Procesamiento Inicial</h4>
                                <p>Tinciones directas y siembra en medios de cultivo</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Incubación</h4>
                                <p>Condiciones específicas de temperatura y atmósfera</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>Identificación</h4>
                                <p>Caracterización morfológica y bioquímica</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h4>Antibiograma</h4>
                                <p>Prueba de sensibilidad a antimicrobianos</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">6</div>
                            <div class="step-content">
                                <h4>Reporte Final</h4>
                                <p>Interpretación y emisión de resultados</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tipos de Bacteriología -->
        <section id="tipos-bacteriologia" class="types-section section">
            <div class="container" data-aos="fade-up">
                <div class="section-header">
                    <h2 class="section-title">Tipos de Bacteriología</h2>
                    <p class="section-description">Especializaciones en el estudio bacteriano</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-hospital"></i>
                            </div>
                            <h4>Bacteriología Clínica</h4>
                            <p>Diagnóstico de infecciones bacterianas en pacientes</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-cup-straw"></i>
                            </div>
                            <h4>Bacteriología Alimentaria</h4>
                            <p>Control de calidad y seguridad en alimentos</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h4>Bacteriología Ambiental</h4>
                            <p>Estudio de bacterias en agua, suelo y aire</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-heart-pulse"></i>
                            </div>
                            <h4>Bacteriología Veterinaria</h4>
                            <p>Diagnóstico en animales y zoonosis</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-dna"></i>
                            </div>
                            <h4>Bacteriología Molecular</h4>
                            <p>Técnicas de biología molecular para identificación</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-droplet"></i>
                            </div>
                            <h4>Bacteriología Anaerobia</h4>
                            <p>Estudio de bacterias que no requieren oxígeno</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS -->
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/aos/aos.js"></script>
    <script src="../vendor/swiper/swiper-bundle.min.js"></script>
    <script src="../vendor/glightbox/js/glightbox.min.js"></script>

    <!-- Main JS-->
    <script src="../js/main.js"></script>

</body>

</html>