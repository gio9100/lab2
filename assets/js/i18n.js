// ============================================================
// SISTEMA DE TRADUCCIÓN MULTIIDIOMA (i18n)
// ============================================================
// Sistema ligero para cambiar entre Español e Inglés
// Almacena preferencia en localStorage para persistencia

// Diccionario de traducciones (ES/EN)
const traducciones = {
    // Navegación
    'nav.inicio': { es: 'Inicio', en: 'Home' },
    'nav.explorar': { es: 'Explorar', en: 'Explore' },
    'nav.perfil': { es: 'Perfil', en: 'Profile' },
    'nav.cerrar_sesion': { es: 'Cerrar Sesión', en: 'Logout' },
    'nav.iniciar_sesion': { es: 'Inicia sesión', en: 'Sign in' },
    'nav.crear_cuenta': { es: 'Crear Cuenta', en: 'Sign up' },

    // Botones
    'btn.leer_mas': { es: 'Leer más', en: 'Read more' },
    'btn.descargar': { es: 'Descargar', en: 'Download' },
    'btn.guardar': { es: 'Guardar', en: 'Save' },
    'btn.cancelar': { es: 'Cancelar', en: 'Cancel' },
    'btn.enviar': { es: 'Enviar', en: 'Send' },
    'btn.buscar': { es: 'Buscar', en: 'Search' },

    // Publicaciones
    'pub.titulo': { es: 'Publicaciones', en: 'Publications' },
    'pub.recientes': { es: 'Recientes', en: 'Recent' },
    'pub.populares': { es: 'Populares', en: 'Popular' },
    'pub.categoria': { es: 'Categoría', en: 'Category' },
    'pub.autor': { es: 'Autor', en: 'Author' },
    'pub.fecha': { es: 'Fecha', en: 'Date' },
    'pub.comentarios': { es: 'Comentarios', en: 'Comments' },
    'pub.likes': { es: 'Me gusta', en: 'Likes' },

    // Perfil
    'perfil.mis_datos': { es: 'Mis Datos', en: 'My Data' },
    'perfil.nombre': { es: 'Nombre', en: 'Name' },
    'perfil.correo': { es: 'Correo', en: 'Email' },
    'perfil.foto': { es: 'Foto de Perfil', en: 'Profile Photo' },
    'perfil.badges': { es: 'Insignias', en: 'Badges' },
    'perfil.nivel': { es: 'Nivel', en: 'Level' },
    'perfil.puntos': { es: 'Puntos', en: 'Points' },

    // Badges
    'badges.obtenidas': { es: 'Insignias Obtenidas', en: 'Earned Badges' },
    'badges.disponibles': { es: 'Disponibles', en: 'Available' },
    'badges.bloqueado': { es: 'Bloqueado', en: 'Locked' },
    'badges.nuevo': { es: '¡Nuevo!', en: 'New!' },

    // Footer
    'footer.terminos': { es: 'Términos', en: 'Terms' },
    'footer.privacidad': { es: 'Privacidad', en: 'Privacy' },
    'footer.contacto': { es: 'Contacto', en: 'Contact' },

    // Mensajes
    'msg.cargando': { es: 'Cargando...', en: 'Loading...' },
    'msg.error': { es: 'Error', en: 'Error' },
    'msg.exito': { es: 'Éxito', en: 'Success' },
    'msg.sin_resultados': { es: 'No hay resultados', en: 'No results' }
};

// Variable global del idioma actual
let idiomaActual = 'es'; // Por defecto español

// ============================================================
// Función: Inicializar sistema de idiomas
// ============================================================
function inicializarIdioma() {
    // Obtener idioma guardado en localStorage
    const idiomaGuardado = localStorage.getItem('idioma');

    if (idiomaGuardado && (idiomaGuardado === 'es' || idiomaGuardado === 'en')) {
        idiomaActual = idiomaGuardado; // Usar idioma guardado
    } else {
        idiomaActual = 'es'; // Español por defecto
        localStorage.setItem('idioma', 'es'); // Guardar en localStorage
    }

    aplicarTraducciones(); // Aplicar traducciones al cargar
    actualizarToggleIdioma(); // Actualizar UI del toggle
}

// ============================================================
// Función: Cambiar idioma
// ============================================================
function cambiarIdioma(nuevoIdioma) {
    if (nuevoIdioma !== 'es' && nuevoIdioma !== 'en') {
        console.error('Idioma no soportado:', nuevoIdioma);
        return; // Idioma inválido
    }

    idiomaActual = nuevoIdioma; // Actualizar idioma actual
    localStorage.setItem('idioma', nuevoIdioma); // Guardar en localStorage (persistente)

    aplicarTraducciones(); // Aplicar nuevas traducciones
    actualizarToggleIdioma(); // Actualizar botón de toggle
}

// ============================================================
// Función: Aplicar traducciones al DOM
// ============================================================
function aplicarTraducciones() {
    // Buscar todos los elementos con atributo data-i18n
    const elementos = document.querySelectorAll('[data-i18n]');

    // forEach = recorrer cada elemento
    elementos.forEach(elemento => {
        const clave = elemento.getAttribute('data-i18n'); // Obtener clave de traducción
        const traduccion = obtenerTraduccion(clave); // Buscar traducción

        if (traduccion) {
            // textContent = cambiar el texto del elemento
            elemento.textContent = traduccion;
        }
    });

    // Actualizar placeholders de inputs
    const inputsConPlaceholder = document.querySelectorAll('[data-i18n-placeholder]');
    inputsConPlaceholder.forEach(input => {
        const clave = input.getAttribute('data-i18n-placeholder');
        const traduccion = obtenerTraduccion(clave);

        if (traduccion) {
            input.placeholder = traduccion; // Cambiar placeholder
        }
    });

    // Actualizar títulos (atributo title para tooltips)
    const elementosConTitle = document.querySelectorAll('[data-i18n-title]');
    elementosConTitle.forEach(elemento => {
        const clave = elemento.getAttribute('data-i18n-title');
        const traduccion = obtenerTraduccion(clave);

        if (traduccion) {
            elemento.title = traduccion; // Cambiar title
        }
    });
}

// ============================================================
// Función: Obtener traducción por clave
// ============================================================
function obtenerTraduccion(clave) {
    const entrada = traducciones[clave]; // Buscar en diccionario

    if (!entrada) {
        console.warn('Traducción no encontrada:', clave);
        return clave; // Retornar clave original si no existe
    }

    return entrada[idiomaActual] || entrada['es']; // Retornar traducción o fallback a español
}

// ============================================================
// Función: Actualizar UI del toggle de idioma
// ============================================================
function actualizarToggleIdioma() {
    const toggleBtn = document.getElementById('lang-toggle');

    if (!toggleBtn) return; // Si no existe el botón, salir

    // Actualizar texto del botón
    if (idiomaActual === 'es') {
        toggleBtn.innerHTML = '<i class="bi bi-globe"></i> EN'; // Muestra EN para cambiar a inglés
    } else {
        toggleBtn.innerHTML = '<i class="bi bi-globe"></i> ES'; // Muestra ES para cambiar a español
    }
}

// ============================================================
// Función: Toggle entre idiomas (alternar)
// ============================================================
function toggleIdioma() {
    const nuevoIdioma = idiomaActual === 'es' ? 'en' : 'es'; // Alternar
    cambiarIdioma(nuevoIdioma); // Cambiar al nuevo idioma
}

// ============================================================
// Inicializar al cargar la página
// ============================================================
// DOMContentLoaded = cuando el HTML termina de cargar
document.addEventListener('DOMContentLoaded', function () {
    inicializarIdioma(); // Cargar idioma guardado

    // Agregar evento click al botón de toggle
    const toggleBtn = document.getElementById('lang-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleIdioma); // Click = cambiar idioma
    }
});

// Exportar funciones para uso global
window.i18n = {
    cambiar: cambiarIdioma,
    toggle: toggleIdioma,
    obtener: obtenerTraduccion,
    idioma: () => idiomaActual // Función para obtener idioma actual
};
