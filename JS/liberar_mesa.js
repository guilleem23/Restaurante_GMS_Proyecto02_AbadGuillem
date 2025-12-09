// =============================================
//   LIBERAR MESA - SweetAlert Process
// =============================================

function liberarMesa(mesaId, mesaNombre, salaId) {
    Swal.fire({
        title: '¿Liberar mesa?',
        html: `
            <p>¿Deseas liberar la mesa <strong>${mesaNombre}</strong>?</p>
            <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                <i class="fa-solid fa-info-circle"></i> La mesa quedará disponible para otros camareros.
            </p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-door-open"></i> Sí, liberar',
        cancelButtonText: '<i class="fa-solid fa-times"></i> Cancelar',
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear formulario y enviarlo
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../PROCEDIMIENTOS/liberar_mesa.php';

            const inputMesa = document.createElement('input');
            inputMesa.type = 'hidden';
            inputMesa.name = 'mesa_id';
            inputMesa.value = mesaId;

            form.appendChild(inputMesa);
            document.body.appendChild(form);

            // Mostrar loading
            Swal.fire({
                title: 'Liberando mesa...',
                html: '<i class="fa-solid fa-spinner fa-spin"></i> Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    form.submit();
                }
            });
        }
    });
}
