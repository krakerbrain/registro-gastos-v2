<?php
include __DIR__ . '/../config.php';
session_start();

if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
}
$ingresar = $_REQUEST['ingresar'];


/**Se obtiene el id del usuario según el nombre que use al iniciar sesión */
$query = $con->prepare("SELECT id FROM users WHERE name = :usuario");
$query->bindParam(':usuario', $usuario);
$query->execute();
while ($datos = $query->fetch()) {
    $idusuario = $datos[0];
};


switch ($ingresar) {
    case 'getGastos':
        try {

            $query = $con->prepare("SELECT descripcion
                                    FROM tipo_gastos
                                    WHERE idusuario = :idusuario
                                    ORDER BY descripcion ASC
                                ");
            $query->bindParam(':idusuario', $idusuario);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $json_result = json_encode($result);
            echo $json_result;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case 'getDetalles':
        $idGasto = $_POST['idGasto'];
        $descripcion = $_POST['descripcion'];
        try {

            $query = $con->prepare("SELECT id,descripcion FROM descripcion_gastos
                                    WHERE idusuario = :idusuario
                                    AND tipo_gasto_id = :idGasto
                                    AND seleccionada = 0
                                    AND descripcion LIKE :descripcion
                                    ORDER BY descripcion ASC;
                                    ");
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':idGasto', $idGasto);
            $query->bindValue(':descripcion', $descripcion . '%');
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $json_result = json_encode($result);
            echo $json_result;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case 'actualizaDetalles':
        $idDetalle = $_POST['idDetalle'];
        $seleccionado = $_POST['seleccionado'];

        try {
            $query = $con->prepare("UPDATE descripcion_gastos set seleccionada = :seleccionado where idusuario = :idusuario and id = :idDetalle");
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':idDetalle', $idDetalle);
            $query->bindParam(':seleccionado', $seleccionado);
            $query->execute();
            if ($query->rowCount() > 0) {
                echo 1;
            } else {
                echo 0;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case 'resetSeleccionados':

        try {
            $query = $con->prepare("UPDATE descripcion_gastos set seleccionada = 0 where idusuario = :idusuario");
            $query->bindParam(':idusuario', $idusuario);
            $query->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        break;

    case 'getDetalleGastosSeleccionados':
        $query =  $con->prepare("SELECT dg.id,dg.descripcion FROM descripcion_gastos dg where dg.idusuario = :idusuario and dg.seleccionada = 1");
        $query->bindParam(':idusuario', $idusuario);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $json_result = json_encode($result);
        echo $json_result;
        break;
    case 'agregaDetalle':
        $tipoGasto = $_POST['tipoGasto'];
        $descripcion = $_POST['descripcion'];
        $seleccionada = $_POST['seleccionada'];

        try {
            // verificamos si existe la descripcion del usuario y del tipo de gasto
            $query = $con->prepare("SELECT count(*) FROM descripcion_gastos WHERE idusuario = :idusuario and tipo_gasto_id = :tipoGasto and descripcion like :descripcion");
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':tipoGasto', $tipoGasto);
            $query->bindParam(':descripcion', $descripcion);
            $query->execute();
            $result = $query->fetch();
            $total = $result[0];
            if ($total > 0) {
                echo "Error en la inserción: Descripcion ya existe";
            } else {
                $query = $con->prepare("INSERT INTO descripcion_gastos (idusuario, tipo_gasto_id, descripcion, seleccionada,created_at,updated_at) VALUES (:idusuario, :tipoGasto, :descripcion, :seleccionada,now(),now())");
                $query->bindParam(':idusuario', $idusuario);
                $query->bindParam(':tipoGasto', $tipoGasto);
                $query->bindParam(':descripcion', $descripcion);
                $query->bindParam(':seleccionada', $seleccionada);
                if ($query->execute()) {
                    echo "";
                } else {
                    echo "Error en la inserción: " . $query->errorInfo();
                }
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case 'insertarGasto':
        $monto = $_POST['monto'];
        $tipoGasto = $_POST['tipoGasto'];
        $idGasto = $_POST['idGasto'];
        $detallesId = $_POST['detallesId'];
        $editar = $_POST['editar'];
        $fecha = $_POST['fechaEdita'];

        try {
            $con->beginTransaction();
            if ($editar) {
                $query = $con->prepare("DELETE FROM gastos WHERE idusuario = :idusuario and gastos.id = :idGasto");
                $query->bindParam(':idusuario', $idusuario);
                $query->bindParam(':idGasto', $idGasto);
                $query->execute();
            }
            // Verifica si $fecha está vacía
            if (empty($fecha)) {
                $fechaSQL = date('Y-m-d H:i:s');
            } else {
                $fechaSQL = $fecha; // Usa la fecha tal como está
            }

            $query = $con->prepare("INSERT INTO gastos (idusuario, monto_gasto, tipo_gasto_id, created_at, updated_at) VALUES (:idusuario, :monto, :tipoGasto, :fecha, :fecha)");
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':monto', $monto, PDO::PARAM_INT);
            $query->bindParam(':tipoGasto', $tipoGasto);
            $query->bindParam(':fecha', $fechaSQL);


            if ($query->execute()) {
                $gasto_id = $con->lastInsertId();
                foreach ($detallesId as $detalleId) {
                    $query = $con->prepare("INSERT INTO descripcion_gasto_gasto (gasto_id, descripcion_gasto_id, created_at, updated_at) VALUES (:gasto_id, :descripcion_gasto_id, :fecha, :fecha)");
                    $query->bindParam(':gasto_id', $gasto_id);
                    $query->bindParam(':descripcion_gasto_id', $detalleId);
                    $query->bindParam(':fecha', $fechaSQL);
                    $query->execute();
                }

                $con->commit(); // Confirma la transacción si todo sale bien.
                $response['success'] = true;
                $response['message'] = "Operación completada con éxito";
            } else {
                throw new Exception("Error al insertar en la tabla 'gastos'.");
            }
        } catch (Exception $e) {
            $con->rollBack(); // Deshace la transacción en caso de error.
            $response['success'] = false;
            $response['message'] = "Error: " . $e->getMessage();
        }
        // Envía la respuesta al cliente.
        header('Content-Type: application/json');
        echo json_encode($response);
        break;
    case 'insertaTipoGasto':

        $tipoGastoDescripcion = $_POST['tipoGasto'];

        $query = $con->prepare("INSERT INTO tipo_gastos (idusuario, descripcion, created_at, updated_at) VALUES (:idusuario, :descripcion, now(), now())");
        $query->bindParam(':idusuario', $idusuario);
        $query->bindParam(':descripcion', $tipoGastoDescripcion);
        $query->execute();

        echo json_encode($con->lastInsertId());


        break;

    default:
        # code...
        break;
}
