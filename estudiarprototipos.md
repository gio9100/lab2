# 🎓 Guía de Defensa de Prototipo: Lab Explora

Esta guía está diseñada para presentar el proyecto **Lab Explora** ante jueces, explicando tanto el valor del producto como las decisiones técnicas detrás del código.

---

## 📢 1. El "Pitch" (Qué decirle a los jueces)

**Introducción:**
"Buenas tardes. Presentamos **Lab Explora**, una plataforma web de publicación de contenido académico y noticias diseñada para ser **inclusiva, segura y eficiente**."

**El Problema:**
"Muchas plataformas educativas son estáticas, no moderan el contenido automáticamente (permitiendo 'spam' o lenguaje inapropiado) y carecen de herramientas de accesibilidad para personas con dificultades visuales o de lectura."

**Nuestra Solución:**
"Lab Explora soluciona esto con tres pilares tecnológicos:"
1.  **Accesibilidad Real**: Un motor de Text-to-Speech (Texto a Voz) avanzado que lee no solo artículos, sino también **imágenes (OCR)** y documentos **Word/PDF** directamente en el navegador.
2.  **Seguridad & Moderación IA**: Un sistema de moderación local que analiza automáticamente el contenido (incluso dentro de archivos adjuntos) para bloquear groserías y filtrar calidad antes de que un humano tenga que intervenir.
3.  **Experiencia de Usuario (UI/UX)**: Una interfaz moderna, responsiva (PWA ready) y rápida, construida con tecnologías estándar optimizadas.

---

## ❓ 2. Preguntas Técnicas Posibles y Respuestas

Los jueces pueden preguntar "cómo hiciste X cosa". Aquí tienes las respuestas técnicas por archivo/tecnología.

### 🖥️ Frontend (HTML / CSS / JS)

**P: ¿Qué tecnologías usaron en el frontend?**
**R:** "Usamos HTML5 semántico y CSS3 nativo (Vanilla) con variables CSS para el sistema de temas (colores consistentes). Para la interactividad, usamos JavaScript puro (ES6+) para máximo rendimiento, evitando la carga de frameworks pesados como React o Angular innecesariamente para este alcance."

**P: ¿Cómo hacen que la computadora lea el texto ("Escuchar Artículo")?**
**R:** "Utilizamos la **Web Speech API** nativa de los navegadores modernos.
*   **El Reto:** Los navegadores cortan el audio si el texto es muy largo.
*   **Nuestra Solución:** Implementamos un algoritmo de 'Chunking' (fragmentación) en `ver-publicacion.php`. Dividimos el texto en bloques de ~200 caracteres respetando los signos de puntuación y los reproducimos secuencialmente (`speakNextChunk`)."

**P: ¿Cómo leen el texto dentro de una imagen o un Word?**
**R:** "Es un enfoque híbrido:
*   **Imágenes:** Usamos **Tesseract.js**, una librería de OCR (Reconocimiento Óptico de Caracteres) que corre en el navegador mediante WebAssembly y extrae el texto de los píxeles.
*   **Word (DOCX):** Usamos **Mammoth.js** para convertir el XML interno del .docx a HTML visible en tiempo real sin necesitar descargas."

**P: ¿Es responsivo (se ve bien en celular)?**
**R:** "Sí. Usamos **CSS Grid y Flexbox**. Tenemos un diseño 'Mobile First' con menús 'Off-Canvas' (la barra lateral que sale en móviles) y tablas adaptables."

---

### ⚙️ Backend (PHP / MySQL)

**P: ¿Cómo manejan la seguridad de los datos?**
**R:**
1.  **Inyección SQL:** Todo el acceso a base de datos usa **Prepared Statements** (`$stmt->bind_param`) en PHP. Nunca concatenamos variables directamente en el SQL.
2.  **XSS (Cross-Site Scripting):** Usamos `htmlspecialchars()` al mostrar cualquier dato ingresado por el usuario para evitar que inyecten scripts maliciosos.
3.  **Passwords:** Las contraseñas se guardan encriptadas con `password_hash()` (Bcrypt), nunca en texto plano.

**P: ¿Cómo funciona la subida de archivos?**
**R:** "Validamos el archivo en el servidor (`guardar_publicacion.php`). Revisamos:
1.  **Tipo MIME y Extensión:** Para asegurar que sea un PDF, DOCX o Imagen real.
2.  **Nombre Único:** Generamos nombres aleatorios/únicos para evitar colisiones (`uniqid`) en la carpeta `uploads/`."

**P: Veo que tienen un sistema de 'Roles' (Admin vs Publicador). ¿Cómo controlan eso?**
**R:** "Mediante Sesiones de PHP (`session_start`). Al loguearse, guardamos el `nivel` del usuario. En cada página crítica (admin), verificamos `if (!isset($_SESSION['admin_nivel']))` y si no tiene permiso, lo redirigimos fuera (`header('Location: ...')`)."

---

### 🤖 Inteligencia Artificial y Moderación (`ModeradorLocal.php`)

**P: ¿Cómo funciona la "IA" de moderación local?**
**R:** "Es un motor lógico construido en PHP (`ModeradorLocal.php`) que evalúa cada publicación basándose en reglas heurísticas:
1.  **Filtro de Profanidad:** Busca groserías en una lista negra ('blacklist').
2.  **Análisis de Calidad:** Asigna puntaje basado en riqueza de vocabulario (palabras técnicas) y estructura (párrafos).
3.  **Extracción Profunda:** Lo innovador es que **abrimos** los archivos adjuntos.
    *   Para **Word**, si PHP falla, usamos comandos de sistema (**PowerShell** en Windows o `tar` en Linux) para desempaquetar el XML del documento y leerlo.
    *   Esto impide que alguien suba un Word con insultos y pase desapercibido."

**P: ¿Por qué eligieron moderación local y no la API de OpenAI/ChatGPT?**
**R:** "Por **privacidad y costo**. Al hacerlo local:
1.  No enviamos datos de alumnos a servidores externos.
2.  Funciona sin internet.
3.  Es de costo cero y latencia mínima (muy rápido)."

---

## 🚀 3. Flujo de Demostración (Demo)

Si tienes que enseñar el proyecto en vivo, sigue este orden:

1.  **Inicio (Wow Factor):** Muestra la *Landing Page*. Destaca el diseño limpio y las animaciones.
2.  **Lectura (Accesibilidad):** Entra a una publicación larga. Dale click a **"Escuchar Artículo"**. Deja que lea un poco.
3.  **Tecnología (Word/OCR):** Muestra una publicación que sea solo un archivo Word. Muestra cómo se ve el documento directo en la página web (gracias a Mammoth) y dale a escuchar.
4.  **Moderación en Vivo (Backend):**
    *   Loguéate con una cuenta de Publicador.
    *   Intenta crear una publicación que diga "puto" en el título o sube un Word con esa palabra.
    *   Muestra cómo el sistema la **RECHAZA** automáticamente al instante.
    *   Luego sube una limpia y muestra cómo se **PUBLICA** automáticamente.
5.  **Panel de Admin:** Entra como Admin y muestra las gráficas/estadísticas y la gestión de usuarios.

---

## ✨ Cierre

"Lab Explora no es solo una página de noticias; es un prototipo escalable de cómo las instituciones educativas pueden gestionar contenido de forma segura, moderna y accesible para todos."
