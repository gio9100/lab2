// ====================================================================
// ASISTENTE DE IA PARA EDITOR QUILL - LAB-EXPLORA (MODO GEMINI)
// ====================================================================
// Versión Limpia: Solo funciones Gemini API.

// --- Configuración y Llamadas API ---
const callWriterAI = async (task, text) => {
    try {
        const response = await fetch('../../ollama_ia/api_cognitiva.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ task: task, text: text })
        });
        const data = await response.json();
        if (data.error) throw new Error(data.error);
        return data.result;
    } catch (e) {
        console.error("AI Error:", e);
        mostrarToast('Error de IA: ' + e.message, 'error');
        return null;
    }
};

// ====================================================================
// FUNCIÓN 1: GENERADOR AUTOMÁTICO DE RESÚMENES (VIA GEMINI)
// ====================================================================
async function generarResumenIA() {
    mostrarCargando('resumen');

    const textoCompleto = quill.getText();
    if (textoCompleto.length < 50) {
        ocultarCargando('resumen');
        mostrarToast('⚠️ Escribe un poco más de contenido.', 'warning');
        return;
    }

    const resultado = await callWriterAI('summarize', textoCompleto);
    ocultarCargando('resumen');

    if (resultado) {
        window.resumenGeneradoActual = resultado;
        document.getElementById('resumen-ia-resultado').innerHTML = `
            <div class="ai-result-card">
                <div class="ai-badge-header"><span class="ai-badge">✨ Gemini Resumen</span></div>
                <p class="resultado-texto">${resultado}</p>
                <div class="ai-actions">
                    <button id="btn-usar-resumen" class="btn btn-sm btn-primary"><i class="bi bi-clipboard"></i> Usar</button>
                    <button onclick="generarResumenIA()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Reintentar</button>
                </div>
            </div>`;

        document.getElementById('btn-usar-resumen').addEventListener('click', () => {
            document.getElementById('resumen').value = resultado;
            mostrarToast('✅ Resumen copiado.', 'success');
        });
    }
}

// ====================================================================
// FUNCIÓN 2: VERIFICADOR GRAMATICAL / MEJORA (VIA GEMINI)
// ====================================================================
async function verificarGramaticaIA() {
    mostrarCargando('gramatica');
    const texto = quill.getText();
    if (texto.length < 20) {
        ocultarCargando('gramatica');
        mostrarToast('⚠️ Escribe más contenido.', 'warning');
        return;
    }

    const resultado = await callWriterAI('improve_writing', texto);
    ocultarCargando('gramatica');

    if (resultado) {
        document.getElementById('gramatica-ia-resultado').innerHTML = `
            <div class="ai-result-card">
                <div class="ai-badge-header"><span class="ai-badge">💅 Sugerencia de Estilo</span></div>
                <div class="text-muted small mb-2">Texto mejorado por IA:</div>
                <div class="p-2 bg-white border rounded mb-2" style="max-height: 200px; overflow-y: auto;">
                    ${resultado.replace(/</g, '&lt;').replace(/\n/g, '<br>')}
                </div>
                <div class="ai-actions">
                    <button onclick="navigator.clipboard.writeText(\`${resultado.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`); mostrarToast('Copiado', 'success')" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-clipboard"></i> Copiar
                    </button>
                </div>
            </div>
        `;
    }
}

// ====================================================================
// FUNCIÓN 3: FORMATEAR TEXTO / LIMPIEZA (VIA GEMINI)
// ====================================================================
async function formatearTextoIA() {
    console.log("Iniciando formatearTextoIA..."); // Debug
    mostrarCargando('formato');

    let texto = quill.getText(); // Obtenemos texto plano sucio

    if (texto.trim().length < 10) {
        ocultarCargando('formato');
        mostrarToast('⚠️ Pega primero el texto que quieres arreglar.', 'warning');
        return;
    }

    const resultado = await callWriterAI('format_content', texto);
    ocultarCargando('formato');

    if (resultado) {
        // Limpieza extra: Eliminar bloques de código markdown si la IA los devolvió
        let cleanHtml = resultado
            .replace(/```html/g, '')
            .replace(/```/g, '')
            .trim();

        // Mostrar preview y botón de aplicar
        document.getElementById('formato-ia-resultado').innerHTML = `
            <div class="ai-result-card">
                <div class="ai-badge-header"><span class="ai-badge">✨ Texto Limpio</span></div>
                <div class="alert alert-success py-2 mb-2" style="font-size:0.8rem">
                    <i class="bi bi-check-circle"></i> Estructura lista.
                </div>
                <!-- Pequeño preview (solo texto) -->
                <div class="p-2 border rounded bg-white mb-2 text-muted" style="max-height:80px; overflow:hidden; font-size:0.75rem;">
                    ${cleanHtml.replace(/</g, '&lt;').substring(0, 150)}...
                </div>
                <div class="ai-actions">
                    <button id="btn-aplicar-formato" class="btn btn-sm btn-success w-100">
                        <i class="bi bi-check-lg"></i> Aplicar Formato (Reemplazar)
                    </button>
                </div>
            </div>`;

        // Evento para aplicar el cambio al editor Quill
        setTimeout(() => {
            const btn = document.getElementById('btn-aplicar-formato');
            if (btn) {
                btn.onclick = function () {
                    console.log("Aplicando formato HTML...");
                    // Usamos dangerouslyPasteHTML para interpretar las etiquetas <h2>, <ul>, etc.
                    quill.clipboard.dangerouslyPasteHTML(cleanHtml);
                    mostrarToast('✅ Texto formateado correctamente.', 'success');
                };
            }
        }, 100);
    }
}

// ====================================================================
// HELPERS VISUALES
// ====================================================================

function mostrarCargando(id) {
    const el = document.getElementById(`${id}-ia-resultado`);
    if (el) el.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><div class="small text-muted mt-2">Gemini pensando...</div></div>';
}

function ocultarCargando(id) {
    const el = document.getElementById(`${id}-ia-resultado`);
    if (el) el.innerHTML = '';
}

function mostrarToast(msg, type = 'info') {
    if (window.showToast) window.showToast(msg, type);
    else alert(msg);
}

// Inicialización
document.addEventListener('DOMContentLoaded', function () {
    console.log('Gemini AI Assistant Ready 🚀');
});
