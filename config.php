<?php
define('BASE_PATH', __DIR__); // Ajusta BASE_PATH según tu estructura
require BASE_PATH . '/vendor/autoload.php';
$ruta = $_SERVER['HTTP_HOST'] === 'localhost' ? BASE_PATH : '/home/u313214080/domains/registro-gastos.fun/private';
$dotenv = Dotenv\Dotenv::createImmutable($ruta);
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
