<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['HOST'];
$bd = $_ENV['BD'];
$usuario = $_ENV['USUARIO'];
$contrasenia = $_ENV['PASS'];

try {
    $con = new PDO("mysql:host=$host;dbname=$bd", $usuario, $contrasenia);
    // echo "Conectado";
} catch (PDOException $ex) {

    echo $ex->getMessage();
}
