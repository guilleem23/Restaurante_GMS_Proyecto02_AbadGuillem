// =============================================
//   VALIDACIONES PARA CREAR Y EDITAR RESERVAS
// =============================================

// --- VALIDAR NOMBRE CLIENTE ---
function validarNombreCliente() {
    const input = document.getElementById('nombre_cliente');
    const valor = input.value.trim();

    if (valor === '') {
        mostrarError(input, 'El nombre del cliente es obligatorio');
        return false;
    } else if (valor.length < 3) {
        mostrarError(input, 'El nombre debe tener al menos 3 caracteres');
        return false;
    } else {
        mostrarExito(input);
        return true;
    }
}

// --- VALIDAR TELÉFONO ---
function validarTelefono() {
    const input = document.getElementById('telefono');
    const valor = input.value.trim();

    if (valor === '') {
        mostrarError(input, 'El teléfono es obligatorio');
        return false;
    } else if (!/^\d{9}$/.test(valor)) {
        mostrarError(input, 'El teléfono debe tener exactamente 9 dígitos');
        return false;
    } else {
        mostrarExito(input);
        return true;
    }
}

// --- VALIDAR FECHA ---
function validarFecha() {
    const input = document.getElementById('fecha');
    const valor = input.value;

    if (valor === '') {
        mostrarError(input, 'La fecha es obligatoria');
        return false;
    }

    const fechaSeleccionada = new Date(valor);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    if (fechaSeleccionada < hoy) {
        mostrarError(input, 'La fecha no puede ser anterior a hoy');
        return false;
    } else {
        mostrarExito(input);
        return true;
    }
}

// --- VALIDAR HORA INICIO ---
function validarHoraInicio() {
    const input = document.getElementById('hora_inicio');
    const valor = input.value;

    if (valor === '') {
        mostrarError(input, 'La hora de inicio es obligatoria');
        return false;
    } else {
        mostrarExito(input);
        return true;
    }
}

// --- VALIDAR MESA ---
function validarMesa() {
    const select = document.getElementById('id_mesa');
    const valor = select.value;

    if (valor === '' || valor === null) {
        mostrarError(select, 'Debes seleccionar una mesa');
        return false;
    } else {
        mostrarExito(select);
        return true;
    }
}

// --- MOSTRAR ERROR ---
function mostrarError(elemento, mensaje) {
    // Limpiar mensajes anteriores
    limpiarMensaje(elemento);

    // Añadir clase de error
    elemento.style.borderColor = '#e74c3c';
    elemento.style.backgroundColor = '#ffe6e6';

    // Crear mensaje de error
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = 'error-mensaje';
    mensajeDiv.style.color = '#e74c3c';
    mensajeDiv.style.fontSize = '0.9rem';
    mensajeDiv.style.marginTop = '5px';
    mensajeDiv.textContent = mensaje;

    // Insertar después del elemento
    elemento.parentNode.insertBefore(mensajeDiv, elemento.nextSibling);
}

// --- MOSTRAR ÉXITO ---
function mostrarExito(elemento) {
    // Limpiar mensajes anteriores
    limpiarMensaje(elemento);

    // Añadir estilo de éxito
    elemento.style.borderColor = '#27ae60';
    elemento.style.backgroundColor = '#e6ffe6';
}

// --- LIMPIAR MENSAJE ---
function limpiarMensaje(elemento) {
    // Restaurar estilos
    elemento.style.borderColor = '';
    elemento.style.backgroundColor = '';

    // Eliminar mensaje de error si existe
    const siguiente = elemento.nextSibling;
    if (siguiente && siguiente.className === 'error-mensaje') {
        siguiente.remove();
    }
}

// --- ASIGNAR EVENTOS AL CARGAR LA PÁGINA ---
document.addEventListener('DOMContentLoaded', function () {
    // Asignar eventos onblur a cada campo
    const nombreCliente = document.getElementById('nombre_cliente');
    const telefono = document.getElementById('telefono');
    const fecha = document.getElementById('fecha');
    const horaInicio = document.getElementById('hora_inicio');
    const mesa = document.getElementById('id_mesa');

    if (nombreCliente) nombreCliente.addEventListener('blur', validarNombreCliente);
    if (telefono) telefono.addEventListener('blur', validarTelefono);
    if (fecha) fecha.addEventListener('blur', validarFecha);
    if (horaInicio) horaInicio.addEventListener('blur', validarHoraInicio);
    if (mesa) mesa.addEventListener('blur', validarMesa);
});
