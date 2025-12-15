/* /js/reservas.js */

document.addEventListener('DOMContentLoaded', function () {
    const salaSelect = document.getElementById('sala_select');

    if (salaSelect) {
        salaSelect.addEventListener('change', function () {
            const idSala = this.value;

            // Obtener la URL base sin parámetros GET antiguos
            const urlBase = window.location.href.split('?')[0];

            // Comprobar si estamos en editar para mantener el ID de la reserva
            const urlParams = new URLSearchParams(window.location.search);
            const idReserva = urlParams.get('id');

            // Capturar valores del formulario para preservarlos
            const nombreCliente = document.querySelector('input[name="nombre_cliente"]')?.value || '';
            const telefono = document.querySelector('input[name="telefono"]')?.value || '';
            const fecha = document.querySelector('input[name="fecha"]')?.value || '';
            const horaInicio = document.querySelector('input[name="hora_inicio"]')?.value || '';

            // Construir parámetros GET
            const params = new URLSearchParams();
            params.append('id_sala_filtro', idSala);

            if (idReserva) {
                params.append('id', idReserva);
            }

            // Añadir datos del formulario si existen
            if (nombreCliente) params.append('nombre_cliente', nombreCliente);
            if (telefono) params.append('telefono', telefono);
            if (fecha) params.append('fecha', fecha);
            if (horaInicio) params.append('hora_inicio', horaInicio);

            // Redirigir con todos los parámetros
            window.location.href = urlBase + '?' + params.toString();
        });
    }
});