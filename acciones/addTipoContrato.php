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

// Obtener el tipo de contrato del formulario
$tipo_contrato = isset($_POST['tipo_contrato']) ? trim($_POST['tipo_contrato']) : '';

// Log para debugging
error_log("Datos recibidos: " . print_r($_POST, true));

if (empty($tipo_contrato)) {
    echo json_encode([
        'success' => false,
        'message' => 'El nombre del tipo de contrato es requerido'
    ]);
    exit;
}

// Verificar si ya existe un tipo de contrato con el mismo nombre
$check_stmt = $conexion->prepare("SELECT id FROM tbl_contratos WHERE tipo_contrato = ?");
$check_stmt->bind_param("s", $tipo_contrato);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Ya existe un tipo de contrato con ese nombre'
    ]);
    $check_stmt->close();
    $conexion->close();
    exit;
}

$check_stmt->close();

// Preparar la consulta SQL para insertar un nuevo tipo de contrato
$stmt = $conexion->prepare("INSERT INTO tbl_contratos (tipo_contrato) VALUES (?)");
$stmt->bind_param("s", $tipo_contrato);

// Ejecutar la consulta
if ($stmt->execute()) {
    $nuevo_id = $conexion->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Tipo de contrato agregado correctamente',
        'id' => $nuevo_id,
        'tipo_contrato' => $tipo_contrato
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al agregar el tipo de contrato: ' . $stmt->error
    ]);
}

// Cerrar la conexión
$stmt->close();
$conexion->close();
?>