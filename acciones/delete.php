<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once("../config/config.php");

    // Leer el cuerpo de la solicitud JSON
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    if (is_array($data)) {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $avatarName = isset($data['avatar']) ? trim($data['avatar']) : '';

        if ($id <= 0) {
            echo json_encode(["success" => false, "message" => "ID inválido"]);
            exit;
        }

        try {
            $stmt = $conexion->prepare("DELETE FROM tbl_empleados WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() > 0) {
                // Eliminar archivo si existe
                $dirLocal = "fotos_empleados";
                $filePath = $dirLocal . '/' . $avatarName;

                if (!empty($avatarName) && file_exists($filePath)) {
                    unlink($filePath);
                }

                echo json_encode([
                    "success" => true,
                    "message" => "Empleado eliminado correctamente"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "No se encontró ningún empleado con ese ID"
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error al eliminar el empleado: " . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Datos JSON inválidos o vacíos"
        ]);
    }
}
?>
