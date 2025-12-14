/**
 * Herramientas Cognitivas - Lab Explorer
 * Maneja la interacción con Gemini para Simplificar/Resumir/Quiz/Chat
 */

document.addEventListener('DOMContentLoaded', () => {
    const dockContainer = document.getElementById('ai-dock-container');
    if (!dockContainer) return;

    // Elementos del DOM
    const btnSimplify = document.getElementById('btn-simplify');
    const btnSummarize = document.getElementById('btn-summarize');
    const btnTranslate = document.getElementById('btn-translate'); // Dejado por compatibilidad
    const btnQuiz = document.getElementById('btn-quiz');
    const btnChat = document.getElementById('btn-chat');

    const resultDrawer = document.getElementById('ai-result-drawer');
    const resultContent = document.getElementById('ai-drawer-content');
    const resultTitle = document.getElementById('ai-drawer-title');
    const btnCloseDrawer = document.getElementById('btn-close-drawer');

    // Estado local para el chat
    let chatHistory = [];

    // Obtener texto del artículo
    const getArticleText = () => {
        // Intentar encontrar el contenedor principal del artículo
        const contentDiv = document.querySelector('.publication-content')
            || document.querySelector('article')
            || document.querySelector('.entry-content')
            || document.body;

        // Limpiamos un poco el texto para ahorrar tokens
        let text = contentDiv.innerText || contentDiv.textContent;
        return text.replace(/\s+/g, ' ').trim().substring(0, 30000);
    };

    // Función principal para llamar a la API
    const callAI = async (task, title, extraParams = {}) => {
        // UI Especial para Chat (no mostramos loading estándar, sino que inicializamos UI vacía si es primera vez)
        if (task !== 'chat_qa') {
            showDrawer(title, 'Analizando contenido con IA... <div class="spinner-border spinner-border-sm text-primary" role="status"></div>');
        } else {
            // Si es chat, solo abrimos el drawer si no está abierto, la UI se maneja aparte
            if (!resultDrawer.classList.contains('active')) {
                showDrawer(title, '');
                renderChatUI(); // Renderiza la caja de chat vacía
            }
        }

        try {
            const articleContext = getArticleText();

            let payload = {
                task: task,
                text: getArticleText() // Por defecto enviamos el texto del artículo
            };

            // Para chat, el 'text' es la pregunta del usuario, y el 'context' es el artículo
            if (task === 'chat_qa') {
                payload = {
                    task: task,
                    text: extraParams.userQuestion,
                    context: articleContext
                };
            }

            const response = await fetch('ollama_ia/api_cognitiva.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.error) {
                if (task === 'chat_qa') return { error: data.error }; // Chat maneja sus errores
                updateDrawerContent(`<div class="alert alert-danger"><i class="bi bi-exclamation-octagon"></i> ${data.error}</div>`);
            } else if (data.success && data.result) {
                // Manejo según tarea
                if (task === 'quiz') {
                    renderQuiz(data.result);
                } else if (task === 'chat_qa') {
                    return { result: data.result }; // Chat maneja su respuesta
                } else {
                    // Texto plano (Simplificar/Resumir)
                    updateDrawerContent(formatText(data.result));
                }
            }
        } catch (error) {
            console.error('Error:', error);
            if (task === 'chat_qa') return { error: "Error de conexión." };
            updateDrawerContent('<div class="alert alert-warning">Error de conexión con el asistente. Intenta de nuevo más tarde.</div>');
        }
    };

    // --- MÓDULO QUIZ ---
    const renderQuiz = (jsonString) => {
        try {
            // Gemini a veces devuelve markdown ```json ... ```, limpiamos
            const cleanJson = jsonString.replace(/```json/g, '').replace(/```/g, '').trim();
            const questions = JSON.parse(cleanJson);

            if (!Array.isArray(questions)) throw new Error("Formato inválido");

            let html = '<div class="quiz-container">';
            questions.forEach((q, index) => {
                html += `
                <div class="quiz-question" id="q-${index}">
                    <p class="fw-bold mb-2">${index + 1}. ${q.pregunta}</p>
                    <div class="quiz-options">
                        ${q.opciones.map((opt, i) => `
                            <div class="quiz-option" onclick="selectOption(${index}, ${i}, ${q.respuesta_correcta}, this)">
                                ${opt}
                            </div>
                        `).join('')}
                    </div>
                </div>`;
            });
            html += '<button onclick="resetQuiz()" class="btn btn-outline-secondary btn-sm mt-3">Reiniciar Quiz</button>'; // Opcional
            html += '</div>';

            // Inyectamos funciones globales temporales para manejar clicks (hack simple)
            window.selectOption = (qIdx, optIdx, correctIdx, element) => {
                const parent = document.getElementById(`q-${qIdx}`);
                // Deshabilitar clicks en este grupo
                const opts = parent.querySelectorAll('.quiz-option');
                opts.forEach(o => o.style.pointerEvents = 'none');

                if (optIdx === correctIdx) {
                    element.classList.add('correct');
                    // element.innerHTML += ' ✅'; 
                } else {
                    element.classList.add('incorrect');
                    // Mostrar la correcta
                    opts[correctIdx].classList.add('correct');
                }
            };

            window.resetQuiz = () => btnQuiz.click();

            updateDrawerContent(html);

        } catch (e) {
            console.error("Error parseando Quiz:", e);
            updateDrawerContent("No se pudo generar el quiz correctamente. Intenta de nuevo.");
        }
    };

    // --- MÓDULO CHAT ---
    const renderChatUI = () => {
        const html = `
            <div class="chat-container">
                <div id="chat-messages" class="chat-messages">
                    <div class="chat-message ai">
                        Hola, he leído el artículo. Hazme cualquier pregunta sobre él.
                    </div>
                </div>
                <div class="chat-input-area">
                    <input type="text" id="chat-input" class="chat-input" placeholder="Escribe tu pregunta..." autocomplete="off">
                    <button id="btn-send-chat" class="btn btn-primary rounded-circle p-2">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
        `;
        resultContent.innerHTML = html;

        // Setup Listeners internos del chat
        const input = document.getElementById('chat-input');
        const btnSend = document.getElementById('btn-send-chat');
        const msgs = document.getElementById('chat-messages');

        const sendMessage = async () => {
            const text = input.value.trim();
            if (!text) return;

            // 1. Mostrar mensaje Usuario
            appendMessage(text, 'user', msgs);
            input.value = '';
            input.disabled = true; // Prevenir spam

            // 2. Loading AI
            const loadingId = appendMessage('<div class="spinner-border spinner-border-sm"></div>', 'ai', msgs);

            // 3. Llamar API
            const response = await callAI('chat_qa', '💬 Chat con Artículo', { userQuestion: text });

            // 4. Remover loading y mostrar respuesta
            document.getElementById(loadingId).remove();
            input.disabled = false;
            input.focus();

            if (response && response.result) {
                appendMessage(response.result, 'ai', msgs);
            } else {
                appendMessage(response.error || "Error.", 'ai', msgs);
            }
        };

        btnSend.onclick = sendMessage;
        input.onkeypress = (e) => { if (e.key === 'Enter') sendMessage(); };
        input.focus();
    };

    const appendMessage = (text, sender, container) => {
        const div = document.createElement('div');
        div.className = `chat-message ${sender} fade-in`;
        div.innerHTML = text; // Permitimos HTML simple
        div.id = 'msg-' + Date.now();
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
        return div.id;
    };


    // Helpers UI Genéricos
    const showDrawer = (title, content) => {
        resultDrawer.classList.add('active');
        resultTitle.textContent = title;
        if (content) resultContent.innerHTML = `<div class="p-3">${content}</div>`;
        document.body.style.overflow = 'hidden';
    };

    const updateDrawerContent = (html) => {
        resultContent.innerHTML = `<div class="p-3 fade-in">${html}</div>`;
    };

    const closeDrawer = () => {
        resultDrawer.classList.remove('active');
        document.body.style.overflow = '';
    };

    // Convertidor Markdown -> HTML
    const formatText = (text) => {
        let html = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/^\* (.*$)/gim, '<li>$1</li>');
        html = html.replace(/^- (.*$)/gim, '<li>$1</li>');
        html = html.replace(/\n\n/g, '<br><br>');
        return html;
    };

    // Event Listeners Principales
    if (btnSimplify) btnSimplify.addEventListener('click', () => callAI('simplify', '🧸 Explicación Simple'));
    if (btnSummarize) btnSummarize.addEventListener('click', () => callAI('summarize', '📝 Resumen Ejecutivo'));
    if (btnTranslate) btnTranslate.addEventListener('click', () => callAI('translate', '🌽 Traducción Purépecha'));
    if (btnQuiz) btnQuiz.addEventListener('click', () => callAI('quiz', '🎓 Ponme a prueba'));
    if (btnChat) btnChat.addEventListener('click', () => callAI('chat_qa', '💬 Chat con el Artículo'));

    if (btnCloseDrawer) btnCloseDrawer.addEventListener('click', closeDrawer);
    // Cerrar click fuera
    resultDrawer.addEventListener('click', (e) => {
        if (e.target === resultDrawer) closeDrawer();
    });
});
