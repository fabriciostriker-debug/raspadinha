<?php
// Define o fuso horário para Brasil/São Paulo
date_default_timezone_set('America/Sao_Paulo');

ini_set("display_errors", 1);
error_reporting(E_ALL);

$host = getenv('MYSQL_HOST') ?: 'localhost';
$db   = getenv('MYSQL_DATABASE');
$user = getenv('MYSQL_USER');
$pass = getenv('MYSQL_PASSWORD');

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Define o fuso horário da conexão MySQL para São Paulo (UTC-3)
$conn->query("SET time_zone = '-03:00'");
?>
