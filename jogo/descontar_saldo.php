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
if (!$data || !isset($data["valor_aposta"])) {
    http_response_code(400);
    echo json_encode(["erro" => "Dados inválidos"]);
    exit;
}

$userId = $_SESSION["usuario_id"];
$valorAposta = floatval($data["valor_aposta"]);

// Valida o valor da aposta
if ($valorAposta <= 0) {
    http_response_code(400);
    echo json_encode(["erro" => "Valor de aposta inválido"]);
    exit;
}

try {
    // Inicia uma transação para garantir consistência
    $conn->begin_transaction();
    
    // Busca o saldo atual do usuário (incluindo saldo bônus)
    $stmt = $conn->prepare("SELECT balance, bonus_balance, bonus_rollover_required, bonus_rollover_completed FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("Usuário não encontrado");
    }
    
    $saldoRegular = $user["balance"];
    $saldoBonus = isset($user["bonus_balance"]) ? $user["bonus_balance"] : 0;
    $saldoTotal = $saldoRegular + $saldoBonus;
    
    // Verifica se há saldo suficiente (total)
    if ($saldoTotal < $valorAposta) {
        throw new Exception("Saldo insuficiente");
    }
    
    // Determina de onde descontar o valor (primeiro do bônus, depois do regular)
    $valorDescontarBonus = 0;
    $valorDescontarRegular = 0;
    $rolloverCompleted = isset($user["bonus_rollover_completed"]) ? $user["bonus_rollover_completed"] : 0;
    $rolloverRequired = isset($user["bonus_rollover_required"]) ? $user["bonus_rollover_required"] : 0;
    
    if ($saldoBonus > 0) {
        $valorDescontarBonus = min($saldoBonus, $valorAposta);
        $valorDescontarRegular = $valorAposta - $valorDescontarBonus;
        
        // Atualizar o rollover completado
        $rolloverCompleted += $valorAposta;
        if ($rolloverCompleted > $rolloverRequired) {
            $rolloverCompleted = $rolloverRequired;
        }
    } else {
        $valorDescontarRegular = $valorAposta;
    }
    
    // Calcula os novos saldos
    $novoSaldoBonus = $saldoBonus - $valorDescontarBonus;
    $novoSaldoRegular = $saldoRegular - $valorDescontarRegular;
    $novoSaldoTotal = $novoSaldoBonus + $novoSaldoRegular;
    
    // Atualiza os saldos no banco de dados
    $stmt = $conn->prepare("UPDATE users SET balance = ?, bonus_balance = ?, bonus_rollover_completed = ? WHERE id = ?");
    $stmt->bind_param("dddi", $novoSaldoRegular, $novoSaldoBonus, $rolloverCompleted, $userId);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao atualizar saldo");
    }
    
    // Registra a transação no histórico
    $stmt = $conn->prepare("INSERT INTO transacoes (usuario_id, tipo, valor, descricao, data_criacao) VALUES (?, 'aposta_raspadinha', ?, 'Aposta em raspadinha', NOW())");
    $stmt->bind_param("id", $userId, $valorAposta);
    $stmt->execute(); // Não é crítico se falhar
    
    // Confirma a transação
    $conn->commit();
    
    // Formata o saldo para exibição
    $saldoFormatado = number_format($novoSaldoTotal, 2, ",", ".");
    
    // Retorna o resultado
    echo json_encode([
        "sucesso" => true,
        "saldo" => $saldoFormatado,
        "saldo_numerico" => $novoSaldoTotal,
        "saldo_regular" => $novoSaldoRegular,
        "saldo_bonus" => $novoSaldoBonus,
        "rollover_completed" => $rolloverCompleted,
        "rollover_required" => $rolloverRequired
    ]);
    
} catch (Exception $e) {
    // Desfaz a transação em caso de erro
    $conn->rollback();
    
    // Determina o código de status HTTP
    $statusCode = 500;
    $mensagem = "Erro interno do servidor";
    
    if ($e->getMessage() === "Saldo insuficiente") {
        $statusCode = 400;
        $mensagem = "Saldo insuficiente";
    }
    
    http_response_code($statusCode);
    echo json_encode([
        "erro" => $mensagem,
        "detalhes" => $e->getMessage()
    ]);
}
?>
