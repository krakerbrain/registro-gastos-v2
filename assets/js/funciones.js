let mesesArray = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

function fechaActual() {
  let fecha = new Date();
  return `${mesesArray[fecha.getMonth()]}, ${fecha.getFullYear()}`;
}

function cargaMeses() {
  $.post("../historial/tabla/conexiones.php", {
    ingresar: "getFecha",
  })
    .done(function (data) {
      let datos = JSON.parse(data);
      let select = document.getElementById("fecha");
      let getFechaActual = fechaActual();
      datos.forEach((element) => {
        let selected;
        let fechaValue = `${mesesArray[element.mes - 1]}, ${element.anio}`;
        select.innerHTML += `<option id="${element.mes}-${element.anio}" value="${fechaValue}" ${fechaValue === getFechaActual ? "selected" : ""}>${fechaValue}</option>`;
      });
      cargaData();
    })
    .fail(function (error) {
      console.log(error);
    });
}
