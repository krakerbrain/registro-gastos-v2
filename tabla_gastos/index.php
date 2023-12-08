<?php
session_start();
$sesion = isset($_SESSION['usuario']);
require '../config.php';
$indice = "estadisticas";
if ($sesion == null || $sesion == "") {
    header($_ENV['URL_LOCAL']);
}
include "../partials/header.php";

?>

<body>
    <style>
    .titleTabla {
        cursor: pointer;
        /* Cambia el cursor a una mano para indicar que es interactivo */
        color: blue;
        /* Cambia el color del texto */
        text-decoration: underline;
        /* Añade un subrayado para indicar que es un enlace */
    }

    .titleTabla:hover {
        color: red;
        /* Cambia el color del texto al pasar el ratón sobre el elemento */
    }
    </style>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php include "../partials/navbar.php" ?>
                <?php include "tabla/index.php" ?>


            </div>
        </div>
    </div>

    <script>
    window.onload = function() {

        cargaMeses();
        resetDetallesSeleccionados();

    }

    function resetDetallesSeleccionados() {
        $.post("../form/conexiones.php", {
            ingresar: "resetSeleccionados",
        }).fail(function(error) {
            console.log(error);
        });
    }
    </script>

    <?php include "../partials/boostrap_script.php"; ?>

</body>