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

// Obtener los datos del formulario
$contrato_id = isset($_POST['contrato_id']) ? (int)$_POST['contrato_id'] : 0;
$tipo_contrato = isset($_POST['tipo_contrato']) ? trim($_POST['tipo_contrato']) : '';

if ($contrato_id <= 0 || $tipo_contrato === '') {
    echo json_encode([
        'success' => false,
        'message' => 'El ID y el nombre del tipo de contrato son requeridos'
    ]);
    exit;
}

// Ejecutar actualización
try {
    $stmt = $conexion->prepare("UPDATE tbl_contratos SET tipo_contrato = :tipo_contrato WHERE id = :id");
    $stmt->execute([
        'tipo_contrato' => $tipo_contrato,
        'id' => $contrato_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Tipo de contrato actualizado correctamente'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el tipo de contrato: ' . $e->getMessage()
    ]);
}
?>
