<?php
session_start();
$sesion = isset($_SESSION['usuario']);
include __DIR__ . '/../config.php';
require_once dirname(__DIR__) . '/config/ConfigUrl.php';
$baseUrl = ConfigUrl::get();
$indice = "recurrentes";

if (!$sesion) {
    header("Location: " . $baseUrl . 'login/index.php');
    exit;
}


// Obtener gastos recurrentes guardados
$gastos_recurrentes = [];
try {
    $query = "SELECT 
                    gr.*, 
                    dg.descripcion AS nombre_gasto 
                FROM 
                    gastos_recurrentes gr
                JOIN 
                    descripcion_gastos dg ON gr.descripcion_gasto_id = dg.id
                WHERE 
                    gr.idusuario = :id_usuario
                    AND dg.idusuario = :id_usuario  -- Filtro adicional para consistencia
                ORDER BY 
                    dg.descripcion ASC";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
    $stmt->execute();
    $gastos_recurrentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Manejo de errores (opcional)
    error_log("Error al obtener gastos recurrentes: " . $e->getMessage());
    $gastos_recurrentes = []; // Asegurar que sea un array aunque falle
}
include "../partials/header.php";
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php include "../partials/navbar.php"; ?>

            <!-- Formulario de Búsqueda -->
            <!-- <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h6>➕ Agregar Gasto Recurrente</h6>
                </div>
                <div class="card-body"> -->
            <fieldset style="all:revert" class="mb-3" id="fieldsetDetalles">
                <legend style="all:revert">Gastos Recurrentes</legend>
                <div id="detallesGastos"></div>
                <!-- Paso 1: Seleccionar tipo de gasto -->
                <div class="mb-3">
                    <label class="form-label">Categoría:</label>
                    <input type="text" id="buscarTipoGasto" class="form-control"
                        placeholder="Ej: Departamento, Servicios...">
                    <div id="sugerenciasTipo" class="list-group mt-2"></div>
                </div>

                <!-- Paso 2: Seleccionar descripción (aparece después de seleccionar tipo) -->
                <div id="seccionDescripcion" class="mb-3" style="display: none;">
                    <label class="form-label">Descripción específica:</label>
                    <input type="text" id="buscarDescripcion" class="form-control"
                        placeholder="Ej: Arriendo, Internet...">
                    <div id="sugerenciasDescripcion" class="list-group mt-2"></div>
                </div>

                <button id="btnAgregar" class="btn btn-success mt-2" disabled>Agregar</button>
            </fieldset>
            <!-- Lista de Gastos Recurrentes -->
            <!-- <div class="card">
                <div class="card-header bg-info text-white">
                    <h6>📝 Mis Gastos Recurrentes</h6>
                </div>
                <div class="card-body"> -->
            <fieldset style="all:revert" class="mb-3">
                <legend style="all:revert">Mis Gastos Recurrentes</legend>
                <div class="d-flex justify-content-lg-between flex-wrap gap-2" id="listaRecurrentes">
                    <?php foreach ($gastos_recurrentes as $gasto): ?>
                        <div class="d-flex align-items-center mb-2 mr-2" style="background-color: #0d6efd; color: white">
                            <button class="btn btn-primary btn-sm"
                                style="border-radius: 0; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; max-width: 150px;"
                                data-id="<?= $gasto['id'] ?>" data-nombre="<?= htmlspecialchars($gasto['nombre_gasto']) ?>">
                                <?= htmlspecialchars($gasto['nombre_gasto']) ?>
                            </button>
                            <button class="btn btn-sm btn-info btnEliminar" style="border-radius: 0;">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button id="btnAnalizar" class="btn btn-success mt-3">Analizar Gastos</button>
            </fieldset>

            <!-- Resultados del Análisis -->
            <div id="resultadosAnalisis" class="mt-4"></div>
        </div>

        <script>
            // Variables globales
            let selectedTipoId = null;
            let selectedDescripcionId = null;

            // Buscar tipos de gasto
            document.getElementById('buscarTipoGasto').addEventListener('input', function() {
                const query = this.value;
                if (query.length < 2) {
                    document.getElementById('sugerenciasTipo').innerHTML = '';
                    return;
                }

                fetch(`buscar_tipos_gastos.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        const sugerencias = document.getElementById('sugerenciasTipo');
                        sugerencias.innerHTML = '';
                        data.forEach(item => {
                            const option = document.createElement('a');
                            option.className = 'list-group-item list-group-item-action';
                            option.textContent = item.descripcion;
                            option.dataset.id = item.id;
                            option.onclick = () => {
                                document.getElementById('buscarTipoGasto').value = item
                                    .descripcion;
                                selectedTipoId = item.id;
                                sugerencias.innerHTML = '';
                                // Mostrar campo para descripción
                                document.getElementById('seccionDescripcion').style.display =
                                    'block';
                                document.getElementById('btnAgregar').disabled = true;
                            };
                            sugerencias.appendChild(option);
                        });
                    });
            });

            // Buscar descripciones de gasto
            document.getElementById('buscarDescripcion').addEventListener('input', function() {
                if (!selectedTipoId) return;

                const query = this.value;
                if (query.length < 2) {
                    document.getElementById('sugerenciasDescripcion').innerHTML = '';
                    return;
                }

                fetch(`buscar_descripciones.php?q=${encodeURIComponent(query)}&tipo_gasto_id=${selectedTipoId}`)
                    .then(response => response.json())
                    .then(data => {
                        const sugerencias = document.getElementById('sugerenciasDescripcion');
                        sugerencias.innerHTML = '';
                        data.forEach(item => {
                            const option = document.createElement('a');
                            option.className =
                                `list-group-item list-group-item-action ${item.ya_existe ? 'text-muted' : ''}`;
                            option.textContent = item.descripcion;
                            option.dataset.id = item.id;
                            if (!item.ya_existe) {
                                option.onclick = () => {
                                    document.getElementById('buscarDescripcion').value = item
                                        .descripcion;
                                    selectedDescripcionId = item.id;
                                    sugerencias.innerHTML = '';
                                    document.getElementById('btnAgregar').disabled = false;
                                };
                            }
                            sugerencias.appendChild(option);
                        });
                    });
            });

            // Agregar a la lista
            document.getElementById('btnAgregar').addEventListener('click', function() {
                const descripcion = document.getElementById('buscarDescripcion').value.trim();
                if (!descripcion || !selectedDescripcionId) {
                    alert('Por favor completa la selección');
                    return;
                }

                fetch('guardar_recurrente.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            descripcion_gasto_id: selectedDescripcionId,
                            descripcion: descripcion
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar la lista
                            const li = document.createElement('li');
                            li.className = 'list-group-item';
                            li.dataset.id = data.id;
                            li.innerHTML = `
                ${descripcion}
                <button class="btn btn-sm btn-danger float-end btnEliminar">×</button>
            `;
                            document.getElementById('listaRecurrentes').appendChild(li);

                            // Resetear formulario
                            document.getElementById('buscarTipoGasto').value = '';
                            document.getElementById('buscarDescripcion').value = '';
                            document.getElementById('seccionDescripcion').style.display = 'none';
                            document.getElementById('btnAgregar').disabled = true;
                            selectedTipoId = null;
                            selectedDescripcionId = null;
                        } else {
                            alert(data.error || 'Error al guardar');
                        }
                    });
            });
            // Analizar gastos
            document.getElementById('btnAnalizar').addEventListener('click', function() {
                // Selecciona los botones principales (no el de eliminar "×")
                const botonesGastos = document.querySelectorAll('#listaRecurrentes button.btn-primary');

                // Extrae los IDs de los botones seleccionados
                const ids = Array.from(botonesGastos).map(boton => boton.dataset.id);

                // Envía la solicitud al servidor
                fetch('analizar_recurrentes.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ids
                        })
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('resultadosAnalisis').innerHTML = html;
                    });
            });

            function toggleChevron(chevronId) {
                const chevron = document.getElementById(chevronId);
                if (chevron) {
                    // Alternar entre los íconos de flecha
                    if (chevron.classList.contains('bi-chevron-down')) {
                        chevron.classList.remove('bi-chevron-down');
                        chevron.classList.add('bi-chevron-up');
                    } else {
                        chevron.classList.remove('bi-chevron-up');
                        chevron.classList.add('bi-chevron-down');
                    }
                }
            }
        </script>

        <?php
        include "../partials/boostrap_script.php";
        include "../partials/footer.php";
        ?>