<?php
// Establecer conexión a la base de datos
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_de_datos = "bd_empleados";

$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión: ' . $conexion->connect_error
    ]));
}

// Configurar headers para JSON
header('Content-Type: application/json');

// Obtener los datos del formulario
$empleado_id = isset($_POST['empleado_id']) ? (int)$_POST['empleado_id'] : 0;
$contrato_id = isset($_POST['contrato_id']) ? (int)$_POST['contrato_id'] : 0;
$fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
$fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : null;
$salario = isset($_POST['salario']) ? (float)$_POST['salario'] : 0;

// Log para debugging
error_log("Datos recibidos: " . print_r($_POST, true));

// Validar datos obligatorios
if (empty($empleado_id) || empty($contrato_id) || empty($fecha_inicio) || empty($salario)) {
    echo json_encode([
        'success' => false,
        'message' => 'Todos los campos obligatorios deben ser completados'
    ]);
    exit;
}

// Validar que el empleado existe
$check_empleado = $conexion->prepare("SELECT id FROM tbl_empleados WHERE id = ?");
$check_empleado->bind_param("i", $empleado_id);
$check_empleado->execute();
$result_empleado = $check_empleado->get_result();

if ($result_empleado->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'El empleado seleccionado no existe'
    ]);
    $check_empleado->close();
    $conexion->close();
    exit;
}
$check_empleado->close();

// Validar que el tipo de contrato existe
$check_contrato = $conexion->prepare("SELECT id FROM tbl_contratos WHERE id = ?");
$check_contrato->bind_param("i", $contrato_id);
$check_contrato->execute();
$result_contrato = $check_contrato->get_result();

if ($result_contrato->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'El tipo de contrato seleccionado no existe'
    ]);
    $check_contrato->close();
    $conexion->close();
    exit;
}
$check_contrato->close();

// Verificar si el empleado ya tiene un contrato activo
$check_existing = $conexion->prepare("SELECT id FROM tbl_detalle_contrato WHERE empleado_id = ?");
$check_existing->bind_param("i", $empleado_id);
$check_existing->execute();
$result_existing = $check_existing->get_result();

if ($result_existing->num_rows > 0) {
    // Si ya existe, actualizar el contrato existente
    $row_existing = $result_existing->fetch_assoc();
    $detalle_id = $row_existing['id'];
    
    $update_stmt = $conexion->prepare("UPDATE tbl_detalle_contrato SET contrato_id = ?, fecha_inicio = ?, fecha_fin = ?, salario = ? WHERE id = ?");
    
    if ($fecha_fin) {
        $update_stmt->bind_param("issdi", $contrato_id, $fecha_inicio, $fecha_fin, $salario, $detalle_id);
    } else {
        $update_stmt->bind_param("issdi", $contrato_id, $fecha_inicio, $fecha_fin, $salario, $detalle_id);
    }
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Contrato actualizado correctamente',
            'action' => 'updated'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el contrato: ' . $update_stmt->error
        ]);
    }
    
    $update_stmt->close();
} else {
    // Si no existe, crear un nuevo contrato
    $insert_stmt = $conexion->prepare("INSERT INTO tbl_detalle_contrato (empleado_id, contrato_id, fecha_inicio, fecha_fin, salario) VALUES (?, ?, ?, ?, ?)");
    
    if ($fecha_fin) {
        $insert_stmt->bind_param("iissd", $empleado_id, $contrato_id, $fecha_inicio, $fecha_fin, $salario);
    } else {
        $insert_stmt->bind_param("iissd", $empleado_id, $contrato_id, $fecha_inicio, $fecha_fin, $salario);
    }
    
    if ($insert_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Contrato registrado correctamente',
            'action' => 'created',
            'id' => $conexion->insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al registrar el contrato: ' . $insert_stmt->error
        ]);
    }
    
    $insert_stmt->close();
}

$check_existing->close();
$conexion->close();
?>