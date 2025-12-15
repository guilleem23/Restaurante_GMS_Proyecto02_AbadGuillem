// =============================================
//   ALERTS PARA GESTIÓN DE RESERVAS - SweetAlert2
// =============================================

// =================== CONFIRMACIÓN DE ELIMINACIÓN ===================
function confirmarEliminarReserva(event, id, nombreCliente) {
    event.preventDefault();

    Swal.fire({
        title: '¿Eliminar reserva?',
        html: `
            <p>¿Estás seguro de que deseas eliminar la reserva de <strong>"${nombreCliente}"</strong>?</p>
            <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                <i class="fa-solid fa-info-circle"></i> Esta acción no se puede deshacer.
            </p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-trash"></i> Sí, eliminar',
        cancelButtonText: '<i class="fa-solid fa-times"></i> Cancelar',
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando reserva...',
                html: '<i class="fa-solid fa-spinner fa-spin"></i> Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    window.location.href = `../PROCEDIMIENTOS/procesar_eliminar_reserva.php?id=${id}`;
                }
            });
        }
    });
}

// =================== MENSAJES DE ÉXITO/ERROR ===================
document.addEventListener('DOMContentLoaded', function () {
    // Obtener parámetros de URL
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');

    // Mensajes de éxito
    if (success) {
        let titulo = '';
        let mensaje = '';

        switch (success) {
            case 'creado':
                titulo = '¡Reserva creada!';
                mensaje = 'La reserva se ha creado exitosamente.';
                break;
            case 'editado':
                titulo = '¡Cambios guardados!';
                mensaje = 'La reserva se ha actualizado correctamente.';
                break;
            case 'eliminado':
                titulo = '¡Reserva eliminada!';
                mensaje = 'La reserva se ha eliminado exitosamente.';
                break;
            default:
                titulo = '¡Éxito!';
                mensaje = 'Operación realizada correctamente.';
        }

        Swal.fire({
            title: titulo,
            html: `<p>${mensaje}</p>`,
            icon: 'success',
            confirmButtonText: '<i class="fa-solid fa-check"></i> Entendido',
            confirmButtonColor: '#27ae60',
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            // Limpiar URL
            const cleanUrl = window.location.href.split('?')[0];
            window.history.replaceState({}, document.title, cleanUrl);
        });
    }

    // Mensajes de error
    if (error) {
        let titulo = 'Error';
        let mensaje = '';

        switch (error) {
            case 'campos_vacios':
                mensaje = 'Por favor completa todos los campos obligatorios.';
                break;
            case 'nombre_corto':
                mensaje = 'El nombre del cliente debe tener al menos 3 caracteres.';
                break;
            case 'telefono_invalido':
                mensaje = 'El teléfono debe tener exactamente 9 dígitos.';
                break;
            case 'fecha_pasada':
                mensaje = 'La fecha de la reserva no puede ser anterior a hoy.';
                break;
            case 'fecha_hora_pasada':
                mensaje = 'La fecha y hora de la reserva no pueden ser anteriores a la fecha y hora actual.';
                break;
            case 'hora_invalida':
                mensaje = 'La hora especificada no es válida.';
                break;
            case 'mesa_no_existe':
                mensaje = 'La mesa seleccionada no existe.';
                break;
            case 'mesa_ocupada':
                mensaje = 'La mesa ya está ocupada en ese horario (rango de 1h 30m).';
                break;
            case 'db_error':
                mensaje = 'Error de base de datos. Por favor intenta nuevamente.';
                break;
            case 'not_found':
                mensaje = 'Reserva no encontrada.';
                break;
            default:
                mensaje = 'Ha ocurrido un error. Por favor intenta nuevamente.';
        }

        Swal.fire({
            title: titulo,
            html: `<p>${mensaje}</p>`,
            icon: 'error',
            confirmButtonText: '<i class="fa-solid fa-check"></i> Entendido',
            confirmButtonColor: '#e74c3c'
        }).then(() => {
            // Limpiar URL
            const cleanUrl = window.location.href.split('?')[0];
            window.history.replaceState({}, document.title, cleanUrl);
        });
    }
});
