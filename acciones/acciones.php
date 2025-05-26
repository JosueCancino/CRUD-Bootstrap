<?php
header('Content-Type: application/json');

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
    // Headers para respuesta JSON
    header('Content-Type: application/json; charset=utf-8');
    
    $nombre   = trim($_POST['nombre'] ?? '');
    $edad     = trim($_POST['edad'] ?? '');
    $sexo     = trim($_POST['sexo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cargo    = trim($_POST['cargo'] ?? '');
    $dirLocal = "fotos_empleados";

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

    // Crear directorio si no existe
    if (!is_dir($dirLocal)) {
        if (!mkdir($dirLocal, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Error al crear directorio para imágenes']);
            exit;
        }
    }

    $nombreArchivo = null;

    // Procesar imagen si se subió
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $archivoTemporal = $_FILES['avatar']['tmp_name'];
        $nombreOriginal = $_FILES['avatar']['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        
        // Validar extensión
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $extensionesPermitidas)) {
            echo json_encode(['success' => false, 'message' => 'Formato de imagen no válido. Use: jpg, jpeg, png, gif']);
            exit;
        }
        
        // Validar tamaño (máximo 5MB)
        if ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'La imagen es demasiado grande. Máximo 5MB']);
            exit;
        }
        
        $nombreArchivo = substr(md5(uniqid(rand())), 0, 10) . "." . $extension;
        $rutaDestino = $dirLocal . '/' . $nombreArchivo;

        if (!move_uploaded_file($archivoTemporal, $rutaDestino)) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
            exit;
        }
    } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Si hay error en la subida
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'El archivo es demasiado grande',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño permitido',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta directorio temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir archivo',
            UPLOAD_ERR_EXTENSION => 'Extensión de archivo bloqueada'
        ];
        
        $error = $_FILES['avatar']['error'];
        $message = isset($errorMessages[$error]) ? $errorMessages[$error] : 'Error desconocido al subir archivo';
        
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
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
            'avatar'   => $nombreArchivo
        ]);

        if ($resultado) {
            echo json_encode([
                'success' => true, 
                'message' => 'Empleado registrado exitosamente',
                'empleado_id' => $conexion->lastInsertId()
            ]);
        } else {
            // Si falló la inserción, eliminar imagen subida
            if ($nombreArchivo && file_exists($dirLocal . '/' . $nombreArchivo)) {
                unlink($dirLocal . '/' . $nombreArchivo);
            }
            echo json_encode(['success' => false, 'message' => 'Error al guardar el empleado en la base de datos']);
        }

    } catch (PDOException $e) {
        // Si hay error de BD, eliminar imagen subida
        if ($nombreArchivo && file_exists($dirLocal . '/' . $nombreArchivo)) {
            unlink($dirLocal . '/' . $nombreArchivo);
        }
        
        // Log del error
        error_log("Error al insertar empleado: " . $e->getMessage());
        
        echo json_encode([
            'success' => false, 
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ]);
    }
    
    exit; // Importante: terminar la ejecución aquí
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