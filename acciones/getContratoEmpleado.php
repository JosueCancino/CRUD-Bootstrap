<?php
// Configuración y conexión con PDO
$host = "dpg-d0oc1u8dl3ps73du8ekg-a";
$port = "5432";
$dbname = "bd_empleados_5765";
$user = "josuecancino";
$password = "UcfOse1UhwBBoIWFyyKgBpURpJhiD1GD";

header('Content-Type: application/json');

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

// Obtener ID del empleado
$empleado_id = isset($_GET['empleado_id']) ? (int)$_GET['empleado_id'] : 0;

if ($empleado_id === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de empleado requerido'
    ]);
    exit;
}

// Consultar datos del contrato
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
            dc.empleado_id = :empleado_id
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->execute(['empleado_id' => $empleado_id]);
$contrato = $stmt->fetch(PDO::FETCH_ASSOC);

if ($contrato) {
    echo json_encode([
        'success' => true,
        'has_contract' => true,
        'data' => $contrato
    ]);
} else {
    // Consultar solo los datos del empleado
    $sql_empleado = "SELECT id, nombre FROM tbl_empleados WHERE id = :empleado_id";
    $stmt_empleado = $conexion->prepare($sql_empleado);
    $stmt_empleado->execute(['empleado_id' => $empleado_id]);
    $empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);

    if ($empleado) {
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
}
?>
