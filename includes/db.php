<?php
date_default_timezone_set('America/Sao_Paulo');
ini_set("display_errors", 1);
error_reporting(E_ALL);

// Função para ler .env (somente se não existir)
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

// Função segura para tentar conexão (somente se não existir)
if (!function_exists('tryConnect')) {
    function tryConnect($host, $user, $pass, $db) {
        try {
            $conn = new mysqli($host, $user, $pass, $db);
            if ($conn->connect_error) throw new Exception($conn->connect_error);
            return $conn;
        } catch (Exception $e) {
            return null;
        }
    }
}

// Caminho do arquivo .env
$envPath = __DIR__ . '/../.env';

// Carrega variáveis do .env se existir
$env = file_exists($envPath) ? loadEnv($envPath) : [];

// Pega valores do .env ou define defaults
$hostEnv = isset($env['MYSQL_HOST']) ? $env['MYSQL_HOST'] : 'db';
$db      = isset($env['MYSQL_DATABASE']) ? $env['MYSQL_DATABASE'] : '';
$user    = isset($env['MYSQL_USER']) ? $env['MYSQL_USER'] : '';
$pass    = isset($env['MYSQL_PASSWORD']) ? $env['MYSQL_PASSWORD'] : '';

// Primeiro tenta o host do .env (Docker)
$conn = tryConnect($hostEnv, $user, $pass, $db);

// Se falhar, tenta localhost (Hostinger)
if (!$conn) {
    $conn = tryConnect('localhost', $user, $pass, $db);
}

// Se ainda falhar, exibe erro
if (!$conn) {
    die("Erro na conexão com o banco de dados. Verifique host, usuário e senha.");
}

// Ajusta fuso horário da conexão
$conn->query("SET time_zone = '-03:00'");
?>
