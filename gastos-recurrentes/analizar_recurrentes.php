<?php
session_start();
require '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
// Obtener las descripciones de los gastos recurrentes seleccionados
$ids = $data['ids'] ?? [];
if (empty($ids)) {
    die('<div class="alert alert-warning">Selecciona al menos un gasto recurrente.</div>');
}

try {
    // Crear placeholders con nombres únicos (:id0, :id1, etc.)
    $placeholders = implode(',', array_map(fn($i) => ":id$i", array_keys($ids)));

    $query = "
        SELECT gr.id, gr.descripcion_gasto_id, dg.descripcion
        FROM gastos_recurrentes gr
        JOIN descripcion_gastos dg ON gr.descripcion_gasto_id = dg.id
        WHERE gr.id IN ($placeholders)
        AND gr.idusuario = :userId
    ";

    $stmt = $con->prepare($query);

    // Bind de los IDs dinámicos
    foreach ($ids as $i => $id) {
        $stmt->bindValue(":id$i", $id, PDO::PARAM_INT);
    }

    // Bind del ID de usuario
    $stmt->bindParam(':userId', $_SESSION['id_usuario'], PDO::PARAM_INT);

    $stmt->execute();
    $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($gastos)) {
        die('<div class="alert alert-info">No se encontraron los gastos seleccionados.</div>');
    }

    echo '<div class="accordion" id="gastosAcordeon">';

    foreach ($gastos as $gasto) {
        // Consulta para las últimas 6 ocurrencias de cada gasto recurrente
        $stmt = $con->prepare("
            SELECT 
                g.created_at AS fecha,
                g.monto_gasto,
                tg.descripcion AS categoria
            FROM gastos g
            JOIN descripcion_gasto_gasto dgg ON g.id = dgg.gasto_id
            JOIN descripcion_gastos dg ON dgg.descripcion_gasto_id = dg.id
            JOIN tipo_gastos tg ON dg.tipo_gasto_id = tg.id
            WHERE dg.id = :descripcionId
            AND g.idusuario = :userId
            ORDER BY g.created_at DESC
            LIMIT 6
        ");

        $stmt->execute([
            ':descripcionId' => $gasto['descripcion_gasto_id'],
            ':userId' => $_SESSION['id_usuario']
        ]);
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mostrar resultados en acordeón
        echo '
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="btn btn-info w-100 accordion-button text-dark d-flex justify-content-between" type="button" data-toggle="collapse" data-target="#gasto' . $gasto['id'] . '" aria-expanded="false" aria-controls="gasto' . $gasto['id'] . '" onclick="toggleChevron(\'chevron' . $gasto['id'] . '\')">
                <span>
                    ' . htmlspecialchars($gasto['descripcion']) . ' (' . count($pagos) . ' registros)
                    </span>
                    <i class="bi bi-chevron-down float-end" id="chevron' . $gasto['id'] . '"></i>
                </button>
            </h2>
            <div id="gasto' . $gasto['id'] . '" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Categoría</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($pagos as $pago) {
            echo '<tr>
                <td>' . $pago['fecha'] . '</td>
                <td>$' . number_format($pago['monto_gasto'], 0, ',', '.') . '</td>
                <td>' . htmlspecialchars($pago['categoria']) . '</td>
            </tr>';
        }

        echo '          </tbody>
                    </table>
                </div>
            </div>
        </div>';
    }

    echo '</div>';
} catch (PDOException $e) {
    error_log("Error en analizar_recurrentes.php: " . $e->getMessage());
    echo '<div class="alert alert-danger">Error al obtener los datos.</div>';
}
