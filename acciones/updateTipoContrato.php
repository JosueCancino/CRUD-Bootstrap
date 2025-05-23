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