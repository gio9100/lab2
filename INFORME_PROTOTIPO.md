# 📊 INFORME TÉCNICO DEL PROTOTIPO
## Lab-Explora: Plataforma de Divulgación Científica para Laboratorio Clínico

---

## 📋 RESUMEN EJECUTIVO

**Nombre del Proyecto:** Lab-Explora  
**Categoría:** Plataforma Web de Divulgación Científica  
**Nivel:** Educación Media Superior  
**Fecha de Presentación:** Diciembre 2025

### Descripción General

Lab-Explora es una plataforma web innovadora diseñada para democratizar el acceso al conocimiento científico en el ámbito del laboratorio clínico. El sistema permite a investigadores, profesionales de la salud y estudiantes publicar, compartir y acceder a contenido científico de calidad, con un enfoque en la accesibilidad y la verificación de contenido mediante moderación automatizada.

### Problema Identificado

En el contexto educativo y profesional del laboratorio clínico, existe una brecha significativa en la divulgación de conocimiento científico actualizado:

1. **Dispersión de información**: El conocimiento científico está fragmentado en múltiples fuentes
2. **Barreras de acceso**: Muchas publicaciones científicas requieren suscripciones costosas
3. **Falta de verificación**: No existe un sistema accesible para validar la calidad del contenido
4. **Desconexión entre profesionales**: Limitada colaboración entre investigadores y estudiantes

### Solución Propuesta

Lab-Explora aborda estos desafíos mediante:

- **Plataforma centralizada** para publicaciones científicas de acceso abierto
- **Sistema de moderación automatizada** basado en reglas locales para garantizar calidad
- **Autenticación robusta** con verificación en dos pasos para publicadores
- **Interfaz intuitiva** diseñada para usuarios de todos los niveles técnicos
- **Sistema de interacción social** que fomenta la colaboración científica

---

## 🎯 OBJETIVOS DEL PROYECTO

### Objetivo General

Desarrollar una plataforma web integral que facilite la publicación, distribución y acceso a contenido científico de calidad en el ámbito del laboratorio clínico, promoviendo la colaboración entre profesionales y estudiantes.

### Objetivos Específicos

1. **Democratizar el acceso al conocimiento científico** mediante una plataforma de código abierto
2. **Garantizar la calidad del contenido** a través de moderación automatizada basada en reglas
3. **Fomentar la colaboración** entre investigadores, profesionales y estudiantes
4. **Implementar medidas de seguridad robustas** para proteger la integridad de la información
5. **Crear una experiencia de usuario excepcional** con diseño responsive y accesible

---

## 💡 INNOVACIÓN Y DIFERENCIADORES

### 1. Sistema de Moderación Automatizada Local

**Innovación:** Moderación automática basada en reglas predefinidas procesadas localmente

**Características:**
- Validación de contenido mediante reglas configurables
- Detección de palabras prohibidas y contenido inapropiado
- Evaluación de completitud y formato
- Procesamiento 100% local (sin enviar datos a servicios externos)
- Respeto total a la privacidad de los autores

**Ventajas:**
- ✅ Moderación instantánea 24/7
- ✅ Reducción de carga administrativa
- ✅ Consistencia en criterios de evaluación
- ✅ Privacidad garantizada (datos no salen del servidor)

### 2. Sistema de Autenticación Multinivel

**Innovación:** Implementación de 2FA obligatorio para roles críticos

**Arquitectura de Seguridad:**
```
┌─────────────────┐
│ Usuarios        │ → 2FA Opcional
│ (Lectores)      │
└─────────────────┘

┌─────────────────┐
│ Publicadores    │ → 2FA OBLIGATORIO
│ (Autores)       │   + Aprobación Admin
└─────────────────┘

┌─────────────────┐
│ Administradores │ → 2FA OBLIGATORIO
│ (Moderadores)   │   + Niveles de acceso
└─────────────────┘
```

**Características de Seguridad:**
- Códigos 2FA encriptados con bcrypt
- Expiración automática (10 minutos)
- Bloqueo temporal tras intentos fallidos
- Registro de IP y auditoría de accesos

### 3. Credenciales Digitales Verificables

**Innovación:** Generación de credenciales PDF con firma digital única

**Características:**
- Hash criptográfico único por usuario
- Código QR para verificación rápida
- Diseño profesional con sello oficial
- Descarga instantánea en formato PDF
- Imposible de falsificar

### 4. Progressive Web App (PWA)

**Innovación:** Aplicación web que funciona como app nativa

**Capacidades:**
- Instalable en dispositivos móviles
- Funciona offline (caché inteligente)
- Notificaciones push
- Actualizaciones automáticas
- Experiencia similar a app nativa

---

## 🏗️ ARQUITECTURA TÉCNICA

### Stack Tecnológico

#### Frontend
```
├── HTML5 Semántico
├── CSS3 con Variables Personalizadas
├── JavaScript Vanilla (ES6+)
├── Bootstrap 5.3 (Framework UI)
├── Bootstrap Icons
├── Driver.js (Onboarding interactivo)
└── html2pdf.js (Generación de PDFs)
```

#### Backend
```
├── PHP 8.x
├── MySQL 8.0
└── PHPMailer 6.x (Envío de emails)
```

#### Servicios
```
├── SMTP (Correos transaccionales)
├── Service Worker (PWA)
└── Almacenamiento local (IndexedDB)
```

### Base de Datos

**Modelo Relacional Optimizado:**

```
usuarios (1) ──────┐
                   │
                   ├──▶ (N) publicaciones_guardadas
                   │
publicadores (1) ──┤
                   │
                   ├──▶ (N) publicaciones
                   │           │
                   │           ├──▶ (N) comentarios
                   │           ├──▶ (N) likes
                   │           └──▶ (N) reportes
                   │
admins (1) ────────┤
                   │
                   └──▶ (N) two_factor_codes
```

**Tablas Principales:**
- `usuarios` (7 campos, índices optimizados)
- `publicadores` (12 campos, estados de aprobación)
- `admins` (9 campos, niveles de acceso)
- `publicaciones` (15 campos, versionado)
- `categorias` (4 campos, jerarquía)
- `comentarios` (8 campos, anidamiento)
- `likes` (5 campos, unicidad)
- `reportes` (10 campos, moderación)
- `two_factor_codes` (8 campos, seguridad)

### Flujo de Datos

```
┌──────────────┐
│   Usuario    │
└──────┬───────┘
       │
       ▼
┌──────────────────┐
│  Autenticación   │ ◀─── 2FA (si aplica)
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│  Autorización    │ ◀─── Roles y permisos
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│  Acción          │ ◀─── CRUD operaciones
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│  Moderación Local │ ◀─── (si es publicación)
└──────────┴──────────┘
       │
       ▼
┌──────────────────┐
│  Base de Datos   │
└──────────────────┘
```

---

## 🔐 SEGURIDAD IMPLEMENTADA

### 1. Protección de Contraseñas

**Método:** Bcrypt con salt automático
```php
password_hash($password, PASSWORD_BCRYPT)
```

**Características:**
- Hash irreversible
- Salt único por contraseña
- Resistente a ataques de fuerza bruta
- Actualizable a algoritmos más seguros

### 2. Prevención de Inyección SQL

**Método:** Prepared Statements
```php
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
```

**Ventajas:**
- Separación de código y datos
- Validación automática de tipos
- Protección contra SQL Injection

### 3. Validación de Entrada

**Capas de validación:**
1. **Frontend:** HTML5 validation + JavaScript
2. **Backend:** Filter functions de PHP
3. **Base de datos:** Constraints y tipos de datos

**Ejemplo:**
```php
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$nombre = htmlspecialchars(trim($_POST['nombre']));
```

### 4. Protección CSRF

**Método:** Tokens de sesión únicos
- Token generado por sesión
- Validación en cada formulario
- Expiración automática

### 5. Control de Acceso

**Niveles de autorización:**
```
Público → Leer publicaciones
Usuario → Comentar, guardar, reportar
Publicador → Crear publicaciones
Admin → Moderar todo
```

### 6. Auditoría y Logs

**Registros de seguridad:**
- Intentos de login fallidos
- Cambios de contraseña
- Acciones administrativas
- Reportes de contenido
- Códigos 2FA generados

---

## 🎨 EXPERIENCIA DE USUARIO

### Diseño Responsive

**Breakpoints:**
```css
Mobile:  < 768px
Tablet:  768px - 991px
Desktop: ≥ 992px
```

**Características:**
- Sidebar colapsable en móvil
- Menú hamburguesa animado
- Tarjetas adaptativas
- Imágenes optimizadas
- Fuentes escalables

### Accesibilidad (A11y)

**Estándares WCAG 2.1:**
- ✅ Contraste de color adecuado (4.5:1)
- ✅ Navegación por teclado
- ✅ Etiquetas ARIA
- ✅ Textos alternativos en imágenes
- ✅ Formularios semánticos

### Onboarding Interactivo

**Driver.js Tour:**
- Guía paso a paso para nuevos usuarios
- Tooltips contextuales
- Destacado de elementos importantes
- Progreso visual
- Saltar o completar tour

### Asistente Virtual con IA

**Funcionalidades:**
- Generación automática de resúmenes
- Formateo profesional de contenido
- Verificación gramatical
- Sugerencias de mejora
- Disponible en editor de publicaciones

---

## 📊 FUNCIONALIDADES PRINCIPALES

### Para Usuarios (Lectores)

1. **Exploración de Contenido**
   - Búsqueda avanzada por palabras clave
   - Filtrado por categorías
   - Ordenamiento por relevancia/fecha
   - Vista previa de publicaciones

2. **Interacción Social**
   - Sistema de likes
   - Comentarios anidados
   - Guardar para leer después
   - Compartir publicaciones

3. **Perfil Personal**
   - Información básica
   - Foto de perfil
   - Historial de interacciones
   - Credencial digital

### Para Publicadores (Autores)

1. **Gestión de Publicaciones**
   - Editor rico con formato (Quill.js)
   - Subida de imágenes
   - Categorización
   - Borradores automáticos
   - Historial de versiones

2. **Estadísticas**
   - Vistas por publicación
   - Likes recibidos
   - Comentarios
   - Tendencias temporales

3. **Asistente de IA**
   - Generación de resúmenes
   - Formateo profesional
   - Corrección gramatical
   - Sugerencias de mejora

4. **Perfil Profesional**
   - Información académica
   - Especialidad
   - Institución
   - Credencial oficial con firma digital

### Para Administradores

1. **Moderación de Contenido**
   - Panel de publicaciones pendientes
   - Moderación automática basada en reglas
   - Moderación manual
   - Historial de decisiones

2. **Gestión de Usuarios**
   - Aprobación de publicadores
   - Gestión de reportes
   - Bloqueo temporal/permanente
   - Estadísticas de usuarios

3. **Gestión de Categorías**
   - Crear/editar/eliminar categorías
   - Asignación de iconos
   - Ordenamiento

4. **Configuración del Sistema**
   - Correos institucionales permitidos
   - Parámetros de moderación automática
   - Configuración SMTP
   - Mantenimiento de base de datos

---

## 🤖 SISTEMA DE MODERACIÓN AUTOMATIZADA

### Arquitectura del Sistema

```
┌─────────────────────┐
│ Nueva Publicación   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Extracción de Texto │
│ (título + contenido)│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Motor de Moderación │
│ (Reglas Locales)    │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Análisis Automático │
│ - Calidad           │
│ - Relevancia        │
│ - Originalidad      │
│ - Apropiado         │
└──────────┬──────────┘
           │
      ┌────┴────┐
      │         │
  APROBAR   RECHAZAR
      │         │
      ▼         ▼
  Publicar   Notificar
```

### Criterios de Evaluación

**El sistema evalúa automáticamente:**

1. **Calidad del Contenido** (0-100 puntos)
   - Longitud mínima del contenido
   - Presencia de título descriptivo
   - Estructura del documento
   - Completitud de la información

2. **Contenido Apropiado** (Sí/No)
   - Detección de palabras prohibidas
   - Lenguaje profesional
   - Sin contenido ofensivo
   - Relevante al tema científico

3. **Formato y Presentación** (0-100 puntos)
   - Imagen principal presente
   - Categoría asignada
   - Resumen incluido
   - Campos obligatorios completos

### Umbrales de Decisión

```
Puntuación ≥ 70  → APROBADO automáticamente
Puntuación 50-69 → REVISIÓN manual
Puntuación < 50  → RECHAZADO automáticamente
```

### Ventajas del Sistema

- ⚡ **Rapidez:** Moderación instantánea
- 🔒 **Privacidad:** Procesamiento 100% local
- 📊 **Consistencia:** Criterios uniformes y predefinidos
- 💰 **Económico:** Sin costos de servicios externos
- 🎯 **Confiable:** Reglas claras y transparentes

---

## 📈 IMPACTO ESPERADO

### Beneficios Educativos

1. **Acceso Democratizado**
   - Contenido científico gratuito
   - Sin barreras de suscripción
   - Disponible 24/7
   - Multiplataforma

2. **Fomento de la Investigación**
   - Plataforma para publicar hallazgos
   - Retroalimentación de pares
   - Colaboración interdisciplinaria
   - Visibilidad para investigadores noveles

3. **Desarrollo de Habilidades**
   - Redacción científica
   - Pensamiento crítico
   - Alfabetización digital
   - Trabajo colaborativo

### Beneficios Sociales

1. **Divulgación Científica**
   - Acercar la ciencia a la sociedad
   - Combatir desinformación
   - Promover pensamiento crítico
   - Cultura científica

2. **Inclusión**
   - Accesible desde cualquier dispositivo
   - Interfaz intuitiva
   - Contenido en español
   - Sin costos de acceso

### Métricas de Éxito

**Indicadores Cuantitativos:**
- Número de usuarios registrados
- Publicaciones creadas
- Interacciones (likes, comentarios)
- Tiempo promedio en plataforma
- Tasa de retención de usuarios

**Indicadores Cualitativos:**
- Calidad del contenido publicado
- Satisfacción de usuarios
- Impacto en aprendizaje
- Colaboraciones generadas

---

## 🚀 PLAN DE IMPLEMENTACIÓN

### Fase 1: Desarrollo (Completada)

**Duración:** 3 meses

**Entregables:**
- ✅ Arquitectura de base de datos
- ✅ Sistema de autenticación
- ✅ Panel de administración
- ✅ Panel de publicadores
- ✅ Interfaz de usuario
- ✅ Moderación automatizada
- ✅ Sistema de interacciones
- ✅ PWA funcional

### Fase 2: Pruebas (En Curso)

**Duración:** 1 mes

**Actividades:**
- Pruebas de seguridad
- Pruebas de carga
- Pruebas de usabilidad
- Corrección de bugs
- Optimización de rendimiento

### Fase 3: Piloto (Próxima)

**Duración:** 2 meses

**Objetivos:**
- Implementar en institución educativa
- Recopilar feedback de usuarios reales
- Ajustar funcionalidades
- Capacitar administradores

### Fase 4: Escalamiento

**Duración:** Continua

**Estrategia:**
- Expandir a más instituciones
- Agregar nuevas categorías
- Implementar analytics avanzados
- Desarrollar app móvil nativa

---

## 💻 REQUISITOS TÉCNICOS

### Servidor

**Mínimo:**
- CPU: 2 cores
- RAM: 4 GB
- Almacenamiento: 20 GB SSD
- Sistema Operativo: Linux (Ubuntu 20.04+)

**Recomendado:**
- CPU: 4 cores
- RAM: 8 GB
- Almacenamiento: 50 GB SSD
- Sistema Operativo: Linux (Ubuntu 22.04 LTS)

### Software

**Requisitos:**
- PHP 8.0 o superior
- MySQL 8.0 o superior
- Apache 2.4 / Nginx 1.18+
- Servidor SMTP (Gmail, SendGrid, etc.)

### Cliente (Usuario Final)

**Navegadores Soportados:**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

**Dispositivos:**
- Desktop (Windows, macOS, Linux)
- Tablets (iOS, Android)
- Smartphones (iOS, Android)

**Conexión:**
- Mínimo: 2 Mbps
- Recomendado: 5 Mbps

---

## 🔧 INSTALACIÓN Y CONFIGURACIÓN

### 1. Clonar Repositorio

```bash
git clone https://github.com/usuario/lab-explora.git
cd lab-explora
```

### 2. Configurar Base de Datos

```bash
mysql -u root -p < base_db/setup_database.sql
mysql -u root -p lab_explora < base_db/setup_2fa.sql
```

### 3. Configurar Conexión

Editar `forms/conexion.php`:
```php
$host = "localhost";
$usuario = "root";
$password = "tu_password";
$base_datos = "lab_explora";
```

### 4. Configurar Email

Editar `forms/EmailHelper.php`:
```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'tu_email@gmail.com';
$mail->Password = 'tu_app_password';
```

### 5. Configurar Apache

```apache
<VirtualHost *:80>
    ServerName lab-explora.local
    DocumentRoot /var/www/lab-explora
    
    <Directory /var/www/lab-explora>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 6. Permisos

```bash
chmod -R 755 /var/www/lab-explora
chmod -R 777 /var/www/lab-explora/uploads
```

---

## 📚 DOCUMENTACIÓN ADICIONAL

### Archivos Incluidos

1. **README.md** - Guía rápida de inicio
2. **DOCUMENTACION_TECNICA.md** - Documentación técnica completa
3. **INSTALACION_SQL.md** - Guía de instalación de base de datos
4. **BITACORA.md** - Historial de cambios y desarrollo

### Recursos de Aprendizaje

**Para Usuarios:**
- Tour interactivo al iniciar sesión
- Tooltips contextuales
- Centro de ayuda (en desarrollo)

**Para Desarrolladores:**
- Código comentado línea por línea
- Arquitectura documentada
- Diagramas de flujo
- Ejemplos de uso

---

## 🏆 CONCLUSIONES

### Logros Alcanzados

1. ✅ **Plataforma Funcional Completa**
   - Sistema de autenticación robusto
   - Moderación automatizada basada en reglas
   - Interfaz responsive y accesible
   - PWA instalable

2. ✅ **Seguridad de Nivel Profesional**
   - 2FA obligatorio para roles críticos
   - Encriptación de contraseñas
   - Protección contra inyección SQL
   - Auditoría de accesos

3. ✅ **Innovación Tecnológica**
   - Sistema de moderación local automatizado
   - Credenciales digitales verificables
   - Asistente virtual para autores
   - Experiencia de app nativa

4. ✅ **Impacto Social**
   - Democratización del conocimiento
   - Fomento de la investigación
   - Plataforma inclusiva y accesible
   - Código abierto y gratuito

### Desafíos Superados

1. **Sistema de Moderación Automatizada**
   - Solución: Motor de reglas locales configurables
   - Resultado: Moderación rápida, privada y confiable

2. **Seguridad 2FA**
   - Solución: Encriptación bcrypt de códigos
   - Resultado: Sistema robusto y confiable

3. **Responsive Design**
   - Solución: Bootstrap + CSS personalizado
   - Resultado: Experiencia fluida en todos los dispositivos

4. **Rendimiento**
   - Solución: Optimización de consultas SQL
   - Resultado: Carga rápida de páginas

### Trabajo Futuro

**Corto Plazo (3-6 meses):**
- [ ] Implementar sistema de notificaciones push
- [ ] Agregar estadísticas avanzadas
- [ ] Desarrollar API REST
- [ ] Mejorar reglas de moderación automatizada

**Mediano Plazo (6-12 meses):**
- [ ] App móvil nativa (iOS/Android)
- [ ] Sistema de mensajería entre usuarios
- [ ] Integración con redes sociales
- [ ] Gamificación (badges, puntos)

**Largo Plazo (1-2 años):**
- [ ] Expansión internacional
- [ ] Soporte multiidioma
- [ ] Integración con bases de datos científicas
- [ ] Sistema de revisión por pares

### Reflexión Final

Lab-Explora representa más que una plataforma web; es una herramienta para democratizar el acceso al conocimiento científico. Al combinar tecnologías modernas con un enfoque centrado en el usuario, hemos creado un ecosistema que empodera a investigadores, profesionales y estudiantes para compartir y acceder a información de calidad.

La implementación de un sistema de moderación automatizada basado en reglas, junto con medidas de seguridad robustas, garantiza que el contenido publicado sea confiable y relevante. El diseño responsive y accesible asegura que cualquier persona, independientemente de su dispositivo o habilidades técnicas, pueda beneficiarse de la plataforma.

Este proyecto demuestra que es posible crear soluciones tecnológicas sofisticadas que tengan un impacto real en la educación y la divulgación científica, sin comprometer la seguridad, la privacidad o la experiencia del usuario.

---

## 👥 EQUIPO DE DESARROLLO

**Desarrollador Principal:** [Tu Nombre]  
**Institución:** [Nombre de tu Preparatoria]  
**Asesor:** [Nombre del Asesor]  
**Fecha:** Diciembre 2025

---

## 📞 CONTACTO

**Email:** [tu_email@ejemplo.com]  
**GitHub:** [github.com/usuario/lab-explora]  
**Sitio Web:** [lab-explora.local]

---

## 📄 LICENCIA

Este proyecto está licenciado bajo MIT License - ver el archivo LICENSE para más detalles.

---

## 🙏 AGRADECIMIENTOS

- A los profesores y asesores por su guía
- A la comunidad de código abierto por las herramientas utilizadas
- A los beta testers por su valiosa retroalimentación
- A la institución educativa por el apoyo al proyecto

---

**Versión del Informe:** 1.0  
**Última Actualización:** Diciembre 2025  
**Estado:** Prototipo Funcional Completo
