<?php
session_start();
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

// Validar sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Obtener datos POST
$data = json_decode(file_get_contents('php://input'), true);
$tipo = (int)$data['tipo'] ?? '';
// Asegurar entre 1 y 12 meses
$meses = isset($data['meses']) && is_numeric($data['meses']) ? max(1, min(12, (int)$data['meses'])) : 6;
try {
    // Consulta con PDO para obtener datos históricos
    $query = "SELECT 
        DATE_FORMAT(g.created_at, '%Y-%m') as mes,
        COALESCE(SUM(g.monto_gasto), 0) as total
    FROM gastos g
    WHERE 
        g.idusuario = :id_usuario
        AND g.tipo_gasto_id = :tipo_id
        AND g.created_at >= (
            SELECT DATE_SUB(MAX(created_at), INTERVAL :meses MONTH) 
            FROM gastos 
            WHERE idusuario = :id_usuario 
            AND tipo_gasto_id = :tipo_id
        )
    GROUP BY mes
    ORDER BY mes";

    $stmt = $con->prepare($query);
    $stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
    $stmt->bindParam(':tipo_id', $data['tipo'], PDO::PARAM_INT);
    $stmt->bindParam(':meses', $meses, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar datos para Chart.js
    $meses = [];
    $valores = [];

    foreach ($resultados as $row) {
        $meses[] = DateTime::createFromFormat('!Y-m', $row['mes'])->format('M Y');
        $valores[] = (float)$row['total'];
    }

    echo json_encode([
        'tipo' => $tipo,
        'meses' => $meses,
        'valores' => $valores
    ]);
} catch (PDOException $e) {
    error_log("Error en métricas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor']);
}
