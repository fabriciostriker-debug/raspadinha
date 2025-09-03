<?php
date_default_timezone_set('America/Sao_Paulo');
ini_set("display_errors", 1);
error_reporting(E_ALL);

// Função para ler .env
if (!function_exists('loadEnv')) {
    function loadEnv($file) {
        if (!file_exists($file)) return [];
        $env = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
        return $env;
    }
}

// Função segura para tentar conexão
if (!function_exists('tryConnect')) {
    function tryConnect($host, $user, $pass, $db) {
        try {
            // Desabilita warnings para capturar falhas com exceptions
            mysqli_report(MYSQLI_REPORT_STRICT);
            $conn = new mysqli($host, $user, $pass, $db);
            $conn->query("SET time_zone = '-03:00'");
            return $conn;
        } catch (Exception $e) {
            return null;
        }
    }
}

// Carrega .env se existir
$envPath = __DIR__ . '/../../.env';
$env = file_exists($envPath) ? loadEnv($envPath) : [];

// Valores
$hostEnv = $env['MYSQL_HOST'] ?? 'db';
$db      = $env['MYSQL_DATABASE'] ?? '';
$user    = $env['MYSQL_USER'] ?? '';
$pass    = $env['MYSQL_PASSWORD'] ?? '';

// Ordem de tentativa: primeiro localhost, depois .env host
$hostsToTry = ['localhost', $hostEnv];

$conn = null;
foreach ($hostsToTry as $host) {
    $conn = tryConnect($host, $user, $pass, $db);
    if ($conn) break; // conexão bem-sucedida
}

if (!$conn) {
    die("Erro na conexão com o banco de dados. Verifique host, usuário e senha.");
}
?>