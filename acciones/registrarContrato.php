<?php
// Configurar respuesta en JSON y prevenir cualquier salida antes
ob_start();
header('Content-Type: application/json; charset=utf-8');

// Configuración de base de datos
$host = "dpg-d0oc1u8dl3ps73du8ekg-a";
$port = "5432";
$dbname = "bd_empleados_5765";
$user = "josuecancino";
$password = "UcfOse1UhwBBoIWFyyKgBpURpJhiD1GD";

try {
    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// Obtener datos POST
$empleado_id = isset($_POST['empleado_id']) ? (int)$_POST['empleado_id'] : 0;
$contrato_id = isset($_POST['contrato_id']) ? (int)$_POST['contrato_id'] : 0;
$fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
$fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : null;
$salario = isset($_POST['salario']) ? (float)$_POST['salario'] : 0;

// Convertir fecha_fin vacía a null
if ($fecha_fin === '') {
    $fecha_fin = null;
}

// Validación de campos obligatorios
if ($empleado_id <= 0 || $contrato_id <= 0 || $fecha_inicio === '' || $salario <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
    exit;
}

try {
    // Validar existencia del empleado
    $stmt = $conexion->prepare("SELECT id FROM tbl_empleados WHERE id = :id");
    $stmt->execute(['id' => $empleado_id]);
    if (!$stmt->fetch()) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'El empleado no existe']);
        exit;
    }

    // Validar existencia del contrato
    $stmt = $conexion->prepare("SELECT id FROM tbl_contratos WHERE id = :id");
    $stmt->execute(['id' => $contrato_id]);
    if (!$stmt->fetch()) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'El tipo de contrato no existe']);
        exit;
    }

    // Verificar si ya existe contrato para este empleado
    $stmt = $conexion->prepare("SELECT id FROM tbl_detalle_contrato WHERE empleado_id = :empleado_id");
    $stmt->execute(['empleado_id' => $empleado_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Actualizar contrato existente
        $detalle_id = $existing['id'];
        $sql = "UPDATE tbl_detalle_contrato SET contrato_id = :contrato_id, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, salario = :salario WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $result = $stmt->execute([
            'contrato_id' => $contrato_id,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'salario' => $salario,
            'id' => $detalle_id
        ]);
        
        if ($result) {
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Contrato actualizado correctamente', 'action' => 'updated']);
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el contrato']);
        }
    } else {
        // Insertar nuevo contrato
        $sql = "INSERT INTO tbl_detalle_contrato (empleado_id, contrato_id, fecha_inicio, fecha_fin, salario) 
                VALUES (:empleado_id, :contrato_id, :fecha_inicio, :fecha_fin, :salario)";
        $stmt = $conexion->prepare($sql);
        $result = $stmt->execute([
            'empleado_id' => $empleado_id,
            'contrato_id' => $contrato_id,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'salario' => $salario
        ]);
        
        if ($result) {
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Contrato registrado correctamente', 'action' => 'created']);
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Error al registrar el contrato']);
        }
    }
    
} catch (PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error general: ' . $e->getMessage()]);
}
?>