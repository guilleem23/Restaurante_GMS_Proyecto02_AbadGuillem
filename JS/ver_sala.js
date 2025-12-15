// =============================================
//   VER SALA - Mostrar Información de Mesa
// =============================================

function mostrarInfoMesa(elemento) {
    // Obtener datos del elemento
    const mesaId = elemento.dataset.mesaId;
    const mesaNombre = elemento.dataset.mesaNombre;
    const mesaSillas = elemento.dataset.mesaSillas;
    const mesaEstado = parseInt(elemento.dataset.mesaEstado);
    const mesaCamarero = elemento.dataset.mesaCamarero;
    const mesaAsignadoPor = elemento.dataset.mesaAsignadoPor;
    const idCamarero = elemento.dataset.idCamarero;
    const salaId = elemento.dataset.salaId;
    const horaOcupacion = elemento.dataset.mesaHoraOcupacion;

    // Obtener datos de reserva si existen
    const reservaId = elemento.dataset.reservaId;
    const reservaCliente = elemento.dataset.reservaCliente;
    const reservaTelefono = elemento.dataset.reservaTelefono;
    const reservaHoraInicio = elemento.dataset.reservaHoraInicio;
    const reservaHoraFin = elemento.dataset.reservaHoraFin;

    // Determinar estado y color
    const estadoText = mesaEstado === 1 ? 'Libre' : 'Ocupada';
    const estadoColor = mesaEstado === 1 ? '#27ae60' : '#e74c3c';
    const estadoIcon = mesaEstado === 1 ? 'check-circle' : 'utensils';

    // Crear contenido HTML personalizado
    let contenidoHTML = `
        <div style="text-align: left; padding: 10px;">
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-tag"></i> Nombre:</strong> ${mesaNombre}</p>
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-chair"></i> Sillas:</strong> ${mesaSillas}</p>
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-${estadoIcon}"></i> Estado:</strong> 
                <span style="color: ${estadoColor}; font-weight: 600;">${estadoText}</span>
            </p>
    `;

    // Si está ocupada, mostrar información del camarero y hora
    if (mesaEstado === 2 && mesaCamarero) {
        contenidoHTML += `
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-user"></i> Asignada a:</strong> ${mesaCamarero}</p>
        `;

        // Mostrar hora de ocupación si existe
        if (horaOcupacion) {
            const fecha = new Date(horaOcupacion);
            const horaFormateada = fecha.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
            contenidoHTML += `
                <p style="margin: 10px 0;"><strong><i class="fa-solid fa-clock"></i> Ocupada desde:</strong> ${horaFormateada}</p>
            `;
        }
    }

    // Si tiene reserva, mostrar información de la reserva
    if (reservaId && reservaId !== '') {
        const horaInicio = reservaHoraInicio ? reservaHoraInicio.substring(0, 5) : 'N/A';
        const horaFin = reservaHoraFin ? reservaHoraFin.substring(0, 5) : 'N/A';

        contenidoHTML += `
            <hr style="margin: 15px 0; border: none; border-top: 2px solid #f39c12;">
            <p style="margin: 10px 0; color: #f39c12; font-weight: 700; font-size: 1.1em;">
                <i class="fa-solid fa-calendar-check"></i> RESERVA
            </p>
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-user"></i> Cliente:</strong> ${reservaCliente}</p>
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-phone"></i> Teléfono:</strong> ${reservaTelefono}</p>
            <p style="margin: 10px 0;"><strong><i class="fa-solid fa-clock"></i> Horario:</strong> ${horaInicio} - ${horaFin}</p>
        `;
    }

    contenidoHTML += `</div>`;

    // Determinar botones según el estado
    let showCancelButton = true;
    let confirmButtonText = '';
    let confirmButtonColor = '';

    if (mesaEstado === 1) {
        // Mesa libre - opción de asignar (puede tener o no reserva)
        confirmButtonText = '<i class="fa-solid fa-check"></i> Asignar Mesa';
        confirmButtonColor = '#27ae60';
    } else {
        // Mesa ocupada - opción de liberar solo si es el mismo camarero
        if (mesaAsignadoPor == idCamarero) {
            confirmButtonText = '<i class="fa-solid fa-door-open"></i> Liberar Mesa';
            confirmButtonColor = '#e74c3c';
        } else {
            confirmButtonText = 'Cerrar';
            confirmButtonColor = '#6c757d';
            showCancelButton = false;
        }
    }

    // Mostrar SweetAlert con la información
    Swal.fire({
        title: `<i class="fa-solid fa-chair"></i> ${mesaNombre}`,
        html: contenidoHTML,
        icon: 'info',
        showCancelButton: showCancelButton,
        confirmButtonText: confirmButtonText,
        confirmButtonColor: confirmButtonColor,
        cancelButtonText: showCancelButton ? 'Cancelar' : '',
        cancelButtonColor: '#6c757d',
        allowOutsideClick: true,
        customClass: {
            confirmButton: 'swal-btn-custom',
            cancelButton: 'swal-btn-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (mesaEstado === 1) {
                // Llamar a función de asignar (funciona igual con o sin reserva)
                asignarMesa(mesaId, mesaNombre, salaId);
            } else if (mesaAsignadoPor == idCamarero) {
                // Llamar a función de liberar
                liberarMesa(mesaId, mesaNombre, salaId);
            }
        }
    });
}
