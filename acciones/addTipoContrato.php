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

// Obtener dato del formulario
$tipo_contrato = isset($_POST['tipo_contrato']) ? trim($_POST['tipo_contrato']) : '';

if (empty($tipo_contrato)) {
    echo json_encode([
        'success' => false,
        'message' => 'El nombre del tipo de contrato es requerido'
    ]);
    exit;
}

try {
    // Verificar si ya existe
    $check_stmt = $conexion->prepare("SELECT id FROM tbl_contratos WHERE tipo_contrato = :tipo");
    $check_stmt->execute(['tipo' => $tipo_contrato]);

    if ($check_stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe un tipo de contrato con ese nombre'
        ]);
        exit;
    }

    // Insertar nuevo tipo de contrato
    $stmt = $conexion->prepare("INSERT INTO tbl_contratos (tipo_contrato) VALUES (:tipo)");
    $stmt->execute(['tipo' => $tipo_contrato]);
    $nuevo_id = $conexion->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Tipo de contrato agregado correctamente',
        'id' => $nuevo_id,
        'tipo_contrato' => $tipo_contrato
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al insertar: ' . $e->getMessage()
    ]);
}
?>
