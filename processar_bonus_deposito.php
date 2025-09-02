<?php
require_once 'includes/db.php';

// Função para log de debug específico para bônus - DESATIVADA
function logBonus($message, $data = null) {
    // Logging desativado conforme solicitado
    return;
}

// Configurar headers
header('Content-Type: application/json');

// Recebe o ID do depósito
$deposit_id = intval($_GET['deposit_id'] ?? 0);

if (!$deposit_id) {
    logBonus("Erro: ID do depósito não informado");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do depósito não informado']);
    exit;
}

logBonus("Processando bônus para depósito", ['deposit_id' => $deposit_id]);

try {
    // Busca o depósito aprovado
    $stmt = $conn->prepare("SELECT * FROM deposits WHERE id = ? AND status = 'pago'");
    $stmt->bind_param("i", $deposit_id);
    $stmt->execute();
    $deposito = $stmt->get_result()->fetch_assoc();
    
    if (!$deposito) {
        logBonus("Erro: Depósito não encontrado ou não aprovado", ['deposit_id' => $deposit_id]);
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Depósito não encontrado ou não aprovado']);
        exit;
    }
    
    logBonus("Depósito encontrado", $deposito);
    
    // Verificar se o bônus já foi aplicado
    $stmt = $conn->prepare("SELECT id FROM transacoes WHERE usuario_id = ? AND tipo = 'bonus_deposito' AND descricao LIKE '%Depósito ID: {$deposit_id}%'");
    $stmt->bind_param("i", $deposito['user_id']);
    $stmt->execute();
    $bonus_existente = $stmt->get_result()->fetch_assoc();
    
    if ($bonus_existente) {
        logBonus("Bônus já aplicado anteriormente", ['deposit_id' => $deposit_id, 'user_id' => $deposito['user_id']]);
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Bônus já aplicado anteriormente']);
        exit;
    }
    
    // Verificar se depósito em dobro está ativo e se o usuário optou por ele
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('double_deposit_enabled', 'double_deposit_rollover_multiplier')");
    $stmt->execute();
    $result = $stmt->get_result();
    $config = [];
    while ($row = $result->fetch_assoc()) {
        $config[$row['setting_key']] = $row['setting_value'];
    }
    
    logBonus("Configurações globais", $config);
    
    // Verificar se o usuário optou pelo depósito em dobro
    $stmt = $conn->prepare("SELECT double_deposit_opted FROM deposits WHERE id = ?");
    $stmt->bind_param("i", $deposit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $depositInfo = $result->fetch_assoc();
    $userOptedForDoubleDeposit = isset($depositInfo['double_deposit_opted']) && $depositInfo['double_deposit_opted'] == 1;
    
    logBonus("Verificação de opção de depósito em dobro", ['deposit_id' => $deposit_id, 'opted' => $userOptedForDoubleDeposit]);
    
    if (!isset($config['double_deposit_enabled']) || $config['double_deposit_enabled'] !== '1' || !$userOptedForDoubleDeposit) {
        $reason = !isset($config['double_deposit_enabled']) || $config['double_deposit_enabled'] !== '1' ? 
            'Depósito em dobro não está ativo globalmente' : 'Usuário não optou pelo depósito em dobro';
        logBonus($reason);
        http_response_code(200);
        echo json_encode(['status' => 'ignored', 'message' => $reason]);
        exit;
    }
    
    $rollover_multiplier = floatval($config['double_deposit_rollover_multiplier'] ?? 3);
    $bonus_amount = floatval($deposito['amount']); // Mesmo valor do depósito
    $rollover_required = $bonus_amount * $rollover_multiplier;
    
    logBonus("Calculando bônus", [
        'bonus_amount' => $bonus_amount,
        'rollover_multiplier' => $rollover_multiplier,
        'rollover_required' => $rollover_required
    ]);
    
    // Inicia transação
    $conn->begin_transaction();
    
    // Adicionar bônus e rollover
    $stmt = $conn->prepare("UPDATE users SET bonus_balance = bonus_balance + ?, bonus_rollover_required = bonus_rollover_required + ? WHERE id = ?");
    $stmt->bind_param("ddi", $bonus_amount, $rollover_required, $deposito['user_id']);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Falha ao atualizar saldo de bônus do usuário");
    }
    
    // Registrar transação de bônus
    $descricao_bonus = "Bônus de depósito em dobro - Depósito ID: {$deposit_id}";
    $stmt = $conn->prepare("INSERT INTO transacoes (usuario_id, tipo, valor, descricao, status) VALUES (?, 'bonus_deposito', ?, ?, 'concluido')");
    $stmt->bind_param("ids", $deposito['user_id'], $bonus_amount, $descricao_bonus);
    $stmt->execute();
    
    // Confirma a transação
    $conn->commit();
    
    logBonus("Bônus aplicado com sucesso", [
        'user_id' => $deposito['user_id'],
        'deposit_id' => $deposit_id,
        'bonus_amount' => $bonus_amount,
        'rollover_required' => $rollover_required
    ]);
    
    // Resposta de sucesso
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Bônus aplicado com sucesso',
        'data' => [
            'deposit_id' => $deposit_id,
            'user_id' => $deposito['user_id'],
            'bonus_amount' => $bonus_amount,
            'rollover_required' => $rollover_required
        ]
    ]);
    
} catch (Exception $e) {
    // Desfaz a transação em caso de erro
    $conn->rollback();
    logBonus("Erro ao processar bônus", [
        'deposit_id' => $deposit_id,
        'error' => $e->getMessage()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?>
