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
    case 'getResumenGastos':
        $mes = $_POST['mes'];
        $anio = $_POST['anio'];

        $query = $con->prepare("SELECT
        g.tipo_gasto_id,
            tg.descripcion,
            SUM(g.monto_gasto) AS total_gasto
        FROM
            gastos g
        JOIN
            tipo_gastos tg ON g.idusuario = tg.idusuario
                          AND g.tipo_gasto_id = tg.id
        WHERE
            g.idusuario = :idusuario
            AND MONTH(g.created_at) = :mes 
            AND YEAR(g.created_at) = :anio
        GROUP BY tg.descripcion");
        try {
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':mes', $mes);
            $query->bindParam(':anio', $anio);
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]);
        }
        break;
    case 'getItemDetails':
        $gasto = $_POST['gasto'];
        $mes = $_POST['mes'];
        $anio = $_POST['anio'];

        $query = $con->prepare("SELECT dg.descripcion,
                                       Count(dgg.descripcion_gasto_id) AS cantidad
                                FROM   descripcion_gastos dg
                                        JOIN tipo_gastos tg
                                            ON tg.id = dg.tipo_gasto_id
                                        JOIN descripcion_gasto_gasto dgg
                                            ON dgg.descripcion_gasto_id = dg.id
                                WHERE  dg.idusuario = :idusuario
                                       AND tg.descripcion = :gasto
                                       AND MONTH(dgg.created_at) = :mes 
                                       AND YEAR(dgg.created_at) = :anio
                                GROUP  BY dg.descripcion 
                                ORDER  BY cantidad DESC");
        try {
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':gasto', $gasto);
            $query->bindParam(':mes', $mes);
            $query->bindParam(':anio', $anio);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]);
        }
        break;

    default:
        # code...
        break;
}
