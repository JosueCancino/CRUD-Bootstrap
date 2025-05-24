<?php
require_once("../config/config.php");

// Verifica si el ID está presente y es válido
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    // Usar consulta preparada para evitar inyección
    $sql = "SELECT * FROM tbl_empleados WHERE id = :id LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id' => $id]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-type: application/json; charset=utf-8');
    echo json_encode($empleado ?: ['error' => 'Empleado no encontrado']);
    exit;

} catch (PDOException $e) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Error al consultar: ' . $e->getMessage()]);
    exit;
}
