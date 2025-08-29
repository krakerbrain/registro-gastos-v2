<?php
session_start();
$sesion = isset($_SESSION['usuario']);
include __DIR__ . '/../config.php';
require_once dirname(__DIR__) . '/config/ConfigUrl.php';
$baseUrl = ConfigUrl::get();
$indice = "tipo-gasto";

if (!$sesion) {
    header("Location: " . $baseUrl . 'login/index.php');
    exit;
}

include "../partials/header.php";
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php include "../partials/navbar.php"; ?>
            <fieldset style="all:revert" class="mb-3" id="fieldsetDetalles">
                <legend style="all:revert">Consulta de Movimientos por Tipo de Gasto</legend>
                <form id="formConsulta" class="mb-4">
                    <div class="mb-3">
                        <label for="tipoGasto" class="form-label">Tipo de Gasto</label>
                        <input type="text" class="form-control" id="tipoGasto" name="tipoGasto" autocomplete="off"
                            required>
                        <div id="sugerenciasTipoGasto" class="list-group"></div>
                    </div>
                    <div class="mb-3">
                        <label for="dias" class="form-label">¿Cuántos días atrás?</label>
                        <input type="number" class="form-control" id="dias" name="dias" min="1" value="30" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Consultar</button>
                </form>
            </fieldset>
            <div id="resultados"></div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            const inputTipoGasto = document.getElementById('tipoGasto');
            const sugerencias = document.getElementById('sugerenciasTipoGasto');
            let tipoGastoId = null;

            inputTipoGasto.addEventListener('input', function() {
                const query = this.value;
                if (query.length < 2) {
                    sugerencias.innerHTML = '';
                    tipoGastoId = null;
                    return;
                }
                fetch('buscar_gasto_especifico.php?q=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {
                        sugerencias.innerHTML = '';
                        data.forEach(item => {
                            const option = document.createElement('a');
                            option.className = 'list-group-item list-group-item-action';
                            option.textContent = item.descripcion;
                            option.onclick = () => {
                                inputTipoGasto.value = item.descripcion;
                                tipoGastoId = item.id;
                                sugerencias.innerHTML = '';
                            };
                            sugerencias.appendChild(option);
                        });
                    });
            });

            document.getElementById('formConsulta').addEventListener('submit', function(e) {
                e.preventDefault();
                if (!tipoGastoId) {
                    alert('Selecciona un tipo de gasto válido.');
                    return;
                }
                const dias = document.getElementById('dias').value;
                fetch('consulta_movimientos.php?tipo_gasto_id=' + tipoGastoId + '&dias=' + dias)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('resultados').innerHTML = html;
                    });
            });
        </script>
        <?php
        include "../partials/boostrap_script.php";
        include "../partials/footer.php";
        ?>