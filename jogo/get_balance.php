<?php
// Exibir erros PHP para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'includes/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401);
    echo json_encode(["erro" => "Usuário não autenticado"]);
    exit;
}

$userId = $_SESSION["usuario_id"];

try {
    // Busca o saldo atual do usuário (incluindo saldo bônus)
    $stmt = $conn->prepare("SELECT balance, bonus_balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("Usuário não encontrado");
    }
    
    $bonus_saldo = isset($user["bonus_balance"]) ? $user["bonus_balance"] : 0;
    $saldo = $user["balance"] + $bonus_saldo;
    
    // Formata o saldo para exibição
    $saldoFormatado = number_format($saldo, 2, ",", ".");
    
    // Retorna o resultado
    echo json_encode([
        "sucesso" => true,
        "saldo" => $saldoFormatado,
        "saldo_numerico" => $saldo
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro ao buscar saldo",
        "detalhes" => $e->getMessage()
    ]);
}
?>
