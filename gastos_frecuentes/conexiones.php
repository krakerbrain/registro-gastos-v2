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
    case 'getGastosFrecuentes':
        try {

            $query = $con->prepare("SELECT tg.id, tg.descripcion, COUNT(*) as total
                                FROM tipo_gastos tg
                                JOIN gastos g ON tg.id = g.tipo_gasto_id
                                WHERE g.idusuario = :idusuario
                                GROUP BY tg.id, tg.descripcion
                                ORDER BY total DESC
                                LIMIT 6;
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
    default:
        # code...
        break;
}
