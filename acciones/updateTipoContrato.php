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

// Obtener los datos del formulario
$contrato_id = isset($_POST['contrato_id']) ? (int)$_POST['contrato_id'] : 0;
$tipo_contrato = isset($_POST['tipo_contrato']) ? trim($_POST['tipo_contrato']) : '';

if (empty($contrato_id) || empty($tipo_contrato)) {
    echo json_encode([
        'success' => false,
        'message' => 'El ID y el nombre del tipo de contrato son requeridos'
    ]);
    exit;
}

// Preparar la consulta SQL para actualizar el tipo de contrato
$stmt = $conexion->prepare("UPDATE tbl_contratos SET tipo_contrato = ? WHERE id = ?");
$stmt->bind_param("si", $tipo_contrato, $contrato_id);

// Ejecutar la consulta
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Tipo de contrato actualizado correctamente'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el tipo de contrato: ' . $stmt->error
    ]);
}

// Cerrar la conexión
$stmt->close();
$conexion->close();
?>