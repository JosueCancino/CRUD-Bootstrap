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

// Consultar los tipos de contrato existentes
$sql = "SELECT id, tipo_contrato FROM tbl_contratos ORDER BY id ASC";
$result = $conexion->query($sql);
?>

<div class="modal fade" id="gestionContratosModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-5 titulo_modal">Gestión de Tipos de Contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body">
                <!-- Formulario para añadir nuevo tipo de contrato -->
                <form id="formularioTipoContrato" method="POST" autocomplete="off" onsubmit="return false;">
                    <input type="hidden" id="contrato_id" name="contrato_id" value="">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Tipo de Contrato:</label>
                        <input type="text" class="form-control" id="tipo_contrato" name="tipo_contrato" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="btnGuardarContrato" onclick="registrarTipoContratoClick(event)">
                            Guardar Tipo de Contrato
                        </button>
                    </div>
                </form>

                <!-- Lista de tipos de contrato existentes -->
                <div class="mt-4">
                    <h6>Tipos de Contrato Existentes</h6>
                    <div class="table-responsive">
                        <table class="table table-striped" id="tabla_contratos">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Tipo de Contrato</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lista_contratos">
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                                <td>" . $row['id'] . "</td>
                                                <td>" . htmlspecialchars($row['tipo_contrato']) . "</td>
                                                <td>
                                                    <button type='button' onclick='editarTipoContrato(" . $row['id'] . ", \"" . htmlspecialchars($row['tipo_contrato'], ENT_QUOTES) . "\")' class='btn btn-warning btn-sm'>
                                                        <i class='bi bi-pencil-square'></i>
                                                    </button>
                                                    <button type='button' onclick='eliminarTipoContrato(" . $row['id'] . ")' class='btn btn-danger btn-sm'>
                                                        <i class='bi bi-trash'></i>
                                                    </button>
                                                </td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No hay tipos de contrato registrados</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>