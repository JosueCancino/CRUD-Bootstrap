/**
 * Modal para gestionar tipos de contrato
 */
async function modalGestionTiposContrato() {
    try {
        // Ocultar la modal si está abierta
        const existingModal = document.getElementById("gestionContratosModal");
        if (existingModal) {
          const modal = bootstrap.Modal.getInstance(existingModal);
          if (modal) {
            modal.hide();
          }
          existingModal.remove(); // Eliminar la modal existente
        }
    
        // Fetch the modal content
        const response = await fetch("modales/modalGestionContrato.php");
    
        if (!response.ok) {
          throw new Error("Error al cargar la modal");
        }
    
        // Get the modal content
        const data = await response.text();
    
        // Create a container for the modal
        const modalContainer = document.createElement("div");
        modalContainer.innerHTML = data;
    
        // Add the modal to the document
        document.body.appendChild(modalContainer);
    
        // Show the modal
        const myModal = new bootstrap.Modal(
          modalContainer.querySelector("#gestionContratosModal")
        );
        myModal.show();
      } catch (error) {
        console.error(error);
        toastr.error("Error al cargar el formulario de gestión de contratos");
    }
}