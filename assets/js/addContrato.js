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
            existingModal.remove();
        }
    
        // Cargar el contenido de la modal
        const response = await fetch("modales/modalAddContrato.php");
    
        if (!response.ok) {
            throw new Error(`Error al cargar la modal: ${response.status}`);
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
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Datos de contrato recibidos:', data);
        
        if (data.success) {
            // Llenar datos básicos del empleado
            const nombreEmpleadoField = document.getElementById('nombre_empleado');
            const empleadoIdField = document.getElementById('empleado_id');
            
            if (nombreEmpleadoField && empleadoIdField) {
                nombreEmpleadoField.value = data.data.nombre_empleado || '';
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
                
                if (detalleIdField) detalleIdField.value = data.data.detalle_id || '';
                if (contratoIdSelect) contratoIdSelect.value = data.data.contrato_id || '';
                if (fechaInicioField) fechaInicioField.value = data.data.fecha_inicio || '';
                if (fechaFinField) fechaFinField.value = data.data.fecha_fin || '';
                if (salarioField) salarioField.value = data.data.salario || '';
                
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
            throw new Error(data.message || 'Error al cargar los datos del contrato');
        }
    } catch (error) {
        console.error('Error al cargar datos de contrato:', error);
        toastr.error(error.message || 'Error al cargar los datos del contrato');
    }
}

/**
 * Función para guardar o actualizar un contrato
 */
async function guardarContrato() {
    let btnGuardar = null;
    let textoOriginal = 'Guardar Contrato';
    
    try {
        const form = document.getElementById('formularioContrato');
        if (!form) {
            throw new Error('No se encontró el formulario');
        }
        
        const formData = new FormData(form);
        
        // Validar campos obligatorios
        const empleadoId = formData.get('empleado_id');
        const contratoId = formData.get('contrato_id');
        const fechaInicio = formData.get('fecha_inicio');
        const salario = formData.get('salario');
        
        console.log('Datos a enviar:', {
            empleadoId,
            contratoId,
            fechaInicio,
            salario
        });
        
        if (!empleadoId || !contratoId || !fechaInicio || !salario) {
            throw new Error('Por favor complete todos los campos obligatorios');
        }
        
        btnGuardar = document.getElementById('btnGuardarContrato');
        if (!btnGuardar) {
            throw new Error('No se encontró el botón de guardar');
        }
        
        textoOriginal = btnGuardar.textContent;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
        
        const response = await fetch('acciones/registrarContrato.php', {
            method: 'POST',
            body: formData
        });

        // Primero verificar si la respuesta es JSON válido
        const responseText = await response.text();
        let data;
        
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('La respuesta no es JSON válido:', responseText);
            throw new Error(`Error en el servidor: ${responseText.substring(0, 100)}...`);
        }

        console.log('Respuesta del servidor:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Error al procesar el contrato');
        }
        
        toastr.success(data.message);
        
        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('agregarContratoModal'));
        if (modal) {
            modal.hide();
        }
        
        // Refrescar datos
        if (typeof refreshTable === 'function') {
            refreshTable();
        } else if (typeof window.insertEmpleadoTable === 'function') {
            window.insertEmpleadoTable();
        } else {
            location.reload();
        }
        
    } catch (error) {
        console.error('Error al guardar contrato:', error);
        toastr.error(error.message || 'Hubo un error al procesar el contrato');
    } finally {
        if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.textContent = textoOriginal;
        }
    }
}

/**
 * Función para eliminar un contrato
 */
async function eliminarContrato() {
    try {
        const empleadoId = document.getElementById('empleado_id')?.value;
        
        if (!empleadoId) {
            throw new Error('No se puede eliminar el contrato');
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
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();
        console.log('Respuesta del servidor:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Error al eliminar el contrato');
        }
        
        toastr.success(data.message);
        
        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('agregarContratoModal'));
        if (modal) {
            modal.hide();
        }
        
        // Refrescar datos
        if (typeof refreshTable === 'function') {
            refreshTable();
        } else if (typeof window.insertEmpleadoTable === 'function') {
            window.insertEmpleadoTable();
        } else {
            location.reload();
        }
        
    } catch (error) {
        console.error('Error al eliminar contrato:', error);
        toastr.error(error.message || 'Hubo un error al eliminar el contrato');
    }
}

/**
 * Función para refrescar la tabla
 */
function refreshTable() {
    console.log('Refrescando tabla...');
    if (typeof window.insertEmpleadoTable === 'function') {
        window.insertEmpleadoTable();
    } else {
        location.reload();
    }
}

// Exportar funciones al ámbito global
window.modalRegistrarContrato = modalRegistrarContrato;
window.cargarDatosContrato = cargarDatosContrato;
window.guardarContrato = guardarContrato;
window.eliminarContrato = eliminarContrato;
window.refreshTable = refreshTable;