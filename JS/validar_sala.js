// =============================================
//   VALIDACIONES DE SALAS - JavaScript
//   Validación en tiempo real con onblur
// =============================================

// Validar nombre de sala
function validarNombreSala(nombre) {
    nombre = nombre.trim();

    if (nombre === '') {
        return 'El nombre de la sala es obligatorio.';
    }

    if (nombre.length < 2) {
        return 'El nombre debe tener al menos 2 caracteres.';
    }

    if (nombre.length > 100) {
        return 'El nombre no puede exceder 100 caracteres.';
    }

    const regex = /^[a-zA-Z0-9\sáéíóúñÑ\-]+$/;
    if (!regex.test(nombre)) {
        return 'Solo se permiten letras, números, espacios y guiones.';
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

// Configurar validaciones para crear sala
function configurarValidacionesCrear() {
    const nombreInput = document.getElementById('nuevo_nombre');

    if (nombreInput) {
        nombreInput.addEventListener('blur', function () {
            const error = validarNombreSala(this.value);
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
}

// Configurar validaciones para editar sala
function configurarValidacionesEditar() {
    const nombreInput = document.getElementById('sala_nombre');

    if (nombreInput) {
        nombreInput.addEventListener('blur', function () {
            const error = validarNombreSala(this.value);
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
