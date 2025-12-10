// =============================================
//   VALIDACIONES DE USUARIOS - JavaScript
//   Validación en tiempo real con onblur
// =============================================

// Validar username
function validarUsername(username) {
    username = username.trim();

    if (username === '') {
        return 'El username es obligatorio.';
    }

    if (username.length < 3) {
        return 'El username debe tener al menos 3 caracteres.';
    }

    if (username.length > 50) {
        return 'El username no puede exceder 50 caracteres.';
    }

    const regex = /^[a-zA-Z0-9_\-]+$/;
    if (!regex.test(username)) {
        return 'Solo se permiten letras, números, guiones y guión bajo.';
    }

    return '';
}

// Validar nombre
function validarNombre(nombre) {
    nombre = nombre.trim();

    if (nombre === '') {
        return 'El nombre es obligatorio.';
    }

    if (nombre.length < 2) {
        return 'El nombre debe tener al menos 2 caracteres.';
    }

    if (nombre.length > 100) {
        return 'El nombre no puede exceder 100 caracteres.';
    }

    const regex = /^[a-zA-ZáéíóúñÑ\s]+$/;
    if (!regex.test(nombre)) {
        return 'Solo se permiten letras y espacios.';
    }

    return '';
}

// Validar email
function validarEmail(email) {
    email = email.trim();

    if (email === '') {
        return 'El email es obligatorio.';
    }

    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regex.test(email)) {
        return 'El formato del email no es válido.';
    }

    return '';
}

// Validar contraseña
function validarPassword(password) {
    if (password === '') {
        return 'La contraseña es obligatoria.';
    }

    if (password.length < 5) {
        return 'La contraseña debe tener al menos 5 caracteres.';
    }

    return '';
}

// Validar confirmación de contraseña
function validarPasswordConfirm(password, passwordConfirm) {
    if (passwordConfirm === '') {
        return 'Debes confirmar la contraseña.';
    }

    if (password !== passwordConfirm) {
        return 'Las contraseñas no coinciden.';
    }

    return '';
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

// Configurar validaciones para crear usuario
function configurarValidacionesCrear() {
    const usernameInput = document.getElementById('nuevo_username');
    const nombreInput = document.getElementById('nuevo_nombre');
    const emailInput = document.getElementById('nuevo_email');
    const passwordInput = document.getElementById('nuevo_password');
    const passwordConfirmInput = document.getElementById('nuevo_password_confirm');

    if (usernameInput) {
        usernameInput.addEventListener('blur', function () {
            const error = validarUsername(this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        usernameInput.addEventListener('input', function () {
            limpiarError(this);
        });
    }

    if (nombreInput) {
        nombreInput.addEventListener('blur', function () {
            const error = validarNombre(this.value);
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

    if (emailInput) {
        emailInput.addEventListener('blur', function () {
            const error = validarEmail(this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        emailInput.addEventListener('input', function () {
            limpiarError(this);
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('blur', function () {
            const error = validarPassword(this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        passwordInput.addEventListener('input', function () {
            limpiarError(this);
        });
    }

    if (passwordConfirmInput) {
        passwordConfirmInput.addEventListener('blur', function () {
            const password = passwordInput ? passwordInput.value : '';
            const error = validarPasswordConfirm(password, this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        passwordConfirmInput.addEventListener('input', function () {
            limpiarError(this);
        });
    }
}

// Configurar validaciones para editar usuario
function configurarValidacionesEditar() {
    const usernameInput = document.getElementById('usuario_username');
    const nombreInput = document.getElementById('usuario_nombre');
    const emailInput = document.getElementById('usuario_email');

    if (usernameInput) {
        usernameInput.addEventListener('blur', function () {
            const error = validarUsername(this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        usernameInput.addEventListener('input', function () {
            limpiarError(this);
        });
    }

    if (nombreInput) {
        nombreInput.addEventListener('blur', function () {
            const error = validarNombre(this.value);
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

    if (emailInput) {
        emailInput.addEventListener('blur', function () {
            const error = validarEmail(this.value);
            if (error) {
                mostrarError(this, error);
            } else {
                mostrarValido(this);
            }
        });

        emailInput.addEventListener('input', function () {
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
