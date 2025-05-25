/**
 * Modal para gestionar contrato de empleado
 * @param {number} empleadoId - ID del empleado
 * @param {string} nombreEmpleado - Nombre del empleado (opcional)
 */
async function modalRegistrarContrato(empleadoId, nombreEmpleado = null) {
    try {
        console.log('Abriendo modal de contrato para empleado:', empleadoId, nombreEmpleado);
        
        // Ocultar la modal si está abierta
        const existingModal = document.getElementById("agregarContratoModal");
        if (existingModal) {
            const modal = bootstrap.Modal.getInstance(existingModal);
            if (modal) {
                modal.hide();
            }
            existingModal.remove(); // Eliminar la modal existente
        }
    
        // Cargar el contenido de la modal
        const response = await fetch("modales/modalAddContrato.php");
    
        if (!response.ok) {
            throw new Error("Error al cargar la modal");
        }
    
        // Obtener el contenido de la modal
        const data = await response.text();
    
        // Crear un contenedor para la modal
        const modalContainer = document.createElement("div");
        modalContainer.innerHTML = data;
    
        // Agregar la modal al documento
        document.body.appendChild(modalContainer);
    
        // Mostrar la modal
        const myModal = new bootstrap.Modal(
            modalContainer.querySelector("#agregarContratoModal")
        );
        myModal.show();

        // Cargar los datos del contrato después de mostrar la modal
        setTimeout(() => {
            if (empleadoId) {
                cargarDatosContrato(empleadoId);
            }
        }, 300);

    } catch (error) {
        console.error('Error al cargar modal de contrato:', error);
        toastr.error("Error al cargar el formulario de contrato");
    }
}

/**
 * Función para cargar los datos del contrato de un empleado
 */
async function cargarDatosContrato(empleadoId) {
    try {
        console.log('Cargando datos de contrato para empleado:', empleadoId);
        
        const response = await fetch(`acciones/getContratoEmpleado.php?empleado_id=${empleadoId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Datos de contrato recibidos:', data);
        
        if (data.success) {
            // Llenar datos básicos del empleado
            const nombreEmpleadoField = document.getElementById('nombre_empleado');
            const empleadoIdField = document.getElementById('empleado_id');
            
            if (nombreEmpleadoField && empleadoIdField) {
                nombreEmpleadoField.value = data.data.nombre_empleado;
                empleadoIdField.value = data.data.empleado_id || empleadoId;
            }
            
            if (data.has_contract) {
                // Si tiene contrato, llenar todos los campos
                console.log('El empleado tiene contrato, llenando campos...');
                
                const detalleIdField = document.getElementById('detalle_id');
                const contratoIdSelect = document.getElementById('contrato_id_select');
                const fechaInicioField = document.getElementById('fecha_inicio');
                const fechaFinField = document.getElementById('fecha_fin');
                const salarioField = document.getElementById('salario');
                const btnEliminar = document.getElementById('btnEliminarContrato');
                const btnGuardar = document.getElementById('btnGuardarContrato');
                const tituloModal = document.querySelector('.titulo_modal');
                
                if (detalleIdField) detalleIdField.value = data.data.detalle_id;
                if (contratoIdSelect) contratoIdSelect.value = data.data.contrato_id;
                if (fechaInicioField) fechaInicioField.value = data.data.fecha_inicio;
                if (fechaFinField) fechaFinField.value = data.data.fecha_fin || '';
                if (salarioField) salarioField.value = data.data.salario;
                
                // Mostrar botón de eliminar y cambiar textos
                if (btnEliminar) btnEliminar.style.display = 'block';
                if (tituloModal) tituloModal.textContent = `Editar Contrato - ${data.data.nombre_empleado}`;
                if (btnGuardar) btnGuardar.textContent = 'Actualizar Contrato';
                
            } else {
                // Si no tiene contrato, limpiar campos y ocultar botón eliminar
                console.log('El empleado no tiene contrato, configurando para nuevo...');
                
                const detalleIdField = document.getElementById('detalle_id');
                const contratoIdSelect = document.getElementById('contrato_id_select');
                const fechaInicioField = document.getElementById('fecha_inicio');
                const fechaFinField = document.getElementById('fecha_fin');
                const salarioField = document.getElementById('salario');
                const btnEliminar = document.getElementById('btnEliminarContrato');
                const btnGuardar = document.getElementById('btnGuardarContrato');
                const tituloModal = document.querySelector('.titulo_modal');
                
                if (detalleIdField) detalleIdField.value = '';
                if (contratoIdSelect) contratoIdSelect.value = '';
                if (fechaInicioField) fechaInicioField.value = '';
                if (fechaFinField) fechaFinField.value = '';
                if (salarioField) salarioField.value = '';
                
                if (btnEliminar) btnEliminar.style.display = 'none';
                if (tituloModal) tituloModal.textContent = `Asignar Contrato - ${data.data.nombre_empleado}`;
                if (btnGuardar) btnGuardar.textContent = 'Guardar Contrato';
            }
        } else {
            toastr.error(data.message || 'Error al cargar los datos del contrato');
        }
    } catch (error) {
        console.error('Error al cargar datos de contrato:', error);
        toastr.error('Error al cargar los datos del contrato');
    }
}

/**
 * Función para guardar o actualizar un contrato
 */
async function guardarContrato() {
    // Declarar variables al inicio de la función
    let btnGuardar = null;
    let textoOriginal = '';
    
    try {
        const form = document.getElementById('formularioContrato');
        const formData = new FormData(form);
        
        // Validar campos obligatorios
        const empleadoId = document.getElementById('empleado_id').value;
        const contratoId = document.getElementById('contrato_id_select').value;
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const salario = document.getElementById('salario').value;
        
        console.log('Datos a enviar:', {
            empleadoId,
            contratoId,
            fechaInicio,
            salario
        });
        
        if (!empleadoId || !contratoId || !fechaInicio || !salario) {
            toastr.error('Por favor complete todos los campos obligatorios');
            return;
        }
        
        btnGuardar = document.getElementById('btnGuardarContrato');
        textoOriginal = btnGuardar.textContent;
        btnGuardar.disabled = true;
        btnGuardar.textContent = 'Guardando...';
        
        const response = await fetch('acciones/registrarContrato.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Verificar que la respuesta sea JSON válido
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const textResponse = await response.text();
            console.error('Respuesta no es JSON:', textResponse);
            throw new Error('El servidor no devolvió una respuesta JSON válida');
        }

        const data = await response.json();
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            toastr.success(data.message);
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('agregarContratoModal'));
            if (modal) {
                modal.hide();
            }
            // Refrescar tabla
            if (typeof refreshTable === 'function') {
                refreshTable();
            } else if (typeof window.insertEmpleadoTable === 'function') {
                window.insertEmpleadoTable();
            } else {
                location.reload();
            }
        } else {
            toastr.error(data.message || 'Error al procesar el contrato');
        }
        
    } catch (error) {
        console.error('Error al guardar contrato:', error);
        if (error.message.includes('JSON')) {
            toastr.error('Error en la respuesta del servidor. Revise los logs para más detalles.');
        } else {
            toastr.error('Hubo un error al procesar el contrato');
        }
    } finally {
        if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.textContent = textoOriginal || 'Guardar Contrato';
        }
    }
}

/**
 * Función para eliminar un contrato
 */
async function eliminarContrato() {
    try {
        const empleadoId = document.getElementById('empleado_id').value;
        
        if (!empleadoId) {
            toastr.error('No se puede eliminar el contrato');
            return;
        }
        
        if (!confirm('¿Está seguro de que desea eliminar este contrato?')) {
            return;
        }
        
        console.log('Eliminando contrato del empleado:', empleadoId);
        
        const response = await fetch('acciones/deleteContrato.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ empleado_id: parseInt(empleadoId) })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Verificar que la respuesta sea JSON válido
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const textResponse = await response.text();
            console.error('Respuesta no es JSON:', textResponse);
            throw new Error('El servidor no devolvió una respuesta JSON válida');
        }

        const data = await response.json();
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            toastr.success(data.message);
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('agregarContratoModal'));
            if (modal) {
                modal.hide();
            }
            // Refrescar tabla
            if (typeof refreshTable === 'function') {
                refreshTable();
            } else if (typeof window.insertEmpleadoTable === 'function') {
                window.insertEmpleadoTable();
            } else {
                location.reload();
            }
        } else {
            toastr.error(data.message || 'Error al eliminar el contrato');
        }
        
    } catch (error) {
        console.error('Error al eliminar contrato:', error);
        if (error.message.includes('JSON')) {
            toastr.error('Error en la respuesta del servidor. Revise los logs para más detalles.');
        } else {
            toastr.error('Hubo un error al eliminar el contrato');
        }
    }
}

/**
 * Función para refrescar la tabla (puede ser personalizada según tu implementación)
 */
function refreshTable() {
    // Esta función puede ser personalizada según cómo manejes la tabla
    // Por ahora, recargamos la página
    console.log('Refrescando tabla...');
    location.reload();
}

// Hacer las funciones disponibles globalmente
window.modalRegistrarContrato = modalRegistrarContrato;
window.cargarDatosContrato = cargarDatosContrato;
window.guardarContrato = guardarContrato;
window.eliminarContrato = eliminarContrato;
window.refreshTable = refreshTable;