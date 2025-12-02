// Esperamos a que todo el contenido HTML se cargue antes de ejecutar el script para evitar errores de elementos no encontrados
document.addEventListener('DOMContentLoaded', function () {
    // Referencia a la lista donde mostraremos los contactos en la barra lateral
    const contactsList = document.getElementById('contacts-list');
    // Referencia al contenedor principal donde aparecerán los mensajes del chat
    const chatMessages = document.getElementById('chat-messages');
    // Referencia al formulario para poder detectar cuando el usuario intenta enviar un mensaje
    const messageForm = document.getElementById('message-form');
    // Referencia al campo de texto donde el usuario escribe su mensaje
    const messageInput = document.getElementById('message-input');
    // Referencia al encabezado del chat para mostrar el nombre y estado del contacto seleccionado
    const chatHeader = document.getElementById('chat-header');
    // Referencia al área inferior de input, para ocultarla si no hay contacto seleccionado
    const chatInputArea = document.getElementById('chat-input-area');
    // Referencia al buscador para filtrar la lista de contactos en tiempo real
    const searchInput = document.getElementById('search-contact');

    // Variable para guardar la información del contacto con el que estamos hablando actualmente
    let currentContact = null;
    // Variable para guardar el intervalo de actualización automática de mensajes (polling)
    let pollInterval = null;
    // Variable para guardar el intervalo de actualización automática de la lista de contactos
    let contactsPollInterval = null;

    // Llamamos a esta función inmediatamente para mostrar la lista de contactos al iniciar
    loadContacts();

    // Configuramos una actualización automática cada 10 segundos para ver si hay nuevos contactos o cambios de estado
    contactsPollInterval = setInterval(loadContacts, 10000);

    // Escuchamos lo que el usuario escribe en el buscador de contactos
    searchInput.addEventListener('input', (e) => {
        // Convertimos el texto a minúsculas para que la búsqueda no distinga mayúsculas
        const term = e.target.value.toLowerCase();
        // Obtenemos todos los elementos de contacto que ya están renderizados en la lista
        const items = contactsList.querySelectorAll('.contact-item');
        // Recorremos cada contacto para ver si coincide con la búsqueda
        items.forEach(item => {
            // Obtenemos el nombre del contacto dentro del elemento
            const name = item.querySelector('.contact-name').textContent.toLowerCase();
            // Si el nombre contiene lo que escribió el usuario, lo mostramos
            if (name.includes(term)) {
                item.style.display = 'flex';
                // Si no coincide, lo ocultamos
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Manejamos el evento de envío del formulario (cuando presionan Enter o el botón enviar)
    messageForm.addEventListener('submit', async (e) => {
        // Evitamos que la página se recargue, que es el comportamiento por defecto de los formularios
        e.preventDefault();
        // Obtenemos el texto del mensaje y le quitamos los espacios vacíos al inicio y final
        const msg = messageInput.value.trim();
        // Si el mensaje está vacío o no hemos seleccionado un contacto, no hacemos nada
        if (!msg || !currentContact) return;

        try {
            // Obtenemos los parámetros de la URL actual para ver si estamos actuando como admin o publicador
            const urlParams = new URLSearchParams(window.location.search);
            // Buscamos específicamente el parámetro 'as' (ej: ?as=admin)
            const asParam = urlParams.get('as');
            // Definimos la URL base para enviar el mensaje
            let url = 'api/send_message.php';
            // Si existe el parámetro 'as', lo agregamos a la URL para que el backend sepa quién envía
            if (asParam) {
                url += `?as=${asParam}`;
            }

            // Hacemos la petición POST al servidor para guardar el mensaje
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }, // Indicamos que enviamos datos JSON
                body: JSON.stringify({
                    contact_id: currentContact.id, // ID del destinatario
                    contact_type: currentContact.tipo, // Tipo de usuario destinatario (admin/publicador)
                    message: msg // El contenido del mensaje
                })
            });
            // Esperamos la respuesta del servidor y la convertimos a objeto JSON
            const data = await response.json();
            // Si el servidor nos dice que todo salió bien
            if (data.success) {
                // Limpiamos el campo de texto para que el usuario pueda escribir otro mensaje
                messageInput.value = '';
                // Recargamos los mensajes para que aparezca el nuevo que acabamos de enviar
                loadMessages();
                // Actualizamos la lista de contactos para que este suba al inicio por ser el más reciente
                loadContacts();
            } else {
                // Si hubo error, lo mostramos en la consola para depurar
                console.error('Error:', data.error);
            }
        } catch (error) {
            // Capturamos cualquier error de red o código y lo mostramos
            console.error('Error enviando mensaje:', error);
        }
    });

    // Función asíncrona para obtener la lista de contactos del servidor
    async function loadContacts() {
        try {
            // Necesitamos mantener el parámetro ?as= para que el backend sepa qué lista de contactos devolver
            const urlParams = new URLSearchParams(window.location.search);
            const asParam = urlParams.get('as');
            // Construimos la URL correcta dependiendo de si existe el parámetro 'as'
            const url = asParam ? `api/get_contacts.php?as=${asParam}` : 'api/get_contacts.php';

            // Hacemos la petición al servidor
            const response = await fetch(url);
            // Convertimos la respuesta a un array de contactos
            const contacts = await response.json();
            // Llamamos a la función que se encarga de dibujar estos contactos en el HTML
            renderContacts(contacts);
        } catch (error) {
            console.error('Error cargando contactos:', error);
        }
    }

    // Función para dibujar la lista de contactos en el DOM
    function renderContacts(contacts) {
        // Guardamos la posición actual del scroll para que no salte al recargar la lista
        const scrollTop = contactsList.scrollTop;

        // Limpiamos la lista actual para reconstruirla desde cero
        contactsList.innerHTML = '';
        // Recorremos cada contacto recibido del servidor
        contacts.forEach(contact => {
            // Creamos un elemento div para el contacto
            const div = document.createElement('div');
            // Verificamos si este contacto es el que tenemos seleccionado actualmente
            const isActive = currentContact && currentContact.id == contact.id && currentContact.tipo == contact.tipo;
            // Asignamos las clases CSS, añadiendo 'active' si está seleccionado para resaltarlo
            div.className = `contact-item ${isActive ? 'active' : ''}`;
            // Al hacer click en este contacto, ejecutamos la función selectContact
            div.onclick = () => selectContact(contact);

            // Formateamos la hora del último mensaje si existe, para mostrarla cortita (ej: 14:30)
            const lastMsgTime = contact.fecha_ultimo_mensaje ? new Date(contact.fecha_ultimo_mensaje).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';

            // Si el contacto tiene avatar lo usamos, si no, generamos uno con sus iniciales usando un servicio externo
            const avatarSrc = contact.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(contact.nombre)}&background=random&color=fff`;

            // Preparamos la etiqueta (badge) que indica el rol del usuario
            let roleBadge = '';
            if (contact.tipo === 'admin') {
                // Distinguimos visualmente entre admin normal y superadmin
                const roleClass = contact.rol_detalle === 'superadmin' ? 'superadmin' : 'admin';
                const roleText = contact.rol_detalle === 'superadmin' ? 'Super Admin' : 'Admin';
                roleBadge = `<span class="role-badge ${roleClass}">${roleText}</span>`;
            } else {
                // Etiqueta simple para publicadores
                roleBadge = `<span class="role-badge publicador">Publicador</span>`;
            }

            // Insertamos todo el HTML interno del contacto usando template strings
            div.innerHTML = `
                <div class="avatar-wrapper">
                    <img src="${avatarSrc}" class="avatar">
                    <!-- Indicador verde si el usuario está conectado -->
                    <span class="status-indicator ${contact.online ? 'online' : ''}"></span>
                </div>
                <div class="contact-info">
                    <div class="contact-top">
                        <div style="display:flex; align-items:center;">
                            <span class="contact-name">${contact.nombre}</span>
                            ${roleBadge}
                        </div>
                        <span class="contact-time">${lastMsgTime}</span>
                    </div>
                    <div class="contact-bottom" style="display:flex; justify-content:space-between; align-items:center;">
                        <!-- Mostramos el último mensaje o un texto por defecto, truncado por CSS -->
                        <span class="contact-preview">${contact.ultimo_mensaje || '<i style="opacity:0.6">Sin mensajes</i>'}</span>
                        <!-- Si hay mensajes no leídos, mostramos el contador rojo -->
                        ${contact.mensajes_no_leidos > 0 ? `<span class="unread-badge">${contact.mensajes_no_leidos}</span>` : ''}
                    </div>
                </div>
            `;
            // Agregamos este contacto a la lista en el DOM
            contactsList.appendChild(div);
        });

        // Restauramos la posición del scroll donde estaba antes de actualizar
        contactsList.scrollTop = scrollTop;
    }

    // Función que se ejecuta al hacer click en un contacto
    function selectContact(contact) {
        // Guardamos el contacto seleccionado en la variable global
        currentContact = contact;

        // Actualizamos el encabezado del chat con los datos de este contacto
        document.getElementById('header-name').textContent = contact.nombre;
        document.getElementById('header-avatar').src = contact.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(contact.nombre)}&background=random&color=fff`;
        document.getElementById('header-status-text').textContent = contact.online ? 'En línea' : 'Desconectado';

        // Actualizamos el punto de color de estado en el encabezado
        const dot = document.getElementById('header-status-dot');
        dot.className = `status-indicator ${contact.online ? 'online' : ''}`;

        // Mostramos el área de chat y el input que podrían estar ocultos
        chatHeader.style.display = 'flex';
        chatInputArea.style.display = 'block';

        // Recargamos la lista de contactos para resaltar visualmente al seleccionado (clase .active)
        loadContacts();

        // Cargamos los mensajes de esta conversación
        loadMessages();

        // Si ya había un intervalo de actualización de mensajes corriendo, lo detenemos
        if (pollInterval) clearInterval(pollInterval);
        // Iniciamos un nuevo intervalo para buscar mensajes nuevos cada 3 segundos
        pollInterval = setInterval(loadMessages, 3000);

        // Ponemos el foco en el campo de texto para escribir rápido
        messageInput.focus();
    }

    // Función para obtener los mensajes de la conversación actual
    async function loadMessages() {
        // Si no hay contacto seleccionado, no hacemos nada
        if (!currentContact) return;

        try {
            // Preparamos los parámetros de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const asParam = urlParams.get('as');
            // Construimos la URL pidiendo los mensajes del contacto seleccionado
            let url = `api/get_messages.php?contact_id=${currentContact.id}&contact_type=${currentContact.tipo}`;
            // No olvidamos pasar el rol actual si existe
            if (asParam) {
                url += `&as=${asParam}`;
            }
            // Hacemos la petición
            const response = await fetch(url);
            const messages = await response.json();
            // Dibujamos los mensajes
            renderMessages(messages);
        } catch (error) {
            console.error('Error cargando mensajes:', error);
        }
    }

    // Función para dibujar los mensajes en el área de chat
    function renderMessages(messages) {
        // Calculamos si el usuario está viendo lo último del chat (scrolleado hasta abajo)
        // Esto sirve para decidir si hacemos scroll automático al llegar mensajes nuevos
        const isAtBottom = chatMessages.scrollHeight - chatMessages.scrollTop === chatMessages.clientHeight;

        // Limpiamos el área de mensajes
        chatMessages.innerHTML = '';

        // Si no hay mensajes, mostramos un estado vacío amigable
        if (messages.length === 0) {
            chatMessages.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon"><i class="far fa-comments"></i></div>
                    <p>No hay mensajes aún. ¡Saluda!</p>
                </div>
            `;
            return;
        }

        // Variable para controlar cuándo mostrar separadores de fecha
        let lastDate = null;

        // Recorremos cada mensaje
        messages.forEach(msg => {
            // Determinamos si el mensaje es mío comparando IDs y roles
            // CURRENT_USER_ID y CURRENT_USER_ROLE deben estar definidos globalmente en el PHP principal
            const isMe = (msg.remitente_id == CURRENT_USER_ID && msg.remitente_tipo == CURRENT_USER_ROLE);

            // Formateamos la fecha del mensaje
            const msgDate = new Date(msg.fecha_envio).toLocaleDateString();
            // Si la fecha es diferente a la del mensaje anterior, insertamos un separador
            if (msgDate !== lastDate) {
                const dateDiv = document.createElement('div');
                dateDiv.style.textAlign = 'center';
                dateDiv.style.margin = '15px 0';
                dateDiv.style.fontSize = '0.75rem';
                dateDiv.style.color = 'var(--text-secondary)';
                dateDiv.style.opacity = '0.7';
                dateDiv.textContent = msgDate;
                chatMessages.appendChild(dateDiv);
                lastDate = msgDate; // Actualizamos la última fecha vista
            }

            // Creamos el contenedor del mensaje
            const div = document.createElement('div');
            // Asignamos clase 'sent' (derecha) si es mío o 'received' (izquierda) si es del otro
            div.className = `message ${isMe ? 'sent' : 'received'}`;

            // Formateamos la hora
            const time = new Date(msg.fecha_envio).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // Preparamos el icono de "visto" (doble check)
            let checkIcon = '';
            if (isMe) {
                if (msg.leido == 1) {
                    // Azul si ya lo leyeron
                    checkIcon = '<i class="fas fa-check-double" style="color: #60a5fa; font-size: 0.7rem;"></i>';
                } else {
                    // Gris si solo fue enviado
                    checkIcon = '<i class="fas fa-check" style="color: rgba(255,255,255,0.5); font-size: 0.7rem;"></i>';
                }
            }

            // Botón de eliminar (solo permitimos borrar nuestros propios mensajes)
            let deleteButton = '';
            if (isMe) {
                deleteButton = `<button class="btn-delete-msg" onclick="deleteMessage(${msg.id})" title="Eliminar mensaje"><i class="fas fa-trash-alt"></i></button>`;
            }

            // Insertamos el HTML del mensaje
            div.innerHTML = `
                <div class="message-bubble">
                    ${msg.mensaje}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                        <span class="message-time">${time}</span>
                        ${checkIcon}
                    </div>
                </div>
                ${deleteButton}
            `;
            // Agregamos el mensaje al chat
            chatMessages.appendChild(div);
        });

        // Por ahora hacemos scroll al final siempre para ver lo último
        // (Idealmente solo deberíamos hacerlo si isAtBottom era true o si es la primera carga)
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Hacemos esta función global (window.) para poder llamarla desde el onclick del HTML generado
    window.deleteMessage = async function (messageId) {
        // Pedimos confirmación antes de borrar
        if (!confirm('¿Estás seguro de que quieres eliminar este mensaje?')) {
            return;
        }

        try {
            // Preparamos la URL con el parámetro de rol
            const urlParams = new URLSearchParams(window.location.search);
            const asParam = urlParams.get('as');
            let url = 'api/delete_message.php';
            if (asParam) {
                url += `?as=${asParam}`;
            }

            // Enviamos la petición de borrado al servidor
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message_id: messageId }) // Enviamos el ID del mensaje a borrar
            });

            const data = await response.json();

            if (data.success) {
                // Si se borró bien, recargamos los mensajes para que desaparezca
                loadMessages();
                // Y actualizamos contactos (por si cambiara el "último mensaje" de la lista)
                loadContacts();
            } else {
                alert('Error al eliminar mensaje: ' + (data.error || 'Desconocido'));
            }
        } catch (error) {
            console.error('Error eliminando mensaje:', error);
            alert('Error de conexión al eliminar mensaje');
        }
    };
});
