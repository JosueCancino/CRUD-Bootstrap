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

    // Procesar la imagen
    $nombreArchivo = null;
    $dirLocal = __DIR__ . "/fotos_empleados"; // Ruta absoluta al directorio
    $dirRelativo = "acciones/fotos_empleados"; // Ruta relativa para mostrar en web

    // Crear directorio si no existe
    if (!file_exists($dirLocal)) {
        if (!mkdir($dirLocal, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Error al crear directorio de fotos']);
            exit;
        }
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $archivoTemporal = $_FILES['avatar']['tmp_name'];
        $nombreOriginal = $_FILES['avatar']['name'];
        $tamanoArchivo = $_FILES['avatar']['size'];
        $tipoArchivo = $_FILES['avatar']['type'];

        // Validar tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo se permiten JPG, PNG y GIF']);
            exit;
        }

        // Validar tamaño (máximo 5MB)
        if ($tamanoArchivo > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande. Máximo 5MB']);
            exit;
        }

        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        
        // Generar un nombre único y seguro para el archivo
        $nombreArchivo = substr(md5(uniqid(rand())), 0, 10) . "." . $extension;
        $rutaDestino = $dirLocal . '/' . $nombreArchivo;

        // Mover el archivo a la ubicación deseada
        if (!move_uploaded_file($archivoTemporal, $rutaDestino)) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
            exit;
        }
    } else if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Si hay un error en la subida del archivo (que no sea "no hay archivo")
        $errores = [
            UPLOAD_ERR_INI_SIZE => 'El archivo es demasiado grande',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo',
            UPLOAD_ERR_EXTENSION => 'Extensión de archivo no permitida'
        ];
        
        $mensajeError = $errores[$_FILES['avatar']['error']] ?? 'Error desconocido al subir archivo';
        echo json_encode(['success' => false, 'message' => $mensajeError]);
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
            'avatar'   => $nombreArchivo // Puede ser null si no se subió imagen
        ]);

        if ($resultado) {
            echo json_encode([
                'success' => true, 
                'message' => 'Empleado registrado exitosamente',
                'empleado_id' => $conexion->lastInsertId(),
                'avatar' => $nombreArchivo
            ]);
        } else {
            // Si falla la inserción y se subió una imagen, eliminarla
            if ($nombreArchivo && file_exists($dirLocal . '/' . $nombreArchivo)) {
                unlink($dirLocal . '/' . $nombreArchivo);
            }
            echo json_encode(['success' => false, 'message' => 'Error al guardar el empleado en la base de datos']);
        }

    } catch (PDOException $e) {
        // Si hay error en la base de datos y se subió una imagen, eliminarla
        if ($nombreArchivo && file_exists($dirLocal . '/' . $nombreArchivo)) {
            unlink($dirLocal . '/' . $nombreArchivo);
        }
        
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

/**
 * Función auxiliar para obtener la URL completa de la imagen
 */
function obtenerUrlAvatar($nombreArchivo) {
    if (empty($nombreArchivo)) {
        return 'assets/img/default-avatar.png'; // Imagen por defecto
    }
    return 'acciones/fotos_empleados/' . $nombreArchivo;
}
?>