<?php
session_start();
require '../config.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $tipoGastoId = $data['tipo_gasto_id'] ?? null;
    $userId = $_SESSION['id_usuario'];

    // Validación final (por si acaso)
    $stmt = $con->prepare("
        SELECT COUNT(*) 
        FROM gastos_recurrentes 
        WHERE tipo_gasto_id = :tipoGastoId 
        AND idusuario = :userId
    ");
    $stmt->execute([':tipoGastoId' => $tipoGastoId, ':userId' => $userId]);

    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Este gasto ya está en tus recurrentes');
    }

    // Insertar
    $stmt = $con->prepare("
        INSERT INTO gastos_recurrentes (idusuario, tipo_gasto_id)
        VALUES (:userId, :tipoGastoId)
    ");
    $success = $stmt->execute([
        ':userId' => $userId,
        ':tipoGastoId' => $tipoGastoId
    ]);

    echo json_encode([
        'success' => $success,
        'id' => $con->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
