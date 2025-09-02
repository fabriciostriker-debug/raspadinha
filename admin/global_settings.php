<?php
session_start();
require_once '../includes/db.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
}

// Verificar se o admin tem 2FA configurado
$stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE id = ? AND is_admin = 1");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($user['two_factor_secret'])) {
    header("Location: setup_2fa.php");

}

$message = '';


// Criar tabela de banners se não existir
$conn->query("
    CREATE TABLE IF NOT EXISTS banners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        file_path VARCHAR(500) NOT NULL,
        file_size INT DEFAULT 0,
        file_type VARCHAR(100),
        width INT DEFAULT 0,
        height INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        position VARCHAR(50) DEFAULT 'header',
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

// Configurações padrão
$default_settings = [
    'default_revshare_rate' => ['value' => '5.0', 'description' => 'Taxa padrão de RevShare (%)'],
    'min_payout_amount' => ['value' => '10.00', 'description' => 'Valor mínimo para saque global (R$)'],
    'max_payout_amount' => ['value' => '5000.00', 'description' => 'Valor máximo para saque (R$)'],
    'min_deposit_amount' => ['value' => '5.00', 'description' => 'Valor mínimo para depósito global (R$)'],
    'initial_bonus_amount' => ['value' => '10.00', 'description' => 'Valor do bônus inicial para novos usuários (R$)'],
    'initial_bonus_enabled' => ['value' => '1', 'description' => 'Bônus inicial ativo (1=Sim, 0=Não)'],
    'affiliate_system_enabled' => ['value' => '1', 'description' => 'Sistema de afiliados ativo (1=Sim, 0=Não)'],
    'auto_approve_payouts' => ['value' => '0', 'description' => 'Aprovar saques automaticamente (1=Sim, 0=Não)'],
    'commission_delay_hours' => ['value' => '24', 'description' => 'Delay para liberar comissões (horas)'],
    'max_affiliate_levels' => ['value' => '4', 'description' => 'Número máximo de níveis de afiliados'],
    'level_2_percentage' => ['value' => '20', 'description' => 'Porcentagem do nível 2 (% da comissão do nível 1)'],
    'level_3_percentage' => ['value' => '10', 'description' => 'Porcentagem do nível 3 (% da comissão do nível 1)'],
    'level_4_percentage' => ['value' => '5', 'description' => 'Porcentagem do nível 4 (% da comissão do nível 1)'],
    'require_password_confirmation' => ['value' => '1', 'description' => 'Exigir confirmação de senha no registro (1=Sim, 0=Não)'],
];

// Inserir configurações padrão se não existirem
foreach ($default_settings as $key => $data) {
    $stmt = $conn->prepare("INSERT IGNORE INTO global_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $key, $data['value'], $data['description']);
    $stmt->execute();
}

// Processar atualizações
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'update_settings') {
        $checkbox_settings = [
            'auto_approve_payouts',
            'initial_bonus_enabled', 
            'affiliate_system_enabled',
            'require_password_confirmation',
            'double_deposit_enabled',
            'show_rollover_box',
            'deduct_withdrawal_from_affiliate'
        ];
        
        // Processar campos que não são checkboxes
        if (isset($_POST['settings'])) {
            foreach ($_POST['settings'] as $key => $value) {
                if (!in_array($key, $checkbox_settings)) {
                    $stmt = $conn->prepare("UPDATE global_settings SET setting_value = ? WHERE setting_key = ?");
                    $stmt->bind_param("ss", $value, $key);
                    $stmt->execute();
                }
            }
        }
        
        // Processar checkboxes - SEMPRE atualizar para '1' se marcado ou '0' se não marcado
        foreach ($checkbox_settings as $checkbox) {
            $value = (isset($_POST['settings'][$checkbox]) && $_POST['settings'][$checkbox] == '1') ? '1' : '0';
            $stmt = $conn->prepare("UPDATE global_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $checkbox);
            $stmt->execute();
        }
        
        $message = "Configurações atualizadas com sucesso!";
    }
    
    
    
    
    
}

    


$settings_result = $conn->query("SELECT * FROM global_settings ORDER BY setting_key");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

$users_result = $conn->query("
    SELECT u.id, u.name as username, u.email,
           us.min_deposit_amount, us.min_withdrawal_amount, us.influence_mode_enabled
    FROM users u 
    LEFT JOIN user_settings us ON u.id = us.user_id 
    WHERE u.is_admin = 0 
    ORDER BY u.name");


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo Completo</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="css/sidebar.css" rel="stylesheet">

    
    <style>
        :root {
            --primary-color: #7257b4;
            --secondary-color: #6876df;
            --success-color: #10b981;
            --warning-color: #fbbf24;
            --danger-color: #ef4444;
            --dark-color: #202c3e;
        }

        body {
            background: linear-gradient(135deg, var(--dark-color) 0%, #1e293b 100%);
			padding-left: 0 !important;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .main-container {
    padding: 1rem;
    margin-left: 250px; /* Espaço para a sidebar */
    transition: margin-left 0.3s ease;
}

@media (max-width: 768px) {
    .main-container {
        margin-left: 0;
        padding: 1rem 0.5rem;
    }
}


        @media (min-width: 768px) {
            .main-container {
                padding: 2rem;
            }
        }

        .settings-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .settings-card {
                padding: 2rem;
            }
        }

        .setting-group {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }

        @media (min-width: 768px) {
            .setting-group {
                padding: 1.5rem;
            }
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 0.5rem;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(114, 87, 180, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
        }

        @media (min-width: 768px) {
            .btn-primary {
                padding: 0.75rem 2rem;
            }
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #059669);
            border: none;
            border-radius: 0.5rem;
        }

        .setting-description {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 0.25rem;
        }

        .danger-zone {
            border-left-color: var(--danger-color);
            background: rgba(239, 68, 68, 0.1);
        }

        .warning-zone {
            border-left-color: var(--warning-color);
            background: rgba(251, 191, 36, 0.1);
        }

        .user-settings-zone {
            border-left-color: var(--success-color);
            background: rgba(16, 185, 129, 0.1);
        }

        .nav-tabs .nav-link {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }

        @media (min-width: 768px) {
            .nav-tabs .nav-link {
                margin-right: 0.5rem;
                margin-bottom: 0;
                border-radius: 0.5rem 0.5rem 0 0;
                font-size: 1rem;
                padding: 0.75rem 1.5rem;
            }
        }

        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .tab-content {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 1rem;
        }

        @media (min-width: 768px) {
            .tab-content {
                border-radius: 0 0.75rem 0.75rem 0.75rem;
                padding: 2rem;
            }
        }

        .table-dark {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
        }

        .table-dark th,
        .table-dark td {
            border-color: rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
        }

        @media (min-width: 768px) {
            .table-dark th,
            .table-dark td {
                font-size: 1rem;
            }
        }

        .modal-content {
            background: var(--dark-color);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .banner-preview {
            max-width: 200px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        @media (min-width: 768px) {
            .stats-number {
                font-size: 2rem;
            }
        }

        .stats-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
        }

        @media (min-width: 768px) {
            .stats-label {
                font-size: 0.9rem;
            }
        }

        /* Responsividade para DataTables */
        .dataTables_wrapper {
            color: white;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: white;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 0.25rem;
        }

        .page-link {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .page-link:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
        <?php require_once 'includes/sidebar.php'; ?>


    <div class="container-fluid main-container">
        <!-- Mensagens -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tabs de Navegação -->
        <ul class="nav nav-tabs mb-4 flex-wrap" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="global-tab" data-bs-toggle="tab" data-bs-target="#global" type="button" role="tab">
                    <i class="bi bi-globe"></i> <span class="d-none d-sm-inline">Configurações Globais</span><span class="d-sm-none">Global</span>
                </button>
            </li>
            
            
        </ul>

        <div class="tab-content" id="configTabsContent">
            <!-- Tab Configurações Globais -->
            <div class="tab-pane fade show active" id="global" role="tabpanel">
                <div class="row">
                    <div class="col-lg-8">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_settings">
                            
                            <!-- Configurações de Comissão -->
                            <div class="setting-group">
                                <h4 class="mb-3">
                                    <i class="bi bi-percent"></i> Configurações de Comissão
                                </h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Taxa Padrão RevShare (%)</label>
                                        <input type="number" class="form-control" name="settings[default_revshare_rate]" 
                                               value="<?php echo $settings['default_revshare_rate']['setting_value']; ?>" 
                                               step="0.1" min="0" max="100">
                                        <div class="setting-description">
                                            <?php echo $settings['default_revshare_rate']['description']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Delay para Comissões (horas)</label>
                                        <input type="number" class="form-control" name="settings[commission_delay_hours]" 
                                               value="<?php echo $settings['commission_delay_hours']['setting_value']; ?>" 
                                               min="0" max="168">
                                        <div class="setting-description">
                                            <?php echo $settings['commission_delay_hours']['description']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configurações de Níveis -->
                            <div class="setting-group">
                                <h4 class="mb-3">
                                    <i class="bi bi-diagram-3"></i> Configurações de Níveis
                                </h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Máximo de Níveis</label>
                                        <select class="form-select" name="settings[max_affiliate_levels]">
                                            <option value="1" <?php echo $settings['max_affiliate_levels']['setting_value'] == '1' ? 'selected' : ''; ?>>1 Nível</option>
                                            <option value="2" <?php echo $settings['max_affiliate_levels']['setting_value'] == '2' ? 'selected' : ''; ?>>2 Níveis</option>
                                            <option value="3" <?php echo $settings['max_affiliate_levels']['setting_value'] == '3' ? 'selected' : ''; ?>>3 Níveis</option>
                                            <option value="4" <?php echo $settings['max_affiliate_levels']['setting_value'] == '4' ? 'selected' : ''; ?>>4 Níveis</option>
                                        </select>
                                        <div class="setting-description">
                                            <?php echo $settings['max_affiliate_levels']['description']; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nível 2 (%)</label>
                                        <input type="number" class="form-control" name="settings[level_2_percentage]" 
                                               value="<?php echo $settings['level_2_percentage']['setting_value']; ?>" 
                                               min="0" max="100">
                                        <div class="setting-description">
                                            <?php echo $settings['level_2_percentage']['description']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nível 3 (%)</label>
                                        <input type="number" class="form-control" name="settings[level_3_percentage]" 
                                               value="<?php echo $settings['level_3_percentage']['setting_value']; ?>" 
                                               min="0" max="100">
                                        <div class="setting-description">
                                            <?php echo $settings['level_3_percentage']['description']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Nível 4 (%)</label>
                                        <input type="number" class="form-control" name="settings[level_4_percentage]" 
                                               value="<?php echo $settings['level_4_percentage']['setting_value']; ?>" 
                                               min="0" max="100">
                                        <div class="setting-description">
                                            <?php echo $settings['level_4_percentage']['description']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configurações de Depósito e Saque -->
                            <div class="setting-group warning-zone">
                                <h4 class="mb-3">
                                    <i class="bi bi-cash-stack"></i> Configurações de Depósito e Saque
                                </h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Valor Mínimo Depósito Global (R$)</label>
                                        <input type="number" class="form-control" name="settings[min_deposit_amount]" 
                                               value="<?php echo $settings['min_deposit_amount']['setting_value']; ?>" 
                                               step="0.01" min="1">
                                        <div class="setting-description">
                                            <?php echo $settings['min_deposit_amount']['description']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Valor Mínimo Saque Global (R$)</label>
                                        <input type="number" class="form-control" name="settings[min_payout_amount]" 
                                               value="<?php echo $settings['min_payout_amount']['setting_value']; ?>" 
                                               step="0.01" min="1">
                                        <div class="setting-description">
                                            <?php echo $settings['min_payout_amount']['description']; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Valor Máximo Saque (R$)</label>
                                        <input type="number" class="form-control" name="settings[max_payout_amount]" 
                                               value="<?php echo $settings['max_payout_amount']['setting_value']; ?>" 
                                               step="0.01" min="1">
                                        <div class="setting-description">
                                            <?php echo $settings['max_payout_amount']['description']; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="settings[auto_approve_payouts]" 
                                           value="1" <?php echo $settings['auto_approve_payouts']['setting_value'] == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Aprovar saques automaticamente
                                    </label>
                                    <div class="setting-description">
                                        <?php echo $settings['auto_approve_payouts']['description']; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Configurações de Bônus -->
                            <div class="setting-group user-settings-zone">
                                <h4 class="mb-3">
                                    <i class="bi bi-gift"></i> Configurações de Bônus
                                </h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Valor do Bônus Inicial (R$)</label>
                                        <input type="number" class="form-control" name="settings[initial_bonus_amount]" 
                                               value="<?php echo $settings['initial_bonus_amount']['setting_value']; ?>" 
                                               step="0.01" min="0">
                                        <div class="setting-description">
                                            <?php echo $settings['initial_bonus_amount']['description']; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="settings[initial_bonus_enabled]" 
                                           value="1" <?php echo $settings['initial_bonus_enabled']['setting_value'] == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Bônus inicial ativo
                                    </label>
                                    <div class="setting-description">
                                        <?php echo $settings['initial_bonus_enabled']['description']; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Configurações de Depósito em Dobro -->
                            <div class="setting-group warning-zone">
                                <h4 class="mb-3">
                                    <i class="bi bi-cash-coin"></i> Configurações de Depósito em Dobro
                                </h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Multiplicador do Rollover</label>
                                        <input type="number" class="form-control" name="settings[double_deposit_rollover_multiplier]" 
                                               value="<?php echo isset($settings['double_deposit_rollover_multiplier']) ? $settings['double_deposit_rollover_multiplier']['setting_value'] : '3'; ?>" 
                                               step="0.1" min="1" max="10">
                                        <div class="setting-description">
                                            Multiplicador para calcular o rollover necessário (ex: 3x o valor do bônus)
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="settings[double_deposit_enabled]" 
                                           value="1" <?php echo (isset($settings['double_deposit_enabled']) && $settings['double_deposit_enabled']['setting_value'] == '1') ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Depósito em dobro ativo
                                    </label>
                                    <div class="setting-description">
                                        Quando ativo, usuários recebem bônus igual ao valor depositado
                                    </div>
                                </div>
                                
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="settings[show_rollover_box]" 
                                           value="1" <?php echo (isset($settings['show_rollover_box']) && $settings['show_rollover_box']['setting_value'] == '1') ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Exibir caixa de rollover no perfil
                                    </label>
                                    <div class="setting-description">
                                        Quando ativo, exibe a caixa de progresso de rollover na página de perfil do usuário
                                    </div>
                                </div>
                            </div>

                            <!-- Configurações do Site -->
                            <div class="setting-group">
                                <h4 class="mb-3">
                                    <i class="bi bi-globe"></i> Configurações do Site
                                </h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nome do Site</label>
                                        <input type="text" class="form-control" name="settings[site_name]" 
                                               value="<?php echo isset($settings['site_name']) ? $settings['site_name']['setting_value'] : ''; ?>">
                                        <div class="setting-description">
                                            Nome do site exibido no cabeçalho e outras áreas
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configurações do Sistema -->
                            <div class="setting-group danger-zone">
                                <h4 class="mb-3">
                                    <i class="bi bi-gear-wide-connected"></i> Configurações do Sistema
                                </h4>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="settings[affiliate_system_enabled]" 
                                           value="1" <?php echo $settings['affiliate_system_enabled']['setting_value'] == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        Sistema de afiliados ativo
                                    </label>
                                    <div class="setting-description">
                                        <?php echo $settings['affiliate_system_enabled']['description']; ?>
                                    </div>
                                </div>
                                
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="settings[require_password_confirmation]" 
                                       value="1" <?php echo $settings['require_password_confirmation']['setting_value'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label">
                                    Exigir confirmação de senha no registro
                                </label>
                                <div class="setting-description">
                                    <?php echo $settings['require_password_confirmation']['description']; ?>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="settings[deduct_withdrawal_from_affiliate]" 
                                       value="1" <?php echo (isset($settings['deduct_withdrawal_from_affiliate']) && $settings['deduct_withdrawal_from_affiliate']['setting_value'] == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label">
                                    Descontar saques dos usuários do saldo do afiliado
                                </label>
                                <div class="setting-description">
                                    Quando ativo, o valor do saque de um usuário será descontado do saldo do afiliado que o indicou
                                </div>
                            </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Salvar Configurações Globais
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Informações de Ajuda -->
                        <div class="settings-card">
                            <h5 class="mb-3">
                                <i class="bi bi-info-circle"></i> Informações Importantes
                            </h5>
                            
                            <div class="alert alert-warning">
                                <strong>Atenção:</strong> Alterações nas configurações de comissão afetarão apenas novos afiliados ou novas comissões.
                            </div>
                            
                            <div class="alert alert-info">
                                <strong>Níveis de Afiliados:</strong> As porcentagens dos níveis 2, 3 e 4 são calculadas sobre a comissão do nível 1.
                            </div>
                            
                            <div class="alert alert-success">
                                <strong>Bônus Inicial:</strong> Quando ativo, novos usuários receberão automaticamente o valor configurado.
                            </div>
                            
                            <div class="alert alert-danger">
                                <strong>Zona de Perigo:</strong> Desativar o sistema de afiliados impedirá novos cadastros e comissões.
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>


            
        </div>
    </div>

    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Inicializar DataTables
        $(document).ready(function() {
            $('#usersTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                responsive: true,
                pageLength: 10,
                order: [[0, 'asc']]
            });
            
            $('#affiliatesTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                responsive: true,
                pageLength: 10,
                order: [[7, 'desc']], // Ordenar por Total Ganho
                columnDefs: [
                    { targets: [5, 6], className: 'text-center' },
                    { targets: [7, 8, 9], className: 'text-end' }
                ]
            });
        });
        
        function editUserSettings(userId, username, minDeposit, minWithdrawal, influenceMode) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_min_deposit').value = minDeposit || '';
            document.getElementById('edit_min_withdrawal').value = minWithdrawal || '';
            document.getElementById('edit_influence_mode').checked = influenceMode;
            
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }
    </script>
</body>
</html>
