<?php
require '../config.php';
header('Content-Type: application/json');

// 1. Validar entrada
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    // 2. Usar consulta preparada con PDO
    $stmt = $con->prepare("DELETE FROM gastos_recurrentes WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // 3. Ejecutar y verificar
    if ($stmt->execute()) {
        $rowCount = $stmt->rowCount();

        if ($rowCount > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'El registro no existe']);
        }
    } else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $errorInfo[2]]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error PDO: ' . $e->getMessage()]);
}
