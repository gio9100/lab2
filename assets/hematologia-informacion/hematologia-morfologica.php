<?php 
session_start();
require_once __DIR__. "/../../forms/usuario.php";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Hematología Morfológica - Lab Explorer</title>
    <meta name="description" content="Información completa sobre hematología morfológica para pacientes y profesionales de la salud">
    <meta name="keywords" content="hematología, morfología, células sanguíneas, laboratorio clínico">

    <!-- Favicon -->
    <link href="../img/logo/logobrayan2.ico" rel="icon">
    <link href="../img/logo/logobrayan2.ico" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
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
                    <h1 class="sitename">Lab-Explorer (Hematología)</h1><span></span>
                </a>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <a href="https://www.facebook.com/laboratorioabcdejacona?locale=es_LA" target="_blank" class="facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" target="_blank" class="twitter"><i class="bi bi-twitter"></i></a>
                        <a href="https://www.instagram.com/lab_explorer_cbtis_52/" target="_blank" class="instagram"><i class="bi bi-instagram"></i></a>
                        <?php if($usuario_logueado): ?>
                        <div class="usuario">
                            <a href="../../forms/perfil.php">Perfil</a>
                            <a href="../../forms/logout.php">Cerrar sesión</a>
                            <a href="../../index.php">Página Principal</a>
                        </div>
                        <?php else: ?>
                        <a href="../../forms/inicio-sesion.php">Inicia sesión</a>
                        <a href="../../forms/register.php">Crear Cuenta</a>
                         <a href="../../Hematologia.php">Hematología</a>
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
        <!-- Hero Section de Hematología Morfológica -->
        <section id="info-hero-section" class="info-hero-section section">
            <div class="container" data-aos="fade-up">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="hero-title">Hematología Morfológica</h1>
                        <p class="hero-subtitle">Estudio de la forma y estructura de las células sanguíneas</p>
                        <p class="hero-description">
                            La hematología morfológica es la rama de la hematología que estudia la forma, tamaño, 
                            estructura y características de las células sanguíneas mediante el examen microscópico 
                            de extensiones de sangre periférica y médula ósea.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <img src="../img/hematologia/hematologia-morfologica.jpg" alt="Hematología Morfológica" class="img-fluid hero-image">
                    </div>
                </div>
            </div>
        </section>

        <!-- Información para Pacientes -->
        <section id="info-pacientes" class="info-section section">
            <div class="container" data-aos="fade-up">
                <div class="section-header">
                    <h2 class="section-title">Información para Pacientes</h2>
                    <p class="section-description">Lo que necesitas saber sobre los estudios hematológicos</p>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="info-card patient">
                            <h3 class="card-title">¿Qué es un frotis sanguíneo?</h3>
                            <p>Es un examen que permite observar las células de la sangre al microscopio para evaluar:</p>
                            <ul class="feature-list">
                                <li>Glóbulos rojos (eritrocitos)</li>
                                <li>Glóbulos blancos (leucocitos)</li>
                                <li>Plaquetas (trombocitos)</li>
                                <li>Presencia de células anormales</li>
                                <li>Parásitos en sangre</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="info-card patient">
                            <h3 class="card-title">Preparación para el examen</h3>
                            <ul class="feature-list">
                                <li>No se requiere ayuno en la mayoría de casos</li>
                                <li>Informa sobre medicamentos que estés tomando</li>
                                <li>Evita esfuerzo físico intenso antes de la prueba</li>
                                <li>Mantén una hidratación adecuada</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="warning-box">
                    <h4><i class="bi bi-exclamation-triangle"></i> Importante para pacientes</h4>
                    <p>El frotis sanguíneo es una prueba complementaria al hemograma completo. Los resultados pueden 
                    estar disponibles en 24-48 horas. Sigue siempre las indicaciones de tu médico para una correcta 
                    interpretación de los hallazgos.</p>
                </div>
            </div>
        </section>

        <!-- Información para Profesionales -->
        <section id="info-profesionales" class="info-section section bg-light">
            <div class="container" data-aos="fade-up">
                <div class="section-header">
                    <h2 class="section-title">Información para Profesionales</h2>
                    <p class="section-description">Técnicas y metodologías en hematología morfológica</p>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="info-card professional">
                            <h3 class="card-title">Tinciones Hematológicas</h3>
                            <ul class="feature-list">
                                <li>Tinción de Wright</li>
                                <li>Tinción de Giemsa</li>
                                <li>Tinción de May-Grünwald-Giemsa</li>
                                <li>Tinción de Perls (hierro)</li>
                                <li>Tinción de PAS</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="info-card professional">
                            <h3 class="card-title">Células Sanguíneas</h3>
                            <ul class="feature-list">
                                <li>Serie roja: Eritrocitos</li>
                                <li>Serie blanca: Leucocitos</li>
                                <li>Serie megacariocítica: Plaquetas</li>
                                <li>Células precursoras</li>
                                <li>Células anormales y blastos</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="info-card professional">
                            <h3 class="card-title">Evaluación Morfológica</h3>
                            <ul class="feature-list">
                                <li>Tamaño celular (anisocitosis)</li>
                                <li>Forma celular (poiquilocitosis)</li>
                                <li>Coloración (hipocromía/hipercromía)</li>
                                <li>Inclusiones celulares</li>
                                <li>Relación núcleo/citoplasma</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="methodology-section">
                    <h3 class="methodology-title">Metodología de Trabajo en el Laboratorio</h3>
                    <div class="methodology-steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Preparación de Extensión</h4>
                                <p>Realización de frotis sanguíneo en portaobjetos</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Tinción</h4>
                                <p>Aplicación de tinciones específicas para hematología</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Examen Microscópico</h4>
                                <p>Evaluación sistemática al microscopio óptico</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>Recuento Diferencial</h4>
                                <p>Clasificación y recuento de tipos celulares</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h4>Descripción Morfológica</h4>
                                <p>Registro de alteraciones y hallazgos significativos</p>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-number">6</div>
                            <div class="step-content">
                                <h4>Reporte Final</h4>
                                <p>Interpretación y correlación con datos clínicos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="tipos-hematologia" class="types-section section">
            <div class="container" data-aos="fade-up">
                <div class="section-header">
                    <h2 class="section-title">Estudios en Hematología Morfológica</h2>
                    <p class="section-description">Evaluaciones especializadas en células sanguíneas</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-droplet"></i>
                            </div>
                            <h4>Frotis Sanguíneo</h4>
                            <p>Evaluación morfológica de células en sangre periférica</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-bone"></i>
                            </div>
                            <h4>Mielograma</h4>
                            <p>Estudio de células de médula ósea por aspiración</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-scissors"></i>
                            </div>
                            <h4>Biopsia de Médula Ósea</h4>
                            <p>Evaluación de arquitectura y celularidad medular</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-magnet"></i>
                            </div>
                            <h4>Citometría de Flujo</h4>
                            <p>Análisis inmunofenotípico de poblaciones celulares</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-dna"></i>
                            </div>
                            <h4>Citogenética</h4>
                            <p>Estudio de alteraciones cromosómicas en células</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="type-card">
                            <div class="type-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <h4>Citología Especial</h4>
                            <p>Tinciones especiales para inclusiones y patologías</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="alteraciones-morfologicas" class="alterations-section section bg-light">
            <div class="container" data-aos="fade-up">
                <div class="section-header">
                    <h2 class="section-title">Alteraciones Morfológicas Comunes</h2>
                    <p class="section-description">Hallazgos significativos en el estudio morfológico</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="alteration-card">
                            <h4 class="alteration-title">Alteraciones Eritrocitarias</h4>
                            <ul class="alteration-list">
                                <li>Anisocitosis (variación de tamaño)</li>
                                <li>Poiquilocitosis (variación de forma)</li>
                                <li>Hipocromía (disminución de hemoglobina)</li>
                                <li>Esferocitos (anemia hemolítica)</li>
                                <li>Dacriocitos (síndrome mielodisplásico)</li>
                                <li>Esquistocitos (anemia microangiopática)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alteration-card">
                            <h4 class="alteration-title">Alteraciones Leucocitarias</h4>
                            <ul class="alteration-list">
                                <li>Desviación a izquierda (infecciones)</li>
                                <li>Células en lágrima (mielofibrosis)</li>
                                <li>Gránulos tóxicos (procesos infecciosos)</li>
                                <li>Vacuolización citoplasmática</li>
                                <li>Células blásticas (leucemias)</li>
                                <li>Linfocitos atípicos (mononucleosis)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

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