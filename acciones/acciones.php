<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre   = trim($_POST['nombre'] ?? '');
    $edad     = trim($_POST['edad'] ?? '');
    $sexo     = trim($_POST['sexo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cargo    = trim($_POST['cargo'] ?? '');
    $dirLocal = "fotos_empleados";

    if (!is_dir($dirLocal)) {
        mkdir($dirLocal, 0755, true);
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $archivoTemporal = $_FILES['avatar']['tmp_name'];
        $nombreOriginal = $_FILES['avatar']['name'];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $nombreArchivo = substr(md5(uniqid(rand())), 0, 10) . "." . $extension;
        $rutaDestino = $dirLocal . '/' . $nombreArchivo;

        if (move_uploaded_file($archivoTemporal, $rutaDestino)) {
            try {
                $sql = "INSERT INTO tbl_empleados (nombre, edad, sexo, telefono, cargo, avatar) 
                        VALUES (:nombre, :edad, :sexo, :telefono, :cargo, :avatar)";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([
                    'nombre'   => $nombre,
                    'edad'     => $edad,
                    'sexo'     => $sexo,
                    'telefono' => $telefono,
                    'cargo'    => $cargo,
                    'avatar'   => $nombreArchivo
                ]);

                header("Location: ../");
                exit;

            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se ha enviado ningún archivo válido']);
    }
}

/**
 * Función para obtener todos los empleados 
 */
function obtenerEmpleados($conexion)
{
    $sql = "SELECT * FROM tbl_empleados ORDER BY id ASC";
    $resultado = $conexion->query($sql);
    if (!$resultado) {
        return false;
    }
    return $resultado;
}

function obtenerContratos($conexion)
{
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
    if ($resultado && $resultado->rowCount()> 0) {
        while ($row = $resultado->fetch(PDO::FETCH_ASSOC)) {
            $contrato[] = $row;
        }
    }
    return $contrato;
}