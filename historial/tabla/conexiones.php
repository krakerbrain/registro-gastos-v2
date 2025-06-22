<?php
require  '../../config.php';
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
    case 'getFecha':
        try {

            $query = $con->prepare("SELECT LPAD(MONTH(updated_at), 2, '0') AS mes, YEAR(updated_at) AS anio
                                    FROM gastos
                                    WHERE idusuario = :idusuario
                                    GROUP BY LPAD(MONTH(updated_at), 2, '0'), anio
                                    ORDER BY anio ASC, mes DESC;");
            $query->bindParam(':idusuario', $idusuario);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $json_result = json_encode($result);
            echo $json_result;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        # code...
        break;
    case 'getData':
        $fecha = $_POST['fechaValue'];
        $columna = $_POST['columna'];
        $ordenColumna = $_POST['ordenColumna'] == '' ? 'desc' : $_POST['ordenColumna'];
        $sufijoOrden = $columna == 'descripcion' ? 'tg' : 'g';
        try {
            $query = $con->prepare("SELECT g.id,tg.id as idTipoGasto,tg.descripcion, g.monto_gasto, DATE_FORMAT(g.created_at, '%d/%m') as fecha, g.created_at as creado
            FROM gastos g
            JOIN tipo_gastos tg 
            ON tg.id = g.tipo_gasto_id
            WHERE g.idusuario = :idusuario
            AND DATE_FORMAT(g.created_at, '%m-%Y') = :fecha
            ORDER BY {$sufijoOrden}.{$columna} {$ordenColumna};");
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':fecha', $fecha);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            $json_result = json_encode($result);
            echo $json_result;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case 'getDescripciones':
        $gasto_id = $_POST['gasto_id'];

        try {
            $query = $con->prepare("SELECT dg.descripcion FROM descripcion_gastos dg
                JOIN descripcion_gasto_gasto dgg
                ON dg.id = dgg.descripcion_gasto_id
                WHERE dg.idusuario = :idusuario
                AND dgg.gasto_id = :gasto_id");
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':gasto_id', $gasto_id);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            $descripciones = array(); // Crea un nuevo array para almacenar las descripciones.

            foreach ($result as $row) {
                $descripciones[] = $row['descripcion']; // Agrega cada descripción al nuevo array.
            }

            // Devuelve el array de descripciones.
            echo json_encode($descripciones);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case 'actualizaDetalles':
        $idGasto = $_POST['idGasto'];

        try {
            $query = $con->prepare("SELECT descripcion_gasto_id FROM descripcion_gasto_gasto WHERE gasto_id = :idGasto");
            $query->bindParam(':idGasto', $idGasto);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            if (empty($result)) {
                echo json_encode(false);
            } else {
                foreach ($result as $row) {
                    $descripcionGastoId = $row['descripcion_gasto_id'];
                    $updateQuery = $con->prepare("UPDATE descripcion_gastos SET updated_at = NOW(), seleccionada = 1 WHERE idusuario = :idusuario AND id = :descripcionGastoId");
                    $updateQuery->bindParam(':idusuario', $idusuario);
                    $updateQuery->bindParam(':descripcionGastoId', $descripcionGastoId);
                    $updateQuery->execute();
                }
                echo json_encode(true);
            }
        } catch (PDOException $e) {
            // Manejar el error de la consulta
            echo "Error en la consulta SQL: " . $e->getMessage();
        }

        break;
    case 'eliminaGasto':
        try {

            $idGasto = $_POST['idGasto'];
            $query = $con->prepare("DELETE FROM gastos WHERE idusuario = :idusuario and id = :idGasto");
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':idGasto', $idGasto);
            $query->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        break;
    case 'totalMes':
        try {
            $fecha = $_POST['fecha'];
            $mes = substr($fecha, 0, 2);
            $anio = substr($fecha, 3);
            $query = $con->prepare("SELECT SUM(gastos.monto_gasto) AS total_gastos FROM gastos WHERE gastos.idusuario = :idusuario AND MONTH(gastos.updated_at) = $mes AND YEAR(gastos.updated_at) = $anio");
            $query->bindParam(':idusuario', $idusuario);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $total_gastos = $result['total_gastos'];
            } else {
                $total_gastos = null;
            }

            echo $total_gastos;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        break;
    default:
        # code...
        break;
}