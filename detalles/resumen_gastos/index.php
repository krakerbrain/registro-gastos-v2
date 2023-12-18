<style>
    #resumenItems {
        list-style-type: none;
        padding: 0;
        margin: 0;
        font-family: Arial, sans-serif;
    }

    #resumenItems li {
        border-bottom: 1px solid #ddd;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #resumenItems li:last-child {
        border-bottom: none;
        font-weight: bold;
    }

    #resumenItems li:nth-of-type(2n) {
        background: hsl(218, 100%, 85%);
    }

    /* Estilos para los nombres y valores */
    #resumenItems li span {
        font-weight: bold;
    }

    /* Estilos para el total */
    #resumenItems li.Total {
        background-color: #f0f0f0;
        font-size: 1.2em;
    }

    #resumenItems span {
        text-transform: capitalize;
        font-weight: 300;
    }
</style>

<div class="d-flex justify-content-between mb-1">
    <select name="fecha" id="fecha" onchange="cargaResumenGastos(event)"></select>
</div>
<ul id="resumenItems"></ul>

<script>
    let mesesArray = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre",
        "Noviembre", "Diciembre"
    ];

    function cargaMeses() {
        $.post("../tabla_gastos/tabla/conexiones.php", {
            ingresar: "getFecha"
        }).done(function(data) {
            let datos = JSON.parse(data);
            let select = document.getElementById("fecha");
            datos.forEach(element => {
                let fechaValue = `${mesesArray[element.mes-1]}, ${element.anio}`;
                select.innerHTML +=
                    `<option id="${element.mes}-${element.anio}" value="${fechaValue}">${fechaValue}</option>`;
            })
            cargaResumenGastos();
        }).fail(function(error) {
            console.log(error)
        })
    }

    function cargaResumenGastos() {
        resumenItems.innerHTML = "";
        const select = document.getElementById("fecha");
        const selectedOption = select.options[select.selectedIndex];
        let selectedOptionId = selectedOption.id;
        let mes = selectedOptionId.split("-")[0];
        let anio = selectedOptionId.split("-")[1];
        let sumData = 0;
        $.post("./resumen_gastos/conexiones.php", {
            ingresar: "getResumenItems",
            mes: mes,
            anio: anio
        }).done(function(data) {
            let datos = JSON.parse(data);
            datos.forEach(element => {
                resumenItems.innerHTML +=
                    `<li><span>${element.descripcion}:</span>  ${formatoMoneda(element.total_gasto)}</li>`
                const numericValue = parseInt(element.total_gasto.trim(), 10);
                sumData += numericValue;
            })
            resumenItems.innerHTML += `<li><span>Total:</span> ${formatoMoneda(sumData)}</li>`

        }).fail(function(error) {
            console.log(error)
        })
    }

    const formatoMoneda = moneda => Math.round(moneda).toLocaleString('es-CL', {
        style: 'currency',
        currency: 'CLP'
    });
</script>