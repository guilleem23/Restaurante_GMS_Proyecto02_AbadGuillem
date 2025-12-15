/* /js/reservas.js */

document.addEventListener('DOMContentLoaded', function() {
    const salaSelect = document.getElementById('sala_select');
    
    if (salaSelect) {
        salaSelect.addEventListener('change', function() {
            const idSala = this.value;
            // Obtener la URL base sin parámetros GET antiguos
            const urlBase = window.location.href.split('?')[0];
            
            // Comprobar si estamos en editar para mantener el ID de la reserva
            const urlParams = new URLSearchParams(window.location.search);
            const idReserva = urlParams.get('id');
            
            let nuevaUrl = urlBase;

            if (idReserva) {
                // Si estamos editando, mantenemos el ID de la reserva en la URL y añadimos el filtro de sala
                nuevaUrl += '?id=' + idReserva + '&id_sala_filtro=' + idSala;
            } else {
                // Si estamos creando, solo añadimos el filtro de sala
                nuevaUrl += '?id_sala_filtro=' + idSala;
            }
            
            window.location.href = nuevaUrl;
        });
    }
});