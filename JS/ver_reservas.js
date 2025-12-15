// =============================================
//   VER RESERVAS - Mostrar Información Simple
// =============================================

function mostrarInfoReserva(elemento) {
    // Obtener datos de la reserva
    const reservaId = elemento.dataset.reservaId;
    const reservaCliente = elemento.dataset.reservaCliente;
    const reservaTelefono = elemento.dataset.reservaTelefono;
    const reservaHoraInicio = elemento.dataset.reservaHoraInicio;
    const reservaHoraFin = elemento.dataset.reservaHoraFin;
    const reservaEstado = elemento.dataset.reservaEstado;
    const mesaNombre = elemento.dataset.mesaNombre;
    const mesaSillas = elemento.dataset.mesaSillas;
    const nombreCamarero = elemento.dataset.nombreCamarero;

    // Formatear horas (sin segundos)
    const horaInicio = reservaHoraInicio ? reservaHoraInicio.substring(0, 5) : 'N/A';
    const horaFin = reservaHoraFin ? reservaHoraFin.substring(0, 5) : 'N/A';

    // Color según estado
    const estadoColor = reservaEstado === 'finalizada' ? '#6c757d' : '#28a745';
    const estadoTexto = reservaEstado === 'finalizada' ? 'Finalizada' : 'Activa';
    const estadoIcono = reservaEstado === 'finalizada' ? 'check-circle' : 'calendar-check';

    // Crear contenido HTML
    const contenidoHTML = `
        <div style="text-align: left; padding: 10px;">
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-tag"></i> Estado:</strong> 
                <span style="color: ${estadoColor}; font-weight: 600;">
                    <i class="fa-solid fa-${estadoIcono}"></i> ${estadoTexto}
                </span>
            </p>
            <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-utensils"></i> Mesa:</strong> ${mesaNombre} (${mesaSillas} sillas)</p>
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-user"></i> Cliente:</strong> ${reservaCliente}</p>
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-phone"></i> Teléfono:</strong> ${reservaTelefono}</p>
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-clock"></i> Horario:</strong> ${horaInicio} - ${horaFin} (1h 30min)</p>
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-user-tie"></i> Creada por:</strong> ${nombreCamarero}</p>
        </div>
    `;

    // Mostrar SweetAlert con opción de finalizar si está activa
    const botones = {
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#3085d6',
        showDenyButton: false
    };

    // Si la reserva está activa, añadir botón de finalizar
    if (reservaEstado === 'activa') {
        botones.showDenyButton = true;
        botones.denyButtonText = '<i class="fa-solid fa-check"></i> Finalizar Reserva';
        botones.denyButtonColor = '#dc3545';
    }

    Swal.fire({
        title: `<i class="fa-solid fa-calendar-check"></i> Reserva #${reservaId}`,
        html: contenidoHTML,
        icon: 'info',
        ...botones,
        allowOutsideClick: true,
        customClass: {
            confirmButton: 'swal-btn-custom',
            denyButton: 'swal-btn-custom'
        }
    }).then((result) => {
        if (result.isDenied) {
            // Confirmar finalización
            Swal.fire({
                title: '¿Finalizar reserva?',
                html: `<p>¿Estás seguro de que quieres marcar esta reserva como <strong>finalizada</strong>?</p>
                       <p style="color: #666; font-size: 0.9em;">Esta acción se puede revertir si es necesario.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, Finalizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then((confirmResult) => {
                if (confirmResult.isConfirmed) {
                    finalizarReserva(reservaId);
                }
            });
        }
    });
}

/**
 * Finaliza una reserva marcándola como 'finalizada'
 */
function finalizarReserva(reservaId) {
    // Redirigir a endpoint PHP para finalizar
    window.location.href = `../PROCEDIMIENTOS/finalizar_reserva.php?id=${reservaId}`;
}
