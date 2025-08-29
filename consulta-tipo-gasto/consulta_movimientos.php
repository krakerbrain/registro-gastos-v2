<?php
session_start();
include __DIR__ . '/../config.php';

$id_usuario = $_SESSION['id_usuario'];
$descripcion_gasto_id = isset($_GET['descripcion_gasto_id']) ? intval($_GET['descripcion_gasto_id']) : 0;
$dias = isset($_GET['dias']) ? intval($_GET['dias']) : 30;

if ($descripcion_gasto_id <= 0 || $dias <= 0) {
    echo '<div class="alert alert-danger">Parámetros inválidos.</div>';
    exit;
}

// 1. Obtener el tipo_gasto_id de la descripcion_gastos seleccionada
// Obtener también el nombre del tipo de gasto general
$sqlTipo = "SELECT dg.tipo_gasto_id, dg.descripcion, tg.descripcion AS tipo_gasto_nombre FROM descripcion_gastos dg JOIN tipo_gastos tg ON dg.tipo_gasto_id = tg.id WHERE dg.id = :descripcion_gasto_id AND dg.idusuario = :id_usuario";
$stmt = $con->prepare($sqlTipo);
$stmt->bindParam(':descripcion_gasto_id', $descripcion_gasto_id, PDO::PARAM_INT);
$stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt->execute();
$rowTipo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rowTipo) {
    echo '<div class="alert alert-danger">Descripción de gasto no encontrada.</div>';
    exit;
}
$tipo_gasto_id = $rowTipo['tipo_gasto_id'];
$descripcion = $rowTipo['descripcion'];
$tipo_gasto_nombre = $rowTipo['tipo_gasto_nombre'];

// 2. Buscar los movimientos del tipo_gasto_id
$sql = "SELECT 
            g.id AS gasto_id,
            g.monto_gasto,
            g.created_at,
            dg.descripcion AS descripcion_gasto,
            tg.descripcion AS tipo_gasto_nombre
        FROM gastos g
        JOIN descripcion_gasto_gasto dgg ON g.id = dgg.gasto_id
        JOIN descripcion_gastos dg ON dgg.descripcion_gasto_id = dg.id
        JOIN tipo_gastos tg ON dg.tipo_gasto_id = tg.id
        WHERE dg.id = :descripcion_gasto_id
          AND g.idusuario = :id_usuario
          AND g.created_at >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
        ORDER BY g.created_at DESC
        LIMIT 100";

$stmt = $con->prepare($sql);
$stmt->bindParam(':descripcion_gasto_id', $descripcion_gasto_id, PDO::PARAM_INT);
$stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
$stmt->execute();
$movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($movimientos as $mov) {
    $total += $mov['monto_gasto'];
}

if (count($movimientos) === 0) {
    echo '<div class="alert alert-warning">No hay movimientos para este tipo de gasto en el período seleccionado.</div>';
    exit;
}
?>
<h5>Movimientos para: <?= htmlspecialchars($descripcion) ?> (Tipo de gasto: <?= htmlspecialchars($tipo_gasto_nombre) ?>)
</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Tipo de Gasto General</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($movimientos as $mov): ?>
            <tr>
                <td><?= htmlspecialchars($mov['fecha'] ?? $mov['created_at']) ?></td>
                <td><?= htmlspecialchars($mov['tipo_gasto_nombre']) ?></td>
                <td class="text-end">$<?= number_format($mov['monto_gasto'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2" class="text-end">Total</th>
            <th class="text-end">$<?= number_format($total, 2) ?></th>
        </tr>
    </tfoot>
</table>