<?php
session_start();
include __DIR__ . '/../config.php';

$id_usuario = $_SESSION['id_usuario'];
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, descripcion FROM descripcion_gastos WHERE idusuario = :id_usuario AND descripcion LIKE :q ORDER BY descripcion LIMIT 10";
$stmt = $con->prepare($sql);
$like = "%$q%";
$stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt->bindParam(':q', $like, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($result);
