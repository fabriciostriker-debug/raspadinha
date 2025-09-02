<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';
require_once 'includes/facebook_pixel.php';

$pixel_code = generate_pixel_code('PageView');

// Verifica se o usuário está logado
$userId = $_SESSION['usuario_id'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}

// Busca dados do usuário incluindo verificação de admin e saldo bônus
$stmt = $conn->prepare("SELECT name, email, balance, bonus_balance, bonus_rollover_required, bonus_rollover_completed, is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Verifica se o usuário é admin
$isAdmin = isset($usuario['is_admin']) && $usuario['is_admin'] == 1;

// Verifica se o usuário é um afiliado ativo
$stmt = $conn->prepare("SELECT id FROM affiliates WHERE user_id = ? AND is_active = 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$isAffiliate = $result->num_rows > 0;

// Verifica se é um agente
$stmt = $conn->prepare("SELECT is_agent FROM users WHERE id = ? AND is_agent = 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$isAgent = $result->num_rows > 0;

// Processa alteração de senha
$mensagem = "";
$tipoMensagem = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nova_senha'])) {
    $novaSenha = $_POST['nova_senha'];
    $confirmarSenha = $_POST['confirmar_senha'];

    if ($novaSenha === $confirmarSenha && strlen($novaSenha) >= 6) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $senhaHash, $userId);
        $update->execute();
        $mensagem = "Senha alterada com sucesso!";
        $tipoMensagem = "sucesso";
    } else {
        $mensagem = "Erro: as senhas não coincidem ou são muito curtas (mínimo 6 caracteres).";
        $tipoMensagem = "erro";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Raspadinhas</title>
    
    <!-- Facebook Pixel Code -->
    <?php echo $pixel_code; ?>
    <!-- End Facebook Pixel Code -->
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        black: '#000000',
                        white: '#ffffff',
                        accent: '#22c55e',
                        'neon-green': '#00ff88',
                        'dark-bg': '#0a0a0f',
                        'card-bg': '#1a1a2e',
                        'accent-gold': '#ffd700',
                        'accent-purple': '#8b5cf6',
                        'accent-blue': '#1e40af',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/mobile_nav.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/perfil.css">
</head>
<body class="font-sans perfil-page">
    <!-- Header -->
    <div class="profile-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center profile-header-content">
            <h1 class="text-4xl font-bold mb-2">Meu Perfil</h1>
            <p class="text-lg mb-6 text-center">Gerencie suas informações, atividades e saldo.</p>
            <div class="profile-avatar">
                <div class="profile-avatar-inner">
                    <i class="fas fa-user-circle profile-avatar-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <!-- Profile Stats Summary -->
        <div class="mt-16 mb-8">
            <div class="stat-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-value">R$ <?= number_format($usuario['balance'] + $usuario['bonus_balance'], 2, ',', '.') ?></div>
                    <div class="stat-label">Saldo Atual</div>
                </div>
                
                <?php
                // Contagem de depósitos concluídos
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM deposits WHERE user_id = ? AND status = 'pago'");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $totalDepositos = $result->fetch_assoc()['total'];
                ?>
                <div class="stat-card stat-secondary">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-value"><?= $totalDepositos ?></div>
                    <div class="stat-label">Depósitos</div>
                </div>
                
                <?php
                // Contagem de jogadas
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM jogadas_raspadinha WHERE usuario_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $totalJogadas = $result->fetch_assoc()['total'];
                ?>
                <div class="stat-card stat-accent">
                    <div class="stat-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="stat-value"><?= $totalJogadas ?></div>
                    <div class="stat-label">Jogadas</div>
                </div>
                
                <?php
                // Contagem de saques concluídos
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM saques_pix WHERE user_id = ? AND status = 'concluido'");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $totalSaques = $result->fetch_assoc()['total'];
                ?>
                <div class="stat-card stat-gold">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-value"><?= $totalSaques ?></div>
                    <div class="stat-label">Saques</div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-4 mb-8">
            <a href="inicio.php" class="btn btn-secondary">
                <i class="fas fa-home mr-2"></i>
                Voltar para Início
            </a>
            <button id="openModalBtn" class="btn btn-primary" onclick="if(typeof fbq !== 'undefined') fbq('track', 'InitiateCheckout');">
                <i class="fas fa-money-bill-wave mr-2"></i>
                Sacar via Pix
            </button>
            
            <?php if ($isAffiliate): ?>
            <a href="affiliate_dashboard.php" class="btn btn-accent">
                <i class="fas fa-users mr-2"></i>
                Painel de Afiliado
            </a>
            <?php endif; ?>
            
            <?php if ($isAdmin): ?>
            <a href="admin/index.php" class="btn btn-danger">
                <i class="fas fa-user-shield mr-2"></i>
                Acessar Admin
            </a>
            <?php endif; ?>
        </div>

        <!-- Navigation Tabs -->
        <div class="profile-tabs">
            <div class="profile-tab active" data-tab="visao-geral">
                <i class="fas fa-th-large profile-tab-icon"></i>
                Visão Geral
            </div>
            <div class="profile-tab" data-tab="transacoes">
                <i class="fas fa-exchange-alt profile-tab-icon"></i>
                Transações
            </div>
            <div class="profile-tab" data-tab="seguranca">
                <i class="fas fa-shield-alt profile-tab-icon"></i>
                Segurança
            </div>
            <div class="profile-tab" data-tab="perfil">
                <i class="fas fa-user profile-tab-icon"></i>
                Perfil
            </div>
        </div>

        <!-- Tab Contents -->
        
        <!-- Visão Geral Tab -->
        <div id="visao-geral" class="tab-content active">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
                <!-- Coluna Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Balance Card (Mobile Only) -->
                    <div class="lg:hidden mb-6">
                        <div class="balance-card">
                            <div class="balance-label">Saldo Disponível</div>
                            <div class="balance-amount">R$ <?= number_format($usuario['balance'] + $usuario['bonus_balance'], 2, ',', '.') ?></div>
                            <div class="balance-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                Faça depósitos para aumentar seu saldo
                            </div>
                        </div>
                    </div>
                
                    <!-- Últimas Jogadas -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-gamepad card-title-icon"></i>
                                Últimas Jogadas
                            </h2>
                            <a href="#" class="text-sm text-accent hover:underline" onclick="changeTab('transacoes')">Ver Tudo</a>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $conn->prepare("SELECT resultado, premio, valor_aposta, data_jogada FROM jogadas_raspadinha WHERE usuario_id = ? ORDER BY data_jogada DESC LIMIT 3");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $jogadas = $stmt->get_result();
                            ?>
                            <div class="space-y-3">
                                <?php if ($jogadas->num_rows > 0): ?>
                                    <?php while($jogada = $jogadas->fetch_assoc()): ?>
                                        <div class="info-item">
                                            <div class="info-item-content">
                                                <div class="info-item-icon game">
                                                    <i class="fas fa-ticket-alt"></i>
                                                </div>
                                                <div class="info-item-details">
                                                    <div class="info-item-title">Raspadinha</div>
                                                    <div class="info-item-subtitle"><?= date('d/m/Y H:i', strtotime($jogada['data_jogada'])) ?></div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="info-item-value <?= $jogada['resultado'] === 'ganhou' ? 'text-green-500' : 'text-red-500' ?>">
                                                    <?= $jogada['resultado'] === 'ganhou' ? '+R$ ' . number_format($jogada['premio'], 2, ',', '.') : '-R$ ' . number_format($jogada['valor_aposta'], 2, ',', '.') ?>
                                                </div>
                                                <div class="info-item-status <?= $jogada['resultado'] === 'ganhou' ? 'status-win' : 'status-loss' ?>">
                                                    <?= $jogada['resultado'] === 'ganhou' ? 'Ganhou' : 'Perdeu' ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-ticket-alt empty-icon"></i>
                                        <p class="empty-text">Você ainda não fez nenhuma jogada</p>
                                        <a href="jogo/index.php" class="btn btn-primary">Jogar Agora</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Últimos Depósitos -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-money-bill-wave card-title-icon"></i>
                                Últimos Depósitos
                            </h2>
                            <a href="#" class="text-sm text-accent hover:underline" onclick="changeTab('transacoes')">Ver Tudo</a>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $conn->prepare("SELECT amount as valor, status, created_at as data_criacao FROM deposits WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $depositos = $stmt->get_result();
                            ?>
                            <div class="space-y-3">
                                <?php if ($depositos->num_rows > 0): ?>
                                    <?php while($deposito = $depositos->fetch_assoc()): ?>
                                        <div class="info-item">
                                            <div class="info-item-content">
                                                <div class="info-item-icon deposit">
                                                    <i class="fas fa-plus"></i>
                                                </div>
                                                <div class="info-item-details">
                                                    <div class="info-item-title">Depósito PIX</div>
                                                    <div class="info-item-subtitle"><?= date('d/m/Y H:i', strtotime($deposito['data_criacao'])) ?></div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="info-item-value">R$ <?= number_format($deposito['valor'], 2, ',', '.') ?></div>
                                                <div class="info-item-status <?= $deposito['status'] === 'pago' ? 'status-completed' : 'status-pending' ?>">
                                                    <?= $deposito['status'] === 'pago' ? 'Concluído' : 'Pendente' ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-money-bill-wave empty-icon"></i>
                                        <p class="empty-text">Você ainda não fez nenhum depósito</p>
                                        <button class="btn btn-primary" onclick="abrirDeposito()">Fazer Depósito</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Balance Card (Desktop Only) -->
                    <div class="hidden lg:block">
                        <div class="balance-card">
                            <div class="balance-label">Saldo Disponível</div>
                            <div class="balance-amount">R$ <?= number_format($usuario['balance'] + $usuario['bonus_balance'], 2, ',', '.') ?></div>
                            <div class="balance-trend trend-up">
                                <i class="fas fa-arrow-up"></i>
                                Faça depósitos para aumentar seu saldo
                            </div>
                        </div>
                        <div class="flex flex-col gap-3 mt-4">
                            <button id="openModalBtnDesktop" class="btn btn-primary w-full" onclick="if(typeof fbq !== 'undefined') fbq('track', 'InitiateCheckout');">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                Sacar via Pix
                            </button>
                            <button class="btn btn-secondary w-full" onclick="abrirDeposito()">
                                <i class="fas fa-plus mr-2"></i>
                                Depositar
                            </button>
                        </div>
                    </div>
                    
                    <?php 
                    // Verificar configuração global para exibir a caixa de rollover
                    $stmt = $conn->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'show_rollover_box'");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $showRolloverBox = $result->fetch_assoc()['setting_value'] ?? '0';
                    
                    $bonusBalance = floatval($usuario['bonus_balance'] ?? 0);
                    $rolloverRequired = floatval($usuario['bonus_rollover_required'] ?? 0);
                    $rolloverCompleted = floatval($usuario['bonus_rollover_completed'] ?? 0);
                    
                    // Exibir a caixa de rollover apenas se estiver ativado nas configurações globais
                    if ($showRolloverBox == '1' && $bonusBalance > 0 && $rolloverRequired > 0): 
                        $rolloverProgress = $rolloverRequired > 0 ? ($rolloverCompleted / $rolloverRequired) * 100 : 0;
                        $rolloverRestante = max(0, $rolloverRequired - $rolloverCompleted);
                    ?>
                    <!-- Card de Bônus Ativo -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-gift card-title-icon"></i>
                                Bônus Ativo
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 bg-yellow-900/20 rounded-lg">
                                    <span class="text-sm">Saldo Bônus</span>
                                    <span class="font-bold text-yellow-400">R$ <?= number_format($bonusBalance, 2, ',', '.') ?></span>
                                </div>
                                
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <div class="flex justify-between text-sm mb-2">
                                        <span>Progresso do Rollover</span>
                                        <span><?= number_format($rolloverProgress, 1) ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-700 rounded-full h-2 mb-2">
                                        <div class="bg-gradient-to-r from-yellow-500 to-yellow-400 h-2 rounded-full transition-all duration-300" 
                                             style="width: <?= min(100, $rolloverProgress) ?>%"></div>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Faltam R$ <?= number_format($rolloverRestante, 2, ',', '.') ?> em apostas
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <p class="text-xs text-gray-400 mb-2">Complete o rollover para liberar o bônus e saques</p>
                                    <a href="jogo/index.php" class="btn btn-primary btn-sm w-full">
                                        <i class="fas fa-gamepad mr-2"></i>
                                        Jogar para Completar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Informações Pessoais -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-user card-title-icon"></i>
                                Informações
                            </h2>
                            <a href="#" class="text-sm text-accent hover:underline" onclick="changeTab('perfil')">Editar</a>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-400">Nome</span>
                                    <span class="font-semibold"><?= htmlspecialchars($usuario['name']) ?></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-400">E-mail</span>
                                    <span class="font-semibold"><?= htmlspecialchars($usuario['email']) ?></span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-400">Status</span>
                                    <span class="font-semibold flex items-center gap-1 text-accent">
                                        <i class="fas fa-check-circle"></i>
                                        Ativo
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($isAffiliate): ?>
                    <!-- Card de Afiliados -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-users card-title-icon"></i>
                                Programa de Afiliados
                            </h2>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-gray-400 mb-4">Acesse seu painel de afiliado para ver comissões e indicações</p>
                            <a href="affiliate_dashboard.php" class="btn btn-accent w-full">
                                <i class="fas fa-users mr-2"></i>
                                Acessar Painel
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($isAdmin): ?>
                    <!-- Card de Painel Admin -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-user-shield card-title-icon"></i>
                                Administrativo
                            </h2>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-gray-400 mb-4">Acesso ao painel de administração do sistema</p>
                            <a href="admin/index.php" class="btn btn-danger w-full">
                                <i class="fas fa-user-shield mr-2"></i>
                                Acessar Admin
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Transações Tab -->
        <div id="transacoes" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
                <!-- Transações Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Histórico de Depósitos -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-money-bill-wave card-title-icon"></i>
                                Histórico de Depósitos
                            </h2>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $conn->prepare("SELECT amount as valor, status, created_at as data_criacao FROM deposits WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $depositos = $stmt->get_result();
                            ?>
                            <div class="space-y-3">
                                <?php if ($depositos->num_rows > 0): ?>
                                    <?php while($deposito = $depositos->fetch_assoc()): ?>
                                        <div class="info-item">
                                            <div class="info-item-content">
                                                <div class="info-item-icon deposit">
                                                    <i class="fas fa-plus"></i>
                                                </div>
                                                <div class="info-item-details">
                                                    <div class="info-item-title">Depósito PIX</div>
                                                    <div class="info-item-subtitle"><?= date('d/m/Y H:i', strtotime($deposito['data_criacao'])) ?></div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="info-item-value">R$ <?= number_format($deposito['valor'], 2, ',', '.') ?></div>
                                                <div class="info-item-status <?= $deposito['status'] === 'pago' ? 'status-completed' : 'status-pending' ?>">
                                                    <?= $deposito['status'] === 'pago' ? 'Concluído' : 'Pendente' ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-money-bill-wave empty-icon"></i>
                                        <p class="empty-text">Nenhum depósito encontrado</p>
                                        <button class="btn btn-primary" onclick="abrirDeposito()">Fazer Depósito</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Histórico de Jogadas -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-gamepad card-title-icon"></i>
                                Histórico de Jogadas
                            </h2>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $conn->prepare("SELECT resultado, premio, valor_aposta, data_jogada FROM jogadas_raspadinha WHERE usuario_id = ? ORDER BY data_jogada DESC LIMIT 10");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $jogadas = $stmt->get_result();
                            ?>
                            <div class="space-y-3">
                                <?php if ($jogadas->num_rows > 0): ?>
                                    <?php while($jogada = $jogadas->fetch_assoc()): ?>
                                        <div class="info-item">
                                            <div class="info-item-content">
                                                <div class="info-item-icon game">
                                                    <i class="fas fa-ticket-alt"></i>
                                                </div>
                                                <div class="info-item-details">
                                                    <div class="info-item-title">Raspadinha</div>
                                                    <div class="info-item-subtitle"><?= date('d/m/Y H:i', strtotime($jogada['data_jogada'])) ?></div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="info-item-value <?= $jogada['resultado'] === 'ganhou' ? 'text-green-500' : 'text-red-500' ?>">
                                                    <?= $jogada['resultado'] === 'ganhou' ? '+R$ ' . number_format($jogada['premio'], 2, ',', '.') : '-R$ ' . number_format($jogada['valor_aposta'], 2, ',', '.') ?>
                                                </div>
                                                <div class="info-item-status <?= $jogada['resultado'] === 'ganhou' ? 'status-win' : 'status-loss' ?>">
                                                    <?= $jogada['resultado'] === 'ganhou' ? 'Ganhou' : 'Perdeu' ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-gamepad empty-icon"></i>
                                        <p class="empty-text">Nenhuma jogada encontrada</p>
                                        <a href="jogo/index.php" class="btn btn-primary">Jogar Agora</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Histórico de Saques -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-hand-holding-usd card-title-icon"></i>
                                Histórico de Saques
                            </h2>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $conn->prepare("SELECT valor, status, data_solicitacao FROM saques_pix WHERE user_id = ? ORDER BY data_solicitacao DESC LIMIT 10");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $saques = $stmt->get_result();
                            ?>
                            <div class="space-y-3">
                                <?php if ($saques->num_rows > 0): ?>
                                    <?php while($saque = $saques->fetch_assoc()): ?>
                                        <div class="info-item">
                                            <div class="info-item-content">
                                                <div class="info-item-icon withdraw">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </div>
                                                <div class="info-item-details">
                                                    <div class="info-item-title">Saque PIX</div>
                                                    <div class="info-item-subtitle"><?= date('d/m/Y H:i', strtotime($saque['data_solicitacao'])) ?></div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="info-item-value">R$ <?= number_format($saque['valor'], 2, ',', '.') ?></div>
                                                <div class="info-item-status <?= $saque['status'] === 'concluido' ? 'status-completed' : 'status-pending' ?>">
                                                    <?= $saque['status'] === 'concluido' ? 'Concluído' : 'Pendente' ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-hand-holding-usd empty-icon"></i>
                                        <p class="empty-text">Nenhum saque encontrado</p>
                                        <button id="openModalBtnEmpty" class="btn btn-primary">Fazer Saque</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Transações -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-chart-pie card-title-icon"></i>
                                Resumo Financeiro
                            </h2>
                        </div>
                        <div class="card-body">
                            <?php
                            // Somatório de depósitos
                            $stmt = $conn->prepare("SELECT SUM(amount) as total FROM deposits WHERE user_id = ? AND status = 'pago'");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $totalDepositos = $result->fetch_assoc()['total'] ?: 0;
                            
                            // Somatório de saques
                            $stmt = $conn->prepare("SELECT SUM(valor) as total FROM saques_pix WHERE user_id = ? AND status = 'concluido'");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $totalSaques = $result->fetch_assoc()['total'] ?: 0;
                            
                            // Ganhos em jogos
                            $stmt = $conn->prepare("SELECT SUM(premio) as total FROM jogadas_raspadinha WHERE usuario_id = ? AND resultado = 'ganhou'");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $totalGanhos = $result->fetch_assoc()['total'] ?: 0;
                            
                            // Gastos em jogos
                            $stmt = $conn->prepare("SELECT SUM(valor_aposta) as total FROM jogadas_raspadinha WHERE usuario_id = ?");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $totalApostas = $result->fetch_assoc()['total'] ?: 0;
                            
                            $lucroJogos = $totalGanhos - $totalApostas;
                            ?>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 bg-emerald-900/20 rounded-lg">
                                    <span class="text-sm">Total Depositado</span>
                                    <span class="font-bold text-emerald-400">R$ <?= number_format($totalDepositos, 2, ',', '.') ?></span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-purple-900/20 rounded-lg">
                                    <span class="text-sm">Total Sacado</span>
                                    <span class="font-bold text-purple-400">R$ <?= number_format($totalSaques, 2, ',', '.') ?></span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-amber-900/20 rounded-lg">
                                    <span class="text-sm">Ganhos em Jogos</span>
                                    <span class="font-bold text-amber-400">R$ <?= number_format($totalGanhos, 2, ',', '.') ?></span>
                                </div>
                                <div class="flex justify-between items-center p-3 <?= $lucroJogos >= 0 ? 'bg-emerald-900/20' : 'bg-red-900/20' ?> rounded-lg">
                                    <span class="text-sm">Lucro em Jogos</span>
                                    <span class="font-bold <?= $lucroJogos >= 0 ? 'text-emerald-400' : 'text-red-400' ?>">
                                        <?= $lucroJogos >= 0 ? '+' : '' ?>R$ <?= number_format($lucroJogos, 2, ',', '.') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-question-circle card-title-icon"></i>
                                Precisa de Ajuda?
                            </h2>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-gray-400 mb-4">Se tiver algum problema com transações, nossa equipe de suporte está pronta para ajudar</p>
                            <a href="<?php echo htmlspecialchars($suporte_url ?? '#'); ?>" target="_blank" class="btn btn-secondary w-full">
                                <i class="fas fa-headset mr-2"></i>
                                Falar com Suporte
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Segurança Tab -->
        <div id="seguranca" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
                <div class="lg:col-span-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-lock card-title-icon"></i>
                                Alterar Senha
                            </h2>
                        </div>
                        <div class="card-body">
                            <?php if ($mensagem && $tipoMensagem === 'sucesso'): ?>
                                <div class="success-message mb-6">
                                    <i class="fas fa-check-circle"></i>
                                    <div><?= $mensagem ?></div>
                                </div>
                            <?php elseif ($mensagem && $tipoMensagem === 'erro'): ?>
                                <div class="error-message mb-6">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <div><?= $mensagem ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" class="space-y-4">
                                <div class="form-group">
                                    <label class="form-label" for="nova_senha">Nova Senha</label>
                                    <input type="password" id="nova_senha" name="nova_senha" class="form-input" required minlength="6">
                                    <p class="text-xs text-gray-400 mt-1">Use pelo menos 6 caracteres</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="confirmar_senha">Confirmar Nova Senha</label>
                                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-input" required minlength="6">
                                </div>
                                <button type="submit" class="btn btn-primary w-full">
                                    <i class="fas fa-save mr-2"></i>
                                    Alterar Senha
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-shield-alt card-title-icon"></i>
                                Dicas de Segurança
                            </h2>
                        </div>
                        <div class="card-body">
                            <ul class="space-y-3 text-sm">
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-accent mt-1"></i>
                                    <span>Use senhas fortes com letras, números e símbolos</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-accent mt-1"></i>
                                    <span>Nunca compartilhe sua senha com outras pessoas</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-accent mt-1"></i>
                                    <span>Evite usar a mesma senha em vários sites</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-accent mt-1"></i>
                                    <span>Certifique-se de estar em um dispositivo seguro ao fazer login</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-accent mt-1"></i>
                                    <span>Realize saques apenas para contas em seu nome</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-history card-title-icon"></i>
                                Atividade Recente
                            </h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-4 border-b border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-full bg-blue-900/30 text-blue-400">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium">Login bem-sucedido</p>
                                        <p class="text-xs text-gray-400"><?= date('d/m/Y H:i') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-full bg-green-900/30 text-green-400">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium">Conta segura</p>
                                        <p class="text-xs text-gray-400">Nenhuma atividade suspeita</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Perfil Tab -->
        <div id="perfil" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
                <div class="lg:col-span-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-user card-title-icon"></i>
                                Informações Pessoais
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="form-label">Nome Completo</label>
                                        <div class="form-input bg-opacity-50 flex items-center"><?= htmlspecialchars($usuario['name']) ?></div>
                                    </div>
                                    <div>
                                        <label class="form-label">E-mail</label>
                                        <div class="form-input bg-opacity-50 flex items-center"><?= htmlspecialchars($usuario['email']) ?></div>
                                    </div>
                                </div>
                                <div class="border-t border-gray-700 pt-6">
                                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                                        <i class="fas fa-id-card text-accent"></i>
                                        Informações da Conta
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="form-label">Status da Conta</label>
                                            <div class="px-4 py-3 rounded-lg bg-green-900/20 text-green-400 flex items-center gap-2">
                                                <i class="fas fa-check-circle"></i>
                                                Verificada
                                            </div>
                                        </div>
                                        <div>
                                            <label class="form-label">Nível de Acesso</label>
                                            <div class="px-4 py-3 rounded-lg bg-blue-900/20 text-blue-400 flex items-center gap-2">
                                                <?php if ($isAdmin): ?>
                                                <i class="fas fa-user-shield"></i>
                                                Administrador
                                                <?php elseif ($isAffiliate): ?>
                                                <i class="fas fa-users"></i>
                                                Afiliado
                                                <?php else: ?>
                                                <i class="fas fa-user"></i>
                                                Jogador
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-cog card-title-icon"></i>
                                Preferências
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 rounded-lg border border-gray-700">
                                    <div>
                                        <p class="font-medium">Notificações por E-mail</p>
                                        <p class="text-xs text-gray-400">Receba atualizações sobre saques e promoções</p>
                                    </div>
                                    <div class="relative">
                                        <div class="w-12 h-6 bg-gray-700 rounded-full"></div>
                                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full"></div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center p-3 rounded-lg border border-gray-700">
                                    <div>
                                        <p class="font-medium">Tema Escuro</p>
                                        <p class="text-xs text-gray-400">Ativado por padrão</p>
                                    </div>
                                    <div class="relative">
                                        <div class="w-12 h-6 bg-accent rounded-full"></div>
                                        <div class="absolute top-1 right-1 w-4 h-4 bg-white rounded-full"></div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center p-3 rounded-lg border border-gray-700">
                                    <div>
                                        <p class="font-medium">Som ao Jogar</p>
                                        <p class="text-xs text-gray-400">Efeitos sonoros durante as jogadas</p>
                                    </div>
                                    <div class="relative">
                                        <div class="w-12 h-6 bg-accent rounded-full"></div>
                                        <div class="absolute top-1 right-1 w-4 h-4 bg-white rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-link card-title-icon"></i>
                                Links Rápidos
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="space-y-2">
                                <a href="jogo/index.php" class="flex items-center gap-2 p-2 hover:bg-white/5 rounded-lg transition-colors">
                                    <i class="fas fa-gamepad text-accent"></i>
                                    <span>Jogar Raspadinha</span>
                                </a>
                                <a href="inicio.php" class="flex items-center gap-2 p-2 hover:bg-white/5 rounded-lg transition-colors">
                                    <i class="fas fa-home text-blue-400"></i>
                                    <span>Página Inicial</span>
                                </a>
                                <a href="#" onclick="abrirDeposito(); return false;" class="flex items-center gap-2 p-2 hover:bg-white/5 rounded-lg transition-colors">
                                    <i class="fas fa-plus text-green-400"></i>
                                    <span>Fazer Depósito</span>
                                </a>
                                <a href="<?php echo htmlspecialchars($suporte_url ?? '#'); ?>" target="_blank" class="flex items-center gap-2 p-2 hover:bg-white/5 rounded-lg transition-colors">
                                    <i class="fas fa-headset text-purple-400"></i>
                                    <span>Suporte</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Saque Pix -->
            <div id="modalSaquePix" class="hidden fixed inset-0 bg-black bg-opacity-80 z-[1001] flex items-center justify-center p-4 backdrop-blur-sm transition-all duration-300">
        <div class="bg-gradient-to-b from-card-bg to-[#151528] rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto border border-gray-700/50 transform transition-all duration-300">
            <div class="flex justify-between items-center p-6 border-b border-gray-700/50 bg-gradient-to-r from-accent-blue/10 to-transparent">
                <h3 class="text-xl font-bold flex items-center">
                    <i class="fas fa-money-bill-wave text-accent mr-3"></i>
                    Saque via Pix
                </h3>
                <button id="closeModalBtn" class="text-gray-400 hover:text-white hover:bg-gray-700/50 h-8 w-8 rounded-full flex items-center justify-center transition-all duration-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <!-- Saldo disponível com estilo melhorado -->
                <div class="bg-gradient-to-r from-accent-blue/20 to-accent/20 p-5 rounded-xl mb-6 border border-accent/20 shadow-lg relative overflow-hidden">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm text-gray-300 block mb-1">Saldo Disponível</span>
                            <span class="font-bold text-2xl text-white">R$ <?= number_format($usuario['balance'] + $usuario['bonus_balance'], 2, ',', '.') ?></span>
                        </div>
                        <div class="bg-accent/20 h-16 w-16 rounded-full flex items-center justify-center border border-accent/30">
                            <i class="fas fa-wallet text-accent text-2xl"></i>
                        </div>
                    </div>
                    <!-- Decorative elements -->
                    <div class="absolute -bottom-6 -right-6 h-24 w-24 rounded-full bg-accent/5 blur-xl"></div>
                    <div class="absolute -top-6 -left-6 h-16 w-16 rounded-full bg-accent-blue/5 blur-xl"></div>
                </div>
                
                <!-- Instruções de saque -->
                <div class="bg-gray-800/30 rounded-lg p-3 mb-6 border border-gray-700/30">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-accent-blue mt-0.5"></i>
                        <p class="text-sm text-gray-300">
                            Preencha os dados abaixo para solicitar seu saque. O valor mínimo é de R$ 10,00 e a transferência será processada em até 24 horas úteis.
                        </p>
                    </div>
                </div>
                
                <form id="formSaquePix" onsubmit="event.preventDefault(); processarSaque();" class="space-y-5">
                    <!-- Valor de saque com ícone e sugestões rápidas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2 flex items-center">
                            <i class="fas fa-dollar-sign text-accent-blue mr-2"></i>
                            Valor do Saque (R$)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400">R$</span>
                            </div>
                            <input type="number" id="valorSaque" name="valor" step="0.01" min="10" max="<?= $usuario['balance'] ?>" 
                                class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-800/50 border border-gray-700 focus:border-accent focus:ring-1 focus:ring-accent outline-none transition-all duration-200 input-field" 
                                placeholder="0,00" required>
                        </div>
                        
                        <!-- Valores rápidos -->
                        <div class="flex flex-wrap gap-2 mt-2">
                            <?php
                            $quickValues = [20, 50, 100, 200];
                            foreach ($quickValues as $value):
                                if ($value <= $usuario['balance']):
                            ?>
                                <button type="button" class="px-3 py-1 text-xs bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-md transition-colors text-gray-300"
                                    onclick="document.getElementById('valorSaque').value = '<?= $value ?>'">
                                    R$ <?= $value ?>
                                </button>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                            <button type="button" class="px-3 py-1 text-xs bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-md transition-colors text-gray-300"
                                onclick="document.getElementById('valorSaque').value = '<?= $usuario['balance'] ?>'">
                                Máximo
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tipo de chave PIX com ícones -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2 flex items-center">
                            <i class="fas fa-key text-accent-blue mr-2"></i>
                            Tipo de Chave Pix
                        </label>
                        <div class="relative">
                            <select id="tipoChave" name="tipo_chave" 
                                class="w-full px-4 py-3 rounded-lg bg-gray-800/50 border border-gray-700 focus:border-accent focus:ring-1 focus:ring-accent appearance-none pr-10 outline-none transition-all duration-200 input-field" 
                                required>
                                <option value="">Selecione o tipo</option>
                                <option value="cpf">CPF</option>
                                <option value="email">E-mail</option>
                                <option value="telefone">Telefone</option>
                                <option value="aleatoria">Chave Aleatória</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chave PIX com ícone dinâmico baseado na seleção -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2 flex items-center">
                            <i class="fas fa-keyboard text-accent-blue mr-2"></i>
                            <span id="labelChavePix">Chave Pix</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i id="iconChavePix" class="fas fa-key text-gray-500"></i>
                            </div>
                            <input type="text" id="chavePix" name="chave_pix" 
                                class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-800/50 border border-gray-700 focus:border-accent focus:ring-1 focus:ring-accent outline-none transition-all duration-200 input-field" 
                                required>
                        </div>
                        <p id="dicaChavePix" class="text-xs text-gray-400 mt-1 hidden">Dica: Insira a chave no formato correto</p>
                    </div>
                    
                    <!-- Nome do beneficiário -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2 flex items-center">
                            <i class="fas fa-user text-accent-blue mr-2"></i>
                            Nome Completo
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                            <input type="text" id="nomeCompleto" name="nome_completo" 
                                class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-800/50 border border-gray-700 focus:border-accent focus:ring-1 focus:ring-accent outline-none transition-all duration-200 input-field" 
                                required>
                        </div>
                    </div>
                    
                    <!-- CPF com máscara -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2 flex items-center">
                            <i class="fas fa-id-card text-accent-blue mr-2"></i>
                            CPF
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-gray-500"></i>
                            </div>
                            <input type="text" id="cpf" name="cpf" maxlength="14" oninput="mascaraCPF(this)" 
                                class="w-full pl-10 pr-4 py-3 rounded-lg bg-gray-800/50 border border-gray-700 focus:border-accent focus:ring-1 focus:ring-accent outline-none transition-all duration-200 input-field" 
                                placeholder="000.000.000-00" required>
                        </div>
                    </div>

                    <!-- Termos e condições -->
                    <div class="p-3 bg-gray-800/30 rounded-lg border border-gray-700/30">
                        <label class="flex items-start gap-2 cursor-pointer">
                            <div class="pt-0.5">
                                <input type="checkbox" id="termosCheck" class="form-checkbox h-4 w-4 text-accent rounded border-gray-700 focus:ring-accent focus:ring-offset-0 transition duration-200" required>
                            </div>
                            <span class="text-sm text-gray-300">
                                Confirmo que a chave PIX informada está em meu nome e concordo com os <a href="#" class="text-accent hover:underline">termos de saque</a>.
                            </span>
                        </label>
                    </div>
                    
                    <!-- Mensagem de erro estilizada -->
                    <div id="mensagemErro" class="hidden rounded-lg p-3 text-sm transform transition-all duration-200 scale-95 opacity-0">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
                            <span class="text-red-300" id="textoErro">Mensagem de erro aqui</span>
                        </div>
                    </div>
                    
                    <!-- Buttons com melhor estilo e feedback visual -->
                    <div class="flex space-x-3 pt-2">
                        <button type="button" id="cancelModalBtn" 
                            class="flex-1 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white font-medium py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" id="botaoSaque" 
                            class="flex-1 bg-gradient-to-r from-accent to-accent-blue text-white font-medium py-3 px-6 rounded-lg hover:opacity-90 transition-all duration-200 flex items-center justify-center shadow-lg shadow-accent/20">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Solicitar Saque
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Função para gerenciar as abas
    function changeTab(tabId) {
        // Remover a classe active de todas as abas e conteúdos
        document.querySelectorAll('.profile-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Adicionar a classe active na aba e conteúdo selecionados
        document.querySelector(`.profile-tab[data-tab="${tabId}"]`).classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }
    
    // Função para mostrar popup de sucesso do saque
    function mostrarSucessoSaque(mensagem, valorSaque) {
      // Verificar se já existe um popup e removê-lo
      const popupExistente = document.getElementById('sucessoSaquePopup');
      if (popupExistente) {
        popupExistente.remove();
      }
      
      // Criar o elemento do popup
      const popup = document.createElement('div');
      popup.id = 'sucessoSaquePopup';
      popup.style.position = 'fixed';
      popup.style.top = '20px';
      popup.style.left = '50%';
      popup.style.transform = 'translateX(-50%)';
      popup.style.backgroundColor = '#22c55e';
      popup.style.color = 'white';
      popup.style.padding = '15px 25px';
      popup.style.borderRadius = '10px';
      popup.style.boxShadow = '0 4px 15px rgba(34, 197, 94, 0.3), 0 6px 20px rgba(0, 0, 0, 0.3)';
      popup.style.zIndex = '10001';
      popup.style.maxWidth = '90%';
      popup.style.textAlign = 'center';
      popup.style.animation = 'slideInDown 0.3s forwards';
      
      // Adicionar conteúdo com ícone de sucesso
      const conteudo = `
        <div style="display: flex; align-items: center;">
          <i class="fas fa-check-circle" style="font-size: 20px; margin-right: 10px;"></i>
          <div>
            <div style="font-weight: bold; margin-bottom: 3px;">Sucesso</div>
            <div style="font-size: 14px;">${mensagem}</div>
            ${valorSaque ? `<div style="font-size: 16px; font-weight: bold; margin-top: 4px;">R$ ${valorSaque}</div>` : ''}
          </div>
          <i class="fas fa-times" style="margin-left: 15px; cursor: pointer; font-size: 16px;" onclick="document.getElementById('sucessoSaquePopup').remove()"></i>
        </div>
      `;
      
      popup.innerHTML = conteudo;
      
      // Adicionar ao body
      document.body.appendChild(popup);
      
      // Adicionar estilo de animação se não existir
      if (!document.getElementById('popupAnimationStyle')) {
        const style = document.createElement('style');
        style.id = 'popupAnimationStyle';
        style.textContent = `
          @keyframes slideInDown {
            from {
              transform: translate(-50%, -20px);
              opacity: 0;
            }
            to {
              transform: translate(-50%, 0);
              opacity: 1;
            }
          }
          @keyframes fadeOut {
            from {
              opacity: 1;
            }
            to {
              opacity: 0;
            }
          }
        `;
        document.head.appendChild(style);
      }
      
      // Auto-fechar após 3 segundos
      setTimeout(() => {
        if (document.getElementById('sucessoSaquePopup')) {
          document.getElementById('sucessoSaquePopup').style.animation = 'fadeOut 0.3s forwards';
          setTimeout(() => {
            if (document.getElementById('sucessoSaquePopup')) {
              document.getElementById('sucessoSaquePopup').remove();
            }
          }, 300);
        }
      }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inicialização das abas
        document.querySelectorAll('.profile-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                changeTab(tabId);
            });
        });
        
        // Modal de saque
        const modal = document.getElementById('modalSaquePix');
        const openBtn = document.getElementById('openModalBtn');
        const openBtnDesktop = document.getElementById('openModalBtnDesktop');
        const openBtnEmpty = document.getElementById('openModalBtnEmpty');
        const closeBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelModalBtn');
        const form = document.getElementById('formSaquePix');
        const erroDiv = document.getElementById('mensagemErro');
        const textoErro = document.getElementById('textoErro');
        const botaoSaque = document.getElementById('botaoSaque');
        const tipoChaveSelect = document.getElementById('tipoChave');
        const chavePixInput = document.getElementById('chavePix');
        const labelChavePix = document.getElementById('labelChavePix');
        const iconChavePix = document.getElementById('iconChavePix');
        const dicaChavePix = document.getElementById('dicaChavePix');

        // Configurações para diferentes tipos de chave PIX
        const configChaves = {
            'cpf': {
                label: 'CPF (Apenas números)',
                icon: 'fa-id-card',
                dica: 'Digite apenas os números do CPF',
                mask: valor => {
                    valor = valor.replace(/\D/g, '');
                    if (valor.length > 11) valor = valor.substring(0, 11);
                    return valor;
                },
                validate: valor => /^\d{11}$/.test(valor)
            },
            'email': {
                label: 'E-mail',
                icon: 'fa-envelope',
                dica: 'Digite um e-mail válido',
                mask: valor => valor,
                validate: valor => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)
            },
            'telefone': {
                label: 'Telefone',
                icon: 'fa-phone',
                dica: 'Digite apenas números com DDD (11 dígitos)',
                mask: valor => {
                    valor = valor.replace(/\D/g, '');
                    if (valor.length > 11) valor = valor.substring(0, 11);
                    return valor;
                },
                validate: valor => /^\d{11}$/.test(valor)
            },
            'aleatoria': {
                label: 'Chave Aleatória',
                icon: 'fa-random',
                dica: 'Digite a chave aleatória completa',
                mask: valor => valor,
                validate: valor => valor.length > 5
            }
        };

        function abrirModal() {
            if (modal) {
                modal.classList.remove('hidden');
                // Adicionar classe para animar a entrada do modal
                setTimeout(() => {
                    if (modal.querySelector('.bg-gradient-to-b')) {
                        modal.querySelector('.bg-gradient-to-b').classList.add('scale-100');
                        modal.querySelector('.bg-gradient-to-b').classList.remove('scale-95');
                    }
                }, 10);
            }
        }

        function fecharModal() {
            if (modal) {
                // Animar a saída do modal
                if (modal.querySelector('.bg-gradient-to-b')) {
                    modal.querySelector('.bg-gradient-to-b').classList.remove('scale-100');
                    modal.querySelector('.bg-gradient-to-b').classList.add('scale-95');
                }
                
                setTimeout(() => {
                    modal.classList.add('hidden');
                    if (form) form.reset();
                    esconderErro();
                    // Resetar o estilo dos campos
                    resetarEstilosCampos();
                }, 200);
            }
        }

        function resetarEstilosCampos() {
            // Resetar a aparência dos campos
            const campos = form.querySelectorAll('input, select');
            campos.forEach(campo => {
                campo.classList.remove('border-red-500', 'border-accent');
                campo.classList.add('border-gray-700');
            });
            
            // Resetar o tipo de chave PIX
            atualizarCampoChavePix('');
        }

        function mostrarErro(mensagem) {
            if (erroDiv && textoErro) {
                textoErro.textContent = mensagem;
                erroDiv.classList.remove('hidden', 'opacity-0', 'scale-95');
                erroDiv.classList.add('bg-red-900/20', 'border', 'border-red-800/50');
                
                // Animar a entrada do erro
                setTimeout(() => {
                    erroDiv.classList.add('opacity-100', 'scale-100');
                }, 10);
            }
        }

        function esconderErro() {
            if (erroDiv) {
                erroDiv.classList.remove('opacity-100', 'scale-100');
                erroDiv.classList.add('opacity-0', 'scale-95');
                
                setTimeout(() => {
                    erroDiv.classList.add('hidden');
                }, 200);
            }
        }

        function atualizarCampoChavePix(tipo) {
            if (!tipo) {
                labelChavePix.textContent = "Chave Pix";
                iconChavePix.className = "fas fa-key text-gray-500";
                dicaChavePix.classList.add('hidden');
                chavePixInput.placeholder = "";
                return;
            }
            
            const config = configChaves[tipo];
            if (config) {
                labelChavePix.textContent = config.label;
                iconChavePix.className = `fas ${config.icon} text-gray-500`;
                dicaChavePix.textContent = config.dica;
                dicaChavePix.classList.remove('hidden');
                
                // Ajustar placeholder baseado no tipo
                if (tipo === 'cpf') {
                    chavePixInput.placeholder = "Ex: 12345678900";
                } else if (tipo === 'email') {
                    chavePixInput.placeholder = "Ex: seu.email@exemplo.com";
                } else if (tipo === 'telefone') {
                    chavePixInput.placeholder = "Ex: 11999887766";
                } else {
                    chavePixInput.placeholder = "Digite a chave";
                }
            }
        }

        // Adicionar animação ao abrir o modal
        if (openBtn) openBtn.addEventListener('click', abrirModal);
        if (openBtnDesktop) openBtnDesktop.addEventListener('click', abrirModal);
        if (openBtnEmpty) openBtnEmpty.addEventListener('click', abrirModal);
        if (closeBtn) closeBtn.addEventListener('click', fecharModal);
        if (cancelBtn) cancelBtn.addEventListener('click', fecharModal);
        
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    fecharModal();
                }
            });
        }
        
        // Inicializar comportamento dos campos do formulário
        if (tipoChaveSelect) {
            tipoChaveSelect.addEventListener('change', function() {
                const tipoSelecionado = this.value;
                atualizarCampoChavePix(tipoSelecionado);
                
                // Limpar o campo de chave PIX quando mudar o tipo
                if (chavePixInput) {
                    chavePixInput.value = '';
                }
                
                // Destacar o campo selecionado
                this.classList.add('border-accent', 'ring-1', 'ring-accent');
            });
        }
        
        if (chavePixInput) {
            chavePixInput.addEventListener('input', function() {
                const tipoSelecionado = tipoChaveSelect.value;
                if (tipoSelecionado && configChaves[tipoSelecionado]) {
                    this.value = configChaves[tipoSelecionado].mask(this.value);
                }
            });
            
            // Highlight ao focar
            chavePixInput.addEventListener('focus', function() {
                this.classList.add('border-accent', 'ring-1', 'ring-accent');
            });
            
            chavePixInput.addEventListener('blur', function() {
                this.classList.remove('border-accent', 'ring-1', 'ring-accent');
                // Validar o campo quando o usuário sair dele
                const tipoSelecionado = tipoChaveSelect.value;
                if (tipoSelecionado && this.value && configChaves[tipoSelecionado]) {
                    const isValid = configChaves[tipoSelecionado].validate(this.value);
                    if (!isValid) {
                        this.classList.add('border-red-500');
                    } else {
                        this.classList.remove('border-red-500');
                    }
                }
            });
        }
        
        // Máscara para CPF
        window.mascaraCPF = function(input) {
            let valor = input.value.replace(/\D/g, '');
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            input.value = valor;
        }

        // Adicionar animações e validações no formulário
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            // Highlight ao focar
            input.addEventListener('focus', function() {
                this.classList.add('border-accent', 'ring-1', 'ring-accent');
            });
            
            input.addEventListener('blur', function() {
                this.classList.remove('border-accent', 'ring-1', 'ring-accent');
            });
        });

        // Processamento do saque com validações melhoradas
        window.processarSaque = function() {
            const valor = parseFloat(document.getElementById('valorSaque').value);
            const saldoAtual = <?= $usuario['balance'] ?>;
            const tipoChave = tipoChaveSelect.value;
            const chavePix = chavePixInput.value;
            const nomeCompleto = document.getElementById('nomeCompleto').value;
            const cpf = document.getElementById('cpf').value;
            
            // Esconder mensagem de erro anterior
            esconderErro();
            
            // Validar valor do saque
            if (isNaN(valor) || valor < 10) {
                mostrarErro('O valor mínimo para saque é R$ 10,00');
                document.getElementById('valorSaque').classList.add('border-red-500');
                return;
            } else {
                document.getElementById('valorSaque').classList.remove('border-red-500');
            }
            
            if (valor > saldoAtual) {
                mostrarErro('Valor do saque não pode ser maior que o saldo disponível');
                document.getElementById('valorSaque').classList.add('border-red-500');
                return;
            }
            
            // Validar tipo de chave
            if (!tipoChave) {
                mostrarErro('Selecione um tipo de chave PIX');
                tipoChaveSelect.classList.add('border-red-500');
                return;
            } else {
                tipoChaveSelect.classList.remove('border-red-500');
            }
            
            // Validar chave PIX
            if (!chavePix) {
                mostrarErro('Digite sua chave PIX');
                chavePixInput.classList.add('border-red-500');
                return;
            }
            
            // Validação específica por tipo de chave
            if (tipoChave && configChaves[tipoChave]) {
                const isValid = configChaves[tipoChave].validate(chavePix);
                if (!isValid) {
                    mostrarErro(`Chave PIX inválida para o tipo ${configChaves[tipoChave].label}`);
                    chavePixInput.classList.add('border-red-500');
                    return;
                } else {
                    chavePixInput.classList.remove('border-red-500');
                }
            }
            
            // Validar nome completo
            if (!nomeCompleto || nomeCompleto.trim().split(' ').length < 2) {
                mostrarErro('Digite seu nome completo');
                document.getElementById('nomeCompleto').classList.add('border-red-500');
                return;
            } else {
                document.getElementById('nomeCompleto').classList.remove('border-red-500');
            }
            
            // Validar CPF
            const cpfLimpo = cpf.replace(/\D/g, '');
            if (cpfLimpo.length !== 11) {
                mostrarErro('CPF inválido');
                document.getElementById('cpf').classList.add('border-red-500');
                return;
            } else {
                document.getElementById('cpf').classList.remove('border-red-500');
            }
            
            // Validar checkbox de termos
            const termosCheck = document.getElementById('termosCheck');
            if (!termosCheck.checked) {
                mostrarErro('É necessário concordar com os termos de saque');
                termosCheck.parentElement.classList.add('ring-1', 'ring-red-500');
                return;
            } else {
                termosCheck.parentElement.classList.remove('ring-1', 'ring-red-500');
            }

            const formData = new FormData(form);
            
            // Animação de loading no botão
            const textoOriginal = botaoSaque.innerHTML;
            botaoSaque.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Processando...';
            botaoSaque.disabled = true;
            botaoSaque.classList.add('opacity-70');

            fetch('processar_saque_pix.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Disparar evento de Purchase do Facebook Pixel
                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'Purchase', {
                            value: valor,
                            currency: 'BRL'
                        });
                    }
                    
                    // Mostrar popup estilizado de sucesso com o valor
                    const valorFormatado = parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    mostrarSucessoSaque('Solicitação de saque enviada com sucesso!', valorFormatado);
                    fecharModal();
                    setTimeout(() => {
                        location.reload();
                    }, 3000); // Recarregar após 3 segundos para que o usuário veja a mensagem
                } else {
                    mostrarErro(data.message || 'Erro ao processar saque. Tente novamente.');
                }
            })
            .catch(error => {
                mostrarErro('Erro de conexão. Verifique sua internet e tente novamente.');
            })
            .finally(() => {
                botaoSaque.innerHTML = textoOriginal;
                botaoSaque.disabled = false;
                botaoSaque.classList.remove('opacity-70');
            });
        }
        
        // Inicializar o estilo do modal
        if (modal && modal.querySelector('.bg-gradient-to-b')) {
            modal.querySelector('.bg-gradient-to-b').classList.add('scale-95', 'transition-transform', 'duration-300');
        }
        
        // Animação do progresso do saque
        const valorSaqueInput = document.getElementById('valorSaque');
        if (valorSaqueInput) {
            valorSaqueInput.addEventListener('input', function() {
                const valor = parseFloat(this.value) || 0;
                const saldoAtual = <?= $usuario['balance'] ?>;
                const porcentagem = Math.min(100, Math.max(0, (valor / saldoAtual) * 100));
                
                // Atualizar o indicador visual se existir
                const saldoCard = document.querySelector('.bg-gradient-to-r.from-accent-blue\\/20');
                if (saldoCard) {
                    const progress = saldoCard.querySelector('.progress-bar');
                    if (!progress) {
                        // Criar barra de progresso se não existir
                        const progressBar = document.createElement('div');
                        progressBar.className = 'progress-bar absolute bottom-0 left-0 h-1 bg-gradient-to-r from-accent to-accent-blue transition-all duration-300';
                        progressBar.style.width = porcentagem + '%';
                        saldoCard.style.position = 'relative';
                        saldoCard.appendChild(progressBar);
                    } else {
                        // Atualizar largura da barra existente
                        progress.style.width = porcentagem + '%';
                    }
                }
            });
        }
        
        // Adicionar vibração ao botão de saque quando o valor for válido
        const botaoSaqueElem = document.getElementById('botaoSaque');
        if (botaoSaqueElem && valorSaqueInput) {
            valorSaqueInput.addEventListener('change', function() {
                const valor = parseFloat(this.value) || 0;
                if (valor >= 10 && valor <= <?= $usuario['balance'] ?>) {
                    botaoSaqueElem.classList.add('pulse-animation');
                    setTimeout(() => {
                        botaoSaqueElem.classList.remove('pulse-animation');
                    }, 1000);
                }
            });
        }
        
        // Adicionar estilos de animação para botão
        const style = document.createElement('style');
        style.textContent = `
            .pulse-animation {
                animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1);
            }
            
            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.05);
                }
            }
        `;
        document.head.appendChild(style);
    });
    </script>
    <!-- Navegação Mobile -->
    <?php include __DIR__ . '/includes/mobile_nav.php'; ?>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
