<?php
header('Content-Type: application/json');

// Establecer conexión con PDO
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

// Obtener datos de la solicitud JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$id = isset($data['id']) ? (int)$data['id'] : 0;

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID inválido'
    ]);
    exit;
}

// Verificar si el tipo de contrato está siendo utilizado
try {
    $check_sql = "SELECT COUNT(*) FROM tbl_detalle_contrato WHERE contrato_id = :id";
    $check_stmt = $conexion->prepare($check_sql);
    $check_stmt->execute(['id' => $id]);
    $count = $check_stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No se puede eliminar este tipo de contrato porque está en uso por empleados.'
        ]);
        exit;
    }

    // Eliminar el contrato
    $stmt = $conexion->prepare("DELETE FROM tbl_contratos WHERE id = :id");
    $stmt->execute(['id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Tipo de contrato eliminado correctamente'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar: ' . $e->getMessage()
    ]);
}
?>
