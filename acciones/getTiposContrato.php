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

// Consultar los tipos de contrato
$sql = "SELECT id, tipo_contrato FROM tbl_contratos ORDER BY id ASC";
$result = $conexion->query($sql);

$contratos = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Asegurarse de que los datos son seguros
        $contratos[] = [
            'id' => (int)$row['id'],  // Asegurarse de que el ID es un entero
            'tipo_contrato' => htmlspecialchars_decode($row['tipo_contrato'])  // Decodificar caracteres especiales
        ];
    }
}

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($contratos);

// Cerrar la conexión
$conexion->close();
?>