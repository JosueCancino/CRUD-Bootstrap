<?php
header('Content-Type: application/json');

// Establecer conexión
$host = "dpg-d0oc1u8dl3ps73du8ekg-a";
$port = "5432";
$dbname = "bd_empleados_5765";
$user = "josuecancino";
$password = "UcfOse1UhwBBoIWFyyKgBpURpJhiD1GD";

try {
    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión: ' . $e->getMessage()
    ]);
    exit;
}

// Obtener datos del cuerpo de la solicitud (JSON)
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$empleado_id = isset($data['empleado_id']) ? (int)$data['empleado_id'] : 0;

if ($empleado_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de empleado requerido'
    ]);
    exit;
}

try {
    $stmt = $conexion->prepare("DELETE FROM tbl_detalle_contrato WHERE empleado_id = :empleado_id");
    $stmt->execute(['empleado_id' => $empleado_id]);
    
    if ($stmt->rowCount() > 0) {
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
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el contrato: ' . $e->getMessage()
    ]);
}
