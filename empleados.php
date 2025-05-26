<div class="table-responsive">
    <table class="table table-hover" id="table_empleados">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Edad</th>
                <th scope="col">Cargo</th>
                <th scope="col">Contrato</th>
                <th scope="col">Avatar</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($contratos as $empleado) { ?>
                <tr id="empleado_<?php echo $empleado['id']; ?>">
                    <th scope='row'><?php echo $empleado['id']; ?></th>
                    <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                    <td><?php echo $empleado['edad']; ?></td>
                    <td><?php echo htmlspecialchars($empleado['cargo']); ?></td>
                    <td><?php 
                        $tipoContrato = isset($empleado['tipo_contrato']) ? $empleado['tipo_contrato'] : 'Sin asignar';
                        echo htmlspecialchars($tipoContrato); 
                    ?></td>
                    <td>
                        <?php
                        $avatar = $empleado['avatar'];
                        if (empty($avatar) || $avatar == '' || $avatar == 'sin-foto.jpg') {
                            $avatar = 'assets/imgs/sin-foto.jpg';
                        } else {
                            $avatar = "acciones/fotos_empleados/" . $avatar;
                            // Verificar si el archivo existe, si no, usar imagen por defecto
                            if (!file_exists($avatar)) {
                                $avatar = 'assets/imgs/sin-foto.jpg';
                            }
                        }
                        ?>
                        <img class="rounded-circle" src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo htmlspecialchars($empleado['nombre']); ?>" width="50" height="50" onerror="this.src='assets/imgs/sin-foto.jpg';">
                    </td>
                    <td>
                        <a title="Agregar contrato al empleado" href="#" onclick="modalRegistrarContrato(<?php echo $empleado['id']; ?>, '<?php echo htmlspecialchars($empleado['nombre'], ENT_QUOTES); ?>')" class="btn btn-primary">
                            <i class="bi bi-file-text"></i>
                        </a>
                        <a title="Ver detalles del empleado" href="#" onclick="verDetallesEmpleado(<?php echo $empleado['id']; ?>)" class="btn btn-success">
                            <i class="bi bi-binoculars"></i>
                        </a>
                        <a title="Editar datos del empleado" href="#" onclick="editarEmpleado(<?php echo $empleado['id']; ?>)" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a title="Eliminar datos del empleado" href="#" onclick="eliminarEmpleado(<?php echo $empleado['id']; ?>, '<?php echo htmlspecialchars($empleado['avatar'], ENT_QUOTES); ?>')" class="btn btn-danger">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>