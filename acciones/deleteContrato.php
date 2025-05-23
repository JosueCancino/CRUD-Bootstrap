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

// Obtener datos de la solicitud JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$empleado_id = isset($data['empleado_id']) ? (int)$data['empleado_id'] : 0;

if (empty($empleado_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de empleado requerido'
    ]);
    exit;
}

// Eliminar el contrato del empleado
$stmt = $conexion->prepare("DELETE FROM tbl_detalle_contrato WHERE empleado_id = ?");
$stmt->bind_param("i", $empleado_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Contrato eliminado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró un contrato para este empleado'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el contrato: ' . $stmt->error
    ]);
}

$stmt->close();
$conexion->close();
?>