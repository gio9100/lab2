// Sistema de chat en tiempo real para mensajería entre admins y publicadores

// addEventListener() = escucha eventos, DOMContentLoaded = cuando el HTML está cargado
document.addEventListener('DOMContentLoaded', function () {
    // getElementById() = obtiene elemento por su ID
    const contactsList = document.getElementById('contacts-list');
    const chatMessages = document.getElementById('chat-messages');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const chatHeader = document.getElementById('chat-header');
    const chatInputArea = document.getElementById('chat-input-area');
    const searchInput = document.getElementById('search-contact');
    const chatLayout = document.querySelector('.chat-layout');
    const mobileBackBtn = document.getElementById('mobile-back-btn');

    console.log("Chat JS Loaded. Layout:", chatLayout, "BackBtn:", mobileBackBtn);

    // Listener para el botón de volver en móvil
    if (mobileBackBtn) {
        mobileBackBtn.addEventListener('click', () => {
            chatLayout.classList.remove('chat-active');
            currentContact = null; // Opcional: limpiar contacto actual
        });
    }

    // Variables globales del chat
    let currentContact = null;      // Contacto actualmente seleccionado
    let pollInterval = null;        // setInterval() para actualizar mensajes
    let contactsPollInterval = null; // setInterval() para actualizar contactos

    // Cargar contactos al iniciar
    loadContacts();

    // setInterval() = ejecuta función cada X milisegundos
    contactsPollInterval = setInterval(loadContacts, 10000);

    // Buscador de contactos en tiempo real
    searchInput.addEventListener('input', (e) => {
        // toLowerCase() = convierte a minúsculas
        const term = e.target.value.toLowerCase();
        // querySelectorAll() = obtiene todos los elementos que coinciden
        const items = contactsList.querySelectorAll('.contact-item');

        // forEach() = recorre cada elemento del array
        items.forEach(item => {
            const name = item.querySelector('.contact-name').textContent.toLowerCase();
            // includes() = verifica si contiene el texto
            if (name.includes(term)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Enviar mensaje
    messageForm.addEventListener('submit', async (e) => {
        // preventDefault() = evita que el formulario recargue la página
        e.preventDefault();
        // trim() = quita espacios al inicio y final
        const msg = messageInput.value.trim();
        if (!msg || !currentContact) return;

        // try-catch = manejo de errores
        try {
            // URLSearchParams() = maneja parámetros de URL
            const urlParams = new URLSearchParams(window.location.search);
            // get() = obtiene valor de parámetro específico
            const asParam = urlParams.get('as');
            let url = 'api/send_message.php';
            // Agregar parámetro ?as= si existe
            if (asParam) {
                url += `?as=${asParam}`;
            }

            // fetch() = hace petición HTTP al servidor
            // await = espera a que termine la petición
            const response = await fetch(url, {
                method: 'POST',                              // method = tipo de petición (POST para enviar datos)
                headers: { 'Content-Type': 'application/json' },  // headers = encabezados HTTP
                // JSON.stringify() = convierte objeto JS a texto JSON
                body: JSON.stringify({
                    contact_id: currentContact.id,
                    contact_type: currentContact.tipo,
                    message: msg
                })
            });

            // json() = convierte respuesta JSON a objeto JS
            const data = await response.json();

            // Verificar si la operación fue exitosa
            if (data.success) {
                messageInput.value = '';  // Limpiar campo de texto
                loadMessages();           // Recargar mensajes
                loadContacts();           // Actualizar lista de contactos
            } else {
                // console.error() = muestra error en consola del navegador
                console.error('Error:', data.error);
            }
        } catch (error) {
            // catch = captura cualquier error que ocurra en try
            console.error('Error enviando mensaje:', error);
        }
    });

    // Obtener lista de contactos del servidor
    async function loadContacts() {
        try {
            // Obtener parámetro ?as= de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const asParam = urlParams.get('as');
            // Construir URL con o sin parámetro
            const url = asParam ? `api/get_contacts.php?as=${asParam}` : 'api/get_contacts.php';

            // fetch() = petición GET al servidor
            const response = await fetch(url);
            // Convertir respuesta JSON a array de contactos
            const contacts = await response.json();
            // Dibujar contactos en el DOM
            renderContacts(contacts);
        } catch (error) {
            console.error('Error cargando contactos:', error);
        }
    }

    // Dibujar lista de contactos en el DOM
    function renderContacts(contacts) {
        // scrollTop = posición actual del scroll
        const scrollTop = contactsList.scrollTop;

        // innerHTML = contenido HTML interno
        contactsList.innerHTML = '';

        contacts.forEach(contact => {
            // createElement() = crea nuevo elemento HTML
            const div = document.createElement('div');
            const isActive = currentContact && currentContact.id == contact.id && currentContact.tipo == contact.tipo;
            div.className = `contact-item ${isActive ? 'active' : ''}`;
            div.onclick = () => selectContact(contact);

            // new Date() = crea objeto de fecha
            // toLocaleTimeString() = formatea hora según idioma local
            const lastMsgTime = contact.fecha_ultimo_mensaje ?
                new Date(contact.fecha_ultimo_mensaje).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';

            // encodeURIComponent() = codifica texto para URL
            const avatarSrc = contact.avatar ||
                `https://ui-avatars.com/api/?name=${encodeURIComponent(contact.nombre)}&background=random&color=fff`;

            // Preparar badge de rol
            let roleBadge = '';
            if (contact.tipo === 'admin') {
                const roleClass = contact.rol_detalle === 'superadmin' ? 'superadmin' : 'admin';
                const roleText = contact.rol_detalle === 'superadmin' ? 'Super Admin' : 'Admin';
                roleBadge = `<span class="role-badge ${roleClass}">${roleText}</span>`;
            } else {
                roleBadge = `<span class="role-badge publicador">Publicador</span>`;
            }

            // Template strings = `` permiten insertar variables con ${}
            div.innerHTML = `
                <div class="avatar-wrapper">
                    <img src="${avatarSrc}" class="avatar">
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
                        <span class="contact-preview">${contact.ultimo_mensaje || '<i style="opacity:0.6">Sin mensajes</i>'}</span>
                        ${contact.mensajes_no_leidos > 0 ? `<span class="unread-badge">${contact.mensajes_no_leidos}</span>` : ''}
                    </div>
                </div>
            `;

            // appendChild() = agrega elemento como hijo
            contactsList.appendChild(div);
        });

        // Restaurar posición del scroll
        contactsList.scrollTop = scrollTop;
    }

    // Seleccionar contacto para chatear
    function selectContact(contact) {
        currentContact = contact;

        // textContent = texto del elemento
        document.getElementById('header-name').textContent = contact.nombre;
        document.getElementById('header-avatar').src = contact.avatar ||
            `https://ui-avatars.com/api/?name=${encodeURIComponent(contact.nombre)}&background=random&color=fff`;
        document.getElementById('header-status-text').textContent = contact.online ? 'En línea' : 'Desconectado';

        const dot = document.getElementById('header-status-dot');
        dot.className = `status-indicator ${contact.online ? 'online' : ''}`;

        // style.display = controla visibilidad del elemento
        chatHeader.style.display = 'flex';
        chatInputArea.style.display = 'block';

        // Aplicar clase para vista móvil
        if (chatLayout) {
            chatLayout.classList.add('chat-active');
        }

        loadContacts();
        loadMessages();

        // clearInterval() = detiene un setInterval
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(loadMessages, 3000);

        // focus() = pone el cursor en el elemento
        // En móvil a veces es mejor no hacer focus automático para que no salte el teclado
        if (window.innerWidth > 768) {
            messageInput.focus();
        }
    }

    // Obtener mensajes de la conversación
    async function loadMessages() {
        if (!currentContact) return;

        try {
            // Obtener parámetro ?as= de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const asParam = urlParams.get('as');
            // Construir URL con parámetros del contacto
            let url = `api/get_messages.php?contact_id=${currentContact.id}&contact_type=${currentContact.tipo}`;
            // Agregar parámetro ?as= si existe
            if (asParam) {
                url += `&as=${asParam}`;
            }

            // fetch() = petición GET al servidor
            const response = await fetch(url);
            // Convertir respuesta JSON a array de mensajes
            const messages = await response.json();
            // Dibujar mensajes en el chat
            renderMessages(messages);
        } catch (error) {
            console.error('Error cargando mensajes:', error);
        }
    }

    // Dibujar mensajes en el chat
    function renderMessages(messages) {
        // scrollHeight = altura total del contenido
        // clientHeight = altura visible
        const isAtBottom = chatMessages.scrollHeight - chatMessages.scrollTop === chatMessages.clientHeight;

        chatMessages.innerHTML = '';

        if (messages.length === 0) {
            chatMessages.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon"><i class="far fa-comments"></i></div>
                    <p>No hay mensajes aún. ¡Saluda!</p>
                </div>
            `;
            return;
        }

        let lastDate = null;

        messages.forEach(msg => {
            // Verificar si el mensaje es mío
            const isMe = (msg.remitente_id == CURRENT_USER_ID && msg.remitente_tipo == CURRENT_USER_ROLE);

            // toLocaleDateString() = formatea fecha según idioma local
            const msgDate = new Date(msg.fecha_envio).toLocaleDateString();

            // Insertar separador de fecha si cambió el día
            if (msgDate !== lastDate) {
                const dateDiv = document.createElement('div');
                dateDiv.style.textAlign = 'center';
                dateDiv.style.margin = '15px 0';
                dateDiv.style.fontSize = '0.75rem';
                dateDiv.style.color = 'var(--text-secondary)';
                dateDiv.style.opacity = '0.7';
                dateDiv.textContent = msgDate;
                chatMessages.appendChild(dateDiv);
                lastDate = msgDate;
            }

            const div = document.createElement('div');
            div.className = `message ${isMe ? 'sent' : 'received'}`;

            const time = new Date(msg.fecha_envio).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // Icono de visto/enviado
            let checkIcon = '';
            if (isMe) {
                if (msg.leido == 1) {
                    checkIcon = '<i class="fas fa-check-double" style="color: #60a5fa; font-size: 0.7rem;"></i>';
                } else {
                    checkIcon = '<i class="fas fa-check" style="color: rgba(255,255,255,0.5); font-size: 0.7rem;"></i>';
                }
            }

            // Botón de eliminar (solo mis mensajes)
            let deleteButton = '';
            if (isMe) {
                deleteButton = `<button class="btn-delete-msg" onclick="deleteMessage(${msg.id})" title="Eliminar mensaje"><i class="fas fa-trash-alt"></i></button>`;
            }

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

            chatMessages.appendChild(div);
        });

        // Scroll al final para ver últimos mensajes
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // window. = hace la función global para usarla desde HTML
    window.deleteMessage = async function (messageId) {
        // confirm() = muestra diálogo de confirmación
        if (!confirm('¿Estás seguro de que quieres eliminar este mensaje?')) {
            return;
        }

        // try-catch = manejo de errores
        try {
            // Obtener parámetro ?as= de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const asParam = urlParams.get('as');
            let url = 'api/delete_message.php';
            // Agregar parámetro ?as= si existe
            if (asParam) {
                url += `?as=${asParam}`;
            }

            // fetch() = petición POST al servidor para eliminar
            const response = await fetch(url, {
                method: 'POST',                              // POST = enviar datos
                headers: { 'Content-Type': 'application/json' },  // Tipo de contenido JSON
                // JSON.stringify() = convierte objeto a texto JSON
                body: JSON.stringify({ message_id: messageId })
            });

            // Convertir respuesta JSON a objeto JS
            const data = await response.json();

            // Verificar si se eliminó correctamente
            if (data.success) {
                loadMessages();   // Recargar mensajes
                loadContacts();   // Actualizar lista de contactos
            } else {
                // alert() = muestra mensaje de alerta
                alert('Error al eliminar mensaje: ' + (data.error || 'Desconocido'));
            }
        } catch (error) {
            // catch = captura errores de red o código
            console.error('Error eliminando mensaje:', error);
            alert('Error de conexión al eliminar mensaje');
        }
    };
});
