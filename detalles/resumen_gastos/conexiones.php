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
    case 'getResumenItems':
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

    default:
        # code...
        break;
}
