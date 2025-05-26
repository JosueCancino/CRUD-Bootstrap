/**
 * Modal para agregar un nuevo empleado
 */
async function modalRegistrarEmpleado() {
  try {
    // Ocultar la modal si está abierta
    const existingModal = document.getElementById("detalleEmpleadoModal");
    if (existingModal) {
      const modal = bootstrap.Modal.getInstance(existingModal);
      if (modal) {
        modal.hide();
      }
      existingModal.remove(); // Eliminar la modal existente
    }

    const response = await fetch("modales/modalAdd.php");

    if (!response.ok) {
      throw new Error("Error al cargar la modal");
    }

    // response.text() es un método en programación que se utiliza para obtener el contenido de texto de una respuesta HTTP
    const data = await response.text();

    // Crear un elemento div para almacenar el contenido de la modal
    const modalContainer = document.createElement("div");
    modalContainer.innerHTML = data;

    // Agregar la modal al documento actual
    document.body.appendChild(modalContainer);

    // Mostrar la modal
    const myModal = new bootstrap.Modal(
      modalContainer.querySelector("#agregarEmpleadoModal")
    );
    myModal.show();
  } catch (error) {
    console.error(error);
  }
}

/**
 * Función para enviar el formulario al backend
 */
async function registrarEmpleado(event) {
  try {
    event.preventDefault(); // Evitar que la página se recargue al enviar el formulario

    const formulario = document.querySelector("#formularioEmpleado");
    // Crear un objeto FormData para enviar los datos del formulario
    const formData = new FormData(formulario);

    // Enviar los datos del formulario al backend usando Axios
    const response = await axios.post("acciones/acciones.php", formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    console.log('Respuesta del servidor:', response); // Para debug

    // Verificar la respuesta del backend
    if (response.status === 200 && response.data) {
      // Verificar si la respuesta indica éxito
      if (response.data.success) {
        // Llamar a la función insertEmpleadoTable para insertar el nuevo registro en la tabla
        if (typeof window.insertEmpleadoTable === 'function') {
          window.insertEmpleadoTable();
        } else {
          // Si no existe la función, recargar la página como alternativa
          window.location.reload();
        }

        setTimeout(() => {
          $("#agregarEmpleadoModal").css("opacity", "");
          $("#agregarEmpleadoModal").modal("hide");

          // Llamar a la función para mostrar un mensaje de éxito
          if (typeof toastr !== 'undefined') {
            toastr.options = window.toastrOptions || {};
            toastr.success(response.data.message || "¡El empleado se registró correctamente!");
          } else {
            alert(response.data.message || "¡El empleado se registró correctamente!");
          }
        }, 600);
      } else {
        // Error del servidor
        console.error("Error del servidor:", response.data.message);
        if (typeof toastr !== 'undefined') {
          toastr.error(response.data.message || "Error al registrar el empleado");
        } else {
          alert(response.data.message || "Error al registrar el empleado");
        }
      }
    } else {
      console.error("Respuesta inesperada del servidor:", response);
      if (typeof toastr !== 'undefined') {
        toastr.error("Error inesperado del servidor");
      } else {
        alert("Error inesperado del servidor");
      }
    }
  } catch (error) {
    console.error("Error al enviar el formulario:", error);
    
    // Mostrar mensaje de error más específico
    let errorMessage = "Error al enviar el formulario";
    
    if (error.response) {
      // El servidor respondió con un código de estado de error
      console.error("Error de respuesta:", error.response.data);
      errorMessage = error.response.data.message || "Error del servidor";
    } else if (error.request) {
      // La petición se hizo pero no se recibió respuesta
      console.error("Error de red:", error.request);
      errorMessage = "Error de conexión con el servidor";
    } else {
      // Algo más causó el error
      console.error("Error:", error.message);
      errorMessage = error.message;
    }
    
    if (typeof toastr !== 'undefined') {
      toastr.error(errorMessage);
    } else {
      alert(errorMessage);
    }
  }
}