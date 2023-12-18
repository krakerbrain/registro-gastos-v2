<style>
.input-container {
    position: relative;
}

#monto_gasto,
#tipoGasto {
    padding-right: 25px;
    /* Ajusta el valor según el ancho del icono y el espaciado que desees */
}

#borrarTextoMontoGasto,
#borrarTextoTipoGasto,
#add-button {
    position: absolute;
    top: 50%;
    right: 55px;
    /* Ajusta el valor según el espaciado que desees */
    transform: translateY(-50%);
    cursor: pointer;
    display: none;
}

#add-button {
    display: none;
    right: 0px;
}
</style>

<form action="" id="registrarGasto">
    <div class="input-container">

        <input class="w-100 form-control" type="number" name="monto_gasto" id="monto_gasto"
            value="<?= $montoGasto != "" ? $montoGasto : "" ?>" placeholder="Monto"
            oninput="muestraX('borrarTextoMontoGasto',this)">
        <i id="borrarTextoMontoGasto" class="fas fa-times text-danger" onclick="eliminaTexto('monto_gasto',this)"></i>
    </div>
    <div class="input-container">
        <input class="w-100 form-control my-3" type="text" id="tipoGasto" name="tipoGasto" placeholder="Tipo Gasto"
            value="<?= $descripcion != "" ? $descripcion : "" ?>" aria-label="Tipo Gasto" list="gastos"
            oninput="muestraX('borrarTextoTipoGasto',this)" onblur="agregaDescripcion(0,this.value)"
            <?= $descripcion != "" ? "disabled" : "" ?> />
        <i id="borrarTextoTipoGasto" class="fas fa-times text-danger" onclick="eliminaTexto('tipoGasto',this)"></i>
    </div>
    <datalist id="gastos"></datalist>
    <input type="hidden" name="" id="tipoGastoId" value="">
    <input type="hidden" name="" id="idGasto" value="<?= $idGasto ?>">
    <?php include "./items_frecuentes/index.php" ?>
    <div class="input-container">
        <input class="w-100 form-control" type="text" id="autocomplete-details" placeholder="Seleccionar Item" disabled>
        <button class="btn btn-primary" id="add-button" onclick="insertaNuevoDetalle(event)">Añadir</button>
    </div>
    <div class="input-container my-3" id="detallesSeleccionados"></div>
    <input class="btn btn-primary w-100 my-3" type="button"
        value="<?= $tipoFormulario == "" ? "Registrar" : $tipoFormulario ?> Gasto" id="btnRegistraGasto">
</form>
<script>
document.getElementById("btnRegistraGasto").addEventListener("click", async function(event) {

    event.preventDefault();
    let validacion = false;
    let mensajeError = "";
    let monto = document.getElementById("monto_gasto").value.trim();
    let tipoGasto = document.getElementById("tipoGastoId").value.trim();
    let idGasto = document.getElementById("idGasto").value;
    let detallesId = await idBtnDetalles();

    if (monto === '' || tipoGasto === '') {
        validacion = true;
        mensajeError = "Todos los campos son obligatorios";
    }
    if (isNaN(monto) || parseFloat(monto) <= 0) {
        validacion = true;
        mensajeError = "El monto debe ser un número positivo";
    }
    if (detallesId.length === 0) {
        validacion = true;
        mensajeError = "Debe seleccionar al menos un Item";
    }

    if (!validacion) {
        let editar = false;
        let fechaEdita = "";
        if (accion == "Editar") {
            editar = true
            fechaEdita = fecha
        }


        $.post("./form/conexiones.php", {
            ingresar: "insertarGasto",
            editar: editar,
            monto: monto,
            tipoGasto: tipoGasto,
            detallesId: detallesId,
            idGasto: idGasto,
            fechaEdita: fechaEdita
        }).done(function(data) {
            if (data.success) {
                // Operación completada con éxito
                console.log(data.message); // Muestra un mensaje al usuario
                window.location.href =
                    "<?php echo $_ENV['URL_ESTADISTICAS']; ?>";
            } else {
                // Ocurrió un error
                console.error(data.message);
            }
        }).fail(function(error) {
            console.log(error)
        });
    } else {
        console.log(mensajeError)
    }

});

function idBtnDetalles() {
    const buttonElements = document.querySelectorAll('button[id^="btnDetalle-"]');
    const numbers = [];

    buttonElements.forEach((button) => {
        const buttonId = button.id;
        const number = parseInt(buttonId.split('-').pop());

        if (!isNaN(number)) {
            numbers.push(number);
        }
    });

    return numbers;
}

function insertaNuevoDetalle(event) {

    event.preventDefault();
    let descripcion = document.getElementById("autocomplete-details").value;
    let tipoGastoId = document.getElementById("tipoGastoId").value;
    let tipoGastoDescripcion = document.getElementById("tipoGasto").value;
    let addButton = document.getElementById("add-button");

    if (tipoGastoId != "") {

        $.post("./form/conexiones.php", {
            ingresar: "agregaDetalle",
            tipoGasto: tipoGastoId,
            descripcion: descripcion,
            seleccionada: 1
        }).done(function(data) {
            if (data == "") {
                document.getElementById("autocomplete-details").value = "";
                btnDetallesSeleccionados()
                addButton.style.display = "none";
            } else {
                console.log(data)
            }
        }).fail(function(error) {
            console.log(error)
        });
    } else {

        insertaNuevoTipoGasto(event, tipoGastoDescripcion)
    }
}

function insertaNuevoTipoGasto(event, tipoGastoDescripcion) {
    $.post("./form/conexiones.php", {
        ingresar: "insertaTipoGasto",
        tipoGasto: tipoGastoDescripcion
    }).done(function(data) {
        let datos = JSON.parse(data);
        document.getElementById("tipoGastoId").value = datos;
        insertaNuevoDetalle(event)
    }).fail(function(error) {
        console.log(error)
    });
}

function listaGastos() {
    $.post("./form/conexiones.php", {
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

document.getElementById("autocomplete-details").addEventListener("input", async function(event) {
    event.preventDefault();
    let value = document.getElementById("autocomplete-details").value;
    let idGasto = "";

    if (document.getElementById("tipoGastoId").value == "") {
        idGasto = await gastoId(document.getElementById("tipoGasto").value)
        document.getElementById("idGasto").value = idGasto;
    } else {
        idGasto = document.getElementById("tipoGastoId").value;
    }

    let addBtn = document.getElementById("add-button");

    console.log("idgasto: " + idGasto, "value: " + value)
    $.post("./form/conexiones.php", {
        ingresar: "getDetalles",
        idGasto: idGasto,
        descripcion: value
    }).done(function(data) {
        let datos = JSON.parse(data);

        let autocompletarData = datos.map(element => ({
            label: element.descripcion,
            value: element.id
        }));
        $('#autocomplete-details').autocomplete({
            source: autocompletarData,
            select: function(event, ui) {
                var idSeleccionado = ui.item.value;
                creaBotonesDetallesSeleccionados(idSeleccionado, event, 1);
                // Vaciar el campo de entrada después de la selección
                $('#autocomplete-details').val('').blur();
                return false; // Evita que se llene el campo con la selección
            }
        });

        // Establecer el placeholder en el campo de entrada
        $('#autocomplete-details').attr('placeholder', 'Seleccionar Item');

        // Enfocar el campo de entrada al hacer clic en él
        $('#autocomplete-details').on("focus", function() {
            $(this).autocomplete("search");
        });

        if (data == "[]") {
            addBtn.style.display = "block";
        } else {
            addBtn.style.display = "none";
        }

    }).fail(function(error) {
        console.log(error)
    })
});

function gastoId(gasto) {
    return new Promise((resolve, reject) => {
        $.post("./form/conexiones.php", {
            ingresar: "getIdGasto",
            gasto: gasto
        }).done(function(data) {
            let datos = JSON.parse(data);
            resolve(datos[0].id);

        }).fail(function(error) {
            reject(error);
        })
    });
}
async function creaBotonesDetallesSeleccionados(id, event, seleccionado) {

    event.preventDefault();
    try {
        //Actualiza en la tabla descripción_gastos la columna "seleccionada" en true
        let resultado = await actualizaDetalles(id, seleccionado);
        if (resultado) {
            let gastoId = ""
            if (accion == "Editar") {
                gastoId = idTipoGasto
            } else {
                gastoId = document.querySelector(".gastoSelected") == null ? document.getElementById("idGasto")
                    .value : document.querySelector(".gastoSelected").getAttribute("id").split("-")[1];
            }
            await btnDetalles(gastoId);
            await btnDetallesSeleccionados()
        }
    } catch (error) {
        console.log(error);
    }
}

function actualizaDetalles(id, seleccionado) {
    return new Promise((resolve, reject) => {
        $.post("./form/conexiones.php", {
            ingresar: "actualizaDetalles",
            idDetalle: id,
            seleccionado: seleccionado
        }).done(function(data) {
            let datos = data == 1 ? true : false;
            resolve(datos); // Aquí se resuelve la Promesa
        }).fail(function(error) {
            reject(error); // Aquí se rechaza la Promesa en caso de error
        });
    });
}

function resetDetallesSeleccionados() {
    $.post("./form/conexiones.php", {
        ingresar: "resetSeleccionados",
    }).fail(function(error) {
        console.log(error);
    });
}

function btnDetallesSeleccionados() {
    document.getElementById("detallesSeleccionados").innerHTML = "";
    $.post("./form/conexiones.php", {
        ingresar: "getDetalleGastosSeleccionados",
    }).done(function(data) {
        let datos = JSON.parse(data);
        datos.forEach(element => {
            document.getElementById("detallesSeleccionados").innerHTML += `
                <button class="btn btn-outline-primary btn-sm mb-1" id="btnDetalle-${element.id}">
                    ${element.descripcion}
                    <i class="fas fa-times text-danger" onclick="eliminaBtnDetalle(${element.id})"></i>
                </button>
                `
        })
    }).fail(function(error) {
        console.log(error);
    });
}



async function agregaDescripcion(id, descripcion, editar) {

    if (id == 0) {
        id = await gastoId(descripcion);
    }

    if (editar == undefined) {

        activaBtnGastoFrecuente(id)
        document.getElementById("borrarTextoTipoGasto").style.display = "block";
        resetDetallesSeleccionados();
    }
    btnDetalles(id)
    // listaDetalles(id);
    document.getElementById("tipoGasto").value = descripcion;
    document.getElementById("tipoGastoId").value = id;
    document.getElementById("autocomplete-details").disabled = false;
}

function activaBtnGastoFrecuente(id) {

    // Selecciona todos los elementos con la clase "active" y "btn"
    const activeBtns = document.querySelectorAll(".active.btn");

    // Itera a través de los elementos seleccionados
    if (activeBtns.length > 0) {
        activeBtns.forEach(element => {
            // Quita la clase "active" de cada elemento
            element.classList.remove("active");
            element.classList.remove("gastoSelected");
        });
        document.getElementById("btn-" + id).classList.add("active");
        document.getElementById("btn-" + id).classList.add("gastoSelected");
    }
}

function muestraX(id, element) {

    //función recibe dos parametros el id del icono que se va a mostrar y el element que es el input completo del cual se obtiene
    // el value del id
    //si el id es undefined se muestra el icono del monto

    if (id == undefined) {
        document.getElementById("borrarTextoMontoGasto").style.display = "block";
    } else {
        document.getElementById('autocomplete-details').disabled = false;
        if (document.getElementById(element.id).value == "") {
            document.getElementById(id).style.display = "none";
        } else {
            document.getElementById(id).style.display = "block";
        }
    }



}

function eliminaTexto(id, element) {
    document.getElementById(id).value = "";
    document.getElementById(element.id).style.display = "none";
    showFieldsetDetalles("none", element.id);
    if (accion != "Editar") {

        document.getElementById("autocomplete-details").disabled = true;
        document.getElementById("detallesSeleccionados").innerHTML = "";
        resetDetallesSeleccionados();
    }
}

async function eliminaBtnDetalle(id) {
    creaBotonesDetallesSeleccionados(id, event, 0)
}
</script>