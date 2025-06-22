<?php
session_start();
require '../config.php';
header('Content-Type: application/json');

try {
    $query = $_GET['q'] ?? '';
    $tipoGastoId = $_GET['tipo_gasto_id'] ?? null;
    $userId = $_SESSION['id_usuario'];

    if (!$tipoGastoId) {
        throw new Exception('ID de tipo gasto no proporcionado');
    }

    $searchTerm = "%$query%";

    // Primera consulta: Buscar descripciones de gastos
    $stmt = $con->prepare("
        SELECT dg.id, dg.descripcion
        FROM descripcion_gastos dg
        WHERE dg.descripcion LIKE :searchTerm
        AND dg.tipo_gasto_id = :tipoGastoId
        AND dg.idusuario = :userId
        ORDER BY dg.descripcion
        LIMIT 5
    ");

    $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':tipoGastoId', $tipoGastoId, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($resultados)) {
        echo json_encode([]);
        exit;
    }

    // Segunda consulta: Verificar cuáles ya son recurrentes (usando parámetros nombrados)
    $ids = array_column($resultados, 'id');
    $placeholders = implode(',', array_map(fn($i) => ":id$i", array_keys($ids)));

    $stmt = $con->prepare("
        SELECT descripcion_gasto_id 
        FROM gastos_recurrentes 
        WHERE descripcion_gasto_id IN ($placeholders)
        AND idusuario = :userId
    ");

    // Bind de parámetros dinámicos
    foreach ($ids as $i => $id) {
        $stmt->bindValue(":id$i", $id, PDO::PARAM_INT);
    }
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $existentes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'descripcion_gasto_id');

    // Marcar los existentes
    $response = array_map(function ($item) use ($existentes) {
        $item['ya_existe'] = in_array($item['id'], $existentes);
        return $item;
    }, $resultados);

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
