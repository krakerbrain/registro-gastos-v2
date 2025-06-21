<?php
session_start();
require '../config.php';
header('Content-Type: application/json');

try {
    $query = $_GET['q'] ?? '';
    $userId = $_SESSION['id_usuario'];

    // 1. Buscar coincidencias en tipo_gastos
    $searchTerm = "%$query%";
    $stmt = $con->prepare("
        SELECT tg.id, tg.descripcion
        FROM tipo_gastos tg
        WHERE tg.descripcion LIKE :searchTerm
        AND tg.idusuario = :userId
        ORDER BY tg.descripcion
        LIMIT 5
    ");
    $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Filtrar los que ya son recurrentes
    $stmt = $con->prepare("
        SELECT tipo_gasto_id 
        FROM gastos_recurrentes 
        WHERE idusuario = :userId
    ");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $existentes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'tipo_gasto_id');

    // 3. Marcar cuales ya existen
    $response = array_map(function ($item) use ($existentes) {
        $item['ya_existe'] = in_array($item['id'], $existentes);
        return $item;
    }, $resultados);

    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en la búsqueda',
        'debug' => $e->getMessage() // Solo en desarrollo
    ]);
}
