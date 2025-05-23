<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("../config/config.php");
    $tbl_empleados = "tbl_empleados";
    $tbl_contratos = "tbl_contratos";
    $tbl_detalles_contrato = "tbl_detalle_contrato";

    $nombre = trim($_POST['nombre']);
    $edad = trim($_POST['edad']);
    $sexo = trim($_POST['sexo']);
    $telefono = trim($_POST['telefono']);
    $cargo = trim($_POST['cargo']);

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
    if ($resultado && $resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $contrato[] = $row;
        }
    }
    return $contrato;
}