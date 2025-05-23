<?php
$host = "dpg-d0oc1u8dl3ps73du8ekg-a";   // el host de Render
$port = "5432";
$dbname = "bd_empleados_5765";          // el nombre de tu base de datos
$user = "josuecancino";                 // tu usuario
$password = "UcfOse1UhwBBoIWFyyKgBpURpJhiD1GD";          // tu contraseña

// Intenta establecer conexión con PDO
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa"; // Puedes usar esto para probar
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>