<?php
// Establecer conexión directamente en este archivo
$host = "dpg-d0oc1u8dl3ps73du8ekg-a";   // el host de Render
$port = "5432";
$dbname = "bd_empleados_5765";          // el nombre de tu base de datos
$user = "josuecancino";                 // tu usuario
$password = "UcfOse1UhwBBoIWFyyKgBpURpJhiD1GD";          // tu contraseña

// Intenta establecer conexión con PDO
try {
    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa"; // Puedes usar esto para probar
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<div class="modal fade" id="agregarContratoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-5 titulo_modal">Gestionar Contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body">
                <form id="formularioContrato" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Empleado:</label>
                        <input type="text" class="form-control" id="nombre_empleado" readonly>
                        <input type="hidden" name="empleado_id" id="empleado_id">
                        <input type="hidden" name="detalle_id" id="detalle_id">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo de Contrato</label>
                        <select class="form-select" name="contrato_id" id="contrato_id_select" required>
                            <option value="">Seleccione un tipo de contrato</option>
                            <?php
                            if (isset($conexion)) {
                                try {
                                    $sql = "SELECT id, tipo_contrato FROM tbl_contratos ORDER BY tipo_contrato ASC";
                                    $contratos = $conexion->query($sql);
                                    
                                    if ($contratos && $contratos->rowCount() > 0) {
                                        while ($row = $contratos->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['tipo_contrato']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No hay contratos disponibles</option>";
                                    }
                                } catch (Exception $e) {
                                    echo "<option value=''>Error: " . $e->getMessage() . "</option>";
                                }
                            } else {
                                echo "<option value=''>Error: Conexión no establecida</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de Fin (opcional)</label>
                        <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Salario</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="salario" id="salario" required />
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="btnGuardarContrato" onclick="guardarContrato()">
                            Guardar Contrato
                        </button>
                        <button type="button" class="btn btn-danger" id="btnEliminarContrato" onclick="eliminarContrato()" style="display: none;">
                            Eliminar Contrato
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function guardarContrato() {
    const form = document.getElementById('formularioContrato');
    const formData = new FormData(form);
    
    // Validar campos obligatorios
    const empleadoId = document.getElementById('empleado_id').value;
    const contratoId = document.getElementById('contrato_id_select').value;
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const salario = document.getElementById('salario').value;
    
    if (!empleadoId || !contratoId || !fechaInicio || !salario) {
        toastr.error('Por favor complete todos los campos obligatorios');
        return;
    }
    
    const btnGuardar = document.getElementById('btnGuardarContrato');
    btnGuardar.disabled = true;
    btnGuardar.textContent = 'Guardando...';
    
    fetch('acciones/registrarContrato.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('agregarContratoModal'));
            modal.hide();
            // Refrescar tabla si existe la función
            if (typeof refreshTable === 'function') {
                refreshTable();
            } else {
                location.reload();
            }
        } else {
            toastr.error(data.message || 'Error al procesar el contrato');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Hubo un error al procesar el contrato');
    })
    .finally(() => {
        btnGuardar.disabled = false;
        btnGuardar.textContent = 'Guardar Contrato';
    });
}

function eliminarContrato() {
    const empleadoId = document.getElementById('empleado_id').value;
    
    if (!empleadoId) {
        toastr.error('No se puede eliminar el contrato');
        return;
    }
    
    if (!confirm('¿Está seguro de que desea eliminar este contrato?')) {
        return;
    }
    
    fetch('acciones/deleteContrato.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ empleado_id: empleadoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('agregarContratoModal'));
            modal.hide();
            // Refrescar tabla
            if (typeof refreshTable === 'function') {
                refreshTable();
            } else {
                location.reload();
            }
        } else {
            toastr.error(data.message || 'Error al eliminar el contrato');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Hubo un error al eliminar el contrato');
    });
}

// Función para cargar los datos del contrato
function cargarDatosContrato(empleadoId) {
    if (!empleadoId) return;
    
    fetch(`acciones/getContratoEmpleado.php?empleado_id=${empleadoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Llenar datos básicos del empleado
                document.getElementById('nombre_empleado').value = data.data.nombre_empleado;
                document.getElementById('empleado_id').value = data.data.empleado_id || empleadoId;
                
                if (data.has_contract) {
                    // Si tiene contrato, llenar todos los campos
                    document.getElementById('detalle_id').value = data.data.detalle_id;
                    document.getElementById('contrato_id_select').value = data.data.contrato_id;
                    document.getElementById('fecha_inicio').value = data.data.fecha_inicio;
                    document.getElementById('fecha_fin').value = data.data.fecha_fin || '';
                    document.getElementById('salario').value = data.data.salario;
                    
                    // Mostrar botón de eliminar y cambiar título
                    document.getElementById('btnEliminarContrato').style.display = 'block';
                    document.querySelector('.titulo_modal').textContent = `Editar Contrato - ${data.data.nombre_empleado}`;
                    document.getElementById('btnGuardarContrato').textContent = 'Actualizar Contrato';
                } else {
                    // Si no tiene contrato, limpiar campos y ocultar botón eliminar
                    document.getElementById('detalle_id').value = '';
                    document.getElementById('contrato_id_select').value = '';
                    document.getElementById('fecha_inicio').value = '';
                    document.getElementById('fecha_fin').value = '';
                    document.getElementById('salario').value = '';
                    
                    document.getElementById('btnEliminarContrato').style.display = 'none';
                    document.querySelector('.titulo_modal').textContent = `Asignar Contrato - ${data.data.nombre_empleado}`;
                    document.getElementById('btnGuardarContrato').textContent = 'Guardar Contrato';
                }
            } else {
                toastr.error(data.message || 'Error al cargar los datos del contrato');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error al cargar los datos del contrato');
        });
}
</script>