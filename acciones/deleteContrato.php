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