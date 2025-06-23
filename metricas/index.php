<?php
session_start();
$sesion = isset($_SESSION['usuario']);
include __DIR__ . '/../config.php';
require_once dirname(__DIR__) . '/config/ConfigUrl.php';
$baseUrl = ConfigUrl::get();
$indice = "metricas";

if (!$sesion) {
    header("Location: " . $baseUrl . 'login/index.php');
    exit;
}

// Obtener tipos de gasto únicos con PDO
$tiposGasto = [];
$tiposGasto = [];
try {
    $stmt = $con->prepare("SELECT id, descripcion FROM tipo_gastos WHERE idusuario = :id_usuario ORDER BY descripcion");
    $stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
    $stmt->execute();
    $tiposGasto = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener tipos de gasto: " . $e->getMessage());
}

include "../partials/header.php";
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php include "../partials/navbar.php"; ?>

            <fieldset style="all:revert" class="mb-3" id="fieldsetAnalisisGastos">
                <legend style="all:revert">Analisis de Gastos</legend>

                <form id="formMetricas">
                    <!-- Fila 1: Selector de Tipo -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <label for="selectTipo" class="form-label">Tipo de Gasto</label>
                            <select id="selectTipo" class="form-select form-select-lg" required>
                                <option value="">Seleccione un tipo de gasto...</option>
                                <?php foreach ($tiposGasto as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Fila 2: Selector de Meses -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="inputMeses" class="form-label">Período (últimos meses)</label>
                            <div class="small">Máximo 12 meses</div>
                            <input type="number" id="inputMeses" class="form-control" min="1" max="12" value="6"
                                required>
                        </div>
                    </div>

                    <!-- Fila 3: Botón -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-graph-up me-2"></i> Generar Análisis
                            </button>
                        </div>
                    </div>
                </form>
            </fieldset>

            <!-- Resultados -->
            <div class="card">
                <div class="card-body">
                    <canvas id="chartGastos" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartGastos = null;

    document.getElementById('formMetricas').addEventListener('submit', function(e) {
        e.preventDefault();

        const tipo = document.getElementById('selectTipo').value;
        const meses = document.getElementById('inputMeses').value;

        fetch('obtener_metricas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tipo,
                    meses
                })
            })
            .then(response => response.json())
            .then(data => {
                renderizarGrafico(data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al obtener los datos');
            });
    });

    function renderizarGrafico(data) {
        const ctx = document.getElementById('chartGastos').getContext('2d');
        const tipoSeleccionado = document.getElementById('selectTipo');
        const nombreTipo = tipoSeleccionado.options[tipoSeleccionado.selectedIndex].text;

        // Destruir gráfico anterior si existe
        if (chartGastos) {
            chartGastos.destroy();
        }

        chartGastos = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.meses,
                datasets: [{
                    label: `Gastos en ${nombreTipo}`,
                    data: data.valores,
                    backgroundColor: '#4e73df',
                    borderColor: '#2e59d9',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Monto gastado'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Mes'
                        }
                    }
                }
            }
        });
    }
</script>

<?php include "../partials/footer.php"; ?>