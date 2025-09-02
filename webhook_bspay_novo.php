<?php
require_once 'includes/db.php';
require_once 'includes/affiliate_functions.php';

function logWebhook($message, $data = null) {
    return;
}

header('Content-Type: application/json');

$input = file_get_contents('php://input');
logWebhook("Webhook recebido", ['raw_input' => $input, 'headers' => getallheaders()]);

logWebhook("Informações do servidor", [
    'SERVER_VARS' => $_SERVER,
    'POST' => $_POST,
    'GET' => $_GET
]);

$data = json_decode($input, true);

if (!$data) {
    logWebhook("Erro: JSON inválido");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'JSON inválido']);
    exit;
}

logWebhook("Dados decodificados", $data);

if (isset($data['requestBody'])) {
    $eventData = $data['requestBody'];
    logWebhook("Usando formato requestBody", $eventData);
} else {
    $eventData = $data;
    logWebhook("Usando formato direto", $eventData);
}

$external_id = $eventData['external_id'] ?? '';
$amount = floatval($eventData['amount'] ?? 0);
$status = $eventData['status'] ?? '';
$transactionType = $eventData['transactionType'] ?? '';
$transactionId = $eventData['transactionId'] ?? '';

logWebhook("Dados extraídos", [
    'external_id' => $external_id,
    'amount' => $amount,
    'status' => $status,
    'transactionType' => $transactionType,
    'transactionId' => $transactionId
]);

if ($transactionType === 'RECEIVEPIX' && $status === 'PAID') {
    logWebhook("Iniciando processamento de transação RECEIVEPIX PAID");
    
    if ($external_id && $amount > 0) {
        $stmt = $conn->prepare("SELECT * FROM deposits WHERE external_id = ? AND status IN ('pendente', 'pending')");
        $stmt->bind_param("s", $external_id);
        $stmt->execute();
        $result = $stmt->get_result();
        logWebhook("Query SQL para deposits", ["query" => "SELECT * FROM deposits WHERE external_id = '$external_id' AND status IN ('pendente', 'pending')"]);
        
        if (!$result) {
            logWebhook("Erro na consulta SQL", ["error" => $conn->error]);
        }
        
        $deposito = $result->fetch_assoc();
        
        logWebhook("Busca depósito", [
            'external_id' => $external_id, 
            'found' => $deposito ? 'sim' : 'não',
            'deposit_data' => $deposito
        ]);
        
        if ($deposito) {
            if ($deposito['status'] === 'pago' || $deposito['status'] === 'paid') {
                logWebhook("Depósito já processado", ['external_id' => $external_id]);
                http_response_code(200);
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Pagamento já processado anteriormente',
                    'external_id' => $external_id
                ]);
                exit;
            }
            
            $conn->begin_transaction();
            
            try {
                $stmt = $conn->prepare("UPDATE deposits SET status = 'pago', payment_id = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $transactionId, $deposito['id']);
                $stmt->execute();
                
                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->bind_param("di", $amount, $deposito['user_id']);
                logWebhook("Atualizando saldo do usuário", [
                    "query" => "UPDATE users SET balance = balance + $amount WHERE id = {$deposito['user_id']}",
                    "user_id" => $deposito['user_id'],
                    "amount" => $amount
                ]);
                $stmt->execute();
                $affected_rows = $stmt->affected_rows;
                
                logWebhook("Resultado da atualização de saldo", [
                    "affected_rows" => $affected_rows,
                    "error" => $conn->error ?: "Nenhum erro reportado"
                ]);
                
                if ($affected_rows > 0) {
                    $stmt = $conn->prepare("INSERT INTO transacoes (usuario_id, tipo, valor, descricao, status) VALUES (?, 'deposito_aprovado', ?, 'Depósito aprovado via BSPay', 'concluido')");
                    $stmt->bind_param("id", $deposito['user_id'], $amount);
                    $stmt->execute();
                    
                    
                    $conn->commit();
                    
                    $stmt = $conn->prepare("SELECT referrer_id FROM users WHERE id = ?");
                    $stmt->bind_param("i", $deposito['user_id']);
                    $stmt->execute();
                    $user_data = $stmt->get_result()->fetch_assoc();
                    
                    if ($user_data && $user_data['referrer_id']) {
                        calculateAndRegisterCommissions(
                            $conn,
                            $deposito['user_id'],
                            'deposit',
                            $amount,
                            $user_data['referrer_id'],
                            1
                        );
                    }
                    
                    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM global_settings WHERE setting_key IN ('pushover_enabled', 'pushover_api_token', 'pushover_user_key', 'pushover_notify_pix_paid')");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $pushover_config = [];
                    
                    while ($row = $result->fetch_assoc()) {
                        $pushover_config[$row['setting_key']] = $row['setting_value'];
                    }
                    
                    if (isset($pushover_config['pushover_enabled']) && $pushover_config['pushover_enabled'] === '1' && 
                        isset($pushover_config['pushover_notify_pix_paid']) && $pushover_config['pushover_notify_pix_paid'] === '1') {
                        if (!empty($pushover_config['pushover_api_token']) && !empty($pushover_config['pushover_user_key'])) {
                            $message = "Depósito pix aprovado✅ R$ " . number_format($amount, 2, ',', '.');
                            
                            $ch = curl_init();
                            curl_setopt_array($ch, array(
                                CURLOPT_URL => "https://api.pushover.net/1/messages.json",
                                CURLOPT_POSTFIELDS => array(
                                    "token" => $pushover_config['pushover_api_token'],
                                    "user" => $pushover_config['pushover_user_key'],
                                    "message" => $message,
                                ),
                                CURLOPT_SAFE_UPLOAD => true,
                                CURLOPT_RETURNTRANSFER => true,
                            ));
                            $response = curl_exec($ch);
                            curl_close($ch);
                            
                            logWebhook("Notificação Pushover enviada", [
                                'message' => $message,
                                'response' => $response
                            ]);
                        }
                    }
                    
                    logWebhook("Depósito aprovado com sucesso", [
                        'user_id' => $deposito['user_id'],
                        'amount' => $amount,
                        'external_id' => $external_id,
                        'transaction_id' => $transactionId
                    ]);
                    
                    $bonus_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/processar_bonus_deposito.php?deposit_id=' . $deposito['id'];
                    
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 1, //
                            'ignore_errors' => true
                        ]
                    ]);
                    file_get_contents($bonus_url, false, $context);
                    
                    logWebhook("Chamada para processamento de bônus enviada", [
                        'bonus_url' => $bonus_url,
                        'deposit_id' => $deposito['id']
                    ]);
                    
                    http_response_code(200);
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'Pagamento processado com sucesso',
                        'external_id' => $external_id,
                        'amount' => $amount,
                        'user_id' => $deposito['user_id']
                    ]);
                } else {
                    throw new Exception("Falha ao atualizar saldo do usuário");
                }
                
            } catch (Exception $e) {
                $conn->rollback();
                logWebhook("Erro ao processar depósito", [
                    'error' => $e->getMessage(),
                    'external_id' => $external_id
                ]);
                
                http_response_code(500);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Erro interno do servidor: ' . $e->getMessage()
                ]);
            }
            
        } else {
            logWebhook("Depósito não encontrado", [
                'external_id' => $external_id,
                'amount' => $amount
            ]);
            
            $stmt = $conn->prepare("SELECT * FROM depositos WHERE codigo_transacao = ? AND status = 'pendente'");
            $stmt->bind_param("s", $external_id);
            logWebhook("Verificando na tabela alternativa", [
                "query" => "SELECT * FROM depositos WHERE codigo_transacao = '$external_id' AND status = 'pendente'"
            ]);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result) {
                logWebhook("Erro na consulta SQL (tabela depositos)", ["error" => $conn->error]);
            }
            
            $deposito_alt = $result->fetch_assoc();
            
            if ($deposito_alt) {
                logWebhook("Depósito encontrado na tabela depositos", $deposito_alt);
                
                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare("UPDATE depositos SET status = 'aprovado', data_aprovacao = NOW() WHERE id = ?");
                    $stmt->bind_param("i", $deposito_alt['id']);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->bind_param("di", $amount, $deposito_alt['usuario_id']);
                    logWebhook("Atualizando saldo do usuário (tabela depositos)", [
                        "query" => "UPDATE users SET balance = balance + $amount WHERE id = {$deposito_alt['usuario_id']}",
                        "user_id" => $deposito_alt['usuario_id'],
                        "amount" => $amount
                    ]);
                    $stmt->execute();
                    $affected_rows = $stmt->affected_rows;
                    
                    logWebhook("Resultado da atualização de saldo (tabela depositos)", [
                        "affected_rows" => $affected_rows,
                        "error" => $conn->error ?: "Nenhum erro reportado"
                    ]);
                    
                    $conn->commit();
                    
                    $stmt = $conn->prepare("SELECT referrer_id FROM users WHERE id = ?");
                    $stmt->bind_param("i", $deposito_alt['usuario_id']);
                    $stmt->execute();
                    $user_data = $stmt->get_result()->fetch_assoc();
                    
                    if ($user_data && $user_data['referrer_id']) {
                        calculateAndRegisterCommissions(
                            $conn,
                            $deposito_alt['usuario_id'],
                            'deposit',
                            $amount,
                            $user_data['referrer_id'],
                            1
                        );
                    }
                    
                    logWebhook("Depósito aprovado (tabela depositos)", [
                        'user_id' => $deposito_alt['usuario_id'],
                        'amount' => $amount,
                        'external_id' => $external_id
                    ]);
                    
                    http_response_code(200);
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'Pagamento processado com sucesso',
                        'external_id' => $external_id,
                        'amount' => $amount
                    ]);
                } catch (Exception $e) {
                    $conn->rollback();
                    logWebhook("Erro ao processar depósito (tabela depositos)", ['error' => $e->getMessage()]);
                    
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Erro interno do servidor']);
                }
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Depósito não encontrado',
                    'external_id' => $external_id,
                    'searched_amount' => $amount
                ]);
            }
        }
    } else {
        logWebhook("Dados insuficientes", [
            'external_id' => $external_id,
            'amount' => $amount
        ]);
        
        http_response_code(400);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Dados insuficientes',
            'received_external_id' => $external_id,
            'received_amount' => $amount
        ]);
    }
} else {
    logWebhook("Evento não processado", [
        'transactionType' => $transactionType,
        'status' => $status,
        'full_data' => $eventData
    ]);
    
    http_response_code(200);
    echo json_encode([
        'status' => 'ignored', 
        'message' => 'Evento não processado - tipo ou status não correspondem',
        'transactionType' => $transactionType,
        'status' => $status,
        'expected' => ['transactionType' => 'RECEIVEPIX', 'status' => 'PAID']
    ]);
}
?>
