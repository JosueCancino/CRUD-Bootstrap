<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Activar reporte de errores para debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
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

    if (!is_dir($dirLocal)) {
        mkdir($dirLocal, 0755, true);
    }

    $nombreArchivo = null; // Inicializar la variable

    // Verificar si se subió un archivo
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $archivoTemporal = $_FILES['avatar']['tmp_name'];
        $nombreOriginal = $_FILES['avatar']['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        
        // Validar extensión
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $extensionesPermitidas)) {
            echo json_encode(['success' => false, 'message' => 'Formato de imagen no válido']);
            exit;
        }
        
        $nombreArchivo = substr(md5(uniqid(rand())), 0, 10) . "." . $extension;
        $rutaDestino = $dirLocal . '/' . $nombreArchivo;

        if (!move_uploaded_file($archivoTemporal, $rutaDestino)) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
            exit;
        }
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
            // Si es una petición AJAX, devolver JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => true, 'message' => 'Empleado registrado exitosamente']);
                exit;
            } else {
                // Si es una petición normal, redirigir
                header("Location: ../");
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el empleado']);
        }

    } catch (PDOException $e) {
        // Log del error para debugging
        error_log("Error al insertar empleado: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
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