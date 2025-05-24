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