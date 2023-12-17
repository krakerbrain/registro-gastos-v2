<link rel="stylesheet" href="tabla/tabla-estilos.css">
<div class="d-flex justify-content-between mb-1">
    <select name="fecha" id="fecha"></select>
    <span id="totalMes"></span>
</div>
<div class="filtros mb-1">
    <select name="ordenarPor" id="ordenarPor" onchange="ordenarPor(event)">
        <option value="reset">Ordenar Por</option>
        <option value="descripcion asc">Gasto A-Z</option>
        <option value="descripcion desc">Gasto Z-A</option>
        <option value="monto_gasto asc">Monto Menor a Mayor</option>
        <option value="monto_gasto desc">Monto Mayor a Menor</option>
        <option value="created_at asc">Fecha Más reciente</option>
        <option value="created_at desc">Fecha Más antigua</option>
    </select>
</div>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th scope="col" class="text-center" onclick="ordenar(event, 'descripcion')">
                    <span class="titleTabla">Gasto</span>
                    <i id="i-descripcion" class="fa-solid"></i>
                </th>
                <th scope="col" class="text-center" onclick="ordenar(event, 'monto_gasto')">
                    <span class="titleTabla">Monto Gasto</span>
                    <i id="i-monto_gasto" class="fa-solid"></i>
                </th>
                <th scope="col" class="text-center" onclick="ordenar(event, 'created_at')">
                    <span class="titleTabla">Fecha</span>
                    <i id="i-created_at" class="fa-solid fa-chevron-down"></i>
                </th>
                <th scope="col" class="text-center acciones">Acciones</th>
            </tr>
        </thead>
        <tbody id="gastos"></tbody>
    </table>
</div>
<input type="hidden" name="ordenColumnas" id="ordenColumnas" value="">
<input type="hidden" name="columnaActiva" id="columnaActiva" value="">
<div id="showModal"></div>

<script>
let mesesArray = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre",
    "Noviembre", "Diciembre"
];

document.getElementById("fecha").addEventListener("change", function() {
    let fecha = event.currentTarget.selectedOptions[0].id;
    cargaData(fecha);
})

function cargaMeses() {
    $.post("./tabla/conexiones.php", {
        ingresar: "getFecha"
    }).done(function(data) {
        let datos = JSON.parse(data);
        let select = document.getElementById("fecha");
        datos.forEach(element => {
            let fechaValue = `${mesesArray[element.mes-1]}, ${element.anio}`;
            select.innerHTML +=
                `<option id="${element.mes}-${element.anio}" value="${fechaValue}">${fechaValue}</option>`;
        })
        cargaData();
    }).fail(function(error) {
        console.log(error)
    })
}

function ordenarPor(event) {

    event.preventDefault();
    let value = event.target.value;
    columnaActiva.value = value.split(" ")[0];
    ordenColumnas.value = value.split(" ")[1] == undefined ? "desc" : value.split(" ")[1];
    let columna = value.split(" ")[0] == "reset" ? "created_at" : value.split(" ")[0];
    cargaData(undefined, columna);
}

function cargaData(selectedDate, columna) {
    const select = document.getElementById("fecha");
    const selectedOption = select.options[select.selectedIndex];
    let selectedOptionId = selectedDate == undefined ? selectedOption.id : selectedDate;
    let column = columna == undefined ? 'created_at' : columna;
    let ordenColumna = document.getElementById("ordenColumnas");

    cambiaIconoAscDesc(column, ordenColumna.value)
    $.post("./tabla/conexiones.php", {
        ingresar: "getData",
        fechaValue: selectedOptionId,
        columna: column,
        ordenColumna: ordenColumna.value
    }).done(function(data) {
        let datos = JSON.parse(data);
        let tbody = document.getElementById("gastos");
        tbody.innerHTML = "";
        datos.forEach(element => {
            tbody.innerHTML += `<tr id="gasto-${element.id}">
                <td data-cell="gasto" class="data align-baseline" >${element.descripcion}</td>
                <td data-cell="monto gasto" class="data align-baseline nowrap">${formatoMoneda(element.monto_gasto)}</td>
                <td data-cell="fecha" class="data align-baseline">${element.fecha}</td>
                <td class="d-flex align-items-center justify-content-around">
                                    <a href="#" id="despliegaDesc" onclick="despliegaDesc(event,'gasto-${element.id}')" title="Descripcion">
                                        <i id="icono-ver-gasto-${element.id}" class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="#" id="editaGasto" title="Editar" onclick="editarGasto(this)" data-monto="${element.monto_gasto}" data-fecha="${element.fecha}" data-descripcion="${element.descripcion}" data-idTipoGasto="${element.idTipoGasto}" data-idGasto="${element.id}" data-creado="${element.creado}">
                                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                                    </a>
                                    <a href="#" id="eliminaGasto" title="Eliminar" data-toggle="modal" data-target="#eliminaGastoModal" onclick="creamodal('${element.id}', '${element.descripcion}')"><i class="fa-regular fa-trash-can text-primary"></i></a>
                                
                                </td>
                </tr>`
        })
        totalMes(selectedOptionId);
    }).fail(function(error) {
        console.log(error)
    });
}



function ordenar(event, columna) {
    event.preventDefault();
    let orden = document.getElementById("ordenColumnas");
    let columnaActiva = document.getElementById("columnaActiva");

    if (orden.value == "") {
        orden.value = columna != "created_at" ? "asc" : "desc";
    } else if (orden.value == "asc") {
        orden.value = "desc";
    } else {
        if (columna != "created_at") {
            orden.value = "";
        } else {
            orden.value = orden.value == "asc" ? "desc" : "asc";
        }
    }

    if (columna != columnaActiva.value && columnaActiva.value != "") {
        if (columna != "created_at") {
            orden.value = "asc";
        } else {
            orden.value = orden.value == "asc" ? "desc" : "asc";
        }
    }
    columnaActiva.value = columna;
    columna = orden.value == "" ? "created_at" : columna;
    cargaData(undefined, columna);
}

function cambiaIconoAscDesc(columna, orden) {
    let element = document.getElementById("i-" + columna);
    let fecha = document.getElementById("i-created_at");
    if (orden == "asc") {
        if (columna != "created_at") {
            fecha.classList.remove(...fecha.classList);
        }
        if (columna == "created_at") {
            element.classList.remove("fa-chevron-down");
        }
        element.classList.add("fa-chevron-up");
    } else if (orden == "desc") {
        if (columna != "created_at") {
            fecha.classList.remove(...fecha.classList);
        }
        if (columna == "created_at") {
            element.classList.remove("fa-chevron-up");
        }
        element.classList.add("fa-chevron-down");
    } else {
        let columnaActiva = document.getElementById("columnaActiva").value;
        element = document.getElementById("i-" + columnaActiva);
        if (columnaActiva != "") {
            element.classList.remove(...element.classList);
            element.classList.add("fa-solid");
            fecha.classList.add("fa-solid");
            fecha.classList.add("fa-chevron-down");
        }
    }
}

function editarGasto(element) {
    // Recupera los valores de los atributos data-*
    const monto = element.getAttribute('data-monto');
    const fecha = element.getAttribute('data-creado');
    const descripcion = element.getAttribute('data-descripcion');
    const idTipoGasto = element.getAttribute('data-idTipoGasto');
    const idGasto = element.getAttribute('data-idGasto');

    $.post("./tabla/conexiones.php", {
        ingresar: "actualizaDetalles",
        idGasto: idGasto
    }).done(function(data) {
        let result = JSON.parse(data);
        if (result) {
            window.location.href =
                `${"<?php echo $_ENV['URL_INICIO']; ?>"}?tipoForm=Editar&montoGasto=${monto}&fecha=${fecha}&descripcion=${descripcion}&idTipoGasto=${idTipoGasto}&idGasto=${idGasto}`;

        }

    }).fail(function(error) {
        console.log(error)
    });

}

function creamodal(idgasto, descripcion) {
    document.getElementById("showModal").innerHTML = `<div class="modal fade" id="eliminaGastoModal" tabindex="-1" role="dialog" aria-labelledby="eliminaGastoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminaGastoModalLabel">Eliminar Gasto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <span>El gasto ${descripcion} será eliminado. Está seguro?</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="eliminarGasto('${idgasto}')">Aceptar</button>
            </div>
        </div>
    </div>
</div>`
}

function eliminarGasto(idGasto) {
    $.post("./tabla/conexiones.php", {
        ingresar: "eliminaGasto",
        idGasto: idGasto
    }).done(function(data) {
        if (data != "") {
            console.log(data)
        } else {
            cargaMeses();
            $('#eliminaGastoModal').modal('hide');
        }
    }).fail(function(error) {
        console.log(error)
    });
}

async function despliegaDesc(event, id) {
    event.preventDefault();
    const descripciones = document.getElementById(`descripciones-${id}`);
    if (descripciones === null) {
        const tr_padre = document.getElementById(id);
        const nuevaFila = document.createElement('tr');
        nuevaFila.setAttribute('id', `descripciones-${id}`);
        const nuevoTd = document.createElement('td');
        nuevoTd.setAttribute('colspan', '4');
        nuevaFila.appendChild(nuevoTd);
        tr_padre.after(nuevaFila);
        modificaIcono(id, 'ver');
        nuevoTd.innerHTML = await obtenerDescripciones(id.split('-')[1]);
    } else {
        descripciones.remove();
        modificaIcono(id, 'ocultar');
    }
}

function modificaIcono(id, evento) {
    let icono = document.getElementById("icono-ver-" + id); // seleccionar el elemento por su id
    if (evento == "ver") {
        icono.classList.remove("fa-eye"); // eliminar la clase "fa-eye"
        icono.classList.add("fa-eye-slash");
    } else {
        icono.classList.remove("fa-eye-slash");
        icono.classList.add("fa-eye"); // eliminar la clase "fa-eye"
    }
}

function obtenerDescripciones(gasto_id) {
    return new Promise((resolve, reject) => {
        $.post("./tabla/conexiones.php", {
            ingresar: "getDescripciones",
            gasto_id: gasto_id
        }).done(function(data) {
            let datos = JSON.parse(data);
            let html =
                `<small style="font-weight:bold">Detalles del gasto:</small><br><span>${datos.join(", ")}</span>`;
            resolve(html);
        }).fail(function(error) {
            reject(error);
        });
    })
}

function totalMes(fecha) {
    $.post("./tabla/conexiones.php", {
        ingresar: "totalMes",
        fecha: fecha
    }).done(function(data) {
        document.getElementById("totalMes").textContent = `Total Mes ${formatoMoneda(data)}`;
    }).fail(function(error) {
        console.log(error)
    });

}

const formatoMoneda = moneda => Math.round(moneda).toLocaleString('es-CL', {
    style: 'currency',
    currency: 'CLP'
});
</script>