// ============================================================================
// 🛡️ VALIDACIONES FRONTEND - JAVASCRIPT
// ============================================================================
// Funciones de validación para formularios de registro
// Autor: Lab Explorer
// Fecha: 2025
// ============================================================================

/**
 * ============================================================================
 * VALIDACIÓN DE NOMBRES
 * ============================================================================
 * Valida que el nombre solo contenga letras, espacios, tildes y ñ
 * NO permite números ni caracteres especiales
 */

/**
 * Valida el nombre en tiempo real (mientras el usuario escribe)
 * @param {HTMLInputElement} input - El campo de input del nombre
 */
function validarNombreEnTiempoReal(input) {
    // Removemos números
    input.value = input.value.replace(/[0-9]/g, '');

    // Removemos caracteres especiales (excepto espacios, tildes, ñ, apóstrofes y guiones)
    input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'\-]/g, '');

    // Removemos espacios múltiples
    input.value = input.value.replace(/\s{2,}/g, ' ');
}

/**
 * Valida el nombre completo antes de enviar el formulario
 * @param {string} nombre - El nombre a validar
 * @returns {object} - {valido: boolean, mensaje: string}
 */
function validarNombre(nombre) {
    // Quitamos espacios al inicio y final
    nombre = nombre.trim();

    // Verificar que no esté vacío
    if (nombre === '') {
        return {
            valido: false,
            mensaje: '❌ El nombre no puede estar vacío'
        };
    }

    // Verificar longitud mínima
    if (nombre.length < 3) {
        return {
            valido: false,
            mensaje: '❌ El nombre debe tener al menos 3 caracteres'
        };
    }

    // Verificar longitud máxima
    if (nombre.length > 100) {
        return {
            valido: false,
            mensaje: '❌ El nombre no puede tener más de 100 caracteres'
        };
    }

    // Verificar que NO contenga números
    if (/[0-9]/.test(nombre)) {
        return {
            valido: false,
            mensaje: '❌ El nombre no puede contener números'
        };
    }

    // Verificar que solo contenga letras, espacios, tildes, ñ, apóstrofes y guiones
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s'\-]+$/.test(nombre)) {
        return {
            valido: false,
            mensaje: '❌ El nombre solo puede contener letras, espacios, tildes y guiones'
        };
    }

    // Verificar que no tenga espacios múltiples
    if (/\s{2,}/.test(nombre)) {
        return {
            valido: false,
            mensaje: '❌ El nombre no puede tener espacios múltiples'
        };
    }

    return {
        valido: true,
        mensaje: '✅ Nombre válido'
    };
}

/**
 * ============================================================================
 * VALIDACIÓN DE EMAILS
 * ============================================================================
 */

/**
 * Valida el email en tiempo real
 * @param {HTMLInputElement} input - El campo de input del email
 */
function validarEmailEnTiempoReal(input) {
    // Convertimos a minúsculas
    input.value = input.value.toLowerCase();

    // Removemos espacios
    input.value = input.value.replace(/\s/g, '');
}

/**
 * Valida el formato del email
 * @param {string} email - El email a validar
 * @returns {object} - {valido: boolean, mensaje: string}
 */
function validarEmail(email) {
    // Quitamos espacios
    email = email.trim();

    // Verificar que no esté vacío
    if (email === '') {
        return {
            valido: false,
            mensaje: '❌ El email no puede estar vacío'
        };
    }

    // Expresión regular para validar email
    const regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (!regexEmail.test(email)) {
        return {
            valido: false,
            mensaje: '❌ El formato del email no es válido'
        };
    }

    // Verificar que tenga un dominio válido
    const dominio = email.split('@')[1];
    if (!dominio || dominio.length < 4) {
        return {
            valido: false,
            mensaje: '❌ El dominio del email no es válido'
        };
    }

    return {
        valido: true,
        mensaje: '✅ Email válido'
    };
}

/**
 * ============================================================================
 * VALIDACIÓN DE TELÉFONOS
 * ============================================================================
 */

/**
 * Valida el teléfono en tiempo real
 * @param {HTMLInputElement} input - El campo de input del teléfono
 */
function validarTelefonoEnTiempoReal(input) {
    // Solo permitimos números, espacios, guiones, paréntesis y el símbolo +
    input.value = input.value.replace(/[^\d\s\-\(\)\+]/g, '');

    // Limitamos a 20 caracteres
    if (input.value.length > 20) {
        input.value = input.value.substring(0, 20);
    }
}

/**
 * Formatea el teléfono automáticamente (formato México: (XXX) XXX-XXXX)
 * @param {HTMLInputElement} input - El campo de input del teléfono
 */
function formatearTelefono(input) {
    // Removemos todo excepto números
    let numeros = input.value.replace(/\D/g, '');

    // Formateamos según la longitud
    if (numeros.length <= 3) {
        input.value = numeros;
    } else if (numeros.length <= 6) {
        input.value = `(${numeros.slice(0, 3)}) ${numeros.slice(3)}`;
    } else if (numeros.length <= 10) {
        input.value = `(${numeros.slice(0, 3)}) ${numeros.slice(3, 6)}-${numeros.slice(6)}`;
    } else {
        // Limitamos a 10 dígitos
        numeros = numeros.slice(0, 10);
        input.value = `(${numeros.slice(0, 3)}) ${numeros.slice(3, 6)}-${numeros.slice(6)}`;
    }
}

/**
 * Valida el teléfono completo
 * @param {string} telefono - El teléfono a validar
 * @param {boolean} requerido - Si el teléfono es obligatorio
 * @returns {object} - {valido: boolean, mensaje: string}
 */
function validarTelefono(telefono, requerido = false) {
    // Quitamos espacios
    telefono = telefono.trim();

    // Si no es requerido y está vacío, es válido
    if (!requerido && telefono === '') {
        return {
            valido: true,
            mensaje: ''
        };
    }

    // Si es requerido y está vacío, error
    if (requerido && telefono === '') {
        return {
            valido: false,
            mensaje: '❌ El teléfono es obligatorio'
        };
    }

    // Extraemos solo los dígitos
    const soloDigitos = telefono.replace(/\D/g, '');

    // Verificamos longitud (10 dígitos para México)
    if (soloDigitos.length < 10) {
        return {
            valido: false,
            mensaje: '❌ El teléfono debe tener 10 dígitos'
        };
    }

    if (soloDigitos.length > 10) {
        return {
            valido: false,
            mensaje: '❌ El teléfono no puede tener más de 10 dígitos'
        };
    }

    return {
        valido: true,
        mensaje: '✅ Teléfono válido'
    };
}

/**
 * ============================================================================
 * VALIDACIÓN DE CONTRASEÑAS
 * ============================================================================
 */

/**
 * Muestra/oculta la contraseña
 * @param {string} inputId - ID del campo de contraseña
 * @param {HTMLElement} boton - El botón de mostrar/ocultar
 */
function togglePassword(inputId, boton) {
    const input = document.getElementById(inputId);
    const icono = boton.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icono.classList.remove('bi-eye');
        icono.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icono.classList.remove('bi-eye-slash');
        icono.classList.add('bi-eye');
    }
}

/**
 * Valida la contraseña
 * @param {string} password - La contraseña a validar
 * @param {number} minLength - Longitud mínima
 * @returns {object} - {valido: boolean, mensaje: string}
 */
function validarPassword(password, minLength = 6) {
    // Verificar que no esté vacía
    if (password === '') {
        return {
            valido: false,
            mensaje: '❌ La contraseña no puede estar vacía'
        };
    }

    // Verificar longitud mínima
    if (password.length < minLength) {
        return {
            valido: false,
            mensaje: `❌ La contraseña debe tener al menos ${minLength} caracteres`
        };
    }

    // Verificar que no sea solo espacios
    if (password.trim() === '') {
        return {
            valido: false,
            mensaje: '❌ La contraseña no puede ser solo espacios'
        };
    }

    return {
        valido: true,
        mensaje: '✅ Contraseña válida'
    };
}

/**
 * Verifica que las contraseñas coincidan
 * @param {string} password - Contraseña
 * @param {string} confirmPassword - Confirmación de contraseña
 * @returns {object} - {valido: boolean, mensaje: string}
 */
function validarPasswordsCoinciden(password, confirmPassword) {
    if (password !== confirmPassword) {
        return {
            valido: false,
            mensaje: '❌ Las contraseñas no coinciden'
        };
    }

    return {
        valido: true,
        mensaje: '✅ Las contraseñas coinciden'
    };
}

/**
 * ============================================================================
 * FUNCIONES AUXILIARES
 * ============================================================================
 */

/**
 * Muestra un mensaje de error en un elemento
 * @param {string} elementId - ID del elemento donde mostrar el error
 * @param {string} mensaje - Mensaje a mostrar
 */
function mostrarError(elementId, mensaje) {
    const elemento = document.getElementById(elementId);
    if (elemento) {
        elemento.textContent = mensaje;
        elemento.style.display = 'block';
        elemento.className = 'mensaje-error';
    }
}

/**
 * Muestra un mensaje de éxito en un elemento
 * @param {string} elementId - ID del elemento donde mostrar el éxito
 * @param {string} mensaje - Mensaje a mostrar
 */
function mostrarExito(elementId, mensaje) {
    const elemento = document.getElementById(elementId);
    if (elemento) {
        elemento.textContent = mensaje;
        elemento.style.display = 'block';
        elemento.className = 'mensaje-exito';
    }
}

/**
 * Limpia un mensaje
 * @param {string} elementId - ID del elemento a limpiar
 */
function limpiarMensaje(elementId) {
    const elemento = document.getElementById(elementId);
    if (elemento) {
        elemento.textContent = '';
        elemento.style.display = 'none';
    }
}

/**
 * Capitaliza la primera letra de cada palabra
 * @param {string} texto - Texto a capitalizar
 * @returns {string} - Texto capitalizado
 */
function capitalizarNombre(texto) {
    return texto
        .toLowerCase()
        .split(' ')
        .map(palabra => palabra.charAt(0).toUpperCase() + palabra.slice(1))
        .join(' ');
}
