# Sistema de Mensajería "LabChat" 🧪

Este sistema permite la comunicación en tiempo real entre Administradores, Super Admins y Publicadores.

## Estructura de Archivos

- **chat.php**: Interfaz principal. Detecta automáticamente el rol del usuario (Admin o Publicador).
- **init.php**: Maneja la sesión y seguridad.
- **db.php**: Conexión a la base de datos dedicada.
- **api/**: Endpoints para AJAX.
  - `get_contacts.php`: Obtiene la lista de usuarios según las reglas de visibilidad.
  - `get_messages.php`: Obtiene el historial de chat.
  - `send_message.php`: Envía mensajes.
- **assets/**: Estilos y Scripts.
  - `css/chat.css`: Estilos "Dark Mode" modernos.
  - `js/chat.js`: Lógica del frontend (polling, envío, renderizado).

## Reglas de Visibilidad

1. **Publicadores**: Ven a todos los Admins (y Super Admins).
2. **Admins**: Ven a Publicadores y Super Admins.
3. **Super Admins**: Ven a otros Admins y Publicadores.

## Base de Datos

Se utiliza la tabla `mensajes` en `lab_exp_db`.
El script SQL se encuentra en `../base_db/mensajes.sql`.

## Uso

Simplemente accede a `mensajes/chat.php` desde el navegador estando logueado como Admin o Publicador.
