# 🦅 GRIMOIRE DE DEFENSA: LAB EXPLORA (DOCUMENTACIÓN TÉCNICA Y ESTRATÉGICA)
> **Versión Maestra para Jueces y Evaluadores**
> *Este documento es la fuente de verdad absoluta del proyecto. Contiene cada detalle técnico, decisión de diseño y justificación estratégica.*

---

## 📑 ÍNDICE DE CONTENIDOS

1.  **VISIÓN ESTRATÉGICA (EL PITCH)**
    *   Introducción de Alto Impacto
    *   El Problema Detallado
    *   La Solución (Nuestros 3 Pilares)
    *   Diferenciadores Clave
2.  **ARQUITECTURA DEL SISTEMA**
    *   Stack Tecnológico
    *   Estructura de Carpetas Explicada
    *   Flujo de Datos (MVC Implícito)
3.  **INGENIERÍA DE DATOS (BASE DE DATOS)**
    *   Diagrama Relacional (Explicado)
    *   Diccionario de Datos (Tablas Clave)
4.  **MÓDULOS DE CÓDIGO (DEEP DIVE)**
    *   Módulo de Autenticación & Seguridad
    *   Módulo de Publicaciones & Archivos
    *   Módulo de Interacción Social
    *   Módulo de Administración
5.  **INNOVACIONES TÉCNICAS (LO QUE NOS HACE ÚNICOS)**
    *   Motor TTS (Texto a Voz) Híbrido
    *   Moderación IA Local (Sin APIs Externas)
    *   Visualización de Archivos en Cliente
6.  **SEGURIDAD Y RENDIMIENTO**
    *   Protocolos de Seguridad Implementados
    *   Optimizaciones de Rendimiento
7.  **BATERÍA DE PREGUNTAS Y RESPUESTAS (Q&A)**
    *   Preguntas sobre Backend
    *   Preguntas sobre Frontend
    *   Preguntas de Negocio/Escalabilidad
8.  **GUIÓN DE DEMOSTRACIÓN EN VIVO**

---

## 🚀 1. VISIÓN ESTRATÉGICA (EL PITCH)

### 🎤 Introducción (El Gancho)
"Vivimos en una era donde la información sobra, pero el conocimiento accesible falta. En el entorno educativo actual, las plataformas son estáticas, inseguras y excluyentes. **Lab Explora** nace no como una simple página de noticias, sino como un **Ecosistema de Gestión de Conocimiento Inclusivo**."

### 🛑 El Problema
1.  **Exclusión Digital:** Un estudiante con discapacidad visual o dificultades de lectura (dislexia) no puede consumir el mismo contenido que sus compañeros si este está atrapado en un PDF o una imagen.
2.  **Inseguridad de Contenido:** Las redes sociales escolares suelen llenarse de *spam*, *bullying* o lenguaje inapropiado (groserías) porque la moderación manual es lenta e ineficiente.
3.  **Fragmentación:** La información académica vive dispersa en correos, grupos de WhatsApp y papeles físicos.

### 💡 La Solución: Lab Explora
Una plataforma web progresiva (PWA) que centraliza la difusión académica con tres superpoderes:
1.  **Accesibilidad Universal:** Transformamos cualquier formato (Texto, PDF, Word, Imagen) en Audio audible y texto legible.
2.  **Moderación Inteligente (Local AI):** Un "guardián digital" pre-entrenado que filtra el contenido antes de que sea público, garantizando un espacio seguro sin costo de servidores externos.
3.  **Experiencia Premium:** Una interfaz diseñada para cautivar al estudiante, rápida como una app nativa pero accesible desde cualquier navegador.

---

## 🏗️ 2. ARQUITECTURA DEL SISTEMA

### 🛠️ Stack Tecnológico
*   **Lenguaje Servidor:** PHP 8.1+ (Nativo, sin frameworks pesados para maximizar compatibilidad en servidores escolares modestos).
*   **Base de Datos:** MySQL / MariaDB (Relacional, optimizada con índices).
*   **Frontend:** HTML5, CSS3 (Vanilla + Custom Properties), JavaScript (ES6+ Vanilla).
*   **Servidor Web:** Apache (XAMPP environment).
*   **Librerías Clave (Frontend):**
    *   `Mammoth.js`: Renderizado de .docx a HTML.
    *   `Tesseract.js`: OCR (Reconocimiento Óptico de Caracteres) vía WebAssembly.
    *   `Bootstrap 5`: Grid system y componentes base (personalizados).

### 📂 Estructura de Carpetas (Mapa del Tesoro)
Si el juez pregunta: *"¿Cómo organizaron su código?"*

*   `/ (Raíz)`: Controladores de vista principales (`pagina-principal.php`, `ver-publicacion.php`). Son el punto de entrada.
*   `/base_db`: **Infraestructura**. Contiene los scripts `.sql` para recrear la base de datos desde cero.
*   `/assets`: **Recursos Estáticos**.
    *   `/css`: Hojas de estilo modulares (`main.css`, `admin.css`).
    *   `/js`: Scripts interactivos (`main.js`, `tts.js`).
    *   `/vendor`: Librerías de terceros aisladas.
*   `/forms`: **La Lógica de Negocio (El Cerebro)**.
    *   `conexion.php`: Singleton para conexión a BD.
    *   `FuncionesTexto.php`: Helper estático para extracción de texto (PDF/Word).
    *   `/admins`: Lógica exclusiva de administración (`index-admin.php`, `gestionar-reportes.php`).
    *   `/publicadores`: Lógica para creadores de contenido (`guardar_publicacion.php`).
*   `/ollama_ia`: **Módulo de Inteligencia**.
    *   `ModeradorLocal.php`: Clase principal del algoritmo de moderación.
    *   `panel-moderacion.php`: Interfaz dedicada para revisión IA.
*   `/uploads`: **Almacenamiento**. Carpeta con permisos de escritura para guardar los archivos de los usuarios.

---

## 🗄️ 3. INGENIERÍA DE DATOS (BASE DE DATOS)

Nuestra base de datos cumple la **3ra Forma Normal (3NF)** para evitar redundancia.

### 📋 Diccionario de Datos Principal

#### Tabla: `publicaciones`
*El corazón del sistema.*
*   `id` (PK): Identificador único.
*   `titulo`: `VARCHAR(200)`.
*   `contenido`: `LONGTEXT`. Almacena HTML rico o texto plano.
*   `archivo_url`: `VARCHAR`. Ruta relativa al archivo adjunto (si existe).
*   `tipo_archivo`: `ENUM('pdf', 'docx', 'imagen')`.
*   `estado`: `ENUM`.
    *   `'borrador'`: Solo el autor lo ve.
    *   `'publicado'`: Visible para todos.
    *   `'revision'`: **CRÍTICO**. Estado intermedio donde la IA aprobó la calidad pero detectó un archivo adjunto que requiere ojo humano.
    *   `'rechazada'`: Bloqueado por la IA (groserías) o por admin.
*   `mensaje_rechazo`: Feedback para el autor.

#### Tabla: `usuarios` / `publicadores` / `admins`
*Separación de roles física para seguridad.*
*   Usamos tablas separadas en lugar de una sola con columna `rol` para permitir atributos específicos (ej: `especialidad` en publicadores, `nivel` en admins).
*   Todas usan `password_hash` (Bcrypt) de 60 caracteres.

#### Tabla: `interacciones`
*Tabla pivote para métricas.*
*   `id` (PK)
*   `usuario_id` (FK)
*   `publicacion_id` (FK)
*   `tipo`: `ENUM('like', 'guardado')`.
*   *Unique Constraint:* Un usuario solo puede dar 1 like por publicación.

---

## 💻 4. MÓDULOS DE CÓDIGO (DEEP DIVE)

### A. Autenticación (`inicio-sesion.php`)
No usamos un simple `if`. Implementamos:
1.  **Validación de Inputs:** `filter_var($email, FILTER_VALIDATE_EMAIL)`.
2.  **Consulta Segura:** `$stmt->bind_param` para buscar el usuario.
3.  **Verificación de Hash:** `password_verify($input_pass, $hash_bd)`.
4.  **Sesión Robusta:**
    *   `session_regenerate_id(true)`: Previene ataques de fijación de sesión.
    *   `$_SESSION['rol']` y `$_SESSION['last_activity']`: Para timeouts automáticos.

### B. Sistema de Publicación (`guardar_publicacion.php`)
El flujo es complejo y seguro:
1.  **Recepción:** Recibe `POST` y `FILES`.
2.  **Validación de Archivo:**
    *   No confiamos en la extensión `.pdf`. Usamos `finfo_file()` para leer los "Magic Bytes" del archivo y saber si es realmente un PDF o un virus `.exe` renombrado.
3.  **Saneamiento:** Limpiamos el nombre del archivo y generamos un ID único (`uniqid()`) para evitar sobrescrituras.
4.  **Moderación (El Hook):**
    *   Antes de guardar el estado final, llamamos a `ModeradorLocal::analizarPublicacion()`.
    *   Dependiendo del retorno, el estado se guarda como `publicado`, `rechazada` o `revision`.

---

## 🔬 5. INNOVACIONES TÉCNICAS (NUESTRO "SECRET SAUCE")

Esto es lo que deben mencionar para ganar puntos extra por complejidad técnica.

### 🗣️ Motor TTS Híbrido (`ver-publicacion.php`)
*   **Desafío:** La API `window.speechSynthesis` es inestable. Si le das 5000 palabras, se corta a los 15 segundos en Chrome/Edge.
*   **Nuestra Ingeniería:**
    1.  **Algoritmo de Chunking JS:**
        ```javascript
        function chunkText(text, maxLength) {
            // Divide el texto en oraciones completas, no corta palabras.
            // Usa Regex para buscar puntos, comas o espacios cerca del límite.
        }
        ```
    2.  **Cola de Reproducción:** Un array `audioChunks` almacena los fragmentos. Una función recursiva `speakNextChunk()` reproduce el índice `i`, y en el evento `onend`, dispara `i+1`.
    3.  **Extractor OCR (Tesseract):**
        *   Si detectamos `<img class="content-image">`, instanciamos un `Tesseract.Worker`.
        *   Procesamos la imagen en un hilo separado (Web Worker) para no congelar la UI.
        *   El texto resultante se inyecta en la variable `originalText` del TTS.

### 🤖 Moderación IA Local (`ModeradorLocal.php` + `FuncionesTexto.php`)
*   **Desafío:** Leer archivos binarios (.docx, .pdf) en el servidor sin instalar librerías pesadas como `phpword`.
*   **Solución "Forensic":**
    *   **PDF:** Leemos el stream binario y buscamos bloques `BT` (Begin Text) y `ET` (End Text) usando Regex avanzados.
    *   **DOCX:** Un archivo `.docx` es en realidad un `.zip`.
        *   Intentamos usar la clase `ZipArchive` de PHP.
        *   **Fallback (Plan B):** Si el servidor no tiene ZipArchive (común en XAMPP básico), ejecutamos un comando de sistema:
            *   *Windows:* `PowerShell` script para abrir el zip en memoria.
            *   *Linux:* Comando `tar -xOzf` para extraer `word/document.xml`.
        *   Esta redundancia garantiza que el sistema funcione en cualquier servidor.

---

## 🛡️ 6. SEGURIDAD

### P: "¿Cómo evitan que hackeen la página?"

1.  **SQL Injection (Inyección SQL):**
    *   *Defensa:* Uso estricto de **Consultas Preparadas (Prepared Statements)** en todo el código. Los datos del usuario nunca tocan la cadena SQL directamente.
    *   *Ejemplo:* `SELECT * FROM users WHERE email = ?` (El `?` es un placeholder seguro).

2.  **XSS (Cross-Site Scripting):**
    *   *Defensa:* **Escapado de Salida**. Cada vez que imprimimos algo en pantalla (`echo`), lo envolvemos en `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`. Esto convierte `<script>` en `&lt;script&gt;`, anulando su ejecución.

3.  **CSRF (Cross-Site Request Forgery):**
    *   *Defensa:* Verificación de `REQUEST_METHOD` y validación de permisos de sesión antes de realizar acciones destructivas (borrar, editar).

4.  **Subida de Archivos Maliciosos:**
    *   *Defensa:*
        1.  Renombramos todos los archivos subidos.
        2.  Verificamos el Tipo MIME.
        3.  Almacenamos los archivos en una carpeta sin permisos de ejecución de scripts (por configuración de `.htaccess` si fuera producción).

---

## ❓ 7. BATERÍA DE PREGUNTAS Y RESPUESTAS (Q&A)

### Nivel 1: Básico
**P: ¿Qué lenguaje usaron?**
R: PHP nativo para el backend y Javascript nativo para el frontend. Queríamos control total y máximo rendimiento.

**P: ¿Es responsiva?**
R: Sí, totalmente. Usamos CSS Grid y Media Queries. El menú lateral se oculta en móviles y las tablas tienen scroll horizontal.

### Nivel 2: Intermedio
**P: ¿Cómo validan que el usuario es quien dice ser?**
R: Usamos sesiones de servidor (`$_SESSION`). Al loguearse, el servidor emite una cookie `PHPSESSID`. En cada carga de página, verificamos que esa sesión exista y corresponda a un usuario activo en base de datos.

**P: ¿Por qué hay una carpeta `ollama_ia` si dicen que es "local"?**
R: El nombre es un tributo a la tecnología de modelos locales, aunque nuestra implementación actual es un algoritmo heurístico optimizado en PHP (`ModeradorLocal`). Está diseñado modularmente para, en el futuro, conectar una API de LLM real (como Llama 3 corriendo localmente) en ese mismo archivo sin romper el resto del sistema.

### Nivel 3: Experto ("Matadoras")
**P: "PHP es inseguro/viejo. ¿Por qué no Node.js?"**
R: "PHP alimenta el 77% de la web (incluyendo Facebook y Wikipedia). Es inseguro solo si se escribe mal. Nosotros usamos PHP 8 moderno con prácticas de seguridad tipadas. Además, para un entorno escolar, PHP es más fácil de desplegar (XAMPP/LAMP) que configurar un entorno de contenedores Node.js/Docker, lo que garantiza mantenibilidad a largo plazo."

**P: "Si subo un archivo Word de 100MB, ¿se cae el servidor?"**
R: "Tenemos configurado el límite `upload_max_filesize` en PHP. Además, nuestro script de extracción de texto está optimizado para leer streams (`fopen`) en lugar de cargar todo en memoria, y si falla, implementamos `timeouts` para evitar que el proceso cuelgue el servidor."

**P: "¿Cómo manejan la concurrencia? ¿Si 1000 alumnos entran a la vez?"**
R: "MySQL maneja bloqueos de fila (row-level locking) gracias al motor **InnoDB** que usamos. Esto permite lecturas y escrituras simultáneas sin corromper datos. Además, los archivos estáticos (CSS/JS) son cacheados por el navegador."

---

## 🎬 8. GUIÓN DE DEMOSTRACIÓN EN VIVO (Minuto a Minuto)

**Minuto 0-1: La Entrada (Login & Landing)**
*   Abre la página. Muestra el diseño limpio.
*   "Miren qué rápido carga. No hay Spinners eternos."
*   Loguéate como **Usuario Normal**.

**Minuto 1-3: La Accesibilidad (El "Wow")**
*   Entra a una publicación que tenga texto y un PDF adjunto.
*   "Imaginemos que soy un alumno con debilidad visual."
*   Clic en **"Escuchar Artículo"**.
    *   Deja que lea el título.
    *   Deja que lea el cuerpo.
    *   **CLAVE:** Deja que diga "Contenido del PDF adjunto..." y empiece a leer el PDF.
*   *Comentario:* "El sistema extrajo el texto del PDF en el servidor y me lo está leyendo. No tuve que descargar nada."

**Minuto 3-5: El Archivo Word (Interactivo)**
*   Entra a una publicación con un `.docx`.
*   Muestra el documento renderizado en pantalla (Mammoth.js).
*   "Miren, es un Word, pero lo veo como página web. Puedo copiar el texto, usar el buscador del navegador..."

**Minuto 5-7: La Seguridad (Moderación en Vivo)**
*   Cierra sesión. Entra como **Publicador**.
*   Crea una publicación nueva.
*   Título: "Prueba de Seguridad".
*   Sube un archivo Word (`prueba_groserias.docx`) que tengas preparado con la palabra "puto" escondida en el texto.
*   Clic en "Publicar".
*   **CLAVE:** Muestra la alerta roja: **"RECHAZADA: Contiene palabras prohibidas: puto"**.
*   *Comentario:* "El sistema abrió el Word, lo leyó, detectó la grosería y me bloqueó. Ningún humano tuvo que intervenir."

**Minuto 7-8: El Admin Panel**
*   Entra como **Admin**.
*   Muestra el Dashboard con gráficas.
*   Ve a "Gestionar Publicaciones". Muestra el filtro por estado.
*   Cierre: "Esto es Lab Explora: Seguro, Accesible y Eficiente."

---
*Fin del Documento.*
