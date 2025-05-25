<?php
header('Content-Type: application/json');

// Establecer conexión
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

// Obtener el ID del empleado desde GET
$empleado_id = isset($_GET['empleado_id']) ? (int)$_GET['empleado_id'] : 0;

if ($empleado_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de empleado requerido'
    ]);
    exit;
}

try {
    // Primero obtener los datos básicos del empleado
    $stmt = $conexion->prepare("
        SELECT 
            e.id as empleado_id,
            CONCAT(e.nombre, ' ', e.apellido) as nombre_empleado
        FROM tbl_empleados e 
        WHERE e.id = :empleado_id
    ");
    $stmt->execute(['empleado_id' => $empleado_id]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$empleado) {
        echo json_encode([
            'success' => false,
            'message' => 'Empleado no encontrado'
        ]);
        exit;
    }
    
    // Ahora buscar si tiene contrato
    $stmt = $conexion->prepare("
        SELECT 
            dc.id as detalle_id,
            dc.contrato_id,
            dc.fecha_inicio,
            dc.fecha_fin,
            dc.salario,
            c.tipo_contrato
        FROM tbl_detalle_contrato dc
        INNER JOIN tbl_contratos c ON dc.contrato_id = c.id
        WHERE dc.empleado_id = :empleado_id
    ");
    $stmt->execute(['empleado_id' => $empleado_id]);
    $contrato = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'has_contract' => $contrato ? true : false,
        'data' => [
            'empleado_id' => $empleado['empleado_id'],
            'nombre_empleado' => $empleado['nombre_empleado']
        ]
    ];
    
    if ($contrato) {
        $response['data'] = array_merge($response['data'], [
            'detalle_id' => $contrato['detalle_id'],
            'contrato_id' => $contrato['contrato_id'],
            'fecha_inicio' => $contrato['fecha_inicio'],
            'fecha_fin' => $contrato['fecha_fin'],
            'salario' => $contrato['salario'],
            'tipo_contrato' => $contrato['tipo_contrato']
        ]);
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los datos: ' . $e->getMessage()
    ]);
}
?>