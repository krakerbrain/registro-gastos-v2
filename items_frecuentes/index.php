<!-- <fieldset style="all:revert;display:none" class="mb-3"> -->
<fieldset style="all:revert;display:none" class="mb-3" id="fieldsetDetalles">
    <legend style="all:revert">Items frecuentes</legend>
    <div id="detallesGastos"></div>
</fieldset>
<script>
    function btnDetalles(id) {
        idGasto = id == "" ? document.getElementById("tipoGastoId").value : id;
        $.post("./items_frecuentes/conexiones.php", {
            ingresar: "getDetallesGastos",
            idGasto: idGasto
        }).done(function(data) {
            let datos = JSON.parse(data);
            if (datos.length > 0) {
                showFieldsetDetalles("block");
                document.getElementById("detallesGastos").innerHTML = "";
                datos.forEach(element => {
                    document.getElementById("detallesGastos").innerHTML += `
                  
                <button "
                id="btnDg-${element.id}" 
                class="btn btn-primary btn-sm m-1"
                onclick="creaBotonesDetallesSeleccionados(${element.id},event,1)" 
                >${element.descripcion}</button>
                
                `
                })
            } else {
                showFieldsetDetalles("none");
            }
        }).fail(function(error) {
            console.log(error)
        });
    }

    function showFieldsetDetalles(event, element) {
        if (element != "borrarTextoMontoGasto") {
            document.getElementById("fieldsetDetalles").style.display = event;
        }
    }
</script>