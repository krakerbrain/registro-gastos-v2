<?php
if ($tipoFormulario != "Editar") {
?>
<style>
.ellipsis {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
<fieldset style="all:revert;" class="mb-3">
    <legend style="all:revert">Gastos más frecuentes</legend>
    <div class="row justify-content-around" id="gastosFrecuentes"></div>
</fieldset>


<script>
function btnMasFrecuente() {
    $.post("./gastos_frecuentes/conexiones.php", {
        ingresar: "getGastosFrecuentes",
    }).done(function(data) {
        let datos = JSON.parse(data);
        datos.forEach(element => {
            document.getElementById("gastosFrecuentes").innerHTML += `
                <div class="col-4">
                    <button style="width:100%"
                            id="btn-${element.id}" 
                            class="btn btn-primary btn-sm mb-1 ellipsis"
                            title="${element.descripcion}" 
                            onclick="agregaDescripcion(${element.id},'${element.descripcion}')">${element.descripcion}</button>
                </div>
                `
        })
    }).fail(function(error) {
        console.log(error)
    });
}
</script>
<?php } ?>