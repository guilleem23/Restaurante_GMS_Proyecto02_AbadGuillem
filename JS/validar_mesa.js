// =============================================
//   VALIDACIONES DE MESAS - JavaScript
//   Validación en tiempo real con onblur
// =============================================

// Validar nombre de mesa
function validarNombreMesa(nombre) {
    nombre = nombre.trim();

    if (nombre === '') {
        return 'El nombre de la mesa es obligatorio.';
    }

    if (nombre.length < 2) {
        return 'El nombre debe tener al menos 2 caracteres.';
    }

    if (nombre.length > 50) {
        return 'El nombre no puede exceder 50 caracteres.';
    }

    const regex = /^[a-zA-Z0-9\s\-]+$/;
    if (!regex.test(nombre)) {
        return 'Solo se permiten letras, números, espacios y guiones.';
    }

    return ''; // Sin errores
}

// Validar número de sillas
function validarSillas(sillas) {
    if (sillas === '') {
        return 'El número de sillas es obligatorio.';
    }

    const numSillas = parseInt(sillas);

    if (isNaN(numSillas)) {
        return 'El número de sillas debe ser numérico.';
    }

    if (numSillas < 1) {
        return 'El número de sillas debe ser al menos 1.';
    }

    if (numSillas > 50) {
        return 'El número de sillas no puede exceder 50.';
    }

    return ''; // Sin errores
}

// Mostrar error en un campo
function mostrarError(campo, mensaje) {
    limpiarError(campo);

    campo.style.borderColor = '#e74c3c';

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '0.85rem';
    errorDiv.style.marginTop = '5px';
    errorDiv.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> ' + mensaje;

    campo.parentNode.appendChild(errorDiv);
}

// Limpiar error de un campo
function limpiarError(campo) {
    campo.style.borderColor = '';

    const errorDiv = campo.parentNode.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Mostrar campo válido
function mostrarValido(campo) {
    limpiarError(campo);
    campo.style.borderColor = '#27ae60';
}

// =================== EVENTOS DE VALIDACIÓN ===================

// Configurar validaciones para crear mesa
function configurarValidacionesCrear() {
    const nombreInput = document.getElementById('nuevo_nombre');
    const sillasInput = document.getElementById('nuevas_sillas');

    if (nombreInput) {
        nombreInput.addEventListener('blur', function () {
            const error = validarNombreMesa(this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        nombreInput.addEventListener('input', function () {
            limpiarError(this);
        });
    }

    if (sillasInput) {
        sillasInput.addEventListener('blur', function () {
            const error = validarSillas(this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        sillasInput.addEventListener('input', function () {
            limpiarError(this);
        });
    }
}

// Configurar validaciones para editar mesa
function configurarValidacionesEditar() {
    const nombreInput = document.getElementById('mesa_nombre');
    const sillasInput = document.getElementById('mesa_sillas');

    if (nombreInput) {
        nombreInput.addEventListener('blur', function () {
            const error = validarNombreMesa(this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        nombreInput.addEventListener('input', function () {
            limpiarError(this);
        });
    }

    if (sillasInput) {
        sillasInput.addEventListener('blur', function () {
            const error = validarSillas(this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        sillasInput.addEventListener('input', function () {
            limpiarError(this);
        });
    }
}

// =================== INICIALIZACIÓN ===================

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        configurarValidacionesCrear();
        configurarValidacionesEditar();
    });
} else {
    configurarValidacionesCrear();
    configurarValidacionesEditar();
}
