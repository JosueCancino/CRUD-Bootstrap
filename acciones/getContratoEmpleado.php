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

// Obtener el ID del empleado
$empleado_id = isset($_GET['empleado_id']) ? (int)$_GET['empleado_id'] : 0;

if (empty($empleado_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de empleado requerido'
    ]);
    exit;
}

// Consultar los datos del contrato del empleado
$sql = "SELECT 
            dc.id as detalle_id,
            dc.empleado_id,
            dc.contrato_id,
            dc.fecha_inicio,
            dc.fecha_fin,
            dc.salario,
            e.nombre as nombre_empleado,
            c.tipo_contrato
        FROM 
            tbl_detalle_contrato dc
        INNER JOIN 
            tbl_empleados e ON dc.empleado_id = e.id
        INNER JOIN 
            tbl_contratos c ON dc.contrato_id = c.id
        WHERE 
            dc.empleado_id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $empleado_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $contrato = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'has_contract' => true,
        'data' => $contrato
    ]);
} else {
    // Si no tiene contrato, obtener solo los datos del empleado
    $sql_empleado = "SELECT id, nombre FROM tbl_empleados WHERE id = ?";
    $stmt_empleado = $conexion->prepare($sql_empleado);
    $stmt_empleado->bind_param("i", $empleado_id);
    $stmt_empleado->execute();
    $result_empleado = $stmt_empleado->get_result();
    
    if ($result_empleado->num_rows > 0) {
        $empleado = $result_empleado->fetch_assoc();
        echo json_encode([
            'success' => true,
            'has_contract' => false,
            'data' => [
                'empleado_id' => $empleado['id'],
                'nombre_empleado' => $empleado['nombre']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Empleado no encontrado'
        ]);
    }
    
    $stmt_empleado->close();
}

$stmt->close();
$conexion->close();
?>