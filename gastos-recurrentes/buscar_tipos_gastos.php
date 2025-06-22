<?php
session_start();
require '../config.php';
header('Content-Type: application/json');

try {
    $query = $_GET['q'] ?? '';
    $searchTerm = "%$query%";
    $userId = $_SESSION['id_usuario'];

    $stmt = $con->prepare("
        SELECT id, descripcion 
        FROM tipo_gastos 
        WHERE descripcion LIKE :searchTerm
        AND idusuario = :userId
        ORDER BY descripcion
        LIMIT 5
    ");

    $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la búsqueda']);
}
