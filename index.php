<?php
session_start();
$sesion = isset($_SESSION['usuario']);
require __DIR__ . '/config.php';
require_once __DIR__ . '/config/ConfigUrl.php';
$baseUrl = ConfigUrl::get();

$indice = "inicio";

if (!$sesion) {
    header("Location: " . $baseUrl . 'login/index.php');
    exit;
}
include "partials/header.php";

$tipoFormulario = isset($_REQUEST['tipoForm']) ? $_REQUEST['tipoForm'] : "";
$montoGasto = isset($_REQUEST['montoGasto']) ?  intval($_REQUEST['montoGasto']) : "";
$descripcion = isset($_REQUEST['descripcion']) ? $_REQUEST['descripcion'] : "";
$idTipoGasto = isset($_REQUEST['idTipoGasto']) ? $_REQUEST['idTipoGasto'] : "";
$idGasto = isset($_REQUEST['idGasto']) ? $_REQUEST['idGasto'] : "";
$fecha = isset($_REQUEST['fecha']) ? $_REQUEST['fecha'] : "";

?>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php include "partials/navbar.php" ?>
                <?php include "gastos_frecuentes/index.php" ?>
                <?php include "form/index.php" ?>
            </div>
        </div>
    </div>
    <?php if ($tipoFormulario != "Editar") { ?>
        <script>
            let accion = "";
            window.onload = function() {
                btnMasFrecuente();
                listaGastos();
                resetDetallesSeleccionados();
            }
        </script>
    <?php } else { ?>
        <script>
            let idTipoGasto = <?= $idTipoGasto ?>;
            let descripcion = "<?= $descripcion ?>";
            accion = "<?= $tipoFormulario ?>";
            let fecha = "<?= $fecha ?>";
            window.onload = function() {
                agregaDescripcion(idTipoGasto, descripcion, "editar");
                listaGastos();
                muestraX();
                btnDetallesSeleccionados()
            }
        </script>
    <?php } ?>
    <?php include "partials/boostrap_script.php"; ?>

</body>