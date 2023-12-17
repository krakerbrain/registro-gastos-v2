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
    case 'getDetallesGastos':
        $idGasto = $_POST['idGasto'];
        try {

            $query = $con->prepare("SELECT dg.id,dg.descripcion 
                                    FROM descripcion_gasto_gasto  dgg
                                    join descripcion_gastos dg
                                    on dgg.descripcion_gasto_id = dg.id
                                    where dg.idusuario = :idusuario
                                    and dg.tipo_gasto_id = :idGasto
                                    and dg.seleccionada = 0
                                    group by dgg.descripcion_gasto_id 
                                    order by count(*) desc 
                                    limit 12;
                                ");
            $query->bindParam(':idusuario', $idusuario);
            $query->bindParam(':idGasto', $idGasto);
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
