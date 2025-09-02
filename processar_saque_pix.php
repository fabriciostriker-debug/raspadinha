<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

// Verificar se o usuário está logado
$userId = $_SESSION['usuario_id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Receber dados do formulário
    $valor = floatval($_POST['valor'] ?? 0);
    $tipoChave = $_POST['tipo_chave'] ?? '';
    $chavePix = $_POST['chave_pix'] ?? '';
    $nomeCompleto = $_POST['nome_completo'] ?? '';
    $cpf = $_POST['cpf'] ?? '';

    // Validações básicas
    if ($valor < 10) {
        echo json_encode(['success' => false, 'message' => 'Valor mínimo para saque é R$ 10,00']);
        exit;
    }

    if (empty($tipoChave) || empty($chavePix) || empty($nomeCompleto) || empty($cpf)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
        exit;
    }

    // Validar CPF (formato básico)
    $cpfLimpo = preg_replace('/\D/', '', $cpf);
    if (strlen($cpfLimpo) !== 11) {
        echo json_encode(['success' => false, 'message' => 'CPF deve ter 11 dígitos']);
        exit;
    }

    // Buscar saldo atual do usuário e informações de bônus
    $stmt = $conn->prepare("SELECT balance, bonus_balance, bonus_rollover_required, bonus_rollover_completed FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit;
    }

    $saldoAtual = floatval($usuario['balance']);
    $bonusBalance = floatval($usuario['bonus_balance']);
    $rolloverRequired = floatval($usuario['bonus_rollover_required']);
    $rolloverCompleted = floatval($usuario['bonus_rollover_completed']);

    // Verificar se há bônus ativo e rollover não cumprido
    if ($bonusBalance > 0 && $rolloverCompleted < $rolloverRequired) {
        $rolloverRestante = $rolloverRequired - $rolloverCompleted;
        echo json_encode([
            'success' => false, 
            'message' => 'Você possui um bônus ativo. Complete o rollover de R$ ' . number_format($rolloverRestante, 2, ',', '.') . ' para liberar o saque.'
        ]);
        exit;
    }

    // Verificar se o usuário tem saldo suficiente (apenas saldo real se há bônus ativo)
    if ($valor > $saldoAtual) {
        echo json_encode(['success' => false, 'message' => 'Saldo insuficiente']);
        exit;
    }

    // Criar tabela de saques se não existir
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS saques_pix (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            tipo_chave VARCHAR(20) NOT NULL,
            chave_pix VARCHAR(255) NOT NULL,
            nome_completo VARCHAR(255) NOT NULL,
            cpf VARCHAR(14) NOT NULL,
            status ENUM('pendente', 'processando', 'concluido', 'cancelado') DEFAULT 'pendente',
            data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_processamento TIMESTAMP NULL,
            observacoes TEXT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ";
    $conn->query($createTableQuery);

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // Inserir solicitação de saque
        $stmt = $conn->prepare("
            INSERT INTO saques_pix (user_id, valor, tipo_chave, chave_pix, nome_completo, cpf) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("idssss", $userId, $valor, $tipoChave, $chavePix, $nomeCompleto, $cpf);
        $stmt->execute();

        $saqueId = $conn->insert_id;

        // Atualizar saldo do usuário (debitar o valor)
        $novoSaldo = $saldoAtual - $valor;
        $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $stmt->bind_param("di", $novoSaldo, $userId);
        $stmt->execute();

        // Confirmar transação
        $conn->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Solicitação de saque enviada com sucesso!',
            'saque_id' => $saqueId,
            'novo_saldo' => $novoSaldo
        ]);

    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Erro ao processar saque: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
