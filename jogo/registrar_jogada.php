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

// Valida se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["erro" => "Método não permitido"]);
    exit;
}

// Decodifica os dados JSON
$data = json_decode(file_get_contents("php://input"), true);

// Valida se os dados foram recebidos corretamente
if (!$data) {
    http_response_code(400);
    echo json_encode(["erro" => "Dados inválidos"]);
    exit;
}

$userId = $_SESSION["usuario_id"];
$ganhou = $data["ganhou"] ?? false;
$premio = $data["premio"] ?? 0;
$valorAposta = $data["valor_aposta"] ?? 0;

// Valida o valor do prêmio
if ($ganhou && ($premio <= 0 || $premio > 1000)) {
    http_response_code(400);
    echo json_encode(["erro" => "Valor do prêmio inválido"]);
    exit;
}

try {
    // Inicia uma transação para garantir consistência
    $conn->begin_transaction();
    
    // Se ganhou, adiciona o prêmio ao saldo
    if ($ganhou && $premio > 0) {
        // Atualiza o saldo do usuário
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $premio, $userId);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar saldo");
        }
        
        // Registra a transação no histórico
        $stmt = $conn->prepare("INSERT INTO transacoes (usuario_id, tipo, valor, descricao, data_criacao) VALUES (?, 'premio_raspadinha', ?, 'Prêmio ganho na raspadinha', NOW())");
        $stmt->bind_param("id", $userId, $premio);
        $stmt->execute(); // Não é crítico se falhar
    }
    
    // Registra a jogada no histórico
    $resultado = $ganhou ? 'ganhou' : 'perdeu';
    $stmt = $conn->prepare("INSERT INTO jogadas_raspadinha (usuario_id, resultado, premio, valor_aposta, data_jogada) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isdd", $userId, $resultado, $premio, $valorAposta);
    $stmt->execute(); // Não é crítico se falhar
    
    // Atualizar rollover de bônus se houver bônus ativo
    $stmt = $conn->prepare("SELECT bonus_balance, bonus_rollover_required, bonus_rollover_completed FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $bonus_result = $stmt->get_result();
    $bonus_info = $bonus_result->fetch_assoc();
    
    if ($bonus_info && $bonus_info['bonus_balance'] > 0 && $bonus_info['bonus_rollover_required'] > 0) {
        // Contar a aposta no rollover
        $novo_rollover_completed = $bonus_info['bonus_rollover_completed'] + $valorAposta;
        
        // Verificar se o rollover foi cumprido
        if ($novo_rollover_completed >= $bonus_info['bonus_rollover_required']) {
            // Rollover cumprido - transferir bônus para saldo real e limpar
            $stmt = $conn->prepare("UPDATE users SET balance = balance + ?, bonus_balance = 0, bonus_rollover_required = 0, bonus_rollover_completed = 0 WHERE id = ?");
            $stmt->bind_param("di", $bonus_info['bonus_balance'], $userId);
            $stmt->execute();
            
            // Registrar a liberação do bônus
            $stmt = $conn->prepare("INSERT INTO transacoes (usuario_id, tipo, valor, descricao, data_criacao) VALUES (?, 'bonus_liberado', ?, 'Bônus liberado após rollover cumprido', NOW())");
            $stmt->bind_param("id", $userId, $bonus_info['bonus_balance']);
            $stmt->execute();
        } else {
            // Apenas atualizar o progresso do rollover
            $stmt = $conn->prepare("UPDATE users SET bonus_rollover_completed = ? WHERE id = ?");
            $stmt->bind_param("di", $novo_rollover_completed, $userId);
            $stmt->execute();
        }
    }
    
    // Busca o saldo atualizado
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("Usuário não encontrado");
    }
    
    // Confirma a transação
    $conn->commit();
    
    // Retorna o resultado
    echo json_encode([
        "sucesso" => true,
        "ganhou" => $ganhou,
        "premio" => $premio,
        "saldo" => number_format($user["balance"], 2, ",", "."),
        "saldo_numerico" => $user["balance"]
    ]);
    
} catch (Exception $e) {
    // Desfaz a transação em caso de erro
    $conn->rollback();
    
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro interno do servidor",
        "detalhes" => $e->getMessage()
    ]);
}
?>
