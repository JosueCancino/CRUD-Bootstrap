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

// Obtener datos de la solicitud JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$id = isset($data['id']) ? (int)$data['id'] : 0;

if (empty($id)) {
    echo json_encode([
        'success' => false,
        'message' => 'ID inválido'
    ]);
    exit;
}

// Verificar si el tipo de contrato está siendo utilizado en algún contrato de empleado
// Nota: Cambié el nombre de la tabla a tbl_detalle_contrato
$check_sql = "SELECT COUNT(*) as count FROM tbl_detalle_contrato WHERE contrato_id = ?";
$check_stmt = $conexion->prepare($check_sql);
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'No se puede eliminar este tipo de contrato porque está siendo utilizado por uno o más empleados'
    ]);
    $check_stmt->close();
    $conexion->close();
    exit;
}

// Preparar la consulta SQL para eliminar el tipo de contrato
$stmt = $conexion->prepare("DELETE FROM tbl_contratos WHERE id = ?");
$stmt->bind_param("i", $id);

// Ejecutar la consulta
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Tipo de contrato eliminado correctamente'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el tipo de contrato: ' . $stmt->error
    ]);
}

// Cerrar la conexión
$stmt->close();
$conexion->close();
?>