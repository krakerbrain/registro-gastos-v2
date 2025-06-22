<?php
session_start();
require '../config.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $descripcionGastoId = $data['descripcion_gasto_id'] ?? null;
    $userId = $_SESSION['id_usuario'];

    if (!$descripcionGastoId) {
        throw new Exception('ID de descripción no proporcionado');
    }

    // Verificar si ya existe
    $stmt = $con->prepare("
        SELECT COUNT(*) 
        FROM gastos_recurrentes 
        WHERE descripcion_gasto_id = :descripcionGastoId
        AND idusuario = :userId
    ");
    $stmt->execute([
        ':descripcionGastoId' => $descripcionGastoId,
        ':userId' => $userId
    ]);

    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Este gasto ya está en tus recurrentes');
    }

    // Insertar
    $stmt = $con->prepare("
        INSERT INTO gastos_recurrentes (idusuario, descripcion_gasto_id)
        VALUES (:userId, :descripcionGastoId)
    ");
    $success = $stmt->execute([
        ':userId' => $userId,
        ':descripcionGastoId' => $descripcionGastoId
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
