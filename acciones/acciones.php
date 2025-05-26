<?php
// Establecer conexión con PDO
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json; charset=utf-8');
    
    $nombre   = trim($_POST['nombre'] ?? '');
    $edad     = trim($_POST['edad'] ?? '');
    $sexo     = trim($_POST['sexo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cargo    = trim($_POST['cargo'] ?? '');

    // Validar campos obligatorios
    if (empty($nombre) || empty($edad) || empty($sexo) || empty($telefono) || empty($cargo)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Validar que la edad sea numérica
    if (!is_numeric($edad) || $edad < 1 || $edad > 120) {
        echo json_encode(['success' => false, 'message' => 'La edad debe ser un número válido']);
        exit;
    }

    // Verificar que la conexión existe
    if (!isset($conexion)) {
        echo json_encode(['success' => false, 'message' => 'Error: No hay conexión a la base de datos']);
        exit;
    }

     $dirLocal = "fotos_empleados";

    if (isset($_FILES['avatar'])) {
        $archivoTemporal = $_FILES['avatar']['tmp_name'];
        $nombreArchivo = $_FILES['avatar']['name'];

        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        // Generar un nombre único y seguro para el archivo
        $nombreArchivo = substr(md5(uniqid(rand())), 0, 10) . "." . $extension;
        $rutaDestino = $dirLocal . '/' . $nombreArchivo;

        // Mover el archivo a la ubicación deseada
        if (move_uploaded_file($archivoTemporal, $rutaDestino)) {

            $sql = "INSERT INTO $tbl_empleados (nombre, edad, sexo, telefono, cargo, avatar) 
            VALUES ('$nombre', '$edad', '$sexo', '$telefono', '$cargo', '$nombreArchivo')";

            if ($conexion->query($sql) === TRUE) {
                header("location:../");
            } else {
                echo "Error al crear el registro: " . $conexion->error;
            }
        } else {
            echo json_encode(array('error' => 'Error al mover el archivo'));
        }
    } else {
        echo json_encode(array('error' => 'No se ha enviado ningún archivo o ha ocurrido un error al cargar el archivo'));
    }

    try {
        // Insertar empleado en la base de datos
        $sql = "INSERT INTO tbl_empleados (nombre, edad, sexo, telefono, cargo, avatar) 
                VALUES (:nombre, :edad, :sexo, :telefono, :cargo, :avatar)";
        $stmt = $conexion->prepare($sql);
        
        $resultado = $stmt->execute([
            'nombre'   => $nombre,
            'edad'     => (int)$edad,
            'sexo'     => $sexo,
            'telefono' => $telefono,
            'cargo'    => $cargo,
            'avatar'   => $nombreArchivo  // Guardar como Base64
        ]);

        if ($resultado) {
            echo json_encode([
                'success' => true, 
                'message' => 'Empleado registrado exitosamente',
                'empleado_id' => $conexion->lastInsertId()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el empleado en la base de datos']);
        }

    } catch (PDOException $e) {
        error_log("Error al insertar empleado: " . $e->getMessage());
        
        echo json_encode([
            'success' => false, 
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ]);
    }
    
    exit;
}

/**
 * Función para obtener todos los empleados 
 */
function obtenerEmpleados($conexion)
{
    try {
        $sql = "SELECT * FROM tbl_empleados ORDER BY id ASC";
        $resultado = $conexion->query($sql);
        return $resultado;
    } catch (PDOException $e) {
        error_log("Error al obtener empleados: " . $e->getMessage());
        return false;
    }
}

function obtenerContratos($conexion)
{
    try {
        $sql = "SELECT 
                e.*, 
                COALESCE(c.tipo_contrato, 'Sin asignar') AS tipo_contrato
            FROM 
                tbl_empleados e
            LEFT JOIN 
                tbl_detalle_contrato dc ON e.id = dc.empleado_id
            LEFT JOIN 
                tbl_contratos c ON dc.contrato_id = c.id
            ORDER BY 
                e.id ASC";

        $resultado = $conexion->query($sql);
        $contrato = [];
        if ($resultado && $resultado->rowCount() > 0) {
            while ($row = $resultado->fetch(PDO::FETCH_ASSOC)) {
                $contrato[] = $row;
            }
        }
        return $contrato;
    } catch (PDOException $e) {
        error_log("Error al obtener contratos: " . $e->getMessage());
        return [];
    }
}
?>