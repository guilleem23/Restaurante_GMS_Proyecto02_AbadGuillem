// =============================================
//   GESTIÓN DE MESAS - SweetAlert2
// =============================================

// =================== INICIALIZACIÓN ===================
document.addEventListener('DOMContentLoaded', function () {
    // Interceptar formularios para mostrar SweetAlert
    const formCrear = document.getElementById('formCrear');
    const formEditar = document.getElementById('formEditar');

    if (formCrear) {
        formCrear.addEventListener('submit', handleCrearMesa);
    }

    if (formEditar) {
        formEditar.addEventListener('submit', handleEditarMesa);
    }
});

// =================== FUNCIONES DE MODAL ===================

// Función para abrir modal de creación
function abrirModalCrear() {
    document.getElementById('formCrear').reset();
    document.getElementById('modalCrear').style.display = 'flex';
}

// Función para cerrar modal de crear
function cerrarModalCrear() {
    document.getElementById('modalCrear').style.display = 'none';
    document.getElementById('formCrear').reset();
}

// Función para editar mesa
function editarMesa(mesa) {
    document.getElementById('mesa_id').value = mesa.id;
    document.getElementById('mesa_nombre').value = mesa.nombre;
    document.getElementById('mesa_sala').value = mesa.id_sala;
    document.getElementById('mesa_sillas').value = mesa.sillas;

    // Mostrar modal
    document.getElementById('modalEditar').style.display = 'flex';
}

// Función para cerrar modal de editar
function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
    document.getElementById('formEditar').reset();
}

// =================== SWEETALERT2 PARA FORMULARIOS ===================

function handleCrearMesa(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const nombre = formData.get('nombre');
    const sillas = formData.get('sillas');

    Swal.fire({
        title: '¿Crear nueva mesa?',
        html: `<p>¿Estás seguro de que deseas crear la mesa <strong>"${nombre}"</strong> con ${sillas} sillas?</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-check"></i> Sí, crear',
        cancelButtonText: '<i class="fa-solid fa-times"></i> Cancelar',
        confirmButtonColor: '#27ae60',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading y enviar formulario
            Swal.fire({
                title: 'Creando mesa...',
                html: '<i class="fa-solid fa-spinner fa-spin"></i> Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    e.target.submit();
                }
            });
        }
    });
}

function handleEditarMesa(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const nombre = formData.get('nombre');

    Swal.fire({
        title: '¿Guardar cambios?',
        html: `<p>¿Deseas guardar los cambios realizados a la mesa <strong>"${nombre}"</strong>?</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-save"></i> Sí, guardar',
        cancelButtonText: '<i class="fa-solid fa-times"></i> Cancelar',
        confirmButtonColor: '#2c3e50',
        cancelButtonColor: '#6c757d',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading y enviar formulario
            Swal.fire({
                title: 'Guardando cambios...',
                html: '<i class="fa-solid fa-spinner fa-spin"></i> Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    e.target.submit();
                }
            });
        }
    });
}

// =================== SWEETALERT2 PARA ACCIONES DE MESA ===================

// Función para eliminar mesa
function eliminarMesa(id, nombre, estado) {
    // estado: 1=libre, 2=ocupada, 3=reservada
    if (estado == 2) {
        Swal.fire({
            title: 'Mesa Ocupada',
            html: `<p>No se puede eliminar la mesa <strong>"${nombre}"</strong> porque está ocupada actualmente.</p>
                   <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                       <i class="fa-solid fa-info-circle"></i> Primero libera la mesa antes de eliminarla.
                   </p>`,
            icon: 'error',
            confirmButtonText: '<i class="fa-solid fa-check"></i> Entendido',
            confirmButtonColor: '#e74c3c'
        });
        return;
    }

    Swal.fire({
        title: '¿Eliminar mesa?',
        html: `
            <p>¿Estás seguro de que deseas eliminar la mesa <strong>"${nombre}"</strong>?</p>
            <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                <i class="fa-solid fa-exclamation-triangle"></i> Esta acción no se puede deshacer.
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
                title: 'Eliminando mesa...',
                html: '<i class="fa-solid fa-spinner fa-spin"></i> Por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    window.location.href = `../PROCEDIMIENTOS/procesar_eliminar_mesa.php?id=${id}`;
                }
            });
        }
    });
}

// =================== EVENT LISTENERS GENERALES ===================

// Cerrar modal al hacer clic fuera
window.onclick = function (event) {
    const modalCrear = document.getElementById('modalCrear');
    const modalEditar = document.getElementById('modalEditar');

    if (event.target === modalCrear) {
        cerrarModalCrear();
    }
    if (event.target === modalEditar) {
        cerrarModalEditar();
    }
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        cerrarModalCrear();
        cerrarModalEditar();
    }
});
