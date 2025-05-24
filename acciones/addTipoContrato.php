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