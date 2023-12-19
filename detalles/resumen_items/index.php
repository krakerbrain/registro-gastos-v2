<style>
.input-gastos {
    position: relative;
}

#gastoInput {
    padding-right: 25px;
    /* Ajusta el valor según el ancho del icono y el espaciado que desees */
}


#borrarTextoGasto {
    position: absolute;
    top: 50%;
    right: 55px;
    /* Ajusta el valor según el espaciado que desees */
    transform: translateY(-50%);
    cursor: pointer;
    display: none;
}
</style>
<h6 class="mt-3">RESUMEN DETALLES GASTOS</h6>
<div class="input-gastos">
    <input class="w-100 form-control my-3" type="text" id="gastoInput" name="gastoInput" placeholder="Tipo Gasto"
        aria-label="Tipo Gasto" list="gastos" oninput="muestraX('borrarTextoGasto',this)" onchange="buscar(event)" />
    <i id="borrarTextoGasto" class="fas fa-times text-danger" onclick="eliminaTexto('gastoInput',this)"></i>
</div>
<datalist id="gastos"></datalist>
<ul id="resumenItems" class="list-group"></ul>
<script>
function buscar(event) {
    resumenItems.innerHTML = "";
    let gasto = event.target.value
    const select = document.getElementById("fecha");
    const selectedOption = select.options[select.selectedIndex];
    let selectedOptionId = selectedOption.id;
    let mes = selectedOptionId.split("-")[0];
    let anio = selectedOptionId.split("-")[1];
    $.post("../detalles/conexiones.php", {
        ingresar: "getItemDetails",
        gasto: gasto,
        mes: mes,
        anio: anio
    }).done(function(data) {
        let datos = JSON.parse(data);
        datos.forEach(element => {
            resumenItems.innerHTML +=
                `<li><span>${element.descripcion}:</span>  ${element.cantidad}</li>`
        })
    }).fail(function(error) {
        console.log(error)
    });
}

function listaGastos() {
    $.post("../form/conexiones.php", {
        ingresar: "getGastos",
    }).done(function(data) {
        let datos = JSON.parse(data);
        datos.forEach(element => {
            document.getElementById("gastos").innerHTML += `
                <option value=${element.descripcion}>
                `
        })
    }).fail(function(error) {
        console.log(error)
    });
}

function muestraX(id, element) {
    if (document.getElementById(element.id).value == "") {
        document.getElementById(id).style.display = "none";
    } else {
        document.getElementById(id).style.display = "block";
    }
}

function eliminaTexto(id, element) {
    document.getElementById(id).value = "";
    document.getElementById(element.id).style.display = "none";
    resumenItems.innerHTML = "";
}
</script>