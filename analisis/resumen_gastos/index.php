<style>
    .list-group {
        list-style-type: none;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    .list-group li {
        border-bottom: 1px solid #ddd;
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        /* Cambia el cursor para indicar que es clickeable */
        transition: background-color 0.2s;
    }

    .list-group li:hover {
        background-color: #f5f5f5;
    }

    .list-group li:last-child {
        border-bottom: none;
        font-weight: bold;
        cursor: default;
        /* El total no es clickeable */
    }

    .list-group li:last-child:hover {
        background-color: inherit;
    }

    .list-group li:nth-of-type(2n) {
        background: hsl(218, 100%, 85%);
    }

    .list-group li.active {
        background-color: #d4edff;
        font-weight: bold;
    }

    .list-group span {
        font-weight: bold;
        text-transform: capitalize;
    }

    /* Estilos para el total */
    .list-group li.Total {
        background-color: #f0f0f0;
        font-size: 1.2em;
    }

    /* Estilos para los detalles */
    .detalle-gasto {
        margin-top: 10px;
        padding-left: 20px;
        border-left: 3px solid #007bff;
    }

    .detalle-item {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
    }
</style>

<div class="d-flex justify-content-between mb-2">
    <select name="fecha" id="fecha" onchange="cargaData(event)"></select>
</div>
<h6>RESUMEN GASTOS</h6>
<ul id="resumenGastos" class="list-group"></ul>

<h6 class="mt-3">DETALLES DEL GASTO</h6>
<div id="detallesGastoContainer" style="display: none;">
    <h5 id="tituloDetalleGasto" class="mb-3"></h5>
    <div id="detallesGasto" class="list-group"></div>
</div>

<script>
    let gastoSeleccionado = null;
    let datosResumen = [];

    function cargaData() {
        resumenGastos.innerHTML = "";
        document.getElementById("detallesGastoContainer").style.display = "none";
        gastoSeleccionado = null;

        const select = document.getElementById("fecha");
        const selectedOption = select.options[select.selectedIndex];
        let selectedOptionId = selectedOption.id;
        let mes = selectedOptionId.split("-")[0];
        let anio = selectedOptionId.split("-")[1];
        let sumData = 0;

        $.post("../analisis/conexiones.php", {
            ingresar: "getResumenGastos",
            mes: mes,
            anio: anio
        }).done(function(data) {
            datosResumen = JSON.parse(data);
            datosResumen.forEach(element => {
                const li = document.createElement("li");
                li.innerHTML = `<span>${element.descripcion}:</span> ${formatoMoneda(element.total_gasto)}`;
                li.dataset.descripcion = element.descripcion;
                li.addEventListener("click", function() {
                    mostrarDetallesGasto(element.descripcion, mes, anio);
                });
                resumenGastos.appendChild(li);

                const numericValue = parseInt(element.total_gasto.trim(), 10);
                sumData += numericValue;
            });

            // Agregar el total (no clickeable)
            const liTotal = document.createElement("li");
            liTotal.className = "Total";
            liTotal.innerHTML = `<span>Total:</span> ${formatoMoneda(sumData)}`;
            resumenGastos.appendChild(liTotal);

        }).fail(function(error) {
            console.log(error);
        });
    }

    function mostrarDetallesGasto(gasto, mes, anio) {
        // Resaltar el elemento seleccionado
        const items = document.querySelectorAll("#resumenGastos li");
        items.forEach(item => {
            item.classList.remove("active");
            if (item.dataset.descripcion === gasto) {
                item.classList.add("active");
            }
        });

        // Mostrar el contenedor de detalles
        const container = document.getElementById("detallesGastoContainer");
        container.style.display = "block";
        document.getElementById("tituloDetalleGasto").textContent = gasto;

        // Cargar los detalles
        $.post("../analisis/conexiones.php", {
            ingresar: "getItemDetails",
            gasto: gasto,
            mes: mes,
            anio: anio
        }).done(function(data) {
            const detalles = JSON.parse(data);
            const detallesContainer = document.getElementById("detallesGasto");
            detallesContainer.innerHTML = "";

            if (detalles.length === 0) {
                detallesContainer.innerHTML = "<li>No hay detalles disponibles para este gasto</li>";
                return;
            }

            detalles.forEach(element => {
                const div = document.createElement("div");
                div.className = "detalle-item";
                div.innerHTML = `<span>${element.descripcion}:</span> ${element.cantidad}`;
                detallesContainer.appendChild(div);
            });
        }).fail(function(error) {
            console.log(error);
        });
    }

    function formatoMoneda(valor) {
        return new Intl.NumberFormat("es-CL", {
            style: "currency",
            currency: "CLP",
        }).format(valor);
    }
</script>