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
                    tg.descripcion AS nombre_gasto 
                FROM 
                    gastos_recurrentes gr
                JOIN 
                    tipo_gastos tg ON gr.tipo_gasto_id = tg.id
                WHERE 
                    gr.idusuario = :id_usuario
                    AND tg.idusuario = :id_usuario  -- Filtro adicional para consistencia
                ORDER BY 
                    tg.descripcion ASC";
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
        <div class="col-md-8">
            <?php include "../partials/navbar.php"; ?>

            <!-- Formulario de Búsqueda -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>➕ Agregar Gasto Recurrente</h5>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" id="buscarGasto" class="form-control"
                            placeholder="Ej: Internet, Arriendo...">
                        <button class="btn btn-success" id="btnAgregar">Agregar</button>
                    </div>
                    <div id="sugerencias" class="list-group"></div>
                </div>
            </div>

            <!-- Lista de Gastos Recurrentes -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>📝 Mis Gastos Recurrentes</h5>
                </div>
                <div class="card-body">
                    <ul id="listaRecurrentes" class="list-group">
                        <?php foreach ($gastos_recurrentes as $gasto): ?>
                            <li class="list-group-item" data-id="<?= $gasto['id'] ?>"
                                data-nombre="<?= htmlspecialchars($gasto['nombre_gasto']) ?>">
                                <?= htmlspecialchars($gasto['nombre_gasto']) ?>
                                <button class="btn btn-sm btn-danger float-end btnEliminar">×</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <button id="btnAnalizar" class="btn btn-primary mt-3">Analizar Gastos</button>
                </div>
            </div>

            <!-- Resultados del Análisis -->
            <div id="resultadosAnalisis" class="mt-4"></div>
        </div>
    </div>
</div>

<script>
    // Autocompletado
    document.getElementById('buscarGasto').addEventListener('input', function() {

        const query = this.value;
        if (query.length < 2) return;

        fetch(`buscar_gastos.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                const sugerencias = document.getElementById('sugerencias');
                sugerencias.innerHTML = '';
                data.forEach(item => {
                    const option = document.createElement('a');
                    option.className =
                        `list-group-item list-group-item-action ${item.ya_existe ? 'text-muted' : ''}`;
                    option.textContent = item.descripcion;
                    option.dataset.item = JSON.stringify(item); // Almacenamos todo el objeto
                    if (!item.ya_existe) {
                        option.onclick = () => {
                            document.getElementById('buscarGasto').value = item.descripcion;
                            selectedId = item.id; // Capturamos el ID
                            sugerencias.innerHTML = '';
                        };
                    }
                    sugerencias.appendChild(option);
                });
            });
    });

    // Al seleccionar un gasto del autocompletado
    let selectedId = null; // Variable global para almacenar el ID seleccionado

    document.getElementById('sugerencias').addEventListener('click', function(e) {
        if (e.target.classList.contains('list-group-item')) {
            const item = JSON.parse(e.target.dataset.item);
            document.getElementById('buscarGasto').value = item.descripcion;
            selectedId = item.id; // Almacenamos el ID seleccionado
            this.innerHTML = ''; // Limpiamos las sugerencias
        }
    });

    // Agregar a la lista
    document.getElementById('btnAgregar').addEventListener('click', function() {
        const nombreGasto = document.getElementById('buscarGasto').value.trim();
        if (!nombreGasto || !selectedId) {
            alert('Por favor selecciona un gasto válido de la lista');
            return;
        }

        fetch('guardar_recurrente.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tipo_gasto_id: selectedId,
                    nombre_gasto: nombreGasto // Opcional, según tu estructura
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar la interfaz
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.dataset.id = data.id;
                    li.innerHTML = `
                ${nombreGasto}
                <button class="btn btn-sm btn-danger float-end btnEliminar">×</button>
            `;
                    document.getElementById('listaRecurrentes').appendChild(li);
                    document.getElementById('buscarGasto').value = '';
                    selectedId = null; // Resetear el ID seleccionado
                } else {
                    alert(data.error || 'Error al guardar');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error en la conexión');
            });
    });

    // Agregar a la lista
    document.getElementById('btnAgregar').addEventListener('click', function() {
        const nombreGasto = document.getElementById('buscarGasto').value.trim();
        if (!nombreGasto) return;

        fetch('guardar_recurrente.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tipo_gasto_id: selectedId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.dataset.id = data.id;
                    li.dataset.nombre = nombreGasto;
                    li.innerHTML =
                        `${nombreGasto} <button class="btn btn-sm btn-danger float-end btnEliminar">×</button>`;
                    document.getElementById('listaRecurrentes').appendChild(li);
                    document.getElementById('buscarGasto').value = '';
                }
            });
    });

    // Eliminar de la lista
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btnEliminar')) {
            const id = e.target.parentElement.dataset.id;
            fetch(`eliminar_recurrente.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(() => e.target.parentElement.remove());
        }
    });

    // Analizar gastos
    document.getElementById('btnAnalizar').addEventListener('click', function() {
        const ids = Array.from(document.querySelectorAll('#listaRecurrentes li'))
            .map(li => li.dataset.id);

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
</script>

<?php
include "../partials/boostrap_script.php";
include "../partials/footer.php";
?>