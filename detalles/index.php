<?php
session_start();
$sesion = isset($_SESSION['usuario']);
require '../config.php';
$indice = "detalles";
if ($sesion == null || $sesion == "") {
    header($_ENV['URL_LOCAL']);
}
include "../partials/header.php";
?>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php include "../partials/navbar.php" ?>
            <?php include "resumen_gastos/index.php" ?>
            <?php include "resumen_items/index.php" ?>
        </div>
    </div>
</div>

<script>
    window.onload = function() {

        cargaMeses();
        listaGastos();


    }
</script>
<?php

include "../partials/footer.php";
?>