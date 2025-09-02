<?php
// Inicia a sessão apenas se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/session_config.php';
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit();
}
?>
