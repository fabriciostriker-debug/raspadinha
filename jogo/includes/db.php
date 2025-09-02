<?php
// Define o fuso horário para Brasil/São Paulo
date_default_timezone_set('America/Sao_Paulo');

ini_set("display_errors", 1);
error_reporting(E_ALL);

$host = 'localhost';
$db = 'u986988049_Raspadinha';
$user = 'u986988049_Firmino777';
$pass = 'Firmino777';


$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Define o fuso horário da conexão MySQL para São Paulo (UTC-3)
$conn->query("SET time_zone = '-03:00'");
?>
