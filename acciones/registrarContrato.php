<?php
header('Content-Type: application/json');

// 1. Configuración de conexión (debería estar en un archivo aparte)
require_once 'config.php';

try {
    $conexion = new PDO("pgsql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// 2. Obtener y validar datos POST
$requiredFields = ['empleado_id', 'contrato_id', 'fecha_inicio', 'salario'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "El campo $field es obligatorio"]);
        exit;
    }
}

$empleado_id = (int)$_POST['empleado_id'];
$contrato_id = (int)$_POST['contrato_id'];
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
$salario = (float)$_POST['salario'];

// 3. Validaciones adicionales
if ($empleado_id <= 0 || $contrato_id <= 0 || $salario <= 0) {
    echo json_encode(['success' => false, 'message' => 'Valores numéricos deben ser positivos']);
    exit;
}

if (!strtotime($fecha_inicio) || ($fecha_fin && !strtotime($fecha_fin))) {
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
    exit;
}

if ($fecha_fin && strtotime($fecha_fin) < strtotime($fecha_inicio)) {
    echo json_encode(['success' => false, 'message' => 'La fecha fin no puede ser anterior a la fecha inicio']);
    exit;
}

// 4. Iniciar transacción
$conexion->beginTransaction();

try {
    // 5. Verificar existencia de empleado y contrato
    $stmt = $conexion->prepare("SELECT 1 FROM tbl_empleados WHERE id = ?");
    $stmt->execute([$empleado_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Empleado no existe');
    }

    $stmt = $conexion->prepare("SELECT 1 FROM tbl_contratos WHERE id = ?");
    $stmt->execute([$contrato_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Tipo de contrato no existe');
    }

    // 6. Verificar si ya existe contrato
    $stmt = $conexion->prepare("SELECT id FROM tbl_detalle_contrato WHERE empleado_id = ?");
    $stmt->execute([$empleado_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // 7. Actualizar contrato existente
        $sql = "UPDATE tbl_detalle_contrato SET 
                contrato_id = ?, 
                fecha_inicio = ?, 
                fecha_fin = ?, 
                salario = ?,
                updated_at = NOW()
                WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $success = $stmt->execute([
            $contrato_id,
            $fecha_inicio,
            $fecha_fin,
            $salario,
            $existing['id']
        ]);
        
        $action = 'actualizado';
    } else {
        // 8. Insertar nuevo contrato
        $sql = "INSERT INTO tbl_detalle_contrato 
                (empleado_id, contrato_id, fecha_inicio, fecha_fin, salario, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conexion->prepare($sql);
        $success = $stmt->execute([
            $empleado_id,
            $contrato_id,
            $fecha_inicio,
            $fecha_fin,
            $salario
        ]);
        
        $action = 'creado';
    }

    if ($success) {
        $conexion->commit();
        echo json_encode([
            'success' => true,
            'message' => "Contrato $action correctamente",
            'action' => $action
        ]);
    } else {
        throw new Exception('No se pudo completar la operación');
    }

} catch (Exception $e) {
    $conexion->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>