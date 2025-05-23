/**
 * Modal para gestión de tipos de contrato
 */
async function modalGestionContratos() {
  try {
    // Ocultar cualquier modal existente
    const existingModal = document.getElementById("gestionContratosModal");
    if (existingModal) {
      const modal = bootstrap.Modal.getInstance(existingModal);
      if (modal) {
        modal.hide();
      }
      existingModal.remove();
    }

    const response = await fetch("modales/modalGestionContrato.php");

    if (!response.ok) {
      throw new Error("Error al cargar la modal");
    }

    const data = await response.text();

    // Crear un elemento div para almacenar el contenido de la modal
    const modalContainer = document.createElement("div");
    modalContainer.innerHTML = data;

    // Agregar la modal al documento actual
    document.body.appendChild(modalContainer);

    // Mostrar la modal
    const myModal = new bootstrap.Modal(
      modalContainer.querySelector("#gestionContratosModal")
    );
    myModal.show();

    // Configurar eventos después de que la modal se cargue
    setTimeout(() => {
      setupModalEvents();
    }, 100);
    
  } catch (error) {
    console.error("Error al cargar modal de gestión de contratos:", error);
    toastr.error("Error al cargar la modal de gestión de contratos");
  }
}

/**
 * Configurar eventos de la modal de gestión de contratos
 */
function setupModalEvents() {
  const formularioTipoContrato = document.getElementById('formularioTipoContrato');
  const modal = document.getElementById('gestionContratosModal');
  
  console.log("Configurando eventos...", formularioTipoContrato, modal);
  
  if (formularioTipoContrato) {
    // Remover todos los event listeners previos
    const newForm = formularioTipoContrato.cloneNode(true);
    formularioTipoContrato.parentNode.replaceChild(newForm, formularioTipoContrato);
    
    // Agregar el event listener al nuevo formulario
    newForm.addEventListener('submit', function(event) {
      event.preventDefault();
      event.stopPropagation();
      console.log("Event listener ejecutado - submit interceptado");
      registrarTipoContrato(event);
      return false;
    });
    
    console.log("Event listener del formulario configurado");
  }

  if (modal) {
    modal.addEventListener('shown.bs.modal', function() {
      console.log("Modal de gestión de contratos mostrado");
      cargarTiposContrato();
      limpiarFormulario();
    });
  }
}

/**
 * Función para manejar click directo del botón (respaldo)
 */
function registrarTipoContratoClick(event) {
  event.preventDefault();
  event.stopPropagation();
  console.log("Función onclick ejecutada");
  registrarTipoContrato(event);
  return false;
}

/**
 * Función para registrar o actualizar un tipo de contrato
 */
async function registrarTipoContrato(event) {
  try {
    event.preventDefault();
    console.log("Función registrarTipoContrato ejecutada");
    
    const formulario = document.getElementById('formularioTipoContrato');
    const formData = new FormData(formulario);
    const contratoId = document.getElementById('contrato_id').value;
    const tipoContrato = document.getElementById('tipo_contrato').value;
    
    console.log("Datos del formulario:", {
      contratoId: contratoId,
      tipoContrato: tipoContrato,
      formData: Object.fromEntries(formData)
    });
    
    // Validar que el campo no esté vacío
    if (!tipoContrato.trim()) {
      toastr.error('El nombre del tipo de contrato es requerido');
      return;
    }
    
    // Determinar la URL según si es crear o actualizar
    const url = contratoId ? 'acciones/updateTipoContrato.php' : 'acciones/addTipoContrato.php';
    
    console.log('Enviando a:', url);
    
    // Mostrar loading
    const btnGuardar = document.getElementById('btnGuardarContrato');
    const textoOriginal = btnGuardar.textContent;
    btnGuardar.disabled = true;
    btnGuardar.textContent = 'Guardando...';
    
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });

    console.log('Respuesta recibida:', response);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    
    console.log('Datos de respuesta:', data);
    
    if (data.success) {
      toastr.success(data.message);
      limpiarFormulario();
      cargarTiposContrato();
    } else {
      toastr.error(data.message || 'Error al procesar la solicitud');
    }
    
  } catch (error) {
    console.error('Error al registrar tipo de contrato:', error);
    toastr.error('Error al procesar la solicitud: ' + error.message);
  } finally {
    // Restaurar botón
    const btnGuardar = document.getElementById('btnGuardarContrato');
    if (btnGuardar) {
      btnGuardar.disabled = false;
      btnGuardar.textContent = document.getElementById('contrato_id').value ? 'Actualizar Tipo de Contrato' : 'Guardar Tipo de Contrato';
    }
  }
}

/**
 * Función para cargar la lista de tipos de contrato
 */
async function cargarTiposContrato() {
  try {
    console.log("Cargando tipos de contrato...");
    
    const response = await fetch('acciones/getTiposContrato.php');
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    console.log("Tipos de contrato cargados:", data);
    
    const listaContratos = document.getElementById('lista_contratos');
    
    if (!listaContratos) {
      console.error("Elemento lista_contratos no encontrado");
      return;
    }
    
    listaContratos.innerHTML = '';
    
    if (data.length === 0) {
      listaContratos.innerHTML = '<tr><td colspan="3" class="text-center">No hay tipos de contrato registrados</td></tr>';
      return;
    }
    
    data.forEach(contrato => {
      const tipoEscaped = contrato.tipo_contrato.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
      const fila = document.createElement('tr');
      fila.innerHTML = `
        <td>${contrato.id}</td>
        <td>${contrato.tipo_contrato}</td>
        <td>
          <button type='button' onclick='editarTipoContrato(${contrato.id}, "${tipoEscaped}")' class='btn btn-warning btn-sm me-1'>
            <i class='bi bi-pencil-square'></i>
          </button>
          <button type='button' onclick='eliminarTipoContrato(${contrato.id})' class='btn btn-danger btn-sm'>
            <i class='bi bi-trash'></i>
          </button>
        </td>
      `;
      listaContratos.appendChild(fila);
    });
    
  } catch (error) {
    console.error('Error al cargar tipos de contrato:', error);
    toastr.error('Error al cargar los tipos de contrato');
  }
}

/**
 * Función para editar un tipo de contrato
 */
function editarTipoContrato(id, tipo) {
  console.log("Editando tipo de contrato:", id, tipo);
  
  const contratoIdField = document.getElementById('contrato_id');
  const tipoContratoField = document.getElementById('tipo_contrato');
  const btnGuardar = document.getElementById('btnGuardarContrato');
  
  if (contratoIdField && tipoContratoField && btnGuardar) {
    contratoIdField.value = id;
    tipoContratoField.value = tipo.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
    btnGuardar.textContent = 'Actualizar Tipo de Contrato';
    
    // Hacer scroll hacia el formulario
    document.getElementById('formularioTipoContrato').scrollIntoView({ behavior: 'smooth' });
  }
}

/**
 * Función para eliminar un tipo de contrato
 */
async function eliminarTipoContrato(id) {
  try {
    console.log("Eliminando tipo de contrato:", id);
    
    if (!confirm('¿Está seguro de eliminar este tipo de contrato?')) {
      return;
    }
    
    const response = await fetch('acciones/deleteTipoContrato.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id: id })
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    
    console.log('Respuesta del servidor:', data);
    
    if (data.success) {
      toastr.success(data.message);
      cargarTiposContrato();
      limpiarFormulario(); // Limpiar el formulario por si estaba editando ese elemento
    } else {
      toastr.error(data.message || 'Error al eliminar el tipo de contrato');
    }
    
  } catch (error) {
    console.error('Error al eliminar tipo de contrato:', error);
    toastr.error('Error al eliminar el tipo de contrato: ' + error.message);
  }
}

/**
 * Función para limpiar el formulario
 */
function limpiarFormulario() {
  const formulario = document.getElementById('formularioTipoContrato');
  const contratoId = document.getElementById('contrato_id');
  const btnGuardar = document.getElementById('btnGuardarContrato');
  
  if (formulario) {
    formulario.reset();
  }
  
  if (contratoId) {
    contratoId.value = '';
  }
  
  if (btnGuardar) {
    btnGuardar.textContent = 'Guardar Tipo de Contrato';
  }
}

// Hacer las funciones disponibles globalmente
window.modalGestionContratos = modalGestionContratos;
window.editarTipoContrato = editarTipoContrato;
window.eliminarTipoContrato = eliminarTipoContrato;
window.cargarTiposContrato = cargarTiposContrato;
window.registrarTipoContrato = registrarTipoContrato;
window.registrarTipoContratoClick = registrarTipoContratoClick;